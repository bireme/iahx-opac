<?
$page_top = 
"	  	<a href=\"{$config['bvs_url']}\">
			<h1>{$texts['TITLE']}</h1>
		</a> 
		{$texts['SEND_BY']}: {$recipientMail}<br/>";
if($q)
	$page_top .= "<br/>&nbsp;<b>Link para a pesquisa</b>: <a href='{$dia_path}index.php?site={$site}&col={$col}&lang={$lang}&q={$q}'>
		{$dia_path}index.php?site={$site}&col={$col}&lang={$lang}&q={$q}
	</a>";

	$page_top .= "<hr/>";
?>
