<?php

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

  public static function processLinks($contact_id, $transaction_id, $transaction_status) {
    $links = \Civi\Api4\CampagnodonTransactionLink::get()
      ->addSelect('*')
      ->addWhere('campagnodon_tid', '=', $transaction_id)
      ->execute();
    $links->indexBy('id');
    foreach ($links as $lid => $link) {
      if ($link['entity_table'] === 'civicrm_group') {
        if (
          ($link['on_complete'] && $transaction_status === 'completed')
          ||
          (!$link['on_complete'] && $transaction_status === 'init')
        ) {
          CRM_CampagnodonCivicrm_Logic_Contact::addInGroup($link['entity_id'], $contact_id);
        }
      }
    }
  }
}