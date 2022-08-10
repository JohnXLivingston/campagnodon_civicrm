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
  $spec["keep_optional_subscriptions"] = [
    "title" => ts("Keep optional subscriptions"),
    "description" => "Optional subscriptions to keep, if present.",
    "api.required" => 0,
  ];
  $spec["optional_subscriptions"] = [
    "title" => ts("Optional subscriptions"),
    "description" => "Optional subscriptions to add",
    "api.required" => 0,
  ];
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

    if (array_key_exists('convert_financial_type', $params) && is_array($params['convert_financial_type'])) {
      // First, we convert all financial_type to financial_type_id...
      $financial_type_map = [];
      foreach ($params['convert_financial_type'] as $old_financial_type_id => $convert_financial_type) {
        if (!is_numeric($old_financial_type_id)) {
          $financial_type = \Civi\Api4\FinancialType::get()
            ->setCheckPermissions(false)
            ->addWhere('name', '=', $old_financial_type_id)
            ->execute()->single();
          $old_financial_type_id = $financial_type['id'];
        }
        $old_financial_type_id = strval($old_financial_type_id);

        if (is_numeric($convert_financial_type['new_financial_type'])) {
          $new_financial_type_id = $convert_financial_type['new_financial_type'];
        } else {
          $financial_type = \Civi\Api4\FinancialType::get()
            ->setCheckPermissions(false)
            ->addWhere('name', '=', $convert_financial_type['new_financial_type'])
            ->execute()->single();
          $new_financial_type_id = $financial_type['id'];
        }
        $new_financial_type_id = strval($new_financial_type_id);

        $new_membership = array_key_exists('membership', $convert_financial_type) ? $convert_financial_type['membership'] : null;
        if (!empty($new_membership)) {
          if (!is_numeric($new_membership)) {
            // TODO: add some unit tests for this case.
            $membership = \Civi\Api4\MembershipType::get()
              ->setCheckPermissions(false)
              ->addWhere('name', '=', $new_membership)
              ->execute()->single();
            $new_membership = $membership['id'];
          }
          $new_membership = strval($new_membership);
        }

        $financial_type_map[$old_financial_type_id] = [
          'new_financial_type_id' => $new_financial_type_id,
          'membership_id' => $new_membership
        ];
      }

      $contribution_links = \Civi\Api4\CampagnodonTransactionLink::get()
        ->setCheckPermissions(false)
        ->addWhere('campagnodon_tid', '=', $transaction['id'])
        ->addWhere('entity_table', '=', 'civicrm_contribution')
        ->execute();

      // we must convert some contributions...
      foreach ($contribution_links as $contribution_link) {
        $current_financial_type_id = strval($contribution_link['financial_type_id']);
        if (!array_key_exists($current_financial_type_id, $financial_type_map)) {
          continue;
        }

        $current_map = $financial_type_map[$current_financial_type_id];
        
        $new_financial_type_id = $current_map['new_financial_type_id'];
        if (!empty($contribution_link['entity_id'])) {
          // Updating the contribution...
          \Civi\Api4\Contribution::update()
            ->setCheckPermissions(false)
            ->addValue('financial_type_id', $new_financial_type_id)
            ->execute();
        }

        $new_membership_type_id = $current_map['membership_id'] ?? null;

        \Civi\Api4\CampagnodonTransactionLink::update()
          ->setCheckPermissions(false)
          ->addWhere('id', '=', $contribution_link['entity_id'])
          ->addValue('financial_type_id', $new_financial_type_id)
          ->addValue('membership_type_id', $new_membership_type_id)
          ->execute();
      }
    }

    $update_transaction = \Civi\Api4\CampagnodonTransaction::update()
      ->setCheckPermissions(false)
      ->addWhere('id', '=', $transaction['id'])
      ->addValue('operation_type', $params['operation_type']);
    // We have to change the status if double_membership (this is a special case)
    if ($transaction['status'] === 'double_membership') {
      $update_transaction->addValue('status', 'init');
      // TODO: add some unit test.
    }
    $update_transaction->execute();
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
