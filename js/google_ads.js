//ads
var myAds = [];
myAds.push('<script type="text/javascript"><!--\n google_ad_client = "ca-pub-7938188809164299";\n /* ima.org-Alternative */\n google_ad_slot = "3482669267";\n google_ad_width = 728;\n google_ad_height = 90;\n //-->\n</script>\n<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>');
myAds.push('<script type="text/javascript"><!--\n google_ad_client = "ca-pub-7938188809164299";\n /* ima.org-Alternative */\n google_ad_slot = "4116707741";\n google_ad_width = 728;\n google_ad_height = 90;\n //-->\n</script>\n<script type="text/javascript" src="http://pagead2.googlesyndication.com/pagead/show_ads.js"></script>');

//choose one of them
var adFrom = 0;
var adLen = myAds.length - 1;
var idx = Math.floor(Math.random() * (adLen - adFrom + 1) + adFrom);
if( myAds[idx] )
{
	document.write("<p>Showing ad " + (idx+1) + "</p>");
	document.write(myAds[idx]);
}