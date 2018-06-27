/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

var content_obj={	
	page_global:{
		del_action:'',
		order_action:'',
		init:function(){
			frame_obj.del_init($('#page .r_con_table')); //删除事件
			frame_obj.select_all($('#page .r_con_table input[name=select_all]'), $('#page .r_con_table input[name=select]')); //批量操作
			/* 批量删除 */
			frame_obj.del_bat($('.r_nav .del'), $('#page .r_con_table input[name=select]'), function(id_list){
				var $this=$(this);
				global_obj.win_alert(lang_obj.global.del_confirm, function(){
					$.get('?', {do_action:content_obj.page_global.del_action, group_id:id_list}, function(data){
						if(data.ret==1){
							window.location.reload();
						}
					}, 'json');
				}, 'confirm');
				return false;
			});
			/* 排序操作 */
			$('#page .r_con_table .myorder_select').on('dblclick', function(){
				var $obj=$(this),
					$number=$obj.attr('data-num'),
					$Id=$obj.parents('tr').find('td:eq(0)>input').val(),
					$mHtml=$obj.html(),
					$sHtml=$('#myorder_select_hide').html(),
					$val;
				$obj.html($sHtml+'<span style="display:none;">'+$mHtml+'</span>');
				$number && $obj.find('select').val($number).focus();
				$obj.find('select').on('blur', function(){
					$val=$(this).val();
					if($val!=$number){
						$.post('?', 'do_action='+content_obj.page_global.order_action+'&Id='+$Id+'&Number='+$(this).val(), function(data){
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
	
	page_init:function(){
		content_obj.page_global.del_action='content.page_del_bat';
		content_obj.page_global.order_action='content.page_edit_myorder';
		content_obj.page_global.init();
	},
	
	page_edit_init:function(){
		$('#edit_form').on('click', '.open', function(){
			if($(this).hasClass('close')){
				$(this).removeClass('close').text(lang_obj.global.open);
				$('.seo_hide').slideUp(300);
			}else{
				$(this).addClass('close').text(lang_obj.global.pack_up);
				$('.seo_hide').slideDown(300);
			}
		});
		frame_obj.submit_form_init($('#edit_form'), './?m=content&a=page');
	},
	
	page_category_init:function(){
		content_obj.page_global.del_action='content.page_category_del_bat';
		content_obj.page_global.order_action='content.page_category_edit_myorder';
		content_obj.page_global.init();
		
		$('#page .r_con_table tbody').dragsort({
			dragSelector:'tr',
			dragSelectorExclude:'a, td[data!=move_myorder]',
			placeHolderTemplate:'<tr class="placeHolder"></tr>',
			scrollSpeed:5,
			dragEnd:function(){
				var data=$(this).parent().children('tr').map(function(){
					return $(this).attr('cateid');
				}).get();
				$.get('?', {do_action:'content.page_category_order', sort_order:data.join('|')});
			}
		});
	},
	
	page_category_edit_init:function(){
		frame_obj.submit_form_init($('#edit_form'), './?m=content&a=page&d=category');
	},
	
	news_global:{
		del_action:'',
		order_action:'',
		init:function(){
			frame_obj.del_init($('#news .r_con_table')); //删除事件
			frame_obj.select_all($('#news .r_con_table input[name=select_all]'), $('#news .r_con_table input[name=select]')); //批量操作
			/* 批量删除 */
			frame_obj.del_bat($('.r_nav .del'), $('#news .r_con_table input[name=select]'), function(id_list){
				var $this=$(this);
				global_obj.win_alert(lang_obj.global.del_confirm, function(){
					$.get('?', {do_action:content_obj.news_global.del_action, group_id:id_list}, function(data){
						if(data.ret==1){
							window.location.reload();
						}
					}, 'json');
				}, 'confirm');
				return false;
			});
			/* 排序操作 */
			$('#news .r_con_table .myorder_select').on('dblclick', function(){
				var $obj=$(this),
					$number=$obj.attr('data-num'),
					$Id=$obj.parents('tr').find('td:eq(0)>input').val(),
					$mHtml=$obj.html(),
					$sHtml=$('#myorder_select_hide').html(),
					$val;
				$obj.html($sHtml+'<span style="display:none;">'+$mHtml+'</span>');
				$number && $obj.find('select').val($number).focus();
				$obj.find('select').on('blur', function(){
					$val=$(this).val();
					if($val!=$number){
						$.post('?', 'do_action='+content_obj.news_global.order_action+'&Id='+$Id+'&Number='+$(this).val(), function(data){
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
	
	news_init:function(){
		content_obj.news_global.del_action='content.news_del_bat';
		content_obj.news_global.order_action='content.news_edit_myorder';
		content_obj.news_global.init();
	},
	
	news_edit_init:function(){
		content_obj.news_global.init();
		$('#edit_form input[name=EditTime]').daterangepicker({
			singleDatePicker:true,
			timePicker:false,
			format:'YYYY-MM-DD'
		});
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
		frame_obj.submit_form_init($('#edit_form'), './?m=content&a=news');
	},
	
	news_category_init:function(){
		content_obj.news_global.del_action='content.news_category_del_bat';
		content_obj.news_global.order_action='content.news_category_edit_myorder';
		content_obj.news_global.init();
		
		$('#news .attr_list').dragsort({
			dragSelector:'dl',
			dragSelectorExclude:'a, dd[class!=attr_ico]',
			placeHolderTemplate:'<dl class="attr_box placeHolder"></dl>',
			scrollSpeed:5,
			dragEnd:function(){
				var data=$(this).parent().children('dl').map(function(){
					return $(this).attr('cateid');
				}).get();
				$.get('?', {do_action:'content.news_category_order', sort_order:data.join('|')});
			}
		});
		$('#news .attr_box').hover(function(){
			$(this).children('.attr_menu').stop(true, true).animate({'right':0}, 200);
		}, function(){
			$(this).children('.attr_menu').stop(true, true).animate({'right':-51}, 200);
		});
		/* 文章分类添加/编辑弹出框 */
		$('.r_nav .ico .add, .attr_add .add, .r_con_table .edit').on('click', function(){
			var $obj=$('.box_news_edit'),
				$path='';
			frame_obj.pop_form($obj);
			if($(this).attr('class')=='add'){
				$path='&ParentId='+$(this).parents('tr').find('td:eq(0) .va_m').val();
			}
			$.ajax({
				type:'post',
				url:$(this).attr('href')+$path,
				async:false,
				success:function(data){
					$('.box_news_edit .r_con_form').html($(data).find('#news').html());
					$('.box_news_edit .t>h1').text($('.box_news_edit .r_con_form .title').text());
				}
			});
			return false;
		});
		//提交
		frame_obj.submit_form_init($('#edit_form'), './?m=content&a=news&d=category');
	},
	
	set_init:function(){
		$('#set .switchery').click(function(){
			var $this=$(this),
				$key=$this.attr('data')
				$checked_ary=new Object(),
				check=0;
			$('#set .switchery').each(function(){
				$checked_ary[$(this).attr('data')]=($key==$(this).attr('data')?($(this).hasClass('checked')?0:1):($(this).hasClass('checked')?1:0));
			});
			$checked=global_obj.json_decode_data($checked_ary);
			$.post('?', 'do_action=content.set_edit&Checked='+$checked, function(data){
				if(data.ret==1){
					if($this.hasClass('checked')){
						$this.removeClass('checked');
					}else{
						$this.addClass('checked');
					}
				}else{
					global_obj.win_alert(lang_obj.global.set_error);
				}
			}, 'json');
		});
	},
	
	ad_init:function(){
		frame_obj.del_init($('#ad .r_con_table'));
	},
	
	ad_add_init:function(){
		$('#edit_form select[name=AdType]').change(function(e){
			if($(this).val()==0){
				$('#pic_qty').css('display', 'block');
			}else{
				$('#pic_qty').css('display', 'none');
			}
        });
		$('#edit_form select[name=version]').change(function(e) {
			if($(this).val()==1){
				$('#pagetype').show(0);
			}else{
				$('#pagetype').hide(0);
			}
        });
		frame_obj.submit_form_init($('#edit_form'), './?m=content&a=ad');
	},
	
	ad_edit_init:function(){
		/* 广告图图片上传 */
		/*
		$('.multi_img .upload_btn, .pic_btn .edit').on('click', function(){
			var $id=$(this).attr('id'),
				$lang=$(this).attr('lang'),
				$num=$(this).parents('.img').attr('num'),
				piccount=parseInt($(this).attr('data-count'));
			frame_obj.photo_choice_init('PicUpload_'+$num, '.picpath', 'PicDetail_'+$lang+' .img[num;'+$num+']', 'ad', 5, 'do_action=products.products_img_del&Model=products');
		});
		*/
		frame_obj.mouse_click($('.multi_img .upload_btn, .pic_btn .edit'), 'ad', function($this){ //产品颜色图点击事件
			var $id=$this.attr('id'),
				$lang=$this.attr('lang'),
				$num=$this.parents('.img').attr('num'),
				piccount=parseInt($this.attr('data-count'));
			frame_obj.photo_choice_init('PicUpload_'+$num, '.picpath', 'PicDetail_'+$lang+' .img[num;'+$num+']', 'ad', 5, 'do_action=products.products_img_del&Model=products');
		});
		$('.multi_img input.picpath').each(function(){
			if($(this).attr('save')==1){
				$(this).parent().append(frame_obj.upload_img_detail($(this).val())).children('.upload_btn').hide();
			}
		});
		//图片上传 开始
		$('.ad_drag').dragsort({
			dragSelector:'.adpic_row',
			dragSelectorExclude:'.ad_info, .multi_img',
			placeHolderTemplate:'<li class="placeHolder"></li>',
			scrollSpeed:5
		});
		
		frame_obj.mouse_click($('.pic_btn .del'), 'adDel', function($this){ //产品颜色图点击事件
			var $obj=$this.parents('.img'),
				$lang=$obj.attr('lang'),
				$num=parseInt($obj.attr('num')),
				$path=$obj.find('input[name=PicPath_'+$lang+'\\[\\]]').val();
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$('#PicDetail_'+$lang+' dl[num='+$num+'] .preview_pic .upload_btn').show();
				$('#PicDetail_'+$lang+' dl[num='+$num+'] .preview_pic a').remove();
				$('#PicDetail_'+$lang+' dl[num='+$num+'] .preview_pic input:hidden').val('').attr('save', 0);
			}, 'confirm');
		});
		$('#edit_form .del_flash').click(function(e){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				var AId=$this.attr('data-AId');
				var S_FlashPath=$('#edit_form input[name="S_FlashPath"]');
				$.get('?', 'do_action=ad.ad_del_flash&AId='+AId+'&S_FlashPath='+S_FlashPath.val(), function(data){
					if(data.ret==1){
						global_obj.win_alert(data.msg);
						S_FlashPath.val('');
						$('#cur_file').remove();
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
		frame_obj.submit_form_init($('#edit_form'), './?m=content&a=ad');
	},
	
	partner_init:function(){
		frame_obj.del_init($('#partner .r_con_table'));
		frame_obj.select_all($('#partner .r_con_table input[name=select_all]'), $('#partner .r_con_table input[name=select]'));//批量操作
		frame_obj.del_bat($('.r_nav .del'), $('#partner .r_con_table input[name=select]'), function(id_list){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('./?do_action=content.partner_del_bat&group_pid='+id_list, function(data){
					if(data.ret==1){
						window.location='./?m=content&a=partner';
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
		$('.r_con_table').on('click', '.used_checkbox .switchery', function(){//启用或关闭合作伙伴
			var $this=$(this),
				$tr=$this.parents('tr');
			if(!$this.hasClass('checked')){
				var IsUsed=1;
				$this.addClass('checked');
			}else{
				var IsUsed=0;
				$this.removeClass('checked');
			}
			$.post('?', 'do_action=content.partner_used&PId='+$tr.attr('pid')+'&IsUsed='+IsUsed, function(data){
				if(data.ret!=1){
					global_obj.win_alert(lang_obj.global.set_error);
				}
			}, 'json');
		});
	},
	
	partner_edit_init:function(){
		/* 友情连接图片上传 */
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
		frame_obj.switchery_checkbox();
		frame_obj.submit_form_init($('#edit_form'), './?m=content&a=partner');
	}
}