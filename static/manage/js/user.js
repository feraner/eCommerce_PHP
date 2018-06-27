/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

var user_obj={
	user_global:{
		choice:function(){	//会员选择器
			var bindCheck=function(obj, type){
				var $this=obj.parent(),
					value=$this.children('input').val(),
					UserIdVal=$('#edit_form input[name=UserIdStr]').val();
				if(type==false || (!type && $this.hasClass('current'))){
					$this.removeClass('current');
					$this.children('input').attr('checked', false);
					if(global_obj.in_array(value, UserIdVal.split('|'))){
						$('#edit_form input[name=UserIdStr]').val(UserIdVal.replace('|'+value+'|', '|'));
					}
				}else{
					$this.addClass('current');
					$this.children('input').attr('checked', true);
					if(!global_obj.in_array(value, UserIdVal.split('|'))){
						$('#edit_form input[name=UserIdStr]').val(UserIdVal+value+'|');
					}
				}
			}
			var checkAll=function(){
				if($('.user_list input:checkbox').size()==$('.user_list input:checkbox:checked').size()){
					$('#all_user').attr('checked', true);
				}else{
					$('#all_user').attr('checked', false);
				}
			}
			var loadAction=function(type){
				var defaultType=parseInt($('#edit_form input[name=UserIdStr]').attr('data'));
				type==1 && $('#edit_form input[name=UserIdStr]').val()=='|' && $('#edit_form input[name=UserIdStr]').val('|');
				$('.user_list a, .user_list input:checkbox').off().on('click', function(){
					bindCheck($(this));
					checkAll();
				});
				$('.user_list a>span').off().on('click', function(){
					window.location=$(this).attr('data');
				});
				/* 多选事件 （检查触发点击当前已选择的会员等级下的会员） */
				$('.user_menu .choice_btn').off().on('click', function(){
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
					$('.user_list li[status='+Num+'] a').each(function(){
						bindCheck($(this), checked);
					});
					checkAll();
				});
				/* 单选事件 （全选） */
				$('.user_choice .all_btn').off().on('click', function(){
					var inputBox=$(this).children('input');
					if($(this).hasClass('current')){
						checked=false;
						$(this).removeClass('current');
					}else{
						checked=true;
						$(this).addClass('current');
					}
					inputBox.attr('checked', checked);
					$('.user_list li a').each(function(){
						bindCheck($(this), checked);
					});
				});
				/* 单选事件 （导出所有会员） */
				$('.user_choice .explode_all_btn').off().on('click', function(){
					var inputBox=$(this).children('input');
					if($(this).hasClass('current')){
						checked=false;
						$(this).removeClass('current');
					}else{
						checked=true;
						$(this).addClass('current');
					}
					inputBox.attr('checked', checked);
				});
				$('.clear_check').off().on('click', function(){	//清空所有勾选选项
					global_obj.win_alert(lang_obj.global.del_confirm, function(){
						$('.user_menu input.level_check, #all_user, .user_list input:checkbox').attr('checked', false);
						$('.user_list li').removeClass('current');
						$('#edit_form input[name=UserIdStr]').val('|');
					}, 'confirm');
				});
				$('.user_menu .choice_btn.current').each(function(){	//检查触发点击当前已选择的会员等级下的会员
					var Num=$(this).children('input').val();
					$('.user_list li[status='+Num+'] a').each(function(){
						bindCheck($(this), true);
					});
				});
				$('.user_list input:checkbox').each(function(){	//检查当前页所有会员是否已勾选
					var value=$(this).val(),
						UserIdVal=$('#edit_form input[name=UserIdStr]').val();
					if(global_obj.in_array(value, UserIdVal.split('|'))){
						$(this).attr('checked', true).parent().addClass('current');
					}
				});
				$('.r_con_wrap .turn_page').off().on('click', 'a', function(){
					$.ajax({
						type:'GET',
						url:$(this).attr('href'),
						async:false,
						success:function(data){
							$('#list_box').html($(data).find('#list_box').html());
							loadAction(0);
						}
					});
					return false;
				});
				checkAll();
			}
			//loadAction(1);
			var defaultType=parseInt($('#edit_form input[name=UserIdStr]').attr('data'))?0:1;
			loadAction(defaultType);
			$('#edit_form .sub_btn').off().on('click', function(){
				$.ajax({
					type:'GET',
					url:'?m=user&a=inbox&d=others_edit&Type=1&Name='+$('input[name=Name]').val(),
					async:false,
					success:function(data){
						$('#list_box').html($(data).find('#list_box').html());
						loadAction(1);
					}
				});
				return false;
			});
			/*
			$('.user_choice input:checkbox').attr('checked', false);
			*/
		}
	},
	
	user_init:function(){
		frame_obj.del_init($('#user .r_con_table'));
		frame_obj.select_all($('.r_con_wrap input[name=select_all]'), $('.r_con_wrap input[name=select]')); //批量操作
		frame_obj.del_bat($('.r_nav .del'), $('#user .r_con_table input[name=select]'), function(id_list){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('?', {do_action:'user.user_del_bat', group_userid:id_list}, function(data){
					if(data.ret==1){
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
		
		frame_obj.del_bat($('.r_nav .bat_close'), $('#user .r_con_table input[name=select]'), function(id_list){
			window.location='./?m=user&a=user&d=batch_edit&userid_list='+id_list;
		}, lang_obj.global.dat_select);
		
		frame_obj.select_all($('.r_nav input[name=custom_all]'), $('.r_nav input[class=custom_list][disabled!=disabled]')); //批量操作
		frame_obj.submit_form_init($('.r_nav .ico form'), './?m=user&a=user');
		$('#user .r_con_table .sales_select').on('dblclick', function(){
			var $obj=$(this),
				$salesid=$obj.attr('data-id'),
				$UserId=$obj.parents('tr').find('td:eq(0)>input').val(),
				$mHtml=$obj.html(),
				$sHtml=$('#sales_select_hide').html(),
				$val;
			$obj.html($sHtml+'<span style="display:none;">'+$mHtml+'</span>');
			$salesid && $obj.find('select').val($salesid).focus();
			$obj.find('select').on('blur', function(){
				$val=$(this).val();
				if($val && $val!=$salesid){
					$.post('?', 'do_action=user.user_edit_sales&UserId='+$UserId+'&SalesId='+$(this).val(), function(data){
						if(data.ret==1){
							$obj.html(data.msg);
							$obj.attr('data-id', $val);
						}
					}, 'json');
				}else{
					$obj.html($obj.find('span').html());
				}
			});
		});
	},
	
	user_add_init:function(){
		frame_obj.submit_form_init($('#edit_form'), './?m=user&a=user');
	},
	
	user_explode_init:function(){
		user_obj.user_global.choice();
		frame_obj.submit_form_init($('#edit_form'), '', '', '', function(data){
			if(data.ret==2){
				$('#explode_progress').append(data.msg[1]);
				$('#edit_form input[name=Number]').val(data.msg[0]);
				$('#edit_form .submit_btn').click();
			}else if(data.ret==1){
				$('#edit_form input[name=Number]').val(0);
				window.location='./?do_action=user.user_explode_down&Status=ok';
			}else{
				$('#edit_form input[name=Number]').val(0);
				global_obj.win_alert(data.msg[0]);
				$('#edit_form :submit').removeAttr('disabled');
			}
		});
	},
	
	user_base_edit_init:function(){
		frame_obj.switchery_checkbox();
		frame_obj.submit_form_init($('#edit_form'));
	},
	
	user_password_edit_init:function(){
		frame_obj.submit_form_init($('#edit_form'));
	},
	
	level_init:function(){
		frame_obj.del_init($('#level .r_con_table'));
	},
	
	level_edit_init:function(){
		/* 会员等级图标上传 */
		$('#PicUpload, .upload_pic .edit').on('click', function(){frame_obj.photo_choice_init('PicUpload', 'form input[name=PicPath]', 'PicDetail', '', 1);});
		$('#PicDetail').html(frame_obj.upload_img_detail($('form input[name=PicPath]').val(), 1));
		$('#PicDetail span').on('click', function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.ajax({
					url:'./?do_action=action.file_del&PicPath='+$this.prev().attr('href'),
					success:function(){
						$('#PicDetail').html('');
						$('form input[name=PicPath]').val('');
					}
				});
			}, 'confirm');
			return false;
		});
		frame_obj.switchery_checkbox();
		frame_obj.submit_form_init($('#edit_form'), './?m=user&a=level');
	},
	
	reg_set_init:function(){
		frame_obj.del_init($('#reg_set .r_con_table'));
		$('#reg_set .switchery[field]').click(function(){
			var o=$(this);
			if(o.attr('field').indexOf('NotNull')!=-1){
				var notnull_obj=o.attr('field').replace('NotNull', '');
				if($('#reg_set .switchery[field='+notnull_obj+']').attr('status')==0){return false;}
			}
			$.get('?', 'do_action=user.reg_set&field='+o.attr('field')+'&status='+o.attr('status'), function(data){
				if(data.ret==1){
					var notnull_obj=$('#reg_set .switchery[field='+o.attr('field')+'NotNull]');
					if(o.attr('status')==0){
						o.attr('status', 1).addClass('checked');
						if(notnull_obj.size()){
							notnull_obj.removeClass('no_drop');
						}
					}else{
						o.attr('status', 0).removeClass('checked');
						if(notnull_obj.size()){
							notnull_obj.attr('status', 0).addClass('no_drop').removeClass('checked');
						}
					}
				}else{
					global_obj.win_alert(lang_obj.global.set_error);
				}
			}, 'json');
		});
	},
	
	reg_set_edit_init:function(type_id){
		var option=$('.row_option');
		type_id=parseInt(type_id);
		if(type_id==1){
			option.show();
		}else{
			option.hide();
		}
		$('#type_select').change(function(){
			$(this).val()==1?$('.row_option').show():$('.row_option').hide();
		});
		frame_obj.submit_form_init($('#edit_form'), './?m=user&a=reg_set');
	},
	
	inbox_inbox_init:function(){
		frame_obj.del_init($('#inbox .r_con_table'));
		frame_obj.select_all($('.r_con_wrap input[name=select_all]'), $('.r_con_wrap input[name=select]')); //批量操作
		frame_obj.del_bat($('.r_nav .del'), $('#inbox .r_con_table input[name=select]'), function(id_list){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('?', {do_action:'user.inbox_del_bat', group_id:id_list}, function(data){
					if(data.ret==1){
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
	},
	
	inbox_edit_init:function(){
		user_obj.user_global.choice();
		frame_obj.submit_form_init($('#edit_form'), './?m=user&a=inbox&d='+$('#edit_form input[name=back]').val());
		/* 文章图片上传 */
		$('#PicUpload, .upload_pic .edit').on('click', function(){frame_obj.photo_choice_init('PicUpload', '#edit_form input[name=PicPath]', 'PicDetail', 'info', 1);});
		if($('#edit_form input[name=PicPath]').attr('save')==1){
			$('#PicDetail').append(frame_obj.upload_img_detail($('#edit_form input[name=PicPath]').val())).children('.upload_btn').hide();
		}
		$('.upload_pic .del').on('click', function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.ajax({
					url:'./?do_action=action.file_del&PicPath='+$this.prev().attr('href'),
					success:function(){
						$('#PicDetail').children('a').remove();
						$('#PicDetail').children('.upload_btn').show();
						$('#edit_form input[name=PicPath]').val('');
					}
				});
			}, 'confirm');
			return false;
		});
		$('#inbox .r_con_form .rows .light_box_pic').lightBox({module:'manage'});
		document.getElementById('View').scrollIntoView();
	},
	
	message_init:function(){
		frame_obj.del_init($('#message .r_con_table'));
		frame_obj.select_all($('.r_con_wrap input[name=select_all]'), $('.r_con_wrap input[name=select]')); //批量操作
		frame_obj.del_bat($('.r_nav .del'), $('#message .r_con_table input[name=select]'), function(id_list){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('?', {do_action:'user.message_del_bat', group_id:id_list}, function(data){
					if(data.ret==1){
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
	},
	
	message_edit_init:function(){
		frame_obj.submit_form_init($('#edit_form'), './?m=user&a=message');
	},
	
	batch_edit_init:function(){
		frame_obj.switchery_checkbox();
		frame_obj.submit_form_init($('#edit_form'), '','','',function(data){
			global_obj.win_alert(data.msg, function(){
				window.location.href='?m=user&a=user';
			});
		});
	}
}