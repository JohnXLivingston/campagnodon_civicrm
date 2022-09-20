<?php

use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_Token_CampagnodonTransaction extends \Civi\Token\AbstractTokenSubscriber {
  public function __construct() {
    parent::__construct('campagnodonTransaction', [
      'payment_link' => E::ts('Payment Link'),
      'email' => E::ts('Email address'),
      'first_name' => E::ts('First Name'),
      'last_name' => E::ts('Last Name')
    ]);
  }

  public function checkActive(\Civi\Token\TokenProcessor $processor) {
    return !empty($processor->context['campagnodonTransactionId'])
      || !empty($processor->context['campagnodonTransaction'])
      || in_array('campagnodonTransactionId', $processor->context['schema'])
      || in_array('campagnodonTransaction', $processor->context['schema']);
  }

  public function evaluateToken(
    \Civi\Token\TokenRow $row,
    $entity,
    $field,
    $prefetch = NULL
  ) {

    if (empty($row->context['campagnodonTransaction'])) {
      Civi::log()->debug(__CLASS__.'::'.__METHOD__ . ' There is no campagnodonTransaction in the context, you cant use campagnodonTransaction tokens.');
      $row->format('text/plain')->tokens($entity, $field, '');
      return;
    }

    if ($field === 'payment_link') {
      $url = $row->context['campagnodonTransaction']['payment_url'] ?? '';
      $link = '<a href="'.$url.'">'.htmlspecialchars($url).'</a>';
      $row->format('text/plain')->tokens($entity, $field, $url);
      $row->format('text/html')->tokens($entity, $field, $link);
      return;
    }
    
    $value = '';
    if (in_array($field, ['email', 'first_name', 'last_name'])) {
      // For these fields, it can have been cleaned from the CampagnodonTransaction.
      // In such cache, we must search on the contact data.
      $value = $row->context['campagnodonTransaction'][$field];
      if (empty($value) && !empty($row->context['contact'])) {
        $value = $row->context['contact'][$field];
      }
      if (empty($value) && !empty($row->context['contactId'])) {
        $contact = \Civi\Api4\Contact::get()
          ->setCheckPermissions(false)
          ->addSelect('*')
          ->addWhere('id', '=', $row->context['contactId'])
          ->execute()
          ->first();
        if ($contact) {
          $value = $contact[$field];
        }
      }
    }

    $row->format('text/plain')->tokens($entity, $field, $value ?? '');
    return;
  }
}
