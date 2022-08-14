<?php
// This file declares a managed database record for the Civirules triggers declaration.
// See: https://docs.civicrm.org/civirules/en/latest/create-your-own-trigger/

use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Function to check whether civirules is installed.
 *
 * @return bool
 */
function _campagnodon_is_civirules_installed() {
  if (civicrm_api3('Extension', 'get', ['key' => 'civirules', 'status' => 'installed'])['count']) {
    return true;
  } elseif (civicrm_api3('Extension', 'get', ['key' => 'org.civicoop.civirules', 'status' => 'installed'])['count']) {
    return true;
  }
  return false;
}

if (_campagnodon_is_civirules_installed()) {
  CRM_Civirules_Utils_Upgrader::insertTriggersFromJson(E::path('civirules/triggers.json'));
}
