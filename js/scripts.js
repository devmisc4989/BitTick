// Slider

$(document).ready(function() {	
	$("ul#slides").cycle({
		fx: 'fade',
		pause: 1,
		prev: '#prev',
		next: '#next',
		timeout: 1000
	});
});

//Video popup
$(document).ready(function() {
$(".various").fancybox({
		'transitionIn'	: 'none',
		'transitionOut'	: 'none'
	});
});
