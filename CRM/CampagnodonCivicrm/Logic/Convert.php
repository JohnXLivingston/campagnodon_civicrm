<?php

use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_Logic_Convert {
  protected $params = null;
  protected $financial_type_map = [];

  /**
   * CRM_CampagnodonCivicrm_Logic_Convert constructor.
   *
   * @param $params The params received by the convert API.
   */
  public function __construct($params) {
    $this->params = $params;
    $this->_readConvertFinancialType();
  }

  public static function normalizeFinancialTypeId($p) {
    if (is_numeric($p)) {
      return strval($p);
    }
    $financial_type = \Civi\Api4\FinancialType::get()
      ->setCheckPermissions(false)
      ->addWhere('name', '=', $p)
      ->execute()->single();
    return strval($financial_type['id']);
  }

  public static function normalizeMembershipTypeId($p) {
    if (empty($p)) {
      return null;
    }
    if (is_numeric($p)) {
      return strval($p);
    }
    $membership = \Civi\Api4\MembershipType::get()
      ->setCheckPermissions(false)
      ->addWhere('name', '=', $p)
      ->execute()->single();
    return strval($membership['id']);
  }

  protected function _readConvertFinancialType() {
    $this->financial_type_map = [];
    if (
      !array_key_exists('convert_financial_type', $this->params)
      || !is_array($this->params['convert_financial_type'])
    ) {
      return;
    }

    foreach ($this->params['convert_financial_type'] as $old_financial_type_id_p => $convert_financial_type) {
      $old_financial_type_id = $this->normalizeFinancialTypeId($old_financial_type_id_p);
      $new_financial_type_id = $this->normalizeFinancialTypeId($convert_financial_type['new_financial_type']);
      $new_membership_id = $this->normalizeMembershipTypeId(array_key_exists('membership', $convert_financial_type) ? $convert_financial_type['membership'] : null);

      $this->financial_type_map[$old_financial_type_id] = [
        'new_financial_type_id' => $new_financial_type_id,
        'membership_id' => $new_membership_id
      ];
    }
  }

  public function getConvertFinancialTypeMap() {
    return $this->financial_type_map;
  }

  public function convertTransactionFinancialType($transaction) {
    // TODO: add some unit tests.
    $financial_type_map = $this->getConvertFinancialTypeMap();
    if (empty($financial_type_map)) {
      return;
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

  public function convertTransactionOperationType($transaction) {
    // TODO: add some unit tests.
    $update_transaction = \Civi\Api4\CampagnodonTransaction::update()
      ->setCheckPermissions(false)
      ->addWhere('id', '=', $transaction['id'])
      ->addValue('operation_type', $this->params['operation_type']);
    // We have to change the status if double_membership (this is a special case)
    if ($transaction['status'] === 'double_membership') {
      $update_transaction->addValue('status', 'init');
      // TODO: add some unit test.
    }
    $update_transaction->execute();
  }
}
