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
   * @param $opt_in
   * @param $keep_current_membership_if_possible: this is used for recurring payment: their can be 12 payments for a yearly membership. We must not change existing membership if it is still ongoing.
   */
  public static function addMembership(
    $transaction_link_id,
    $transaction_link_parent_id,
    $membership_type_id,
    $contact_id,
    $opt_in,
    $keep_current_membership_if_possible
  ) {

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

    // We must search again for a current membership, because the API call will not be the same.
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

    // Note: following API calls are based on this code: https://code.globenet.org/attacfr/spip2CiviCRM/-/blob/master/convert.py#L1053
    if ($current_membership) {
      $membership_id = $current_membership['id'];

      $dont_create = false;

      if ($keep_current_membership_if_possible) {
        // TODO: add some unit test.
        Civi::log()->debug(__METHOD__.': Checking if current membership expires in less than 1 month...');
        $end_date = $current_membership['end_date'];
        $limit_date = date('Y-m-d', strtotime("+1 month"));

        Civi::log()->debug(
          __METHOD__.': end_date='.$end_date
          . ', limit_date='.$limit_date
        );
        if ($end_date < $limit_date) {
          Civi::log()->info(
            __CLASS__.'::'.__METHOD__
            . ': We must renew the membership id='
            . $membership_id
            . ' despite the keep_current_membership_if_possible attribute'
          );
        } else {
          Civi::log()->info(
            __CLASS__.'::'.__METHOD__
            . ': We must add a contribution to membership id='
            . $membership_id
            . ' without touching the membership, because of the keep_current_membership_if_possible attribute'
          );
          $dont_create = true;
        }
      }

      if (!$dont_create) {
        // When we renew a membership after 1 year,
        // Civicrm code can't compute the status for membership starting "in the future".
        // Indead, with Payzen, we receive monthly/annual due 2 weeks in advance...
        // So we introduce an optional campagnodon_renewal_force_status_id settings.
        // If set, it will force the status to the settings value ("Current" by default).
        $force_status_id = Civi::settings()->get('campagnodon_renewal_force_status_id');

        civicrm_api3('Membership', 'create', array_merge(
          $custom_fields,
          array(
            'id' => $membership_id,
            'membership_type_id' => $membership_type_id,
            'num_terms' => 1,
            'skipStatusCal' => 0,
            'campaign_id' => $contribution && array_key_exists('campaign_id', $contribution) ? $contribution['campaign_id'] : null,
            'start_date' => $start_date,
            'status_id' => empty($force_status_id) ? NULL : $force_status_id,
            'check_permissions' => 0,
            'sequential' => true
          )
        ));
      }
    } else {
      if (empty($start_date) && $period_type === 'fixed') {
        Civi::log()->debug(
          __CLASS__.'::'.__METHOD__
          . ': We are on a brand new fixex membership, we must compare the payment date with the rollover date'
        );
        $start_date = CRM_CampagnodonCivicrm_Logic_Contact::_testNewMembershipRolloverDate($receive_date);
      }

      $membership = civicrm_api3('Membership', 'create', array_merge(
        $custom_fields,
        array(
          'membership_type_id' => $membership_type_id,
          'contact_id' => $contact_id,
          'campaign_id' => $contribution && array_key_exists('campaign_id', $contribution) ? $contribution['campaign_id'] : null,
          'source' => $contribution ? $contribution['source'] : null, // Only doing this for new membership, not renewal.
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
    if ($contribution_id) {
      civicrm_api3('MembershipPayment', 'create', array(
        'membership_id' => $membership_id,
        'contribution_id' => $contribution_id,
        'check_permissions' => 0
      ));
    }
    
    \Civi\Api4\CampagnodonTransactionLink::update()
      ->setCheckPermissions(false)
      ->addValue('entity_id', $membership_id)
      ->addWhere('id', '=', $transaction_link_id)
      ->execute();
  }

  /**
   * @param $tag_id
   * @param $contact_id
   */
  public static function addTag($tag_id, $contact_id) {
    $entity_tag = \Civi\Api4\EntityTag::get()
      ->setCheckPermissions(false)
      ->addWhere('tag_id', '=', $tag_id)
      ->addWhere('entity_table', '=', 'civicrm_contact')
      ->addWhere('entity_id', '=', $contact_id)
      ->execute()->first();
    if (!$entity_tag) {
      \Civi\Api4\EntityTag::create()
        ->setCheckPermissions(false)
        ->addValue('entity_table', 'civicrm_contact')
        ->addValue('entity_id', $contact_id)
        ->addValue('tag_id', $tag_id)
        ->execute();
    }
    // the code bellow seams to provoke unwanted and invisible rollback...
    // // Note: this API3 call only create EntityTag if not exists.
    // // But it can raise an Exception if all tags are already there... so... try/catch
    // try {
    //   civicrm_api3('EntityTag', 'create', array(
    //     'contact_id' => $contact_id,
    //     'tag_id' => $tag_id,
    //     'check_permissions' => 0
    //   ));
    // } catch (CiviCRM_API3_Exception $e) {
    //   // TODO: add some unit test
    //   if ($e->getMessage() != 'Unable to add tags') {
    //     throw $e;
    //   }
    // }
  }

  protected static function _testOnComplete($link, $transaction_status) {
    if ($link['on_complete'] && $transaction_status === 'completed') return true;
    if (!$link['on_complete'] && $transaction_status === 'init') return true;
    return false;
  }

  /**
   * Search for double membership.
   * Note: only fixed period_type are tested.
   * TODO: add some unit tests.
   * @return boolean
   */
  public static function searchDoubleMembership($contact_id, $transaction_id) {
    $double = false;
    $links = \Civi\Api4\CampagnodonTransactionLink::get()
      ->setCheckPermissions(false)
      ->addSelect('*')
      ->addWhere('campagnodon_tid', '=', $transaction_id)
      ->addWhere('entity_table', '=', 'civicrm_membership')
      ->execute();
    $links->indexBy('id');
    foreach ($links as $lid => $link) {
      if (!empty($link['cancelled'])) {
        if ($link['cancelled'] === 'already_member') {
          $double = true;
        }
        continue;
      }
      $membership_type_id = $link['membership_type_id'];
      $membership_type = \Civi\Api4\MembershipType::get()
        ->setCheckPermissions(false)
        ->addWhere('id', '=', $membership_type_id)
        ->execute()->single();
      $period_type = $membership_type['period_type']; // 'rolling' or 'fixed'

      if ($period_type === 'fixed') { // only fixed period membership are searched for double.
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

        if ($current_membership) {
          $end_date = $current_membership['end_date'];
          if ($end_date >= date("Y-m-d")) {
            $double = true;
            \Civi\Api4\CampagnodonTransactionLink::update()
              ->setCheckPermissions(false)
              ->addValue('entity_id', $current_membership['id'])
              ->addValue('cancelled', 'already_member')
              ->addWhere('id', '=', $link['id'])
              ->execute();
          }
        }
      }
    }

    return $double;
  }

  public static function processLinks($contact_id, $transaction_id, $transaction_status) {
    Civi::log()->debug(__METHOD__.' Entering processLinks...');
    $links = \Civi\Api4\CampagnodonTransactionLink::get()
      ->setCheckPermissions(false)
      ->addSelect('*')
      ->addWhere('campagnodon_tid', '=', $transaction_id)
      ->execute();
    $links->indexBy('id');
    foreach ($links as $lid => $link) {
      if ($link['cancelled']) {
        Civi::log()->debug(__METHOD__.' link cancelled, ignoring: '.$lid);
        continue;
      }

      if ($link['entity_table'] === 'civicrm_group') {
        if (CRM_CampagnodonCivicrm_Logic_Contact::_testOnComplete($link, $transaction_status)) {
          Civi::log()->debug(__METHOD__.' Calling addInGroup for link '.$lid);
          CRM_CampagnodonCivicrm_Logic_Contact::addInGroup($link['entity_id'], $contact_id);
        }
      } else if ($link['entity_table'] === 'civicrm_contact') {
        if (!empty($link['opt_in']) && CRM_CampagnodonCivicrm_Logic_Contact::_testOnComplete($link, $transaction_status)) {
          if (CRM_CampagnodonCivicrm_BAO_CampagnodonTransaction::isOptInValid($link['opt_in'])) {
            Civi::log()->debug(__METHOD__.' Updating the contact for link '.$lid);
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
          Civi::log()->debug(__METHOD__.' Calling addMembership for link '.$lid);
          CRM_CampagnodonCivicrm_Logic_Contact::addMembership(
            $link['id'],
            $link['parent_id'],
            $link['membership_type_id'],
            $contact_id,
            $link['opt_in'],
            $link['keep_current_membership_if_possible']
          );
        }
      } else if ($link['entity_table'] === 'civicrm_tag') {
        if (CRM_CampagnodonCivicrm_Logic_Contact::_testOnComplete($link, $transaction_status)) {
          Civi::log()->debug(__METHOD__.' Calling addTag for link '.$lid);
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
        foreach (['street_address', 'supplemental_address_1', 'supplemental_address_2', 'postal_code', 'city', 'country_id'] as $field) {
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
          foreach (['street_address', 'supplemental_address_1', 'supplemental_address_2', 'postal_code', 'city', 'country_id'] as $field) {
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

  /**
   * Read the campagnodon_new_membership_rollover_day_month settings if set,
   * to get the rollover date and month.
   */
  protected static function _newMembershipRollover() {
    $v = Civi::settings()->get('campagnodon_new_membership_rollover_day_month');
    if (empty($v)) { return null; }

    if (!preg_match('/^(\d{1,2})\/(\d{1,2})$/', $v, $matches)) {
      Civi::log()->error(__CLASS__.'::'.__METHOD__.': Invalid value for campagnodon_new_membership_rollover_day_month');
      return null;
    }

    $o = new stdClass();
    $o->day = $matches[1];
    $o->month = $matches[2];
    return $o;
  }

  protected static function _testNewMembershipRolloverDate($date) {
    $rollover = CRM_CampagnodonCivicrm_Logic_Contact::_newMembershipRollover();
    if (!$rollover) { return null; }

    if (empty($date)) {
      // Defaulting to today
      $now = new DateTime();
      $date = $now->format('Y-m-d');
    }

    $dateObject = new DateTime($date);

    // we can simply compare with this (we use a new DateTime, to avoid issues when month or day as a single number):
    $pivot = new DateTime($dateObject->format('Y') . '-' . $rollover->month . '-' . $rollover->day);
    $pivot = $pivot->format('Y-m-d');
    if ($date < $pivot) { // Note: $date can contain an hour, so we must test this way.
      Civi::log()->info(
        __CLASS__.'::'.__METHOD__
        . ': The payment date ' . $date . ' is less than the rollover date ' . $pivot .', membership start date should not be modified'
      );
      return null;
    }

    $r = new DateTime();
    $r->setDate(intval($dateObject->format('Y')) + 1, 1, 1);
    $r = $r->format('Y-m-d');
    Civi::log()->info(
      __CLASS__.'::'.__METHOD__
      . ': The payment date ' . $date . ' is greater or equal than the rollover date ' . $pivot .', '
      . 'the membership must start the next year, we will use ' . $r . ' as start date.'
    );
    return $r;
  }

  public static function membershipStatus () {
    $options = CRM_Member_PseudoConstant::membershipStatus();
    $options['0'] = '';
    return $options;
  }
}
