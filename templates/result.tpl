{if $result eq ''}
	<div class="noResults">
		{$texts.COLLECTION_UNAVAILABLE}
	</div>
{elseif isset($result->response->connection_problem)}
	<div class="noResults">
		{$texts.CONNECTION_ERROR}
	</div>
{elseif $numFound eq '0'}
	<div class="noResults">
		{$texts.NO_RESULTS}
	</div>	
{else}

<div class="results">
	<div class="resultServices">
		<div id="searchHistory" class="searchHistory closed">
		<h4>
			<a onclick="showHideBox('searchHistory');" title="{$texts.SHOW_HIDE}">
				<span>{$texts.HISTORY}</span>&#160;
				(<span id="sizeOfHistorySearch"></span>)
			</a>
		</h4>
		<div class="bContent">
			<ul id="searchHistoryList">
				<!-- Preenchido pelo PHP 'searchHistory.php' na funcão 'printSearchItem($term)' -->
			</ul>
			<script type="text/javascript">retrieveSearchTerms();</script>
		</div>
		<div id="searchHistoryOperators" class="popup" style="display:none">
			<h4>
				<span>{$texts.OPERATORS}</span>
				<img src="image/common/close.gif" onclick="showhideLayers('searchHistoryOperators')"/>
			</h4>
			<ul>
				<li onclick="addTermToSearch(this.innerHTML);">OR</li>
				<li onclick="addTermToSearch(this.innerHTML);">AND</li>
				<li onclick="addTermToSearch(this.innerHTML);">AND NOT</li>
			</ul>
		</div>
	</div>

	<div id="yourSelection" class="yourSelection closed">
		<h4>
			<a onclick="showHideBox('yourSelection')" title="{$texts.SHOW_HIDE}">
				<span>{$texts.YOUR_SELECTION}</span>&#160;
				(<span id="sizeOfBookmarks_0"></span>)
			</a>
		</h4>
		<div class="bContent">
			<p>
				{$texts.SELECTION.YOU_HAVE}&#160;<span id="sizeOfBookmarks_1">0</span>&#160;{$texts.SELECTION_REGISTERS}.
			</p>
			<ul>
				<li><a onclick="showBookmarks()">{$texts.SELECTION_LIST_REGISTERS}</a></li>
				<li><a onclick="clearBookmarks()" href="index.php?">{$texts.SELECTION_CLEAR_LIST}</a></li>
			</ul>
		</div>
	</div>

	<div class="refineSearch" id="refineSearch">
		<h4><a href="#" onclick="showHideBox('refineSearch')" title="{$texts.SHOW_HIDE}">{$texts.REFINE}</a></h4>
		<div class="collapseAll" onclick="expandRetractResults('retract')">
			<img src="image/common/resultServices_minus_icon2.jpg" alt="Collapse All"/>
			&#160;{$texts.RETRACT_ALL}
		</div>
		<div class="expandAll" onclick="expandRetractResults('expand')">
			<img src="image/common/resultServices_plus_icon2.jpg" alt="Expand All"/>
			&#160;{$texts.EXPAND_ALL}
		</div>
		{include file="result-clusters.tpl"}
		<div class="spacer"/>
	</div>
</div>
</div>

<div class="resultSet">
	{include file="result-navigation.tpl"}

    {if $fmt eq ''}
        {include file="result-doc.tpl"}
    {else}
        {include file="result-doc-$fmt.tpl"}
    {/if}

	{include file="result-navigation.tpl"}
</div>


</div>

{/if}