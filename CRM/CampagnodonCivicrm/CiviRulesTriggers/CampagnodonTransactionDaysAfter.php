<?php

class CRM_CampagnodonCivicrm_CiviRulesTriggers_CampagnodonTransactionDaysAfter extends CRM_Civirules_Trigger_Cron {

  private $dao = null;

  /**
   * Returns a redirect url to extra data input from the user after adding a condition
   *
   * Return false if you do not need extra data input
   *
   * @param int $ruleId
   * @return bool|string
   */
  public function getExtraDataInputUrl($ruleId) {
    return CRM_Utils_System::url('civicrm/campagnodon/civirule/form/trigger/campagnodontransactiondaysafter', "rule_id={$ruleId}");
  }

  /**
   * This function returns a CRM_Civirules_TriggerData_TriggerData this entity is used for triggering the rule
   *
   * Return false when no next entity is available
   *
   * @return CRM_Civirules_TriggerData_TriggerData|false
   */
  protected function getNextEntityTriggerData() {
    if (!$this->dao) {
      if (!$this->queryForTriggerEntities()) {
        return false;
      }
    }
    if ($this->dao->fetch()) {
      Civi::log()->debug(__METHOD__.' fetched the transaction id='.$this->dao->id);
      $data = array();
      CRM_Core_DAO::storeValues($this->dao, $data);
      $triggerData = new CRM_Civirules_TriggerData_Cron($this->dao->contact_id, 'CampagnodonTransaction', $data, $this->dao->id);

      // Note: Were are using a Cron Civirule trigger, to avoid permissions issue (the api uses a user with restricted rights)
      // But doing so, if civirules' conditions are not met, this trigger will execute everytime.
      // So we are using a special table to store the fact that we already triggered on this entity.
      $log = \Civi\Api4\CampagnodonCivirulesLog::create()
        ->addValue('entity_table', 'civicrm_campagnodon_transaction')
        ->addValue('entity_id', $this->dao->id)
        ->addValue('trigger_name', 'daysafter')
        ->addValue('rule_id', $this->ruleId)
        ->execute();

      return $triggerData;
    }
    return false;
  }

  /**
   * Method to query trigger entities
   *
   * @access private
   */
  private function queryForTriggerEntities() {
    $days = $this->triggerParams['days'];
    if (empty($days) && $days !== '0') {
      return false;
    }

    Civi::log()->debug(__METHOD__.' Constructing sql request for rule id='.$this->ruleId.' with days='.$days);

    $sql = "SELECT t.*
            FROM `civicrm_campagnodon_transaction` AS `t`
            LEFT JOIN `civicrm_campagnodon_civirules_log` AS `rule_log`
              ON `rule_log`.`rule_id` = %1
                  AND `rule_log`.`entity_table` = 'civicrm_campagnodon_transaction'
                  AND `rule_log`.`entity_id` = t.id
                  AND  `rule_log`.`trigger_name` = 'daysafter'
            WHERE DATE(`t`.`start_date`) = DATE_SUB(CURRENT_DATE(), INTERVAL %2 DAY)
              AND `rule_log`.`id` IS NULL
    ";
    $params = [];
    $params[1] = [$this->ruleId, 'Integer'];
    $params[2] = [$days, 'Integer'];

    $this->dao = CRM_Core_DAO::executeQuery($sql, $params, true, 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction');
    return true;
  }

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition(
      'Campagnodon transaction',
      'CampagnodonTransaction',
      'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
      'CampagnodonTransaction'
    );
  }

  public function setTriggerParams($triggerParams) {
    $this->triggerParams = unserialize($triggerParams);
  }
}
