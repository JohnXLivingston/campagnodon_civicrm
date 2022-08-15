<?php

class CRM_CampagnodonCivicrm_CiviRulesConditions_CampagnodonTransaction_NotPaid extends CRM_Civirules_Condition {
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
    return FALSE;
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
    $triggerCampagnodonTransaction = $triggerData->getEntityData('CampagnodonTransaction');
    if (!$triggerCampagnodonTransaction) {
      // Dont know if it can happen...
      return FALSE;
    }
    // Nb: for a not well-understanded reason, $triggerCampagnodonTransaction['status'] is empty...
    // So we get the transaction from the Database to check...
    $transaction = \Civi\Api4\CampagnodonTransaction::get()
      ->setCheckPermissions(false)
      ->addSelect('*')
      ->addWhere('id', '=', $triggerCampagnodonTransaction['id'])
      ->execute()->first();
    if (empty($transaction)) {
      // Transaction deleted?
      return FALSE;
    }
    $status = $transaction['status'];
    if (CRM_CampagnodonCivicrm_BAO_CampagnodonTransaction::isStatusNotPaid($status)) {
      return TRUE;
    }
    return FALSE;
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
