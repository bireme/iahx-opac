<?xml version="1.0" encoding="UTF-8"?>

<rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/">
	<channel>
		<title>{$texts.TITLE}</title>
		<link>{$url}</link>
		<description>{$texts.DESCRIPTION}</description>
		
		{foreach from=$result->response->docs item=doc}
			<item>
				<title>{occ element=$doc->ti separator=/}</title>
				<author>{occ element=$doc->au separator=;}</author>
				<link>{$url}?q=%2Bid:("{$doc->id}")&amp;lang={$lang}</link>
				<description>
					&lt;table width="90%"&gt;
						&lt;tr valign="top"&gt;
							&lt;td colspan="2"&gt;	
								&lt;br/&gt;
								&lt;font size="4" color="#0068CF"&gt;
								{if isset($doc->ur)}	
									&lt;a href="{$doc->ur}" target="doc"&gt;
										{occ element=$doc->ti separator=/}
									&lt;/a&gt;
								{else}
									{occ element=$doc->ti separator=/}
								{/if}
								&lt;/font&gt;	
							&lt;/td&gt;
						&lt;/tr&gt;
						&lt;tr valign="top"&gt;
							&lt;td width="15%"&gt;
								&lt;b&gt;
									{$texts.LABEL_AUTHOR}
								&lt;/b&gt;
							&lt;/td&gt;
							&lt;td&gt;
								{occ element=$doc->au separator=;}
							&lt;/td&gt;
						&lt;/tr&gt;
						&lt;tr valign="top"&gt;
							&lt;td&gt;
								&lt;b&gt;
									{$texts.LABEL_SOURCE}
								&lt;/b&gt;
							&lt;/td&gt;
							&lt;td&gt;
								{occ element=$doc->fo separator=;}
							&lt;/td&gt;
						&lt;/tr&gt;
						{ if isset($doc->ab) }
						&lt;tr valign="top"&gt;
							&lt;td&gt;
								&lt;b&gt;
									{$texts.LABEL_ABSTRACT}
								&lt;/b&gt;
							&lt;/td&gt;
							&lt;td&gt;
								{occ element=$doc->ab separator=/}
							&lt;/td&gt;
						&lt;/tr&gt;
						{/if}
						&lt;tr valign="top"&gt;
							&lt;td&gt;
								&lt;b&gt;
									{$texts.LABEL_SUBJECT}
								&lt;/b&gt;
							&lt;/td&gt;
							&lt;td&gt;
								{occ element=$doc->mh separator=;}
							&lt;/td&gt;
						&lt;/tr&gt;
					&lt;/table&gt;
				</description>
			</item>
		{/foreach}
	</channel>
</rss>