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

		</div> <!-- div content -->

        <script type="text/javascript">
          var _gaq = _gaq || [];
          _gaq.push(['_setAccount', '{$config->google_analytics_code}']);
          _gaq.push(['_trackPageview']);
        {literal}
          (function() {
            var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
            ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
          })();
        {/literal}
        </script>

	</body>
</html>
