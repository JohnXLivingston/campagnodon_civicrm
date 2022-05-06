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
      "title" => ts("Donation external identifier"),
      "description" => "Unique identifier",
      "type" => CRM_Utils_Type::T_STRING,
      "api.default" => "",
  ];
  $spec["campaign_id"] = [
      "name" => "campaign_id",
      "title" => ts("Campaign ID"),
      "description" => "Unique campaign id",
      "type" => CRM_Utils_Type::T_INT,
      "api.default" => "",
  ];
  $spec["donation_amount"] = [
      "title" => "Donation amount",
      "type" => CRM_Utils_Type::T_INT,
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
    $donation_amount = intval($params['donation_amount'] ?? 0);
    if ($donation_amount <= 0) {
      throw new API_Exception('No donation');
    }
    // TODO: implement other type of subscriptions

    // We need to found or create the contact.
    $contacts = civicrm_api3('Contact', 'get', array(
      'email' => $params['email'],
      'sequential' => true
    ));

    $contact = null;
    // TODO: avoid update existing contact (in case paiment is not valid, to avoid database corruption attacks)
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

    // Now that we have a contact, we can make a donation.
    if ($donation_amount > 0) {
      // $contribution = civicrm_api3("contribution", "create", [
      //   "financial_type_id" => 2,
      //   "contact_id" => $params["contact_id"],
      //   "total_amount" => $params["amount"] - 12,
      // ]);
    }

    $result = [
      'contact' => $contact
    ];
  } catch (Exception $e) {
    $tx->rollback();
    throw $e;
  }
  $tx->commit();

  return civicrm_api3_create_success($result, $params, 'Campagnodon', 'create');
}
