/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

window.fbAsyncInit=function(){
	if($('#fb_button').attr('appid')){
		FB.init({
			appId: $('#fb_button').attr('appid'),
			cookie: true,  // enable cookies to allow the server to access 
			xfbml: false,  // parse social plugins on this page
			version: 'v2.6' // use version 2.1
		});
	}
};




// This is called with the results from from FB.getLoginStatus().
function statusChangeCallback(response){
	//console.log('statusChangeCallback');
	//console.log(response);
	// The response object is returned with a status field that lets the
	// app know the current login status of the person.
	// Full docs on the response object can be found in the documentation
	// for FB.getLoginStatus().
	if(response.status === 'connected'){ //已经成功登录过
		global_obj.div_mask();
		global_obj.data_posting(true, 'Login with Facebook');
		
		testAPI();
	/*
	}else if(response.status === 'not_authorized'){ //不是这个网站的应用程序
		alert('Please log into this app.');
	*/
	}else{
		// The person is not logged into Facebook, so we're not sure if
		// they are logged into this app or not.
		FB.login(function(response){
		  // Handle the response object, like in statusChangeCallback() in our demo
		  // code.
		  if(response.status != 'not_authorized' && response.status != 'unknown'){ //除了“关闭登录框”，“取消登录”，继续判别执行
		  	statusChangeCallback(response);
		  }
		}, {scope: 'email'});//,manage_pages
		//document.getElementById('status').innerHTML = 'Please log ' + 'into Facebook.';
	}
}

// This function is called when someone finishes with the Login
// Button.  See the onlogin handler attached to it in the sample
// code below.
function checkLoginState(){
	FB.getLoginStatus(function(response){
		statusChangeCallback(response);
	});
}

// Load the SDK asynchronously
(function(d, s, id) {
	var js, fjs = d.getElementsByTagName(s)[0];
	if(d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = "https://connect.facebook.net/en_US/sdk.js";
	fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

// Here we run a very simple test of the Graph API after login is
// successful.  See statusChangeCallback() for when this call is made.
function testAPI(){
	//console.log('Welcome!  Fetching your information.... ');
	FB.api('/me', {fields: 'id,email,first_name,last_name,gender,age_range,picture'}, function(response){
		//console.log('Successful login for: ' + response.name);
		var url='&id='+response['id']+'&email='+response['email']+'&first_name='+response['first_name']+'&last_name='+response['last_name']+'&gender='+response['gender'];
		
		$.get('/?do_action=user.user_oauth&Type=Facebook', url, function(data){
			global_obj.div_mask(1);
			global_obj.data_posting(false);
			if(data.ret==1){
				//window.location=data.msg[0];
				if(typeof data.msg==='string'){
					window.location=data.msg;
				}else{
					window.location=data.msg[0];
				}
			}else{
				$.get(data.msg[0]+'?module=1', '', function(data){
					$('body').prepend(data);
					$('body').find('#binding_module').css({left:$(window).width()/2-220});
					global_obj.div_mask();
					user_obj.user_login_binding();
				});
			}
		}, 'json');
	});
}