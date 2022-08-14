<?php
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
