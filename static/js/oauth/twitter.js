/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
$(function(){
	var twitter_on=!0;
	$('#twitter_btn').click(function(){
		var key=$(this).attr('key');
		var secret=$(this).attr('secret');
		var callback=$(this).attr('callback');
		if (twitter_on && key && secret && callback){
			twitter_on=!1;
			$.post('/?do_action=user.twitter_oauth_url', {key:key, secret:secret, callback:callback, apilogin:1, r:Math.random()}, function (data){
				if(data.status==0){
					window.location.href=data.url;
					//window.open(data.url,'twitterlogin','height=600,width=400,top=200,left='+($(window).width()/2-200)+',toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
				}else if(data.status==1){
					window.location.href='/';
				}else{
					twitter_on=!0;
				}
			}, 'json');
		}
	});
})