<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_Page_CampagnodonView extends CRM_Core_Page {

  public function run() {
    // FIXME: add custom permissions for Campagnodon
    if (!CRM_Core_Permission::check('access CiviCRM')) {
      CRM_Core_Error::statusBounce(ts('You do not have permission to access this page.'));
    }

    $id = CRM_Utils_Request::retrieve('id', 'Positive', $this, TRUE);
    $row = \Civi\Api4\CampagnodonTransaction::get()
      ->addSelect('*', 'payment_instrument_id:label', 'campaign_id:label', 'prefix_id:label', 'country_id:label')
      ->addWhere('id', '=', $id)
      ->execute()->single();
    if (!empty($row['contact_id'])) {
      $displayName = CRM_Contact_BAO_Contact::displayName($row['contact_id']);
      $this->assign('displayName', $displayName);
      $row['contact'] = '<a href="'.CRM_Utils_System::url('civicrm/contact/view', ['reset' => 1, 'cid' => $row['contact_id']]).'">'.CRM_Contact_BAO_Contact::displayName($row['contact_id']).'</a>';
    }
    $row['payment_instrument'] = $row['payment_instrument_id:label'];
    if (!empty($row['campaign_id'])) {
      // TODO: lien vers la campagne.
      $row['campaign_title'] = $row['campaign_id:label'];
    }
    if (!empty($row['prefix_id'])) {
      $row['prefix_label'] = $row['prefix_id:label'];
    }
    if (!empty($row['country_id'])) {
      $row['country_label'] = $row['country_id:label'];
    }
    $this->assign('id', $id);
    $this->assign('row', $row);

    $links = \Civi\Api4\CampagnodonTransactionLink::get()
      ->addSelect('*')
      ->addWhere('campagnodon_tid', '=', $id)
      ->addOrderBy('entity_table', 'ASC')
      ->addOrderBy('entity_id', 'ASC')
      ->execute();

    foreach ($links as &$link) {
      if ($link['entity_table'] === 'civicrm_contribution') {
        $contribution = \Civi\Api4\Contribution::get()
          ->addSelect('*')
          ->addWhere('id', '=', $link['entity_id'])
          ->execute()->first();
        if ($contribution) {
          $url = CRM_Utils_System::url('civicrm/contact/view/contribution', [
            'action' => 'view',
            'reset' => 1,
            'id' => $contribution['id'],
            'cid' => $contribution['contact_id']
          ]);
          $link['view'] = '<a href="'
            .htmlspecialchars($url)
            .'">'
            .CRM_Utils_Money::format($contribution['total_amount'], $contribution['currency']) 
            .'</a>';
        } else {
          $link['view'] = '???';
        }
      }
    }

    $this->assign('links', $links);

    parent::run();
  }
}
