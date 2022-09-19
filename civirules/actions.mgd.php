<?php
// This file declares a managed database record for the Civirules triggers declaration.
// See: https://docs.civicrm.org/civirules/en/latest/create-your-own-trigger/

use CRM_CampagnodonCivicrm_ExtensionUtil as E;

require_once(E::path('civirules/civirules_installed.php'));

if (_campagnodon_is_civirules_installed()) {
  CRM_Civirules_Utils_Upgrader::insertActionsFromJson(E::path('civirules/actions.json'));
}
