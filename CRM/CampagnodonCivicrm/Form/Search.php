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
      'issue', // field name
      E::ts('Issue'), // field label
      $this->getIssueOptions(), // list of options
      false // is required
    );
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
    $this->addEntityRef(
      'campaign_id',
      E::ts('Campaign'),
      ['entity' => 'Campaign', 'create' => false, 'multiple' => true],
      false
    );
    $this->add(
      'text',
      'idx',
      E::ts('Campagnodon IDX'),
      array('class' => 'huge'),
      false
    );
    $this->add(
      'select',
      'operation_type',
      E::ts('Operation Type'),
      $this->getOperationTypeOptions(),
      false
    );
    $this->add(
      'select',
      'tax_receipt',
      E::ts('Tax Receipt'),
      $this->getTaxReceiptOptions(),
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

  public function getIssueOptions() {
    $options = array(
      '' => E::ts('- select -'),
    );
    $options['already_member'] = E::ts('Already member');
    return $options;
  }

  public function getStatusOptions() {
    $statuses = CRM_CampagnodonCivicrm_BAO_CampagnodonTransaction::statusTables();
    $options = array(
      '' => E::ts('- select -'),
    );
    $options = array_merge($options, $statuses);
    return $options;
  }

  public function getOperationTypeOptions() {
    $options = array(
      '' => E::ts('- select -'),
    );
    $query = 'SELECT DISTINCT(operation_type) AS unique_operation_type FROM civicrm_campagnodon_transaction ORDER BY unique_operation_type';
    $dao = CRM_Core_DAO::executeQuery($query);

    while ($dao->fetch()) {
      $options[$dao->unique_operation_type] = $dao->unique_operation_type;
    }
    return $options;
  }

  public function getTaxReceiptOptions() {
    $options = array(
      '' => E::ts('- select -'),
      '1' => E::ts('With tax receipt'),
      '0' => E::ts('Without tax receipt')
    );
    return $options;
  }

  public function query() {
    $api = Civi\Api4\CampagnodonTransaction::get()
      ->selectRowCount()
      ->addSelect('*', 'campaign_id:label')
      ->addOrderBy('id', 'DESC');
    if ($this->limit !== false) {
      $api->setLimit($this->limit);
      $api->setOffset($this->offset);
    }

    if (isset($this->formValues['issue']) && !empty($this->formValues['issue'])) {
      $api->addJoin(
        'CampagnodonTransactionLink AS tlink',
        'INNER', null,
        ['tlink.campagnodon_tid', '=', 'id']
      );
      $api->addWhere('tlink.cancelled', '=', $this->formValues['issue']);
    }
    if (isset($this->formValues['status']) && !empty($this->formValues['status'])) {
      $api->addWhere('status', '=', $this->formValues['status']);
    }
    if (isset($this->formValues['idx']) && !empty($this->formValues['idx'])) {
      $api->addWhere('idx', 'CONTAINS', $this->formValues['idx']);
    }
    if (isset($this->formValues['operation_type']) && !empty($this->formValues['operation_type'])) {
      $api->addWhere('operation_type', '=', $this->formValues['operation_type']);
    }
    if (isset($this->formValues['tax_receipt'])) {
      if ($this->formValues['tax_receipt'] === '1') {
        $api->addWhere('tax_receipt', '=', true);
      } else if ($this->formValues['tax_receipt'] === '0') {
        $api->addWhere('tax_receipt', '=', false);
      }
    }

    foreach (['contact_id', 'campaign_id'] as $entity_ref_field) {
      if (isset($this->formValues[$entity_ref_field])) {
        if (is_array($this->formValues[$entity_ref_field])) {
          $entity_ref_ids = $this->formValues[$entity_ref_field];
        } else {
          $entity_ref_ids = explode(',', $this->formValues[$entity_ref_field]);
        }
        $entity_ref_ids_safe = array();
        foreach ($entity_ref_ids as $eid) {
          if (preg_match('/^\d+$/', $eid)) {
            $entity_ref_ids_safe[] = $eid;
          }
        }
        if (count($entity_ref_ids_safe)) {
          $api->addWhere($entity_ref_field, 'IN', $entity_ref_ids_safe);
        }
      }
    }

    $transactions = $api->execute();

    // With CiviCRM 5.50+, there is a new countMatched method. Using it when available.
    $this->count = method_exists($transactions, 'countMatched') ? $transactions->countMatched() : $transactions->count();
    
    $this->rows = array();
    foreach ($transactions as $transaction) {
      $row = $transaction;
      $row['contact'] = '';
      if (!empty($row['contact_id'])) {
        $row['contact'] = '<a href="'.CRM_Utils_System::url('civicrm/contact/view', ['reset' => 1, 'cid' => $row['contact_id']]).'">'.CRM_Contact_BAO_Contact::displayName($row['contact_id']).'</a><br>';
      }
      if ($row['email']) {
        $row['contact'].= '<a href="mailto:'.htmlspecialchars($row['email']).'">'.htmlspecialchars($row['email']).'</a><br>';
      }
      if ($row['tax_receipt']) {
        if ($row['phone']) {
          $row['contact'].= htmlspecialchars($row['phone']).'<br>';
        }
      }
      if (!empty($row['campaign_id'])) {
        $row['campaign'] = $row['campaign_id:label'];
      }
      $this->rows[] = $row;
    }
  }

}
