<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Campagnodon.Convert API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_campagnodon_Convert_spec(&$spec) {
  $spec["campagnodon_version"] = [
    "name" => "prefix",
    "title" => ts("Campagnodon API version"),
    "description" => ts("Campagnodon API version"),
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 1,
    "api.default" => "",
  ];
  $spec["transaction_idx"] = [
    "name" => "transaction_idx",
    "title" => ts("External identifier"),
    "description" => "Unique identifier",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 1,
    "api.default" => "",
  ];
  $spec["operation_type"] = [
    "name" => "operation_type",
    "title" => ts("Operation Type"),
    "description" => "Operation Type",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 1,
    "api.default" => "",
  ];
  $spec["convert_financial_type"] = [
    "title" => ts("Convert financial types"),
    "description" => "Financial type ids (or names) to convert. Array where keys are old value et value are the new ones.",
    "api.required" => 0,
  ];
  // TODO:
  // $spec["cancel_optional_subscription"] = [
  //   "title" => ts("Cancel optional subscriptions"),
  //   "description" => "Optional subscriptions to cancel. Array of optional subscriptions names.",
  //   "api.required" => 0,
  // ];
}

/**
 * Campagnodon.Convert API
 * 
 * Converts a transaction from one type to another.
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
function civicrm_api3_campagnodon_Convert($params) {
  $tx = new CRM_Core_Transaction();
  $transaction = null;
  try {
    // checking API version
    if ($params['campagnodon_version'] !== '1') {
      throw new API_Exception("Unkwnown API version '".($params['campagnodon_version'] ?? '')."'");
    }

    $transaction = \Civi\Api4\CampagnodonTransaction::get()
      ->setCheckPermissions(false)
      ->addWhere('idx', '=', $params['transaction_idx'])
      ->execute()
      ->single();
    if (!$transaction) {
      throw new Exception('Transaction '.$params['transaction_idx'].' not found.');
    }

    if ($transaction['status'] === 'completed') {
      throw new Exception('Transaction '.$params['transaction_idx'].' is already in completed status.');
    }

    if ($transaction['operation_type'] === $params['operation_type']) {
      throw new Exception('Transaction '.$params['transaction_idx'].' has already the correct operation_type.');
    }

    $convert_logic = new CRM_CampagnodonCivicrm_Logic_Convert($params);

    // First, we convert all financial_type to financial_type_id...
    $convert_logic->convertTransactionFinancialType($transaction);

    // Last, update the transaction.
    $convert_logic->convertTransactionOperationType($transaction);

  } catch (Throwable $e) {
    $tx->rollback();
    throw $e;
  }
  $tx->commit();

  $transaction = \Civi\Api4\CampagnodonTransaction::get()
    ->setCheckPermissions(false)
    ->addWhere('id', '=', $transaction['id'])
    ->execute()
    ->single();
  return civicrm_api3_create_success(array($transaction['id'] => array(
    'id' => $transaction['id'],
    'operation_type' => $transaction['operation_type'],
    'status' => $transaction['status']
  )), $params);
}
