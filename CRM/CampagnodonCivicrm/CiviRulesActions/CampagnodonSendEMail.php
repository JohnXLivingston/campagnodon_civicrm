<?php

use CRM_CampagnodonCivicrm_ExtensionUtil as E;
use Civi\Token\TokenProcessor;

class CRM_CampagnodonCivicrm_CiviRulesActions_CampagnodonSendEMail extends CRM_Civirules_Action {
  /**
   * Method to return the url for additional form processing for action
   * and return false if none is needed
   *
   * @param int $ruleActionId
   * @return bool
   * @access public
   */
  public function getExtraDataInputUrl($ruleActionId) {
    return CRM_Utils_System::url('civicrm/campagnodon/civirule/form/action/campagnodontransactionsendemail', "rule_action_id={$ruleActionId}");
  }

  /**
   * Returns a user friendly text explaining the condition params
   * e.g. 'Older than 65'
   *
   * @return string
   * @access public
   */
  public function userFriendlyConditionParams() {
    $template = 'unknown template';
    $fromAddress = 'unknown';
    $params = $this->getActionParameters();

    if (!empty($params['from_email']) && !empty($params['from_name'])) {
      $fromAddress = htmlspecialchars("\"{$params['from_name']} <{$params['from_email']}>\"");
    } else {
      $fromAddress = E::ts('Default domain from email address');
      list($defaultFromName, $defaultFromEmail) = CRM_Core_BAO_Domain::getNameAndEmail();
      $fromAddress.= ' (' . htmlspecialchars("\"{$defaultFromName} <{$defaultFromEmail}>\"") . ')';
    }

    if (!empty($params['template_id'])) {
      $messageTemplates = new CRM_Core_DAO_MessageTemplate();
      $messageTemplates->id = $params['template_id'];
      $messageTemplates->is_active = true;
      if ($messageTemplates->find(TRUE)) {
        $template = "<a href='"
          . CRM_Utils_System::url('civicrm/admin/messageTemplates/add', ['action' => 'update', 'id' => $messageTemplates->id, 'reset' => 1])
          . "'>$messageTemplates->msg_title</a>";
      }
    }

    $message = E::ts("Send email from '%1' with Template '%2' to the mail used for a Campagnodon Transaction.", [
      1 => $fromAddress,
      2 => $template
    ]);
    $message.= E::ts("If this action is not triggered on a Campagnodon Transaction, it will not do anything.");
    return $message;
  }

  /**
   * Method processAction to execute the action
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @access public
   *
   */
  public function processAction(CRM_Civirules_TriggerData_TriggerData $triggerData) {
    $params = $this->getActionParameters();
    $contactId = $triggerData->getContactId();
    $params['contact_id'] = $contactId;

    $triggerCampagnodonTransaction = $triggerData->getEntityData('CampagnodonTransaction');
    if (!$triggerCampagnodonTransaction) {
      // This actions only work if there is a related Campagnodon Transaction
      $message = __METHOD__.' There is no CampagnodonTransaction for this triggered action.';
      Civi::log()->error($message);
      throw new Exception($message);
    }

    Civi::log()->info(__CLASS__.'::'.__METHOD__ . ' Sending a Campagnodon email for the transaction '.$triggerCampagnodonTransaction['id'].'...');

    if (!empty($params['from_email']) && !empty($params['from_name'])) {
      $from = '"' . $params['from_name'] . '" <' . $params['from_email'] . '>';
    } elseif (!empty($params['from_email']) || !empty($params['from_name'])) {
      throw new Exception('Missing from_email or from_name. Please set both or none.');
    } else {
      list($defaultFromName, $defaultFromEmail) = CRM_Core_BAO_Domain::getNameAndEmail();
      $from = "\"$defaultFromName\" <$defaultFromEmail>";
    }

    $messageTemplates = new CRM_Core_DAO_MessageTemplate();
    $messageTemplates->id = $params['template_id'];
    if (!$messageTemplates->find(TRUE)) {
      throw new API_Exception('Could not find template with ID: ' . $params['template_id']);
    }

    // NB: email can be removed from the transaction (see the Clean API).
    // So we are using the email used for the transaction, and fallback to the contact mail if needed.
    if (!empty($triggerCampagnodonTransaction['email'])) {
      $toName = '';
      $toEmail = $triggerCampagnodonTransaction['email'];
    } else {
      $contact = \Civi\Api4\Contact::get()
        ->setCheckPermissions(false)
        ->addSelect('*')
        ->addWhere('id', '=', $contactId)
        ->execute()
        ->single();
      $toName = $contact['display_name'];
      $toEmail = $contact['email'];
    }

    list ($subject, $html, $text) = $this->_processTemplate($messageTemplates, $contactId, $triggerCampagnodonTransaction);

    // Creating an activity
    $activity = $this->_createActivity($contactId, $subject, $html, $text);

    $mailParams = [
      'from' => $from,
      'toName' => $toName,
      'toEmail' => $toEmail,
      'subject' => $subject,
      'text' => $text,
      'html' => $html,
      'contactId' => $contactId,
    ];
    // Try to send the email.
    Civi::log()->debug(__CLASS__.'::'.__METHOD__ . ' Sending the mail with CRM_Utils_Mail');
    $result = CRM_Utils_Mail::send($mailParams);
    if (!$result) {
      Civi::log()->error(__CLASS__.'::'.__METHOD__ . ' Failed to send mail.');
      throw new Exception('Error sending email to ' . $toEmail . '.');
    }

    // Mail sent, updating the activity.
    $this->_completeActivity($activity);
  }

  private function _processTemplate($messageTemplates, $contactId, $triggerCampagnodonTransaction) {
    Civi::log()->debug(__CLASS__.'::'.__METHOD__ . 'Processing the template...');
    $schema = [];
    $context = [];
    $schema['contactId'] = 'contactId';
    $context['contactId'] = $contactId;
    $schema['campagnodontransactionId'] = 'campagnodontransactionId';
    $context['campagnodontransactionId'] = $triggerCampagnodonTransaction['id'];
    $context['campagnodontransaction'] = $triggerCampagnodonTransaction;

    $useSmarty = (defined('CIVICRM_MAIL_SMARTY') && CIVICRM_MAIL_SMARTY);

    $tokenProcessor = new TokenProcessor(\Civi::dispatcher(), [
      'controller' => __CLASS__,
      'schema' => $schema,
      'smarty' => $useSmarty,
    ]);

    $tokenProcessor->addMessage('messageSubject', $messageTemplates->msg_subject, 'text/plain');
    $tokenProcessor->addMessage('html', $messageTemplates->msg_html, 'text/html');
    $tokenProcessor->addMessage('text',
      $messageTemplates->msg_text
        ? $messageTemplates->msg_text : CRM_Utils_String::htmlToText($messageTemplates->msg_html), 'text/plain'
    );
    $row = $tokenProcessor->addRow($context);
    $tokenProcessor->evaluate();

    $subject = $row->render('messageSubject');
    $html = $row->render('html');
    $text = $row->render('text');

    Civi::log()->debug(__CLASS__.'::'.__METHOD__ . ' Template processed.');

    return [$subject, $html, $text];
  }

  private function _createActivity($contactId, $subject, $html, $text) {
    Civi::log()->debug(__CLASS__.'::'.__METHOD__ . ' Creating an activity');

    $activityTypeID = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Email');
    if (!empty($html) && !empty($text)) {
      $details = "-ALTERNATIVE ITEM 0-\n$html\n-ALTERNATIVE ITEM 1-\n$text\n-ALTERNATIVE END-\n";
    } else {
      $details = $html ? $html : $text;
    }
    $activityParams = [
      'source_contact_id' => $contactId,
      'activity_type_id' => $activityTypeID,
      'subject' => $subject,
      'details' => $details,
      'status_id' => CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_status_id', 'Cancelled'),
    ];
    $activity = civicrm_api3('Activity', 'create', $activityParams);

    $activityContacts = CRM_Core_OptionGroup::values('activity_contacts', FALSE, FALSE, FALSE, NULL, 'name');
    $targetID = CRM_Utils_Array::key('Activity Targets', $activityContacts);

    $activityTargetParams = [
      'activity_id' => $activity['id'],
      'contact_id' => $contactId,
      'record_type_id' => $targetID,
    ];
    CRM_Activity_BAO_ActivityContact::create($activityTargetParams);

    return $activity;
  }

  private function _completeActivity($activity) {
    Civi::log()->debug(__CLASS__.'::'.__METHOD__ . ' Updating the activity to set it completed');

    $activityParams = [
      'id' => $activity['id'],
      'status_id' => CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_status_id', 'Completed'),
      'return' => 'id'
    ];
    civicrm_api3('Activity', 'create', $activityParams);
  }
}
