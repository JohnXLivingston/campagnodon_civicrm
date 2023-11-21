<?php

use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Some utility function, shared by some API.
 */
class CRM_CampagnodonCivicrm_Logic_Transactions {
  public static function checkContributionsParams($contributions_params) {
    if (!is_array($contributions_params)) {
      throw new API_Exception('Missing contributions');
    }

    foreach ($contributions_params as $key => $contribution_params) {
      if (!is_array($contribution_params)) {
        throw new API_Exception('Invalid contributions '.$key);
      }
      $amount = intval($contribution_params['amount'] ?? 0);
      if ($amount <= 0) {
        throw new API_Exception('Invalid amount for contribution '.$key);
      }
    }
  }

  public static function createContributionsFromParams($transaction, $contributions_params) {
    foreach ($contributions_params as $key => $contribution_params) {
      $link = \Civi\Api4\CampagnodonTransactionLink::create()
        ->setCheckPermissions(false)
        ->addValue('campagnodon_tid', $transaction['id'])
        ->addValue('entity_table', 'civicrm_contribution')
        ->addValue('entity_id', null)
        ->addValue('total_amount', $contribution_params['amount'])
        ->addValue('currency', $contribution_params['currency'])
        ->addValue(
          is_numeric($contribution_params['financial_type']) ? 'financial_type_id' : 'financial_type_id:name',
          $contribution_params['financial_type']
        )
        ->execute()
        ->single();

      // if there is a membership, we also create the child link:
      if (array_key_exists('membership', $contribution_params) && !empty($contribution_params['membership'])) {
        $membership_link_create = \Civi\Api4\CampagnodonTransactionLink::create()
          ->setCheckPermissions(false)
          ->addValue('parent_id', $link['id'])
          ->addValue('campagnodon_tid', $transaction['id'])
          ->addValue('entity_table', 'civicrm_membership')
          ->addValue('entity_id', null) // will come later.
          ->addValue('total_amount', $contribution_params['amount'])
          ->addValue('currency', $contribution_params['currency'])
          ->addValue(
            is_numeric($contribution_params['financial_type']) ? 'financial_type_id' : 'financial_type_id:name',
            $contribution_params['financial_type']
          )
          ->addValue(
            is_numeric($contribution_params['membership']) ? 'membership_type_id': 'membership_type_id:name',
            $contribution_params['membership']
          )
          ->addValue(
            'keep_current_membership_if_possible',
            !!$contribution_params['keep_current_membership_if_possible']
          );

        // Special case: there can be a custom opt-in option in params... FIXME: the way this is handled is not clean.
        if (array_key_exists('membership_option', $contribution_params) && !empty($contribution_params['membership_option'])) {
          $membership_link_create->addValue('opt_in', $contribution_params['membership_option']);
        }
        $member_link = $membership_link_create->execute()->single();
      }
    }
  }
}