{assign var="vorigBezorggebied" value=""}
{assign var="inTable" value="no"}

{foreach from=$rows item=row key=rowid}
  {if $row.bezorg_gebied_deliver_area_name neq $vorigBezorggebied }
    {if $inTable eq 'yes' }
      </table>
    {/if}
  
    {assign var="vorigBezorggebied" value=$row.bezorg_gebied_deliver_area_name}
    <h2>{$row.afdeling_afdeling} - Bezorggebied {$row.bezorg_gebied_deliver_area_name}</h2>
    
    <table style="page-break-after: always;">
    <tr>    
	  <th>Naam</th>
	  <th>Straat</th>
	  <th>Postcode</th>
	  <th>Gemeente</th>
    </tr>
    {assign var="inTable" value="yes"}
  {/if}

  <tr style="page-break-inside: avoid !important">
    <td style="text-align: left; margin: 0px 4px 0px 1px; white-space: nowrap;">{$row.civicrm_contact_display_name}</td>
    <td style="text-align: left; margin: 0px 4px 0px 1px; white-space: nowrap;">{$row.civicrm_address_street_address}</td>
    <td style="text-align: left; margin: 0px 4px 0px 1px; white-space: nowrap;">{$row.civicrm_address_postal_code}</td>
    <td style="text-align: left; margin: 0px 4px 0px 1px; white-space: nowrap;">{$row.civicrm_address_city}</td>
  </tr>

{/foreach}  

{if $inTable eq 'yes' }
  </table>
{/if}
{if $pager and $pager->_response and $pager->_response.numPages > 1}
	<div class="report-pager">
		{include file="CRM/common/pager.tpl"  noForm=0}
	</div>
{/if}
