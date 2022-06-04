<?php

use CRM_CampagnodonCivicrm_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_CampagnodonCivicrm_Form_Search extends CRM_Core_Form {
  protected $formValues;
  protected $pageId = false;
  protected $offset = 0;
  protected $limit = false;
  public $count = 0;
  public $rows = [];

  public function preProcess() {
    parent::preProcess();
    $this->formValues = $this->getSubmitValues();
    $this->setTitle(E::ts('Campagnodon'));

    $this->limit = CRM_Utils_Request::retrieveValue('crmRowCount', 'Positive', 20);
    $this->pageId = CRM_Utils_Request::retrieveValue('crmPID', 'Positive', 1);
    if ($this->limit !== false) {
      $this->offset = ($this->pageId - 1) * $this->limit;
    }
    $this->query();
    $this->assign('entities', $this->rows);

    $pagerParams = [];
    $pagerParams['total'] = 0;
    $pagerParams['status'] =E::ts('%%StatusMessage%%');
    $pagerParams['csvString'] = NULL;
    $pagerParams['rowCount'] =  20;
    $pagerParams['buttonTop'] = 'PagerTopButton';
    $pagerParams['buttonBottom'] = 'PagerBottomButton';
    $pagerParams['total'] = $this->count;
    $pagerParams['pageID'] = $this->pageId;
    $this->pager = new CRM_Utils_Pager($pagerParams);
    $this->assign('pager', $this->pager);
  }

  public function buildQuickForm() {
    // $this->add(
    //   'text', // field type
    //   'title', // field name
    //   E::ts('Title'), // field label
    //   array('class' => 'huge'), // list of options
    //   TRUE // is required
    // );
    $this->add(
      'select', // field type
      'status', // field name
      E::ts('Status'), // field label
      $this->getStatusOptions(), // list of options
      false // is required
    );
    $this->addEntityRef(
      'contact_id',
      E::ts('Contact'),
      ['entity' => 'Contact', 'create' => false, 'multiple' => true],
      false
    );
    $this->add(
      'text',
      'idx',
      E::ts('Campagnodon IDX'),
      array('class' => 'huge'),
      false
    );

    $this->addButtons(array(
      array(
        'type' => 'refresh',
        'name' => E::ts('Search'),
        'isDefault' => TRUE,
      ),
    ));

    // // add form elements
    // $this->add(
    //   'select', // field type
    //   'favorite_color', // field name
    //   'Favorite Color', // field label
    //   $this->getColorOptions(), // list of options
    //   TRUE // is required
    // );
    // $this->addButtons(array(
    //   array(
    //     'type' => 'submit',
    //     'name' => E::ts('Submit'),
    //     'isDefault' => TRUE,
    //   ),
    // ));

    // // export form elements
    // $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  public function postProcess() {
    parent::postProcess();
  }

  public function getStatusOptions() {
    $statuses = CRM_CampagnodonCivicrm_BAO_CampagnodonTransaction::statusTables();
    $options = array(
      '' => E::ts('- select -'),
    );
    $options = array_merge($options, $statuses);
    return $options;
  }

  public function query() {
    $api = Civi\Api4\CampagnodonTransaction::get()
      ->selectRowCount()
      ->addSelect('*')
      ->addOrderBy('id', 'DESC');
    if ($this->limit !== false) {
      $api->setLimit($this->limit);
      $api->setOffset($this->offset);
    }

    if (isset($this->formValues['status']) && !empty($this->formValues['status'])) {
      $api->addWhere('status', '=', $this->formValues['status']);
    }
    if (isset($this->formValues['idx']) && !empty($this->formValues['idx'])) {
      $api->addWhere('idx', 'CONTAINS', $this->formValues['idx']);
    }
    if (isset($this->formValues['contact_id'])) {
      if (is_array($this->formValues['contact_id'])) {
        $contact_ids = $this->formValues['contact_id'];
      } else {
        $contact_ids = explode(',', $this->formValues['contact_id']);
      }
      $contact_ids_safe = array();
      foreach ($contact_ids as $cid) {
        if (preg_match('/^\d+$/', $cid)) {
          $contact_ids_safe[] = $cid;
        }
      }
      if (count($contact_ids_safe)) {
        $api->addWhere('contact_id', 'IN', $contact_ids_safe);
      }
    }

    $transactions = $api->execute();

    // With CiviCRM 5.50+, there is a new countMatched method. Using it when available.
    $this->count = method_exists($transactions, 'countMatched') ? $transactions->countMatched() : $transactions->count();
    
    $this->rows = array();
    foreach ($transactions as $transaction) {
      $row = $transaction;
      if (!empty($row['contact_id'])) {
        $row['contact'] = '<a href="'.CRM_Utils_System::url('civicrm/contact/view', ['reset' => 1, 'cid' => $row['contact_id']]).'">'.CRM_Contact_BAO_Contact::displayName($row['contact_id']).'</a>';
      }
      $this->rows[] = $row;
    }
  }

}
