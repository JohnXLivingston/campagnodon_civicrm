<?php

$campagnodon_settings = array();
$campagnodon_defaults = array(
  'pending' => 2,
  'completed' => 1,
  'cancelled' => 3,
  'failed' => 4,
  'refunded' => 7
);

foreach ($campagnodon_defaults as $status => $default) {
  $campagnodon_settings['campagnodon_contribution_status_'.$status] = array(
    'name' => 'campagnodon_contribution_status_'.$status,
    'type' => 'Integer',
    'html_type' => 'select',
    'default' => $default,
    'title' => ts('Contribution status for transaction in '.$status.' status.'),
    'is_domain' => 1,
    'is_contact' => 0,
    'pseudoconstant' => [
      'optionGroupName' => 'contribution_status'
    ],
    'settings_pages' => ['campagnodon' => ['weight' => 10]],
  );
}

$campagnodon_settings['campagnodon_dedupe_rule'] = array(
  'name' => 'campagnodon_dedupe_rule',
  'type' => 'Text',
  'html_type' => 'select',
  'default' => '',
  'title' => ts('Deduplication rule'),
  'is_domain' => 1,
  'is_contact' => 0,
  'pseudoconstant' => [
    'callback' => 'CRM_CampagnodonCivicrm_Logic_Dedupe_Contact::dedupeTables',
  ],
  'settings_pages' => ['campagnodon' => ['weight' => 20]],
);
$campagnodon_settings['campagnodon_dedupe_rule_with_tax_receipt'] = array(
  'name' => 'campagnodon_dedupe_rule_with_tax_receipt',
  'type' => 'Text',
  'html_type' => 'select',
  'default' => '',
  'title' => ts('Deduplication rule with tax receipt'),
  'is_domain' => 1,
  'is_contact' => 0,
  'pseudoconstant' => [
    'callback' => 'CRM_CampagnodonCivicrm_Logic_Dedupe_Contact::dedupeTables',
  ],
  'settings_pages' => ['campagnodon' => ['weight' => 20]],
);

$campagnodon_settings['campagnodon_clean_nb_days'] = array(
  'name' => 'campagnodon_clean_nb_days',
  'type' => 'Integer',
  'html_type' => 'number',
  'default' => '',
  'title' => ts('Number of days to keep personnal data. Empty=forever.'),
  'is_domain' => 1,
  'is_contact' => 0,
  'settings_pages' => ['campagnodon' => ['weight' => 20]],
);

return $campagnodon_settings;
