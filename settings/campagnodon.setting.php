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

return $campagnodon_settings;
