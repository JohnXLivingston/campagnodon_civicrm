<?php

use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_CiviRulesConditions_CampagnodonTransaction_Form_OperationType extends CRM_CivirulesConditions_Form_Form {

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $label = "Operation type (coma-separated list)";
    $this->add('text', 'operation_type', $label, [], TRUE);
    $this->add('select', 'operator', ts('Operator'), [0 => ts('is one of'), 1 => ts('is NOT one of')], TRUE);

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

    if (!empty($data['operation_type'])) {
      $defaultValues['operation_type'] = $data['operation_type'];
    }

    if (!empty($data['operator'])) {
      $defaultValues['operator'] = $data['operator'];
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
    $data['operation_type'] = $this->_submitValues['operation_type'];
    $data['operator'] = $this->_submitValues['operator'];
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
    $s = E::ts('This condition allows you to select a list of transaction operation types to match. You add multiple values separating them with coma.');
    $s.= '<br>';
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
