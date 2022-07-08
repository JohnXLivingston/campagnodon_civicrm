<?php

use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_Logic_Contact {
  /**
   * @param $group_id
   * @param $contact_id
   */
  public static function addInGroup($group_id, $contact_id) {
    $group_contact = \Civi\Api4\GroupContact::get()
      ->addWhere('group_id', '=', $group_id)
      ->addWhere('contact_id', '=', $contact_id)
      ->execute()
      ->first();
    
    if (!$group_contact) {
      \Civi\Api4\GroupContact::create()
        ->addValue('group_id', $group_id)
        ->addValue('contact_id', $contact_id)
        ->addValue('status', 'Added')
        ->execute()
        ->single();
      return;
    }

    if ($group_contact['status'] !== 'Added') {
      \Civi\Api4\GroupContact::update()
        ->addWhere('id', '=', $group_contact['id'])
        ->addValue('status', 'Added')
        ->execute();
      return;
    }
  }

  /**
   * Create the membership related to a contribution.
   * FIXME: add some unit tests.
   * @param $transaction_link_id
   * @param $contribution_id
   * @param $membership_type_id
   * @param $contact_id
   */
  public static function addMembership($transaction_link_id, $transaction_link_parent_id, $membership_type_id, $contact_id) {
    // TODO: handle cases when membership already exists.

    $membership_type = \Civi\Api4\MembershipType::get()
      ->addWhere('id', '=', $membership_type_id)
      ->execute()->single();

    $contribution = null;
    $contribution_id = null;
    if (!empty($transaction_link_parent_id)) {
      $parent = \Civi\Api4\CampagnodonTransactionLink::get()
        ->addWhere('id', '=', $transaction_link_parent_id)
        ->execute()->single();

      if ($parent['entity_table'] !== 'civicrm_contribution') {
        throw new Exception("CampagnodonTransactionLink parent is not a contribution, dont know what to do.");
      }
      $contribution_id = $parent['entity_id'];
      if (!$contribution_id) {
        throw new Exception("CampagnodonTransactionLink parent has no contribution_id, dont know what to do.");
      }
      $contribution = \Civi\Api4\Contribution::get()
        ->addWhere('id', '=', $contribution_id)
        ->execute()->single();
    }

    $period_type = $membership_type['period_type']; // 'rolling' or 'fixed'
    $receive_date = $contribution ? $contribution['receive_date'] : null;
    $start_date = null; // FIXME
    if ($period_type === 'rolling') {
      $start_date = $receive_date;
    }

    // Searching for a current membership record.
    // Note: ordering by end_date and taking last. In case there is multiple membership for this contact.
    $current_membership = \Civi\Api4\Membership::get()
      ->addSelect('*')
      ->addWhere('contact_id', '=', $contact_id)
      ->addWhere('membership_type_id', '=', $membership_type_id)
      ->addOrderBy('end_date', 'ASC')
      ->addOrderBy('id', 'ASC')
      ->execute()
      ->last();

    $cancel = null;

    // Note: following API calls are based on this code: https://code.globenet.org/attacfr/spip2CiviCRM/-/blob/master/convert.py#L1053
    if ($current_membership) {
      $membership_id = $current_membership['id'];

      if ($period_type === 'fixed') {
        // For fixed period membership, we don't renew if the current membership is still running
        $end_date = $current_membership['end_date'];
        if ($end_date >= date("Y-m-d")) {
          $cancel = 'already_member';
        }
      }

      if (!$cancel) {
        civicrm_api3('Membership', 'create', array(
          'id' => $membership_id,
          'membership_type_id' => $membership_type_id,
          'num_terms' => 1,
          'skipStatusCal' => 0,
          'campaign_id' => $contribution ? $contribution['campaign_id'] : null, // FIXME: keep this?
          'start_date' => $start_date,
          // FIXME: custom_21 field.
          'sequential' => true
        ));
      }
    } else {
      $membership = civicrm_api3('Membership', 'create', array(
        'membership_type_id' => $membership_type_id,
        'contact_id' => $contact_id,
        'campaign_id' => $contribution ? $contribution['campaign_id'] : null, // FIXME: keep this?
        'join_date' => $receive_date,
        'start_date' => $start_date,
        // FIXME: custom_21 field.
        'sequential' => true
      ));
      $membership_id = $membership['values'][0]['id'];
    }

    // FIXME: for now, the membership status is «new», and that is not correct.

    // Linking payment
    if (!$cancel && $contribution_id) {
      civicrm_api3('MembershipPayment', 'create', array(
        'membership_id' => $membership_id,
        'contribution_id' => $contribution_id
      ));
    }
    
    \Civi\Api4\CampagnodonTransactionLink::update()
      ->addValue('entity_id', $membership_id)
      ->addValue('cancelled', $cancel)
      ->addWhere('id', '=', $transaction_link_id)
      ->execute();
  }

  protected static function _testOnComplete($link, $transaction_status) {
    if ($link['on_complete'] && $transaction_status === 'completed') return true;
    if (!$link['on_complete'] && $transaction_status === 'init') return true;
    return false;
  }

  public static function processLinks($contact_id, $transaction_id, $transaction_status) {
    $links = \Civi\Api4\CampagnodonTransactionLink::get()
      ->addSelect('*')
      ->addWhere('campagnodon_tid', '=', $transaction_id)
      ->execute();
    $links->indexBy('id');
    foreach ($links as $lid => $link) {
      if ($link['entity_table'] === 'civicrm_group') {
        if (CRM_CampagnodonCivicrm_Logic_Contact::_testOnComplete($link, $transaction_status)) {
          CRM_CampagnodonCivicrm_Logic_Contact::addInGroup($link['entity_id'], $contact_id);
        }
      } else if ($link['entity_table'] === 'civicrm_contact') {
        if (!empty($link['opt_in']) && CRM_CampagnodonCivicrm_Logic_Contact::_testOnComplete($link, $transaction_status)) {
          if (CRM_CampagnodonCivicrm_BAO_CampagnodonTransaction::isOptInValid($link['opt_in'])) {
            $contact_update = \Civi\Api4\Contact::update()
              ->addWhere('id', '=', $link['entity_id'])
              ->addValue($link['opt_in'], false)
              ->execute();
          }
        }
      } else if ($link['entity_table'] === 'civicrm_membership') {
        if (
          !empty($link['membership_type_id']) // this contribution opens a membership.
          && $transaction_status === 'completed' // transaction is completed.
          && empty($link['entity_id']) // only add the membership the first time.
        ) {
          // FIXME: do something when payment is cancelled?
          CRM_CampagnodonCivicrm_Logic_Contact::addMembership($link['id'], $link['parent_id'], $link['membership_type_id'], $contact_id);
        }
      }
    }
  }
}
