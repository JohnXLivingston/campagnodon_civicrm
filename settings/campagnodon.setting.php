<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

$campagnodon_settings = array();

$campagnodon_settings['campagnodon_contribution_status_pending'] = array(
  'name' => 'campagnodon_contribution_status_pending',
  'type' => 'Integer',
  'html_type' => 'select',
  'default' => 2,
  'title' => E::ts('Contribution status for transaction in pending status.'),
  'is_domain' => 1,
  'is_contact' => 0,
  'pseudoconstant' => [
    'optionGroupName' => 'contribution_status'
  ],
  'settings_pages' => ['campagnodon' => ['weight' => 10]],
);
$campagnodon_settings['campagnodon_contribution_status_completed'] = array(
  'name' => 'campagnodon_contribution_status_completed',
  'type' => 'Integer',
  'html_type' => 'select',
  'default' => 1,
  'title' => E::ts('Contribution status for transaction in completed status.'),
  'is_domain' => 1,
  'is_contact' => 0,
  'pseudoconstant' => [
    'optionGroupName' => 'contribution_status'
  ],
  'settings_pages' => ['campagnodon' => ['weight' => 10]],
);
$campagnodon_settings['campagnodon_contribution_status_cancelled'] = array(
  'name' => 'campagnodon_contribution_status_cancelled',
  'type' => 'Integer',
  'html_type' => 'select',
  'default' => 3,
  'title' => E::ts('Contribution status for transaction in cancelled status.'),
  'is_domain' => 1,
  'is_contact' => 0,
  'pseudoconstant' => [
    'optionGroupName' => 'contribution_status'
  ],
  'settings_pages' => ['campagnodon' => ['weight' => 10]],
);
$campagnodon_settings['campagnodon_contribution_status_failed'] = array(
  'name' => 'campagnodon_contribution_status_failed',
  'type' => 'Integer',
  'html_type' => 'select',
  'default' => 4,
  'title' => E::ts('Contribution status for transaction in failed status.'),
  'is_domain' => 1,
  'is_contact' => 0,
  'pseudoconstant' => [
    'optionGroupName' => 'contribution_status'
  ],
  'settings_pages' => ['campagnodon' => ['weight' => 10]],
);
$campagnodon_settings['campagnodon_contribution_status_refunded'] = array(
  'name' => 'campagnodon_contribution_status_refunded',
  'type' => 'Integer',
  'html_type' => 'select',
  'default' => 7,
  'title' => E::ts('Contribution status for transaction in refunded status.'),
  'is_domain' => 1,
  'is_contact' => 0,
  'pseudoconstant' => [
    'optionGroupName' => 'contribution_status'
  ],
  'settings_pages' => ['campagnodon' => ['weight' => 10]],
);

$campagnodon_settings['campagnodon_dedupe_rule'] = array(
  'name' => 'campagnodon_dedupe_rule',
  'type' => 'Text',
  'html_type' => 'select',
  'default' => '',
  'title' => E::ts('Deduplication rule'),
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
  'title' => E::ts('Deduplication rule with tax receipt'),
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
  'title' => E::ts('Number of days to keep personnal data. Empty=forever.'),
  'is_domain' => 1,
  'is_contact' => 0,
  'settings_pages' => ['campagnodon' => ['weight' => 20]],
);

return $campagnodon_settings;
