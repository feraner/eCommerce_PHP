/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
(function(){
	var el=document.createElement("script");
	el.type="text/javascript";
	el.src="//vk.com/js/api/openapi.js";
	el.async=true;
	var s=document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(el, s);
})();
window.vkAsyncInit=function(){
	if(!$('#vk_button').attr('apiid')) return false;
	VK.init({
	  apiId : $('#vk_button').attr('apiid')
	});
	$('#vk_button').click(function(){
		VK.Auth.login(function(response){
			if(response.session.user){
				var data='&id='+response.session.user.id+'&first_name='+response.session.user.first_name+'&last_name='+response.session.user.last_name;
				$.get('/?do_action=user.user_oauth&Type=VK', data, function(result){
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
			}
		});
	});
};