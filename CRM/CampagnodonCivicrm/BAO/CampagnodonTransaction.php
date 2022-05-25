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
  public static function statusTables(): array {
    return [
      'init' => 'Init',
      'pending' => 'Pending',
      'completed' => 'Completed',
      'cancelled' => 'Cancelled',
      'failed' => 'Failed',
      'refunded' => 'Refunded'
      // TODO: other status?
    ];
  }
}
