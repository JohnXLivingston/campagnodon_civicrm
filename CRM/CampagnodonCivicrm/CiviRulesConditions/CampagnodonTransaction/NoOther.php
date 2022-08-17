<?php

class CRM_CampagnodonCivicrm_CiviRulesConditions_CampagnodonTransaction_NoOther extends CRM_Civirules_Condition {
  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleConditionId
   * @return bool|string
   * @access public
   * @abstract
   */
  public function getExtraDataInputUrl($ruleConditionId) {
    return CRM_Utils_System::url('civicrm/campagnodon/civirule/form/condition/campagnodontransactionnoother', "rule_condition_id={$ruleConditionId}");
  }

  /**
   * Method to set the Rule Condition data
   *
   * @param array $ruleCondition
   * @access public
   */
  public function setRuleConditionData($ruleCondition) {
    parent::setRuleConditionData($ruleCondition);
    $this->conditionParams = array();
    if (!empty($this->ruleCondition['condition_params'])) {
      $this->conditionParams = unserialize($this->ruleCondition['condition_params']);
    }
  }

  /**
   * Method is mandatory and checks if the condition is met
   *
   * @param CRM_Civirules_TriggerData_TriggerData $triggerData
   * @return bool
   * @access public
   */
  public function isConditionValid(CRM_Civirules_TriggerData_TriggerData $triggerData)
  {
    Civi::log()->debug(__METHOD__.' We must test the CampagnodonTransaction_NoOther condition');
    $triggerCampagnodonTransaction = $triggerData->getEntityData('CampagnodonTransaction');
    if (!$triggerCampagnodonTransaction) {
      Civi::log()->error(__METHOD__.' There is no CampagnodonTransaction');
      // Dont know if it can happen...
      return FALSE;
    }

    Civi::log()->debug(__METHOD__.' Searching for other transaction for contact_id='.$triggerCampagnodonTransaction['contact_id']);
    $transactions_get = \Civi\Api4\CampagnodonTransaction::get()
      ->setCheckPermissions(false)
      ->selectRowCount()
      ->addWhere('id', '!=', $triggerCampagnodonTransaction['id'])
      ->addWhere('contact_id', '=', $triggerCampagnodonTransaction['contact_id']);

    $statuses = $this->conditionParams['status_id'];
    if (!empty($statuses)) {
      $nop = '';
      if ($this->conditionParams['status_operator'] == 1) {
        $nop = 'NOT ';
      }
      Civi::log()->debug(__METHOD__.' must test status '.$nop.' IN : '.print_r($statuses, true));
      $transactions_get->addWhere('status', $nop.'IN', $statuses);
    }

    $operation_types = $this->conditionParams['operation_type'] ?? '';
    $operation_types = explode(',', $operation_types);
    $operation_types_clean = array();
    foreach ($operation_types as $operation_type) {
      $operation_type = trim($operation_type);
      if (empty($operation_type)) {
        continue;
      }
      $operation_types_clean[] = $operation_type;
    }
    $operation_types = $operation_types_clean;
    if (!empty($operation_types)) {
      $nop = '';
      if ($this->conditionParams['operation_type_operator'] == 1) {
        $nop = 'NOT ';
      }
      Civi::log()->debug(__METHOD__.' must test operation_type '.$nop.' IN : '.print_r($operation_types, true));
      $transactions_get->addWhere('operation_type', $nop.'IN', $operation_types);
    }

    $transactions_get->execute();

    // With CiviCRM 5.50+, there is a new countMatched method. Using it when available.
    $count = method_exists($transactions_get, 'countMatched') ? $transactions_get->countMatched() : $transactions_get->count();
    Civi::log()->debug(__METHOD__.' Found '.$count.' other transactions.');

    return $count === 0;
  }

  /**
   * This function validates whether this condition works with the selected trigger.
   *
   * This function could be overriden in child classes to provide additional validation
   * whether a condition is possible in the current setup. E.g. we could have a condition
   * which works on contribution or on contributionRecur then this function could do
   * this kind of validation and return false/true
   *
   * @param CRM_Civirules_Trigger $trigger
   * @param CRM_Civirules_BAO_Rule $rule
   * @return bool
   */
  public function doesWorkWithTrigger(CRM_Civirules_Trigger $trigger, CRM_Civirules_BAO_Rule $rule) {
    return $trigger->doesProvideEntity('CampagnodonTransaction');
  }

}
