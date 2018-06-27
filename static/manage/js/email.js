/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

var email_obj={
	send_init:function(){
		/******************************** 选择会员分组 Start ********************************/
		$('.user_group').on('click', function(){ //会员分组选择
			//frame_obj.pop_iframe_page_init('./?m=email&d=user_level', 'user_group');
			frame_obj.pop_form($('.box_user_edit'));
		});
		$('#user_level_form .choice_btn').on('click', function(){
			var $this=$(this);
			$('#user_level_form').find('.choice_btn').removeClass('current').children('input').attr('checked', false);
			$this.addClass('current').children('input').attr('checked', true);
			return false;
		});
		$('#user_level_form input[name=submit_button]').on('click', function(){
			var data='';
			$('#user_level_form input[name=User]').each(function(){
				if($(this).get(0).checked){
					data+=$(this).parent().attr('title')+'/'+$(this).prev().text()+"\r\n";
				}
			});
			if(data){
				if($('.MemberToName').attr('type')=='testarea'){
					data=parent.$('#edit_form .MemberToName').val()+data;
				}
				parent.$('#edit_form .MemberToName').val(data);
				//parent.frame_obj.pop_contents_close_init(parent.$('#user_group'), 1);
				frame_obj.pop_form($('.box_user_edit'), 1);
			}else{
				global_obj.win_alert(lang_obj.manage.email.not_user, '', '', 0);
			}
			return false;
		});
		/******************************** 选择会员分组 End ********************************/
		frame_obj.file_upload($('#TxtUpload'), '', '', 'file_upload', true, 1, function(filepath){
			$.post('?', {do_action:"email.send_import", FilePath:filepath}, function(data){
				if(data.ret==1){
					$('.MemberToName').html(data.msg);
				}
			}, 'json');
		}, '*.txt');	//导入会员
		$('#email_tpl_form .email_tab .item').click(function(e){	//模板切换
			var index=$('#email_tpl_form .email_tab .item').index(this);
            $(this).addClass('cur').siblings('.item').removeClass('cur');
			$('#email_tpl_form .list').eq(index).css('display', 'block').siblings('.list').css('display', 'none');
			if(index==3){
				$('#email_tpl_form .btn_del').show();
			}else{
				$('#email_tpl_form .btn_del').hide();
			}
        });
		$('#mail_tpl_btn').click(function(){//显示模板选择器
			/*
			frame_obj.pop_contents_init($('#email_tpl_form'));
			var resize=function(){
				$('#email_tpl_form .tpl_list').height($('#email_tpl_form').height()-$('#email_tpl_form .email_tab').outerHeight(true)-$('#email_tpl_form .list_foot').outerHeight(true));
			}
			resize();
			$(window).resize(function(){resize();});
			*/
			frame_obj.pop_form($('.box_tpl_edit'));
        });
		$('#email_tpl_form .list .item .img').click(function(){	//模板选择
			var $obj=$(this).parent();
			if($obj.hasClass('cur')){
				$obj.removeClass('cur');
				$('#email_tpl_form input[name=template]').val('');//清除选择
				$('#email_tpl_form input[name=class]').val('');//清零
			}else{
				$obj.addClass('cur').siblings().removeClass('cur');
				$('#email_tpl_form input[name=template]').val($obj.attr('template'));//选择了那个模板
				$('#email_tpl_form input[name=class]').val($('#email_tpl_form .email_tab .cur').attr('data-class'));//清零
			}
        });
		var close_template=function(){
			$('#email_tpl_form .list .item').removeClass('cur');//清零
			$('#email_tpl_form input[name=template]').val('');//清零
			$('#email_tpl_form input[name=class]').val('');//清零
			//$('#email_tpl_form').css({'display':'none'});
			frame_obj.pop_form($('.box_tpl_edit'), 1);
			$('#div_mask').remove();
		}
		$('#email_tpl_form .btn_del').click(function(){	//删除自定义模板
			var num=$('#email_tpl_form .list:eq(3) .item.cur').attr('template');
			$.get('?do_action=email.customize_del&EId='+num, function(data){
				if(data.ret==1){
					window.location.reload();
				}else{
					global_obj.win_alert(data.msg);
				}
			}, 'json');
		});
		frame_obj.submit_form_init($('#email_tpl_form'), '', function(){
			if($('#email_tpl_form input[name=template]').val()==''){
				close_template();
				global_obj.win_alert(lang_obj.manage.email.not_model);
				return false;
			}
		}, false, function(data){
			if(data.ret==1){
				CKEDITOR.instances['Content'].setData(data.msg);
				CKEDITOR.instances['Content'].updateElement();//更新数据
			}else{
				global_obj.win_alert(lang_obj.manage.email.load_error);
			}
			close_template();
			$('#email_tpl_form input[name=submit_button]').attr('disabled', false);
		});
		frame_obj.submit_form_init($('#edit_form'), './?m=email&d=send');
		
		/* 保存模块弹出框 */
		$('#email .btn_save').on('click', function(){
			var $obj=$('.box_email_edit'),
				$content=CKEDITOR.instances['Content'].getData();
			if($content){
				frame_obj.pop_form($obj);
				$obj.find('textarea[name=Content]').html($content);
			}else{
				global_obj.win_alert(lang_obj.manage.email.not_content);
			}
			return false;
		});
		//提交
		var $obj=$('#customize_form');
		$obj.submit(function(){return false;});
		$obj.find('input:submit').click(function(){
			if(global_obj.check_form($obj.find('*[notnull]'), $obj.find('*[format]'), 1)){return false;};
			$(this).attr('disabled', true);
			$content=$obj.find('textarea[name=Content]').html();
			$.post('?', $obj.serialize()+'&Content='+escape($content), function(data){
				window.location.reload();
				return false;
			}, 'json');
		});
	},
	
	config_init:function(){
		//多选事件
		$('.notice_menu .choice_btn').off().on('click', function(){
			var inputBox=$(this).children('input'),
				Num=inputBox.val(),
				checked;
			if(inputBox.is(':checked')){
				checked=false;
				$(this).removeClass('current');
			}else{
				checked=true;
				$(this).addClass('current');
			}
			inputBox.attr('checked', checked);
		});
		//提交
		frame_obj.submit_form_init($('#edit_form'), './?m=email&d=config');
	},
	
	email_group_init:function(){
		var resize=function(){
			$('#user_group .user_list').height($(window).height()-20-$('.r_nav').outerHeight(true)-$('#user_group .list_foot').outerHeight(true));
		}
		resize();
		$(window).resize(function(){resize();});
		$('.choice_btn').on('click', function(){
			var $this=$(this);
			if(parent.$('#edit_form .MemberToName').hasClass('member_textarea')){
				if($this.hasClass('current')){
					$this.removeClass('current').children('input').attr('checked', false);
				}else{
					$this.addClass('current').children('input').attr('checked', true);
				}
			}else{
				$('#user_level_form').find('.choice_btn').removeClass('current').children('input').attr('checked', false);
				$this.addClass('current').children('input').attr('checked', true);
			}
		});
		$('#button_add').on('click', function(){
			var data='';
			$('#user_level_form input[name=User]').each(function(){
				if($(this).get(0).checked){
					data+=$(this).parent().attr('title')+'/'+$(this).prev().text()+"\r\n";
				}
			});
			if(data){
				if($('.MemberToName').attr('type')=='testarea'){
					data=parent.$('#edit_form .MemberToName').val()+data;
				}
				parent.$('#edit_form .MemberToName').val(data);
				parent.frame_obj.pop_contents_close_init(parent.$('#user_group'), 1);
			}else{
				global_obj.win_alert(lang_obj.manage.email.not_user);
			}
		});
	},
	
	newsletter_init:function(){
		frame_obj.del_init($('#email .r_con_table'));
		
		$('#excel_format').on('click', function(){
			window.location='./?&do_action=email.newsletter_explode';
		});
	},
	
	arrival_init:function(){
		frame_obj.del_init($('#email .r_con_table'));
	},
	
	system_init:function(){//系统邮件模板
		$('#sys_tpl_btn').click(function (){
			frame_obj.pop_form($('.sys_tpl_edit'));
		});
		frame_obj.switchery_checkbox();
		//下次更新删掉8-10
		/*$('#email_tpl_form .list .item .img').click(function(){	//模板选择
			var $obj=$(this).parent();
			if($obj.hasClass('cur')){
				$obj.removeClass('cur');
				$('#email_tpl_form input[name=template]').val('');//清除选择
			}else{
				$obj.addClass('cur').siblings().removeClass('cur');
				$('#email_tpl_form input[name=template]').val($obj.attr('template'));//选择了那个模板
			}
		});
		
		var close_template=function(){
			$('#email_tpl_form .list .item').removeClass('cur');//清零
			$('#email_tpl_form input[name=template]').val('');//清零
			frame_obj.pop_form($('.sys_tpl_edit'), 1);
			$('#div_mask').remove();
		}
		
		frame_obj.submit_form_init($('#email_tpl_form'), '', function(){
			if($('#email_tpl_form input[name=template]').val()==''){
				close_template();
				global_obj.win_alert(lang_obj.manage.email.not_model);
				return false;
			}
		}, false, function(data){
			if(data.ret==1){
				$.each(data.msg.lang, function (i, v){
					$('#edit_form input[name=Title_'+v+']').val(data.msg.Title[v]);
					CKEDITOR.instances['Content_'+v].setData(data.msg.Content[v]);
					CKEDITOR.instances['Content_'+v].updateElement();//更新数据
				});
				
				if(data.msg.IsUsed==1){
					$('#edit_form input[name=IsUsed]').attr('checked', true).parent('.switchery').addClass('checked');
				}else{
					$('#edit_form input[name=IsUsed]').attr('checked', false).parent('.switchery').removeClass('checked');
				}
				$('#edit_form input[name=template]').val(data.msg.template);
				$('.tpl_tips').hide(0);
				$('.r_con_form .rows .input .'+data.msg.template).show(0);
			}else{
				global_obj.win_alert(lang_obj.manage.email.load_error);
			}
			close_template();
			$('#email_tpl_form input[name=submit_button]').attr('disabled', false);
		});	*/
		//模板选择
		$('#template_select').change(function (){
			var template = $(this).val();
			var lang = $(this).attr('lang');
			var lang_ary = new Array();
			lang_ary = lang.split(",");
			if (template!=''){
				$('#template_select').attr('disabled', true);
				$.post('./', {do_action:'email.sys_get_tpl', template:template, lang:lang}, function (data){
					if(data.ret==1){
						$.each(data.msg.lang, function (i, v){
							$('#edit_form input[name=Title_'+v+']').val(data.msg.Title[v]);
							CKEDITOR.instances['Content_'+v].setData(data.msg.Content[v]);
							CKEDITOR.instances['Content_'+v].updateElement();//更新数据
						});
						if(data.msg.IsUsed==1){
							$('#edit_form input[name=IsUsed]').attr('checked', true).parent('.switchery').addClass('checked');
						}else{
							$('#edit_form input[name=IsUsed]').attr('checked', false).parent('.switchery').removeClass('checked');
						}
						$('#edit_form input[name=template]').val(data.msg.template);
						$('.tpl_tips').hide(0);
						$('.r_con_form .rows .input .'+data.msg.template).show(0);
						$('#template_select').attr('disabled', false);
					}else{
						global_obj.win_alert(lang_obj.manage.email.load_error);
						$('#template_select').attr('disabled', false);
					}
				}, 'json');
			}else{
				$('#edit_form input[name=IsUsed]').attr('checked', false).parent('.switchery').removeClass('checked');
				$('#edit_form input[name=template]').val('');
				$('.tpl_tips').hide(0);
				$.each(lang_ary, function (i, v){
					if ($('#Content_'+v).length){
						$('#edit_form input[name=Title_'+v+']').val('');
						CKEDITOR.instances['Content_'+v].setData('');
						CKEDITOR.instances['Content_'+v].updateElement();//更新数据
					}
				});
			}
		});
		
		//提交表单
		frame_obj.submit_form_init($('#edit_form'), '', '', '', function (data){
			if(data.ret==1){
				global_obj.win_alert(data.msg, function (){
					window.location.href='./?m=email&d=system';
				});
			}else{
				$('#edit_form').find('input:submit').attr('disabled', false);
				global_obj.win_alert(data.msg);
			}
		});
	}
}