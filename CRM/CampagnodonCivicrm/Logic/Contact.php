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
}