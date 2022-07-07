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
  $spec["payment_url"] = [
    "name" => "payment_url",
    "title" => ts("Payment url"),
    "description" => "Payment url",
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
    if (!is_array($contributions_params)) {
      throw new API_Exception('Missing contributions');
    }
    foreach ($contributions_params as $key => $contribution_params) {
      if (!is_array($contribution_params)) {
        throw new API_Exception('Invalid contributions '.$key);
      }
      $amount = intval($contribution_params['amount'] ?? 0);
      if ($amount <= 0) {
        throw new API_Exception('Invalid amount for contribution '.$key);
      }
    }

    if (false === filter_var($params['email'], FILTER_VALIDATE_EMAIL)) {
      throw new API_Exception('Invalid email');
    }
    if (!empty($params['payment_url']) && false === filter_var($params['payment_url'], FILTER_VALIDATE_URL)) {
      throw new API_Exception('Invalid payment_url');
    }

    // We need to found or create the contact.
    $contact = null;

    $tax_receipt = !empty($params['tax_receipt']) && $params['tax_receipt'];
    $dedupe_contact = CRM_CampagnodonCivicrm_Logic_Dedupe_Contact::init($params);
    if ($tax_receipt) {
      $dedupe_contact->withTaxReceipt();
    }
    $contact = $dedupe_contact->getContact();
    $is_new_contact = $dedupe_contact->isNewContact();

    if (empty($contact)) {
      throw new API_Exception('Failed to get or create the contact.');
    }

    $transaction_create = \Civi\Api4\CampagnodonTransaction::create();
    $transaction_create->addValue('contact_id', $contact['id']);
    $transaction_create->addValue('email', $params['email']);
    $transaction_create->addValue('idx', $params['transaction_idx']);
    $transaction_create->addValue('tax_receipt', $tax_receipt);
    $transaction_create->addValue('original_contact_id', $contact['id']); // TODO: add some unit test
    $transaction_create->addValue('new_contact', $is_new_contact); // TODO: add some unit test
    foreach (
      array(
        'campaign_id',
        'first_name', 'last_name', 'birth_date', 'street_address', 'postal_code', 'city', 'phone',
        'payment_url'
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
    foreach ($contributions_params as $key => $contribution_params) {
      $link_create = \Civi\Api4\CampagnodonTransactionLink::create()
        ->addValue('campagnodon_tid', $transaction['id'])
        ->addValue('entity_table', 'civicrm_contribution')
        ->addValue('entity_id', null)
        ->addValue('total_amount', $contribution_params['amount'])
        ->addValue('currency', $contribution_params['currency'])
        ->addValue(
          is_numeric($contribution_params['financial_type']) ? 'financial_type_id' : 'financial_type_id:name',
          $contribution_params['financial_type']
        );
      if (array_key_exists('membership', $contribution_params) && !empty($contribution_params['membership'])) {
        $link_create->addValue(
          is_numeric($contribution_params['membership']) ? 'membership_type_id': 'membership_type_id:name',
          $contribution_params['membership']
        );
      }
      $link = $link_create->execute()->single();
    }

    // And now, optional_subscriptions!
    $optional_subscriptions = $params['optional_subscriptions'] ?? array();
    foreach ($optional_subscriptions as $optional_subscription) {
      if ($optional_subscription['type'] === 'group') {
        $group_key = $optional_subscription['key'];
        $group_field = is_numeric($optional_subscription['key']) ? 'id' : 'name';
        $group = \Civi\Api4\Group::get()
          ->addWhere($group_field, '=', $group_key)
          ->execute()
          ->single();

        $on_complete = $optional_subscription['when'] === 'completed';
        $link = \Civi\Api4\CampagnodonTransactionLink::create()
          ->addValue('campagnodon_tid', $transaction['id'])
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
          ->addValue('campagnodon_tid', $transaction['id'])
          ->addValue('entity_table', 'civicrm_contact')
          ->addValue('entity_id', $contact['id'])
          ->addValue('on_complete', $on_complete)
          ->addValue('opt_in', $optional_subscription['key'])
          ->execute()
          ->single();
      } else {
        throw new Exception('Invalid optional_subscriptions type: "'.$optional_subscription['type'].'"');
      }
    }

    CRM_CampagnodonCivicrm_Logic_Contact::processLinks($contact['id'], $transaction['id'], 'init');

  } catch (Throwable $e) {
    $tx->rollback();
    throw $e;
  }
  $tx->commit();

  return civicrm_api3_create_success(array($transaction['id'] => $transaction), $params, 'Campagnodon', 'transaction');
}
