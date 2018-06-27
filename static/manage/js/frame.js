/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

var frame_obj={
	page_init:function(){
		var resize=function(){
			$(window).width()>=1280?$('body').addClass('w_1200'):$('body').removeClass('w_1200');
			$(window).width()>=1400?$('body').addClass('w_1400'):$('body').removeClass('w_1400');
			$('#main').width($(window).width()>=980?$(window).width():980).height($(window).height()-$('#header').outerHeight());
			$('#main .menu').width($('#main .menu_ico').width()+$('#main .menu_list').width()+$('#main .menu_button').width());
			$('#main .menu_list').height($('#main').height());
			$('#main .menu_ico, #main .menu_list').jScrollPane();
			$('#main .menu_button i').css('marginTop', $('#main .menu_button a').height()/2-6);
			$('#main .righter').width($('#main').width()-$('#main .menu').width());
			/******************** 2017/4/17 sheldon添加 Start ********************/
			$('#main .r_con_wrap').css({width:$(window).width()-$('#main .menu').width()});
			/******************** 2017/4/17 sheldon添加 End ********************/
			$('#main .r_con_wrap').css({height:$('#main').height()-$('#main .r_nav').height()-$('#edit_form .submit_btn_fixed').outerHeight()-20});
			!$('#main .r_nav .ico li').size() && $('#main .r_nav .ico').remove();
			$('#header').width($('#main').width());
			if($('#edit_form').length && ($('#edit_form').height()>$('#main .r_con_wrap').height() || $('.ued_hide').length)){	//提交按钮固定于底部
				$('#edit_form input:submit.btn_ok').parent().parent().addClass('submit_btn_fixed');
				$('#edit_form .submit_btn_fixed').css({width:$('#main').width()-$('#main .menu').width(), left:$('#main .menu').width()});
				$('#main .r_con_wrap').css({height:$('#main').height()-$('#main .r_nav').height()-$('#edit_form .submit_btn_fixed').outerHeight()-20});
			}else{
				$('#edit_form .submit_btn_fixed').removeAttr('style').removeClass('submit_btn_fixed');
			}
			if($('#div_mask').length) $('#div_mask').css({height:$(document).height()});//刷新遮罩层高度
		}
		resize();
		$(window).resize(function(){resize();});
		
		$('#turn_page').size() && $('.r_nav .turn_page').html($('#turn_page').html()) && $('#turn_page').remove();
		$('.r_nav .turn_page').html()=='' && $('.r_nav .turn_page').remove();
		$('#header ul li').on('click', function(){//头部栏目
			$(this).blur();
			var a=$(this).find('a');
			if(a.hasClass('clear_cache')){
				global_obj.win_alert(lang_obj.global.del_confirm, function(){
					$.get('./?do_action=action.file_clear_cache', function(data){
						if(data.ret==1){
							global_obj.win_alert(lang_obj.manage.account.success_cache);
							return false;
						}
					}, 'json');
				}, 'confirm');
				return false;
			}
		});
		$('#main .menu dt').on('click', function(){//左侧子栏目
			var $this=$(this);
			$this.addClass('cur').siblings().removeClass('cur');
			if($this.next('dd').length){
				if($this.next('dd').is(':hidden')){
					$('#main .menu dt div').html('-');
					$this.next().filter('dd').slideDown(function(){
						$this.addClass('cur');
						$('#main .menu_list').jScrollPane();
					});
				}else{
					$this.children('div').html('+');
					$this.next().filter('dd').slideUp(function(){
						$this.removeClass('cur');
						$('#main .menu_list').jScrollPane();
					});
				}
			}
		});
		$('#main .menu_button a').on('click', function(){//左侧子栏目显藏按钮
			$(this).blur();
			if($('#main .menu_list').attr('status')=='off'){
				$(this).children('i').removeClass('show');
				$('#main .menu_list').attr('status', 'on').stop(true, false).animate({'width':130}, 200, function(){
					$('#main .menu_list').jScrollPane();
				});
				$('#main .menu').width($('#main .menu_ico').width()+$('#main .menu_button').width()+130);
				$('#main .righter').width($('#main').width()-$('#main .menu').width());
				/******************** 2017/4/17 sheldon添加 Start ********************/
				$('#main .r_con_wrap').css({width:$(window).width()-$('#main .menu').width()});
				/******************** 2017/4/17 sheldon添加 End ********************/
				$('#edit_form .submit_btn_fixed').css({width:$('#main').width()-$('#main .menu').width(), left:$('#main .menu').width()});
			}else{
				$(this).children('i').addClass('show');
				$('#main .menu_list').attr('status', 'off').stop(true, false).animate({'width':0}, 200, function(){
					$('#main .menu').width($('#main .menu_ico').width()+$('#main .menu_button').width());
					$('#main .righter').width($('#main').width()-$('#main .menu').width());
					/******************** 2017/4/17 sheldon添加 Start ********************/
					$('#main .r_con_wrap').css({width:$(window).width()-$('#main .menu').width()});
					/******************** 2017/4/17 sheldon添加 End ********************/
					$('#edit_form .submit_btn_fixed').css({width:$('#main').width()-$('#main .menu').width(), left:$('#main .menu').width()});
				});
			}
		});
		if($('.r_nav .search_form .ext>div').size()){	//搜索框
			$('.r_nav .search_form .more').click(function(){
				if($('.r_nav .search_form .ext').is(':hidden')){
					$('.r_nav .search_form .ext').show();
					$('.r_nav .search_form form').css('border-radius', '5px 5px 0 0');
					$('.r_nav .search_form .more').addClass('more_up');
				}else{
					$('.r_nav .search_form .ext').hide();
					$('.r_nav .search_form form').css('border-radius', '5px');
					$('.r_nav .search_form .more').removeClass('more_up');
				}
			});
		}else{
			$('.r_nav .search_form .more').remove();
			$('.r_nav .search_form .form_input').addClass('long_form_input');
		}
		$('.tool_tips_ico').each(function(){	//弹出提示
			$(this).html('&nbsp;');
			$('#main .r_con_wrap').tool_tips($(this), {position:'horizontal', html:$(this).attr('content'), width:260});
		});
		$('*[placeholder]').each(function(){
			if(!$(this).val()){
				$(this).val($(this).attr('placeholder')).css('color', '#bbb');
			}
		}).focus(function(){
			if($(this).val()==$(this).attr('placeholder')){
            	$(this).val('').css('color', '#333');
			}
		}).blur(function(){
			if(!$(this).val()){
				$(this).val($(this).attr('placeholder')).css('color', '#bbb');
			}
		});
		$('.tip_ico').hover(function(){
			$(this).append('<span class="tip_ico_txt'+($(this).hasClass('tip_min_ico')?' tip_min_ico_txt':'')+' fadeInUp animate">'+$(this).attr('label')+'<em></em></span>');
		}, function(){
			$(this).removeAttr('style').children('span').remove();
		});
		$('.tip_ico_down').hover(function(){
			$(this).append('<span class="tip_ico_txt_down'+($(this).hasClass('tip_min_ico')?' tip_min_ico_txt_down':'')+' fadeInDown animate">'+$(this).attr('label')+'<em></em></span>');
		}, function(){
			$(this).removeAttr('style').children('span').remove();
		});
		//手机版预览效果
		$('#main .menu .ico_mpreview').on('click', function(){
			if(!$("#mpreview_box").length){
				var html='',
					src=$(this).children('a').attr('src');
				global_obj.div_mask();
				html+='<div id="mpreview_box">';
					html+='<div class="mpreview_main">';
						html+='<a class="mpreview_close" href="javascript:;"></a>';
						html+='<iframe src="'+src+'" frameborder="0" name="mpreview_iframe" id="mpreview_iframe" scrolling="yes"></iframe>';
					html+='</div>';
				html+='</div>';
				$('body').prepend(html);
				$('#mpreview_box').fadeIn().css({left:$(window).width()/2-186});
				
				$("#mpreview_box .mpreview_close").on("click", function(){
					$("#mpreview_box").fadeOut().remove();
					global_obj.div_mask(1);
				});
			}
		});
		//选项卡效果
		$('.tab_box_btn').on('click', function(){
			var $num=$(this).index();
			$(this).addClass('current').siblings().removeClass('current');
			$(this).parent().nextAll('.tab_txt_'+$num).show().siblings('.tab_txt').hide();
		});
		$('.tab_box_row').each(function(){
			$(this).children('.tab_box_btn').eq(0).click();
		});
		frame_obj.rows_input();
	},
	
	rows_input:function(){ //计算多语言表单长度
		$('.r_con_form .rows .input:visible').has('.lang_input').each(function(){
			var o=$(this);
			var input_width=Math.max(o.find('input').outerWidth(), o.find('textarea').outerWidth());
			var title_width=last_width=0;
			o.find('.lang_input b[class!=last]').each(function(){
				var w=$(this).outerWidth();
				w>title_width && (title_width=w);
			});
			title_width+=1;
			o.find('.lang_input b.last').each(function(){
				var w=$(this).outerWidth();
				w>last_width && (last_width=w);
			});
			last_width+=1;
			o.find('.lang_input').width(input_width+title_width+last_width);
			o.find('.lang_input b[class!=last]').width(title_width-(title_width?19:0));
			o.find('.lang_input b.last').width(last_width-(last_width?19:0));
			o.find('.lang_input input, .lang_input textarea').width(o.find('.lang_input').outerWidth()-o.find('.lang_input b[class!=last]').outerWidth()-o.find('.lang_input b.last').outerWidth()-24);
		});
	},
	
	//客服专员和备份时间
	windows_init:function(){
		$("#header").on("click", ".user_service", function(){
			if(!$("#user_service_box").length){
				$.post('?', 'do_action=account.ueeshop_web_get_service_data', function(data){
					if(data.ret==1){
						if(data.msg.trial==1){
							var html='';
							global_obj.div_mask();
							html+='<div id="user_service_box">';
								html+='<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">';
									html+='<thead>';
										html+='<tr>';
											html+='<td width="62" nowrap="nowrap">&nbsp;</td>';
											html+='<td nowrap="nowrap" colspan="2" class="chat_info"><div class="chat_close"><button class="close">X</button></div><div class="chat_head_bg"></div><div class="chat_head"></div><h3 class="service_name">'+data.msg.service.Contacts+'</h3><span class="service_email">'+data.msg.service.Email+'</span></td>';
										html+='</tr>';
									html+='</thead>';
									html+='<tbody>';
										html+='<tr>';
											html+='<td nowrap="nowrap" valign="middle"><i class="chat_icon chat_icon_0"></i></td>';
											html+='<td nowrap="nowrap" width="250">'+lang_obj.manage.frame.support[0]+'</td>';
											html+='<td nowrap="nowrap" width="352" class="service_qq">'+data.msg.service.QQ+'</td>';
										html+='</tr>';
										html+='<tr>';
											html+='<td nowrap="nowrap"><i class="chat_icon chat_icon_1"></i></td>';
											html+='<td nowrap="nowrap">'+lang_obj.manage.frame.support[1]+'</td>';
											html+='<td nowrap="nowrap" class="service_work_time">'+data.msg.service.WorkTime+'</td>';
										html+='</tr>';
										html+='<tr>';
											html+='<td nowrap="nowrap"><i class="chat_icon chat_icon_2"></i></td>';
											html+='<td nowrap="nowrap">'+lang_obj.manage.frame.support[2]+'</td>';
											html+='<td nowrap="nowrap" class="service_phone">'+data.msg.service.Telephone+'</td>';
										html+='</tr>';
										html+='<tr>';
											html+='<td nowrap="nowrap"><i class="chat_icon chat_icon_3"></i></td>';
											html+='<td nowrap="nowrap">'+lang_obj.manage.frame.support[3]+'</td>';
											html+='<td nowrap="nowrap" class="service_wechat">'+data.msg.service.Wechat+'</td>';
										html+='</tr>';
										html+='<tr>';
											html+='<td nowrap="nowrap"><i class="chat_icon chat_icon_4"></i></td>';
											html+='<td nowrap="nowrap">'+lang_obj.manage.frame.support[4]+'</td>';
											html+='<td nowrap="nowrap" class="service_complaint">'+data.msg.service.Complain+'</td>';
										html+='</tr>';
										html+='<tr>';
											html+='<td nowrap="nowrap"><i class="chat_icon chat_icon_5"></i></td>';
											html+='<td nowrap="nowrap">'+lang_obj.manage.frame.support[5]+'</td>';
											html+='<td nowrap="nowrap" class="service_expired">'+data.msg.expired+'</td>';
										html+='</tr>';
									html+='</tbody>';
								html+='</table>';
							html+='</div>';
							$('body').prepend(html);
							$('#user_service_box').fadeIn().css({left:$(window).width()/2-332});
							
							$("#div_mask, #user_service_box .close").on("click", function(){
								$("#user_service_box").fadeOut().remove();
								global_obj.div_mask(1);
							});
						}
					}
				},'json');
			}
		}).on("click", ".user_backup", function(){
			if(!$("#user_backup_box").length){
				$.post('?', 'do_action=account.ueeshop_web_get_service_data', function(data){
					if(data.ret==1){
						if(data.msg.trial==1){
							var html='';
							global_obj.div_mask();
							html+='<div id="user_backup_box">';
								html+='<div class="chat_title"><h3>'+lang_obj.manage.frame.data_backup+'</h3><button class="close">X</button></div>';
								html+='<div class="chat_container service_backup clean">';
									html+='<dl class="backup_box fl">';
										html+='<dt>'+lang_obj.manage.frame.backup[0]+'</dt>';
										html+='<dd>';
											html+='<div class="row">';
												html+='<div class="item">'+lang_obj.manage.frame.time+'</div>';
												html+='<div class="item">'+lang_obj.manage.frame.status+'</div>';
											html+='</div>';
											//for($i=0; $i<7; ++$i){
												html+='<div class="row">';
													html+='<div class="item">'+data.msg.backup+'</div>';
													html+='<div class="item"></div>';
												html+='</div>';
											//}
										html+='</dd>';
									html+='</dl>';
									html+='<dl class="backup_box fl">';
										html+='<dt>'+lang_obj.manage.frame.backup[1]+'</dt>';
										html+='<dd>';
											html+='<div class="row">';
												html+='<div class="item">'+lang_obj.manage.frame.time+'</div>';
												html+='<div class="item">'+lang_obj.manage.frame.status+'</div>';
											html+='</div>';
											//for($i=0; $i<7; ++$i){
												html+='<div class="row">';
													html+='<div class="item">'+data.msg.backup+'</div>';
													html+='<div class="item"></div>';
												html+='</div>';
											//}
										html+='</dd>';
									html+='</dl>';
								html+='</div>';
							html+='</div>';
							$('body').prepend(html);
							$('#user_backup_box').fadeIn().css({left:$(window).width()/2-332});
							
							$("#div_mask, #user_backup_box .close").on("click", function(){
								$("#user_backup_box").fadeOut().remove();
								global_obj.div_mask(1);
							});
						}
					}
				},'json');
			}
		});
		
		$(window).on('resize', function(){
			if($("#user_service_box:visible").length){
				$('#user_service_box').css({left:$(window).width()/2-332});
			}
			if($("#user_backup_box:visible").length){
				$("#user_backup_box").css({left:$(window).width()/2-332});
			}
		});
	},
	
	category_wrap_page_init:function(){
		var resize=function(){
			$('#photo .wrap_content').css({'overflow':'auto', 'height':($(window).height()-$('.r_nav').outerHeight()-$('.list_foot').outerHeight()-20)});
		};
		resize();
		$(window).resize(function(){resize();});
	},
	
	submit_form_init:function(o, jump, fun, debug, callback, is_pop){
		if(o.height()>$('#main .r_con_wrap').height()){
			var html='';
			var part=0;
			o.find('.rows_hd_part:visible').each(function(){
				$(this).before('<a name="part_'+part+'"></a>');
				html+='<dt></dt><dd><a href="#part_'+part+'">'+$(this).html()+'</a></dd>';
				part++;
			});
			if(html){
				$('.r_nav h1').after('<dl class="edit_form_part">'+html+'</dl>');
				$('.r_nav .edit_form_part a').click(function(){
					$('.r_nav .edit_form_part a').removeClass('current');
					$(this).addClass('current');
				});
			}
		}
		o.find('input[rel=amount]').keydown(function(e){
			var value	= $(this).val(),
				key		= (window.event?e.keyCode:e.which),
				ctrl	= (e.ctrlKey || e.metaKey);
			console.log(key);
			if((key>95 && key<106) || (key>47 && key<60) || (key==110 && value.indexOf('.')<0) || (key==190 && value.indexOf('.')<0)){ //[0~9][.]
			}else if((ctrl && key==67) || (ctrl && key==86) || (ctrl && key==65)){ //Ctrl+C Ctrl+V Ctrl+A
			}else if(key!=8){ //删除键
				if(window.event){//IE
					e.returnValue=false;
				}else{//Firefox
					e.preventDefault();
				}
				return false;
			}
		});
		o.find('input[rel=digital]').keydown(function(e){
			var value=$(this).val();
			var key=window.event?e.keyCode:e.which;
			if((key>95 && key<106) || (key>47 && key<60)){
				value==0 && $(this).val('');
			}else if(key!=8){
				if(window.event){//IE
					e.returnValue=false;
				}else{//Firefox
					e.preventDefault();
				}
				return false;
			}
		});
		o.submit(function(){return false;});
		o.find('input:submit').click(function(){
			if($.isFunction(fun) && fun()===false){return false;};
			if(global_obj.check_form(o.find('*[notnull]'), o.find('*[format]'), 1)){return false;};
			if (typeof(CKEDITOR)=='object'){
				for(var i in CKEDITOR.instances) CKEDITOR.instances[i].updateElement();//更新编辑器内容
			}
			$(this).attr('disabled', true);
			$.post('?', o.serialize(), function(data){
				if($.isFunction(callback)){
					callback(data);
					o.find('input:submit').attr('disabled', false);
				}else{
					if(debug){
						o.find('input:submit').attr('disabled', false);
						alert(unescape(data.replace(/\\/g, '%')));
					}else if(data.ret==1){
						if(data.msg.jump){
							window.location=data.msg.jump;
						}else if(data.msg){
							global_obj.win_alert(data.msg, '', 'alert', is_pop);
						}else if(jump){
							window.location=jump;
						}else{
							window.location.reload();
						}
					}else{
						o.find('input:submit').attr('disabled', false);
						global_obj.win_alert(data.msg, '', 'alert', is_pop);
					}
				}
			}, debug?'text':'json');
		});
	},
	
	switchery_checkbox:function(confirmBind, cancelBind){
		$('.switchery').on('click', function(){
			if($(this).hasClass('checked')){
				$(this).removeClass('checked').find('input').attr('checked', false);
				cancelBind && cancelBind($(this));
			}else{
				$(this).addClass('checked').find('input').attr('checked', true);
				confirmBind && confirmBind($(this));
			}
		});
	},
	
	select_all:function(checkbox_select_btn, checkbox_list){
		checkbox_select_btn.on('click', function(){ //全选
			checkbox_list.each(function(index, element) {
				$(element).get(0).checked=checkbox_select_btn.get(0).checked?'checked':'';
            });
		});	
	},
	
	del_bat:function(btn_del_bat, checkbox_list, callback, alert_txt){ 
		btn_del_bat.on('click', function(){
			var id_list='';
			checkbox_list.each(function(index, element) {
				id_list+=$(element).get(0).checked?$(element).val()+'-':'';
            });
			if(id_list){
				id_list=id_list.substring(0,id_list.length-1);
				callback(id_list);	
			}else{
				global_obj.win_alert(alert_txt?alert_txt:lang_obj.global.del_dat_select);
			}
		});
	},
	
	del_init:function(o){
		o.find('.del').click(function(){
			var o=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
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
	},
	
	pop_form:function(o, remove, not_div_mask){ //弹出编辑框
		if(remove==1){
			o.slideUp(250, function(){
				(!not_div_mask || not_div_mask==0) && global_obj.div_mask(1);
			});
		}else{
			global_obj.div_mask();
			o.slideDown(250);
			if($(document).height()<=680){
				o.css('top', 70);
				o.find('.r_con_form').css({'max-height':350});
			}
			o.find('.t h2').add(o.find('.btn_cancel')).click(function(){
				o.slideUp(250, function(){
					(!not_div_mask || not_div_mask==0) && global_obj.div_mask(1);
				});
			});
		}
	},
	
	pop_iframe:function(url, title, callback){ //弹出框架显示框
		var $html='';
		$html+='<div class="pop_form pop_iframe">';
			$html+='<form id="pop_iframe_form" class="w_1000">';
				$html+='<div class="t"><h1>'+title+'</h1><h2>×</h2></div>';
				$html+='<div class="r_con_form"><iframe src="" frameborder="0"></iframe></div>';
			$html+='</form>';
		$html+='</div>';
		$('.r_con_wrap').append($html);
		var o=$('.pop_iframe');
		frame_obj.pop_form(o, 0, callback);
		setTimeout(function(){
			o.find('iframe').attr('src', url+'&iframe=1&r='+Math.random()).load(function(){ //效果执行完，才加载内容显示
				iframe_resize();
			});
		}, 200);
		
		var resize=function(){
			var $h=$(window).height()-o.find('form>.t').outerHeight()-70;
			o.css('top', 30).find('.r_con_form').css({'height':$h, 'max-height':$h});
		}, iframe_resize=function(){
			var $h=$(window).height()-o.find('form>.t').outerHeight()-70,
				$iframe=o.find('iframe').contents();
				$iframe.find('.r_con_wrap').css({'overflow':'auto', 'height':($h-10)});
				$iframe.find('.menu_list').height($iframe.find('.r_con_wrap').height()-$iframe.find('.nav_list').outerHeight(true)-10).jScrollPane();
				$iframe.find('.shipping_area_edit').height($iframe.find('.r_con_wrap').height()-$iframe.find('.nav_list').outerHeight(true)-8);
		}
		resize();
		$(window).resize(function(){
			resize();
			if(o.find('.r_con_form').outerHeight()>300){ //已经固定min-height为300px
				iframe_resize();
			}
		});
		
		o.find('.t h2').add(o.find('.btn_cancel')).click(function(){ //取消
			frame_obj.pop_form(o, 1, callback);
			o.remove();
		});
	},
	
	upload_img_detail:function(img){
		if(!img){return;}
		var del=(typeof(arguments[1])=='undefined')?'':arguments[1];
		return '<a href="javascript:;"><img src="'+img+'"><em></em></a><a href="'+img+'" class="zoom" target="_blank"></a>'+(del?'<span>'+lang_obj.global.del+'</span>':'');
	},
	
	/*
	pop_contents_init:function(o, remove){
		var callback=(typeof(arguments[2])=='undefined')?'':arguments[2];
		var not_div_mask=0;
		global_obj.div_mask();
		o.show();
		var resize=function(){
			o.height($(window).height()-70);
		}
		resize();
		$(window).resize(function(){resize();});
		callback && callback=='not_div_mask=1;' && eval(callback);
		$('#div_mask').click(function(){
			frame_obj.pop_contents_close_init(o, remove, not_div_mask);
		});
	},
	*/
	
	pop_contents_close_init:function(o, remove, not_div_mask){
		if(not_div_mask) eval(not_div_mask); //执行回调函数
		if(!not_div_mask || not_div_mask==0) global_obj.div_mask(1);
		frame_obj.pop_form(o, 1, not_div_mask);
		remove==1?o.remove():o.hide();
	},
	
	/*
	pop_iframe_page_init:function(url, id, callback){
		$('#'+id).remove();
		$('body').append('<div class="pop_contents" id='+id+'><iframe src="'+url+'&iframe=1&r='+Math.random()+'" frameborder="0"></iframe></div>');
		frame_obj.pop_contents_init($('#'+id), 1, callback);
	},
	*/
	
	photo_choice_iframe_init:function(url, id, callback){
		var $html='';
		$html+='<div class="pop_form photo_choice">';
			$html+='<form id="photo_choice_edit_form">';
				$html+='<div class="t"><h1>'+lang_obj.manage.photo.picture_upload+'</h1><h2>×</h2></div>';
				$html+='<div class="r_con_form"><iframe src="" frameborder="0"></iframe></div>';
				$html+='<div class="button"><input type="submit" class="btn_ok" id="button_add" name="submit_button" value="'+lang_obj.global.submit+'" /><input type="button" class="btn_cancel" value="'+lang_obj.global.cancel+'" /></div>';
			$html+='</form>';
		$html+='</div>';
		//$('.r_con_wrap').append($html);
		$('#righter').append($html);
		var o=$('.photo_choice');
		frame_obj.pop_form(o, 0, callback);
		setTimeout(function(){
			o.find('iframe').attr('src', url+'&iframe=1&r='+Math.random()); //效果执行完，才加载内容显示
		}, 200);
		
		var resize=function(){
			var $h=$(window).height()-o.find('form>.t').outerHeight()-o.find('form>.button').outerHeight()-70;
			o.css('top', 30).find('.r_con_form').css({'height':$h, 'max-height':$h});
		}
		resize();
		$(window).resize(function(){resize();});
		
		o.find('#button_add').click(function(){ //提交
			var obj=o.find('iframe').contents(),
				save=obj.find('input[name=save]').val(),//保存图片隐藏域ID
				id=obj.find('input[name=id]').val(),//显示元素的ID
				type=obj.find('input[name=type]').val(),//类型
				maxpic=obj.find('input[name=maxpic]').val();//最大允许图片数
			frame_obj.photo_choice_return(id, type, save, maxpic);
			return false;
		});
		o.find('.t h2').add(o.find('.btn_cancel')).click(function(){ //取消
			frame_obj.pop_contents_close_init($('.photo_choice'), 1, callback);
		});
	},
	
	photo_choice_init:function(o, save, id, type, maxpic, del_url, callback){	//参数：【点击按钮id】【保存图片隐藏域(单图：id值；多图：name值)】【显示元素的id】【类型，例如：editor即添加到编辑器】【最大允许图片数】【删除图片地址('./?'+del_url+'&...')】
		var save=global_obj.urlencode(save);
		var maxpic=maxpic==null?-1:maxpic;
		if(type=='editor'){maxpic=9999;}//编辑器上传没上限
		frame_obj.photo_choice_iframe_init('./?m=set&a=photo&d=choice&obj='+o+'&save='+save+'&id='+id+'&type='+type+'&maxpic='+maxpic, 'photo_choice', callback);
		$('.photo_choice').append('<input type="hidden" class="del_url" value="'+del_url+'" /><input type="hidden" class="callback" value="'+callback+'" />');
	},
	
	photo_choice_return:function(id, type){
		var save=(typeof(arguments[2])=='undefined')?'':arguments[2].replace('\\[\\]', '[]');//保存图片隐藏域id
		var maxpic=(typeof(arguments[3])=='undefined')?'':arguments[3];//最大允许图片数
		var num=(typeof(arguments[4])=='undefined')?'':arguments[4];//类型(本地上传/图片银行)
		var imgpath=(typeof(arguments[5])=='undefined')?'':arguments[5];//已上传图片地址
		var surplus=(typeof(arguments[6])=='undefined')?0:arguments[6];//剩余图片数
		var number=(typeof(arguments[7])=='undefined')?1:arguments[7];//当前图片序号
		var img_name=(typeof(arguments[8])=='undefined')?1:arguments[8];//当前图片名称
		var del_url=parent.$('.photo_choice .del_url').val();
		var callback=parent.$('.photo_choice .callback').val();
		var not_div_mask=0;
		id=id.replace(';', '=');
		save=save.replace(';', '=');
		
		if(num){
			/* 本地上传 */
			if(imgpath){//防止代码自动执行
				if(type!='editor' && parent.$('#'+id+' div').size()>=parseInt(maxpic)){
					global_obj.win_alert(lang_obj.manage.account.picture_tips.replace('xxx', maxpic), function(){
						//parent.frame_obj.pop_contents_close_init(parent.$('.photo_choice'), 1);
						parent.frame_obj.pop_contents_close_init(parent.$('.photo_choice'), 1, not_div_mask);
					}, '', not_div_mask);
					return;
				}
				if(type=='editor'){//编辑框
					$('.fileupload-buttonbar').find('[name=Name\\[\\]][value='+img_name+']').attr('picpath',imgpath);
					if(!surplus){
						$('.fileupload-buttonbar').find('[name=Name\\[\\]]').each(function(){
							var imgpath_list = $(this).attr('picpath');
							var obj=parent.CKEDITOR.instances[id].insertHtml('<img src="'+imgpath_list+'" />');//向编辑器增加内容
						});
					}
				}else if (type=='ad'){
					var obj=parent.$('#'+id);
					if(number==1){//优先上传第一张图片
						obj.find('.preview_pic a').remove();
						obj.find('.preview_pic').append(frame_obj.upload_img_detail(imgpath)).children('.upload_btn').hide();
						obj.find('.preview_pic').children(save?save:'input:hidden').val(imgpath).attr('save', 1);
					}else{
						var oth_obj = $(obj).parent().parent().siblings('.adpic_row');//其他图片框
						var save0_obj = $(oth_obj).find((save?save:'input:hidden')+'[save=0]');//判断.picpath save为0，空图片框对象
						if(save0_obj.length>0){//判断是否还有空位传多图
							save0_obj.eq(0).parent().append(frame_obj.upload_img_detail(imgpath)).children('.upload_btn').hide();
							save0_obj.eq(0).val(imgpath).attr('save', 1);
						}
					}
				}else{
					if(maxpic>1){//多图上传
						var obj=parent.$('#'+id);
						save=save.replace(/\\/g,'');
						if(number==1){//优先上传第一张图片
							obj.find('.preview_pic a').remove();
							obj.find('.preview_pic').append(frame_obj.upload_img_detail(imgpath)).children('.upload_btn').hide();
							obj.find('.preview_pic').children(save?save:'input:hidden').val(imgpath).attr('save', 1);
						}else{
							obj.siblings().each(function(){
								if(imgpath && $(this).find(save?save:'input:hidden').attr('save')==0){
									$(this).find('.preview_pic').append(frame_obj.upload_img_detail(imgpath)).children('.upload_btn').hide();
									$(this).find('.preview_pic').children(save?save:'input:hidden').val(imgpath).attr('save', 1);
									return false;
								}
							});
						}
					}else{//单图上传
						var saveHtml='';
						if(save!='' && parent.$(save.replace('[]', '\\[\\]')).length){
							parent.$(save).val(imgpath);
						}else{
							saveHtml='<input type="hidden" name="'+(save?save:'PicPath[]')+'" value="'+imgpath+'" />';
						}
						parent.$('#'+id+' a').remove();
						parent.$('#'+id).append(frame_obj.upload_img_detail(imgpath)+saveHtml).children('.upload_btn').hide();
						parent.$('#'+id).children('input:hidden').val(imgpath).attr('save', 1);
					}
				}
				callback && eval(callback);
				if(!surplus) parent.frame_obj.pop_contents_close_init(parent.$('.photo_choice'), 1, not_div_mask);//最后一张，自动关闭
			}
		}else{
			/* 从图片银行复制 */
			//$.post('./', $('#photo_list_form').serialize()+'&type='+type, function(data){
			$.post('./', $('.photo_choice').find('iframe').contents().find('#photo_list_form').serialize()+'&type='+type, function(data){
				$('#button_add').attr('disabled', 'disabled');
				if(data.ret!=1){
					//parent.global_obj.win_alert(data.msg);
					global_obj.win_alert(data.msg, function(){
						frame_obj.pop_contents_close_init($('.photo_choice'), 1, not_div_mask);
					}, '', callback);
					callback && eval(callback);
					return false;
				}else{
					if(data.type=='editor'){
						/* 编辑框 */
						var html='';
						var obj = parent.CKEDITOR.instances[id];
						for (var i in data.Pic){
							html += '<img src="'+data.Pic[i]+'" />';
						}
						obj.insertHtml(html);//向编辑器增加内容
					}else if(data.type=='ad'){
						/* 广告图 */
						var html='';
						var obj=parent.$('#'+id);
						if(data.Pic){
							for(var i in data.Pic){
								var imgpath = data.Pic[i];
								if(i==0){//优先上传第一张图片
									obj.find('.preview_pic a').remove();
									obj.find('.preview_pic').append(frame_obj.upload_img_detail(imgpath)).children('.upload_btn').hide();
									obj.find('.preview_pic').children(save?save:'input:hidden').val(imgpath).attr('save', 1);
								}else{
									var oth_obj = $(obj).parent().parent().siblings('.adpic_row');//其他图片框
									var save0_obj = $(oth_obj).find((save?save:'input:hidden')+'[save=0]');//判断.picpath save为0，空图片框对象
									if(save0_obj.length>0){//判断是否还有空位传多图
										save0_obj.eq(0).parent().append(frame_obj.upload_img_detail(imgpath)).children('.upload_btn').hide();
										save0_obj.eq(0).val(imgpath).attr('save', 1);
									}
								}
							}
						}
						callback && eval(callback);
					}else if(data.type=='custom'){
						/* 自定义 */
						var html='';
						var obj=parent.document.getElementById(id);
						var name=$(obj).attr('data');
						$(obj).html(frame_obj.upload_img_detail(data.Pic[0]));
						$(obj).parents('body').find("input[name="+name+"]").val(data.Pic[0]);
					}else if(data.type=='products'){
						/* 多图上传 */
						var html='';
						var obj=parent.$('#'+id);
						save=save.replace(/\\/g,'');
						if(data.Pic && maxpic==1){//只允许单张上传
							obj.find('.preview_pic a').remove();
							obj.find('.preview_pic').append(frame_obj.upload_img_detail(data.Pic[0])).children('.upload_btn').hide();
							obj.find('.preview_pic').children(save?save:'input:hidden').val(data.Pic[0]).attr('save', 1);
						}else if(data.Pic && maxpic>1){//允许多张上传
							//优先上传第一张图片
							obj.find('.preview_pic a').remove();
							obj.find('.preview_pic').append(frame_obj.upload_img_detail(data.Pic[0])).children('.upload_btn').hide();
							obj.find('.preview_pic').children(save?save:'input:hidden').val(data.Pic[0]).attr('save', 1);
							if(data.Pic.length>1){//选择多张
								i=1;
								obj.siblings().each(function(){
									if(data.Pic[i] && $(this).find(save?save:'input:hidden').attr('save')==0){
										$(this).find('.preview_pic').append(frame_obj.upload_img_detail(data.Pic[i])).children('.upload_btn').hide();
										$(this).find('.preview_pic').children(save?save:'input:hidden').val(data.Pic[i]).attr('save', 1);
										++i;
									}
								});
							}
						}
						callback && eval(callback);
					}else{
						/* 单图上传 */
						var html='';
						var obj=parent.$('#'+id);
						if(data.Pic){
							for(var i in data.Pic){
								if(obj.children('div').size()>=parseInt(maxpic)){global_obj.win_alert(lang_obj.manage.account.picture_tips.replace('xxx', maxpic), function(){parent.frame_obj.pop_contents_close_init(parent.$('.photo_choice'), 1);}, ''); return; }
								var saveHtml='';
								if(save!='' && parent.$(save.replace('[]', '\\[\\]')).length){
									parent.$(save.replace('[]', '\\[\\]')).val(data.Pic[i]);
								}else{
									saveHtml='<input type="hidden" name="'+(save?save:'PicPath[]')+'" value="'+data.Pic[i]+'" />';
								}
								obj.find('a').remove();
								obj.append(frame_obj.upload_img_detail(data.Pic[i])+saveHtml).children('.upload_btn').hide();
								obj.children('input:hidden').val(imgpath).attr('save', 1);
							}
						}
						callback && eval(callback);
					}
				}
				//parent.frame_obj.pop_contents_close_init(parent.$('.photo_choice'), 1, not_div_mask);
				frame_obj.pop_contents_close_init($('.photo_choice'), 1, not_div_mask);
			}, 'json');
		}
	},
	
	ajax_photo_del:function(obj, id, Url){
		if(Url==''){return;}
		var $this=$(this);
		global_obj.win_alert(lang_obj.global.del_confirm, function(){
			$.ajax({
				url:'./?'+Url+'&Path='+$(obj).prev().attr('href')+'&Index='+$(obj).parent().index(),
				success:function(data){
					json=eval('('+data+')');
					$('#'+id+' div:eq('+json.msg[0]+')').remove();
				}
			});
		}, 'confirm');
		return false;
	},
	
	file_upload:function(file_input_obj, filepath_input_obj, img_detail_obj, size){
		var size=(typeof(arguments[3])=='undefined')?'':arguments[3];
		var multi=(typeof(arguments[4])=='undefined')?false:arguments[4];
		var queueSizeLimit=(typeof(arguments[5])=='undefined')?5:arguments[5];
		var callback=arguments[6];
		var fileExt=(typeof(arguments[7])=='undefined' || arguments[7]=='')?'*.jpg;*.png;*.gif;*.jpeg;*.ico':arguments[7];//;*.bmp
		var do_action=(typeof(arguments[8])=='undefined')?'action.file_upload':arguments[8];
		file_input_obj.omFileUpload({
			action:'./?session_id='+session_id,
			actionData:{
				do_action:do_action,
				size:size
			},
			fileExt:fileExt,
			fileDesc:'Files',
			autoUpload:true,
			multi:multi,
			queueSizeLimit:queueSizeLimit,
			swf:'/inc/file/fileupload.swf?r='+Math.random(),
			method:'post',
			buttonText:lang_obj.manage.frame.file_upload,
			onComplete:function(ID, fileObj, response, data, event){
				var jsonData=eval('('+response+')');
				if(jsonData.status==1){
					if($.isFunction(callback)){
						callback(jsonData.filepath, data.fileCount, jsonData.name);
					}else{
						filepath_input_obj.val(jsonData.filepath);
						img_detail_obj.html(frame_obj.upload_img_detail(jsonData.filepath));
					}
				}
			}
		});
	},
	
	/******************************旧版数据统计(start)******************************/
	chart:function(){
		$('.chart').height(frame_obj.chart_par.height).highcharts({
            chart:{
				type:frame_obj.chart_par.themes,
				backgroundColor:frame_obj.chart_par.bg
            },
            title:{text:''},
			tooltip: {
				shared: true,
				valueSuffix: frame_obj.chart_par.valueSuffix                   
			},
			xAxis:{categories:chart_data.date},
			yAxis:[{
				title:{text:''},
				min:0
			}],
			legend:frame_obj.chart_par.legend,
			plotOptions:{
				line:{
					dataLabels:{enabled:true},
					enableMouseTracking:false
				},
				bar:{
					dataLabels:{enabled:true}
				}
			},
			series:chart_data.count,
			exporting:{enabled:false}
        });
	},
	
	chart_pie:function(){
		$('.chart').height(500).highcharts({
			title:{text:''},
            credits:{enabled:false},
			tooltip:{
				pointFormat:'{series.name}: <b>{point.percentage:.2f}%</b>'
			},
			plotOptions:{
				pie:{
					allowPointSelect:true,
					cursor:'pointer',
					dataLabels:{
						enabled:true,
						color:'#000000',
						connectorColor:'#000000',
						format:'<b>{point.name}</b>: {point.percentage:.2f} %'
					}
				}
			},
			series:[{
				type:'pie',
				name:lang_obj.manage.account.percentage,
				data:user_area_data
			}]
		});
	},
	
	chart_par:{themes:'column',height:500,bg:'',legend:{},valueSuffix:''},
	/******************************旧版数据统计(end)******************************/
	
	prompt_steps:function(){
		if(!$('#prompt_steps_tips').length){
			var PromptContent='';
			PromptContent+='<div id="prompt_steps_bg"></div>';
			PromptContent+='<div id="prompt_steps_tips" status="0"><a href="javascript:;"><img src="/static/manage/images/prompt/prompt_steps_begin.png" /></a></div>';
			PromptContent+='<div id="prompt_steps_light"></div>';
			PromptContent+='<div id="prompt_steps_ico"><span>设置</span></div>';
			$('body').prepend(PromptContent);
			$('#prompt_steps_bg').css({'width':'100%', 'height':$(document).height(), 'overflow':'hidden', 'position':'fixed', 'top':0, 'left':0, 'background':'url(/static/manage/images/prompt/prompt_steps_bg.png) repeat', 'z-index':10000});
			$('#prompt_steps_ico').css({'position':'absolute', 'width':70, 'height':70, 'background-image':'url(/static/manage/images/frame/m-ico.png)', 'background-repeat':'no-repeat', 'background-color':'#53a18e', 'text-align':'center', 'display':'none', 'z-index':10001}).find('span').css({'padding-top':44, 'color':'#fff', 'display':'block'});
			$('#prompt_steps_light').css({'position':'absolute', 'width':70, 'height':70, 'background':'url(/static/manage/images/prompt/prompt_steps_light.png) repeat', 'display':'none', 'z-index':10002});
		}
		var $PromptTips=$('#prompt_steps_tips'),
			$PromptStatus=parseInt($PromptTips.attr('status')),
			$PromptIco=$('#prompt_steps_ico'),
			$PromptLight=$('#prompt_steps_light'),
			$PromptClick=$PromptTips.find('a');
		if($PromptStatus==0){
			$PromptTips.css({'position':'absolute', 'top':74, 'background':'url(/static/manage/images/prompt/prompt_steps_0.png) no-repeat', 'width':619, 'height':107, 'z-index':10003});
			$PromptTips.css({'left':$(window).width()/2-309.5});
			$PromptClick.css({'marginTop':75, 'marginRight':10, 'float':'right'});
		}else if($PromptStatus==1){
			$PromptClick.find('img').attr('src', '/static/manage/images/prompt/prompt_steps_know.png');
			$PromptTips.css({'left':61, 'top':90, 'background':'url(/static/manage/images/prompt/prompt_steps_1.png) no-repeat', 'width':383, 'height':249});
			$PromptIco.css({'display':'block', 'left':0, 'top':53});
			$PromptLight.css({'display':'block', 'left':0, 'top':53});
			$PromptClick.css({'marginTop':180, 'marginRight':33});
		}else if($PromptStatus==2){
			$PromptTips.css({'left':58, 'top':230, 'background':'url(/static/manage/images/prompt/prompt_steps_2.png) no-repeat', 'width':336, 'height':185});
			$PromptIco.css({'top':197, 'background-position':'-140px -140px'}).find('span').text('产品');
			$PromptLight.css({'top':197});
			$PromptClick.css({'marginTop':137, 'marginRight':20});
		}else if($PromptStatus==3){
			$PromptTips.css({'left':62, 'top':158, 'background':'url(/static/manage/images/prompt/prompt_steps_3.png) no-repeat', 'width':337, 'height':158});
			$PromptIco.css({'top':125, 'background-position':'-70px -70px'}).find('span').text('内容');
			$PromptLight.css({'top':125});
			$PromptClick.css({'marginTop':104, 'marginRight':33});
		}else if($PromptStatus==4){
			$('#header ul li.ico-0').removeClass('current');
			$PromptLight.css({'background':'url(/static/manage/images/prompt/prompt_steps_light_to.png) repeat'});
			$PromptTips.css({'left':'auto', 'right':103, 'top':43, 'background':'url(/static/manage/images/prompt/prompt_steps_4.png) no-repeat', 'width':313, 'height':172});
			$PromptIco.css({'left':'auto', 'right':384, 'top':0, 'background-image':'url(/static/manage/images/frame/h-ico.png)', 'background-position':'0 -51px', 'background-color':'#e7e7e7', 'width':64, 'height':51}).find('span').text(' ');
			$PromptLight.css({'left':'auto', 'right':384, 'top':0, 'width':64, 'height':51});
			$PromptClick.css({'marginTop':111, 'marginRight':38});
		}else if($PromptStatus==5){
			$('#header ul li.ico-0').addClass('current');
			$PromptTips.css({'left':'auto', 'right':31, 'top':43, 'background':'url(/static/manage/images/prompt/prompt_steps_5.png) no-repeat', 'width':349, 'height':94});
			$PromptIco.css({'left':'auto', 'right':127, 'background-position':'0 -357px'});
			$PromptLight.css({'left':'auto', 'right':127});
			$PromptClick.css({'marginTop':49, 'marginRight':38});
		}else{
			$('#prompt_steps_bg, #prompt_steps_tips, #prompt_steps_ico, #prompt_steps_light').remove();
		}
		$PromptClick.on('click', function(){
			$PromptTips.attr('status', $PromptStatus+1);
			frame_obj.prompt_steps();
		});
	},
	
	mouse_click:function(o, name, fun){ //兼容个别浏览器，点击事件与拖动效果相互冲突
		var $obj={};
		$obj[name+'_hasMove'] = false;
		$obj[name+'_obj'] = {};
		$obj[name+'_end'] = 1;
		
		o.on('mousedown', function(e){ //按下鼠标键事件
			$obj[name+'_obj'].x = e.pageX;
			$obj[name+'_obj'].y = e.pageY;
			$obj[name+'_end'] = 0;
			$obj[name+'_hasMove'] = false;
		}).on('mousemove', function(e){ //移动鼠标键事件
			if($obj[name+'_end'] == 1) return false;
			if(e.pageX === $obj[name+'_obj'].x && e.pageY === $obj[name+'_obj'].y) {
				$obj[name+'_hasMove'] = false;
			}else{
				$obj[name+'_hasMove'] = true;
			}
		}).on('mouseup', function(e){ //松开鼠标键事件
			$obj[name+'_obj'].x = e.pageX;
			$obj[name+'_obj'].y = e.pageY;
			$obj[name+'_end'] = 1;
			if(!$obj[name+'_hasMove']){ //没有移动过当前元素
				$.isFunction(fun) && fun($(this));
			}
			$obj[name+'_hasMove'] = false;
		});
	}
}