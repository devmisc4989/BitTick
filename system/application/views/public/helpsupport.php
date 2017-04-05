<iframe class="help_support" src="//blacktri-jira.atlassian.net/wiki/x/XAB7AQ"></iframe>
<script>
function helpResize(){
	var h = $(window).height();
	
	if($('#header_wrap').is(':visible'))
		h-= $('#header_wrap').height();
	if($('#admin_header').is(':visible'))
		h-= $('#admin_header').height();
		
	$('iframe.help_support').height(h);
}
helpResize();
$(window).resize(helpResize);
</script>
</body>
</html>
