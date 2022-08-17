<?php

use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_CiviRulesConditions_CampagnodonTransaction_Form_NoOther extends CRM_CivirulesConditions_Form_Form {

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $label = "Status";
    $options = CRM_CampagnodonCivicrm_BAO_CampagnodonTransaction::statusTables();
    $this->add('select', 'status_id', $label, $options, FALSE, [
      'multiple' => TRUE,
      'class' => 'crm-select2'
    ]);
    $this->add('select', 'status_operator', ts('Operator'), [0 => ts('is one of'), 1 => ts('is NOT one of')], TRUE);

    $label = "Operation type (coma-separated list)";
    $this->add('text', 'operation_type', $label, [], FALSE);
    $this->add('select', 'operation_type_operator', ts('Operator'), [0 => ts('is one of'), 1 => ts('is NOT one of')], TRUE);

    $this->add('text', 'days', ts('Days after creation'), array('class' => 'huge'), TRUE);
    $this->addRule('days', ts('Interval should be a numeric value'), 'numeric');

    $this->addButtons(array(
      array('type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,),
      array('type' => 'cancel', 'name' => ts('Cancel'))));
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   * @access public
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $data = unserialize($this->ruleCondition->condition_params);

    $data['status_id'] = $data['status_id'] ?? [];
    if (!is_array($data['status_id'])) {
      $data['status_id'] = [$data['status_id']];
    }
    $defaultValues['status_id'] = $data['status_id'];

    if (!empty($data['status_operator'])) {
      $defaultValues['status_operator'] = $data['status_operator'];
    }

    if (!empty($data['operation_type'])) {
      $defaultValues['operation_type'] = $data['operation_type'];
    }

    if (!empty($data['operation_type_operator'])) {
      $defaultValues['operation_type_operator'] = $data['operation_type_operator'];
    }

    if (!empty($data['days']) || $data['days'] === '0') {
      $defaultValues['days'] = $data['days'];
    }

    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   * @access public
   */
  public function postProcess() {
    $data['status_id'] = $this->_submitValues['status_id'];
    $data['status_operator'] = $this->_submitValues['status_operator'];
    $data['operation_type'] = $this->_submitValues['operation_type'];
    $data['operation_type_operator'] = $this->_submitValues['operation_type_operator'];
    $data['days'] = $this->_submitValues['days'];
    $this->ruleCondition->condition_params = serialize($data);
    $this->ruleCondition->save();
    parent::postProcess();
  }

  /**
   * Returns a help text for this trigger.
   * The help text is shown to the administrator who is configuring the condition.
   *
   * @return string
   */
  protected function getHelpText() {
    $s = E::ts('This condition test that there is no other transaction for the same contact_id, that match the following criterias.');
    $s.= '<br>';

    $s.= E::ts('Status');
    $s.= ': ';
    $s.= E::ts('You can optionally choose transaction status.');
    $s.= '<br>';

    $s.= E::ts('Operation type');
    $s.= ': ';
    $s.= E::ts('This condition allows you to select a list of transaction operation types to match. You add multiple values separating them with coma.');
    $s.= E::ts('Here are the current existing types in the database:');
    $s.= '<ul>';

    $query = 'SELECT DISTINCT(operation_type) AS unique_operation_type FROM civicrm_campagnodon_transaction ORDER BY unique_operation_type';
    $dao = CRM_Core_DAO::executeQuery($query);

    while ($dao->fetch()) {
      $s.= '<li>'.htmlspecialchars($dao->unique_operation_type).'</li>';
    }
    $s.= '</ul>';
    return $s;
  }
}
