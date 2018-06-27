/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

var set_obj={
	config_edit_init:function(){
		/* 开关按钮*/
		frame_obj.switchery_checkbox(function(obj){
			if(obj.find('input[name=IsWater]').length){
				obj.parents('.rows').next('.watermark_box').slideDown();
				obj.next().css('display', '');
			}
			if(obj.find('input[name=LessStock]').length){
				var $index=obj.index();
				obj.parents('.rows').find('.switchery:not(:eq('+$index+'))').removeClass('checked').find('input').attr('checked', false);
			}
			if(obj.find('input[name=UserVerification]').length){
				obj.parents('.rows').find('input[name=UserStatus]').attr('checked', true).parent().addClass('checked');
			}
			if(obj.find('input[name=AutoRegister]').length){
				obj.parents('.rows').find('input[name=TouristsShopping]').attr('checked', true).parent().addClass('checked');
			}
			if(obj.find('input[name=IsCloseWeb]').length){
				$('.close_web_box').show();
			}
			if(obj.find('input[name=IsNotice]').length){
				$('.notice_box').show();
			}
			if(obj.find('input[name=TopNewTarget\\[\\]]').length){
				var $num=obj.parents('.help_item').index();
				obj.removeClass('checked').find('input').attr('checked', false);
				obj.parents('.tab_txt').siblings().find('.help_item:eq('+$num+') .switchery input').attr('checked', false).parent().removeClass('checked');
			}
		}, function(obj){
			if(obj.find('input[name=IsWater]').length){
				obj.parents('.rows').next('.watermark_box').slideUp();
				obj.next().css('display', 'none');
			}
			if(obj.find('input[name=LessStock]').length){
				var $index=obj.index();
				obj.parents('.rows').find('.switchery:not(:eq('+$index+'))').addClass('checked').find('input').attr('checked', true);
			}
			if(obj.find('input[name=UserStatus]').length){
				obj.parents('.rows').find('input[name=UserVerification]').attr('checked', false).parent().removeClass('checked');
			}
			if(obj.find('input[name=TouristsShopping]').length){
				obj.parents('.rows').find('input[name=AutoRegister]').attr('checked', false).parent().removeClass('checked');
			}
			if(obj.find('input[name=IsCloseWeb]').length){
				$('.close_web_box').hide();
			}
			if(obj.find('input[name=IsNotice]').length){
				$('.notice_box').hide();
			}
			if(obj.find('input[name=TopNewTarget\\[\\]]').length){
				var $num=obj.parents('.help_item').index();
				obj.addClass('checked').find('input').attr('checked', true);
				obj.parents('.tab_txt').siblings().find('.help_item:eq('+$num+') .switchery input').attr('checked', true).parent().addClass('checked');
			}
		});
		/* LOGO图片上传 */
		$('#LogoUpload, .upload_logo .edit').on('click', function(){frame_obj.photo_choice_init('LogoUpload', 'form input[name=LogoPath]', 'LogoDetail', '', 1);});
		if($('form input[name=LogoPath]').attr('save')==1){
			$('#LogoDetail').append(frame_obj.upload_img_detail($('form input[name=LogoPath]').val())).children('.upload_btn').hide();
		}
		$('.upload_logo .del').on('click', function(){
			$('#LogoDetail').children('a').remove();
			$('#LogoDetail').children('.upload_btn').show();
			$('#edit_form input[name=LogoPath]').val('');
		});
		/* ICO图片上传 */
		$('#IcoUpload, .upload_ico .edit').on('click', function(){frame_obj.photo_choice_init('IcoUpload', 'form input[name=IcoPath]', 'IcoDetail', '', 1);});
		if($('form input[name=IcoPath]').attr('save')==1){
			$('#IcoDetail').append(frame_obj.upload_img_detail($('form input[name=IcoPath]').val())).children('.upload_btn').hide();
		}
		$('.upload_ico .del').on('click', function(){
			$('#IcoDetail').children('a').remove();
			$('#IcoDetail').children('.upload_btn').show();
			$('#edit_form input[name=IcoPath]').val('');
		});
		/* 水印图片上传 */
		$('#WatermarkUpload, .upload_watermark .edit').on('click', function(){frame_obj.photo_choice_init('WatermarkUpload', 'form input[name=WatermarkPath]', 'WatermarkDetail', '', 1, '', "not_div_mask=0;parent.$('#preview_pic').attr('src', parent.$('input[name=WatermarkPath]').val());");});
		if($('form input[name=WatermarkPath]').attr('save')==1){
			$('#WatermarkDetail').append(frame_obj.upload_img_detail($('form input[name=WatermarkPath]').val())).children('.upload_btn').hide();
		}
		$('.upload_watermark .del').on('click', function(){
			$('#WatermarkDetail').children('a').remove();
			$('#WatermarkDetail').children('.upload_btn').show();
			$('#edit_form input[name=WatermarkPath]').val('');
		});
		/* 水印位置选择 */
		$('#edit_form .watermark_tab:eq(0) td').click(function(){
			var $num=$(this).attr('data');
			if(!$(this).hasClass('item_on')){
				$('#edit_form .watermark_tab td').removeClass('item_on');
				$('#edit_form input[name=WaterPosition]').val($num);
				$(this).addClass('item_on');
				$('#preview_pic').clone(true).remove().appendTo('#edit_form .watermark_tab:eq(1) td:eq('+($num-1)+')').parents('.watermark_tab').find('td[data!='+$num+'] img').remove();
			}
		});
		$('#preview_pic').css('opacity',$('#edit_form input[name=Alpha]').val()*0.01);
		$('#slider').slider({
			value:$('#edit_form input[name=Alpha]').val(),
			change:function(){
				var val=$(this).slider('value');
				$('#slider_value').html(val+'%');
				$('#preview_pic').css('opacity',val*0.01);
				$('#edit_form input[name=Alpha]').val(val);
			}
		});
		/* 多选事件（订单短信通知） */
		$('#edit_form').on('click', '.orders_sms_radio .choice_btn', function(){
			var $this=$(this);
			if($this.children('input').is(':checked')){
				$this.removeClass('current').children('input').attr('checked', false);
			}else{
				$this.addClass('current').children('input').attr('checked', true);
			}
		});
		/* 单选事件（语言） */
		$('#edit_form').on('click', '.lang_list .choice_btn', function(){
			var $this=$(this);
			if($this.children('input').is(':checked')){
				if($this.siblings('.current').size()<1){
					global_obj.win_alert(lang_obj.manage.set.select_once_language);
					return false;
				}
				$this.removeClass('current').children('input').attr('checked', false);
				$('#edit_form .default_lang .choice_btn').children('input[value='+$this.children('input').val()+']').parent().remove();
				if(!$('#edit_form .default_lang .choice_btn.current').size()) $('#edit_form .default_lang .choice_btn').eq(0).addClass('current').children('input').attr('checked', true);
			}else{
				$this.addClass('current').children('input').attr('checked', true);
				$('#edit_form .default_lang').append('<span class="choice_btn"><b>'+$this.children('b').text()+'</b><input type="radio" name="LanguageDefault" class="hide" value="'+$this.children('input').val()+'" /></span>');
			}
		});
		/* 单选事件（前端显示） */
		$('#edit_form').on('click', '.web_display .choice_btn, .default_lang .choice_btn, .manage_lang .choice_btn', function(){
			$(this).addClass('current').siblings().removeClass('current');
			$(this).children('input').attr('checked', true);
		});
		/* 单选事件 （特效方式） */
		$('#edit_form .effects_list .choice_btn').on('click', function(){
			$(this).addClass('current').siblings().removeClass('current');
			$(this).children('input').attr('checked', true);
			$(this).siblings().children('input').attr('checked', false);
		});
		
		/* 语言编辑 */
		$('.lang_list .edit').on('click', function(){
			var $obj=$('.box_language_edit'),
				$lang=$(this).attr('lang'),
				$data_flag=$obj.attr('data-flag'),
				$data_currency=$obj.attr('data-currency'),
				$path='';
			if($data_flag) $data_flag=$.evalJSON($data_flag);
			if($data_currency) $data_currency=$.evalJSON($data_currency);
			frame_obj.pop_form($obj);
			$obj.find('h1>em').text(lang_obj.language[$lang]);
			$obj.find('input[name=Language]').val($lang);
			$('#FlagDetail a').remove();
			$('#FlagDetail .upload_btn').show();
			if($data_flag[$lang]){
				$obj.find('input[name=FlagPath]').val($data_flag[$lang]);
				$('#FlagDetail').append(frame_obj.upload_img_detail($data_flag[$lang])).children('.upload_btn').hide();
			}else{
				$obj.find('input[name=FlagPath]').val('');
			}
			if($data_currency[$lang]){
				$obj.find('select[name=Currency]').val($data_currency[$lang]);
			}else{
				$obj.find('select[name=Currency]').val('');
			}
		});
		/* 语言国旗上传 */
		var callback="not_div_mask=1;";
		$('#FlagUpload, .upload_flag .edit').on('click', function(){frame_obj.photo_choice_init('FlagUpload', '.box_language_edit input[name=FlagPath]', 'FlagDetail', '', 1, '', callback);});
		if($('.box_language_edit input[name=FlagPath]').attr('save')==1){
			$('#FlagDetail').append(frame_obj.upload_img_detail($('.box_language_edit input[name=FlagPath]').val())).children('.upload_btn').hide();
		}
		$('.upload_flag .del').on('click', function(){
			$('#FlagDetail').children('a').remove();
			$('#FlagDetail').children('.upload_btn').show();
			$('.box_language_edit input[name=FlagPath]').val('');
		});
		$('.box_language_edit form').submit(function(){return false;});
		frame_obj.submit_form_init($('.box_language_edit form'), '');
		
		/* 表单提交 */
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=config');
		
		/* 2016/10/24 追加 sheldon */
		$('.tab_box .tab_txt').on('change', 'input.input_url', function(){
			var $num=$(this).parents('.help_item').index();
			$(this).parents('.tab_txt').siblings().find('.help_item:eq('+$num+') input[name=TopUrl\\[\\]]').val($(this).val());
		}).on('click', '.switchery', function(){
			var $num=$(this).parents('.help_item').index();
			if($(this).hasClass('checked')){
				$(this).removeClass('checked').find('input').attr('checked', false);
				$(this).parents('.tab_txt').siblings().find('.help_item:eq('+$num+') .switchery input').attr('checked', false).parent().removeClass('checked');
			}else{
				$(this).addClass('checked').find('input').attr('checked', true);
				$(this).parents('.tab_txt').siblings().find('.help_item:eq('+$num+') .switchery input').attr('checked', true).parent().addClass('checked');
			}
		});
	},
	
	orders_print_edit_init:function(){
		/* 订单LOGO上传 */
		$('#LogoUpload, .upload_logo .edit').on('click', function(){frame_obj.photo_choice_init('LogoUpload', 'form input[name=LogoPath]', 'LogoDetail', '', 1);});
		if($('form input[name=LogoPath]').attr('save')==1){
			$('#LogoDetail').append(frame_obj.upload_img_detail($('form input[name=LogoPath]').val())).children('.upload_btn').hide();
		}
		$('.upload_logo .del').on('click', function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.ajax({
					url:'./?do_action=action.file_del&PicPath='+$this.prev().attr('href'),
					success:function(){
						$('#LogoDetail').children('a').remove();
						$('#LogoDetail').children('.upload_btn').show();
						$('form input[name=LogoPath]').val('');
					}
				});
			}, 'confirm');
			return false;
		});
		/* 表单提交 */
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=orders_print');
	},
	
	exchange_init:function(){
		frame_obj.del_init($('#exchange .r_con_table'));
		$('.r_con_table').on('click', '.used_checkbox .switchery', function(){	//启用
			var $this=$(this),
				$tr=$this.parents('tr');
			if(!$this.hasClass('no_drop')){
				if(!$this.hasClass('checked')){
					$this.addClass('checked');
				}else{
					$this.removeClass('checked');
				}
				$.get('?', 'do_action=set.exchange_switch&Type=0&CId='+$tr.attr('cid'), function(data){
					if(data.ret!=1){
						global_obj.win_alert(lang_obj.global.set_error, '', '', 1);
					}
				}, 'json');
			}
		}).on('click', '.default_checkbox .switchery', function(){	//默认
			var $this=$(this),
				$tr=$this.parents('tr');
			if(!$this.hasClass('no_drop') && !$this.hasClass('checked')){
				$this.addClass('checked no_drop');
				$tr.find('.used_checkbox .switchery').addClass('checked no_drop');
				$tr.parents('tr').siblings().find('.used_checkbox .switchery').removeClass('no_drop');
				$tr.siblings().find('.default_checkbox .switchery').removeClass('checked no_drop');
				$.get('?', 'do_action=set.exchange_switch&Type=1&CId='+$tr.attr('cid'), function(data){
					if(data.ret==1){
						window.location.href='./?m=set&a=exchange';
					}else{
						global_obj.win_alert(lang_obj.global.set_error, '', '', 1);
					}
				}, 'json');
			}
		}).on('click', '.manage_default_checkbox .switchery', function(){	//后台默认
			var $this=$(this),
				$tr=$this.parents('tr');
			if(!$this.hasClass('no_drop') && !$this.hasClass('checked')){
				$this.addClass('checked no_drop');
				$tr.find('.used_checkbox .switchery').addClass('checked no_drop');
				$tr.parents('tr').siblings().find('.used_checkbox .switchery').removeClass('no_drop');
				$tr.siblings().find('.manage_default_checkbox .switchery').removeClass('checked no_drop');
				$.get('?', 'do_action=set.exchange_switch&Type=2&CId='+$tr.attr('cid'), function(data){
					if(data.ret==1){
						window.location.href='./?m=set&a=exchange';
					}else{
						global_obj.win_alert(lang_obj.global.set_error, '', '', 1);
					}
				}, 'json');
			}
		});
		/* 编辑弹出框 */
		$('.r_nav .ico .add, .r_con_table .edit').on('click', function(){
			var $id=parseInt($(this).attr('data-id')),
				$obj=$('.box_exchange_edit');
			frame_obj.pop_form($obj);
			frame_obj.rows_input();
			if($id){ //编辑
				var $data=$.evalJSON($(this).parents('tr').attr('data'));
				$('#edit_form input[name=Currency]').val($data['Currency']); //货币名称
				$('#edit_form input[name=Symbol]').val($data['Symbol']); //符号
				$('#edit_form input[name=ExchangeRate]').val($data['ExchangeRate']); //汇率
				if($id && $id<=17){
					$('#edit_form input[name=Currency], #edit_form input[name=Symbol], #edit_form input[name=ExchangeRate]').addClass('no_drop').attr('readonly', true);
				}else{
					$('#edit_form input[name=Currency], #edit_form input[name=Symbol], #edit_form input[name=ExchangeRate]').removeClass('no_drop').removeAttr('readonly');
				}
				$data['Currency']!='USD' && $('#edit_form input[name=ExchangeRate]').removeClass('no_drop').removeAttr('readonly'); //只有美元是固定汇率
				//国旗
				$('#FlagDetail a').remove();
				$('#FlagDetail .upload_btn').show();
				if($data['IsFlagPath']){
					$obj.find('input[name=FlagPath]').val($data['FlagPath']).attr('save', 1);
					$('#FlagDetail').append(frame_obj.upload_img_detail($data['FlagPath'])).children('.upload_btn').hide();
				}else{
					$obj.find('input[name=FlagPath]').val('').attr('save', 0);;
				}
				//启用
				if(($('#edit_form input[name=IsUsed]').is(':checked')?1:0)!=$data['IsUsed']) $('#edit_form input[name=IsUsed]').parent().click();
				if(!parseInt($data['IsDefault'])){
					$('#edit_form .rows:eq(4)').show();
				}else{
					$('#edit_form .rows:eq(4)').hide();
				}
				$('#edit_form input[name=CId]').val($id); //id
				$('.box_exchange_edit .t>h1>span').text(lang_obj.global.edit); //编辑框标题
			}else{ //添加
				$('#edit_form input[name=Currency], #edit_form input[name=Symbol], #edit_form input[name=ExchangeRate]').val('').removeClass('no_drop').removeAttr('readonly');
				//国旗
				$('#FlagDetail a').remove();
				$('#FlagDetail .upload_btn').show();
				//启用
				if(($('#edit_form input[name=IsUsed]').is(':checked')?1:0)!=1) $('#edit_form input[name=IsUsed]').parent().click(); //默认开启
				$('#edit_form .rows:eq(4)').show();
				$('#edit_form input[name=CId]').val(0); //id
				$('.box_exchange_edit .t>h1>span').text(lang_obj.global.add); //编辑框标题
			}
			return false;
		});
		/* 启用按钮 */
		$('#edit_form').on('click', '.switchery', function(){	//后台默认
			if($(this).hasClass('checked')){
				$(this).removeClass('checked').find('input').attr('checked', false);
				if($(this).find('input[name=IsUsed]').length){
					$("#default").css("display", "none");
				}
			}else{
				$(this).addClass('checked').find('input').attr('checked', true);
				if($(this).find('input[name=IsUsed]').length){
					$("#default").css("display", "block");
				}
			}
		});
		/* 国旗上传 */
		var callback="not_div_mask=1;";
		$('#FlagUpload, .upload_flag .edit').on('click', function(){frame_obj.photo_choice_init('FlagUpload', 'form input[name=FlagPath]', 'FlagDetail', '', 1, '', callback);});
		if($('form input[name=FlagPath]').attr('save')==1){
			$('#FlagDetail').append(frame_obj.upload_img_detail($('form input[name=FlagPath]').val())).children('.upload_btn').hide();
		}
		$('.upload_flag .del').on('click', function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.ajax({
					url:'./?do_action=action.file_del&PicPath='+$this.prev().attr('href'),
					success:function(){
						$('#FlagDetail').children('a').remove();
						$('#FlagDetail').children('.upload_btn').show();
						$('form input[name=FlagPath]').val('');
					}
				});
			}, 'confirm', 1);
			return false;
		});
		/* 表单提交 */
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=exchange');
	},
	
	oauth_init:function(){
		frame_obj.switchery_checkbox();
		/* 第三方登录图片上传 */
		var callback="not_div_mask=1;";
		$('#LogoUpload, .upload_logo .edit').on('click', function(){frame_obj.photo_choice_init('LogoUpload', 'form input[name=LogoPath]', 'LogoDetail', '', 1, '', callback);});
		if($('form input[name=LogoPath]').attr('save')==1){
			$('#LogoDetail').append(frame_obj.upload_img_detail($('form input[name=LogoPath]').val())).children('.upload_btn').hide();
		}
		$('.upload_logo .del').on('click', function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.ajax({
					url:'./?do_action=action.file_del&PicPath='+$this.prev().attr('href'),
					success:function(){
						$('#LogoDetail').children('a').remove();
						$('#LogoDetail').children('.upload_btn').show();
						$('form input[name=LogoPath]').val('');
					}
				});
			}, 'confirm');
			return false;
		});
		/* 编辑弹出框 */
		$('.r_con_table .edit').on('click', function(){
			var $id=parseInt($(this).attr('data-id')),
				$obj=$('.box_oauth_edit');
			frame_obj.pop_form($obj);
			if($id){ //编辑
				var $data=$.evalJSON($(this).parents('tr').attr('data')),
					$data_account=$.evalJSON(global_obj.htmlspecialchars_decode($data['Data'])),
					$FunVersion=$('#FunVersion').val(),
					$html='';
				//图片
				$('#LogoDetail a').remove();
				$('#LogoDetail .upload_btn').show();
				if($data['IsLogoPath']){
					$obj.find('input[name=LogoPath]').val($data['LogoPath']).attr('save', 1);
					$('#LogoDetail').append(frame_obj.upload_img_detail($data['LogoPath'])).children('.upload_btn').hide();
				}else{
					$obj.find('input[name=LogoPath]').val('').attr('save', 0);;
				}
				//账号信息
				for(k in $data_account){
					$html+='<div class="rows"'+($FunVersion==0 && k=='SignIn'?' style="display:none;"':'')+'><label>'+lang_obj.manage.set.platform_ary[k]+'</label><span class="input">';
						for(l in $data_account[k]){
							if(l=='IsUsed'){
								$html+='<div class="switchery'+(parseInt($data_account[k][l])==1?' checked':'')+'"><input type="checkbox" name="IsUsed['+k+']" value="1"'+(parseInt($data_account[k][l])==1?' checked':'')+'><div class="switchery_toggler"></div><div class="switchery_inner"><div class="switchery_state_on"></div><div class="switchery_state_off"></div></div></div><div class="blank9"></div>';
							}else{
								for(m in $data_account[k][l]){
									$html+='<span class="price_input lang_input"><b>'+m+'<div class="arrow"><em></em><i></i></div></b><input type="text" class="form_input" name="Value['+k+'][]" value="'+$data_account[k][l][m]+'" size="40" /><input type="hidden" name="Name['+k+'][]" value="'+m+'" /></span><div class="blank9"></div>';
								}
							}
						}
					$html+='</span><div class="clear"></div></div>';
				}
				$('#edit_form .account_info').html($html);
				$('#edit_form input[name=SId]').val($id); //id
				$('.box_oauth_edit .t>h1').text($data['Title']); //平台名称
				frame_obj.switchery_checkbox();
			}
			return false;
		});
		/* 表单提交 */
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=oauth');
	},
	
	payment_edit_init:function(){
		frame_obj.switchery_checkbox();
		/* 支付接口图片上传 */
		$('#LogoUpload, .upload_logo .edit').on('click', function(){frame_obj.photo_choice_init('LogoUpload', 'form input[name=LogoPath]', 'LogoDetail', '', 1);});
		if($('form input[name=LogoPath]').attr('save')==1){
			$('#LogoDetail').append(frame_obj.upload_img_detail($('form input[name=LogoPath]').val())).children('.upload_btn').hide();
		}
		$('.upload_logo .del').on('click', function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.ajax({
					url:'./?do_action=action.file_del&PicPath='+$this.prev().attr('href'),
					success:function(){
						$('#LogoDetail').children('a').remove();
						$('#LogoDetail').children('.upload_btn').show();
						$('form input[name=LogoPath]').val('');
					}
				});
			}, 'confirm');
			return false;
		});
		/* 表单提交 */
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=payment');
	},
	
	country_init:function(){
		frame_obj.select_all($('#country .r_con_table input[name=select_all]'), $('#country .r_con_table input[name=select]')); //批量操作
		frame_obj.del_bat($('.r_nav .bat_open'), $('#country .r_con_table input[name=select]'), function(id_list){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.used_confirm, function(){
				$.get('./?do_action=set.country_used_bat&group_cid='+id_list+'&used=1', function(data){
					if(data.ret==1){
						window.location='./?m=set&a=country';
					}
				}, 'json');
			}, 'confirm');
			return false;
		}, lang_obj.global.used_dat_select);
		frame_obj.del_bat($('.r_nav .bat_close'), $('#country .r_con_table input[name=select]'), function(id_list){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.close_confirm, function(){
				$.get('./?do_action=set.country_used_bat&group_cid='+id_list+'&used=0', function(data){
					if(data.ret==1){
						window.location='./?m=set&a=country';
					}
				}, 'json');
			}, 'confirm');
			return false;
		}, lang_obj.global.close_dat_select);
		$('.r_con_table tbody td.default').css({'color':'#F00', 'font-weight':'bold', 'font-size':'14px'});
		$('.r_con_table').on('click', '.used_checkbox .switchery', function(){	//启用
			var $this=$(this),
				$tr=$this.parents('tr'),
				check=0;
			if(!$this.hasClass('no_drop')){
				if(!$this.hasClass('checked')){
					$this.addClass('checked');
					check=1;
				}else{
					$this.removeClass('checked');
					$tr.find('.hot_checkbox .switchery').removeClass('checked');
				}
				$.get('?', 'do_action=set.country_switch&Type=0&CId='+$tr.attr('cid')+'&Check='+check, function(data){
					if(data.ret!=1){
						global_obj.win_alert(lang_obj.global.set_error);
					}
				}, 'json');
			}
		}).on('click', '.hot_checkbox .switchery', function(){	//热门国家
			var $this=$(this),
				$tr=$this.parents('tr'),
				check=0;
			if(!$this.hasClass('no_drop')){
				if(!$this.hasClass('checked')){
					$this.addClass('checked');
					$tr.find('.used_checkbox .switchery').addClass('checked');
					check=1;
				}else{
					$this.removeClass('checked');
				}
				$.get('?', 'do_action=set.country_switch&Type=1&CId='+$tr.attr('cid')+'&Check='+check, function(data){
					if(data.ret!=1){
						global_obj.win_alert(lang_obj.global.set_error);
					}
				}, 'json');
			}
		}).on('click', '.open_state', function(){ //打开省份管理
			var $url=$(this).attr('data-url');
			frame_obj.pop_iframe($url, lang_obj.manage.counrtry.state);
			return false;
		});;
		frame_obj.del_init($('#country .r_con_table'));
	},
	
	country_edit_init:function(){
		frame_obj.switchery_checkbox(function(obj){
			var $this=obj.parent();
			if($this.find('input[name=IsUsed]').length){
				$('input[name=IsHot], input[name=HasState]').parents('.rows').css('display', 'block');
			}else if($this.find('input[name=IsHot]').length){
				$('input[name=IsDefault]').parents('.rows').css('display', 'block');
			}
		}, function(obj){
			var $this=obj.parent();
			if($this.find('input[name=IsUsed]').length){
				$('input[name=IsHot], input[name=IsDefault], input[name=HasState]').removeAttr('checked').parents('.rows').css('display', 'none');
			}else if($this.find('input[name=IsHot]').length){
				$('input[name=IsDefault]').removeAttr('checked').parents('.rows').css('display', 'none');
			}
		});
		/* 国旗上传 */
		$('#FlagUpload, .upload_flag .edit').on('click', function(){frame_obj.photo_choice_init('FlagUpload', 'form input[name=FlagPath]', 'FlagDetail', '', 1);});
		if($('form input[name=FlagPath]').attr('save')==1){
			$('#FlagDetail').append(frame_obj.upload_img_detail($('form input[name=FlagPath]').val())).children('.upload_btn').hide();
		}
		$('.upload_flag .del').on('click', function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.ajax({
					url:'./?do_action=action.file_del&PicPath='+$this.prev().attr('href'),
					success:function(){
						$('#FlagDetail').children('a').remove();
						$('#FlagDetail').children('.upload_btn').show();
						$('form input[name=FlagPath]').val('');
					}
				});
			}, 'confirm');
			return false;
		});
		/* 表单提交 */
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=country');
	},
	
	country_states_init:function(){
		var CId=$('.country_states').attr('CId');
		var load_edit_form=function(target_obj, url, type, value, callback){
			$.ajax({
				type:type,
				url:url+value,
				success:function(data){
					$(target_obj).html($(data).find(target_obj).html());
					callback && callback(data);
				}
			});
		};
		var edit_form=function(){
			load_edit_form('.country_states_edit', '?m=set&a=country&d=state_edit', 'GET', '&CId='+CId, function(){
				//frame_obj.submit_form_init($('#edit_form'), './?m=set&a=country&d=state_list&CId='+CId);
				frame_obj.submit_form_init($('#country_states_edit_form'), '', '', '', function(){
					window.location.reload();
				});
			});
		};
		//$('.country_states .menu_list').height($('.r_con_wrap').height()-10);
		//$('.country_states .menu_list').jScrollPane();
		$('.country_states .menu_list dl').dragsort({
			dragSelector:'dt',
			dragEnd:function(){
				var data=$(this).parent().children('dt').map(function(){
					return $(this).attr('sid');
				}).get();
				$.get('?m=set&a=country', {do_action:'set.country_states_order', sort_order:data.join('|')});
			},
			dragSelectorExclude:'a',
			placeHolderTemplate:'<dt class="placeHolder"></dt>',
			scrollSpeed:5
		});
		$('.country_states .edit').click(function(){
			load_edit_form('.country_states_edit', '?m=set&a=country&d=state_edit', 'GET', $(this).parent().attr('data'), function(){
				frame_obj.submit_form_init($('#country_states_edit_form'), '', '', '', function(){
					window.location.reload();
				});
				$('.country_states .btn_cancel').live('click', function(){
					edit_form();
				});
			});
		})
		edit_form();
		frame_obj.del_init($('#country .country_states'));
	},
	
	themes_themes_edit_init:function(){
		$('#themes_themes .item').hover(function(){
			$(this).children('.info').stop(true, true).slideDown(500);
		},function(){
			$(this).children('.info').stop(true, true).slideUp(500);
		}).children('.img').click(function(){
			if(!$(this).parent().hasClass('current')){
				var $this=$(this);
				global_obj.win_alert(lang_obj.manage.module.sure_module, function(){
					$this.parent().addClass('current').siblings().removeClass('current');
					$.get('?', "do_action=set.themes_themes_edit&themes="+$this.parent().attr('themes'), function(data){
						if(data.ret!=1){
							global_obj.win_alert(data.msg, function(){
								window.location.reload();
							}, 'confirm');
						}else{
							window.location.reload();
						}
					}, 'json');
				}, 'confirm');
				return false;
			}
		});
	},
	
	themes_products_list_edit_init:function(){
		frame_obj.switchery_checkbox(function(obj){
			if(obj.find('input[name=IsColumn]').length){
				obj.parents('.rows').next().next().slideDown();
			}
		}, function(obj){
			if(obj.find('input[name=IsColumn]').length){
				obj.parents('.rows').next().next().slideUp();
			}
		});
		$('#edit_form').delegate('input[name=reset]', 'click', function(){
			global_obj.win_alert(lang_obj.global.reset_confirm, function(){
				$.get('?', "do_action=set.themes_products_list_reset", function(data){
					if(data.ret!=1){
						global_obj.win_alert(data.msg);
					}else{
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
		});
		$('#edit_form .choice_btn').on('click', function(){
			$(this).addClass('current').siblings().removeClass('current');
			$(this).children('input').attr('checked', true);
		});
		$('#edit_form .order_list select').on('change', function(){
			var $number=$(this).children('option:selected').attr('number'),
				$select=$(this).parents('.rows').next().find('span.input');
			if($number){
				var ary=$number.split(',');
				var $html='<select name="OrderNumber">';
				for(var i=0; i<ary.length; ++i){
					$html+='<option'+($select.attr('number')==ary[i]?' selected':'')+'>'+ary[i]+'</option>';
				}
				$html+='</select>';
			}
			$select.html($html);
		});
		$('#edit_form .order_list select').change();
		$('#edit_form .choice_btn.current').click();
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=themes&d=products_list');
	},
	
	themes_products_detail_edit_init:function(){
		$('#edit_form .item').hover(function(){
			$(this).children('.info').stop(true, true).slideDown(500);
		},function(){
			$(this).children('.info').stop(true, true).slideUp(500);
		}).children('.img').click(function(){
			if(!$(this).parent().hasClass('current')){
				var $this=$(this);
				global_obj.win_alert(lang_obj.manage.module.sure_module, function(){
					$this.parent().addClass('current').siblings().removeClass('current');
					$.post('?', "do_action=set.themes_products_detail_themes_edit&Key="+$this.parent().attr('detail-id'), function(data){
						if(data.ret!=1){
							global_obj.win_alert(data.msg);
						}else{
							window.location.reload();
						}
					}, 'json');
				}, 'confirm');
				return false;
			}
		});
		
		$('.tab_box .tab_txt').on('click', '.add', function(){ //添加帮助选项
			var $box=$(this).parent('.help_item'),
				$num=$box.index(),
				$obj=$(this).parents('.tab_txt');
			if($obj.find('.help_item:eq(0) .not_input').is(':hidden')){
				$('.tab_box .tab_txt').each(function(){
					$(this).find('.help_item:eq(0) .not_input, .help_item:eq(0) .switchery, .help_item:eq(0) b, .help_item:eq(0) .del').show();
					$(this).find('.help_item:eq(0) .not_input input').val('');
					$(this).find('.help_item:eq(0) input').attr('disabled', false);
				});
			}else{
				$('.tab_box .tab_txt').each(function(){
					$(this).find('.help_item:eq('+$num+')').after($(this).find('.help_item:eq('+$num+')').prop('outerHTML'));
					$(this).find('.help_item:eq('+($num+1)+')').siblings().find('.add').hide();
					$(this).find('.help_item:eq('+($num+1)+') .not_input input').val('');
				});
			}
		}).on('click', '.del', function(){ //删除帮助选项
			var $box=$(this).parent('.help_item'),
				$num=$box.index(),
				$obj=$(this).parents('.tab_txt');
			if($obj.find('.help_item').size()==1){
				$('.tab_box .tab_txt').each(function(){
					$(this).find('.help_item:eq(0) .not_input, .help_item:eq(0) .switchery, .help_item:eq(0) b, .help_item:eq(0) .del').hide();
					$(this).find('.help_item:eq(0) input').attr('disabled', true);
				});
			}else{
				$('.tab_box .tab_txt').each(function(){
					$(this).find('.help_item:eq('+$num+')').remove();
					$(this).find('.help_item:last .add').show();
					$(this).find('.help_item:last').siblings().find('.add').hide();
				});
			}
		}).on('change', 'input.input_url', function(){
			var $num=$(this).parents('.help_item').index();
			$(this).parents('.tab_txt').siblings().find('.help_item:eq('+$num+') input[name=Url\\[\\]]').val($(this).val());
		}).on('click', '.switchery', function(){
			var $num=$(this).parents('.help_item').index();
			if($(this).hasClass('checked')){
				$(this).removeClass('checked').find('input').attr('checked', false);
				$(this).parents('.tab_txt').siblings().find('.help_item:eq('+$num+') .switchery input').attr('checked', false).parent().removeClass('checked');
			}else{
				$(this).addClass('checked').find('input').attr('checked', true);
				$(this).parents('.tab_txt').siblings().find('.help_item:eq('+$num+') .switchery input').attr('checked', true).parent().addClass('checked');
			}
		});
		
		$('#share_list').dragsort({
			dragSelector:'div',
			dragSelectorExclude:'',
			placeHolderTemplate:'<div class="share_btn fl placeHolder"></div>',
			scrollSpeed:5
		});
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=themes&d=products_detail');
	},
	
	themes_nav_global:{
		type:'',
		init:function(){
			frame_obj.del_init($('#themes .r_con_table')); //删除事件
			frame_obj.select_all($('input[name=select_all]'), $('input[name=select]')); //批量操作
			frame_obj.del_bat($('.r_nav .del'), $('input[name=select]'), function(id_list){
				global_obj.win_alert(lang_obj.global.del_confirm, function(){
					$.get('?', {do_action:'set.themes_nav_del_bat', Type:set_obj.themes_nav_global.type, group_id:id_list}, function(data){
						if(data.ret==1){
							window.location.reload();
						}
					}, 'json');
				}, 'confirm');
				return false;
			});
			$('#themes .r_con_table tbody').dragsort({
				dragSelector:'tr',
				dragSelectorExclude:'a, td[data!=move_myorder]',
				placeHolderTemplate:'<tr class="placeHolder"></tr>',
				scrollSpeed:5,
				dragEnd:function(){
					var data=$(this).parent().children('tr').map(function(){
						return $(this).attr('data-id');
					}).get();
					$.get('?', {do_action:'set.themes_nav_order', sort_order:data.join('|'), Type:set_obj.themes_nav_global.type}, function(){
						var num=0;
						$('.r_con_table tbody>tr').each(function(){
							$(this).attr('data-id', num);
							num++;
						});
					});
				}
			});
			$(document).on('change', 'select[name="Nav"]', function(){//是否有下拉 && 产品、单页判断
				var $this=$(this),
					opt=$this.find('option:selected'),
					$urlBox=$('#edit_form input[name="Url"]').parents('.rows');
				if(opt.attr('down')==1 && !$this.hasClass('no_down')){
					$('#edit_form select[name="Down"]').html('<option value="0">'+lang_obj.global.n_y[0]+'</option><option value="1">'+lang_obj.global.n_y[1]+'</option>');
				}else{
					$('#edit_form select[name="Down"]').html('<option value="0">'+lang_obj.global.n_y[0]+'</option>');
				}
				$urlBox.hide();
				if($this.val()==-1){//自定义
					$this.siblings('.nav_oth').hide().eq(3).show();
					$urlBox.show();
				}else if($this.val()==1){//单页
					$this.siblings('.nav_oth').hide().eq(0).show();
				}else if($this.val()==2){//文章
					$this.siblings('.nav_oth').hide().eq(1).show();
				}else if($this.val()==3){//产品
					$this.siblings('.nav_oth').hide().eq(2).show();
				}else{
					$this.siblings('.nav_oth').hide();
				}
			});
			/* 导航编辑弹出框 */
			$('.r_nav .ico .add, .r_con_table .edit').on('click', function(){
				var $id=$(this).attr('data-id'),
					$obj=$('.box_nav_edit'),
					$nav_data=$.evalJSON($('.r_con_table').attr('data'));
				frame_obj.pop_form($obj);
				frame_obj.rows_input();
				if($id){ //编辑
					var $data=$nav_data[$id-1], Nav=0;
					if($data['Custom']==1) Nav=-1;
					if($data['Nav']) Nav=$data['Nav'];
					//标题
					$('#edit_form select[name=Nav]').val(Nav).change();
					$('#edit_form select[name=Page]').val($data['Page']);
					$('#edit_form select[name=Info]').val($data['Info']);
					$('#edit_form select[name=Cate]').val($data['Cate']);
					$('#edit_form .nav_oth:eq(3) input').each(function(){
						$(this).val($data[$(this).attr('Name')]);
					});
					//链接地址
					$('#edit_form input[name=Url]').val($data['Url']);
					//下拉、下拉宽度、新窗口
					$('#edit_form select[name=Down]').val($data['Down']);
					$('#edit_form select[name=DownWidth]').val($data['DownWidth']);
					$('#edit_form select[name=NewTarget]').val($data['NewTarget']);
					$('.box_nav_edit .t>h1>span').text(lang_obj.global.edit);
					//id
					$('#edit_form input[name=Id]').val($id);
				}else{ //添加
					//标题
					$('#edit_form select[name=Nav]').val(0).change();
					$('#edit_form select[name=Page], #edit_form select[name=Info], #edit_form select[name=Cate]').val('');
					$('#edit_form .nav_oth:eq(3) input').val('');
					//链接地址
					$('#edit_form input[name=Url]').val('');
					//下拉、下拉宽度、新窗口
					$('#edit_form select[name=Down], #edit_form select[name=DownWidth], #edit_form select[name=NewTarget]').val(0);
					$('.box_nav_edit .t>h1>span').text(lang_obj.global.add);
					//id
					$('#edit_form input[name=Id]').val(0);
				}
				return false;
			});
		}
	},
	
	themes_nav_init:function(){
		set_obj.themes_nav_global.type='nav';
		set_obj.themes_nav_global.init();
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=themes&d=nav');
	},
	
	themes_footer_nav_init:function(){
		set_obj.themes_nav_global.type='foot_nav';
		set_obj.themes_nav_global.init();
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=themes&d=footer_nav');
	},
	
	themes_style_edit_init:function(){
		var obj=$('#edit_form');
		obj.delegate('input[name=reset]', 'click', function(){
			global_obj.win_alert(lang_obj.global.reset_confirm, function(){
				$.get('?', "do_action=set.themes_style_reset", function(data){
					if(data.ret!=1){
						global_obj.win_alert(data.msg);
					}else{
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
		});
		$('input[name=NavBgColor]').length && obj.delegate('input[name=NavBgColor]', 'change', function(){$('.NavBgColor').css('background-color', '#'+$(this).val());});
		$('input[name=NavHoverBgColor]').length && $('.NavHoverBgColor').mouseover(function(){$(this).css('background', '#'+$('input[name=NavHoverBgColor]').val());}).mouseleave(function(){$(this).css('background', 'none');});
		$('input[name=NavBorderColor1]').length && obj.delegate('input[name=NavBorderColor1]', 'change', function(){$('.NavBorderColor1').css('border-color', '#'+$(this).val());});
		$('input[name=NavBorderColor2]').length && obj.delegate('input[name=NavBorderColor2]', 'change', function(){$('.NavBorderColor2').css('border-color', '#'+$(this).val());});
		$('input[name=CategoryBgColor]').length && obj.delegate('input[name=CategoryBgColor]', 'change', function(){$('.CategoryBgColor').css('background-color', '#'+$(this).val());});
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=themes&d=style');
	},
	
	shipping_global:{
		init:function(){ //地区设置
			$('.open_area').on('click', function(){
				var $url=$(this).attr('data-url'),
					$name=$(this).attr('data-name');
				frame_obj.pop_iframe($url, lang_obj.manage.shipping.area_config+'<span class="small">[ '+$name+' ]</span>');
				return false;
			});
		},
		weight:function(){
			var WeightBetween=$('#WeightBetween'),
				VolumeBetween=$('#VolumeBetween'),
				WeightArea=$('#WeightArea'), //重量区间的div
				ExtWeightArea=$('#ExtWeightArea'), //续重区间的div
				Weightrow=$('#Weightrow'), //重量区间，只包含输入框的div
				ExtWeightrow=$('#ExtWeightrow'), //只包含输入框的div
				ExtWeight=$('#ExtWeight'), //首重续重div
				Quantity=$('#Quantity'), //按数量div
				VolumeArea=$('#VolumeArea'), //体积区间的div
				Volumerow=$('#Volumerow'), //体积区间，只包含输入框的div
				FirstWeight=$('#FirstWeight'),
				StartWeight_span=$('#StartWeight_span'),
				StartWeight=$('#StartWeight_span input'), //混合计算，区间开始计算的重量
				MinWeight=$('#MinWeight'); //最小重量限制
				MaxWeight=$('#MaxWeight'); //最大重量限制
				MinVolume=$('#MinVolume'); //最小体积限制
				MaxVolume=$('#MaxVolume'); //最大体积限制
				fixed_weight=$('input[name="WeightArea[]"]:first', WeightArea), //重量区间第一个输入框，固定不能修改
				fixed_Extweight=$('input[name="ExtWeightArea[]"]:first', ExtWeightArea); //重量区间第一个输入框，固定不能修改
				fixed_volume=$('input[name="VolumeArea[]"]:first', VolumeArea), //体积区间第一个输入框，固定不能修改
			
			$('input[name="IsWeightArea"]').change(function(){
				var val=parseInt($(this).val());
				WeightBetween.hide();
				VolumeBetween.hide();
				StartWeight_span.hide();
				fixed_weight.val(0); //只按照区间计费就必须从0kg开始
				WeightArea.hide();
				ExtWeight.hide();
				ExtWeightArea.hide();
				Quantity.hide();
				VolumeArea.hide();
				MaxWeight.attr({'disabled':false, 'readonly':false});
				MaxVolume.attr({'disabled':false, 'readonly':false});
				if(val!=3){
					WeightBetween.show();
					WeightBetween.find('.box_max').show();
					WeightBetween.find('.box_unlimited').hide();
				}
				switch(val){
					case 1: //区间
						WeightArea.show();
						break;
					case 2: //重量混合计算，从输入的值开始
						StartWeight_span.show();
						fixed_weight.val(StartWeight.val());
						WeightArea.show();
						ExtWeight.show();
						ExtWeightArea.show();
						break;
					case 3: //按数量
						Quantity.show();
						break;
					case 4: //重量体积混合
						WeightBetween.find('.box_max').hide();
						WeightBetween.find('.box_unlimited').show();
						VolumeBetween.show();
						fixed_weight.val(MinWeight.val());
						WeightArea.show();
						VolumeArea.show();
						MaxWeight.attr({'disabled':true, 'readonly':true});
						MaxVolume.attr({'disabled':true, 'readonly':true});
						break;
					default: //首重
						ExtWeight.show();
						ExtWeightArea.show();
						break;
				}
			});
			$('input[name="IsWeightArea"]:checked').change(); //勾选点击
			StartWeight.focus(function(){
				StartWeight.select();
			});
			StartWeight.keyup(function(){
				fixed_weight.val($(this).val());
			});
			//重量区间
			$('#addWeight').click(function(){//新增重量区间输入节点
				var Weightunit=$(this).attr('data-unit');
				Weightrow.append('<div class="row"><span class="price_input"><input type="text" name="WeightArea[]" value="" class="form_input" size="6" maxlength="10" rel="amount"><b class="last">'+Weightunit+'</b></span><a href="javascript:;"><img hspace="5" src="/static/ico/del.png"></a></div>');
			});
			WeightArea.on('click', '.row a', function(){//删除重量区间输入节点
				$(this).parent().remove();
			});
			//体积区间
			$('#addVolume').click(function(){//新增重量区间输入节点
				var Volumeunit=$(this).attr('data-unit');
				Volumerow.append('<div class="row"><span class="price_input"><input type="text" name="VolumeArea[]" value="" class="form_input" size="6" maxlength="10" rel="amount"><b class="last">'+Volumeunit+'</b></span><a href="javascript:;"><img hspace="5" src="/static/ico/del.png"></a></div>');
			});
			VolumeArea.on('click', '.row a', function(){//删除重量区间输入节点
				$(this).parent().remove();
			});
			
			FirstWeight.focus(function(){
				FirstWeight.select();
			});
			FirstWeight.keyup(function(){
				fixed_Extweight.val(($(this).val()?parseFloat($(this).val()):0)+.001);
			});
			$('#addExtWeight').click(function(){//新增续重区间输入节点
				var Weightunit=$(this).attr('data-unit');
				ExtWeightrow.append('<div class="row"><span class="price_input"><input type="text" name="ExtWeightArea[]" value="" class="form_input" size="6" maxlength="10" rel="amount"><b class="last">'+Weightunit+'</b></span><a href="javascript:;"><img hspace="5" src="/static/ico/del.png"></a></div>');
			});
			ExtWeightArea.on('click', '.row a', function(){//删除续重区间输入节点
				$(this).parent().remove();
			});
			
			MinWeight.focus(function(){
				MinWeight.select();
			});
			MinWeight.keyup(function(){
				fixed_weight.val(($(this).val()?parseFloat($(this).val()):0));
			});
			
			MinVolume.focus(function(){
				MinVolume.select();
			});
			MinVolume.keyup(function(){
				fixed_volume.val(($(this).val()?parseFloat($(this).val()):0));
			});
		},
		volume:function(){
			var WeightArea=$('#VolumeArea');//重量区间的div
			var Weightrow=$('#Volumerow');//只包含输入框的div
			$('#addVolume').click(function(){//新增区间输入节点
				var Weightunit=$(this).attr('data-unit');
				Weightrow.append('<div class="row"><span class="price_input"><input type="text" name="VolumeArea[]" value=" " class="form_input" size="6" maxlength="5" rel="amount"><b class="last">'+Weightunit+'</b></span><a href="javascript:;"><img hspace="5" src="/static/ico/del.png"></a></div>');
			});
			WeightArea.on('click', '.row a', function(){//删除区间输入节点
				$(this).parent().remove();
			});
		},
		country:function(){
			var left_country=$('#left_country');//左边未选择的国家
			var right_country=$('#right_country');//右边已选择的国家
			var country_anti=function($this, obj){
				var ischeck=0,
					continent=0,
					$input=$this.parent().find('.input_anti');
				if($(obj).parent().find('.continent_list').length){
					continent=$(obj).parent().find('.continent_list a.current').attr('continent');
				}
				$(obj).find('.item'+(continent?'[continent='+continent+']':'')+' .select_cid').each(function(index, element){
					if($(element).attr('checked')){
						$(element).attr('checked', false);
					}else{
						$(element).attr('checked', true);
						ischeck=1;
					}
				});
				if(ischeck==1){ //勾选
					$input.attr('checked', true);
				}else{ //取消
					$input.attr('checked', false);
				}
			};
			$('.left_anti').click(function(e){
				country_anti($(this), left_country);
			});
			$('.right_anti').click(function(e){
				country_anti($(this), right_country);
			});
			$('.country_box .item .select_cid').click(function(e){
				e.stopPropagation();
			});
			$('.country_box .item').click(function(e){
				if($('.select_cid', this).attr('checked')){
					$('.select_cid', this).attr('checked', false);
				}else{
					$('.select_cid', this).attr('checked', true);
				}
			});
			$('.btn_add').click(function(){//批量添加按钮
				$('.item .select_cid', left_country).each(function(index, element){
					if($(element).attr('checked')){
						$(element).attr('checked', false);
						right_country.children('.initial_'+$(element).attr('initial')).after($(element).parent('.item'));
					}
				});
				set_obj.shipping_global.country_size();
				$('#edit_form input:submit').click(); //直接提交
			});
			$('.btn_cut').click(function(){//批量删除按钮
				$('.item .select_cid', right_country).each(function(index, element){
					if($(element).attr('checked')){
						$(element).attr('checked', false);
						left_country.children('.initial_'+$(element).attr('initial')).after($(element).parent('.item'));
					}
				});
				set_obj.shipping_global.country_size();
				$('#edit_form input:submit').click(); //直接提交
			});
			var itemH=$('.country_box .item').eq(0).outerHeight(true);//一个item的高度
			$('.initial_left_list a').click(function(){ //点击字母定位事件 左侧
				var val=$(this).attr('initial');
				var obj=$('.initial_'+val, left_country);
				if(obj.length){
					var sTop=obj.prevAll('.item').length*itemH;
					left_country.scrollTop(sTop);
				}
			});
			$('.initial_right_list a').click(function(){ //点击字母定位事件 右侧
				var val=$(this).attr('initial');
				var obj=$('.initial_'+val, right_country);
				if(obj.length){
					var sTop=obj.prevAll('.item').length*itemH;
					right_country.scrollTop(sTop);
				}
			});
			$('.continent_list a').click(function(){ //点击洲筛选国家事件 左侧
				var val=$(this).attr('continent');
				var obj=$('.item[continent='+val+']', left_country);
				if($(this).hasClass('current')){ //取消
					$(this).removeClass('current');
					$('.item', left_country).show();
				}else{ //勾选
					$(this).addClass('current').siblings().removeClass('current');
					obj.show();
					$('.item[continent!='+val+']', left_country).hide().find(':checkbox').attr('checked', false);
				}
			});
			set_obj.shipping_global.country_size();
			set_obj.shipping_global.country_box_size();
			$(window).resize(function(){
				set_obj.shipping_global.country_box_size();
			});
		},
		country_size:function(){
			$('#left_country, #right_country').children('.initial').each(function(){ //首字母js
				if($(this).next().hasClass('item')){
					$(this).show();
				}else{
					$(this).hide();
				}
			});
			$('.initial_left_list a').each(function(){ //定位js
				var val=$(this).attr('initial');
				var obj=$('.initial_'+val+':visible', left_country);
				if(!obj.length){
					$(this).hide();
				}else{
					$(this).show();
				}
			});
			$('.initial_right_list a').each(function(){ //定位js
				var val=$(this).attr('initial');
				var obj=$('.initial_'+val+':visible', right_country);
				if(!obj.length){
					$(this).hide();
				}else{
					$(this).show();
				}
			});
		},
		country_box_size:function(){
			var $menu_h=$('.menu_list').outerHeight();
			$('.country_list td:eq(0), .country_list td:eq(2)').each(function(){
				$(this).find('.country_box').css('height', ($menu_h-$('.shipping_area_title').outerHeight(true)-$(this).find('.country_title').outerHeight()-$(this).find('.continent_list').outerHeight()-$(this).find('.initial_list').outerHeight()-$(this).find('.btn_anti').outerHeight()-2));
			});
		}
	},
	
	shipping_set_edit_init:function(){
		frame_obj.switchery_checkbox();
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=shipping&d=set');
	},
	
	shipping_express_init:function(){
		frame_obj.del_init($('#shipping .r_con_table'));
		set_obj.shipping_global.init();
	},
	
	shipping_express_edit_init:function(){
		frame_obj.switchery_checkbox();
		//API接口
		$('.api_box .choice_btn').click(function(){
			var $this=$(this), $Attr=new Object, $Html='';
			$this.addClass('current').siblings().removeClass('current');
			$this.children('input').attr('checked', true);
			if($this.find('input').val()==0){ //不使用
				$('#method_shipping_box').show();
				$('#method_api_box').hide().find('.input').html('');
			}else{ //使用
				$('#method_shipping_box').hide();
				$('#method_api_box').show();
				$Attr=$.evalJSON($this.attr('data-attribute'));
				typeof $Attr!=='object' && ($Attr=$.evalJSON($Attr)); //返回是字符串，就再转换一次
				for(k in $Attr){ //数据的输出
					$Html+='<span class="price_input lang_input"><b>'+k+'<div class="arrow"><em></em><i></i></div></b><input type="text" name="Value[]" value="'+global_obj.htmlspecialchars_decode($Attr[k])+'" class="form_input input_name" size="80" maxlength="100" /><input type="hidden" name="Name[]" value="'+k+'" /></span>';
				}
				if($this.attr('data-name')=='4PX'){ //目前仅有4PX
					$Html+='<div class="blank5"></div><a href="/plugins/api/'+$this.attr('data-api-name')+'/note/'+$this.attr('data-name')+'.xls" target="_blank">说明文档</a>';
				}
				$('#method_api_box').find('.input').html($Html);
				frame_obj.rows_input();
			}
		});
		if($('.api_box input[name=IsAPI]:checked').val()>0){ //如果有勾选，默认点击触发事件
			$('.api_box input[name=IsAPI]:checked').parent().click();
		}
		//重量计算方式
		$('#WeightArea .choice_btn').click(function(){
			var $this=$(this);
			if($this.children('input').is(':checked')){
				$this.removeClass('current').children('input').attr('checked', false);
			}else{
				$this.addClass('current').children('input').attr('checked', true);
			}
		});
		/* 快递LOGO上传 */
		$('#LogoUpload, .upload_logo .edit').on('click', function(){frame_obj.photo_choice_init('LogoUpload', 'form input[name=Logo]', 'LogoDetail', 'shipping', 1);});
		if($('form input[name=Logo]').attr('save')==1){
			$('#LogoDetail').append(frame_obj.upload_img_detail($('form input[name=Logo]').val())).children('.upload_btn').hide();
		}
		$('.upload_logo .del').on('click', function(){
			$('#LogoDetail').children('a').remove();
			$('#LogoDetail').children('.upload_btn').show();
			$('#edit_form input[name=Logo]').val('');
		});
		set_obj.shipping_global.weight();
		/* 表单提交 */
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=shipping&d=express');
	},
	
	shipping_express_area_init:function(){
		var obj={
			init: function(){
				frame_obj.del_init($('#shipping .shipping_area')); //删除事件
				$('#shipping .list_btn>a, #shipping .unit_box .item').off().on('click', function(){
					var $this=$(this);
					obj.load_edit_form('.menu_list', $this.attr('data-url'), 'GET', '', function(){
						obj.init();
						$('.shipping_area_edit').html('');
						$('.unit_box').removeClass('show');
						//$('.shipping_area .menu_list').jScrollPane();
						$('#shipping .list_btn>a[data-id='+$this.attr('data-id')+']').addClass('current').siblings().removeClass('current');
					});
				});
				$('.unit_box .add_unit').off().on('click', function(){ //海外仓 更多按钮
					var $obj=$('.unit_box');
					if($obj.hasClass('show')){
						$obj.removeClass('show');
					}else{
						$obj.addClass('show');
					}
				});
				$(document).off().on('click', function(e){ //关闭 海外仓更多信息
					if($('.unit_box.show').length){
						var $obj=$('.unit_box.show').parent();
						if(!(e.target==$obj[0] || $.contains($obj[0], e.target)) && $('.unit_box.show').length){
							$('.unit_box').removeClass('show');
						}
					}
				});
				$('.btn_overseas_add').off().on('click', function(){ //海外仓 添加按钮
					var $obj=$('.unit_box');
					if(global_obj.check_form($obj.find('*[notnull]'), $obj.find('*[format]'), 1)){return false;};
					$.post('?do_action=set.shipping_overseas_edit', $('form[name=Overseas]').serialize(), function(data){
						if(data.ret==1){
							window.location.reload();
						}
					}, 'json');
				});
				$('.btn_overseas_del').off().on('click', function(){ //海外仓 删除按钮
					var o=$(this);
					global_obj.win_alert(lang_obj.manage.shipping.overseas_del_confirm, function(){
						$.get(o.attr('href'), function(data){
							if(data.ret==1){
								window.location.reload();
							}else{
								global_obj.win_alert(data.msg);
							}
						}, 'json');
					}, 'confirm');
					return false;
				});
				$('.add, .edit, .set').off().on('click', function(){
					var $this=$(this);
					obj.load_edit_form('.shipping_area_edit', $(this).attr('data-url'), 'GET', '', function(){
						frame_obj.rows_input();
						if($this.hasClass('set')){ //地区编辑
							set_obj.shipping_global.country();
							frame_obj.submit_form_init($('#edit_form'), '', '', '', function(){
								$('#edit_form input:submit').attr('disabled', false);
							});
						}else{
							frame_obj.switchery_checkbox();
							frame_obj.submit_form_init($('#shipping_area_edit_form'), '', '', '', function(data){
								//window.location.reload();
								//$('.shipping_area_edit').html('');
								$('#shipping .nav_list a[data-id='+data.msg+']').click();
							});
							
							$('.box_select').on('mouseleave', function(){
								$list=$(this).find('.list');
								$(this).find('.head').removeClass('selected');
								$list.hide();
							}).on('mouseenter', function(){
								$list=$(this).find('.list');
								$(this).find('.head').addClass('selected');
								$list.show();
							}).on('click', '.list>li', function(){
								$this=$(this);
								$input=$this.parents('.price_input');
								$list=$input.find('.list');
								$title=$this.text();
								
								if($this.index()==1){ //KG
									$input.find('input[name=FreeShippingPrice]').hide().attr('disabled', true);
									$input.find('input[name=FreeShippingWeight]').show().attr('disabled', false);
								}else{ //$
									$input.find('input[name=FreeShippingPrice]').show().attr('disabled', false);
									$input.find('input[name=FreeShippingWeight]').hide().attr('disabled', true);
								}
								$input.find('.head>span').text($title);
								$list.hide();
							});
						}
					});
				});
			},
			load_edit_form: function(target_obj, url, type, value, callback){
				$.ajax({
					type:type,
					url:url+value,
					success:function(data){
						if(target_obj=='.menu_list'){ //左侧栏目，保留滚动条效果
							//$(target_obj).find('.jspPane').html($(data).find(target_obj).html());
							$(target_obj).html($(data).find(target_obj).html());
							$(target_obj).jScrollPane();
						}else{
							$(target_obj).html($(data).find(target_obj).html());
							jQuery.getScript('/static/js/plugin/tool_tips/tool_tips_shipping.js').done(function(){
								$('.tool_tips_ico').each(function(){ //弹出提示
									$(this).html('&nbsp;');
									$('#shipping').tool_tips($(this), {position:'horizontal', html:$(this).attr('content'), width:260});
								});
							});
						}
						callback && callback(data);
					}
				});
			}
		}
		obj.init();
		$('#shipping .list_btn>a:eq(0)').click();
	},
	
	shipping_air_edit_init:function(){
		set_obj.shipping_global.weight();
		set_obj.shipping_global.volume();
		set_obj.shipping_global.init();
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=shipping&d=air');
	},
	
	shipping_air_area_init:function(){
		frame_obj.del_init($('#shipping .shipping_area'));
		var o=$('.shipping_area');
		var load_edit_form=function(target_obj, url, type, value, callback){
			$.ajax({
				type:type,
				url:url+value,
				success:function(data){
					$(target_obj).html($(data).find(target_obj).html());
					callback && callback(data);
				}
			});
		};
		$('.add, .edit, .set').click(function(){
			var $this=$(this);
			load_edit_form('.shipping_area_edit', $(this).attr('data-url'), 'GET', '', function(){
				frame_obj.rows_input();
				if($this.hasClass('set')){ //地区编辑
					set_obj.shipping_global.country();
					frame_obj.submit_form_init($('#edit_form'), '', '', '', function(){
						$('#edit_form input:submit').attr('disabled', false);
					});
				}else{
					frame_obj.switchery_checkbox();
					frame_obj.submit_form_init($('#shipping_area_edit_form'), '', '', '', function(){
						window.location.reload();
						//$('.shipping_area_edit').html('');
					});
				}
			});
		});
	},
	
	shipping_ocean_edit_init:function(){
		set_obj.shipping_global.weight();
		set_obj.shipping_global.volume();
		set_obj.shipping_global.init();
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=shipping&d=ocean');
	},
	
	shipping_ocean_area_init:function(){
		frame_obj.del_init($('#shipping .shipping_area'));
		var o=$('.shipping_area');
		var load_edit_form=function(target_obj, url, type, value, callback){
			$.ajax({
				type:type,
				url:url+value,
				success:function(data){
					$(target_obj).html($(data).find(target_obj).html());
					callback && callback(data);
				}
			});
		};
		$('.add, .edit, .set').click(function(){
			var $this=$(this);
			load_edit_form('.shipping_area_edit', $(this).attr('data-url'), 'GET', '', function(){
				frame_obj.rows_input();
				if($this.hasClass('set')){ //地区编辑
					set_obj.shipping_global.country();
					frame_obj.submit_form_init($('#edit_form'), '', '', '', function(){
						$('#edit_form input:submit').attr('disabled', false);
					});
				}else{
					frame_obj.switchery_checkbox();
					frame_obj.submit_form_init($('#shipping_area_edit_form'), '', '', '', function(){
						window.location.reload();
						//$('.shipping_area_edit').html('');
					});
				}
			});
		});
	},
	
	shipping_insurance_edit_init:function(){
		frame_obj.switchery_checkbox();
		var InsArea=$('#InsArea');
		var currency=InsArea.attr('currency');
		var tips=InsArea.attr('tips');
		$('#addArea').click(function(){	//增加节点
			InsArea.append('<tr><td nowrap="nowrap"><span class="price_input"><b>'+currency+'<div class="arrow"><em></em><i></i></div></b><input type="text" name="ProPrice[]" value="" class="form_input" size="3" maxlength="5" rel="amount" notnull><b class="last">'+tips+'</b></span></td><td nowrap="nowrap"><span class="price_input"><b>'+currency+'<div class="arrow"><em></em><i></i></div></b><input type="text" name="AreaPrice[]" value="" class="form_input" size="3" maxlength="5" rel="amount" notnull></span><a href="javascript:;"><img hspace="5" src="/static/ico/del.png"></a></td></tr>');
        });
		InsArea.on('click', 'tr a', function(){	//删除区间输入节点
            if(InsArea.find('tr').size()>2){
				$(this).parent().parent().remove();
			}
        });
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=shipping&d=insurance');
	},
	
	shipping_overseas_init:function(){
		$('#shipping .r_con_table .del').off().on('click', function(){ //海外仓 删除按钮
			var o=$(this);
			global_obj.win_alert(lang_obj.manage.shipping.overseas_del_confirm, function(){
				$.get(o.attr('href'), function(data){
					if(data.ret==1){
						window.location.reload();
					}else{
						global_obj.win_alert(data.msg);
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
		/* 编辑弹出框 */
		$('.r_nav .ico .add, .r_con_table .edit').on('click', function(){
			var $id=parseInt($(this).attr('data-id')),
				$obj=$('.box_overseas_edit');
			frame_obj.pop_form($obj);
			frame_obj.rows_input();
			if($id>=0){ //编辑
				var $data=$.evalJSON($(this).parents('tr').attr('data'));
				$obj.find('.rows:eq(0) input').each(function(){ //名称
					$(this).val($data[$(this).attr('Name')]?global_obj.htmlspecialchars_decode($data[$(this).attr('Name')]):'');
				});
				$('#edit_form input[name=OvId]').val($id); //id
				$('.box_overseas_edit .t>h1>span').text(lang_obj.global.edit); //编辑框标题
			}else{ //添加
				$obj.find('.rows:eq(0) input').each(function(){ //名称
					$(this).val('');
				});
				$('#edit_form input[name=OvId]').val(0); //id
				$('.box_overseas_edit .t>h1>span').text(lang_obj.global.add); //编辑框标题
			}
			return false;
		});
		/* 表单提交 */
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=shipping&d=overseas');
	},
	
	manage_init:function(){
		frame_obj.del_init($('#manage .r_con_table'));
	},
	
	photo_global:{
		list:function(){//图片银行列表
			$('body').on('click', '.bat_open', function(){//全选
				$('#photo_list_form input:checkbox').not(':checked').each(function(){
					$(this).parent('.img').click();
				});
			}).on('click', '.add', function(){//添加
				var $obj=$('.box_photo_edit');
				frame_obj.pop_form($obj);
				return false;
			}).on('click', '.del', function(){//删除
				var $this=$(this);
				global_obj.win_alert(lang_obj.global.del_confirm, function(){
					$.post('?', $('#photo_list_form').serialize(), function (data){
						if(data.ret!=1){
							global_obj.win_alert(data.msg);
						}else{
							//window.location='./?m=set&a=photo&CateId='+$('#photo_list_form input[name=CateId]').val()+'&IsSystem='+$('#photo_list_form input[name=IsSystem]').val()+'&page='+$('#photo_list_form input[name=Page]').val();
							window.location='./?m=set&a=photo&CateMenu='+$('.search_form select[name=CateMenu] option:selected').val()+'&Keyword='+$('.search_form input[name=Keyword]').val()+'&page='+$('#photo_list_form input[name=Page]').val();
						}
					}, 'json');
				}, 'confirm');
				return false;
			}).on('click', '.clears', function(e){//清空临时文件夹
				var $this=$(this);
				global_obj.win_alert(lang_obj.global.del_confirm, function(){
					$.post('?', {do_action:'set.photo_clear_folder'}, function (data){
						if(data.ret==1){
							global_obj.win_alert(lang_obj.manage.photo.empty_temp);
						}
					}, 'json');
				}, 'confirm');
				return false;
			}).on('click', '.photo_list .item .img', function(){//勾选图片框
				var parent=$(this).parent('.item'),
					$sort=$('#photo input[name=sort]').val(),
					$val=$(this).find('input').val();
				if(parent.hasClass('cur')){
					parent.removeClass('cur');
					$(this).find('input').attr('checked', false);
					$(this).find('.img_mask').hide();
					if($sort && global_obj.in_array($val, $sort.split('|'))){
						$('#photo input[name=sort]').val($sort.replace('|'+$val+'|', '|'));
					}
				}else{
					parent.addClass('cur');
					$(this).find('input').attr('checked', true);
					$(this).find('.img_mask').show();
					if($sort && !global_obj.in_array($val, $sort.split('|'))){
						$('#photo input[name=sort]').val($sort+$val+'|');
					}
				}
				return false;
			}).on('click', '.photo_list .item .zoom', function(e){
				e.stopPropagation();
			}).on('click', '.refresh', function(){ //单个移动（已移除）
				frame_obj.pop_iframe_page_init('./?m=set&a=photo&d=move&PId='+$(this).prev().val(), 'user_group');
			}).on('click', '.move', function(){ //批量移动
				var $obj=$('.box_move_edit'),
					$html='';
				frame_obj.pop_form($obj);
				$obj.find('input[name=PId], input[name=PId\\[\\]]').remove();
				$('.PIds:checked').each(function(){
					$html+='<input type="hidden" name="PId[]" value="'+$(this).val()+'" />';
				});
				$obj.find('.rows').after($html);
				return false;
			})/*.on('click', '#button_add', function(){
				var save=$('input[name=save]').val(),//保存图片隐藏域ID
					id=$('input[name=id]').val(),//显示元素的ID
					type=$('input[name=type]').val(),//类型
					maxpic=$('input[name=maxpic]').val();//最大允许图片数
				frame_obj.photo_choice_return(id, type, save, maxpic);
			}).on('click', 'input.btn_cancel', function(){
				var not_div_mask=0,
					callback=parent.$('input:hidden.callback').val();
				callback=='not_div_mask=1;' && eval(callback);
				parent.frame_obj.pop_contents_close_init(parent.$('#photo_choice'), 1, not_div_mask);
			})*/;
			//提交
			frame_obj.submit_form_init($('#edit_form'), './?m=set&a=photo');
			frame_obj.submit_form_init($('#move_edit_form'), './?m=set&a=photo');
		}
	},
	
	photo_choice_init:function(){
		frame_obj.category_wrap_page_init();
		//$('.upload .tips').text(parent.$('#'+$('input[name=obj]').val()).attr('tips'));
		/*
		var save=$('input[name=save]').val(),//保存图片隐藏域ID
			id=$('input[name=id]').val(),//显示元素的ID
			type=$('input[name=type]').val(),//类型
			maxpic=$('input[name=maxpic]').val(),//最大允许图片数
			number=0;//执行次数
		frame_obj.file_upload($('#PicUpload'), '', '', type, true, maxpic, function(imgpath, surplus){
			frame_obj.photo_choice_return(id, type, save, maxpic, 1, imgpath, surplus, ++number);
		});
		*/
		var save=$('input[name=save]').val(),//保存图片隐藏域ID
			id=$('input[name=id]').val(),//显示元素的ID
			type=$('input[name=type]').val(),//类型
			maxpic=$('input[name=maxpic]').val(),//最大允许图片数
			number=0;//执行次数
		$('form[name=upload_form]').fileupload({
			url: '/manage/?do_action=action.file_upload&size='+type,
			acceptFileTypes: /^image\/(gif|jpe?g|png|x-icon)$/i,
			callback: function(imgpath, surplus, name){
				frame_obj.photo_choice_return(id, type, save, maxpic, 1, imgpath, surplus, ++number,name);
			}
		});
		$('form[name=upload_form]').fileupload(
			'option',
			'redirect',
			window.location.href.replace(/\/[^\/]*$/, '/cors/result.html?%s')
		);
		set_obj.photo_global.list();
	},
	
	photo_init:function(){
		frame_obj.del_init($('#photo .category'));
		set_obj.photo_global.list();
	},
	
	photo_category_init:function(){
		frame_obj.del_init($('#photo .r_con_table')); //删除事件
		frame_obj.select_all($('#photo .r_con_table input[name=select_all]'), $('#photo .r_con_table input[name=select]')); //批量操作
		/* 批量删除 */
		frame_obj.del_bat($('.r_nav .del'), $('#photo .r_con_table input[name=select]'), function(id_list){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('?', {do_action:'set.photo_category_del_bat', group_id:id_list}, function(data){
					if(data.ret==1){
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
		/* 批量排序 */
		$('#photo .r_con_table .myorder_select').on('dblclick', function(){
			var $obj=$(this),
				$number=$obj.attr('data-num'),
				$CateId=$obj.parents('tr').find('td:eq(0)>input').val(),
				$mHtml=$obj.html(),
				$sHtml=$('#myorder_select_hide').html(),
				$val;
			$obj.html($sHtml+'<span style="display:none;">'+$mHtml+'</span>');
			$number && $obj.find('select').val($number).focus();
			$obj.find('select').on('blur', function(){
				$val=$(this).val();
				if($val!=$number){
					$.post('?', 'do_action=set.photo_category_edit_myorder&Id='+$CateId+'&Number='+$(this).val(), function(data){
						if(data.ret==1){
							$obj.html(data.msg);
							$obj.attr('data-num', $val);
						}
					}, 'json');
				}else{
					$obj.html($obj.find('span').html());
				}
			});
		});
		$('#photo .r_con_table tbody').dragsort({
			dragSelector:'tr',
			dragSelectorExclude:'a, td[data!=move_myorder], dl',
			placeHolderTemplate:'<tr class="placeHolder"></tr>',
			scrollSpeed:5,
			dragEnd:function(){
				var data=$(this).parent().children('tr').map(function(){
					return $(this).attr('cateid');
				}).get();
				$.get('?', {do_action:'set.photo_category_edit_myorder', sort_order:data.join('|')});
			}
		});
		$('#photo .attr_list').dragsort({
			dragSelector:'dl',
			dragSelectorExclude:'a, dd[class!=attr_ico]',
			placeHolderTemplate:'<dl class="attr_box placeHolder"></dl>',
			scrollSpeed:5,
			dragEnd:function(){
				var data=$(this).parent().children('dl').map(function(){
					return $(this).attr('cateid');
				}).get();
				$.get('?', {do_action:'set.photo_category_order', sort_order:data.join('|')});
			}
		});
		$('#photo .attr_box').hover(function(){
			$(this).children('.attr_menu').stop(true, true).animate({'right':0}, 200);
		}, function(){
			$(this).children('.attr_menu').stop(true, true).animate({'right':-51}, 200);
		});
		/* 编辑弹出框 */
		$('.r_nav .ico .add, .attr_add .add, .r_con_table .edit').on('click', function(){
			var $id=parseInt($(this).attr('data-id')),
				$obj=$('.box_photo_edit');
			frame_obj.pop_form($obj);
			frame_obj.rows_input();
			if($id){ //编辑
				var $data='';
				if($(this).parent().hasClass('attr_menu')){
					$data=$.evalJSON($(this).parents('dl.attr_box').attr('data'));
				}else{
					$data=$.evalJSON($(this).parents('tr').attr('data'));
				}
				$('#edit_form input[name=Category]').val($data['Category']); //分类名称
				$.post('?', {do_action:'set.photo_category_select', 'ParentId':0, 'CateId':$id}, function(data){
					$('#edit_form .rows:eq(2) .input').html(data); //分类所属
					$('#edit_form select[name=UnderTheCateId]').val($data['TopCateId']);
				});
				$('#edit_form input[name=CateId]').val($id); //id
				$('.box_photo_edit .t>h1>span').text(lang_obj.global.edit); //编辑框标题
			}else{ //添加
				var $ParentId=0;
				if($(this).parent().hasClass('attr_add')){
					$ParentId=$(this).parents('tr').attr('cateid');
				}
				$('#edit_form input[name=Category]').val(''); //分类名称
				$.post('?', {do_action:'set.photo_category_select', 'ParentId':$ParentId, 'CateId':0}, function(data){
					$('#edit_form .rows:eq(2) .input').html(data); //分类所属
					$('#edit_form select[name=UnderTheCateId]').val($ParentId);
				});
				$('#edit_form input[name=CateId]').val(0); //id
				$('.box_photo_edit .t>h1>span').text(lang_obj.global.add); //编辑框标题
			}
			return false;
		});
		//提交
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=photo&d=category');
	},
	
	photo_upload_init:function(){
		/*
		var callback=function(imgpath, surplus, name){
			if($('#PicDetail .pic').size()>=20){
				global_obj.win_alert(lang_obj.manage.account.picture_tips.replace('xxx', 20));
				return;
			}
			$('#PicDetail').append('<div class="pic"><div>'+frame_obj.upload_img_detail(imgpath)+'<span>'+lang_obj.global.del+'</span><input type="hidden" name="PicPath[]" value="'+imgpath+'" /></div><input type="text" maxlength="30" class="form_input" value="'+name+'" name="Name[]" placeholder="'+lang_obj.global.picture_name+'" notnull></div>');
			$('#PicDetail div span').off('click').on('click', function(){
				var $this=$(this);
				global_obj.win_alert(lang_obj.global.del_confirm, function(){
					$.ajax({
						url:'./?m=set&a=photo&do_action=set.photo_upload_del&Path='+$this.prev().attr('href')+'&Index='+$this.parent().index(),
						success:function(data){
							json=eval('('+data+')');
							$('#PicDetail div:eq('+json.msg[0]+')').remove();
						}
					});
				}, 'confirm');
				return false;
			});
		};
		frame_obj.file_upload($('#PicUpload'), '', '', '', true, 20, callback, '', 'set.photo_file_upload');
		*/
		$('form[name=upload_form]').fileupload({
			url: '/manage/?do_action=action.file_upload_plugin&size=photo',
			acceptFileTypes: /^image\/(gif|jpe?g|png)$/i,
			callback: function(imgpath, surplus, name){
				if($('#PicDetail .pic').size()>=20){
					global_obj.win_alert(lang_obj.manage.account.picture_tips.replace('xxx', 20));
					return;
				}
				$('#PicDetail').append('<div class="pic"><div>'+frame_obj.upload_img_detail(imgpath)+'<span>'+lang_obj.global.del+'</span><input type="hidden" name="PicPath[]" value="'+imgpath+'" /></div><input type="text" maxlength="30" class="form_input" value="'+name+'" name="Name[]" placeholder="'+lang_obj.global.picture_name+'" notnull></div>');
				$('#PicDetail div span').off('click').on('click', function(){
					var $this=$(this);
					global_obj.win_alert(lang_obj.global.del_confirm, function(){
						$.ajax({
							url:'./?m=set&a=photo&do_action=set.photo_upload_del&Path='+$this.prev().attr('href')+'&Index='+$this.parent().parent().index(),
							success:function(data){
								json=eval('('+data+')');
								$('#PicDetail .pic:eq('+json.msg[0]+')').remove();
							}
						});
					}, 'confirm', 1);
					return false;
				});
			}
		});
		$('form[name=upload_form]').fileupload(
			'option',
			'redirect',
			window.location.href.replace(/\/[^\/]*$/, '/cors/result.html?%s')
		);
	},
	
	chat_set_init:function(){
		frame_obj.switchery_checkbox();
		
		/** 0 */
		$('#Bg3_0, .upload_Bg3_0 .edit').on('click', function(){frame_obj.photo_choice_init('Bg3_0', 'form input[name=Bg3_0]', 'DetailBg3_0', '', 1);});
		if($('form input[name=Bg3_0]').attr('save')==1){
			$('#DetailBg3_0').append(frame_obj.upload_img_detail($('form input[name=Bg3_0]').val())).children('.upload_btn').hide();
		}
		$('.upload_Bg3_0 .del').on('click', function(){
			$('#DetailBg3_0').children('a').remove();
			$('#DetailBg3_0').children('.upload_btn').show();
			$('#edit_form input[name=Bg3_0]').val('');
		});
		
		/** 1 */
		$('#Bg3_1, .upload_Bg3_1 .edit').on('click', function(){frame_obj.photo_choice_init('Bg3_1', 'form input[name=Bg3_1]', 'DetailBg3_1', '', 1);});
		if($('form input[name=Bg3_1]').attr('save')==1){
			$('#DetailBg3_1').append(frame_obj.upload_img_detail($('form input[name=Bg3_1]').val())).children('.upload_btn').hide();
		}
		$('.upload_Bg3_1 .del').on('click', function(){
			$('#DetailBg3_1').children('a').remove();
			$('#DetailBg3_1').children('.upload_btn').show();
			$('#edit_form input[name=Bg3_1]').val('');
		});
		
		/** 2 */
		$('#Bg4_0, .upload_Bg4_0 .edit').on('click', function(){frame_obj.photo_choice_init('Bg4_0', 'form input[name=Bg4_0]', 'DetailBg4_0', '', 1);});
		if($('form input[name=Bg4_0]').attr('save')==1){
			$('#DetailBg4_0').append(frame_obj.upload_img_detail($('form input[name=Bg4_0]').val())).children('.upload_btn').hide();
		}
		$('.upload_Bg4_0 .del').on('click', function(){
			$('#DetailBg4_0').children('a').remove();
			$('#DetailBg4_0').children('.upload_btn').show();
			$('#edit_form input[name=Bg4_0]').val('');
		});
		
		$('#chat .style_box input[name=Type]').click(function(){
			$Type=$(this).val();
			$.post('?', 'do_action=set.chat_style&Type='+$Type, function(data){
				if(data.ret==1){
				}else{
					global_obj.win_alert(lang_obj.global.set_error);
				}
			}, 'json');
		});
		
		$('.style_select').click(function(){
			var val = $(this).val();
			$('#bgcolor').show(0);
			$('#mulcolor').hide(0);
			$('#bg3pic').hide(0);
			$('#bg4pic').hide(0);
			if (val==1){
				$('#bgcolor').hide(0);
				$('#mulcolor').show(0);
			}else if (val==3){
				$('#mulcolor').show(0);
				$('#bg3pic').show(0);
			}else if (val==4){
				$('#bg4pic').show(0);
			}
			$('#window_style').val($(this).val());	
		});
		
		$('#chat .color').change(function (){
			var name = $(this).attr('name');
			$('.'+name).css('background-color', '#'+$(this).val()).attr('color', '#'+$(this).val());
			var hover = $('.hover'+name);
			hover.attr('hover-color', '#'+$(this).val());
		});
		
		$('#service_2 .Color').hover(function (){
			$(this).css('background-color', $(this).attr('hover-color'));
		}, function (){
			$(this).css('background-color', $(this).attr('color'));
		});
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=chat');
	},
	
	chat_init:function(){
		frame_obj.del_init($('#chat .r_con_table'));
		frame_obj.switchery_checkbox();
		frame_obj.submit_form_init($('#edit_form'), './?m=set&a=chat');
		
		//弹出框编辑
		var box_chat_edit = $('.box_chat_edit');
		var select_Type = $('select[name=Type]', box_chat_edit);//选择框
		var ubox = $('.ubox', box_chat_edit);//图片上传框
		var $data = box_chat_edit.attr('data-chat');//数据
		$data=$.evalJSON($data);
		$('.r_nav .add').click(function (){//添加
			frame_obj.pop_form(box_chat_edit);
			$('#Picture, .whatsapp_tips').hide(0);
			$('#PicDetail a').remove();
			$('#PicDetail .upload_btn').css('display', 'block');
			$('input[name=Name]', box_chat_edit).val('');
			$('input[name=CId]', box_chat_edit).val('');
			$('input[name=PicPath]', box_chat_edit).val('');
			$('input[name=Account]', box_chat_edit).val('');
			select_Type.find('option:selected').attr('selected', false);
			select_Type.find('option:eq(0)').attr('selected', true);
			if ($data.add){//判断添加权限
				ubox.css('display', 'block');
			}else{
				ubox.css('display', 'none');
			}
		});
		
		$('#chat').on('click', '.mod', function (){//修改
			var CId=$(this).attr('CId');
			var data=$data[CId];
			frame_obj.pop_form(box_chat_edit);
			$('#Picture, .whatsapp_tips').hide(0);
			$('#PicDetail a').remove();
			$('#PicDetail .upload_btn').css('display', 'block');
			$('input[name=Name]', box_chat_edit).val(data.Name);
			$('input[name=PicPath]', box_chat_edit).val(data.PicPath);
			$('input[name=CId]', box_chat_edit).val(CId);
			$('input[name=Account]', box_chat_edit).val(data.Account);
			select_Type.find('option:selected').attr('selected', false);
			select_Type.find('option[value="'+data.Type+'"]').attr('selected', true);
			
			if (data.Type==4){
				$('#Picture').show(0);
				if (data.PicPath){
					$('#PicDetail').append(frame_obj.upload_img_detail(data.PicPath)).children('.upload_btn').hide(0);
				}else{
					$('#PicDetail').children('.upload_btn').show(0);
				}
			}
			if(data.Type==5) $('.whatsapp_tips').show(0);
			
			if ($data.edit){//判断添加权限
				ubox.css('display', 'block');
			}else{
				ubox.css('display', 'none');
			}
		});
		select_Type.change(function(){
			$val=$(this).val();	
			if($val==4){
				$('#Picture').show();
				$('.whatsapp_tips').hide();
			}else if($val==5){
				$('.whatsapp_tips').show();
				$('#Picture').hide();
			}else{
				$('.whatsapp_tips').hide();
				$('#Picture').hide();	
			}
		});
		/* 图片上传 */
		var callback="not_div_mask=1;";
		$('#PicUpload, .upload_pic .edit').on('click', function(){frame_obj.photo_choice_init('PicUpload', '.box_chat_edit input[name=PicPath]', 'PicDetail', '', 1, '', callback);});
		$('.upload_pic .del').on('click', function(){
			$('#PicDetail').children('a').remove();
			$('#PicDetail').children('.upload_btn').show();
			$('.box_chat_edit input[name=PicPath]').val('');
		});
		$('.box_chat_edit form').submit(function(){return false;});
		//提交弹出框
		frame_obj.submit_form_init($('.box_chat_edit form'), '');
		
		$('#chat .r_con_table tbody').dragsort({
			dragSelector:'tr',
			dragSelectorExclude:'a, td[data!=move_myorder]',
			placeHolderTemplate:'<tr class="placeHolder"></tr>',
			scrollSpeed:5,
			dragEnd:function(){
				var data=$(this).parent().children('tr').map(function(){
					return $(this).attr('cid');
				}).get();
				$.get('?', {do_action:'set.chat_my_order', sort_order:data.join('|')});
			}
		});
		
	},
	
	/**************************************************平台授权(start)**************************************************/
	authorization_init:function(){
		//添加授权弹出框
		var box_authorization_add = $('.box_authorization_add');
		$('.r_nav').on('click', '.add', function(){//添加
			frame_obj.pop_form(box_authorization_add);
			$('.r_con_form input', box_authorization_add).val('').removeAttr('style');
			$('.r_con_form select', box_authorization_add).removeAttr('style').find('option').removeAttr('selected').first().attr('selected', 'selected');
			$('.box_authorization_add input[name=do_action]').val('set.authorization_add');
		});

		//删除授权
		$('#authorization').on('click', '.del', function (){
			if(!confirm(lang_obj.global.del_confirm)){return false;}
			
			var $this=$(this),
				$AId=$this.parent().parent().attr('aid');
			
			$.post('./', 'do_action=set.authorization_del&AId='+$AId, function(data){
				if(data.ret==1){
					$this.parent().parent().remove();
					return false;
				}
			},'json');
		});
	},
	
	open_init:function(){//开放接口设置
		function set_open_api(){
			$.post('./', 'do_action=set.set_open_api', function(data){
				if(data.ret==1){
					if(data.msg.jump==1){
						window.top.location=window.top.location.href;
					}else{
						$('#authorization .open_api .appkey').text(data.msg.appkey);
					}
				}else{
					global_obj.win_alert('操作失败！');
				}
			},'json');
		}
		
		$('#authorization .open_api').delegate('.enable', 'click', function(){
			if($(this).hasClass('add')){
				set_open_api();
			}else{
				if(confirm(lang_obj.manage.set.open_api_refresh)) set_open_api();
			}
		});
		
		$('#authorization .open_api').delegate('.del', 'click', function(){
			if(!confirm(lang_obj.global.del_confirm)){return false;}
			
			$.post('./', 'do_action=set.del_open_api', function(data){
				if(data.ret==1){
					window.top.location=window.top.location.href;
				}
			},'json');
		});
	},
	
	aliexpress_init:function(){//速卖通授权部分
		//添加授权提交
		$('.box_authorization_add form').submit(function(){
			var $this=$(this),
				$Name=$this.find('input[name=Name]').val();
			if(global_obj.check_form($this.find('*[notnull]'), $this.find('*[format]'), 1)){return false;};
			
			var wi = window.open('about:blank', '_blank');
			$.post('./', 'do_action=set.authhz_url&Name='+$Name+'&d=aliexpress', function(data){
				if(data.ret==1){
					wi.location.href=data.msg.url;
					return false;
				}
			},'json');
			return false;
		});
		
		//店铺重新授权
		$('#authorization').on('click', '.refresh', function (){
			var $account=$(this).parent().parent().attr('account');
			
			var wi = window.open('about:blank', '_blank');
			$.post('./', 'do_action=set.authhz_url&Account='+$account+'&d=aliexpress', function(data){
				if(data.ret==1){
					wi.location.href=data.msg.url;
					return false;
				}
			},'json');
			return false;
		});
		
		//编辑店铺名称
		var box_authorization_edit = $('.box_authorization_edit');
		$('#authorization').on('click', '.mod', function (){//修改
			var AId=$(this).parent().parent().attr('aid'),
				Name=$(this).parent().siblings('td.name').text();
				
			frame_obj.pop_form(box_authorization_edit);
			$('input[type=submit]', box_authorization_edit).attr('disabled', false);
			$('input[name=Name]', box_authorization_edit).val(Name);
			$('input[name=AId]', box_authorization_edit).val(AId);
			$('input[name=d]', box_authorization_edit).val('aliexpress');
			$('input[name=do_action]', box_authorization_edit).val('set.authorization_edit');
		});
		$('.box_authorization_edit form').submit(function(){
			var $this=$(this);
			var $Name=$this.find('input[name=Name]').val(),
				$AId=$this.find('input[name=AId]').val();
			
			if(global_obj.check_form($this.find('*[notnull]'), $this.find('*[format]'), 1)){return false;};
	
			$.post('./', 'do_action=set.authorization_edit&Name='+$Name+'&AId='+$AId+'&d=aliexpress', function(data){
				if(data.ret==1){
					var obj=$('#authorization').find('tr[aid='+data.msg.AId+']');
					obj.find('td.name').text(data.msg.Name);
					
					frame_obj.pop_form($('.box_authorization_edit'), 1);
				}else{
					global_obj.win_alert(data.msg);
				}
			},'json');
			
			return false;
		});
	},
	
	amazon_init:function(){//亚马逊授权
		$('.pop_form form select[name=MarkectPlace]').change(function(){
			if($(this).val())
				$(this).siblings('a').attr({'href':$(this).find('option:selected').attr('data-url'),'target':'_blank'});
			else
				$(this).siblings('a').attr('href','javascript:;').removeAttr('target');
		});
		$('.box_authorization_add form').submit(function(){
			if(global_obj.check_form($(this).find('*[notnull]'), $(this).find('*[format]'), 1)){return false;};
			
			$(this).find('input:submit').attr('disabled', 'disabled');
			$.post('./', $(this).serialize(), function(data){
				$('.box_authorization_add form input:submit').removeAttr('disabled');
				if(data.ret==1){
					window.location.href=window.location.href;
				}else{
					alert(data.msg);
				}
			},'json');
			return false;
		});
		
		//店铺重新授权
		$('#authorization').on('click', '.refresh', function (){
			var $account=$(this).parent().parent().attr('account');
			
			$.post('./', 'do_action=set.authhz_url&Account='+$account+'&d=amazon', function(data){
				if(data.ret==1){
					window.location.href=window.location.href;
				}
			},'json');
			return false;
		});
		
		
		//编辑店铺名称
		var box_authorization_edit = $('.box_authorization_edit');
		$('#authorization').on('click', '.mod,.refresh', function (){//修改
			var AId=$(this).parent().parent().attr('aid'),
				Name=$(this).parent().siblings('td.name').text(),
				account=jQuery.parseJSON(global_obj.htmlspecialchars_decode($(this).parent().parent().attr('account')));
				
			frame_obj.pop_form(box_authorization_edit);
			$('input[type=submit]', box_authorization_edit).attr('disabled', false);
			$('input[name=Name]', box_authorization_edit).val(Name);
			$('select[name=MarkectPlace]', box_authorization_edit).find('option[value='+account.MarkectPlace+']').attr('selected', 'selected');
			$('input[name=MerchantId]', box_authorization_edit).val(account.MerchantId).addClass('bg_gray');
			$('input[name=AWSAccessKeyId]', box_authorization_edit).val(account.AWSAccessKeyId).addClass('bg_gray');
			$('input[name=SecretKey]', box_authorization_edit).val(account.SecretKey).addClass('bg_gray');
			$('input[name=AId]', box_authorization_edit).val(AId);
			$('input[name=d]', box_authorization_edit).val('amazon');
			$('input[name=do_action]', box_authorization_edit).val('set.authorization_edit');
			
			var method='';
			if($(this).hasClass('refresh')) method='refresh';
			$('input[name=method]', box_authorization_edit).val(method);
			
			var url=$('select[name=MarkectPlace]', box_authorization_edit).find('option[value='+account.MarkectPlace+']').attr('data-url');
			$('a.amazon_url', box_authorization_edit).attr({'href':url, 'target':'_blank'});
		});
		$('.box_authorization_edit form').submit(function(){
			var $this=$(this),
				$Name=$this.find('input[name=Name]').val(),
				$MarkectPlace=$this.find('select[name=MarkectPlace]').val(),
				$AId=$this.find('input[name=AId]').val();
			
			if(global_obj.check_form($this.find('*[notnull]'), $this.find('*[format]'), 1)){return false;};
	
			$.post('./', $(this).serialize(), function(data){
				if(data.ret==1){
					var obj=$('#authorization').find('tr[aid='+data.msg.AId+']');
					obj.find('td.name').text(data.msg.Name);
					data.msg.token && obj.attr('account', data.msg.token);
					
					frame_obj.pop_form($('.box_authorization_edit'), 1);
				}else{
					global_obj.win_alert(data.msg, '', '', 1);
				}
			},'json');
			
			return false;
		});
	}
	/**************************************************平台授权(end)**************************************************/
}