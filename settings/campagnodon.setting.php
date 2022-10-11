<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

$campagnodon_settings = array();

$campagnodon_settings['campagnodon_contribution_status_pending'] = array(
  'name' => 'campagnodon_contribution_status_pending',
  'type' => 'Integer',
  'html_type' => 'select',
  'default' => 2, // 2 = pending
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
  'default' => 1, // 1 = completed
  'title' => E::ts('Contribution status for transaction in completed status.'),
  'is_domain' => 1,
  'is_contact' => 0,
  'pseudoconstant' => [
    'optionGroupName' => 'contribution_status'
  ],
  'settings_pages' => ['campagnodon' => ['weight' => 10]],
);
$campagnodon_settings['campagnodon_contribution_status_double_membership'] = array(
  'name' => 'campagnodon_contribution_status_double_membership',
  'type' => 'Integer',
  'html_type' => 'select',
  'default' => 1, // 1 = completed. We want the same for double_membership and completed.
  'title' => E::ts('Contribution status for transaction in double_membership status.'),
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
  'default' => 3, // 3 = cancelled
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
  'default' => 4, // 4 = failed
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
  'default' => 7, // 7 = refunded
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

$campagnodon_settings['campagnodon_dedupe_rule_custom_1_operation_type'] = array(
  'name' => 'campagnodon_dedupe_rule_custom_1_operation_type',
  'type' => 'Text',
  'html_type' => 'text',
  'default' => '',
  'title' => E::ts('Custom deduplication rule 1 - Operation Type'),
  'is_domain' => 1,
  'is_contact' => 0,
  'pseudoconstant' => [
    'callback' => 'CRM_CampagnodonCivicrm_Logic_Dedupe_Contact::dedupeTables',
  ],
  'settings_pages' => ['campagnodon' => ['weight' => 20]],
);
$campagnodon_settings['campagnodon_dedupe_rule_custom_1'] = array(
  'name' => 'campagnodon_dedupe_rule_custom_1',
  'type' => 'Text',
  'html_type' => 'select',
  'default' => '',
  'title' => E::ts('Custom deduplication rule 1'),
  'is_domain' => 1,
  'is_contact' => 0,
  'pseudoconstant' => [
    'callback' => 'CRM_CampagnodonCivicrm_Logic_Dedupe_Contact::dedupeTables',
  ],
  'settings_pages' => ['campagnodon' => ['weight' => 20]],
);
$campagnodon_settings['campagnodon_dedupe_rule_custom_2_operation_type'] = array(
  'name' => 'campagnodon_dedupe_rule_custom_2_operation_type',
  'type' => 'Text',
  'html_type' => 'text',
  'default' => '',
  'title' => E::ts('Custom deduplication rule 2 - Operation Type'),
  'is_domain' => 1,
  'is_contact' => 0,
  'pseudoconstant' => [
    'callback' => 'CRM_CampagnodonCivicrm_Logic_Dedupe_Contact::dedupeTables',
  ],
  'settings_pages' => ['campagnodon' => ['weight' => 20]],
);
$campagnodon_settings['campagnodon_dedupe_rule_custom_2'] = array(
  'name' => 'campagnodon_dedupe_rule_custom_2',
  'type' => 'Text',
  'html_type' => 'select',
  'default' => '',
  'title' => E::ts('Custom deduplication rule 2'),
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

$campagnodon_settings['campagnodon_contribution_tax_receipt_field'] = array(
  'name' => 'campagnodon_contribution_tax_receipt_field',
  'type' => 'Text',
  'html_type' => 'text',
  'default' => '',
  'title' => E::ts('Tax receipt field'),
  'is_domain' => 1,
  'is_contact' => 0,
  'settings_pages' => ['campagnodon' => ['weight' => 30]],
);

$campagnodon_settings['campagnodon_allow_migrate_contribution'] = array(
  'name' => 'campagnodon_allow_migrate_contribution',
  'type' => 'Boolean',
  'html_type' => 'checkbox',
  'default' => '',
  'title' => E::ts('Allow the use of the migrationcontribution API. Don\'t activate if you don\'t know what it is.'),
  'is_domain' => 1,
  'is_contact' => 0,
  'settings_pages' => ['campagnodon' => ['weight' => 90]],
);


return $campagnodon_settings;
