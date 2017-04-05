function SetupFB(FB)
{
	FB.getLoginStatus(function (resp) {
		if (resp.status == 'connected') {
			//user connected call fb user info for login/register when clicking
			$('.fblogin span').click(function () {
				FB.api('/me', function (user) {
					var sData = $.param(user);
					HandleFBLogin(sData);
				});
			});
	
		}
		else {
			//unknown login, login to fb when click
			$('.fblogin span').click(function () {
				FB.login(function (resp) {
					if (resp.status == 'connected')
					{
						FB.api('/me', function (user) {
							var sData = $.param(user);
							HandleFBLogin(sData);
						});
					}
				},
				{ perms: 'email,user_about_me' });
			});
		}
	});
}
function HandleFBLogin(data)
{
	var path = document.location.protocol + "//" + document.location.hostname + "/";
	var fbLoginURL = path + "users/fbsignin/";
	$.post( fbLoginURL, data, function(data){
		path = path.replace("http://", "https://");
		if(data==0)
		{
			window.location=path+'lpc/cs';
		}
		else
			alert("FB Login return: " + data);
	});
}