{crmScope extensionKey='campagnodon_civicrm'}
  <div class="crm-content-block">
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
              {ts}Contact{/ts}
            </th>
            <th scope="col">
              {ts}Campagnodon key{/ts}
            </th>
            <th scope="col">
              {ts}Start Date{/ts}
            </th>
            <th scope="col">
              {ts}Status{/ts}
            </th>
            <th scope="col">
              {ts}Payment url{/ts}
            </th>
            <th scope="col">
              {ts}Payment Method{/ts}
            </th>
            <th scope="col">
              {ts}Campaign{/ts}
            </th>
          </tr>
          </thead>
          {foreach from=$rows item=row}
            <tr>
              <td>{$row.view}</td>
              <td>{$row.contact}</td>
              <td>{$row.idx}</td>
              <td>{$row.start_date|crmDate:"%b %d, %Y %l:%M %P"}</td>
              <td>{$row.status}</td>
              <td>
                {if $row.payment_url}
                  <a target="_blank" href="{$row.payment_url}">
                    {ts}Payment url{/ts}
                  </a>
                {/if}
              </td>
              <td>{$row.payment_instrument}</td>
              <td>{$row.campaign_title}</td>
            </tr>
          {/foreach}
        </table>
      </div>
      {include file="CRM/common/pager.tpl" location="bottom"}
    </div>
  </div>
{/crmScope}
