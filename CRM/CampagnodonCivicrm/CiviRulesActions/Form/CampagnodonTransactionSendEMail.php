<?php

use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_CiviRulesActions_Form_CampagnodonTransactionSendEMail extends CRM_CivirulesActions_Form_Form {
  /**
   * Overridden parent method to build the form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_action_id');

    $this->add('text', 'from_name', E::ts('From Name'));
    $this->add('text', 'from_email', E::ts('From Email'));
    $this->addRule("from_email", E::ts('Email is not valid.'), 'email');

    $this->addEntityRef('template_id', E::ts('Message Template'),[
      'entity' => 'MessageTemplate',
      'api' => [
        'label_field' => 'msg_title',
        'search_field' => 'msg_title',
        'params' => [
          'is_active' => 1,
          'workflow_id' => ['IS NULL' => 1],
        ]
      ],
      'placeholder' => E::ts(' - select - ')
    ], TRUE);

    $this->addButtons([
      ['type' => 'next', 'name' => E::ts('Save'), 'isDefault' => TRUE,],
      ['type' => 'cancel', 'name' => E::ts('Cancel')]
    ]);
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();

    if (!empty($this->ruleAction->action_params)) {
      $data = unserialize($this->ruleAction->action_params);
    } else {
      $data = [];
    }
    if (!empty($data['from_name'])) {
      $defaultValues['from_name'] = $data['from_name'];
    }
    if (!empty($data['from_email'])) {
      $defaultValues['from_email'] = $data['from_email'];
    }
    if (!empty($data['template_id'])) {
      $defaultValues['template_id'] = $data['template_id'];
    }

    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submitting
   *
   * @access public
   */
  public function postProcess() {
    $data = [];
    $data['from_name'] = $this->_submitValues['from_name'];
    $data['from_email'] = $this->_submitValues['from_email'];
    $data['template_id'] = $this->_submitValues['template_id'];

    $this->ruleAction->action_params = serialize($data);
    $this->ruleAction->save();
    parent::postProcess();
  }
}

