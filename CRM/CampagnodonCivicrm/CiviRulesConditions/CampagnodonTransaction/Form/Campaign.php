<?php

use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_CiviRulesConditions_CampagnodonTransaction_Form_Campaign extends CRM_CivirulesConditions_Form_Form {

  /**
   * Overridden parent method to build form
   *
   * @access public
   */
  public function buildQuickForm() {
    $this->add('hidden', 'rule_condition_id');

    $label = "Campaign";
    $this->addEntityRef(
      'campaign_id',
      E::ts('Campaign'),
      ['entity' => 'Campaign', 'create' => false, 'multiple' => true],
      true
    );

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

    if (!empty($data['campaign_id'])) {
      $defaultValues['campaign_id'] = $data['campaign_id'];
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
    $data['campaign_id'] = $this->_submitValues['campaign_id'];
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
    return E::ts('This condition allows you to select a list of campaign to match.');
  }
}
