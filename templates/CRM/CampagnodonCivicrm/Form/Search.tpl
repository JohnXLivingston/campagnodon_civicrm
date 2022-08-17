{crmScope extensionKey='campagnodon_civicrm'}
  <div class="crm-content-block">

    <div class="crm-block crm-form-block crm-basic-criteria-form-block">
      <div class="crm-accordion-wrapper crm-expenses_search-accordion collapsed">
        <div class="crm-accordion-header crm-master-accordion-header">{ts}Search Campagnodon{/ts}</div><!-- /.crm-accordion-header -->
        <div class="crm-accordion-body">
          <table class="form-layout">
            <tbody>
            <tr>
              <td class="label">{$form.status.label}</td>
              <td>{$form.status.html}</td>
            </tr>
            <tr>
              <td class="label">{$form.contact_id.label}</td>
              <td>{$form.contact_id.html}</td>
            </tr>
            <tr>
              <td class="label">{$form.campaign_id.label}</td>
              <td>{$form.campaign_id.html}</td>
            </tr>
            <tr>
              <td class="label">{$form.idx.label}</td>
              <td>{$form.idx.html}</td>
            </tr>
            <tr>
              <td class="label">{$form.operation_type.label}</td>
              <td>{$form.operation_type.html}</td>
            </tr>
            <tr>
              <td class="label">{$form.tax_receipt.label}</td>
              <td>{$form.tax_receipt.html}</td>
            </tr>
            <tr>
              <td class="label">{ts}Start Date{/ts}</td>
              <td>{$form.start_date_greater_than.html} {$form.start_date_lower_than.html}</td>
            </tr>
            </tbody>
          </table>
          <div class="crm-submit-buttons">
            {include file="CRM/common/formButtons.tpl"}
          </div>
        </div><!- /.crm-accordion-body -->
      </div><!-- /.crm-accordion-wrapper -->
    </div><!-- /.crm-form-block -->

    <div class="clear"></div>

    <div class="crm-results-block">
      {include file="CRM/common/pager.tpl" location="top"}

      <div class="crm-search-results">
        <table class="selector row-highlight">
          <thead class="sticky">
          <tr>
            <th scope="col">
              {ts}ID{/ts}
            </th>
            <th scope="col">
              {ts}Campagnodon IDX{/ts}
            </th>
            <th scope="col">
              {ts}Operation Type{/ts}
            </th>
            <th scope="col">
              {ts}Start Date{/ts}
            </th>
            <th scope="col">
              {ts}Status{/ts}
            </th>
            <th scope="col">
              {ts}Contact{/ts}
            </th>
            <th scope="col">
              {ts}Campaign{/ts}
            </th>
            <th scope="col">
              {ts}Tax Receipt{/ts}
            </th>
            <th>&nbsp;</th>
          </tr>
          </thead>
          {foreach from=$entities item=row}
            <tr>
              <td>
                <a href="{crmURL p='civicrm/campagnodon/view' q="id=`$row.id`"}">{$row.id}</a>
              </td>
              <td>{$row.idx|escape}</td>
              <td>{$row.operation_type|escape}</td>
              <td>{$row.start_date|crmDate:"%b %d, %Y %l:%M %P"}</td>
              <td>{$row.status|escape}</td>
              <td>{$row.contact}</td>
              <td>{$row.campaign|escape}</td>
              <td><input type="checkbox" disabled {if $row.tax_receipt} checked {/if}></td>
              <td class="right nowrap">
                  <span>
                    <a class="action-item crm-hover-button" href="{crmURL p='civicrm/campagnodon/view' q="id=`$row.id`"}">{ts}View{/ts}</a>
                  </span>
              </td>
            </tr>
          {/foreach}
        </table>

      </div>

      {include file="CRM/common/pager.tpl" location="bottom"}
    </div>
  </div>
{/crmScope}
