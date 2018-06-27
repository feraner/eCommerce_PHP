/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

// 加载google js文件
(function(){
	var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	po.src = '//apis.google.com/js/client:plusone.js?onload=googleRender'; // 后边加onload触发初始化函数
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
})();

//生成登录按钮
function googleRender() {
	var clientid=document.getElementById('google_btn').getAttribute('clientid');
	if(clientid){
		gapi.signin.render('google_btn', {
			'callback': 'signinCallback',
			'approvalprompt': 'auto',
			'clientid': clientid,
			'cookiepolicy': 'single_host_origin',
			'requestvisibleactions': 'http://schemas.google.com/AddActivity',
			'scope': 'https://www.googleapis.com/auth/plus.login https://www.googleapis.com/auth/userinfo.email'
		});
	}
}


// 回调函数
function signinCallback(authResult){
	if(authResult){
		if(typeof(authResult.status)=='object' && authResult.status.google_logged_in){//是否有错 authResult["error"]==undefined
			global_obj.div_mask();
			global_obj.data_posting(true, 'Login with Google+');
			var data='';
			gapi.client.load("oauth2","v2",function(){
				var request=gapi.client.oauth2.userinfo.get();
				request.execute(function(obj){//取得登录邮箱并显示
					//if(obj["email"]) data+='&email='+obj["email"];
					data+='&email='+obj["email"];//id
					data+='&id='+obj["id"];//id
				});
			});
			// 加载api
			gapi.client.load('plus','v1',function(){
				var request=gapi.client.plus.people.get({'userId':'me'});// 传入me即视为自己
				request.execute(function(profile){//回调函数，只有公开的资料才能获取
					//data+='&id='+profile["id"];//id
					data+='&name='+profile["displayName"];//名字
					//data+='&age='+profile["ageRange"]["min"];//生日
					//data+='&head='+profile["image"]["url"]+"&sz=200";//头像(sz=200 即图片大小200)
					disconnectUser();
					$.get('/?do_action=user.user_oauth&Type=Google', data, function(result){
						//window.top.location=result.msg[0];
						global_obj.div_mask(1);
						global_obj.data_posting(false);
						if(result.ret==1){
							//window.location=result.msg[0];
							if(typeof result.msg==='string'){
								window.location=result.msg;
							}else{
								window.location=result.msg[0];
							}
						}else{
							$.get(result.msg[0]+'?module=1', '', function(result){
								$('body').prepend(result);
								$('body').find('#binding_module').css({left:$(window).width()/2-220});
								global_obj.div_mask();
								user_obj.user_login_binding();
							});
						}
					}, 'json');
				});
			});
		}
	}
}
	
//取消与应用关联的代码
function disconnectUser(){
	var revokeUrl = 'https://accounts.google.com/o/oauth2/revoke?token='+gapi.auth.getToken().access_token;
	$.ajax({
		type: 'GET',
		url: revokeUrl,
		async: false,
		contentType: "application/json",
		dataType: 'jsonp',
		success: function(nullResponse){
			//alert("退出成功！");
		},
		error: function(e){
			//alert("取消關聯失敗！請到 https://plus.google.com/apps 手动解除！");
			//window.open("https://plus.google.com/apps");
		}
	});
}