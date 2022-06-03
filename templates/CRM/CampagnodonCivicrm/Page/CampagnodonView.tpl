{crmScope extensionKey='campagnodon_civicrm'}
<h3>{ts}Campagnodon transaction{/ts}</h3>
<div class="crm-block crm-content-block crm-contribution-view-form-block">
  <table class="crm-info-panel">
    <tr>
      <td class="label">{ts}From{/ts}</td>
      <td class="bold">
        {if $row.contact_id}
          <a href="{crmURL p='civicrm/contact/view' q="cid=$row.contact_id"}">{$displayName}</a>
        {/if}
      </td>
    </tr>
    <tr>
      <td class="label">{ts}Campagnodon IDX{/ts}</td>
      <td>{$row.idx}</td>
    </tr>
    <tr>
      <td class="label">{ts}Status{/ts}</td>
      <td>{$row.status}</td>
    </tr>
    <tr>
      <td class="label">{ts}Payment url{/ts}</td>
      <td>{$row.payment_url}</td>
    </tr>
    <tr>
      <td class="label">{ts}Payment Method ID{/ts}</td>
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
      <td class="label">{ts}Personnal informations{/ts}</td>
      <td>
        {$row.email}<br>
        {$row.prefix} {$row.first_name} {$row.last_name}<br>
        {$row.street_address}<br>
        {$row.postal_code} {$row.city}<br>
        {$row.country_label}<br>
        {$row.phone}
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
          </tr>
          {foreach from=$links item=link}
            <tr>
              <td>{$link.entity_table}</td>
              <td>{$link.id}</td>
              <td>{$link.view}</td>
            </tr>
          {/foreach}
        </table>
      </td>
    </tr>
  </table>
</div>
{/crmScope}