function loginValidation()
 {
            $('#signin_button').attr("disabled", "true");
            $('#signin_button').removeClass('but signin').addClass('but signin disable');
    	 	var path= document.frmLogin.path.value;
    	 	var email= $("#email").val();
    	 	var password= $("#password").val();
    	 	document.getElementById('msg').innerHTML="<img src='"+path+"images/preloader.gif'>";
			$.post(path+"users/signin/?" + Math.random(), {email: email,password: password},
					function(data)
			        {
			 			if(data==0)
			 			{
			 				//window.location=login_msg["base_ssl_url"] +'lpc/cs';
							document.frmLogin.externallogin.value = 'true';
							document.frmLogin.submit();
			 			}
			 			else if(data == 1)
			 			{
			 				document.getElementById('msg').innerHTML = login_msg["inputerror"];
			 				document.frmLogin.password.value = '';
			 	            $('#signin_button').removeClass('but signin disable').addClass('but signin');
			 	            $('#signin_button').attr("disabled","");
			 			}
			 			else if(data == 2)
			 			{
			 				document.getElementById('msg').innerHTML = login_msg["subscriptioncancelled"];
			 				document.frmLogin.password.value = '';
			 	            $('#signin_button').removeClass('but signin disable').addClass('but signin');
			 	            $('#signin_button').attr("disabled","");
			 			}
			 			else if(data == 5)
			 			{
			 				document.getElementById('msg').innerHTML = login_msg["betanotapproved"];
			 				document.frmLogin.password.value = '';
			 	            $('#signin_button').removeClass('but signin disable').addClass('but signin');
			 	            $('#signin_button').attr("disabled","");
			 			}
			        }
		    );
         
    }
function passwordRemainder()
{
	var path= document.frmLogin.path.value;
	var username= document.frmPassword.forgot_username.value;
	if(username =='')
	{
		document.getElementById('error-message').innerHTML= login_msg["pwr_emptyfield"];
		document.frmPassword.forgot_username.focus();
		return false;
	}
	else
	{
		document.getElementById('error-message').innerHTML="<img src='"+path+"images/preloader.gif'>";
		$.post(path+"users/updatepassword/", {username: username},
				function(data)
		        {
		 			// Username or email not found
		 			if(data==0)
		 			{
		 				document.getElementById('error-message').innerHTML= login_msg["pwr_inputerror"];
		 				document.frmPassword.forgot_username.focus();
		 			}
		 			// mail server error
		 			else if(data==1)
		 			{
		 				document.getElementById('error-message').innerHTML= login_msg["pwr_mail_send_failed"];
		 				document.frmPassword.forgot_username.focus();
		 			}
		 			// success
		 			else if(data==2)
		 			{
		 				document.getElementById('resend-message').innerHTML= login_msg["pwr_success"];
		 				//window.location=path;
		 				document.getElementById('email_success').style.display ='block';
		 			}
		 			// Email not validated
		 			else
		 			{
		 				document.getElementById('error-message').innerHTML=login_msg["pwr_notvalidated"] + data;
		 				document.frmPassword.forgot_username.focus();
		 			}
		        }
		);
	}
}

function emailsuccess(clientid_hash,status)
{
	var path= login_msg["base_ssl_url"];
	document.getElementById('error-message').innerHTML= "<img src='"+path+"images/preloader.gif'><br/>" + document.getElementById('error-message').innerHTML;
	$.post(path+"users/emailsuccess/", {clientid_hash: clientid_hash,status: status},
		function(data)
        {
 			if(data == 'success')
 			{
 				document.getElementById('resend-message').innerHTML='<h1>' + login_msg["pwr_validationmail_sent_header"] + '</h1><div>' + login_msg["pwr_validationmail_sent_copy"] + '</div>';
 				document.getElementById('email_success').style.display ='block';
 			}
 			else
 			{
 				document.getElementById('resend-message').innerHTML='<h1>' + login_msg["pwr_validationmail_failed_header"] + '</h1><span class="error-message">' + login_msg["pwr_mail_send_failed"] + '<span>';
 				document.getElementById('email_success').style.display ="block";
 			}
        }
    );
}

// resend mail

function emailResend(clientid_hash,status)
{
	var path= login_msg["base_ssl_url"];
	document.getElementById('resendMail').innerHTML="<img src='"+path+"images/preloader.gif'>";
	$.post(path+"users/emailsuccess/", {clientid_hash: clientid_hash,status: status},
		function(data)
        {
 			if(data == 'success')
 			{
 				document.getElementById('resendMail').innerHTML=login_msg["validate_sendemail"];
 			}
 			else
 			{
 				document.getElementById('resendMail').innerHTML=login_msg["validate_emailerror"];
 			}
        }
    );
}
