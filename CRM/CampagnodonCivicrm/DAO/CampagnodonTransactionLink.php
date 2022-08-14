<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from campagnodon_civicrm/xml/schema/CRM/CampagnodonCivicrm/CampagnodonTransactionLink.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:b5d1a7bf60b51821f6ca25b6250a5ff3)
 */
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Database access object for the CampagnodonTransactionLink entity.
 */
class CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_campagnodon_transaction_link';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = FALSE;

  /**
   * Unique CampagnodonTransactionLink ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * FK to CampagnodonTransaction
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $campagnodon_tid;

  /**
   * Optional parent id for this CampagnodonTransactionLink. Used to find the contribution link associated to a membership subscription.
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $parent_id;

  /**
   * Optional name for optional subscription. Can be used by APIs.
   *
   * @var string
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $optional_subscription_name;

  /**
   * Table of the linked object
   *
   * @var string
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $entity_table;

  /**
   * ID of the linked object. Can be null if the object is not created in pending state.
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $entity_id;

  /**
   * Only when entity_table='group' or 'contact' or 'tag'. If true, the contact will be added in group only when transaction is complete.
   *
   * @var bool|string|null
   *   (SQL type: tinyint)
   *   Note that values will be retrieved from the database as a string.
   */
  public $on_complete;

  /**
   * Only when entity_table='contribution'. Total amount of this contribution.
   *
   * @var float|string
   *   (SQL type: decimal(20,2))
   *   Note that values will be retrieved from the database as a string.
   */
  public $total_amount;

  /**
   * Only when entity_table='contribution'. 3 character string, value from config setting or input via user.
   *
   * @var string|null
   *   (SQL type: varchar(3))
   *   Note that values will be retrieved from the database as a string.
   */
  public $currency;

  /**
   * Only when entity_table='contribution'. FK to Financial Type.
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $financial_type_id;

  /**
   * Only when entity_table='membership'. FK to Membership Type.
   *
   * @var int|string
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $membership_type_id;

  /**
   * An opt-in action to do on the contact (or membership).
   *
   * @var string|null
   *   (SQL type: varchar(25))
   *   Note that values will be retrieved from the database as a string.
   */
  public $opt_in;

  /**
   * Some links can be cancelled. This field contains a keyword to describe the reason. Example: membership already exists.
   *
   * @var string|null
   *   (SQL type: varchar(20))
   *   Note that values will be retrieved from the database as a string.
   */
  public $cancelled;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_campagnodon_transaction_link';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Campagnodon Transaction Links') : E::ts('Campagnodon Transaction Link');
  }

  /**
   * Returns foreign keys and entity references.
   *
   * @return array
   *   [CRM_Core_Reference_Interface]
   */
  public static function getReferenceColumns() {
    if (!isset(Civi::$statics[__CLASS__]['links'])) {
      Civi::$statics[__CLASS__]['links'] = static::createReferenceColumns(__CLASS__);
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'campagnodon_tid', 'civicrm_campagnodon_transaction', 'id');
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'parent_id', 'civicrm_campagnodon_transaction_link', 'id');
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Dynamic(self::getTableName(), 'entity_id', NULL, 'id', 'entity_table');
      CRM_Core_DAO_AllCoreTables::invoke(__CLASS__, 'links_callback', Civi::$statics[__CLASS__]['links']);
    }
    return Civi::$statics[__CLASS__]['links'];
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
          'description' => E::ts('Unique CampagnodonTransactionLink ID'),
          'required' => TRUE,
          'where' => 'civicrm_campagnodon_transaction_link.id',
          'table_name' => 'civicrm_campagnodon_transaction_link',
          'entity' => 'CampagnodonTransactionLink',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'campagnodon_tid' => [
          'name' => 'campagnodon_tid',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Campagnodon Tid'),
          'description' => E::ts('FK to CampagnodonTransaction'),
          'required' => TRUE,
          'where' => 'civicrm_campagnodon_transaction_link.campagnodon_tid',
          'table_name' => 'civicrm_campagnodon_transaction_link',
          'entity' => 'CampagnodonTransactionLink',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink',
          'localizable' => 0,
          'FKClassName' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'add' => NULL,
        ],
        'parent_id' => [
          'name' => 'parent_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Parent CampagnodonTransactionLink ID'),
          'description' => E::ts('Optional parent id for this CampagnodonTransactionLink. Used to find the contribution link associated to a membership subscription.'),
          'where' => 'civicrm_campagnodon_transaction_link.parent_id',
          'default' => NULL,
          'table_name' => 'civicrm_campagnodon_transaction_link',
          'entity' => 'CampagnodonTransactionLink',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink',
          'localizable' => 0,
          'FKClassName' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink',
          'add' => NULL,
        ],
        'optional_subscription_name' => [
          'name' => 'optional_subscription_name',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Optional Subscription Name'),
          'description' => E::ts('Optional name for optional subscription. Can be used by APIs.'),
          'required' => FALSE,
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'where' => 'civicrm_campagnodon_transaction_link.optional_subscription_name',
          'default' => NULL,
          'table_name' => 'civicrm_campagnodon_transaction_link',
          'entity' => 'CampagnodonTransactionLink',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink',
          'localizable' => 0,
          'add' => NULL,
        ],
        'entity_table' => [
          'name' => 'entity_table',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Entity table'),
          'description' => E::ts('Table of the linked object'),
          'required' => TRUE,
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'where' => 'civicrm_campagnodon_transaction_link.entity_table',
          'table_name' => 'civicrm_campagnodon_transaction_link',
          'entity' => 'CampagnodonTransactionLink',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink',
          'localizable' => 0,
          'add' => NULL,
        ],
        'entity_id' => [
          'name' => 'entity_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Entity ID'),
          'description' => E::ts('ID of the linked object. Can be null if the object is not created in pending state.'),
          'required' => FALSE,
          'where' => 'civicrm_campagnodon_transaction_link.entity_id',
          'default' => NULL,
          'table_name' => 'civicrm_campagnodon_transaction_link',
          'entity' => 'CampagnodonTransactionLink',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink',
          'localizable' => 0,
          'add' => NULL,
        ],
        'on_complete' => [
          'name' => 'on_complete',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => E::ts('On complete'),
          'description' => E::ts('Only when entity_table=\'group\' or \'contact\' or \'tag\'. If true, the contact will be added in group only when transaction is complete.'),
          'where' => 'civicrm_campagnodon_transaction_link.on_complete',
          'default' => 'false',
          'table_name' => 'civicrm_campagnodon_transaction_link',
          'entity' => 'CampagnodonTransactionLink',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink',
          'localizable' => 0,
          'add' => NULL,
        ],
        'total_amount' => [
          'name' => 'total_amount',
          'type' => CRM_Utils_Type::T_MONEY,
          'title' => E::ts('Total Amount'),
          'description' => E::ts('Only when entity_table=\'contribution\'. Total amount of this contribution.'),
          'required' => FALSE,
          'precision' => [
            20,
            2,
          ],
          'where' => 'civicrm_campagnodon_transaction_link.total_amount',
          'default' => NULL,
          'table_name' => 'civicrm_campagnodon_transaction_link',
          'entity' => 'CampagnodonTransactionLink',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink',
          'localizable' => 0,
          'add' => NULL,
        ],
        'currency' => [
          'name' => 'currency',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Currency'),
          'description' => E::ts('Only when entity_table=\'contribution\'. 3 character string, value from config setting or input via user.'),
          'maxlength' => 3,
          'size' => CRM_Utils_Type::FOUR,
          'where' => 'civicrm_campagnodon_transaction_link.currency',
          'default' => NULL,
          'table_name' => 'civicrm_campagnodon_transaction_link',
          'entity' => 'CampagnodonTransactionLink',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink',
          'localizable' => 0,
          'pseudoconstant' => [
            'table' => 'civicrm_currency',
            'keyColumn' => 'name',
            'labelColumn' => 'full_name',
            'nameColumn' => 'name',
            'abbrColumn' => 'symbol',
          ],
          'add' => NULL,
        ],
        'financial_type_id' => [
          'name' => 'financial_type_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Financial Type ID'),
          'description' => E::ts('Only when entity_table=\'contribution\'. FK to Financial Type.'),
          'required' => FALSE,
          'where' => 'civicrm_campagnodon_transaction_link.financial_type_id',
          'default' => NULL,
          'table_name' => 'civicrm_campagnodon_transaction_link',
          'entity' => 'CampagnodonTransactionLink',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink',
          'localizable' => 0,
          'pseudoconstant' => [
            'table' => 'civicrm_financial_type',
            'keyColumn' => 'id',
            'labelColumn' => 'name',
          ],
          'add' => NULL,
        ],
        'membership_type_id' => [
          'name' => 'membership_type_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Membership Type ID'),
          'description' => E::ts('Only when entity_table=\'membership\'. FK to Membership Type.'),
          'required' => FALSE,
          'where' => 'civicrm_campagnodon_transaction_link.membership_type_id',
          'default' => NULL,
          'table_name' => 'civicrm_campagnodon_transaction_link',
          'entity' => 'CampagnodonTransactionLink',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink',
          'localizable' => 0,
          'pseudoconstant' => [
            'table' => 'civicrm_membership_type',
            'keyColumn' => 'id',
            'labelColumn' => 'name',
          ],
          'add' => NULL,
        ],
        'opt_in' => [
          'name' => 'opt_in',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Opt-in'),
          'description' => E::ts('An opt-in action to do on the contact (or membership).'),
          'maxlength' => 25,
          'size' => CRM_Utils_Type::MEDIUM,
          'where' => 'civicrm_campagnodon_transaction_link.opt_in',
          'default' => NULL,
          'table_name' => 'civicrm_campagnodon_transaction_link',
          'entity' => 'CampagnodonTransactionLink',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink',
          'localizable' => 0,
          'pseudoconstant' => [
            'callback' => 'CRM_CampagnodonCivicrm_BAO_CampagnodonTransaction::optInTables',
          ],
          'add' => NULL,
        ],
        'cancelled' => [
          'name' => 'cancelled',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Cancelled'),
          'description' => E::ts('Some links can be cancelled. This field contains a keyword to describe the reason. Example: membership already exists.'),
          'maxlength' => 20,
          'size' => CRM_Utils_Type::MEDIUM,
          'where' => 'civicrm_campagnodon_transaction_link.cancelled',
          'default' => NULL,
          'table_name' => 'civicrm_campagnodon_transaction_link',
          'entity' => 'CampagnodonTransactionLink',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransactionLink',
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
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'campagnodon_transaction_link', $prefix, []);
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
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'campagnodon_transaction_link', $prefix, []);
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
      'index_entity_table_entity_id' => [
        'name' => 'index_entity_table_entity_id',
        'field' => [
          0 => 'entity_table',
          1 => 'entity_id',
        ],
        'localizable' => FALSE,
        'sig' => 'civicrm_campagnodon_transaction_link::0::entity_table::entity_id',
      ],
      'index_cancelled' => [
        'name' => 'index_cancelled',
        'field' => [
          0 => 'cancelled',
        ],
        'localizable' => FALSE,
        'sig' => 'civicrm_campagnodon_transaction_link::0::cancelled',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
