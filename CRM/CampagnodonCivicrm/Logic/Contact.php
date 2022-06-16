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
      }
    }
  }

  public static function dedupeTables() {
    $dedupeRules = \Civi\Api4\DedupeRuleGroup::get(FALSE)
      ->addSelect('*')
      ->addWhere('contact_type', '=', 'Individual')
      ->addOrderBy('title', 'ASC')
      ->execute();

    $types = [];
    $types[''] = E::ts('Always create new contacts');
    $types['Unsupervised/first'] = E::ts('Use the unsupervised rule') . ' / ' . E::ts('First match');
    $types['Unsupervised/onlyifsingle'] = E::ts('Use the unsupervised rule') . ' / ' . E::ts('Duplicate if multiple match');
    $types['Supervised/first'] = E::ts('Use the supervised rule') . ' / ' . E::ts('First match');
    $types['Supervised/onlyifsingle'] = E::ts('Use the supervised rule') . ' / ' . E::ts('Duplicate if multiple match');

    foreach ($dedupeRules as $rule) {
      $types[''.$rule['id'].'/first'] = $rule['title'] . ' / ' . E::ts('First match');
      $types[''.$rule['id'].'/onlyifsingle'] = $rule['title'] . ' / ' . E::ts('Duplicate if multiple match');
    }
    return $types;
  }
}