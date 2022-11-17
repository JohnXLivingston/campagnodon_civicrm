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
 * This is basically a wrapper to the Campaign API, to permit «campagnodon api» user to get campaigns.
 * The result is filtered so it does not expose unwanted data.
 *
 * It also add the current contributed amount.
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
  // We only admit a few 'options' parameters, to avoid security issues (for example by doing a join on another table)
  $campaign_params = array('options' => []);
  if (is_array($params)) {
    if (array_key_exists('sequential', $params)) {
      $campaign_params['sequential'] = $params['sequential'];
    }
    if (array_key_exists('options', $params) && is_array($params['options'])) {
      foreach (['limit', 'offset', 'sort'] as $p) {
        $campaign_params['options'][$p] = $params['options'][$p];
      }
    }
  }
  $campaign_params['check_permissions'] = 0;

  $campaigns = civicrm_api3('Campaign', 'get', $campaign_params);
  $result = array();
  foreach ($campaigns['values'] as $campaign) { // $id_or_index depends on the 'sequential' option
    $r = array();
    foreach ([
      'id', 'name', 'title', 'description', 'start_date',
      'goal_revenue',
      // 'campaign_type_id', 'is_active',
      // 'created_id', 'created_date', 'last_modified_id', 'last_modified_date'
    ] as $field) {
      if (array_key_exists($field, $campaign)) {
        $r[$field] = $campaign[$field];
      }
    }

    // Computing the current revenue:
    // Only if there is a goal_revenue.
    // This is for performance reasons, to avoid sumup all contribution each time.
    if (!empty($r['goal_revenue'])) {
      // FIXME: as there is no currency attached to goal_revenue, we sum regardless of the contribution currency.
      //        This assumes that there is only one used currency.
      // Note: contribution_status_id=1 <=> Completed
      $where = 'campaign_id = '.((int) $r['id']);
      $where.= ' AND contribution_status_id = 1';
      $r['current_revenue'] = CRM_Core_DAO::singleValueQuery('SELECT sum(total_amount) FROM civicrm_contribution WHERE '.$where) ?? 0;
    }

    $result[$campaign['id']] = $r;
  }

  return civicrm_api3_create_success($result, $params);
}
