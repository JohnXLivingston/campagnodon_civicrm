<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Campagnodon.Dsp2info API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_campagnodon_Dsp2info_spec(&$spec) {
  $spec["transaction_idx"] = [
    "name" => "transaction_idx",
    "title" => ts("External identifier"),
    "description" => "Unique identifier",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 1,
    "api.default" => "",
  ];
}

/**
 * Campagnodon.Dsp2info API
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
function civicrm_api3_campagnodon_Dsp2info($params) {
  $transaction = \Civi\Api4\CampagnodonTransaction::get()
    ->setCheckPermissions(false)
    ->addSelect('*', 'country_id:name')
    ->addWhere('idx', '=', $params['transaction_idx'])
    ->execute()
    ->single();
  if (!$transaction) {
    throw new Exception('Transaction '.$params['transaction_idx'].' not found.');
  }

  if ($transaction['status'] !== 'init' && $transaction['status'] !== 'pending') {
    throw new Exception('Transaction '.$params['transaction_idx'].' is no more pending, can\'t get informations.');
  }

  return civicrm_api3_create_success(array(
    array(
      'id' => $transaction['id'],
      'last_name' => $transaction['last_name'],
      'first_name' => $transaction['first_name'],
      'email' => $transaction['email'],
      'street_address' => $transaction['street_address'],
      'phone' => $transaction['phone'],
      'supplemental_address_1' => $transaction['supplemental_address_1'],
      'supplemental_address_2' => $transaction['supplemental_address_2'],
      'postal_code' => $transaction['postal_code'],
      'city' => $transaction['city'],
      'country' => $transaction['country_id:name']
    )
  ), $params);
}
