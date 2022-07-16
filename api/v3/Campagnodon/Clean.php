<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Campagnodon.Clean API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_campagnodon_Clean_spec(&$spec) {
}

/**
 * Campagnodon.Clean API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_campagnodon_Clean($params) {
  $nb_days = Civi::settings()->get('campagnodon_clean_nb_days');
  if (empty($nb_days)) {
    return civicrm_api3_create_success(array(), $params);
  }
  if (!preg_match('/^\d+$/', $nb_days)) {
    throw new Exception('Invalid settings campagnodon_clean_nb_days');
  }

  $today = new DateTime();
  $period = new DateInterval('P'.strval($nb_days).'D');
  $date = $today->sub($period)->format('Y-m-d');

  \Civi\Api4\CampagnodonTransaction::update()
    ->setCheckPermissions(false)
    ->addWhere('cleaned', '=', 'false')
    ->addWhere('start_date', '<', $date)
    ->addValue('email', null)
    ->addValue('prefix', null)
    ->addValue('first_name', null)
    ->addValue('last_name', null)
    ->addValue('street_address', null)
    ->addValue('postal_code', null)
    ->addValue('country_id', null)
    ->addValue('phone', null)
    ->addValue('cleaned', true)
    ->execute();
  return civicrm_api3_create_success(array(), $params);
}
