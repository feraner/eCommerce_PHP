/*
 * 广州联雅网络
 */

(function($, win){
	win.user_obj={
		user_index:function(){
			
		},
		
		user_order:function(){
			$('#cancelForm .m_form_button').on('tap', function (){
				$('#cancelForm').submit();
			});
			$('#cancelForm').submit(function (){
				if($('textarea[name=CancelReason]').val()==''){
					$('textarea[name=CancelReason]').addClass('null');
					return false;
				}else{
					return window.confirm('Are you sure you want to delete this pending order?');
				}
			});
			
			if($('#orderlist').length){
				ajax_user_orders_list({"page":0}, 1);
				
				$(document).scroll(function(){
					var $winTop		= $(window).scrollTop(),
						$winH		= $(window).height(),
						$listTop	= $("#orderlist").offset().top,
						$listHeight	= $("#orderlist").outerHeight(),
						$loadNum	= $listTop+$listHeight-$winH-100,
						$Num		= parseInt($("#orderlist").attr('data-number'));
					
					if($winTop>=$loadNum && $Num==0){
						$("#orderlist").attr('data-number', '1');
						var page=parseInt($("#orderlist").attr("data-page"));
						if(!isNaN(page)){
							var Total=parseInt($("#orderlist").attr('data-total'));
							if(page>=Total){
								return false;
							}
							$("#orderlist").attr('data-page', page+1);
							ajax_user_orders_list({"page":page+1}, 0);
						}
					}
				});
			}
			
			function ajax_user_orders_list(data, clear){
				clear && $("#orderlist").html('');
				$("#orderlist").loading();
				$(".loading_msg").css({"top":0, "position":"initial", "width":"auto", "height":'4rem', "background-position":"center"});
				setTimeout(function(){
					$.ajax({
						url:"/ajax/user_orders_list.html",//"/?m=ajax&a=user_orders_list",
						async:false,
						type:'post',
						data:data,
						dataType:'html',
						success:function(result){
							if(result){
								$("#orderlist").append(result).unloading();
								$("#orderlist").attr('data-number', '0');
							}else{
								//var html='<div class="no_product">'+lang_obj.seckill.no_products+'</div>';
								$("#orderlist").append(html).unloading();
								$("#orderlist").attr('data-number', '1');
							}
							
							$('#orderlist .item').on('tap', '.title, .prod_list', function(){
								window.top.location.href=$(this).parents('.item').attr('data-url');
							});
							
							$('#orderlist .btn_more').off().on('tap', function(){
								if($(this).hasClass('current')){
									$(this).removeClass('current');
									$(this).parents('.item').find('.prod_tr').slideUp();
								}else{
									$(this).addClass('current');
									$(this).parents('.item').find('.prod_tr').slideDown();
								}
								return false;
							});
						}
					});
				}, 300);
			}
			
			//选择支付方式
			$('body').on('change', 'form[name=pay_edit_form] select[name=PId]', function(){
				var PId=$(this).val(),
					Fee=parseFloat($(this).children('option:selected').attr('fee')),
					Affix=parseFloat($(this).children('option:selected').attr('affix')),
					total_price=parseFloat($('input[name=TotalPrice]').val());
					feePrice=total_price*(Fee/100)+Affix;	//付款手续费
				
				$('.total_price').text($('input[name=Symbols]').attr('currency')+' '+$('input[name=Symbols]').val()+parseFloat(total_price+feePrice).toFixed(2));
				//$('.pay_content>div.pay_contents_'+PId).css('display', 'block').siblings().css('display', 'none');
			});
			
			//提交编辑支付方式
			$('form[name=pay_edit_form]').submit(function(){ return false; });
			$('#pay_button').click(function(){
				var obj=$('form[name=pay_edit_form]');
				$(this).attr('disabled', 'disabled').blur();
				
				$.post('/?do_action=cart.orders_payment_update', obj.serialize(), function(data){
					window.top.location=$('form[name=pay_edit_form] input[name=BackLocation]').val();
				});
				return false;
			});
		},
		
		user_login:function(){
			$('.user_login_form').submit(function(e) {return false;});
			
			//重新发送验证邮件
			$('#send_email_btn').on('click', function(){
				var $email=$.trim($(this).attr('email'));
				var $uid=$(this).attr('uid');
				$.post('/', 'do_action=action.verification_mail&Email='+$email+'&UserId='+$uid, function(data){
					if(data.ret==1){
						alert(lang_obj.user.send_email_ture);
					}else{
						alert(lang_obj.user.send_email_false);
					}
				}, 'json');
			});

			//注册
			var reg_form	= $('#reg_form'),
				reg_notnull	= $('input[notnull]', reg_form),
				reg_rq_mark	= true;
			$('.btn_submit', reg_form).on('tap', function(e){
				if(reg_rq_mark){
					reg_notnull.parent().parent().removeClass('null');
					setTimeout(function(){
						var status=0,
							move=0,
							$Email=$('input[name=Email]', reg_form),
							$pwd=$('input[name=Password]', reg_form),
							$pwd2=$('input[name=Password2]', reg_form);
						$Email.removeClass('null').next('p.error').hide();
						$pwd.removeClass('null').next('p.error').hide();
						$pwd2.removeClass('null').next('p.error').hide();
						if($.trim($Email.val())==''){
							$Email.addClass('null').next('p.error').text(lang_obj.user.reg_error.PleaseEnter.replace('%field%', $Email.attr('data-field'))).show();
							status+=1;
							$('body, html').animate({scrollTop:$Email.offset().top-20}, 50);
							move=1;
						}
						if($.trim($Email.val()) && /^\w+[a-zA-Z0-9-.+_]+@[a-zA-Z0-9-.+_]+\.\w*$/.test($.trim($Email.val()))==false){
							$Email.addClass('null').next('p.error').text(lang_obj.user.reg_error.EmailFormat).show();
							status+=1;
							if(!move){
								$('body, html').animate({scrollTop:$Email.offset().top-20}, 50);
								move=1;
							}
						}
						if($pwd.val()=='' || $pwd2.val()=='' || $pwd.val()!=$pwd2.val()){
							if($pwd.val()==''){
								$pwd.addClass('null').next('p.error').text(lang_obj.user.reg_error.PleaseEnter.replace('%field%', $pwd.attr('data-field'))).show();
							}else if($pwd2.val()==''){
								$pwd2.addClass('null').next('p.error').text(lang_obj.user.reg_error.PWDConfirm).show();
							}else if($pwd.val()!=$pwd2.val()){
								$pwd2.addClass('null').next('p.error').text(lang_obj.user.reg_error.PWDNotMatch).show();
							}
							status+=1;
							if(!move){
								$('body, html').animate({scrollTop:$pwd.offset().top-20}, 50);
								move=1;
							}
						}
						reg_notnull.each(function(index, element){
							if($(element).attr('name')=='Email' || $(element).attr('name')=='Password' || $(element).attr('name')=='Password2'){ return true; }
							if($(element).val()==''){
								$(element).addClass('null').next('p.error').text(lang_obj.user.reg_error.PleaseEnter.replace('%field%', $(element).attr('data-field'))).show();
								status+=1;
								if(!move){
									$('body, html').animate({scrollTop:$(element).offset().top-20}, 50);
									move=1;
								}
							}else{
								$(element).removeClass('null').next('p.error').hide();
							}
						});
						if(status){
							return false;
						}
						//通过验证，提交数据
						reg_rq_mark=false;
						$.post('/?m=user', reg_form.serializeArray(), function (data){
							if(data.ret!=1){
								$('html').tips_box(data.msg, 'error');
								reg_rq_mark=true;
								if($('.form_code').length){ //有验证码图片
									$('.form_code img').click();
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
								window.location=data.msg;
							}
						}, 'json');
					}, 10);
				}
			});
			
			//登录
			var login_form = $('#login_form');
			var login_notnull = $('input[notnull]', login_form);
			var login_rq_mark = true;
			$('.btn_submit', login_form).on('tap', function (e){
				if (login_rq_mark){
					login_notnull.removeClass('null');
					setTimeout(function (){
						var status=0;
						login_notnull.each(function(index, element) {
							if ($(element).val()==''){
								$(element).addClass('null');
								status=1;
							}else{
								$(element).removeClass('null');
							}
						});
						
						//判断内容
						var Email = $('input[name=Email]', login_form);
						if(/^\w+[a-zA-Z0-9-.+_]+@[a-zA-Z0-9-.+_]+\.\w*$/.test($.trim(Email.val()))==false){
							Email.addClass('null');
							status=1;
						}else{
							Email.removeClass('null');
						}
						if(status){
							return false;
						}
						//通过验证，提交数据
						login_rq_mark=false;
						$.post('/?m=user', login_form.serializeArray(), function (data){
							if(data.ret!=1){
								var msg=data.msg[0];
								msg=msg.replace(/(<br>|<br \/>)\s?/i, "\r\n");
								$('html').tips_box(msg, 'error');
								login_rq_mark=true;
							}else{
								window.location=data.msg;
							}
						}, 'json');
					}, 10);//setTimeout end
					
				}// if login_rq_mark end
			});
			
			//切换
			var user_login_tab = $('.user_login_tab div');
			user_login_tab.on('tap', function (){
				reg_notnull.parent().parent().removeClass('null');
				login_notnull.removeClass('null');
				user_login_tab.removeClass('on');
				$(this).addClass('on');
				$('.user_login').eq(user_login_tab.index(this)).css('display', 'block').siblings('.user_login').css('display', 'none');
			});
		},
		
		user_binding:function(){
			var binding_form = $('#binding_form');
			var binding_notnull = $('input[notnull]', binding_form);
			var binding_rq_mark = true;
			$('.btn_submit', binding_form).on('tap', function (e){
				if (binding_rq_mark){
					binding_notnull.removeClass('null');
					setTimeout(function (){
						var status=0;
						binding_notnull.each(function(index, element) {
							if ($(element).val()==''){
								$(element).addClass('null');
								status=1;
							}else{
								$(element).removeClass('null');
							}
						});
						
						//判断内容
						var Email = $('input[name=Email]', binding_form);
						if(/^\w+[a-zA-Z0-9-.+_]+@[a-zA-Z0-9-.+_]+\.\w*$/.test($.trim(Email.val()))==false){
							Email.addClass('null');
							status=1;
						}else{
							Email.removeClass('null');
						}
						if (status){
							return false;
						}
						//通过验证，提交数据
						binding_rq_mark = false;
						$.post('/?m=user', binding_form.serializeArray(), function (data){
							if(data.ret!=1){
								var msg = data.msg[0];
								msg = msg.replace(/(<br>|<br \/>)\s?/i, "\r\n");
								$('html').tips_box(msg, 'error');
								binding_rq_mark = true;
							}else{
								window.location=data.msg;
							}
						}, 'json');
					}, 10);//setTimeout end
					
				}// if binding_rq_mark end
			});
		},
		
		forgot_init:function(){
			$('.forgot_back>a').click(function(){	//返回
				history.back();
			});
			
			var frm=$('.form_forgot');
			frm.submit(function(){return false;});
			frm.find('.btn_fotgot').click(function(){//发送忘记密码邮件
				if(global_obj.check_form(frm.find('*[notnull]'), frm.find('*[format]'), 1)){
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
				
				$.post('/account/', frm.serialize(), function(data){
					frm.find('.btn_fotgot').attr('disabled', false);
					if(data.ret!=1){
						$('#error_register_box').html(data.msg[0]).show();
					}else{
						window.location=data.msg[0];
					}
				}, 'json');
			});
			
			frm.find('.btn_reset').click(function(){//发送忘记密码邮件
				var $pwd=$('#Password');
					$pwd2=$('#Password2');
				status=0;
				$pwd.next().hide();
				$pwd2.removeAttr('style').next().hide();
				if(global_obj.check_form(frm.find('*[notnull]'), frm.find('*[format]'), 1)) status=1;
				
				if(!$.trim($pwd.val())){
					$pwd.next().show();
					status=1;
				}
				if(!$.trim($pwd2.val()) || $.trim($pwd.val())!=$.trim($pwd2.val())){
					$pwd2.css('border', '1px solid red').next().show();
					status=1;
				}
				
				if(status==1) return false;
				$(this).attr('disabled', true);
				
				$.post('/account/', frm.serialize(), function(data){
					frm.find('.btn_reset').attr('disabled', false);
					if(data.ret!=1){
						$('#error_register_box').html(data.msg[0]).show();
					}else{
						window.location=data.msg[0];
					}
				}, 'json');
			});
		},
		
		user_fav:function(){
			var rq_mark=true;
			$('.detail_prolist .item .info .del').on('tap', function(){
				var self=this,
					ProId=$(self).attr('data-proid');
				if(rq_mark){
					rq_mark=false;
					$.get('/account/favorite/remove'+ProId+'.html', {isjson:1}, function(data){
						if(data.ret==1){
							window.location.reload();
						}
						rq_mark=true;
					}, 'json');
				}
			});
		},
		
		user_address:function(){
			var $form=$('.user_address_form');
			
			$('.address_row .para a.fl').on('tap', function(){ //选择地址
				var $this=$(this), AId=$this.attr('data-aid');
				$.post('/account/', 'do_action=user.addressbook_selected&AId='+AId, function(data){
					if(data.ret==1){
						$('.address_row .para a.fl').addClass('noselected').removeClass('selected FontColor');
						$this.addClass('selected FontColor').removeClass('noselected');
						location.href = '/cart/checkout.html'; 
					}
				}, 'json');
			});
			
			$('.address_row .para .del').on('tap', function(){
				var $this=$(this),
					url=$this.attr('url');
				$('html').tips_box(lang_obj.user.delete_shipping, 'confirm', function(){
					$.get(url, function(data){
						if(data){
							$this.parents('.address_row').prev('.global_line').remove();
							$this.parents('.address_row').remove();
						}
					});
				});
			});
			
			$form.find('.btn_back').on('tap', function(){ //返回按钮
				window.location.href=$form.find('input[name=back_url]').val();
				return false;
			});
			
			var address_rq_mark=true;
			$form.submit(function(){ return false; });
			$('#save_address').on('tap', function(){
				if(address_rq_mark && !$('#save_address').hasClass('disabled')){
					$('#save_address').addClass('disabled');
					address_rq_mark=false;
					var $notnull=$('.user_address_form input[notnull], .user_address_form select[notnull]');
					$notnull.removeClass('null').next('p.error').hide();
					setTimeout(function(){
						var $Email=$('input[Name=Email]'),
							status=0;
						if($Email.length && $Email.val() && /^\w+[a-zA-Z0-9-.+_]+@[a-zA-Z0-9-.+_]+\.\w*$/.test($Email.val())==false){
							$Email.addClass('null').next('p.error').text(lang_obj.user.reg_error.EmailFormat).show();
							status+=1;
							$('body, html').animate({scrollTop:$Email.offset().top-20}, 50);
							address_rq_mark=true;
							$('#save_address').removeClass('disabled');
							return false;
						}
						$notnull.each(function(index, element) {
							if($(element).val()==''){
								$(element).addClass('null').next('p.error').text(lang_obj.user.address_tips.PleaseEnter.replace('%field%', $(element).attr('data-field'))).show();
								status++;
								if(status==1){
									$('body,html').animate({scrollTop:$(element).offset().top-20}, 500);
								}
							}
						});
						if(status){ //检查表单
							address_rq_mark=true;
							$('#save_address').removeClass('disabled');
							return false;
						}
						$.post('/account/', $form.serialize()+'&do_action=user.addressbook_mod', function(data){
							if(data.ret==1){
								window.location.href=$form.find('input[name=back_url]').val();
							}else{
								address_rq_mark=true;
								$('#save_address').removeClass('disabled');
							}
						}, 'json');
					}, 100);
				}
				return false;
			});
			
			function change_country(){
				var CId=$('#country').find(':selected').val();
				user_obj.get_state_from_country(CId);
			}
			change_country();
			$('#country').change(function(e){change_country()});
		},
		
		//修改地址
		set_default_address:function(AId, NotUser){
			$.ajax({
				url:"/account/",
				async:false,
				type:"POST",
				data:{'do_action':'user.get_addressbook', 'AId':AId, 'NotUser':NotUser},
				dataType:"json",
				success:function (data){
					if(data.ret==1){
						$('input[name=edit_address_id]').val(data.msg.address.AId);
						if($('input[name=Email]').length){
							$('input[name=Email]').val(data.msg.address.Email);
						}
						$('input[name=FirstName]').val(data.msg.address.FirstName);
						$('input[name=LastName]').val(data.msg.address.LastName)
						$('input[name=AddressLine1]').val(data.msg.address.AddressLine1);
						$('input[name=AddressLine2]').val(data.msg.address.AddressLine2);
						$('input[name=City]').val(data.msg.address.City);
						var index=$('select[name=country_id]').find('option[value='+data.msg.address.CId+']').eq(0).attr('selected', 'selected').index();
						user_obj.get_state_from_country(data.msg.address.CId);
						
						if(data.msg.address.CId==30 || data.msg.address.CId==211){
							$('select[name=tax_code_type]').find('option[value='+data.msg.address.CodeOption+']').attr('selected', 'selected');
							//$('input[name=tax_code_value]').attr('maxlength', (data.msg.address.CodeOption==1?11:(data.msg.address.CodeOption==2?14:12))).val(data.msg.address.TaxCode);
							$('input[name=tax_code_value]').attr('maxlength', (data.msg.address.CodeOption==1?11:14)).val(data.msg.address.TaxCode);
						}
						//alert(data.msg.address.SId);
						if(data.msg.country.HasState==1){
							$('select[name=Province]').find('option[value='+data.msg.address.SId+']').attr('selected', 'selected');
						}else{
							$('input[name=State]').val(data.msg.address.State);
						}
						
						$('input[name=ZipCode]').val(data.msg.address.ZipCode);
						$('input[name=CountryCode]').val('+'+data.msg.address.CountryCode);
						$('input[name=PhoneNumber]').val(data.msg.address.PhoneNumber);
					}else{
						//alert(data);
					}
				}
			});//ajax
		},//set_default_address 结束
		
		get_state_from_country:function (cid){
			$.ajax({
				url:"/account/",
				async:false,
				type:"POST",
				data:{"CId": cid, do_action:'user.select_country'},
				dataType:"json",
				success:function (data){
					if(data.ret==1){
						d=data.msg.contents;
						if(d==-1){
							$('#zoneId').css({'display':'none'}).find('select').attr('disabled', 'disabled').removeAttr('notnull');
							$('#state').css({'display':'block'}).find('input').removeAttr('disabled');
						}else{
							$('#zoneId').css({'display':'block'}).find('select').removeAttr('disabled').attr('notnull', '');
							$('#state').css({'display':'none'}).find('input').attr('disabled', 'disabled');
							str='';
							var vselect='';//'<option value="-1">Please select</option>';
							for(i=0;i<d.length;i++){
								vselect+='<option value="'+d[i]['SId']+'">'+d[i]['States']+'</option>';
							}
							$('#zoneId select').html(vselect);
						}
						$('#countryCode').val('+'+data.msg.code);
						if(data.msg.cid==30){
							$('#taxCode').css({'display':'block'}).find('select, input').removeAttr('disabled');
							$('#tariffCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled');
						}else if(data.msg.cid==211){
							$('#tariffCode').css({'display':'block'}).find('select, input').removeAttr('disabled');
							$('#taxCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled');
						}else{
							$('#taxCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled');
							$('#tariffCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled');
						}
						return true;
					}
				}
			});//ajax end
		},//get_state_from_country 结束
		
		user_setting:function(){
			$('.user_login_form').submit(function(){ return false; });
			
			//账号资料更改
			var reg_form	= $('#reg_form'),
				reg_notnull	= $('input[notnull]', reg_form),
				reg_rq_mark	= true;
			$('.btn_submit', reg_form).on('tap', function(e){
				if(reg_rq_mark){
					reg_notnull.parent().parent().removeClass('null');
					setTimeout(function(){
						var status=0,
							move=0,
							$Email=$('input[name=Email]', reg_form),
							$pwd=$('input[name=Password]', reg_form),
							$pwd2=$('input[name=Password2]', reg_form);
						$Email.removeClass('null').next('p.error').hide();
						$pwd.removeClass('null').next('p.error').hide();
						$pwd2.removeClass('null').next('p.error').hide();
						if($Email.val()==''){
							$Email.addClass('null').next('p.error').text(lang_obj.user.reg_error.PleaseEnter.replace('%field%', $Email.attr('data-field'))).show();
							status+=1;
							$('body, html').animate({scrollTop:$Email.offset().top-20}, 50);
							move=1;
						}
						if($Email.val() && /^\w+[a-zA-Z0-9-.+_]+@[a-zA-Z0-9-.+_]+\.\w*$/.test($Email.val())==false){
							$Email.addClass('null').next('p.error').text(lang_obj.user.reg_error.EmailFormat).show();
							status+=1;
							if(!move){
								$('body, html').animate({scrollTop:$Email.offset().top-20}, 50);
								move=1;
							}
						}
						if($pwd.val()=='' || $pwd2.val()=='' || $pwd.val()!=$pwd2.val()){
							if($pwd.val()==''){
								$pwd.addClass('null').next('p.error').text(lang_obj.user.reg_error.PleaseEnter.replace('%field%', $pwd.attr('data-field'))).show();
							}else if($pwd2.val()==''){
								$pwd2.addClass('null').next('p.error').text(lang_obj.user.reg_error.PWDConfirm).show();
							}else if($pwd.val()!=$pwd2.val()){
								$pwd2.addClass('null').next('p.error').text(lang_obj.user.reg_error.PWDNotMatch).show();
							}
							status+=1;
							if(!move){
								$('body, html').animate({scrollTop:$pwd.offset().top-20}, 50);
								move=1;
							}
						}
						reg_notnull.each(function(index, element){
							if($(element).attr('name')=='Email' || $(element).attr('name')=='Password' || $(element).attr('name')=='Password2'){ return true; }
							if($(element).val()==''){
								$(element).addClass('null').next('p.error').text(lang_obj.user.reg_error.PleaseEnter.replace('%field%', $(element).attr('data-field'))).show();
								status+=1;
								if(!move){
									$('body, html').animate({scrollTop:$(element).offset().top-20}, 50);
									move=1;
								}
							}else{
								$(element).removeClass('null').next('p.error').hide();
							}
						});
						if(status){
							return false;
						}
						//通过验证，提交数据
						reg_rq_mark=false;
						$.post('?', reg_form.serializeArray(), function (data){
							if(data.ret!=1){
								$('html').tips_box(msg, 'error');
								reg_rq_mark=true;
							}else{
								window.location='/account/';
							}
						}, 'json');
					}, 10);
				}
			});
		},
		
		user_password:function(){
			$('.user_login_form').submit(function(){ return false; });
			
			//账号资料更改
			var reg_form	= $('#reg_form'),
				reg_notnull	= $('input[notnull]', reg_form),
				reg_rq_mark	= true;
			$('.btn_submit', reg_form).on('tap', function(e){
				if(reg_rq_mark){
					reg_notnull.parent().parent().removeClass('null');
					setTimeout(function(){
						var status=0,
							$_pwd=$('input[name=ExtPassword]', reg_form),
							$pwd=$('input[name=NewPassword]', reg_form),
							$pwd2=$('input[name=NewPassword2]', reg_form);
						$_pwd.removeClass('null').next('p.error').hide();
						$pwd.removeClass('null').next('p.error').hide();
						$pwd2.removeClass('null').next('p.error').hide();
						if($_pwd.val()==''){
							$_pwd.addClass('null').next('p.error').text(lang_obj.user.reg_error.PleaseEnter.replace('%field%', $_pwd.attr('data-field'))).show();
						}
						if($pwd.val()=='' || $pwd2.val()=='' || $pwd.val()!=$pwd2.val()){
							if($pwd.val()==''){
								$pwd.addClass('null').next('p.error').text(lang_obj.user.reg_error.PleaseEnter.replace('%field%', $pwd.attr('data-field'))).show();
							}else if($pwd2.val()==''){
								$pwd2.addClass('null').next('p.error').text(lang_obj.user.reg_error.PWDConfirm).show();
							}else if($pwd.val()!=$pwd2.val()){
								$pwd2.addClass('null').next('p.error').text(lang_obj.user.reg_error.PWDNotMatch).show();
							}
							status+=1;
						}
						if(status){
							return false;
						}
						//通过验证，提交数据
						reg_rq_mark=false;
						$.post('?', reg_form.serializeArray(), function(data){
							if(data.ret!=1){
								$('html').tips_box(data.msg, 'error');
								reg_rq_mark=true;
							}else{
								window.location='/account/';
							}
						}, 'json');
					}, 10);
				}
			});
		},
		//站内信
		inbox_init:function(){
			var module='inbox';
			var inbox_page=1;
			var inbox_cut_page=0;
			var outbox_page=1;
			var outbox_cut_page=0;
			var scroll_cut=0;
			var load_list=function(module){
				if(module=='write') return;
				if(!eval(module+'_cut_page')){
					scroll_cut=1;
					$('.inbox_tab_con .'+module).append('<div class="more"></div>');
					$.post('?', 'do_action=user.get_inbox_list_mb&module='+module+'&Page='+eval(module+'_page'), function(data){
						if(data.ret==1){
							var html='';
							for(var key in data.msg[0]){
								html+='<div class="item '+(data.msg[0][key].IsRead==1?'read':'')+'" data-id="'+data.msg[0][key].MId+'">';
									html+='<div class="title">'+data.msg[0][key].Subject+'</div>';
									html+='<div class="date">'+data.msg[0][key].AccTime+'</div>';
									html+='<div class="clear"></div>';
								html+='</div>';
							}
							$('.inbox_tab_con .'+module+' .more').fadeOut(function(){
								$(this).remove();	
								$('.inbox_tab_con .'+module).append(html);
								if(data.msg[1]==0 && data.msg[2]==1){	//第一页没数据
									$('.inbox_tab_con .'+module).append($('.no_data').html());
								}
							});
							if(module=='inbox'){
								inbox_page++;
								if(data.msg[2]==data.msg[3]) inbox_cut_page=1;
							}else if(module=='outbox'){
								outbox_page++;
								if(data.msg[2]==data.msg[3]) outbox_cut_page=1;
							}
							scroll_cut=0
						}
					}, 'json');
				}
			}
			load_list('inbox');
			load_list('outbox');
			$('.tab_inbox li').on('touchend',function(){
				module=$(this).data('module');
				$('.inbox_tab_con').scrollTop(0);
				$(this).addClass('current').siblings().removeClass('current');
				$('.inbox_tab_con .tab_con').eq($(this).index()).show().siblings().hide();
				
			});
			$('.inbox_tab_con').scroll(function(){
				if($(this).scrollTop()+$(this).height()>=$(this).find('.'+module).height() && !scroll_cut){
					load_list(module);
				}
			});
			$('.inbox_tab_con .tab_con').on('click','.item',function(){
				var _this=$(this);
				var h_init=function(){
					var margin=20;	//margin-top:20px;
					var box_h=Math.max($('body').height(),$('.inbox_detail_pop .box').outerHeight()+2*margin);
					if(box_h==$('body').height()){
						margin=($('body').height()-$('.inbox_detail_pop .box').outerHeight())/2;
					}
					$('.inbox_detail_pop .box').css('margin-top',margin);
					$('.inbox_detail_pop').height(box_h);
				}
				$.post('?', 'do_action=user.get_inbox_detail_mb&module='+module+'&MId='+$(this).data('id'), function(data){
					if(data.ret==1){
						$('.inbox_detail_pop .box .title').html(data.msg.Subject);
						$('.inbox_detail_pop .box .date').html(data.msg.AccTime);
						$('.inbox_detail_pop .box .con').html(data.msg.Content);
						if(module=='inbox') _this.addClass('read');
						var img_html='';
						if(data.msg.PicPath){
							img_html+='<img src="'+data.msg.PicPath+'"/>';
							var image = new Image();
							image.src = data.msg.PicPath;
							image.onload = function(){
								h_init();
							}
							
						}
						$('.inbox_detail_pop .box .img').html(img_html);
						$('.inbox_detail_pop').fadeIn();
						h_init();
					}
				}, 'json');	
			});
			$('.inbox_detail_pop').click(function(){$(this).fadeOut();});
			$('.inbox_tab_con .tab_con form').submit(function(){
				if(global_obj.check_form($(this).find('*[notnull]'), $(this).find('*[format]'))){return false;}	
			});
		}
	};
})(jQuery, window);
