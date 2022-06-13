<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Collection of upgrade steps.
 */
class CRM_CampagnodonCivicrm_Upgrader extends CRM_CampagnodonCivicrm_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).

  /**
   * Example: Run an external SQL script when the module is installed.
   *
  public function install() {
    $this->executeSqlFile('sql/myinstall.sql');
  }

  /**
   * Example: Work with entities usually not available during the install step.
   *
   * This method can be used for any post-install tasks. For example, if a step
   * of your installation depends on accessing an entity that is itself
   * created during the installation (e.g., a setting or a managed entity), do
   * so here to avoid order of operation problems.
   */
  // public function postInstall() {
  //  $customFieldId = civicrm_api3('CustomField', 'getvalue', array(
  //    'return' => array("id"),
  //    'name' => "customFieldCreatedViaManagedHook",
  //  ));
  //  civicrm_api3('Setting', 'create', array(
  //    'myWeirdFieldSetting' => array('id' => $customFieldId, 'weirdness' => 1),
  //  ));
  // }

  /**
   * Example: Run an external SQL script when the module is uninstalled.
   */
  // public function uninstall() {
  //  $this->executeSqlFile('sql/myuninstall.sql');
  // }

  /**
   * Example: Run a simple query when a module is enabled.
   */
  // public function enable() {
  //  CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 1 WHERE bar = "whiz"');
  // }

  /**
   * Example: Run a simple query when a module is disabled.
   */
  // public function disable() {
  //   CRM_Core_DAO::executeQuery('UPDATE foo SET is_active = 0 WHERE bar = "whiz"');
  // }

  /**
   * Example: Run a couple simple queries.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4200(): bool {
  //   $this->ctx->log->info('Applying update 4200');
  //   CRM_Core_DAO::executeQuery('UPDATE foo SET bar = "whiz"');
  //   CRM_Core_DAO::executeQuery('DELETE FROM bang WHERE willy = wonka(2)');
  //   return TRUE;
  // }


  /**
   * Example: Run an external SQL script.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4201(): bool {
  //   $this->ctx->log->info('Applying update 4201');
  //   // this path is relative to the extension base dir
  //   $this->executeSqlFile('sql/upgrade_4201.sql');
  //   return TRUE;
  // }


  /**
   * Example: Run a slow upgrade process by breaking it up into smaller chunk.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4202(): bool {
  //   $this->ctx->log->info('Planning update 4202'); // PEAR Log interface

  //   $this->addTask(E::ts('Process first step'), 'processPart1', $arg1, $arg2);
  //   $this->addTask(E::ts('Process second step'), 'processPart2', $arg3, $arg4);
  //   $this->addTask(E::ts('Process second step'), 'processPart3', $arg5);
  //   return TRUE;
  // }
  // public function processPart1($arg1, $arg2) { sleep(10); return TRUE; }
  // public function processPart2($arg3, $arg4) { sleep(10); return TRUE; }
  // public function processPart3($arg5) { sleep(10); return TRUE; }

  /**
   * Example: Run an upgrade with a query that touches many (potentially
   * millions) of records by breaking it up into smaller chunks.
   *
   * @return TRUE on success
   * @throws Exception
   */
  // public function upgrade_4203(): bool {
  //   $this->ctx->log->info('Planning update 4203'); // PEAR Log interface

  //   $minId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(min(id),0) FROM civicrm_contribution');
  //   $maxId = CRM_Core_DAO::singleValueQuery('SELECT coalesce(max(id),0) FROM civicrm_contribution');
  //   for ($startId = $minId; $startId <= $maxId; $startId += self::BATCH_SIZE) {
  //     $endId = $startId + self::BATCH_SIZE - 1;
  //     $title = E::ts('Upgrade Batch (%1 => %2)', array(
  //       1 => $startId,
  //       2 => $endId,
  //     ));
  //     $sql = '
  //       UPDATE civicrm_contribution SET foobar = whiz(wonky()+wanker)
  //       WHERE id BETWEEN %1 and %2
  //     ';
  //     $params = array(
  //       1 => array($startId, 'Integer'),
  //       2 => array($endId, 'Integer'),
  //     );
  //     $this->addTask($title, 'executeSql', $sql, $params);
  //   }
  //   return TRUE;
  // }

  /**
   * New columns.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_0001(): bool {
    $this->ctx->log->info('Planning update 0001');
    CRM_Core_DAO::executeQuery('ALTER TABLE civicrm_campagnodon_transaction_link ADD COLUMN `on_complete` tinyint DEFAULT false');
    return TRUE;
  }

  /**
   * New columns.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_0002(): bool {
    $this->ctx->log->info('Planning update 0002');
    CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_campagnodon_transaction ADD COLUMN `tax_receipt` tinyint NOT NULL DEFAULT false COMMENT 'True if the user want a tax receipt'");
    return TRUE;
  }

  /**
   * SQL definition changes.
   *
   * @return TRUE on success
   * @throws Exception
   */
  public function upgrade_0003(): bool {
    $this->ctx->log->info('Planning update 0003');
    CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_campagnodon_transaction_link MODIFY COLUMN `entity_id` int unsigned NULL DEFAULT NULL COMMENT 'ID of the linked object. Can be null if the object is not created in pending state.'");
    CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_campagnodon_transaction_link ADD COLUMN IF NOT EXISTS `total_amount` decimal(20,2) NULL DEFAULT NULL COMMENT 'Only when entity_table=contribution. Total amount of this contribution.'");
    CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_campagnodon_transaction_link ADD COLUMN IF NOT EXISTS `currency` varchar(3) DEFAULT NULL COMMENT 'Only when entity_table=contribution. 3 character string, value from config setting or input via user.'");
    CRM_Core_DAO::executeQuery("ALTER TABLE civicrm_campagnodon_transaction_link ADD COLUMN IF NOT EXISTS `financial_type_id` int unsigned NULL DEFAULT NULL COMMENT 'Only when entity_table=contribution. FK to Financial Type.'");
    // Seems that following lines dont work...
    // CRM_Core_DAO::executeQuery(
    //   "UPDATE civicrm_campagnodon_transaction_link SET "
    //   ." total_amount = "
    //   ." (SELECT total_amount FROM civicrm_contribution WHERE civicrm_contribution.id = civicrm_campagnodon_transaction_link.entity_id) "
    //   ." WHERE entity_table = 'civicrm_contribution' AND total_amout IS NULL "
    //   ." AND entity_id IS NOT NULL"
    // );
    // CRM_Core_DAO::executeQuery(
    //   "UPDATE civicrm_campagnodon_transaction_link SET "
    //   ." currency = "
    //   ." (SELECT currency FROM civicrm_contribution WHERE civicrm_contribution.id = civicrm_campagnodon_transaction_link.entity_id) "
    //   ." WHERE entity_table = 'civicrm_contribution' AND currency IS NULL "
    //   ." AND entity_id IS NOT NULL"
    // );
    // CRM_Core_DAO::executeQuery(
    //   "UPDATE civicrm_campagnodon_transaction_link SET "
    //   ." financial_type_id = "
    //   ." (SELECT financial_type_id FROM civicrm_contribution WHERE civicrm_contribution.id = civicrm_campagnodon_transaction_link.entity_id) "
    //   ." WHERE entity_table = 'civicrm_contribution' AND financial_type_id IS NULL "
    //   ." AND entity_id IS NOT NULL"
    // );
    return TRUE;
  }
}
