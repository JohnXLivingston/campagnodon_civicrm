<?php
use CRM_CampagnodonCivicrm_ExtensionUtil as E;

class CRM_CampagnodonCivicrm_CiviRulesTriggers_Form_CampagnodonTransactionDaysAfter extends CRM_CivirulesTrigger_Form_Form {

  public function buildQuickForm() {
    $this->add('hidden', 'rule_id');
    $this->add('text', 'days', ts('Days after creation'), array('class' => 'huge'), TRUE);
    $this->addRule('days', ts('Interval should be a numeric value'), 'numeric');
    $this->addButtons([
      ['type' => 'next', 'name' => ts('Save'), 'isDefault' => TRUE,],
      ['type' => 'cancel', 'name' => ts('Cancel')]
    ]);
    parent::buildQuickForm();
  }

  /**
   * Overridden parent method to set default values
   *
   * @return array $defaultValues
   */
  public function setDefaultValues() {
    $defaultValues = parent::setDefaultValues();
    $data = unserialize($this->rule->trigger_params);
    if (!empty($data['days']) || $data['days'] === '0') {
      $defaultValues['days'] = $data['days'];
    }
    return $defaultValues;
  }

  /**
   * Overridden parent method to process form data after submission
   *
   * @throws Exception when rule condition not found
   */
  public function postProcess() {
    $data = [];
    $data['days'] = $this->_submitValues['days'];
    $this->rule->trigger_params = serialize($data);
    $this->rule->save();

    parent::postProcess();
  }

  /**
   * Returns a help text for this trigger.
   * The help text is shown to the administrator who is configuring the condition.
   *
   * @return string
   */
  protected function getHelpText() {
    return E::ts('This trigger will be called X days after the transaction started. You can then add conditions on the payment status for example.');
  }
}
