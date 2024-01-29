{crmScope extensionKey='campagnodon_civicrm'}
<h3>{ts}Campagnodon transaction{/ts}</h3>
<div class="crm-block crm-content-block crm-contribution-view-form-block">
  <table class="crm-info-panel">
    <tr>
      <td class="label">{ts}From{/ts}</td>
      <td class="bold">
        {if $row.contact_id}
          <a href="{crmURL p='civicrm/contact/view' q="cid=`$row.contact_id`"}">{$displayName}</a>
        {/if}
      </td>
    </tr>
    <tr>
      <td class="label">{ts}Campagnodon IDX{/ts}</td>
      <td>{$row.idx|escape}</td>
    </tr>
    {if $row.parent_id}
      <tr>
        <td class="label">{ts}Parent CampagnodonTransaction ID{/ts}</td>
        <td>
          <a href="{crmURL p='civicrm/campagnodon/view' q="id=`$row.parent_id`"}">{$row.parent_id}</a>
        </td>
      </tr>
    {/if}
    <tr>
      <td class="label">{ts}Operation Type{/ts}</td>
      <td>{$row.operation_type|escape}</td>
    </tr>
    <tr>
      <td class="label">{ts}Source{/ts}</td>
      <td>{$row.source|escape}</td>
    </tr>
    <tr>
      <td class="label">{ts}Start Date{/ts}</td>
      <td>{$row.start_date|crmDate:"%b %d, %Y %l:%M %P"}</td>
    </tr>
    <tr>
      <td class="label">{ts}Contribution Date{/ts}</td>
      <td>{$row.contribution_date|crmDate:"%b %d, %Y %l:%M %P"}</td>
    </tr>
    <tr>
      <td class="label">{ts}Status{/ts}</td>
      <td>{$row.status|escape}</td>
    </tr>
    {if $row.recurring_status}
      <tr>
        <td class="label">{ts}Recurring Status{/ts}</td>
        <td>{$row.recurring_status|escape}</td>
      </tr>
    {/if}
    <tr>
      <td class="label">{ts}Payment Url{/ts}</td>
      <td>{$row.payment_url}</td>
    </tr>
    <tr>
      <td class="label">{ts}Original transaction url{/ts}</td>
      <td><a target="_blank" href="{$row.transaction_url}">{$row.transaction_url}</a></td>
    </tr>
    <tr>
      <td class="label">{ts}Payment Instrument{/ts}</td>
      <td>{$row.payment_instrument|escape}</td>
    </tr>
    <tr>
      <td class="label">{ts}Campaign{/ts}</td>
      <td>
        {if $row.campaign_id}
          {$row.campaign_id|escape}: {$row.campaign_title|escape}
        {/if}
      </td>
    </tr>
    <tr>
      <td class="label">{ts}Tax Receipt{/ts}</td>
      <td><input type="checkbox" disabled {if $row.tax_receipt} checked {/if}></td>
    </tr>
    <tr>
      <td class="label">{ts}Personnal informations{/ts}</td>
      <td>
        {if $row.cleaned}
          <span style="color:red;">
            {ts}Personnal information were cleaned.{/ts}<br>
          </span>
        {/if}
        {if $row.merged}
          <span style="color:green;">
            {ts}Personnal information were merged into contact.{/ts}<br>
          </span>
        {/if}
        {$row.email|escape}<br>
        {$row.prefix|escape} {$row.first_name|escape} {$row.last_name|escape}<br>
        {$row.street_address|escape}<br>
        {$row.supplemental_address_1|escape}<br>
        {$row.supplemental_address_2|escape}<br>
        {$row.postal_code|escape} {$row.city|escape}<br>
        {$row.country_label|escape}<br>
        {$row.phone|escape}
      </td>
    </tr>
    <tr>
      <td class="label"></td>
      <td>
        {if $row.new_contact}
          {ts}This contact was created for this transaction.{/ts}
        {else} 
          {ts}This contact already existed when this transaction was created.{/ts}
        {/if}
        <br>
        {if $row.contact_id != $row.original_contact_id}
          {ts}The original contact was:{/ts} {$row.original_contact_id}.<br>
          {ts}The current contact is:{/ts} {$row.contact_id}.<br>
        {/if}
      </td>
    </tr>
    <tr>
      <td class="label">{ts}Linked entities{/ts}</td>
      <td>
        <table class="selector row-highlight">
          <tr>
            <th>{ts}Link ID{/ts}</th>
            <th></th>
            <th>{ts}Entity table{/ts}</th>
            <th>{ts}Entity ID{/ts}</th>
            <th></th>
            <th>{ts}Link options{/ts}</th>
          </tr>
          {foreach from=$links item=link}
            <tr>
              <td>{if $link.parent_id}{$link.parent_id}/{/if}{$link.id}</td>
              <td>{$link.optional_subscription_name|escape}</td>
              <td>{$link.entity_table|escape}</td>
              <td>{$link.entity_id}</td>
              <td>{$link.view}</td>
              <td>
                {if $link.entity_table === 'civicrm_group'}
                  {if $link.on_complete}
                    {ts}On complete{/ts}
                  {/if}
                {/if}
                {if $link.entity_table === 'civicrm_contribution'}
                  {$link.financial_type|escape}
                  {$link.total_amount|crmMoney:$link.currency}
                {/if}
                {if $link.entity_table === 'civicrm_contact'}
                  {$link.opt_in}
                  {if $link.on_complete}
                    {ts}On complete{/ts}
                  {/if}
                {/if}
                {if $link.entity_table === 'civicrm_membership'}
                  {if $link.membership_type}
                    {$link.membership_type}
                    {$link.total_amount|crmMoney:$link.currency}
                    <br>
                  {/if}
                  {if $link.opt_in}
                    {$link.opt_in}<br>
                  {/if}
                  {if $link.keep_current_membership_if_possible}
                      (only renew membership when needed)<br>
                  {/if}
                {/if}
                {if $link.cancelled}
                  <br>
                  {ts}Cancelled:{/ts}
                  <span style="color:red;">{$link.cancelled|escape}</span>
                {/if}
              </td>
            </tr>
          {/foreach}
        </table>
      </td>
    </tr>
    {if $childs}
      <tr>
        <td class="label">{ts}Related transactions{/ts}</td>
        <td>
          <table class="selector row-highlight">
            <tr>
              <th>{ts}ID{/ts}</th>
              <th>{ts}Campagnodon IDX{/ts}</th>
              <th>{ts}Operation Type{/ts}</th>
              <th>{ts}Start Date{/ts}</th>
            </tr>
            {foreach from=$childs item=child}
              <tr>
                <td><a href="{crmURL p='civicrm/campagnodon/view' q="id=`$child.id`"}">{$child.id}</a></td>
                <td>{$child.idx|escape}</td>
                <td>{$child.operation_type|escape}</td>
                <td>{$child.start_date|crmDate:"%b %d, %Y %l:%M %P"}</td>
              </tr>
            {/foreach}
          </table>
        </td>
      </tr>
          {/if}
  </table>
</div>
{/crmScope}
