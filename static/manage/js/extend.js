/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

var extend_obj={
	seo_global:{
		del_action:'',
		order_action:'',
		init:function(){
			frame_obj.del_init($('#seo .r_con_table')); //删除事件
			frame_obj.select_all($('#seo .r_con_table input[name=select_all]'), $('#seo .r_con_table input[name=select]')); //批量操作
			/* 批量删除 */
			frame_obj.del_bat($('.r_nav .del'), $('#seo .r_con_table input[name=select]'), function(id_list){
				var $this=$(this);
				global_obj.win_alert(lang_obj.global.del_confirm, function(){
					$.get('?', {do_action:extend_obj.seo_global.del_action, group_id:id_list}, function(data){
						if(data.ret==1){
							window.location.reload();
						}
					}, 'json');
				}, 'confirm');
				return false;
			});
		}
	},
	
	seo_link_init:function(){
		extend_obj.seo_global.del_action='extend.seo_link_del_bat';
		extend_obj.seo_global.init();
	},
	
	seo_link_edit_init:function(){
		frame_obj.submit_form_init($('#edit_form'), './?m=extend&a=seo&d=link');
	},
	
	seo_meta_init:function(){
		$('.meta_box').on('click', '.meta_head', function(){
			var $obj=$(this).next('.meta_body');
			if($obj.is(':hidden')){
				$(this).addClass('current');
				$obj.slideDown();
			}else{
				$(this).removeClass('current');
				$obj.slideUp();
			}
		}).on('click', '.more', function(){
			window.location=$(this).attr('href');
			return false;
		});
		
		/* 编辑弹出框 */
		$('.r_con_table .edit').on('click', function(){
			var $id=parseInt($(this).attr('data-id')),
				$type=$(this).attr('data-type'),
				$title=$(this).attr('data-title'),
				$obj=$('.box_seo_edit'),
				$data=$.evalJSON($(this).parents('tr').attr('data')),
				$str;
			frame_obj.pop_form($obj);
			frame_obj.rows_input();
			for(k in ueeshop_config.language){
				$str=ueeshop_config.language[k];
				$('#edit_form input[name=SeoTitle_'+$str+']').val($data['SeoTitle_'+$str]?global_obj.htmlspecialchars_decode($data['SeoTitle_'+$str]):''); //标题
				$('#edit_form input[name=SeoKeyword_'+$str+']').val($data['SeoKeyword_'+$str]?global_obj.htmlspecialchars_decode($data['SeoKeyword_'+$str]):''); //关键词
				$('#edit_form textarea[name=SeoDescription_'+$str+']').val($data['SeoDescription_'+$str]?global_obj.htmlspecialchars_decode($data['SeoDescription_'+$str]):''); //描述
			}
			$('#edit_form input[name=MId]').val($id); //id
			$('#edit_form input[name=Type]').val($type); //type
			$('#edit_form input.hide_name').attr('name', $data['Column']).val($id); //Column
			$title && $('.box_seo_edit .t>h1>span').text($title); //编辑框标题
			return false;
		});
		/* 提交 */
		frame_obj.submit_form_init($('#edit_form'), '', '', '', function(data){
			if(data.ret==1){
				window.location.reload();
			}else{
				global_obj.win_alert(data.msg, '', '', 1);
			}
		});
	},
	
	seo_third_init:function(){
		extend_obj.seo_global.del_action='extend.seo_third_del_bat';
		extend_obj.seo_global.order_action='extend.seo_third_my_order';
		extend_obj.seo_global.init();
		
		$('#seo .r_con_table tbody').dragsort({
			dragSelector:'tr',
			dragSelectorExclude:'a, td[data!=move_myorder]',
			placeHolderTemplate:'<tr class="placeHolder"></tr>',
			scrollSpeed:5,
			dragEnd:function(){
				var data=$(this).parent().children('tr').map(function(){
					return $(this).attr('tid');
				}).get();
				$.get('?', {do_action:'extend.seo_third_order', sort_order:data.join('|')});
			}
		});
	},
	
	seo_third_edit_init:function(){
		frame_obj.switchery_checkbox();
		frame_obj.submit_form_init($('#edit_form'), './?m=extend&a=seo&d=third');
	},
	
	seo_sitemap_edit_init:function(){
		frame_obj.submit_form_init($('#edit_form'), './?m=extend&a=seo&d=sitemap');
	},
	
	blog_global:{
		del_action:'',
		order_action:'',
		init:function(){
			frame_obj.del_init($('#blog .r_con_table')); //删除事件
			frame_obj.select_all($('#blog .r_con_table input[name=select_all]'), $('#blog .r_con_table input[name=select]')); //批量操作
			/* 批量删除 */
			frame_obj.del_bat($('.r_nav .del'), $('#blog .r_con_table input[name=select]'), function(id_list){
				var $this=$(this);
				global_obj.win_alert(lang_obj.global.del_confirm, function(){
					$.get('?', {do_action:extend_obj.blog_global.del_action, group_id:id_list}, function(data){
						if(data.ret==1){
							window.location.reload();
						}
					}, 'json');
				}, 'confirm');
				return false;
			});
			/* 批量排序 */
			$('#blog .r_con_table .myorder_select').on('dblclick', function(){
				var $obj=$(this),
					$number=$obj.attr('data-num'),
					$AId=$obj.parents('tr').find('td:eq(0)>input').val(),
					$mHtml=$obj.html(),
					$sHtml=$('#myorder_select_hide').html(),
					$val;
				$obj.html($sHtml+'<span style="display:none;">'+$mHtml+'</span>');
				$number && $obj.find('select').val($number).focus();
				$obj.find('select').on('blur', function(){
					$val=$(this).val();
					if($val!=$number){
						$.post('?', 'do_action='+extend_obj.blog_global.order_action+'&Id='+$AId+'&Number='+$(this).val(), function(data){
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
		}
	},
	
	blog_set_init:function(){
		/* 添加导航栏目 */
		$('#edit_form').on('click', '.addNav', function (){
			var container=$('.blog_nav');
			var name_lang=container.attr('data-name');
			var link_lang=container.attr('data-link');
			container.append('<div>'+name_lang+':<input type="text" name="name[]" class="form_input" value="" size="10" maxlength="30" /> '+link_lang+':<input type="text" name="link[]" class="form_input" value="" size="30" max="150" /><a href="javascript:void(0);"><img hspace="5" src="/static/ico/del.png"></a><div class="blank6"></div></div>');
		});
		/* 移除导航栏目 */
		$('.blog_nav').on('click', 'div a', function (){
			$(this).parent().remove();
		});
		/* 广告图上传 */
		$('#AdUpload, .upload_ad .edit').on('click', function(){frame_obj.photo_choice_init('AdUpload', 'form input[name=Banner]', 'AdDetail', '', 1);});
		if($('form input[name=Banner]').attr('save')==1){
			$('#AdDetail').append(frame_obj.upload_img_detail($('form input[name=Banner]').val())).children('.upload_btn').hide();
		}
		$('.upload_ad .del').on('click', function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.ajax({
					url:'./?do_action=action.file_del&PicPath='+$this.prev().attr('href'),
					success:function(){
						$('#AdDetail').children('a').remove();
						$('#AdDetail').children('.upload_btn').show();
						$('form input[name=Banner]').val('');
					}
				});
			}, 'confirm');
			return false;
		});
		frame_obj.submit_form_init($('#edit_form'), './?m=extend&a=blog&d=set');
	},
	
	blog_init:function(){
		extend_obj.blog_global.del_action='extend.blog_del_bat';
		extend_obj.blog_global.order_action='extend.blog_edit_myorder';
		extend_obj.blog_global.init();
	},
	
	blog_edit_init:function(){
		/* 图片上传 */
		$('#PicUpload').on('click', function(){
			frame_obj.photo_choice_init('PicUpload', '#edit_form input[name=PicPath]', 'PicDetail', 'info', 1);
		});
		$('#PicUpload, .upload_pic .edit').on('click', function(){frame_obj.photo_choice_init('PicUpload', '#edit_form input[name=PicPath]', 'PicDetail', 'blog', 1);});
		$('#PicDetail').html(frame_obj.upload_img_detail($('#edit_form input[name=PicPath]').val()));
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
		$('#edit_form .choice_btn').click(function(){
			var $this=$(this);
			if($this.children('input').is(':checked')){
				$this.removeClass('current');
				$this.children('input').attr('checked', false);
			}else{
				$this.addClass('current');
				$this.children('input').attr('checked', true);
			}
		});
		$('#edit_form').on('click', '.open', function(){
			if($(this).hasClass('close')){
				$(this).removeClass('close').text(lang_obj.global.open);
				$('.seo_hide').slideUp(300);
			}else{
				$(this).addClass('close').text(lang_obj.global.pack_up);
				$('.seo_hide').slideDown(300);
			}
		});
		frame_obj.submit_form_init($('#edit_form'), './?m=extend&a=blog&d=blog');
	},
	
	blog_category_init:function(){
		extend_obj.blog_global.del_action='extend.blog_category_del_bat';
		extend_obj.blog_global.order_action='extend.blog_category_my_order';
		extend_obj.blog_global.init();
		
		$('#blog .r_con_table tbody').dragsort({
			dragSelector:'tr',
			dragSelectorExclude:'a, td[data!=move_myorder]',
			placeHolderTemplate:'<tr class="placeHolder"></tr>',
			scrollSpeed:5,
			dragEnd:function(){
				var data=$(this).parent().children('tr').map(function(){
					return $(this).attr('cateid');
				}).get();
				$.get('?', {do_action:'extend.blog_category_order', sort_order:data.join('|')});
			}
		});
	},
	
	blog_category_edit_init:function(){
		frame_obj.submit_form_init($('#edit_form'), './?m=extend&a=blog&d=category');
	},
	
	blog_review_init:function(){
		extend_obj.blog_global.del_action='extend.blog_review_del_bat';
		extend_obj.blog_global.init();
	},
	
	blog_review_reply_init:function(){
		frame_obj.submit_form_init($('#edit_form'), './?m=extend&a=blog&d=review');
	},
	
	translate_init:function (){
		$('.r_nav').on('click', '.switchery', function(){
			var $this=$(this),
				$key=0;
			if(!$this.hasClass('checked')){
				$key=1
				$this.addClass('checked');
			}else{
				$this.removeClass('checked');
			}
			$.post('?', 'do_action=extend.translate_set&key='+$key, function(data){
				if(data.ret!=1){
					global_obj.win_alert(lang_obj.global.set_error);
				}
			}, 'json');
		});
		
		$('.r_con_table').on('click', '.used_checkbox .switchery', function(){
			var $this=$(this),
				$tr=$this.parents('tr'),
				$key=0;
			if(!$this.hasClass('no_drop')){
				if(!$this.hasClass('checked')){
					$key=1;
					$this.addClass('checked');
				}else{
					$this.removeClass('checked');
				}
				$.post('?', 'do_action=extend.translate_lang_set&lang='+$tr.attr('lang')+'&key='+$key, function(data){
					if(data.ret!=1){
						global_obj.win_alert(data.msg);
					}
				}, 'json');
			}
		});
	},
	
	analytics_set_init:function(){
		frame_obj.submit_form_init($('#edit_form'), './?m=extend&a=analytics&d=set');
	},
	
	search_init:function(){
		frame_obj.del_init($('#search .r_con_table'));
		frame_obj.select_all($('.r_con_wrap input[name=select_all]'), $('.r_con_wrap input[name=select]')); //批量操作
		frame_obj.del_bat($('.r_nav .del'), $('#search .r_con_table input[name=select]'), function(id_list){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('?', {do_action:'extend.search_del_bat', group_sid:id_list}, function(data){
					if(data.ret==1){
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
	},
	
	search_edit_init:function(){
		frame_obj.submit_form_init($('#edit_form'), './?m=extend&a=search');
	},
	
	search_logs_init:function(){
		frame_obj.del_init($('#search_logs_del_bat .r_con_table'));
		frame_obj.select_all($('.r_con_wrap input[name=select_all]'), $('.r_con_wrap input[name=select]')); //批量操作
		frame_obj.del_bat($('.r_nav .del'), $('#search_logs .r_con_table input[name=select]'), function(id_list){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('?', {do_action:'extend.search_logs_del_bat', group_sid:id_list}, function(data){
					if(data.ret==1){
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
	}
}