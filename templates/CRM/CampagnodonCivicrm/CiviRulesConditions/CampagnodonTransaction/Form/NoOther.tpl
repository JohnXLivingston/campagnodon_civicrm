{crmScope extensionKey='campagnodon_civicrm'}
  <h3>{$ruleConditionHeader}</h3>
  <div class="crm-block crm-form-block">
  <div class="help">{$ruleConditionHelp}</div>
  <div class="crm-section">
    <div class="label">{$form.status_id.label}</div>
    <div class="content">{$form.status_operator.html} {$form.status_id.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.operation_type.label}</div>
    <div class="content">{$form.operation_type_operator.html} {$form.operation_type.html}</div>
  </div>
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
{/crmScope}
