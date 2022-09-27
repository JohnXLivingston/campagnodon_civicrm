<?php

require_once 'campagnodon_civicrm.civix.php';
// phpcs:disable
use CRM_CampagnodonCivicrm_ExtensionUtil as E;
// phpcs:enable

/**
 * Implements hook_civicrm_config().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_config/
 */
function campagnodon_civicrm_civicrm_config(&$config) {
  _campagnodon_civicrm_civix_civicrm_config($config);
  \Civi::dispatcher()->addSubscriber(new CRM_CampagnodonCivicrm_Token_CampagnodonTransaction());
}

/**
 * Implements hook_civicrm_install().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_install
 */
function campagnodon_civicrm_civicrm_install() {
  _campagnodon_civicrm_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_postInstall
 */
function campagnodon_civicrm_civicrm_postInstall() {
  _campagnodon_civicrm_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_uninstall
 */
function campagnodon_civicrm_civicrm_uninstall() {
  _campagnodon_civicrm_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_enable
 */
function campagnodon_civicrm_civicrm_enable() {
  _campagnodon_civicrm_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_disable
 */
function campagnodon_civicrm_civicrm_disable() {
  _campagnodon_civicrm_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_upgrade
 */
function campagnodon_civicrm_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _campagnodon_civicrm_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_entityTypes
 */
function campagnodon_civicrm_civicrm_entityTypes(&$entityTypes) {
  _campagnodon_civicrm_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_preProcess
 */
//function campagnodon_civicrm_civicrm_preProcess($formName, &$form) {
//
//}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_navigationMenu
 */
function campagnodon_civicrm_civicrm_navigationMenu(&$menu) {
  _campagnodon_civicrm_civix_insert_navigation_menu($menu, 'Administer', [
    'label' => E::ts('Campagnodon'),
    'name' => 'campagnodon-settings',
    'url' => 'civicrm/admin/setting/campagnodon',
    'permission' => 'administer CiviCRM',
    //  'operator' => 'OR',
    'separator' => 0,
  ]);
  _campagnodon_civicrm_civix_insert_navigation_menu($menu, 'Contributions', [
    'label' => E::ts('Campagnodon'),
    'name' => 'campagnodon',
    'url' => 'civicrm/campagnodon',
    'permission' => 'access Campagnodon',
    //  'operator' => 'OR',
    'separator' => 0,
  ]);
  _campagnodon_civicrm_civix_insert_navigation_menu($menu, 'Search', [
    'label' => E::ts('Search Campagnodon'),
    'name' => 'search_campagnodon',
    'url' => 'civicrm/campagnodon/search',
    'permission' => 'access Campagnodon',
    //  'operator' => 'OR',
    'separator' => 0,
  ]);
  _campagnodon_civicrm_civix_navigationMenu($menu);
}

/**
 * Implementation of hook_civicrm_tabset
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_tabset
 */
function campagnodon_civicrm_civicrm_tabset($path, &$tabs, $context) {
  if ($path === 'civicrm/contact/view' && CRM_Core_Permission::check('access Campagnodon')) {
    // add a tab to the contact summary screen
    $contactId = $context['contact_id'];
    $url = CRM_Utils_System::url('civicrm/campagnodon/contacttab', ['cid' => $contactId]);

    $campagnodon_transactions = \Civi\Api4\CampagnodonTransaction::get()
      ->selectRowCount()
      ->addWhere('contact_id', '=', $contactId)
      ->execute();

    $tabs[] = array(
      'id' => 'contact_campagnodon',
      'url' => $url,
      'count' => $campagnodon_transactions->count(),
      'title' => E::ts('Campagnodon'),
      'weight' => 100,
      'icon' => 'crm-i fa-credit-card',
    );
  }
}

/**
 * Implementation of hook_civicrm_permission
 * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_permission/
 */
function campagnodon_civicrm_civicrm_permission(&$permissions) {
  $permissions['access Campagnodon'] = [
    E::ts('Access to Campagnodon'),
    E::ts('Permission to see Campagnodon application in CiviCRM')
  ];
  $permissions['Campagnodon api'] = [
    E::ts('Use Campagnodon API'),
    E::ts('Permission for the Campagnodon API user (external system: SPIP, ...)')
  ];
}

/**
 * Implementation of hook_civicrm_alterAPIPermissions
 * Set permissions for APIv3/
 */
function campagnodon_civicrm_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
  $permissions['campagnodon'] = [
    'test' => ['Campagnodon api'],
    'start' => ['Campagnodon api'],
    'dsp2info' => ['Campagnodon api'],
    'updatestatus' => ['Campagnodon api'],
    'convert' => ['Campagnodon api'],
    'campaign' => ['Campagnodon api'],
    'recurrence' => ['Campagnodon api']
  ];
}
