{if isset($smarty.request.debug)}
	{debug}
{/if}

{foreach from=$result->response->docs item=doc name=doclist}
<div class="resultSet">

  <div id="{$doc->id}" class="record">	
	<div class="data">

		<!-- title -->
		<h3>
		{if $doc->db eq 'LIS'}
			{assign var=url value=$doc->ur[0]}	

			<a href="{$url}">
				{occ element=$doc->ti separator=/}
			</a>

		{else}
			{occ element=$doc->ti separator=/}
		{/if}
		</h3>
		<!-- author -->
		{occ label=$texts.LABEL_AUTHOR element=$doc->au separator=; class=author}
		<!-- source -->
		{occ label=$texts.LABEL_SOURCE element=$doc->fo separator=; class=source}
		<!-- database -->
		<div class="database">
			{translate text=$doc->db suffix=DB_ translation=$texts}
		</div>
		<!-- source -->
		<div class="source">	
			<span class="type">
				<img src="image/common/type_{$doc->type}.gif"/>
				<span>{translate text=$doc->type suffix=TYPE_ translation=$texts}</span>
			</span>
				
			{occ label=$texts.LABEL_LANG element=$doc->la separator=; translation=$texts suffix=LANG_}
		</div>	
		<!-- abstract -->
		{if $doc->ab|@count > 0 or $doc->ab_pt|@count > 0 or $doc->ab_es|@count > 0 or $doc->ab_en|@count > 0}
			<div class="description">
				{$texts.LABEL_ABSTRACT}:
				{capture name=abstract}
					{occ element=$doc->ab separator=<hr/>}
					{occ element=$doc->ab_pt separator=<hr/>}
					{occ element=$doc->ab_es separator=<hr/>}
					{occ element=$doc->ab_en separator=<hr/>}
				{/capture}
				
				<span>
					{$smarty.capture.abstract}	
				</span>
			</div>
		{/if}

		<!-- subject -->
		{if $doc->mh|@count > 0}
			<div class="tags">
				{$texts.LABEL_SUBJECT}:
				{foreach key=mhKey item=mh from=$doc->mh}
					{if $doc->db eq 'MEDLINE'}
						{assign var="searchTerm" value=$mh|regex_replace:"/\/.*/":""}
						
						<a href="#" onclick="javascript:refineByIndex('{$searchTerm}','mh')">{$mh}</a>
					{else}
						<a href="#" onclick="javascript:refineByIndex('{$mh}','mh')">{$mh}</a>	
					{/if}
				{/foreach}
			</div>
		{/if}

		<div class="links">
			{iahlinks scielo=$links->response->docs document=$doc->ur id=$doc->id lang=$lang}
		</div>
		
	</div>
	<div class="services"></div>
	<div class="spacer"></div>
	<div class="user-actions">
		<div class="print">
			<a href="index.php?q=%2Bid:(%22{$doc->id}%22)&amp;lang={$lang}&amp;printMode=true">
				<img src="./image/common/icon_print.gif"/>
				&#160;<span>{$texts.PRINT}</span>
			</a>
		</div>

		{if $config->photocopy_url != ''}
			<div class="scad">
				<a href="{$config->photocopy_url}&lang={$lang}&db={$doc->db}&ident={$refID}">
					<img src="./image/common/icon_scad.gif"/>
					&#160;<span>Fotocópia</span>
				</a>
			</div>
		{/if}

		<div class="bookmark">
			<!-- AddThis Button BEGIN -->
			<script type="text/javascript">addthis_pub  = 'bvs.regional';</script>
			<a href="http://www.addthis.com/bookmark.php" onmouseover="return addthis_open(this, '', '[URL]', '[TITLE]')" onmouseout="addthis_close()" onclick="return addthis_sendto()"><img src="http://s9.addthis.com/button1-bm.gif" width="125" height="16" border="0" alt="" /></a>
			<script type="text/javascript" src="http://s7.addthis.com/js/152/addthis_widget.js"></script>
			<!-- AddThis Button END -->
		</div>
<!--
		<div class="scielo">
			<a>
				<img src="./image/common/icon_scielo.gif"/>
				&#160;<span>Texto completo SciELO Brasil</span>
			</a>
		</div>
		<div class="related">
			<a>
				<img src="./image/common/icon_related.gif"/>
				&#160;<span>Documentos relacionados</span>
			</a>
		</div>
-->
	</div>
  </div>

</div>
{/foreach}