<?php

class CRM_CampagnodonCivicrm_CiviRulesConditions_CampagnodonTransaction_OperationType extends CRM_Civirules_Condition {
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
    return CRM_Utils_System::url('civicrm/campagnodon/civirule/form/condition/campagnodontransactionoperationtype', "rule_condition_id={$ruleConditionId}");
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
    Civi::log()->debug(__FUNCTION__.' We must test the CampagnodonTransaction_OperationType condition');
    $triggerCampagnodonTransaction = $triggerData->getEntityData('CampagnodonTransaction');
    if (!$triggerCampagnodonTransaction) {
      Civi::log()->error(__FUNCTION__.' There is no CampagnodonTransaction');
      // Dont know if it can happen...
      return FALSE;
    }
    // Nb: for a not well-understanded reason, $triggerCampagnodonTransaction['status'] is empty...
    // I dont know if we get the same issue for operation_type. So we get the transaction from the Database to check...
    $transaction = \Civi\Api4\CampagnodonTransaction::get()
      ->setCheckPermissions(false)
      ->addSelect('*')
      ->addWhere('id', '=', $triggerCampagnodonTransaction['id'])
      ->execute()->first();
    if (empty($transaction)) {
      // Transaction deleted?
      return FALSE;
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

    Civi::log()->debug(__FUNCTION__.' here is the list of operation_types in the condition: '.print_r($operation_types, true));

    $in = in_array($transaction['operation_type'], $operation_types);
    Civi::log()->debug(__FUNCTION__.' Transation id='.$transaction['id'].', operation_type '.$transaction['operation_type'].' is in = '.($in ? 'yes' : 'no'));
    if (1 == $this->conditionParams['operator']) {
      Civi::log()->debug(__FUNCTION__.' The operator was "not one of", inverting the result');
      return !$in;
    }
    return $in;
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
