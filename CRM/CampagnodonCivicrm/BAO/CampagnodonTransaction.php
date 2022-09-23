<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_BAO_CampagnodonTransaction extends CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction {

  /**
   * Create a new CampagnodonTransaction based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction|NULL
   *
  public static function create($params) {
    $className = 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction';
    $entityName = 'CampagnodonTransaction';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

  /**
   * Whitelist of possible values for the status field
   *
   * @return array
   */
  public static function statusTables() {
    return [
      'init' => 'Init',
      'pending' => 'Pending',
      'completed' => 'Completed',
      'double_membership' => 'Double membership',
      'cancelled' => 'Cancelled',
      'failed' => 'Failed',
      'refunded' => 'Refunded'
      // TODO: other status?
    ];
  }

  /**
   * Possible values for the recurring_status field.
   * @return array
   */
  public static function recurringStatusTables() {
    return [
      'init' => 'Init',
      'waiting' => 'Waiting',
      'ongoing' => 'Ongoing',
      'ended' => 'Ended'
    ];
  }

  /**
   * Return true if the status is a «not paid status».
   */
  public static function isStatusNotPaid($status) {
    return $status === 'init' || $status === 'pending' || $status === 'failed';
  }

  /**
   * Possible values for opt-in fields
   *
   * @return array
   */
  public static function optInTables() {
    return [
      'do_not_trade' => 'do_not_trade'
    ];
  }

  /**
   * Test if an opt-in field is valid
   *
   * @return Boolean
   */
  public static function isOptInValid($v) {
    return array_key_exists($v, CRM_CampagnodonCivicrm_BAO_CampagnodonTransaction::optInTables());
  }
}
