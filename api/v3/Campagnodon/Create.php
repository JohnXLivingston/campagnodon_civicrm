<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Campagnodon.Create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_campagnodon_Create_spec(&$spec) {
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
  $spec["transaction_idx"] = [
      "name" => "transaction_idx",
      "title" => ts("External identifier"),
      "description" => "Unique identifier",
      "type" => CRM_Utils_Type::T_STRING,
      "api.required" => 1,
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
 * Campagnodon.Create API
 *
 * Used to create a new donation/subscription/[...].
 * Create the contact if not found, otherwise update it.
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
function civicrm_api3_campagnodon_Create($params) {
  $tx = new CRM_Core_Transaction();
  try {
    // checking if there is at least one type of donation, membership, ...
    $contributions = $params['contributions'];
    if (!is_array($contributions)) {
      throw new API_Exception('Missing contributions');
    }
    foreach ($contributions as $key => $contribution) {
      if (!is_array($contribution)) {
        throw new API_Exception('Invalid contributions '.$key);
      }
      $amount = intval($contribution['amount'] ?? 0);
      if ($amount <= 0) {
        throw new API_Exception('Invalid amount for contribution '.$key);
      }
    }

    // We need to found or create the contact.
    $contacts = civicrm_api3('Contact', 'get', array(
      'email' => $params['email'], // FIXME: use Email.get instead of Contact.get?
      'sequential' => true
    ));
    // TODO: use getDuplicateContacts method?

    $contact = null;
    // TODO: avoid update existing contact (in case payment is not valid, to avoid database corruption attacks)
    if ($contacts['count'] == 0) {
      $contactsBis = civicrm_api3('Contact', 'create', array(
        'email' => $params['email'],
        'first_name' => $params['first_name'],
        'last_name' => $params['last_name'],
        // 'postal_code' => $params['postal_code'],
        // 'country' => $params['country'],
        'contact_type' => 'Individual',
        'sequential' => true,
        'options' => [
          'reload' => true
        ]
      ));
      if ($contactsBis['count'] === 1) {
        $contact = $contactsBis['values'][0];
      }
    } else if ($contacts['count'] === 1) {
      $contact = $contacts['values'][0];
    } else if ($contacts['count'] > 1) {
      throw new API_Exception('Multiple contacts found, deduplication code not ready. FIXME');
    } else {
      throw new API_Exception('Unknown error');
    }

    if (empty($contact)) {
      throw new API_Exception('Failed to get or create the contact.');
    }

    $transaction_create = \Civi\Api4\CampagnodonTransaction::create();
    $transaction_create->addValue('contact_id', $contact['id']);
    $transaction_create->addValue('email', $params['email']);
    $transaction_create->addValue('idx', $params['transaction_idx']);
    foreach (
      array(
        'campaign_id', 'prefix', 'first_name', 'last_name', 'birth_date', 'street_address', 'postal_code', 'city', 'country', 'phone'
      ) as $field
    ) {
      if (array_key_exists($field, $params) && !empty($params[$field])) {
        $transaction_create->addValue($field, $params[$field]);
      }
    }
    $transaction_result = $transaction_create->execute();
    $transaction = $transaction_result->first();

    // Now that we have a contact, we can make contributions.
    foreach ($contributions as $key => $contribution) {
      $contribution = civicrm_api3('Contribution', 'create', array(
        'financial_type_id' => $contribution['financial_type'],
        'contact_id' => $contact['id'],
        'contribution_status_id' => 'Pending',
        'total_amount' => $contribution['amount'],
        // FIXME: following fields?
        // 'receive_date'
      ));
    }

    $result = [
      // 'transaction' => $transaction,
      'contact' => $contact
    ];
  } catch (Exception $e) {
    $tx->rollback();
    throw $e;
  }
  $tx->commit();

  return civicrm_api3_create_success($result, $params, 'Campagnodon', 'create');
}
