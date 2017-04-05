<?php
/* Redirect to a different page in the current directory that was requested */
$host  = $_SERVER['HTTP_HOST'];
$my404page = 'pages/notfound';
header("Location: http://$host/$my404page/");
exit;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="Content-language" content="de" />
<meta name="google-site-verification" content="2zM_710VjIeJwaO4ezq79erJe75jcBOPLNtBOk5nWEs" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<link href="/css/style.css" rel="stylesheet" type="text/css" />
<link href="/js/fancybox/jquery.fancybox-1.3.4.css" rel="stylesheet" type="text/css" />
<link href="/css/popup_new.css" rel="stylesheet" type="text/css" />
<!--[if IE 7]>
<link href="/css/ie7.css" rel="stylesheet" type="text/css"  />
<![endif]-->

<title>A/B Test Tool | Landingpage Optimierung | BlackTri Optimizer</title><script type='text/javascript' src='/js/jquery-latest.js'></script>
<script type='text/javascript' src='/js/fancybox/jquery.fancybox-1.3.4.js'></script>
<script type='text/javascript' src='/js/fancybox/jquery.easing-1.3.pack.js'></script>
<script type='text/javascript' src='/js/popup.js'></script>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-27812300-1']);
  _gaq.push(['_trackPageview', location.pathname + location.search + location.hash]);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

</head>
<body>
<div id="header_wrap">
	<div id="header">
	<div class="logotype"><a href="/de/">Blacktri</a></div>
    <div id="main_nav">
      <ul>
	        <li><a href="/de/" >Home</a></li>
        <!-- 
        <li><a href="/de/tour/" >Tour</a></li>
        -->
		        <li><a href="/de/preise/" >Preise</a></li>
		        <li><a href="/blog/hilfe/">Hilfe</a></li>
		<li><a href="/blog/">Blog</a></li>
				<li><a href="/de/login/" >Login</a></li>
        
                
		      </ul>
    </div>
  </div>
</div>

<div id="title_bg">
  <div class="title-inner">
    <h2>Wir konnten die Seite leider nicht finden...</h2>
  </div>
</div>
<div id="main_container">
  <div class="terms">
    <h3>Vielleicht haben Sie sich vertippt?</h3>
    <h4><a style="font-size:17px;" href="/">Klicken Sie hier um zur Startseite zu kommen.</a></h4>
</div>
</div>
<div id="footer_wrap">
  <div id="footer">
    <div id="footer_nav">
      <ul>
        <li><a href="/de/agb/">AGB + Datenschutz</a><span>|</span></li>
        <li><a href="/de/impressum/">Impressum</a><span>|</span></li>
        <li><a href="/de/unternehmen/">Über BlackTri</a></li>
      </ul>
    </div>
    <div id="footer_right"><img src="/images/footer_logo.png" /></div>
  </div>
</div>
<script type="text/javascript">
function RepositionFooter()
{
	var htmlHeight = $("body").outerHeight(true);
	var screenHeight = $(window).height();
	var innerHeight = $("#header_wrap").outerHeight(true) + $("#inner_bg").outerHeight(true);	
	if( htmlHeight <= screenHeight)
		$("#footer_wrap").css("margin-top", screenHeight - innerHeight - $("#footer_wrap").height());
}
RepositionFooter();
</script>
</body>
</html>
