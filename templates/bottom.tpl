				<div class="footer">
					<div class="powered">
						powered by <strong>iAHx-{$smarty.const.VERSION}</strong> {$texts.BVS_TITLE}
					</div>
					<div class="links">
						<a href="{$config->bvs_url}">{$texts.BACK_HOME}</a> 
						|
						<a href="{$config->about_bvs_url}">{$texts.ABOUT_BVS}</a>
					</div>
				</div>
				{literal}
				<script language="JavaScript" type="text/javascript">
					if(!cookiesEnabled()){
						document.getElementById('yourSelection').style.display = 'none';
						document.getElementById('searchHistory').style.display = 'none';
						document.getElementById('mailSend').style.display = 'none'; 
						hideClass('div','yourSelectionCheck');
						alertBookmarkUnavailable();
					}
				</script>
				{/literal}
			</div>
			
		</div>
		
		<!-- Displayed by thickbox -->
		<div id="mailSendContent" style="display:none">
			<form method="post" class="mailForm" action="mail.php" name="mailSend" onsubmit="return sendMail(this);">
				<input type="hidden" name="lang" value="{$lang}"/>
				<div class="field">
					<label>{$texts.MAIL_FROM}:</label>
					<input type="text" class="inputText" value="" name="senderMail"/>
				</div>

				<div class="field">
					<label>{$texts.MAIL_TO}:</label>
					<input type="text" class="inputText" value="" name="recipientMail"/>
				</div>

				<div class="field">
					<label>{$texts.SUBJECT}:</label>
					<input type="text" class="inputText" value="" name="subject"/>
				</div>
				
				<br/>
				<div class="options">
					<input type="radio" name="option" value="selected" style="border:none"/>
                    <span>{$texts.SELECTION_SEND_THE}&#160;<span id="sizeOfBookmarks_2">0</span>&#160;{$texts.SELECTION_REGISTERS}.</span>
                    <br/><br/>

					<input type="radio" name="option" value="query_only" style="border:none"/>
					<span>{$texts.SEND_SEARCH_TERM_ONLY}</span>&nbsp;
                    <input type="hidden" name="q" value="{$q_escaped}"/>
                    <input type="hidden" name="where" value="{$smarty.request.where}"/>
                    <input type="hidden" name="index" value="{$smarty.request.index}"/>

					<br/><br/>
					
					<input type="radio" name="option" value="from_to" style="border:none"/>
					<span>{$texts.STARTING_FROM_RECORD}</span>
					<input type="text" name="from" size="3" value="1"/>,&nbsp;
					<span>{$texts.SEND}</span>
					<select name="count">
						<option value="5">5</option>
						<option value="10">10</option>
						<option value="20">20</option>
						<option value="50">50</option>
						<option value="100">100</option>
						<option value="200">200</option>
						<option value="500">500</option>
					</select>
                    <span>{$texts.RECORDS}</span>
				</div>
				<br/>
				
				<input type="submit" class="submit" value="{$texts.SEND}" name="go"/>
				
				<span id="sendingMail" class="transmission" style="display:none;">{$texts.MAIL_SENDING}</span>
				<span id="mailSent" class="transmission" style="display:none;">{$texts.MAIL_SENT}</span>
				<span id="mailError" class="transmission" style="display:none;">Erro!</span>
			</form>
		</div>
		<script language="JavaScript" type="text/javascript">recoverBookmarks();</script>
	</body>
</html>
