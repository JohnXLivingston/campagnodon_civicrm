<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_BAO_CampagnodonCivirulesLog extends CRM_CampagnodonCivicrm_DAO_CampagnodonCivirulesLog {

  /**
   * Create a new CampagnodonCivirulesLog based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_CampagnodonCivicrm_DAO_CampagnodonCivirulesLog|NULL
   *
  public static function create($params) {
    $className = 'CRM_CampagnodonCivicrm_DAO_CampagnodonCivirulesLog';
    $entityName = 'CampagnodonCivirulesLog';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

}
