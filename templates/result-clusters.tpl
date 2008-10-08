<div class="bContent" id="refine_facet">
{foreach from=$result->facet_counts item=cluster}

  {foreach key=key item=item from=$cluster}

	{assign var="label" value="REFINE_$key"}
	{assign var="totalItems" value=$item|@count}

	{if $totalItems gt 0}
		<div id="{$key}">
	
			<strong onclick="showHideBox('{$key}')">{$texts.$label}</strong>
			<!--
			<a href="#" onclick="showChart(this,'{$texts.$label}','{$key}')" class="thickbox"><img src="image/common/chart.gif"></a>
			-->
			<ul id="{$key}_set">
			{if $key == 'type'}
				{foreach key=clusterKey item=clusterItem from=$item}
	
					{capture name=type}{translate text=$clusterItem[0] suffix=TYPE_ translation=$texts}{/capture}
					{if $smarty.capture.type ne ''}
						<li>
							<a href="#" onclick="javascript:applyFilter('{$clusterItem[0]}','{$key}')">{$smarty.capture.type}</a> ({$clusterItem[1]})
						</li>
					{/if}
				{/foreach}
	
			{elseif $key == 'mh_cluster'}
	
				{foreach key=clusterKey item=clusterItem from=$item}
					<li>
						<a href="#" onclick="javascript:applyFilter('{$clusterItem[0]}','mj')">{$clusterItem[0]}</a> ({$clusterItem[1]})
					</li>
				{/foreach}
	
			{elseif $key == 'la'}
	
				{foreach key=clusterKey item=clusterItem from=$item}
	
					{capture name=lang}{translate text=$clusterItem[0] suffix=LANG_ translation=$texts}{/capture}
					{if $smarty.capture.lang ne ''}
						<li>
							<a href="#" onclick="javascript:applyFilter('{$clusterItem[0]}','{$key}')">{$smarty.capture.lang}</a> ({$clusterItem[1]})
						</li>
					{/if}
				{/foreach}
	
			{else}
				{foreach key=clusterKey item=clusterItem from=$item}
					<li>
						<a href="#" onclick="javascript:applyFilter('{$clusterItem[0]}','{$key}')">{$clusterItem[0]}</a> ({$clusterItem[1]})
					</li>
				{/foreach}
			{/if}
	
			{if $totalItems%$colectionData->cluster_items_limit eq 0}
				<li class="more"><a href="#" onclick="javascript:showMoreClusterItems('{$key}','{$totalItems+$colectionData->cluster_items_limit}'); return false;">{$texts.MORE}...</a></li>
			{/if}
			</ul>
		</div>
	{/if}
  {/foreach}
{/foreach}
</div>