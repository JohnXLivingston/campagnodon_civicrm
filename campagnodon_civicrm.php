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
 _campagnodon_civicrm_civix_navigationMenu($menu);
}
