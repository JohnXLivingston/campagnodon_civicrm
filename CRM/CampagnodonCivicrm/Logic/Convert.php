<?php

use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_Logic_Convert {
  protected $params = null;

  /**
   * CRM_CampagnodonCivicrm_Logic_Convert constructor.
   *
   * @param $params The params received by the convert API.
   */
  public function __construct($params) {
    $this->params = $params;
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

  public function getConvertFinancialTypeMap() {
    $financial_type_map = [];
    if (
      !array_key_exists('convert_financial_type', $this->params)
      || !is_array($this->params['convert_financial_type'])
    ) {
      return [];
    }

    foreach ($this->params['convert_financial_type'] as $old_financial_type_id_p => $convert_financial_type) {
      $old_financial_type_id = $this::normalizeFinancialTypeId($old_financial_type_id_p);
      $new_financial_type_id = $this::normalizeFinancialTypeId($convert_financial_type['new_financial_type']);
      $new_membership_p = array_key_exists('membership', $convert_financial_type) ? $convert_financial_type['membership'] : null;
      $new_membership_id = $this::normalizeMembershipTypeId($new_membership_p);

      $financial_type_map[$old_financial_type_id] = [
        'new_financial_type_id' => $new_financial_type_id,
        'membership_id' => $new_membership_id
      ];
    }
    return $financial_type_map;
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
      if ($contribution_link['cancelled']) {
        continue;
      }
      $current_financial_type_id = strval($contribution_link['financial_type_id']);
      if (!array_key_exists($current_financial_type_id, $financial_type_map)) {
        continue;
      }

      $current_map = $financial_type_map[$current_financial_type_id];
      
      $new_financial_type_id = $current_map['new_financial_type_id'];
      if (!empty($contribution_link['entity_id'])) {
        // Updating the contribution...
        throw new Exception('voila');
        \Civi\Api4\Contribution::update()
          ->setCheckPermissions(false)
          ->addWhere('id', '=', $contribution_link['entity_id'])
          ->addValue('financial_type_id', $new_financial_type_id)
          ->execute();
      }

      $new_membership_type_id = $current_map['membership_id'] ?? null;
      $membership_link = \Civi\Api4\CampagnodonTransactionLink::get()
        ->setCheckPermissions(false)
        ->addWhere('campagnodon_tid', '=', $transaction['id'])
        ->addWhere('parent_id', '=', $contribution_link['id'])
        ->addWhere('entity_table', '=', 'civicrm_membership')
        ->execute()->first();

      if (!empty($new_membership_id)) {
        // TODO
        throw new Exception('Not implemented yet (adding a membership on a conversion)');
        // if (!empty($membership_link)) {
        //   \Civi\Api4\CampagnodonTransactionLink::update()
        //     ->setCheckPermissions(false)
        //     ->addWhere('id', '=', $membership_link['id'])
        //     ->addValue('membership_type_id', $new_membership_type_id)
        //     ->execute();
        //   // FIXME: must also change some opt-in !
        // } else {
        //   // TODO: create the membership.
        // }
      } else {
        if ($membership_link) {
          // We must remove the membership.
          // To do so, we just cancel it.
          // TODO: add some unit test.
          if (empty($membership_link['cancelled'])) {
            \Civi\Api4\CampagnodonTransactionLink::update()
              ->setCheckPermissions(false)
              ->addWhere('id', '=', $membership_link['id'])
              ->addValue('cancelled', 'converted')
              ->execute();
          }
        }
      }

      \Civi\Api4\CampagnodonTransactionLink::update()
        ->setCheckPermissions(false)
        ->addWhere('id', '=', $contribution_link['id'])
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
