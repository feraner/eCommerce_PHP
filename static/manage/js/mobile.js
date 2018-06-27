/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

var mobile_obj={
	themes_edit_init:function(){
		$('.temp_list .item').hover(function(){
			$(this).children('.info').stop(true, true).slideDown(500);
		},function(){
			$(this).children('.info').stop(true, true).slideUp(500);
		}).children('.img').click(function(){
			if(!$(this).parent().hasClass('current')){
				var $this=$(this);
				global_obj.win_alert(lang_obj.manage.module.sure_module, function(){
					$this.parent().addClass('current').siblings().removeClass('current');
					$.post('?', "do_action=mobile.themes_edit&tpl="+$this.parent().attr('data-tpl'), function(data){
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
	},
	
	list_edit_init:function(){
		$('.temp_list .item').hover(function(){
			$(this).children('.info').stop(true, true).slideDown(500);
		},function(){
			$(this).children('.info').stop(true, true).slideUp(500);
		}).children('.img').click(function(){
			if(!$(this).parent().hasClass('current')){
				var $this=$(this);
				global_obj.win_alert(lang_obj.manage.module.sure_module, function(){
					$this.parent().addClass('current').siblings().removeClass('current');
					$.post('?', "do_action=mobile.products_list_edit&tpl="+$this.parent().attr('data-tpl'), function(data){
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
	},
	
	config_edit_init:function(){
		var btn_preview=$('#btn_preview'),//按钮
			cart_btn_preview=$('#cart_btn_preview');//购物车按钮
		btn_preview.css({'color':$('#btn_color').val(),'background-color':$('#btn_bg').val()});
		cart_btn_preview.css({'color':$('#cart_btn_color').val(),'background-color':$('#cart_btn_bg').val()});
		$('#btn_color').change(function (){
			btn_preview.css('color', '#'+$(this).val());
		});
		$('#btn_bg').change(function (){
			btn_preview.css('background-color', '#'+$(this).val());
		});
		$('#cart_btn_color').change(function (){
			cart_btn_preview.css('color', '#'+$(this).val());
		});
		$('#cart_btn_bg').change(function (){
			cart_btn_preview.css('background-color', '#'+$(this).val());
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
		/* 头部管理 */
		frame_obj.switchery_checkbox();
		var icon_img=$('#edit_form .headicon .img');
		icon_img.css('background-color', $('#head_bg').val());
		$('#head_bg').change(function(){
			icon_img.css('background-color', '#'+$(this).val());
		});
		icon_img.on('click', function(){
			icon_img.removeClass('on');
			$(this).addClass('on');
			$('#edit_form input[name=icon]').val($(this).attr('data-icon'));
		});
		/* 底部管理 */
		var foot_preview=$('#foot_preview');
		foot_preview.css({'background-color':$('#foot_bg').val(), 'color':$('#font_color').val()});
		$('#foot_bg').change(function(){
			foot_preview.css('background-color', '#'+$(this).val());
		});
		$('#font_color').change(function(){
			foot_preview.css('color', '#'+$(this).val());
		});
		$('#addLink').on('click', function(){	//新增导航
            $('#Linkrow').append($('#cus_html').html());
			$('.r_con_wrap').scrollTop(1000000);
        });
		$('#Linkrow').on('click', '.rows .del', function(){	//删除区间输入节点
            var $this=$(this);
			if($('#global_win_alert').size()) return false;
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$this.parent().parent().remove();
			}, 'confirm');
			return false;
        });
		frame_obj.submit_form_init($('#edit_form'), './?m=mobile&d=config');
	}
}