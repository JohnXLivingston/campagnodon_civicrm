<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Campagnodon.Recurrence API specification
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_campagnodon_Recurrence_spec(&$spec) {
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
    "api.required" => 1,
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
    "api.required" => 0, // Not required here
    "api.default" => "",
  ];
  $spec["financial_type"] = [
    "name" => "financial_type",
    "title" => ts("Financial ID"),
    "description" => "Financial ID",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 1,
    "api.default" => "",
  ];
  $spec["currency"] = [
    "name" => "currency",
    "title" => ts("Currency"),
    "description" => "Currency",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 1,
    "api.default" => "",
  ];
  $spec["amount"] = [
    "name" => "amount",
    "title" => ts("Amount"),
    "description" => "Amount",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 1,
    "api.default" => "",
  ];
  $spec["contribution_date"] = [
    "name" => "contribution_date",
    "title" => ts("Contribution Date"),
    "description" => "Contribution date",
    "type" => CRM_Utils_Type::T_DATE,
    "api.required" => 0,
    "api.default" => "",
  ];
}

/**
 * Campagnodon.Recurrence API
 * Creates (or ensure that it is already created) a recurrent payment.
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
function civicrm_api3_campagnodon_Recurrence($params) {
  $transaction = \Civi\Api4\CampagnodonTransaction::get()
    ->setCheckPermissions(false)
    ->addWhere('idx', '=', $params['transaction_idx'])
    ->execute()
    ->first();
  
  if ($transaction) {
    // The child transaction was already created, everything is fine.
    return civicrm_api3_create_success([], $params);
  }

  $parent_transaction = \Civi\Api4\CampagnodonTransaction::get()
    ->setCheckPermissions(false)
    ->addWhere('idx', '=', $params['parent_transaction_idx'])
    ->execute()
    ->single(); // fail if not found

  $tx = new CRM_Core_Transaction();
  try {
    // NB: in case of concurrent call, this API can fail (error like «duplicate key»).
    // The calling system should handle this correctly.
    $transaction_create = \Civi\Api4\CampagnodonTransaction::create();
    $transaction_create->setCheckPermissions(false);
    $transaction_create->addValue('parent_id', $parent_transaction['id']);
    $transaction_create->addValue('idx', $params['transaction_idx']);

    $transaction_create->addValue('contact_id', $parent_transaction['contact_id']);
    $transaction_create->addValue('cleaned', true); // we are not copying private data, so we can flag as cleaned.
    
    foreach ([
      'source',
      'tax_receipt',
      'original_contact_id', 'new_contact',
      'campaign_id'
    ] as $field) {
      $transaction_create->addValue($field, $parent_transaction[$field]);
    }
    
    foreach (
      array(
        'payment_url', 'transaction_url', 'contribution_date'
      ) as $field
    ) {
      if (array_key_exists($field, $params) && !empty($params[$field])) {
        $transaction_create->addValue($field, $params[$field]);
      }
    }

    if (array_key_exists('operation_type', $params) && !empty($params['operation_type'])) {
      $transaction_create->addValue('operation_type', $params['operation_type']);
    } else {
      $transaction_create->addValue('operation_type', $parent_transaction['operation_type']);
    }

    $transaction_result = $transaction_create->execute();
    $transaction = $transaction_result->single();

    // // We now copy all parents contributions
    // $parent_contribution_links = \Civi\Api4\CampagnodonTransactionLink::get()
    //     ->setCheckPermissions(false)
    //     ->addWhere('campagnodon_tid', '=', $parent_transaction['id'])
    //     ->addWhere('entity_table', '=', 'civicrm_contribution')
    //     ->execute();
    // foreach ($parent_contribution_links as $parent_contribution_link) {
    //   $financial_type_field = 'financial_type_id';
    //   $financial_type = $parent_contribution_link['financial_type_id'];
    //   if (array_key_exists('financial_type', $params) && !empty($params['financial_type'])) {
    //     $financial_type = $params['financial_type'];
    //     $financial_type_field = is_numeric($financial_type) ? 'financial_type_id' : 'financial_type_id:name';
    //   }
    //   $link = \Civi\Api4\CampagnodonTransactionLink::create()
    //     ->setCheckPermissions(false)
    //     ->addValue('campagnodon_tid', $transaction['id'])
    //     ->addValue('entity_table', 'civicrm_contribution')
    //     ->addValue('entity_id', null)
    //     ->addValue('total_amount', $parent_contribution_link['total_amount'])
    //     ->addValue('currency', $parent_contribution_link['currency'])
    //     ->addValue($financial_type_field, $financial_type)
    //     ->execute()
    //     ->single();
    // }
    // We now copy all parents contributions
    $financial_type = $params['financial_type'];
    $financial_type_field = is_numeric($financial_type) ? 'financial_type_id' : 'financial_type_id:name';
    $link = \Civi\Api4\CampagnodonTransactionLink::create()
      ->setCheckPermissions(false)
      ->addValue('campagnodon_tid', $transaction['id'])
      ->addValue('entity_table', 'civicrm_contribution')
      ->addValue('entity_id', null)
      ->addValue('total_amount', $params['amount'])
      ->addValue('currency', $params['currency'])
      ->addValue($financial_type_field, $financial_type)
      ->execute()
      ->single();
  } catch (Throwable $e) {
    $tx->rollback();
    throw $e;
  }
  $tx->commit();

  return civicrm_api3_create_success([], $params);
}
