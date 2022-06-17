<?php

use CRM_CampagnodonCivicrm_ExtensionUtil as E;

// TODO: add some unit tests.
class CRM_CampagnodonCivicrm_Logic_Dedupe_Contact {
  protected $contact_create_params = null;
  protected $contact = null;
  protected $is_new_contact = null;
  protected $tax_receipt = false;

  public function __construct($contact_create_params) {
    $this->contact_create_params = $contact_create_params;
  }
  
  /**
   * Initiate the process and returns an object ready to do the work.
   * @param $params. The params received by the Start API.
   */
  public static function init($params) {
    $contact_create_params = [
      'contact_type' => 'Individual',
      'do_not_trade' => true, // can be changed later, with optional subscription
    ];
    foreach ([
      'email' => 'email',
      'prefix' => 'prefix_id',
      'first_name' => 'first_name',
      'last_name' => 'last_name',
      'birth_date' => 'birth_date',
    ] as $pfield => $create_field) {
      if (array_key_exists($pfield, $params) && !empty($params[$pfield])) {
        $contact_create_params[$create_field] = $params[$pfield];
      }
    }
    foreach ([
      'street_address' => 'street_address',
      'postal_code' => 'postal_code',
      'city' => 'city',
      'country' => 'country_id'
    ] as $pfield => $create_field) {
      if (array_key_exists($pfield, $params) && !empty($params[$pfield])) {
        if (!array_key_exists('api.address.create', $contact_create_params)) {
          $contact_create_params['api.address.create'] = [];
        }
        $contact_create_params['api.address.create'][$create_field] = $params[$pfield];
      }
    }
    if (array_key_exists('phone', $params) && !empty($params['phone'])) {
      $contact_create_params['api.phone.create'] = ['phone' => $params['phone']];
    }
    return new CRM_CampagnodonCivicrm_Logic_Dedupe_Contact($contact_create_params);
  }

  public function withTaxReceipt($tax_receipt = true) {
    $this->tax_receipt = $tax_receipt;
  }

  /**
   * Search the contact and return it.
   * Must be called after init.
   */
  public function getContact() {
    if ($this->contact !== null) {
      return $this->contact;
    }

    $contact = null;
    $dedupe_rule = $this->tax_receipt
      ? Civi::settings()->get('campagnodon_dedupe_rule_with_tax_receipt')
      : Civi::settings()->get('campagnodon_dedupe_rule');

    $contact = $this->_searchContact($dedupe_rule);

    if (empty($contact)) {
      return $this->_createContact();
    }
    
    $this->is_new_contact = false;
    $this->contact = $contact;
    return $contact;
  }

  protected function _searchContact($dedupe_rule) {
    if (empty($dedupe_rule) || $dedupe_rule == '0') {
      return null;
    }

    $contacts = null;
    $dedupe_rule_id = null;
    $dedupe_rule_type = null;
    $dedupe_mode = 'first'; // 'first' or 'onlyifsingle'
    list($a, $b) = explode('/', $dedupe_rule);
    if (is_numeric($a)) {
      $dedupe_rule_id = intval($a);
    } else {
      $dedupe_rule_type = $a;
    }
    if (!empty($b)) {
      $dedupe_mode = $b;
    }

    $contacts = civicrm_api3('Contact', 'duplicatecheck', array(
      'match' => $this->contact_create_params,
      'rule_type' => $dedupe_rule_type,
      'dedupe_rule_id' => $dedupe_rule_id,
      'sequential' => true
    ));

    if ($contacts['count'] === 1) {
      return $contacts['values'][0];
    } else if ($contacts['count'] > 1 && $dedupe_mode === 'first') {
      return $contacts['values'][0];
    }
    return null;
  }

  protected function _createContact() {
    $contacts = civicrm_api3('Contact', 'create', array_merge(
      $this->contact_create_params,
      [
        'sequential' => true,
        'options' => [
          'reload' => true
        ]
      ]
    ));
    if ($contacts['count'] === 1) {
      $this->contact = $contacts['values'][0];
      $this->is_new_contact = true;
      return $this->contact;
    }
    return null;
  }

  public function isNewContact() {
    return $this->is_new_contact;
  }

  /**
   * Settings options for deduplication rules.
   */
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
