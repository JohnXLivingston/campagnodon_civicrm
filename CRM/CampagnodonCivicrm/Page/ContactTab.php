<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_Page_ContactTab extends CRM_Core_Page {

  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(E::ts('ContactTab'));

    $contactId = CRM_Utils_Request::retrieve('cid', 'Positive', $this, TRUE);
    $campagnodon_transactions = \Civi\Api4\CampagnodonTransaction::get()
      ->addSelect('*', 'payment_instrument_id:label', 'campaign_id:label')
      ->addWhere('contact_id', '=', $contactId)
      ->execute();
    $rows = array();
    foreach($campagnodon_transactions as $campagnodon_transaction) {
      $row = $campagnodon_transaction;
      if (!empty($row['contact_id'])) {
        $row['contact'] = '<a href="'.CRM_Utils_System::url('civicrm/contact/view', ['reset' => 1, 'cid' => $row['contact_id']]).'">'.CRM_Contact_BAO_Contact::displayName($row['contact_id']).'</a>';
      }
      $row['payment_instrument'] = $row['payment_instrument_id:label'];
      if (!empty($row['campaign_id'])) {
        // TODO: lien vers la campagne.
        $row['campaign_title'] = $row['campaign_id:label'];
      }
      $row['view'] = '<a href="'.CRM_Utils_System::url('civicrm/campagnodon/view', ['reset' => 1, 'id' => $row['id']]).'">'.($row['id']).'</a>';
      $rows[] = $row;
    }
    $this->assign('contactId', $contactId);
    $this->assign('rows', $rows);

    // Set the user context
    $session = CRM_Core_Session::singleton();
    $userContext = CRM_Utils_System::url('civicrm/contact/view', 'cid='.$contactId.'&selectedChild=contact_campagnodon&reset=1');
    $session->pushUserContext($userContext);

    parent::run();
  }
}
