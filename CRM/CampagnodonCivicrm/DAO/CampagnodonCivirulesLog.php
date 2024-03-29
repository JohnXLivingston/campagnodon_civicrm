<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from campagnodon_civicrm/xml/schema/CRM/CampagnodonCivicrm/CampagnodonCivirulesLog.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:743f471bef76db8a9475cb01a4ebedbb)
 */
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Database access object for the CampagnodonCivirulesLog entity.
 */
class CRM_CampagnodonCivicrm_DAO_CampagnodonCivirulesLog extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_campagnodon_civirules_log';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = FALSE;

  /**
   * Unique CampagnodonCivirulesLog ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $rule_id;

  /**
   * @var string
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $trigger_name;

  /**
   * @var string|null
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $entity_table;

  /**
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $entity_id;

  /**
   * @var string
   *   (SQL type: datetime)
   *   Note that values will be retrieved from the database as a string.
   */
  public $log_date;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_campagnodon_civirules_log';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Campagnodon Civirules Logs') : E::ts('Campagnodon Civirules Log');
  }

  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  public static function &fields() {
    if (!isset(Civi::$statics[__CLASS__]['fields'])) {
      Civi::$statics[__CLASS__]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => E::ts('Unique CampagnodonCivirulesLog ID'),
          'required' => TRUE,
          'where' => 'civicrm_campagnodon_civirules_log.id',
          'table_name' => 'civicrm_campagnodon_civirules_log',
          'entity' => 'CampagnodonCivirulesLog',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonCivirulesLog',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'rule_id' => [
          'name' => 'rule_id',
          'type' => CRM_Utils_Type::T_INT,
          'where' => 'civicrm_campagnodon_civirules_log.rule_id',
          'default' => NULL,
          'table_name' => 'civicrm_campagnodon_civirules_log',
          'entity' => 'CampagnodonCivirulesLog',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonCivirulesLog',
          'localizable' => 0,
          'add' => NULL,
        ],
        'trigger_name' => [
          'name' => 'trigger_name',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Trigger Name'),
          'required' => TRUE,
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_campagnodon_civirules_log.trigger_name',
          'table_name' => 'civicrm_campagnodon_civirules_log',
          'entity' => 'CampagnodonCivirulesLog',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonCivirulesLog',
          'localizable' => 0,
          'add' => NULL,
        ],
        'entity_table' => [
          'name' => 'entity_table',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Entity Table'),
          'maxlength' => 255,
          'size' => CRM_Utils_Type::HUGE,
          'where' => 'civicrm_campagnodon_civirules_log.entity_table',
          'default' => NULL,
          'table_name' => 'civicrm_campagnodon_civirules_log',
          'entity' => 'CampagnodonCivirulesLog',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonCivirulesLog',
          'localizable' => 0,
          'add' => NULL,
        ],
        'entity_id' => [
          'name' => 'entity_id',
          'type' => CRM_Utils_Type::T_INT,
          'where' => 'civicrm_campagnodon_civirules_log.entity_id',
          'default' => NULL,
          'table_name' => 'civicrm_campagnodon_civirules_log',
          'entity' => 'CampagnodonCivirulesLog',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonCivirulesLog',
          'localizable' => 0,
          'add' => NULL,
        ],
        'log_date' => [
          'name' => 'log_date',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => E::ts('Log Date'),
          'required' => TRUE,
          'where' => 'civicrm_campagnodon_civirules_log.log_date',
          'default' => 'NOW()',
          'table_name' => 'civicrm_campagnodon_civirules_log',
          'entity' => 'CampagnodonCivirulesLog',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonCivirulesLog',
          'localizable' => 0,
          'add' => NULL,
        ],
      ];
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'fields_callback', Civi::$statics[__CLASS__]['fields']);
    }
    return Civi::$statics[__CLASS__]['fields'];
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[__CLASS__]['fieldKeys'])) {
      Civi::$statics[__CLASS__]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', self::fields()));
    }
    return Civi::$statics[__CLASS__]['fieldKeys'];
  }

  /**
   * Returns the names of this table
   *
   * @return string
   */
  public static function getTableName() {
    return self::$_tableName;
  }

  /**
   * Returns if this table needs to be logged
   *
   * @return bool
   */
  public function getLog() {
    return self::$_log;
  }

  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &import($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'campagnodon_civirules_log', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  public static function &export($prefix = FALSE) {
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'campagnodon_civirules_log', $prefix, []);
    return $r;
  }

  /**
   * Returns the list of indices
   *
   * @param bool $localize
   *
   * @return array
   */
  public static function indices($localize = TRUE) {
    $indices = [
      'idx_rule_id_name_entity' => [
        'name' => 'idx_rule_id_name_entity',
        'field' => [
          0 => 'rule_id',
          1 => 'trigger_name',
          2 => 'entity_table',
          3 => 'entity_id',
        ],
        'localizable' => FALSE,
        'sig' => 'civicrm_campagnodon_civirules_log::0::rule_id::trigger_name::entity_table::entity_id',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
