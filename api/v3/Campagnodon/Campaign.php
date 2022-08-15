<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Campagnodon.Campaign API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_campagnodon_Campaign_spec(&$spec) {
}

/**
 * Campagnodon.Campaign API
 * 
 * This is a wrapper to the Campaign API, just to permit «campagnodon api» user to get campaigns.
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_campagnodon_Campaign($params) {
  return civicrm_api3('Campaign', 'get', array_merge(
    $params,
    [
      'check_permissions' => 0
    ]
  ));
}
