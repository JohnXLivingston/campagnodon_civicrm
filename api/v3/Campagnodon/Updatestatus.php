<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Campagnodon.Updatestatus API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_campagnodon_Updatestatus_spec(&$spec) {
  $spec["transaction_idx"] = [
    "name" => "transaction_idx",
    "title" => ts("External identifier"),
    "description" => "Unique identifier",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 1,
    "api.default" => "",
  ];
  $spec["status"] = [
    "name" => "status",
    "title" => ts("Status"),
    "description" => "Payment status",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 1,
    "api.default" => "",
  ];
  $spec["payment_instrument"] = [
    "name" => "payment_instrument",
    "title" => ts("Payment Method ID"),
    "description" => "Payment status",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 0,
    "api.default" => "",
  ];
}

/**
 * Campagnodon.Updatestatus API
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
function civicrm_api3_campagnodon_Updatestatus($params) {
  $tx = new CRM_Core_Transaction();
  try {
    $transaction = \Civi\Api4\CampagnodonTransaction::get()
      ->addWhere('idx', '=', $params['transaction_idx'])
      ->execute()
      ->single();
    if (!$transaction) {
      throw new Exception('Transaction '.$params['transaction_idx'].' not found.');
    }

    $status = $params['status'];
    $payment_type = $params['payment_instrument'];
    $payment_field = null;
    if (!empty($payment_type)) {
      $payment_field = 'payment_instrument_id';
      if (!is_numeric($payment_type)) {
        $payment_field.= ':name';
      }
    }

    $contribution_status = $params['contribution_status'];
    $contribution_status_field = null;
    if (!empty($contribution_status)) {
      $contribution_status_field = 'contribution_status_id';
      if (!is_numeric($contribution_status)) {
        $contribution_status_field.= ':name';
      }
    }

    $transaction_has_update = false;
    $transaction_update = \Civi\Api4\CampagnodonTransaction::update()
      ->addWhere('id', '=', $transaction['id']);
    if ($transaction['status'] !== $status) {
      $transaction_update->addValue('status', $status);
      $transaction_has_update = true;
    }
    if (!empty($payment_field)) { // FIXME: avoid update when value does not change (difficult because of the pseudoConstant)
      $transaction_update->addValue($payment_field, $payment_type);
      $transaction_has_update = true;
    }
    if ($transaction_has_update) {
      $transaction_update->execute();
    }

    // Also updating contributions
    $contributions = \Civi\Api4\Contribution::get()
      ->addSelect('*', 'financial_type_id:name')
      ->addJoin(
        'CampagnodonTransactionLink AS tlink',
        'INNER', null,
        ['tlink.entity_table', '=', '"civicrm_contribution"'],
        ['tlink.entity_id', '=', 'id']
      )
      ->addWhere('tlink.campagnodon_tid', '=', $transaction['id'])
      ->execute();
    foreach ($contributions as $contribution) {
      $contribution_has_update = false;
      $contribution_update = \Civi\Api4\Contribution::update()
        ->addWhere('id', '=', $contribution['id']);
      if (!empty($contribution_status_field)) { // FIXME: avoid update when value does not change (difficult because of the pseudoConstant)
        $contribution_update->addValue($contribution_status_field, $contribution_status);
        $contribution_has_update = true;
      }
      if (!empty($payment_field)) { // FIXME: avoid update when value does not change (difficult because of the pseudoConstant)
        $contribution_update->addValue($payment_field, $payment_type);
        $contribution_has_update = true;
      }
      if ($contribution_has_update) {
        $contribution_update->execute();
      }
    }
  } catch (Exception $e) {
    $tx->rollback();
    throw $e;
  }
  $tx->commit();

  return civicrm_api3_create_success(array($transaction['id'] => $transaction), $params, 'Campagnodon', 'transaction');
}
