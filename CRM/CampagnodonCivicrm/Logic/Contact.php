<?php

use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_Logic_Contact {
  /**
   * @param $group_id
   * @param $contact_id
   */
  public static function addInGroup($group_id, $contact_id) {
    $group_contact = \Civi\Api4\GroupContact::get()
      ->setCheckPermissions(false)
      ->addWhere('group_id', '=', $group_id)
      ->addWhere('contact_id', '=', $contact_id)
      ->execute()
      ->first();
    
    if (!$group_contact) {
      \Civi\Api4\GroupContact::create()
        ->setCheckPermissions(false)
        ->addValue('group_id', $group_id)
        ->addValue('contact_id', $contact_id)
        ->addValue('status', 'Added')
        ->execute()
        ->single();
      return;
    }

    if ($group_contact['status'] !== 'Added') {
      \Civi\Api4\GroupContact::update()
        ->setCheckPermissions(false)
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
  public static function addMembership($transaction_link_id, $transaction_link_parent_id, $membership_type_id, $contact_id, $opt_in) {
    // TODO: handle cases when membership already exists.

    $membership_type = \Civi\Api4\MembershipType::get()
      ->setCheckPermissions(false)
      ->addWhere('id', '=', $membership_type_id)
      ->execute()->single();

    $contribution = null;
    $contribution_id = null;
    if (!empty($transaction_link_parent_id)) {
      $parent = \Civi\Api4\CampagnodonTransactionLink::get()
        ->setCheckPermissions(false)
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
        ->setCheckPermissions(false)
        ->addWhere('id', '=', $contribution_id)
        ->execute()->single();
    }

    $period_type = $membership_type['period_type']; // 'rolling' or 'fixed'
    $receive_date = $contribution ? $contribution['receive_date'] : null;
    $start_date = null; // FIXME
    if ($period_type === 'rolling') {
      $start_date = $receive_date;
    }

    // Special case... FIXME: handle these parameters differently. It is not clean.
    $custom_fields = array();
    if (!empty($opt_in) && preg_match('/^(custom_\w+):(0|1)$/', $opt_in, $matches)) {
      $custom_fields[$matches[1]] = intval($matches[2]);
    }

    // Searching for a current membership record.
    // Note: ordering by end_date and taking last. In case there is multiple membership for this contact.
    $current_membership = \Civi\Api4\Membership::get()
      ->setCheckPermissions(false)
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
        civicrm_api3('Membership', 'create', array_merge(
          $custom_fields,
          array(
            'id' => $membership_id,
            'membership_type_id' => $membership_type_id,
            'num_terms' => 1,
            'skipStatusCal' => 0,
            'campaign_id' => $contribution ? $contribution['campaign_id'] : null, // FIXME: keep this?
            'start_date' => $start_date,
            'check_permissions' => 0,
            'sequential' => true
          )
        ));
      }
    } else {
      $membership = civicrm_api3('Membership', 'create', array_merge(
        $custom_fields,
        array(
          'membership_type_id' => $membership_type_id,
          'contact_id' => $contact_id,
          'campaign_id' => $contribution ? $contribution['campaign_id'] : null, // FIXME: keep this?
          'join_date' => $receive_date,
          'start_date' => $start_date,
          'check_permissions' => 0,
          'sequential' => true
        )
      ));
      $membership_id = $membership['values'][0]['id'];
    }

    // FIXME: for now, the membership status is «new», and that is not correct.

    // Linking payment
    if (!$cancel && $contribution_id) {
      civicrm_api3('MembershipPayment', 'create', array(
        'membership_id' => $membership_id,
        'contribution_id' => $contribution_id,
        'check_permissions' => 0
      ));
    }
    
    \Civi\Api4\CampagnodonTransactionLink::update()
      ->setCheckPermissions(false)
      ->addValue('entity_id', $membership_id)
      ->addValue('cancelled', $cancel)
      ->addWhere('id', '=', $transaction_link_id)
      ->execute();
  }

  /**
   * @param $tag_id
   * @param $contact_id
   */
  public static function addTag($tag_id, $contact_id) {
    // Note: this API3 call only create EntityTag if not exists.
    // But it can raise an Exception if all tags are already there... so... try/catch
    try {
      civicrm_api3('EntityTag', 'create', array(
        'contact_id' => $contact_id,
        'tag_id' => $tag_id,
        'check_permissions' => 0
      ));
    } catch (CiviCRM_API3_Exception $e) {
      // TODO: add some unit test
      if ($e->getMessage() != 'Unable to add tags') {
        throw $e;
      }
    }
  }

  protected static function _testOnComplete($link, $transaction_status) {
    if ($link['on_complete'] && $transaction_status === 'completed') return true;
    if (!$link['on_complete'] && $transaction_status === 'init') return true;
    return false;
  }

  public static function processLinks($contact_id, $transaction_id, $transaction_status) {
    $links = \Civi\Api4\CampagnodonTransactionLink::get()
      ->setCheckPermissions(false)
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
              ->setCheckPermissions(false)
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
          CRM_CampagnodonCivicrm_Logic_Contact::addMembership($link['id'], $link['parent_id'], $link['membership_type_id'], $contact_id, $link['opt_in']);
        }
      } else if ($link['entity_table'] === 'civicrm_tag') {
        if (CRM_CampagnodonCivicrm_Logic_Contact::_testOnComplete($link, $transaction_status)) {
          CRM_CampagnodonCivicrm_Logic_Contact::addTag($link['entity_id'], $contact_id);
        }
      }
    }
  }

  /**
   * @param $transaction_id The transaction id to merge.
   * @return boolean success or not
   */
  public static function mergeIntoContact($transaction_id) {
    $transaction = \Civi\Api4\CampagnodonTransaction::get()
      ->setCheckPermissions(false)
      ->addSelect('*')
      ->addWhere('id', '=', $transaction_id)
      ->execute()
      ->single();
    
    if ($transaction['cleaned']) {
      // can't merge, as this transaction was cleaned (personnal data were removed)
      return false;
    }

    $contact_id = $transaction['contact_id'];
    if (!$contact_id) {
      return false;
    }

    $contact = \Civi\Api4\Contact::get()
      ->setCheckPermissions(false)
      ->addSelect('*')
      ->addWhere('id', '=', $contact_id)
      ->execute()
      ->first();
    if (!$contact) {
      return false;
    }

    if (!empty($transaction['email'])) {
      $email = \Civi\Api4\Email::get()
        ->setCheckPermissions(false)
        ->addSelect('*')
        ->addWhere('contact_id', '=', $contact_id)
        ->addWhere('email', '=', $transaction['email'])
        ->execute()
        ->first();
      if (!$email) {
        \Civi\Api4\Email::create()
          ->setCheckPermissions(false)
          ->addValue('email', $transaction['email'])
          ->addValue('contact_id', $contact_id)
          ->addValue('is_primary', true)
          ->execute();
      }
    }

    if ($transaction['tax_receipt']) {
      $is_contact_modification = false;
      $contact_update = \Civi\Api4\Contact::update();
      $contact_update->setCheckPermissions(false);
      $contact_update->addWhere('id', '=', $contact_id);
      foreach (['prefix_id', 'first_name', 'last_name'] as $field) {
        if (!empty($transaction[$field]) && $transaction[$field] != $contact[$field]) {
          $is_contact_modification = true;
          $contact_update->addValue($field, $transaction[$field]);
        }
      }

      if ($is_contact_modification) {
        $contact_update->execute();
      }

      if (!empty($transaction['phone'])) {
        $phone = \Civi\Api4\Phone::get()
          ->setCheckPermissions(false)
          ->addSelect('*')
          ->addWhere('contact_id', '=', $contact_id)
          ->addWhere('phone_numeric', '=', preg_replace('/[^\d]/', '', $transaction['phone']))
          ->execute()
          ->first();
        if (!$phone) {
          \Civi\Api4\Phone::create()
            ->setCheckPermissions(false)
            ->addValue('phone', $transaction['phone'])
            ->addValue('contact_id', $contact_id)
            ->addValue('is_primary', true)
            ->execute();
        }
      }

      if (!empty($transaction['street_address']) && !empty($transaction['city'])) {
        $address = \Civi\Api4\Address::get()
          ->setCheckPermissions(false)
          ->addSelect('*')
          ->addWhere('contact_id', '=', $contact_id);
        foreach (['street_address', 'postal_code', 'city', 'country_id'] as $field) {
          if (!empty($transaction[$field])) {
            $address->addWhere($field, '=', $transaction[$field]);
          }
        }
        $address = $address
          ->execute()
          ->first();
        if (!$address) {
          $address_create = \Civi\Api4\Address::create()
            ->setCheckPermissions(false)
            ->addValue('contact_id', $contact_id)
            ->addValue('is_primary', true);
          foreach (['street_address', 'postal_code', 'city', 'country_id'] as $field) {
            if (!empty($transaction[$field])) {
              $address_create->addValue($field, $transaction[$field]);
            }
          }
          $address_create->execute();
        }
      }
    }

    \Civi\Api4\CampagnodonTransaction::update()
      ->setCheckPermissions(false)
      ->addValue('merged', true)
      ->addWhere('id', '=', $transaction_id)
      ->execute();

      return true;
    // TODO: add some unit tests.
  }
}
