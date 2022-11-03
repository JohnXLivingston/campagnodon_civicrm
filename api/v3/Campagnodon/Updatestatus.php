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
  $spec["recurring_status"] = [
    "name" => "recurring_status",
    "title" => ts("Recurring Status"),
    "description" => "Recurring status",
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
    Civi::log()->debug(__METHOD__.' Entering in Updatestatus API');
    $transaction = \Civi\Api4\CampagnodonTransaction::get()
      ->setCheckPermissions(false)
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

    if (!preg_match('/^\w+$/', $status)) {
      throw new Exception('Invalid status "'.$status.'".');
    }

    if ($status === 'completed' && $transaction['status'] != 'completed') {
      // We must first search double membership.
      // If found, we must go to a special state.
      // Note: must be done before computing $contribution_status
      // TODO: add some unit tests.
      if (CRM_CampagnodonCivicrm_Logic_Contact::searchDoubleMembership($transaction['contact_id'], $transaction['id'])) {
        $status = 'double_membership';
      }
    }

    Civi::log()->debug(__METHOD__.' the status to set is: '.$status.'. Current transaction status is: '.$transaction['status']);

    $contribution_status = Civi::settings()->get('campagnodon_contribution_status_'.($status === 'init' ? 'pending' : $status));
    if (!$contribution_status) {
      throw new Exception('Cant find contribution_status for "'.$status.'".');
    }
    $contribution_status = intval($contribution_status); // just in case...

    $transaction_has_update = false;
    $transaction_update = \Civi\Api4\CampagnodonTransaction::update()
      ->setCheckPermissions(false)
      ->addWhere('id', '=', $transaction['id']);
    if ($transaction['status'] !== $status) {
      $transaction_update->addValue('status', $status);
      $transaction_has_update = true;
    }
    if (array_key_exists('recurring_status', $params) && $params['recurring_status']) {
      // Civi::log()->debug(__METHOD__.' The API call contains a recurring_status: ' . $params['recurring_status'];
      if ($params['recurring_status'] !== $transaction['recurring_status']) {
        $transaction_update->addValue('recurring_status', $params['recurring_status']);
        $transaction_has_update = true;
      }
    }
    if (!empty($payment_field)) { // FIXME: avoid update when value does not change (difficult because of the pseudoConstant)
      $transaction_update->addValue($payment_field, $payment_type);
      $transaction_has_update = true;
    }
    if ($transaction_has_update) {
      Civi::log()->debug(__METHOD__.' updating the transaction...');
      $transaction_update->execute();
    }

    // Updating existing contributions
    $contributions = \Civi\Api4\Contribution::get()
      ->setCheckPermissions(false)
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
        ->setCheckPermissions(false)
        ->addWhere('id', '=', $contribution['id']);
      if ($contribution['contribution_status_id'] != $contribution_status) {
        $contribution_update->addValue('contribution_status_id', $contribution_status);
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

    // Creating missing contributions if this is a final states and there are not yet created.
    if ($status !== 'pending') {
      $tax_receipt_field = Civi::settings()->get('campagnodon_contribution_tax_receipt_field');

      $missing_contribution_links = \Civi\Api4\CampagnodonTransactionLink::get()
        ->setCheckPermissions(false)
        ->addWhere('campagnodon_tid', '=', $transaction['id'])
        ->addWhere('entity_table', '=', 'civicrm_contribution')
        ->addWhere('entity_id', 'IS NULL')
        ->execute();
      foreach ($missing_contribution_links as $missing_contribution_link) {
        $contribution = \Civi\Api4\Contribution::create()
          ->setCheckPermissions(false)
          ->addValue('contact_id', $transaction['contact_id'])
          ->addValue('contribution_status_id', $contribution_status)
          ->addValue('total_amount', $missing_contribution_link['total_amount'])
          ->addValue('currency', $missing_contribution_link['currency'])
          ->addValue('financial_type_id', $missing_contribution_link['financial_type_id']);
        if (!empty($transaction['campaign_id'])) {
          $contribution->addValue('campaign_id', $transaction['campaign_id']);
        }
        if (!empty($transaction['source'])) {
          $contribution->addValue('source', $transaction['source']); // TODO: add some unit tests.
        }
        if (!empty($payment_field)) {
          $contribution->addValue($payment_field, $payment_type);
        }
        if (!empty($tax_receipt_field)) {
          $contribution->addValue($tax_receipt_field, $transaction['tax_receipt'] ? '1' : '0'); // TODO: add some tests
        }
        // FIXME: following fields?
        // 'receive_date'
        $contribution = $contribution->execute()->single();

        \Civi\Api4\CampagnodonTransactionLink::update()
          ->setCheckPermissions(false)
          ->addValue('entity_id', $contribution['id'])
          ->addWhere('id', '=', $missing_contribution_link['id'])
          ->execute();
      }
    }

    // Processing links (to deal with stuff like: optional_subscriptions with when=completed, ...)
    CRM_CampagnodonCivicrm_Logic_Contact::processLinks($transaction['contact_id'], $transaction['id'], $status);

    if ($status === 'completed') {
      // TODO: add some unit tests
      CRM_CampagnodonCivicrm_Logic_Contact::mergeIntoContact($transaction['id']);
    }
  } catch (Throwable $e) {
    Civi::log()->debug(__METHOD__.' rollbacking...');
    $tx->rollback();
    throw $e;
  }

  Civi::log()->debug(__METHOD__.' commiting...');
  $tx->commit();

  // Then we get the statut from database, to be sure we have the correct one.
  $transaction = \Civi\Api4\CampagnodonTransaction::get()
    ->setCheckPermissions(false)
    ->addWhere('idx', '=', $params['transaction_idx'])
    ->execute()
    ->single();
  if (!$transaction) {
    throw new Exception('Transaction '.$params['transaction_idx'].' not found.');
  }

  return civicrm_api3_create_success(array($transaction['id'] => array(
    'id' => $transaction['id'],
    'status' => $transaction['status'],
    'recurring_status' => $transaction['recurring_status']
  )), $params);
}
