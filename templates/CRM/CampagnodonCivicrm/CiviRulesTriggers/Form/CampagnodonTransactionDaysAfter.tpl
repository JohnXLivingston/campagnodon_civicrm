{crmScope extensionKey='campagnodon_civicrm'}
  <h3>{$ruleTriggerHeader}</h3>
  <div class="crm-block crm-form-block crm-civirule-cron_trigger-block-activity_date">
  <div class="help">{$ruleTriggerHelp}</div>
  <div class="crm-section">
    <div class="label">{$form.days.label}</div>
    <div class="content">{$form.days.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
{/crmScope}
