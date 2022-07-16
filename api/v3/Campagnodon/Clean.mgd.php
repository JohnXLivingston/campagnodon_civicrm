<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'Cron:Campagnodon.Clean',
    'entity' => 'Job',
    'params' => [
      'version' => 3,
      'name' => 'Call Campagnodon.Clean API',
      'description' => 'Call Campagnodon.Clean API',
      'run_frequency' => 'Daily',
      'api_entity' => 'Campagnodon',
      'api_action' => 'Clean',
      'parameters' => '',
    ],
  ],
];
