<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Campagnodon.Migratecontribution API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_campagnodon_Migratecontribution_spec(&$spec) {
  $spec["transaction_idx"] = [
    "name" => "transaction_idx",
    "title" => ts("External identifier"),
    "description" => "Unique identifier",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 1,
    "api.default" => ""
  ];
  $spec["parent_transaction_idx"] = [
    "name" => "parent_transaction_idx",
    "title" => ts("Parent's External identifier"),
    "description" => "Unique identifier",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 0,
    "api.default" => ""
  ];
  $spec["payment_url"] = [
    "name" => "payment_url",
    "title" => ts("Payment Url"),
    "description" => "Payment Url",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 0,
    "api.default" => "",
  ];
  $spec["transaction_url"] = [
    "name" => "transaction_url",
    "title" => ts("Original transaction url"),
    "description" => "Original transaction url",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 0,
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
}

/**
 * Campagnodon.Migratecontribution API
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
function civicrm_api3_campagnodon_Migratecontribution($params) {
  $parent_transaction = null;
  if ($params['parent_transaction_idx']) {
    $parent_transaction = \Civi\Api4\CampagnodonTransaction::get()
      ->setCheckPermissions(false)
      ->addWhere('idx', '=', $params['parent_transaction_idx'])
      ->execute()
      ->single(); // fail if not found
  }

  $tx = new CRM_Core_Transaction();
  try {
    $contribution = \Civi\Api4\Contribution::get()
      ->setCheckPermissions(false)
      ->addWhere('trxn_id', '=', $params['transaction_idx'])
      ->execute()
      ->single(); // fail if not found.
    
    $transaction_create = \Civi\Api4\CampagnodonTransaction::create();
    $transaction_create->setCheckPermissions(false);
    if ($parent_transaction) {
      $transaction_create->addValue('parent_id', $parent_transaction['id']);
    }
    $transaction_create->addValue('idx', $params['transaction_idx']);

    $transaction_create->addValue('contact_id', $contribution['contact_id']);
    $transaction_create->addValue('original_contact_id', $contribution['contact_id']);
    $transaction_create->addValue('new_contact', false);
    $transaction_create->addValue('source', $contribution['source']);
    $transaction_create->addValue('campaign_id', $contribution['campaign_id']);

    $transaction_create->addValue('operation_type', $params['operation_type']);

    // FIXME: how to copy tax_receipt???

    foreach (
      array(
        'payment_url', 'transaction_url'
      ) as $field
    ) {
      if (array_key_exists($field, $params) && !empty($params[$field])) {
        $transaction_create->addValue($field, $params[$field]);
      }
    }

    $transaction_create->addValue('cleaned', true); // we are not copying private data, so we can flag as cleaned.

    $transaction_result = $transaction_create->execute();
    $transaction = $transaction_result->single();

    $link = \Civi\Api4\CampagnodonTransactionLink::create()
      ->setCheckPermissions(false)
      ->addValue('campagnodon_tid', $transaction['id'])
      ->addValue('entity_table', 'civicrm_contribution')
      ->addValue('entity_id', $contribution['id'])
      ->addValue('total_amount', $contribution['total_amount'])
      ->addValue('currency', $contribution['currency'])
      ->addValue('financial_type_id', $contribution['financial_type_id'])
      ->execute()
      ->single();
  } catch (Throwable $e) {
    $tx->rollback();
    throw $e;
  }
  $tx->commit();

  return civicrm_api3_create_success([], $params);
}
