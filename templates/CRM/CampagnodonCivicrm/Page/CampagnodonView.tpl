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
      <td>{$row.idx}</td>
    </tr>
    <tr>
      <td class="label">{ts}Start Date{/ts}</td>
      <td>{$row.start_date|crmDate:"%b %d, %Y %l:%M %P"}</td>
    <tr>
      <td class="label">{ts}Status{/ts}</td>
      <td>{$row.status}</td>
    </tr>
    <tr>
      <td class="label">{ts}Payment url{/ts}</td>
      <td>{$row.payment_url}</td>
    </tr>
    <tr>
      <td class="label">{ts}Original transaction url{/ts}</td>
      <td><a target="_blank" href="{$row.transaction_url}">{$row.transaction_url}</a></td>
    </tr>
    <tr>
      <td class="label">{ts}Payment Instrument{/ts}</td>
      <td>{$row.payment_instrument}</td>
    </tr>
    <tr>
      <td class="label">{ts}Campaign{/ts}</td>
      <td>
        {if $row.campaign_id}
          {$row.campaign_id}: {$row.campaign_title}
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
        {$row.email}<br>
        {$row.prefix} {$row.first_name} {$row.last_name}<br>
        {$row.street_address}<br>
        {$row.postal_code} {$row.city}<br>
        {$row.country_label}<br>
        {$row.phone}
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
            <th>{ts}Entity table{/ts}</th>
            <th>{ts}Entity ID{/ts}</th>
            <th></th>
            <th>{ts}Link options{/ts}</th>
          </tr>
          {foreach from=$links item=link}
            <tr>
              <td>{$link.entity_table}</td>
              <td>{$link.entity_id}</td>
              <td>{$link.view}</td>
              <td>
                {if $link.entity_table === 'civicrm_group'}
                  {if $link.on_complete}
                    {ts}On complete{/ts}
                  {/if}
                {/if}
                {if $link.entity_table === 'civicrm_contribution'}
                  {$link.financial_type}
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
                {/if}
                {if $link.cancelled}
                  <br>
                  {ts}Cancelled:{/ts}
                  <span style="color:red;">{$link.cancelled}</span>
                {/if}
              </td>
            </tr>
          {/foreach}
        </table>
      </td>
    </tr>
  </table>
</div>
{/crmScope}
