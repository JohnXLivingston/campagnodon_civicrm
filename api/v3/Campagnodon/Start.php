<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Campagnodon.Start API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_campagnodon_Start_spec(&$spec) {
  $spec["campagnodon_version"] = [
    "name" => "prefix",
    "title" => ts("Campagnodon API version"),
    "description" => ts("Campagnodon API version"),
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 1,
    "api.default" => "",
  ];
  $spec["prefix"] = [
    "name" => "prefix",
    "title" => ts("Individual Prefix"),
    "description" => ts("Individual Prefix"),
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 0,
    "api.default" => "",
  ];
  $spec["first_name"] = [
    "name" => "first_name",
    "title" => ts("First name"),
    "description" => ts("First name"),
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 0,
    "api.default" => "",
  ];
  $spec["last_name"] = [
      "name" => "last_name",
      "title" => ts("Last name"),
      "description" => ts("Last name"),
      "type" => CRM_Utils_Type::T_STRING,
      "api.required" => 0,
      "api.default" => "",
  ];
  $spec["tax_receipt"] = [
    "name" => "tax_receipt",
    "title" => "Tax Receipt",
    "description" => "Send a tax receipt",
    "type" => CRM_Utils_Type::T_BOOLEAN,
    "api.required" => 0
  ];
  $spec["email"] = [
      "name" => "email",
      "title" => ts("E-mail"),
      "description" => ts("E-mail"),
      "type" => CRM_Utils_Type::T_STRING,
      "api.required" => 1,
      "api.default" => "",
  ];
  $spec["source"] = [
    "name" => "source",
    "title" => ts("Source"),
    "description" => ts("Source"),
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 0,
    "api.default" => "",
];
  $spec["postal_code"] = [
      "name" => "postal_code",
      "title" => ts("Postal code"),
      "description" => ts("Postal code"),
      "type" => CRM_Utils_Type::T_STRING,
      "api.required" => 0,
      "api.default" => "",
  ];
  $spec["country"] = [
      "name" => "country",
      "title" => ts("Country"),
      "description" => "Country ISO code",
      "type" => CRM_Utils_Type::T_STRING,
      "api.required" => 0,
      "api.default" => "",
  ];
  $spec["phone"] = [
      "name" => "phone",
      "title" => ts("Phone"),
      "description" => "Phone",
      "type" => CRM_Utils_Type::T_STRING,
      "api.required" => 0,
      "api.default" => "",
  ];
  $spec["birth_date"] = [
    "name" => "birth_date",
    "title" => ts("Birth Date"),
    "description" => "Date of birth",
    "type" => CRM_Utils_Type::T_DATE,
    "api.required" => 0,
    "api.default" => "",
  ];
  $spec["transaction_idx"] = [
      "name" => "transaction_idx",
      "title" => ts("External identifier"),
      "description" => "Unique identifier",
      "type" => CRM_Utils_Type::T_STRING,
      "api.required" => 1,
      "api.default" => "",
  ];
  $spec["operation_type"] = [
    "name" => "operation_type",
    "title" => ts("Operation Type"),
    "description" => "Operation Type",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 1,
    "api.default" => "",
  ];
  $spec["payment_url"] = [
    "name" => "payment_url",
    "title" => ts("Payment Url"),
    "description" => "Payment Url",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 0,
    "api.default" => "",
  ];
  $spec["transaction_url"] = [
    "name" => "transaction_url",
    "title" => ts("Original transaction url"),
    "description" => "Original transaction url",
    "type" => CRM_Utils_Type::T_STRING,
    "api.required" => 0,
    "api.default" => "",
  ];
  $spec["campaign_id"] = [
      "name" => "campaign_id",
      "title" => ts("Campaign ID"),
      "description" => "Unique campaign id",
      "type" => CRM_Utils_Type::T_INT,
      "api.required" => 0,
      "api.default" => "",
  ];
  $spec["contributions"] = [
      "title" => "Contributions",
      "api.required" => 1,
  ];
  $spec["optional_subscriptions"] = [
    "title" => ts("Optional subscriptions"),
    "description" => "Optional subscriptions to add",
    "api.required" => 0,
  ];
  $spec["is_recurring"] = [
    "name" => "is_recurring",
    "title" => "Is Recurring",
    "description" => "true if this is a recurring transaction (only for the parent)",
    "type" => CRM_Utils_Type::T_BOOLEAN,
    "api.required" => 0
  ];
}

/**
 * Campagnodon.Start API
 *
 * Used to start a new donation/subscription/[...] process.
 * Creates a CampagnodonTransaction.
 * Creates the contact if not found.
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
function civicrm_api3_campagnodon_Start($params) {
  $tx = new CRM_Core_Transaction();
  try {
    // checking API version
    if ($params['campagnodon_version'] !== '1') {
      throw new API_Exception("Unkwnown API version '".($params['campagnodon_version'] ?? '')."'");
    }
    // checking if there is at least one type of donation, membership, ...
    $contributions_params = $params['contributions'];
    CRM_CampagnodonCivicrm_Logic_Transactions::checkContributionsParams($contributions_params);

    if (false === filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
      throw new API_Exception('Invalid email');
    }
    if (!empty($params['payment_url']) && false === filter_var($params['payment_url'], FILTER_VALIDATE_URL)) {
      throw new API_Exception('Invalid payment_url');
    }
    if (!empty($params['transaction_url']) && false === filter_var($params['transaction_url'], FILTER_VALIDATE_URL)) {
      throw new API_Exception('Invalid transaction_url');
    }

    // We need to found or create the contact.
    $contact = null;

    $dedupe_contact = CRM_CampagnodonCivicrm_Logic_Dedupe_Contact::init($params);
    $tax_receipt = $dedupe_contact->getWithTaxReceipt(); // just to be sure to have the same value as CRM_CampagnodonCivicrm_Logic_Dedupe_Contact
    $contact = $dedupe_contact->getContact();
    $is_new_contact = $dedupe_contact->isNewContact();

    if (empty($contact)) {
      throw new API_Exception('Failed to get or create the contact.');
    }

    $transaction_create = \Civi\Api4\CampagnodonTransaction::create();
    $transaction_create->setCheckPermissions(false);
    $transaction_create->addValue('contact_id', $contact['id']);
    $transaction_create->addValue('email', $params['email']);
    if (!empty($params['source'])) {
      $transaction_create->addValue('source', $params['source']); // TODO: add some unit test for this field.
    }
    $transaction_create->addValue('idx', $params['transaction_idx']);
    $transaction_create->addValue('operation_type', $params['operation_type']);
    $transaction_create->addValue('tax_receipt', $tax_receipt);
    $transaction_create->addValue('original_contact_id', $contact['id']); // TODO: add some unit test
    $transaction_create->addValue('new_contact', $is_new_contact); // TODO: add some unit test
    if (array_key_exists('is_recurring', $params) && $params['is_recurring']) {
      $transaction_create->addValue('recurring_status', 'init');
    }
    foreach (
      array(
        'campaign_id',
        'first_name', 'last_name', 'birth_date', 'street_address', 'supplemental_address_1', 'supplemental_address_2', 'postal_code', 'city', 'phone',
        'payment_url', 'transaction_url'
      ) as $field
    ) {
      if (array_key_exists($field, $params) && !empty($params[$field])) {
        $transaction_create->addValue($field, $params[$field]);
      }
    }
    if (array_key_exists('country', $params) && !empty($params['country'])) {
      $transaction_create->addValue('country_id:name', $params['country']);
    }
    if (array_key_exists('prefix', $params) && !empty($params['prefix'])) {
      if (is_numeric($params['prefix'])) {
        $transaction_create->addValue('prefix_id', $params['prefix']);
      } else {
        $transaction_create->addValue('prefix_id:name', $params['prefix']);
      }
    }
    $transaction_result = $transaction_create->execute();
    $transaction = $transaction_result->single();

    $pending_contribution_status = Civi::settings()->get('campagnodon_contribution_status_pending');
    if (!$pending_contribution_status) {
      throw new Exception('Cant find contribution_status for "pending".');
    }
    $pending_contribution_status = intval($pending_contribution_status); // just in case...

    // Now that we have a contact, we can make contributions.
    CRM_CampagnodonCivicrm_Logic_Transactions::createContributionsFromParams($transaction, $contributions_params);

    // And now, optional_subscriptions!
    $optional_subscriptions = $params['optional_subscriptions'] ?? array();
    foreach ($optional_subscriptions as $optional_subscription) {
      $optional_subscription_name = array_key_exists('name', $optional_subscription) && !empty($optional_subscription['name']) ? $optional_subscription['name'] : null;
      if ($optional_subscription['type'] === 'group') {
        $group_key = $optional_subscription['key'];
        $group_field = is_numeric($optional_subscription['key']) ? 'id' : 'name';
        $group = \Civi\Api4\Group::get()
          ->setCheckPermissions(false)
          ->addWhere($group_field, '=', $group_key)
          ->execute()
          ->single();

        $on_complete = $optional_subscription['when'] === 'completed';
        $link = \Civi\Api4\CampagnodonTransactionLink::create()
          ->setCheckPermissions(false)
          ->addValue('campagnodon_tid', $transaction['id'])
          ->addValue('optional_subscription_name', $optional_subscription_name)
          ->addValue('entity_table', 'civicrm_group')
          ->addValue('entity_id', $group['id'])
          ->addValue('on_complete', $on_complete)
          ->execute()
          ->single();
      } else if ($optional_subscription['type'] === 'opt-in') {
        if (!CRM_CampagnodonCivicrm_BAO_CampagnodonTransaction::isOptInValid($optional_subscription['key'])) {
          throw new Exception('Invalid opt-in optional_subscriptions: "'.$optional_subscription['key'].'"');
        }
        $on_complete = $optional_subscription['when'] === 'completed';
        $link = \Civi\Api4\CampagnodonTransactionLink::create()
          ->setCheckPermissions(false)
          ->addValue('campagnodon_tid', $transaction['id'])
          ->addValue('optional_subscription_name', $optional_subscription_name)
          ->addValue('entity_table', 'civicrm_contact')
          ->addValue('entity_id', $contact['id'])
          ->addValue('on_complete', $on_complete)
          ->addValue('opt_in', $optional_subscription['key'])
          ->execute()
          ->single();
      } else if ($optional_subscription['type'] === 'tag') {
        // FIXME: add some unit tests for tags.
        $tag_key = $optional_subscription['key'];
        $tag_field = is_numeric($optional_subscription['key']) ? 'id' : 'name';
        $tag = \Civi\Api4\Tag::get()
          ->setCheckPermissions(false)
          ->addWhere($tag_field, '=', $tag_key)
          ->execute()
          ->single();

        $on_complete = $optional_subscription['when'] === 'completed';
        $link = \Civi\Api4\CampagnodonTransactionLink::create()
          ->setCheckPermissions(false)
          ->addValue('campagnodon_tid', $transaction['id'])
          ->addValue('optional_subscription_name', $optional_subscription_name)
          ->addValue('entity_table', 'civicrm_tag')
          ->addValue('entity_id', $tag['id'])
          ->addValue('on_complete', $on_complete)
          ->execute()
          ->single();
      } else {
        throw new Exception('Invalid optional_subscriptions type: "'.$optional_subscription['type'].'"');
      }
    }

    CRM_CampagnodonCivicrm_Logic_Contact::processLinks($contact['id'], $transaction['id'], 'init');

  } catch (Throwable $e) {
    Civi::log()->warning(__METHOD__.' got a throwable: '.$e->getMessage());
    Civi::log()->debug(__METHOD__.' Stack trace: '.$e->getTraceAsString());
    Civi::log()->warning(__METHOD__.' rollbacking...');
    $tx->rollback();
    throw $e;
  }
  $tx->commit();

  return civicrm_api3_create_success(array($transaction['id'] => array(
    'id' => $transaction['id'],
    'status' => 'init',
    'recurring_status' => array_key_exists('is_recurring', $params) && $params['is_recurring'] ? 'init' : null
  )), $params);
}
