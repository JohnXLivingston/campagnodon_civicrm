<?php

/**
 * @package CRM
 * @copyright CiviCRM LLC https://civicrm.org/licensing
 *
 * Generated from campagnodon_civicrm/xml/schema/CRM/CampagnodonCivicrm/CampagnodonTransaction.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 * (GenCodeChecksum:a06b7bcd281dbd8b00074b6fde44947f)
 */
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Database access object for the CampagnodonTransaction entity.
 */
class CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;
  const TABLE_ADDED = '';

  /**
   * Static instance to hold the table name.
   *
   * @var string
   */
  public static $_tableName = 'civicrm_campagnodon_transaction';

  /**
   * Should CiviCRM log any modifications to this table in the civicrm_log table.
   *
   * @var bool
   */
  public static $_log = TRUE;

  /**
   * Unique CampagnodonTransaction ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $id;

  /**
   * The campagnodon key as given by the origin system (SPIP, ...). A string like: spip/12345.
   *
   * @var string
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $idx;

  /**
   * The status of the transaction.
   *
   * @var string
   *   (SQL type: varchar(20))
   *   Note that values will be retrieved from the database as a string.
   */
  public $status;

  /**
   * The url to pay the subscriptions.
   *
   * @var string|null
   *   (SQL type: varchar(255))
   *   Note that values will be retrieved from the database as a string.
   */
  public $payment_url;

  /**
   * FK to Contact
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $contact_id;

  /**
   * The campaign for which this Campagnodon transaction is attached.
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $campaign_id;

  /**
   * Email address
   *
   * @var string|null
   *   (SQL type: varchar(254))
   *   Note that values will be retrieved from the database as a string.
   */
  public $email;

  /**
   * Prefix or Title for name (Ms, Mr...). FK to prefix ID
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $prefix_id;

  /**
   * First Name.
   *
   * @var string|null
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $first_name;

  /**
   * Last Name.
   *
   * @var string|null
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $last_name;

  /**
   * Date of birth
   *
   * @var string|null
   *   (SQL type: date)
   *   Note that values will be retrieved from the database as a string.
   */
  public $birth_date;

  /**
   * Concatenation of all routable street address components (prefix, street number, street name, suffix, unit
   * number OR P.O. Box). Apps should be able to determine physical location with this data (for mapping, mail
   * delivery, etc.).
   *
   * @var string|null
   *   (SQL type: varchar(96))
   *   Note that values will be retrieved from the database as a string.
   */
  public $street_address;

  /**
   * Store both US (zip5) AND international postal codes. App is responsible for country/region appropriate validation.
   *
   * @var string|null
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $postal_code;

  /**
   * City, Town or Village Name.
   *
   * @var string|null
   *   (SQL type: varchar(64))
   *   Note that values will be retrieved from the database as a string.
   */
  public $city;

  /**
   * Which Country does this address belong to.
   *
   * @var int|string|null
   *   (SQL type: int unsigned)
   *   Note that values will be retrieved from the database as a string.
   */
  public $country_id;

  /**
   * Complete phone number.
   *
   * @var string|null
   *   (SQL type: varchar(32))
   *   Note that values will be retrieved from the database as a string.
   */
  public $phone;

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->__table = 'civicrm_campagnodon_transaction';
    parent::__construct();
  }

  /**
   * Returns localized title of this entity.
   *
   * @param bool $plural
   *   Whether to return the plural version of the title.
   */
  public static function getEntityTitle($plural = FALSE) {
    return $plural ? E::ts('Campagnodon Transactions') : E::ts('Campagnodon Transaction');
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
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'contact_id', 'civicrm_contact', 'id');
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'campaign_id', 'civicrm_campaign', 'id');
      Civi::$statics[__CLASS__]['links'][] = new CRM_Core_Reference_Basic(self::getTableName(), 'country_id', 'civicrm_country', 'id');
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
          'description' => E::ts('Unique CampagnodonTransaction ID'),
          'required' => TRUE,
          'where' => 'civicrm_campagnodon_transaction.id',
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'html' => [
            'type' => 'Number',
          ],
          'readonly' => TRUE,
          'add' => NULL,
        ],
        'idx' => [
          'name' => 'idx',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Idx'),
          'description' => E::ts('The campagnodon key as given by the origin system (SPIP, ...). A string like: spip/12345.'),
          'required' => FALSE,
          'maxlength' => 255,
          'size' => 30,
          'where' => 'civicrm_campagnodon_transaction.idx',
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
            'label' => E::ts("External key"),
          ],
          'add' => NULL,
        ],
        'status' => [
          'name' => 'status',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Status'),
          'description' => E::ts('The status of the transaction.'),
          'required' => TRUE,
          'maxlength' => 20,
          'size' => CRM_Utils_Type::MEDIUM,
          'where' => 'civicrm_campagnodon_transaction.status',
          'default' => 'init',
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'callback' => 'CRM_CampagnodonCivicrm_BAO_CampagnodonTransaction::statusTables',
          ],
          'add' => NULL,
        ],
        'payment_url' => [
          'name' => 'payment_url',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Payment Url'),
          'description' => E::ts('The url to pay the subscriptions.'),
          'maxlength' => 255,
          'size' => 60,
          'where' => 'civicrm_campagnodon_transaction.payment_url',
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
            'label' => E::ts("Payment url"),
          ],
          'add' => NULL,
        ],
        'contact_id' => [
          'name' => 'contact_id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => E::ts('FK to Contact'),
          'where' => 'civicrm_campagnodon_transaction.contact_id',
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'FKClassName' => 'CRM_Contact_DAO_Contact',
          'html' => [
            'type' => 'EntityRef',
            'label' => E::ts("Contact"),
          ],
          'add' => NULL,
        ],
        'campaign_id' => [
          'name' => 'campaign_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Campaign ID'),
          'description' => E::ts('The campaign for which this Campagnodon transaction is attached.'),
          'where' => 'civicrm_campagnodon_transaction.campaign_id',
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'FKClassName' => 'CRM_Campaign_DAO_Campaign',
          'component' => 'CiviCampaign',
          'html' => [
            'type' => 'EntityRef',
            'label' => E::ts("Campaign"),
          ],
          'pseudoconstant' => [
            'table' => 'civicrm_campaign',
            'keyColumn' => 'id',
            'labelColumn' => 'title',
            'prefetch' => 'FALSE',
          ],
          'add' => NULL,
        ],
        'email' => [
          'name' => 'email',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Email'),
          'description' => E::ts('Email address'),
          'maxlength' => 254,
          'size' => 30,
          'where' => 'civicrm_campagnodon_transaction.email',
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
          'add' => NULL,
        ],
        'prefix_id' => [
          'name' => 'prefix_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Individual Prefix'),
          'description' => E::ts('Prefix or Title for name (Ms, Mr...). FK to prefix ID'),
          'where' => 'civicrm_campagnodon_transaction.prefix_id',
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'html' => [
            'type' => 'Select',
          ],
          'pseudoconstant' => [
            'optionGroupName' => 'individual_prefix',
            'optionEditPath' => 'civicrm/admin/options/individual_prefix',
          ],
          'add' => NULL,
        ],
        'first_name' => [
          'name' => 'first_name',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('First Name'),
          'description' => E::ts('First Name.'),
          'maxlength' => 64,
          'size' => 30,
          'where' => 'civicrm_campagnodon_transaction.first_name',
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
            'label' => E::ts("First Name"),
          ],
          'add' => NULL,
        ],
        'last_name' => [
          'name' => 'last_name',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Last Name'),
          'description' => E::ts('Last Name.'),
          'maxlength' => 64,
          'size' => 30,
          'where' => 'civicrm_campagnodon_transaction.last_name',
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
            'label' => E::ts("Last Name"),
          ],
          'add' => NULL,
        ],
        'birth_date' => [
          'name' => 'birth_date',
          'type' => CRM_Utils_Type::T_DATE,
          'title' => E::ts('Birth Date'),
          'description' => E::ts('Date of birth'),
          'where' => 'civicrm_campagnodon_transaction.birth_date',
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'html' => [
            'type' => 'Select Date',
            'formatType' => 'birth',
            'label' => E::ts("Birth Date"),
          ],
          'add' => NULL,
        ],
        'street_address' => [
          'name' => 'street_address',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Street Address'),
          'description' => E::ts('Concatenation of all routable street address components (prefix, street number, street name, suffix, unit
      number OR P.O. Box). Apps should be able to determine physical location with this data (for mapping, mail
      delivery, etc.).'),
          'maxlength' => 96,
          'size' => CRM_Utils_Type::HUGE,
          'import' => TRUE,
          'where' => 'civicrm_campagnodon_transaction.street_address',
          'export' => TRUE,
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
          'add' => NULL,
        ],
        'postal_code' => [
          'name' => 'postal_code',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Postal Code'),
          'description' => E::ts('Store both US (zip5) AND international postal codes. App is responsible for country/region appropriate validation.'),
          'maxlength' => 64,
          'size' => 6,
          'import' => TRUE,
          'where' => 'civicrm_campagnodon_transaction.postal_code',
          'export' => TRUE,
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
          'add' => NULL,
        ],
        'city' => [
          'name' => 'city',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('City'),
          'description' => E::ts('City, Town or Village Name.'),
          'maxlength' => 64,
          'size' => CRM_Utils_Type::BIG,
          'import' => TRUE,
          'where' => 'civicrm_campagnodon_transaction.city',
          'export' => TRUE,
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
          ],
          'add' => NULL,
        ],
        'country_id' => [
          'name' => 'country_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => E::ts('Country ID'),
          'description' => E::ts('Which Country does this address belong to.'),
          'where' => 'civicrm_campagnodon_transaction.country_id',
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'localize_context' => 'country',
          'FKClassName' => 'CRM_Core_DAO_Country',
          'html' => [
            'type' => 'Select',
            'label' => E::ts("Country"),
          ],
          'pseudoconstant' => [
            'table' => 'civicrm_country',
            'keyColumn' => 'id',
            'labelColumn' => 'name',
            'nameColumn' => 'iso_code',
            'abbrColumn' => 'iso_code',
          ],
          'add' => NULL,
        ],
        'phone' => [
          'name' => 'phone',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => E::ts('Phone'),
          'description' => E::ts('Complete phone number.'),
          'maxlength' => 32,
          'size' => CRM_Utils_Type::MEDIUM,
          'import' => TRUE,
          'where' => 'civicrm_campagnodon_transaction.phone',
          'export' => TRUE,
          'table_name' => 'civicrm_campagnodon_transaction',
          'entity' => 'CampagnodonTransaction',
          'bao' => 'CRM_CampagnodonCivicrm_DAO_CampagnodonTransaction',
          'localizable' => 0,
          'html' => [
            'type' => 'Text',
            'label' => E::ts("Phone"),
          ],
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
    $r = CRM_Core_DAO_AllCoreTables::getImports(__CLASS__, 'campagnodon_transaction', $prefix, []);
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
    $r = CRM_Core_DAO_AllCoreTables::getExports(__CLASS__, 'campagnodon_transaction', $prefix, []);
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
      'index_idx' => [
        'name' => 'index_idx',
        'field' => [
          0 => 'idx',
        ],
        'localizable' => FALSE,
        'unique' => TRUE,
        'sig' => 'civicrm_campagnodon_transaction::1::idx',
      ],
    ];
    return ($localize && !empty($indices)) ? CRM_Core_DAO_AllCoreTables::multilingualize(__CLASS__, $indices) : $indices;
  }

}
