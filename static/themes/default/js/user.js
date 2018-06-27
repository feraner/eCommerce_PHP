/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
var address_perfect=0;

var user_obj={
	/******************* 登录或注册 Start *******************/
	sign_in_init:function(){
		var cancelback=(typeof(arguments[0])=='undefined')?'':arguments[0];//取消返回函数
		
		$('body').off().on('click', '.SignInButton', function(){ //点击登录链接，显示登录框
			user_obj.set_form_sign_in('', '', 1);
		})
		.on('click', '#signin_close, #div_mask', function(){ //关闭登录
			cancelback && cancelback();
			if($('#signin_module').length && $('#signin_close').length && !ueeshop_config['_login']){
				$('#signin_module').remove();
				global_obj.div_mask(1);
			}
		})
		.on('submit', '.global_signin_module form[name=signin_form]', function(){ //会员登录
			if(global_obj.check_form($(this).find('*[notnull]'))){return false;};
			var Email=$.trim($(this).find('input[name=Email]').val());
			var r=/^\w+[a-zA-Z0-9-.+_]+@[a-zA-Z0-9-.+_]+\.\w*$/;
			if(!r.test(Email)){
				alert(lang_obj.format.email);
				return false;
			}
			$(this).find('button:submit').attr('disabled', true);
			
			$.post('/', $(this).serialize(), function(data){
				$('.global_signin_module form[name=signin_form] button:submit').removeAttr('disabled');
				if(data.ret!=1){
					$('#error_login_box').html(data.msg[0]).show();
				}else{
					if($('input[name=comeback]').length){
						var $callback=$('input[name=comeback]').val();
						$callback && eval($callback);
						$('html').checkUser();
					}else window.location=data.msg[0];
				}
			}, 'json');
			
			return false;
		});
	},
	
	set_form_sign_in:function(type){//生成登录框
		var Url=(typeof(arguments[1])=='undefined')?'':arguments[1];
		var Model=(typeof(arguments[2])=='undefined')?0:arguments[2];
		var addStr='',
			obj=$('body'),
			w=$(window),
			regUrl='/account/sign-up.html';
			
		if(type=='parent'){//父框架元素
			obj=$(window.parent.document).find('body');
			w=$(window.parent.window);
		}
		
		if(obj.find('#attr_hide').val()){
			addStr=obj.find('#attr_hide').serialize()?obj.find('#attr_hide').serialize():obj.find('#attr_hide').val();
			addStr=Object(addStr);
		}
		var signin_html='<div id="signin_module" class="global_signin_module">';
			signin_html=signin_html+'<div class="box_bg"></div>'+(Model?'<a class="noCtrTrack" id="signin_close">×</a>':'');
			signin_html=signin_html+'<div id="lb-wrapper"><form name="signin_form" class="login" method="POST">';
				signin_html=signin_html+'<h3>'+lang_obj.signIn.title+'</h3>';
				signin_html=signin_html+'<div id="error_login_box" class="error_note_box">'+lang_obj.signIn.error_note+'</div>';
				signin_html=signin_html+'<div class="row"><label for="Email">'+lang_obj.signIn.email+'</label><input name="Email" class="lib_txt" type="text" maxlength="100" format="Email" notnull /></div>';
				signin_html=signin_html+'<div class="row"><label for="Password">'+lang_obj.signIn.password+'</label><input name="Password" class="lib_txt" type="password" notnull /></div>';
				signin_html=signin_html+'<div class="row">'+lang_obj.signIn.forgot+'</div>';
				signin_html=signin_html+'<div class="row protect"><input class="ckb" type="checkbox" name="IsStay" value="1" checked="checked" /> '+lang_obj.signIn.stay_note+'</div>';
				signin_html=signin_html+'<div class="row"><button class="signbtn signin FontBgColor FontBorderColor" type="submit">'+lang_obj.signIn.sign_in+'</button>'+(!obj.find('form.register').length?('<a href="/account/sign-up.html" class="signbtn signup">'+lang_obj.signIn.join_fee+'</a>'):'')+'</div>';
			signin_html=signin_html+'<input type="hidden" name="do_action" value="user.login" />';
			if(obj.find('input[name=jumpUrl]').length) signin_html=signin_html+'<input type="hidden" name="jumpUrl" value="'+$('input[name=jumpUrl]').serialize().replace('jumpUrl=', '')+'" />';
			if(Url) signin_html=signin_html+'<input type="hidden" name="jumpUrl" value="'+Url+'" />';
			signin_html=signin_html+'</form></div>';
		signin_html=signin_html+'</div>';
		
		obj.find('#signin_module').length && obj.find('#signin_module').remove();
		obj.prepend(signin_html);
		obj.find('#signin_module').css({left:w.width()/2-220,top:'20%'});
		global_obj.div_mask();
	},
	
	sign_up_init:function(){
		var frm_register=$('#signup form.register');
		frm_register.find('input[name=Birthday]').attr('readonly', 'readonly');
		frm_register.submit(function(){return false;});
		frm_register.find('button:submit').click(function(){
			var status=0;
			if(global_obj.check_form(frm_register.find('*[notnull]'), frm_register.find('*[format]'), 1, 1)){
				status+=1;
			}else status+=0;
			
			if(/^\w+[a-zA-Z0-9-.+_]+@[a-zA-Z0-9-.+_]+\.\w*$/.test($.trim($('#Email').val()))==false){
				$('#Email').next().show();
				status+=1;
			}else{
				$('#Email').next().hide();
				status+=0;
			}
			
			if($.trim($('#Password').val())!=$.trim($('#Password2').val())){
				$('#Password2').next().show();
				status+=1;
			}else{
				$('#Password2').next().hide();
				status+=0;
			}
			if(status) return false;
			$(this).attr('disabled', true);
			
			$.post('/', frm_register.serialize(), function(data){
				frm_register.find('button:submit').attr('disabled', false);
				if(data.ret!=1){
					$('#error_register_box').html(data.msg[0]).show();
					$("body, html").animate({scrollTop:$("#error_register_box").offset().top}, 500);
					if($('#Code').length){ //有验证码图片
						$('#Code').next().click();
					}
				}else{
					//When a sign up is completed, such as signup for trial.
					if(parseInt(ueeshop_config.FbPixelOpen)==1){
						fbq("track", "Lead");
						fbq('track', 'CompleteRegistration', {
							value: '0.00',
							currency: 'USD'
						});
					}
					setTimeout(function(){window.location=data.msg[0]}, 1000);
				}
			}, 'json');
		});
		
		$('.amount').keydown(function(e){
			var value=$(this).val();
			var key=window.event?e.keyCode:e.which;
			if((key>95 && key<106) || (key>47 && key<60) || (key==109 && value.indexOf("-")<0) || (key==110 && value.indexOf(".")<0) || (key==190 && value.indexOf(".")<0)){
			}else if(key!=8){
				if(window.event){//IE
					e.returnValue=false;
				}else{//Firefox
					e.preventDefault();
				}
				return false;
			}
		});
		
		$('#send_email_btn').on('click', function(){
			$email=$.trim($(this).attr('email'));
			$uid=$(this).attr('uid');
			$.post('/', 'do_action=action.verification_mail&Email='+$email+'&UserId='+$uid, function(data){
				if(data.ret==1){
					alert(lang_obj.user.send_email_ture);
				}else{
					alert(lang_obj.user.send_email_false);
				}
			}, 'json');
		});
	}, 
	
	user_login_binding:function(){
		$('body').off().on('click', '#binding_close, #btn_cannel, #div_mask', function(){ //关闭
			if($('#binding_module').length && $('#binding_close').length){
				$('#binding_module').remove();
				global_obj.div_mask(1);
			}
		});
		
		var frm_binding=$('#binding_module form.login');
		frm_binding.submit(function(){return false;});
		frm_binding.find('button:submit').click(function(){
			if(global_obj.check_form(frm_binding.find('*[notnull]'), frm_binding.find('*[format]'), 0, 1)){return false;}
			$(this).attr('disabled', true);
			$.post('/', frm_binding.serialize()+'&do_action=user.user_oauth_binding', function(data){
				frm_binding.find('button:submit').attr('disabled', false);
				if(data.ret!=1){
					$('#error_login_box').html(data.msg[0]).show();
				}else{
					window.location=data.msg;
				}
			}, 'json');
		});
	},
	
	forgot_init:function (){
		var frm_register=$('#signup form.register');
		frm_register.submit(function(){return false;});
		frm_register.find('.fotgotbtn').click(function(){//发送忘记密码邮件
			if(global_obj.check_form(frm_register.find('*[notnull]'), frm_register.find('*[format]'), 1, 1)){
				status=1;
			}else status=0;
			
			if(/^\w+[a-zA-Z0-9-.+_]+@[a-zA-Z0-9-.+_]+\.\w*$/.test($('#Email').val())==false){
				$('#Email').next().show();
				status=1;
			}else{
				$('#Email').next().hide();
				status=0;
			}
			
			if(status==1) return false;
			$(this).attr('disabled', true);
			
			$.post('/account/', frm_register.serialize(), function(data){
				frm_register.find('.fotgotbtn').attr('disabled', false);
				if(data.ret!=1){
					$('#error_register_box').html(data.msg[0]).show();
				}else{
					window.location=data.msg[0];
				}
			}, 'json');
		});
		
		frm_register.find('.resetbtn').click(function(){//发送忘记密码邮件
			if(global_obj.check_form(frm_register.find('*[notnull]'), frm_register.find('*[format]'), 1, 1)){
				status=1;
			}else status=0;
			
			if($('#Password').val() && $('#Password2').val()){
				if($('#Password').val()!=$('#Password2').val()){
					$('#Password2').next().show();
					status=1;
				}else{
					$('#Password2').next().hide();
					status=0;
				}
			}else{
				status=1;
			}
			
			if(status==1) return false;
			$(this).attr('disabled', true);
			
			$.post('/account/', frm_register.serialize(), function(data){
				frm_register.find('.resetbtn').attr('disabled', false);
				if(data.ret!=1){
					$('#error_register_box').html(data.msg[0]).show();
				}else{
					window.location=data.msg[0];
				}
			}, 'json');
		});
	},
	/******************* 登录或注册 End *******************/
	
	/******************* 会员首页 Start *******************/
	user_index_init:function(){
		user_obj.edit_pay_init();
		$('.order_table .see_more').click(function(){
			if($(this).hasClass('cur')){
				$(this).removeClass('cur').parent().parent().parent().parent().find('.hide').hide();
			}else{
				$(this).addClass('cur').parent().parent().parent().parent().find('.hide').show();
			}
		});
		$('.user_ind_ptype a').click(function(){
			var ind = $(this).index('.user_ind_ptype a');
			$('.user_ind_ptype a').removeClass('cur').eq(ind).addClass('cur');
			$(this).parent().next('.user_page_pro').find('.pro_list').hide().eq(ind).show();
		});
		
		$('.user_get_coupons .get_it').click(function(){
			var	_this = $(this), 
				CId = _this.attr('data-cid');
			$.post('/', 'do_action=user.get_user_coupons&CId='+CId, function(data){
				if(data.ret==1){
					global_obj.new_win_alert(data.msg,function(){
						_this.parent().remove();
					}, '', undefined, '', lang_obj.global.ok);
				}else{
					global_obj.new_win_alert(data.msg);
				}
			}, 'json');
		});

		$('.remove_newsletter').click(function(){
			var $email=$(this).attr('email');
			global_obj.new_win_alert(lang_obj.global.del_confirm, function(){
				$.post('/', 'do_action=user.cancel_newsletter&Email='+$email, function(data){
					if(data.ret==1){
						window.location.reload();
					}else{
						global_obj.new_win_alert(data.msg);
					}
				}, 'json');
			}, 'confirm');
		});
	},
	/******************* 会员首页 End *******************/
	
	order_init:function(){
		
		user_obj.edit_pay_init();
		$('.order_table .see_more').click(function(){
			if($(this).hasClass('cur')){
				$(this).removeClass('cur').parent().parent().parent().parent().find('.hide').hide();
			}else{
				$(this).addClass('cur').parent().parent().parent().parent().find('.hide').show();
			}
		});
		

		$('#cancelForm').submit(function(){
			if(global_obj.check_form($(this).find('*[notnull]'))){
				return false;
			}else{
				var result=window.confirm(lang_obj.user.order_cancel);
				if(result){
					$.post('/?do_action=user.cancel_order', $('#cancelForm').serialize(), function(data){
						window.location.href='/account/orders/';
					});
				}
			}
			return false;
		});

		$('.confirm_receiving').click(function(){
			var _this = $(this);
			global_obj.new_win_alert(lang_obj.user.sure,function(){
				$.post('/?do_action=user.confirm_receiving', {OId:_this.attr('oid')}, function(data){
					window.location.reload();
				});
			},'confirm');
			return false;
		});
		/*
		$('.payment2btn').click(function(){
			if($('form[name=paypal_checkout_form]').length){ //Paypal支付方式
				$('#paypal_checkout_button').click();
			}else{
				window.open($(this).attr('href'));
			}
			return false;
		});
		*/
		if ($('#reply_form').length){
			$('#reply_form').submit(function(){
				if(global_obj.check_form($('#reply_form').find('*[notnull]'))){return false};
			});
			$('#lib_user_products .item .light_box_pic').lightBox();
			document.getElementById('View').scrollIntoView();
		}
	},
	
	coupon_init:function(){
		var maxWidth=0,
			coupon_item_size=function(){
			$('.cou_list .item').each(function(){
				$(this).find('.itl').width()>maxWidth && (maxWidth=$(this).find('.itl').width());
			});
			$('.cou_list .item').each(function(){
				$(this).find('.itl').css('width', maxWidth);
				$(this).find('.itr').css('width', $(this).outerWidth(true)-maxWidth-17-10);
			});
		}
		$(window).resize(function(){
			coupon_item_size();
		});
		coupon_item_size();
	},
	
	user_address:function(){
		$('.chzn-container-single .chzn-search').css('height', $('.chzn-container-single .chzn-search input').height());
		!address_perfect && user_obj.set_default_address(0);
		$('a.chzn-single').off().on('click', function(){
			$(this).parent().next('p.errorInfo').text('');
			if($(this).hasClass('chzn-single-with-drop')){
				$(this).blur().removeClass('chzn-single-with-drop').next().css({'left':'-9000px'}).parent().removeClass('chzn-container-active').css('z-index', '0').find('li.result-selected').removeClass('highlighted');
			}else{
				$(this).blur().addClass('chzn-single-with-drop').next().css({'left':'0', 'top':'41px'}).parent().addClass('chzn-container-active').css('z-index', '11').find('li.result-selected').addClass('highlighted');
				if(!$('#country_chzn li.group-result:eq(0)').next('li.group-option').length) $('#country_chzn li.group-result').hide();
			}
		});
		$('.chzn-results li.group-option').live('mouseover', function(){
			$(this).parent().find('li').removeClass('highlighted');
			$(this).addClass('highlighted');
		}).live('mouseout', function(){
			$(this).removeClass('highlighted');
		});
		$('#country_chzn li.group-option').click(function(){	//Select Country
			var obj		= $('#country_chzn li.group-option').removeClass('result-selected').index($(this));
			var s_cid	= $('select[name=country_id]').val();
			$(this).addClass('result-selected').parent().parent().css({'left':'-9000px'}).parent().removeClass('chzn-container-active').children('a').removeClass('chzn-single-with-drop').find('span').text($(this).text()).parent().parent().prev().find('option').eq(obj+1).attr('selected', 'selected');
			var cid = $('select[name=country_id]').val();
			(s_cid!=cid) && user_obj.get_state_from_country(cid);	//change country
		});
		$('#zoneId li.group-option').live('click', function(){
			var obj=$('#zoneId li.group-option').removeClass('result-selected').index($(this));
			$(this).addClass('result-selected').parent().parent().css({'left':'-9000px'}).parent().removeClass('chzn-container-active').children('a').removeClass('chzn-single-with-drop').find('span').text($(this).text()).parent().parent().prev().find('option').eq(obj+1).attr('selected', 'selected');
		});
		$(document).click(function(e){ 
			e	= window.event || e; // 兼容IE7
			obj	= $(e.srcElement || e.target);
			if(!$(obj).is("#country_chzn, #country_chzn *")){ 
				$('#country_chzn').removeClass('chzn-container-active').css('z-index', '0').children('a').blur().removeClass('chzn-single-with-drop').end().children('.chzn-drop').css({'left':'-9000px'}).find('input').val('').parent().next().find('.group-option').addClass('active-result');
			} 
			if(!$(obj).is("#zoneId .chzn-container, #zoneId .chzn-container *")){ 
				$('#zoneId .chzn-container').removeClass('chzn-container-active').css('z-index', '0').children('a').blur().removeClass('chzn-single-with-drop').end().children('.chzn-drop').css({'left':'-9000px'}).find('input').val('').parent().next().find('.group-option').addClass('active-result');
			} 
		});
		jQuery.expr[':'].Contains=function(a,i,m){
			return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase())>=0;
		};
		function filterList(input, list){ 
			$(input)
			.change(function(){
				var filter=$(this).val();
				if(filter){
					$matches=$(list).find('li:Contains(' + filter + ')');
					$('li', list).not($matches).removeClass('active-result');
					$matches.addClass('active-result');
				}else{
					$(list).find("li").addClass('active-result');
				}
				return false;
			})
			.keyup(function(){
				$(this).change();
			});
		}
		filterList("#country_chzn .chzn-search input", $("#country_chzn .chzn-results"));
		filterList("#zoneId .chzn-search input", $("#zoneId .chzn-results"));
		
		/*
		$('#save_address').on('click', function(){ //提交会员地址资料
			if(!check_form_address()){ return false; }
			$(this).attr('disabled', 'disabled');
			var obj=$('.user_address_form');
			var typeAddr=parseInt(obj.find('input[name=typeAddr]').val())==1?1:0;
			if(typeAddr==1){
				cart_obj.checkout_no_login();
			}else{
				$.post('/', obj.serialize()+'&do_action=user.addressbook_mod', function(data){
					if(data.ret==1){
						var $location=window.location.href;
						if($location.indexOf('/account/address/add.html')>0){
							window.top.location='/account/address/';
						}else{
							window.top.location.reload();
						}
					}
				}, 'json');
			}
			$(this).removeAttr('disabled');
			return false;
		});
		*/
		var address_rq_mark=true;
		$('.user_address_form').submit(function(){ return false; });
		$('#save_address').on('click', function(){
			if(address_rq_mark && !$('#save_address').hasClass('disabled')){
				var $notnull=$('.user_address_form input[notnull], .user_address_form select[notnull]'),
					$TypeAddr=parseInt($('.user_address_form input[name=typeAddr]').val())==1?1:0,
					$errorObj=new Object;
				$('#save_address').addClass('disabled');
				address_rq_mark=false;
				setTimeout(function(){
					var status=0;
					$notnull.each(function(){
						$errorObj=($(this).attr('name')=='PhoneNumber'?$(this).parent().parent().next('p.error'):$(this).parent().next('p.error'));
						if($.trim($(this).val())==''){
							$(this).addClass('null');
							$errorObj.text(lang_obj.user.address_tips.PleaseEnter.replace('%field%', $(this).attr('placeholder'))).show();
							status++;
							if(status==1){
								$('body,html').animate({scrollTop:$(this).offset().top-20}, 500);
							}
						}else{
							$(this).removeClass('null');
							$errorObj.hide();
						}
					});
					$('.user_address_form input[format][notnull]').each(function(){
						$errorObj=$(this).parent().next('p.error');
						$format=$(this).attr('format').split('|');
						if($format[0]=='Length' && $.trim($(this).val()).length<parseInt($format[1])){
							$(this).addClass('null');
							$errorObj.text(lang_obj.format.length.replace('%num%', $format[1])).show();
							status++;
							if(status==1){
								$('body,html').animate({scrollTop:$(this).offset().top-20}, 500);
							}
						}else{
							$(this).removeClass('null');
							$errorObj.hide();
						}
					});
					if(status){ //检查表单
						address_rq_mark=true;
						$('#save_address').removeClass('disabled');
						return false;
					}
					$.post('/', $('.user_address_form').serialize()+'&do_action=user.addressbook_mod', function(data){
						if(data.ret==1){
							var $location=window.location.href;
							if($location.indexOf('/account/address/add.html')>0){
								window.top.location='/account/address/';
							}else{
								window.top.location.reload();
							}
						}
					}, 'json');
					address_rq_mark=true;
					$('#save_address').removeClass('disabled');
				}, 100);
			}
			return false;
		});
		
		function set_tax_code_value(obj, v){
			maxlen=obj.val()==1?11:14;
			obj.next('input[name=tax_code_value]').attr('maxlength', maxlen);
			v==1 && obj.next('input[name=tax_code_value]').val('');
		}
		$('select[name=tax_code_type]').change(function(){set_tax_code_value($(this), 1);});
		set_tax_code_value($('select[name=tax_code_type]').not(':disabled'));
		
		$('select[name=country_id]').change(function(){ //使用谷歌浏览器的自动表单填写功能，出现country_id自动选择，相关联效果不能自动实现
			var name=$('select[name=country_id] option:selected').text(),
				cid=$('select[name=country_id]').val();
			$('#country_chzn li.group-option').each(function(){
				if($(this).text()==name){
					$(this).click();
					user_obj.get_state_from_country(cid); //已经自动选择国家选项，需要执行加载省份
				}
			});
		});
	},
	
	get_state_from_country:function(cid){
		$.ajax({
			url:"/",
			async:false,
			type:"POST",
			data:{"CId": cid, do_action:'user.select_country'},
			dataType:"json",
			success: function(data){
				if(data.ret==1){
					d=data.msg.contents;
					if(d==-1){
						$('#zoneId').css({'display':'none'}).find('select').attr('disabled', 'disabled');
						$('#state').css({'display':'block'}).find('input').removeAttr('disabled');
					}else{
						$('#zoneId').css({'display':'block'}).find('select').removeAttr('disabled');
						$('#state').css({'display':'none'}).find('input').attr('disabled', 'disabled');
						str='';
						var vselect='<option value="-1"></option>';
						var vli='';
						for(i=0; i<d.length; i++){
							vselect+='<option value="'+d[i]['SId']+'">'+d[i]['States']+'</option>';
							vli+='<li class="group-option active-result">'+d[i]['States']+'</li>';
						}
						$('#zoneId select').html(vselect);
						$('#zoneId ul').html(vli);
						$('#zoneId .chzn-container a span').text(lang_obj.global.selected+'---');
					}
					$('#countryCode').val('+'+data.msg.code);
					$('#phoneSample span').text(data.msg.code);
					if(data.msg.cid==30){
						$('#taxCode').css({'display':'block'}).find('select, input').removeAttr('disabled');
						$('#taxCode').find('input').attr('notnull', 'notnull');
						$('#tariffCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled').parent().find('p.errorInfo').text('');
						$('#tariffCode').find('input').removeAttr('notnull');
					}else if(data.msg.cid==211){
						$('#tariffCode').css({'display':'block'}).find('select, input').removeAttr('disabled');
						$('#tariffCode').find('input').attr('notnull', 'notnull');
						$('#taxCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled').parent().find('p.errorInfo').text('');
						$('#taxCode').find('input').removeAttr('notnull');
					}else{
						$('#taxCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled').parent().find('p.errorInfo').text('');
						$('#tariffCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled').parent().find('p.errorInfo').text('');
						$('#taxCode, #tariffCode').find('input').removeAttr('notnull');
					}
				}
			}
		});
	},
	
	set_default_address:function(AId){
		$.ajax({
			url:"/",
			async:false,
			type:'post',
			data:{'do_action':'user.get_addressbook', 'AId':AId},
			dataType:'json',
			success:function(data){
				if(data.ret==1){
					$('input[name=edit_address_id]').val(data.msg.address.AId);
					$('input[name=FirstName]').val(data.msg.address.FirstName);
					$('input[name=LastName]').val(data.msg.address.LastName);
					$('input[name=AddressLine1]').val(data.msg.address.AddressLine1);
					$('input[name=AddressLine2]').val(data.msg.address.AddressLine2);
					$('input[name=City]').val(data.msg.address.City);
					
					var index=$('select[name=country_id]').find('option[value='+data.msg.address.CId+']').eq(0).attr('selected', 'selected').index();
					$('#country_chzn a span').text(data.msg.country.Country);
					$('#country_chzn ul.chzn-results li.group-option').eq(index).addClass('result-selected');
					user_obj.get_state_from_country(data.msg.address.CId);
					if(data.msg.address.CId==30||data.msg.address.CId==211){
						$('select[name=tax_code_type]').find('option[value='+data.msg.address.CodeOption+']').attr('selected', 'selected');
						$('input[name=tax_code_value]').attr('maxlength', (data.msg.address.CodeOption==1?11:14)).val(data.msg.address.TaxCode);
					}
					
					if(data.msg.country.HasState==1){
						$('#zoneId div a span').text(data.msg.address.StateName);
						var sindex=$('select[name=Province]').find('option[value='+data.msg.address.SId+']').attr('selected', 'selected').index();
						$('#zoneId ul.chzn-results li.group-option').eq(sindex-1).addClass('result-selected');
					}else{
						$('input[name=State]').val(data.msg.address.State);
					}
					
					$('input[name=ZipCode]').val(data.msg.address.ZipCode);
					$('input[name=CountryCode]').val('+'+data.msg.address.CountryCode);
					$('input[name=PhoneNumber]').val(data.msg.address.PhoneNumber);
					
				}else if(data.ret==2){
					$('input[name=edit_address_id], input[name=FirstName], input[name=LastName], input[name=AddressLine1], input[name=AddressLine2], input[name=City], input[name=tax_code_value], input[name=State], input[name=ZipCode], input[name=CountryCode], input[name=PhoneNumber]').val('');

					var index=$('select[name=country_id]').find('option[value='+data.msg.country.CId+']').eq(0).attr('selected', 'selected').index();
					$('#country_chzn a span').text(data.msg.country.Country);
					$('#country_chzn ul.chzn-results li.group-option').eq(index).addClass('result-selected');
					user_obj.get_state_from_country(data.msg.country.CId);
				}else{
					global_obj.new_win_alert(data.msg.error);
				}
				
				$('.user_address_form .input_box_txt').each(function(){
					if($.trim($(this).val())!=''){
						$(this).parent().addClass('filled');
					}else{
						$(this).parent().removeClass('filled');
					}
				});
			}
		});
	},
	
	address_init:function(){
		$('.address_menu .menu_title li').click(function(){
			if(!$(this).hasClass('add') && $('#addressForm').css('display')=='none'){
				$('.address_menu .menu_title li a').removeClass('current').removeClass('FontBorderColor');
				$(this).find('a').addClass('current').addClass('FontBorderColor');
				$('.address_menu .menu_content .menu').eq($(this).index()).removeClass('hide').siblings().addClass('hide');
				if($(this).hasClass('shipping')){
					$(this).parent().find('.add').show();
				}else{
					$(this).parent().find('.add').hide();
				}
			}	
		});	
		$('#cancel_address').click(function(){
			$('#addressForm').slideUp('fast', function(){
				$('.address_menu .menu_content').slideDown('fast');
			});
			return false;
		});
		$('.address_list .options a[name=edit]').click(function(){
			var addrId=$(this).data('addrid');
			$('#addressForm .errorInfo').html('');
			user_obj.set_default_address(addrId);
			$('.address_menu .menu_content').slideUp('fast',function(){
				$('#addressForm').slideDown('fast');	
			});
			return false;
		});
		$('.address_list .options a[name=del]').click(function(){
			return window.confirm(lang_obj.user.delete_shipping);
		});
		$('.address_menu .menu_title li.add').click(function(){
			$('#addressForm .errorInfo').html('');
			user_obj.set_default_address(0);
			$('.address_menu .menu_content').slideUp('fast',function(){
				$('#addressForm').slideDown('fast');	
			});
			return false;
		});
		$('.address_list .options a[name=default]').click(function(){
			$.post('/account/', 'do_action=user.addressbook_selected&AId='+$(this).data('addrid'), function(data){
				if(data.ret==1){
					window.location.reload();
				}
			}, 'json');
		});
	},
	
	inbox_init:function(){
		user_obj.inbox_click();
		user_obj.inbox_ajax_list('inbox_list');
		user_obj.inbox_ajax_list('outbox_list');
		$('.msg_view .rows .light_box_pic').lightBox();

	},

	write_inbox:function(){
		$('#inbox_form').submit(function(){
			if(global_obj.check_form($('#inbox_form').find('*[notnull]'))){return false};
		});
	},
	
	inbox_click:function(){
		$('.menu_content').off().on('click', 'a.page_item,a.page_button', function(){
			var name=$(this).attr('data'),
				obj=$('#'+name),
				page=$(this).attr('page');
			
			obj.attr('page', page);
			user_obj.inbox_ajax_list(name);
			return false;
		});
	},
	
	inbox_ajax_list:function(name){
		var obj=$('#'+name),
			page=obj.attr('page');
		
		$.post('/', {Name:name, Page:page, do_action:'user.get_inbox_list'}, function(data){
			data=$.evalJSON(data);
			if(data.ret==1){
				obj.html(data.msg);
			}
		});
	},
	
	products_init:function(){
		//详细页面
		$('#reply_form').submit(function(){
			if(global_obj.check_form($('#reply_form').find('*[notnull]'))){return false};
		});
		if($('#lib_user_products .light_box_pic').length){
			$('#lib_user_products .light_box_pic').lightBox();
		}
		if($('#View').length){
			document.getElementById('View').scrollIntoView();
		}
	},

	edit_pay_init:function(){
		//编辑支付方式
		$('.edit_pay_btn').click(function(){
			if($(this).attr('disabled')) return false;
			$(this).blur().attr('disabled', 'disabled');
			var $OId=$(this).attr('oid');
			$.ajax({
				type: "POST",
				url: "/?do_action=cart.get_payment_methods",
				dataType: "json",
				data:{'OId':$OId},
				success: function(data){
					if(data.ret==1){
						var c=data.msg.info,
							defaultPId=0,
							payment_list='',
							pay_content='',
							feePrice=0,
							total=data.msg.total_price,
							PId=parseInt($('.edit_pay_btn').attr('pid'));
							total=parseFloat(total.replace(data.msg.currency_symbols, '', total));
						for(i=0; i<c.length; i++){
							if(PId==c[i].PId) defaultPId=c[i].PId;
							var s=defaultPId==c[i].PId?'checked="checked"':'';
							var i_feePrice=$('html').currencyFormat(parseFloat(total*(c[i].AdditionalFee/100)+parseFloat(c[i].AffixPrice)).toFixed(2), data.msg.currency);
							payment_list+='<div class="item"><input type="radio" name="PId" value="'+c[i].PId+'" fee="'+c[i].AdditionalFee+'" affix="'+c[i].AffixPrice+'" '+s+' /><span class="pic_box"><img src="'+c[i].LogoPath+'" /><span></span></span><span class="name">'+c[i].Name;
							if(i_feePrice>0) payment_list+=' ( +'+data.msg.currency_symbols+i_feePrice+' )';
							payment_list+='</span></div>';
							var w=defaultPId==c[i].PId?'':'style="display:none;"';
							// pay_content+='<div class="pay_contents_'+c[i].PId+'" '+w+'>'+c[i].Description+'</div>';
							//defaultPId==c[i].PId && (total+=parseFloat(c[i].AdditionalFee));
							defaultPId==c[i].PId && (feePrice=parseFloat(total*(c[i].AdditionalFee/100)+parseFloat(c[i].AffixPrice)));	//付款手续费
						}
						var pay_html='<div id="alert_choose" class="alert_choose">';
							pay_html+='<div class="box_bg"></div><a class="noCtrTrack BuyNowBgColor" id="choose_close">×</a>';
							pay_html+='<div class="choose_content"><form name="pay_edit_form" method="POST" action="">';
								pay_html+='<h2>'+lang_obj.cart.checkout+'</h2>';
								pay_html+='<h3>'+lang_obj.cart.payment+': </h3>';
								pay_html+='<div class="payment_list">'+payment_list+'</div>';
								// pay_html+='<div class="pay_content">'+pay_content+'</div>';
								pay_html+='<p class="footRegion">';
									pay_html+='<input class="btn BuyNowBgColor" id="pay_button" type="submit" value="'+lang_obj.cart.pay_now+'" /><span class="choose_price">'+lang_obj.orders.order_total+': <span>'+data.msg.currency_symbols+$('html').currencyFormat(parseFloat(total+feePrice).toFixed(2), data.msg.currency)+'</span></span>';
								pay_html+='</p>';
							pay_html+='<input type="hidden" name="TotalPrice" value="'+total+'" /><input type="hidden" name="Symbols" value="'+data.msg.currency_symbols+'" /><input type="hidden" name="Currency" value="'+data.msg.currency+'" /><input type="hidden" name="OId" value="'+$OId+'" /></form></div>';
						pay_html+='</div>';
						
						$('#alert_choose').length && $('#alert_choose').remove();
						$('body').prepend(pay_html);
						$('#alert_choose').css({left:$(window).width()/2-220,top:'20%'});
						global_obj.div_mask();
						
						//提交编辑支付方式
						$('form[name=pay_edit_form]').submit(function(){ return false; });
						$('#pay_button').click(function(){
							var obj=$('form[name=pay_edit_form]'),
								OId=$('input[name=OId]').val();
							$(this).attr('disabled', 'disabled').blur();
							
							$.post('/?do_action=cart.orders_payment_update', obj.serialize(), function(data){
								window.location.href='/cart/complete/'+OId+'.html';

							});
							return false;
						});
					}else{
						global_obj.new_win_alert(lang_obj.products.sign_in, function(){window.top.location='/account/login.html';});
					}
				}
			});
			return false;
		});
		
		//关闭编辑支付方式
		$('body').on('click', '#choose_close, #div_mask, #exback_button', function(){
			if($('#alert_choose').length){
				$('#alert_choose').remove();
				global_obj.div_mask(1);
				$('.edit_pay_btn').removeAttr('disabled');
			}
		});

		//选择支付方式
		$('body').on('change', 'form[name=pay_edit_form] input[name=PId]', function(){
			var PId=$(this).val(),
				Fee=parseFloat($(this).attr('fee')),
				Affix=parseFloat($(this).attr('affix')),
				currency=$('form[name=pay_edit_form] input[name=Currency]').val();
				total_price=parseFloat($('form[name=pay_edit_form] input[name=TotalPrice]').val());
				feePrice=total_price*(Fee/100)+Affix;	//付款手续费
			
			$('.choose_price>span').text($('form[name=pay_edit_form] input[name=Symbols]').val()+$('html').currencyFormat(parseFloat(total_price+feePrice), currency));
			// $('.pay_content>div.pay_contents_'+PId).css('display', 'block').siblings().css('display', 'none');
		});
	}

};