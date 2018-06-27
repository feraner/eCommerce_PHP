/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

var sales_obj={
	//--------- 公共 start ----------//
	sales_global:{
		del_action:'',
		order_action:'',
		init:function(){
			frame_obj.del_init($('#sales .r_con_table')); //删除事件
			frame_obj.select_all($('#sales .r_con_table input[name=select_all]'), $('#sales .r_con_table input[name=select]')); //批量操作
			/* 批量删除 */
			frame_obj.del_bat($('.r_nav .del'), $('#sales .r_con_table input[name=select]'), function(id_list){
				var $this=$(this);
				global_obj.win_alert(lang_obj.global.del_confirm, function(){
					$.get('?', {do_action:sales_obj.sales_global.del_action, group_id:id_list}, function(data){
						if(data.ret==1){
							window.location.reload();
						}
					}, 'json');
				}, 'confirm');
				return false;
			});
			$('#sales .r_con_table .myorder_select').on('dblclick', function(){
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
						$.post('?', 'do_action='+sales_obj.sales_global.order_action+'&Id='+$Id+'&Number='+$(this).val(), function(data){
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
	package_frame_init:function(){
		frame_obj.select_all($('#combination .r_con_table input[name=select_all]'), $('#combination .r_con_table input[name=select]')); //批量操作
	},
	
	package_main_id:$('#proid_hide').val()?$('#proid_hide').val():0, //用于保存主产品区域的产品id
	package_assist_id:$('#packageproid_hide').val()?$('#packageproid_hide').val().split('|'):'', //用于保存捆绑产品区域的产品id
	package_edit_init:function(){
		sales_obj.package_resize();	//设置背景高度
		sales_obj.dragElement(); //产品拖动
		sales_obj.ChangeMainProducts();
		sales_obj.removeItem(); //移除产品框
		$(window).resize(sales_obj.package_resize);//设置背景高度
		sales_obj.fixIE7BorderBox('.lefter','.list_box_righter','.p_main_info','.p_related_info','.p_info');
		var is_main=parseInt($('#is_main').val());//判断是否为组合产品系列
		var type_id=parseInt($('#type_hide').val());//判断(组合购买/组合促销)或者(秒杀/团购/节日模板)
		if((!is_main && type_id==3)||!is_main && !type_id || !is_main && type_id==1) $('.start_time').daterangepicker({showDropdowns:true});
		//产品搜索文本框
		$('#search_form input[name=Name]').keydown(function(e){
			var key=window.event?e.keyCode:e.which;
			if(key==13){ //回车按键
				$('#search_btn').click();
				return false;
			}
		});
		
		//产品搜索按钮
		$('#search_btn').live('click', function(){
			var url;
			$(this).attr('disabled', true);
			
			if(parseInt($('#is_main').val())){//判断是否为组合产品系列
				if(parseInt($('#type_hide').val())){
					url='./?m=sales&a=promotion&d=edit';
				}else url='./?m=sales&a=package&d=edit';
			}else{
				if(parseInt($('#type_hide').val())==1){
					url='./?m=sales&a=tuan&d=add';
				}else if(parseInt($('#type_hide').val())==2){
					url='./?m=sales&a=holiday&d=products';
				}else if(parseInt($('#type_hide').val())==3){
					url='./?m=sales&a=sales&d=add';
				}else url='./?m=sales&a=seckill&d=add';
			}
			
			$.ajax({
				type:'post',
				url:url+'&'+$('#search_form').serialize(),
				async:false,
				success:function(data){
					$('.product_frame').html($(data).find('.product_frame').html());
					sales_obj.package_resize();
					sales_obj.dragElement();
				}
			});
			return false;
		});
		
		//表单提交
		$('.lefter>form').submit(function(){return false;});
		$('.lefter>form .submit_btn').live('click', function(){
			var _this = $(this);
			if(global_obj.check_form($('.lefter>form *[notnull]'))){return false;};
			$(this).attr('disabled', true);
			if(parseInt($('#is_main').val())){//判断是否为组合产品系列
				$.post('?', $('#related_form').serialize(), function(data){
					_this.attr('disabled', false);
					if(data.ret!=1){
						global_obj.win_alert(data.msg);
					}else{
						window.location=$('#back_action').val();
					}
				}, 'json');
			}else{
				$.post('?', $('#tuan_form').serialize(), function(data){
					_this.attr('disabled', false);
					if(data.ret!=1){
						global_obj.win_alert(data.msg);
					}else{
						if(parseInt($('#type_hide').val())==1){
							window.location=$('#back_action').val();
						}else if(parseInt($('#type_hide').val())==2){
							window.location='./?m=sales&a=holiday&d=edit&theme='+data.msg[0];
						}else window.location=$('#back_action').val();
					}
				}, 'json');
			}
		});
		
		$('#turn_page_oth a').live('click', function(){
			$.ajax({
				type:'post',
				url:$(this).attr('href'),
				data:{'remove_pid':$('input[name=remove_pid]').val()},
				async:false,
				success:function(data){
					$('.product_frame').html($(data).find('.product_frame').html());
					sales_obj.package_resize();
					sales_obj.dragElement();
				}
			});
			return false;
		});
		
		$('.product_item select').on('change', function(){
			$(this).children('option[value='+$(this).val()+']').attr('selected', true).siblings().attr('selected', false);
		});
		
		frame_obj.switchery_checkbox();
	},
	package_resize:function(){
		var h=$('#main').height()-$('.r_nav').outerHeight()-60,oh;
		$('#main .lefter, #main .list_box_righter').height(h);
		oh = $('#main .lefter .p_title').outerHeight(true)*$('#main .lefter .p_title').length+$('#main .lefter .rows').outerHeight(true)*$('#main .lefter .rows').length;
		if(parseInt($('#is_main').val())){//判断是否为组合产品系列
			$('#main .lefter .p_related_frame').height(h-oh);
		}else{
			$('#main .lefter .p_related_frame').height(h-oh);
		}
		$('#main .list_box_righter .product_frame').height(h-$('#main .list_box_righter .p_title').outerHeight(true));
	},
	fixIE7BorderBox:function(){
		if($.browser.msie && $.browser.version=='7.0'){
			for(var i=0;i<arguments.length;i++){
				if($(arguments[i]).length > 0){ //元素存在
					var iW=$(arguments[i]).get(0).offsetWidth-$(arguments[i]).width();
					var iH=$(arguments[i]).get(0).offsetHeight-$(arguments[i]).height();
					$(arguments[i]).width($(arguments[i]).width()-iW);
					$(arguments[i]).height($(arguments[i]).height()-iH);
				}
			}
		}
	},
	dragElement:function(){
		var is_main=parseInt($('#is_main').val());//判断是否为组合产品系列
		var type_id=parseInt($('#type_hide').val());//判断(组合购买/组合促销)或者(秒杀/团购/节日模板)
		$('#main .product_item').off().click(function(){
			var _this = $(this),
				p_id=_this.attr('pro_num'), //产品id
				judge=0,
				oNear;
			var getLstInfo={
				'img'	: $("#product_item_"+p_id+" .p_img img").attr("src"),
				'name'	: $("#product_item_"+p_id+" .p_info .p_list span").eq(0).text(),
				'price'	: $("#product_item_"+p_id+" .p_info .p_list span").eq(1).text(),
				'attr'	: $("#product_item_"+p_id+" .p_info .p_list").eq(2).html()
			};
			var str="";//模块构造
			oNear = '.p_related_frame';
			if(sales_obj.package_main_id!=p_id){
				str += '<div id="related_product_'+p_id+'" class="p_related_item">';
				if(is_main){
					str +=	'<div class="main_products';
					if($('#proid_hide').val()<1){
						str +=	' p_checked';	
					}
					str +=	'" pro_num="'+p_id+'"></div>';
				}
				str +=		'<div class="p_related_img"><img src="'+getLstInfo['img']+'" /></div>';
				str +=		'<div class="p_related_info">';
				str +=			'<div class="related_list p_name"> <span>'+getLstInfo['name']+'</span></div>';//'+lang_obj.manage.sales.proName+':
				str +=			'<div class="related_list p_price">'+lang_obj.manage.sales.proPrice+': <span>'+getLstInfo['price']+'</span></div>';                   
				if(!is_main && type_id==3){//产品促销
					var i=0;
					// str += '<div class="related_list related_big_list"><input name="PromotionTime[]" type="text" value="'+lang_str_obj.now_time+'/'+lang_str_obj.now_time+'" class="start_time form_input" size="42" readonly></div>';
					str += '<div class="related_list related_big_list"><input type="radio" name="PromotionType['+p_id+']" value="0" class="promotion_type" checked /> '+lang_obj.manage.sales.money+': '+lang_str_obj.currency+'<input name="PromotionPrice[]" type="text" value="" class="form_input" size="5" maxlength="10" notnull />&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" name="PromotionType['+p_id+']" value="1" class="promotion_type" /> '+lang_obj.manage.sales.discount+': <input name="PromotionDiscount[]" type="text" value="" class="form_input" size="5" maxlength="5" />%</div>';
				}else if(!is_main && type_id==1){//团购
					var i=0;
					// str += '<div class="related_list related_big_list"><input name="PromotionTime[]" type="text" value="'+lang_str_obj.now_time+'/'+lang_str_obj.now_time+'" class="start_time form_input" size="42" readonly></div>';
					str += '<div class="related_list related_big_list">'+lang_obj.manage.sales.tuan_price+': '+lang_str_obj.currency+'<input name="Price[]" type="text" value="" class="form_input" size="5" maxlength="5" notnull>&nbsp;&nbsp;&nbsp;&nbsp;'+lang_obj.manage.sales.buyer_count+': <input name="BuyerCount[]" type="text" value="" class="form_input" size="5" maxlength="10" notnull>&nbsp;&nbsp;&nbsp;&nbsp;'+lang_obj.manage.sales.total_count+': <input name="TotalCount[]" type="text" value="" class="form_input" size="5" maxlength="10" notnull></div>';
				}else if(!is_main && !type_id){//秒杀
					var i=0;
					// str += '<div class="related_list related_big_list"><input name="PromotionTime[]" type="text" value="'+lang_str_obj.now_time+'/'+lang_str_obj.now_time+'" class="start_time form_input" size="42" readonly></div>';
					str += '<div class="related_list related_big_list">'+lang_obj.manage.sales.seckill_price+': '+lang_str_obj.currency+'<input name="Price[]" type="text" value="" class="form_input" size="5" maxlength="5" notnull>&nbsp;&nbsp;'+lang_obj.manage.sales.qty+': <input name="Qty[]" type="text" value="" class="form_input" size="5" maxlength="5" notnull>&nbsp;&nbsp;'+lang_obj.manage.sales.max_qty+': <input name="MaxQty[]" type="text" value="" class="form_input" size="5" maxlength="5" notnull></div>';
					str += '<div class="related_list related_big_list">'+lang_obj.manage.sales.remainder_qty+': <input name="RemainderQty[]" type="text" value="" class="form_input" size="5" maxlength="5" notnull></div>';
				}else{//组合产品
					str += '<div class="related_list attr_list"><span>'+getLstInfo['attr']+'</span></div>';
				}
				str +=		'</div>';
				str +=		'<div class="remove-item hand" type="';
				if(is_main && $('#proid_hide').val()<1){
					str += 'main';
				}else{
					str += 'related';
				}
				str += '" del_num="'+p_id+'">X</div>';
				str += '</div>';
				
				if(!is_main && type_id==2 && $(oNear).find('.p_related_item').size()+1>$(oNear).attr("max")){
					global_obj.win_alert(lang_obj.manage.sales.beyondNumber+$(oNear).attr("max"));
				}else if($(oNear).find('.p_related_item').size()>0){
					if($("#related_product_"+p_id).length<1){
						$(oNear).append(str);
						judge=1;
					}else{
						global_obj.win_alert(lang_obj.manage.sales.mainArea);
					}	
				} else {
					$(oNear).html(str);
					judge=1;
				}
				
				if(judge){
					if($('#packageproid_hide').val()){
						$('#packageproid_hide')[0].value+=p_id+'|';
					}else{
						$('#packageproid_hide').val('|'+p_id+'|');
					}
				}
				// if(!is_main) $('.start_time').daterangepicker({showDropdowns:true});
				
				if(!is_main && type_id==3){ //产品促销
					$('.lefter input.promotion_type').click(function(){
						var $obj=$(this).parent();
						$obj.find('input').attr('style', '');
						if($(this).val()=='0'){
							$obj.find('input[name=PromotionPrice\\[\\]]').attr('notnull', 'notnull');
							$obj.find('input[name=PromotionDiscount\\[\\]]').removeAttr('notnull');
						}else{
							$obj.find('input[name=PromotionPrice\\[\\]]').removeAttr('notnull');
							$obj.find('input[name=PromotionDiscount\\[\\]]').attr('notnull', 'notnull');
						}
					});
				}
				sales_obj.ChangeMainProducts();
				if(judge && is_main && $('#proid_hide').val()<1){
					$('#proid_hide').val(p_id);
					$('#packageproid_hide').val($('#packageproid_hide').val().replace(p_id+'|', ''));
				}
				if(judge){
					_this.remove();
					$('input[name=remove_pid]').val($('input[name=remove_pid]').val()+p_id+',');
				}
			}else{
				global_obj.win_alert(lang_obj.manage.sales.mainArea);
			}
		});
		$('.list_box_righter .r_search_btn').off().click(function(){
			$(this).parent().find('.p_search').toggle();
		});
	},
	ChangeMainProducts:function(){
		$('.p_related_item .main_products').off().click(function(){
			var p_id = $(this).attr('pro_num'),
				relate_id = '|';
			$('.p_related_item').find('.remove-item').attr('type','related');
			$(this).parent().find('.remove-item').attr('type','main');
			$('.p_related_item .main_products').removeClass('p_checked');
			$(this).addClass('p_checked');
			$('#proid_hide').val(p_id);
			var _html = $(this).parent();
			$(this).parent().remove();
			$('.p_related_frame').prepend(_html);
			sales_obj.ChangeMainProducts();
			$('.p_related_item .main_products').each(function(){
				relate_id += $(this).attr('pro_num')+'|';
			});
			if($('.p_related_item .main_products').length){
				$('#packageproid_hide').val(relate_id.replace(p_id+'|', ''));
			}
		});
	},
	removeItem:function(){ //移除产品框
		var is_main=parseInt($('#is_main').val());//判断是否为组合产品系列
		var type_id=parseInt($('#type_hide').val());//判断(组合购买/组合促销)或者(秒杀/团购/节日模板)
		var htmlInfo=$("#main .lefter .p_main_frame").html();
		var relatedNotice;
		$("#main .lefter .remove-item").live('click',function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				var type=$this.attr('type');
				if(type=='main'){
					sales_obj.package_main_id=0;
					$('#proid_hide').val(0);
				}
				$('#packageproid_hide').val($('#packageproid_hide').val().replace($this.attr('del_num')+'|', ''));
				$('input[name=remove_pid]').val($('input[name=remove_pid]').val().replace($this.attr('del_num')+',', ''));
				var oParent=$this.parents('.p_related_item');
				oParent.remove();
				if($('#packageproid_hide').val()=='|'){
					$('#packageproid_hide').val('');
					if(is_main){
						relatedNotice=lang_obj.manage.sales.relatedNotice;
					}else{
						if(type_id==1) relatedNotice=lang_obj.manage.sales.tuanNotice;
						else if(type_id==2) relatedNotice=lang_obj.manage.sales.holidayNotice;
						else if(type_id==3) relatedNotice=lang_obj.manage.sales.salesNotice;
						else relatedNotice=lang_obj.manage.sales.seckillNotice;
					}
					if($('#packageproid_hide').val()<1 && $('#proid_hide').val() < 1){
						$('.p_related_frame').html('<div class="p_related_notice">'+relatedNotice+'</div>');
					}
				}
			}, 'confirm');
			return false;
		});
	},
	//--------- 公共 end ----------//
	
	//--------- 节日模板 start ----------//
	holiday_list_init:function(){
		$('#holiday .list_bd .item').hover(function(){
			$(this).children('.info').stop(true, true).slideDown(500);
		},function(){
			$(this).children('.info').stop(true, true).slideUp(500);
		}).children('.img').click(function(){
			if(!$(this).parent().hasClass('current')){
				var $this=$(this);
				global_obj.win_alert(lang_obj.manage.module.sure_module, function(){
					$this.parent().addClass('current').siblings().removeClass('current');
					$.post('?', "do_action=sales.holiday_select&HId="+$this.parent().attr('hid'), function(data){
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
		
		$('#holiday_search_form').submit(function(){return false;});
		$('#holiday_search_form input:submit').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};
			$(this).attr('disabled', true);
			window.location='./?m=sales&a=holiday&'+$('#holiday_search_form').serialize();
		});
	},
	
	holiday_frame_init:function(){
		$('#holiday .m_righter').width($('.r_con_wrap').width()-$('#holiday .m_lefter').width()-100);
	},
	
	holiday_edit_init:function(){
		//加载上传按钮
		$('#PicUpload, .upload_pic .edit').on('click', function(){frame_obj.photo_choice_init('PicUpload', 'form input[name=PicPath]', 'PicDetail', '', 1);});
		$('.upload_pic .del').on('click', function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.ajax({
					url:'./?do_action=action.file_del&PicPath='+$this.prev().attr('href'),
					success:function(){
						$('#PicDetail').children('a').remove();
						$('#PicDetail').children('.upload_btn').show();
						$('form input[name=PicPath]').val('');
					}
				});
			}, 'confirm');
			return false;
		});
		/* Logo图片上传 */
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
		
		//加载版面内容
		for(i=0; i<web_template_data.length; i++){
			var obj=$("#web_template div").filter('[rel=edit-'+web_template_data[i]['Postion']+']');
			obj.attr('no', i);
			if(web_template_data[i]['NeedLink']==1){
				obj.find('.text').html('<a href="">'+(web_template_data[i]['ContentsType']==1?web_template_data[i]['Title'][0]:web_template_data[i]['Title'])+'</a>');
			}else{
				obj.find('.text').html((web_template_data[i]['ContentsType']==1?web_template_data[i]['Title'][0]:web_template_data[i]['Title']));
			}
			obj.find('.img').html('<img src="'+(web_template_data[i]['ContentsType']==1?web_template_data[i]['PicPath'][0]:web_template_data[i]['PicPath'])+'" />');
		}
		$('.template_box').append('<div class="mod">&nbsp;</div>');	//追加编辑按钮
		$('.template_box').hover(function(){$(this).find('.mod').show();}, function(){$(this).find('.mod').hide();});
		
		//切换编辑内容
		$('#web_template .mod').click(function(){
			var parent=$(this).parent();
			var no=parent.attr('no');
			
			$('.current_box').remove();
			parent.append("<div class='current_box'></div>");
			$('.current_box').css({'height':parent.height()-10, 'width':parent.width()-10})
			
			$("#set_banner, #set_config, #set_config>.rows").hide();
			$("#holiday_form input").removeAttr('notnull');
			
			if(web_template_data[no]['ContentsType']==1){
				$("#set_banner").show();
				var len=parseInt(web_template_data[no]['ListNum']);
				var dataTitle=web_template_data[no]['Title'];
				var dataImgPath=web_template_data[no]['PicPath'];
				var dataUrl=web_template_data[no]['Url'];
				
				for(var i=0; i<len; i++){
					if(web_template_data[no]['IsTitle']==1) $('#holiday_form div[value=title_list]').eq(i).show();
					if(web_template_data[no]['IsLink']==1) $('#holiday_form div[value=url_list]').eq(i).show();
					if(i>0 && !$('#set_banner>.rows').eq(i).length){
						$('#set_banner>.rows').eq(0).clone().appendTo('#set_banner');
						$('#set_banner>.rows').eq(i).find('.up_input').find('*[name!=PicUpload]').remove();
						$('#set_banner label').eq(i).text(lang_obj.global.picture+(i+1)+':');
						$('#set_banner input[name=PicUpload]').eq(i).attr('id', 'PicUpload_'+i);
					}
					$('#set_banner .img_tips_number').eq(i).text(web_template_data[no]['Size'][i]);
					$('#holiday_form input[name=TitleList\\[\\]]').eq(i).val(dataTitle[i]); //标题
					$('#holiday_form input[name=UrlList\\[\\]]').eq(i).val(dataUrl[i]); //链接
					$('#holiday_form input[name=ImgPath\\[\\]]').eq(i).val(dataImgPath[i]?dataImgPath[i]:'');
					$('#holiday_form input[name=ImgPathHide\\[\\]]').eq(i).val(dataImgPath[i]?dataImgPath[i]:'');
					//$("#holiday_form .pic_detail").eq(i).html(dataImgPath[i]?frame_obj.upload_img_detail(dataImgPath[i]):'');
					$("#holiday_form .preview_pic:eq("+i+")>a").remove();
					if(dataImgPath[i]){
						$("#holiday_form .preview_pic").eq(i).append(frame_obj.upload_img_detail(dataImgPath[i])).children('.upload_btn').hide();
					}else{
						$("#holiday_form .preview_pic:eq("+i+") .upload_btn").show();
					}
				}
				
				$('input[name=PicUpload], .upload_picture .edit').on('click', function(){
					var num=$(this).parents('.upload_picture').find('input[name=PicUpload]').attr('id').replace('PicUpload_', '');
					frame_obj.photo_choice_init('PicUpload_'+num, '#holiday_form input[name=ImgPath\[\]]:eq('+num+')', 'holiday_form .preview_pic:eq('+num+')', '', 1);
				});
				$('.upload_picture .del').on('click', function(){
					var $this=$(this);
					global_obj.win_alert(lang_obj.global.del_confirm, function(){
						$.ajax({
							url:'./?do_action=action.file_del&PicPath='+$this.prev().attr('href'),
							success:function(){
								$this.parent().find('.preview_pic>a').remove();
								$this.parent().find('.preview_pic .upload_btn').show();
								$this.parents('.rows input[name=ImgPath\\[\\]]').val('');
							}
						});
					}, 'confirm');
					return false;
				});
				
				$('#type_hide').val(1);
				if($('#set_banner>.rows').size()>len) $('#set_banner>.rows:gt('+(len-1)+')').remove();//删除多余
			}else{
				$('#set_config').show();
				if(web_template_data[no]['IsTitle']==1) $('#holiday_form div[value=title]').show().find('input[name=Title]').val(web_template_data[no]['Title']).attr('notnull', ''); //标题
				if(web_template_data[no]['IsImg']==1){//图片
					$('#holiday_form div[value=images]').show();
					$('#holiday_form input[name=PicPath]').attr('notnull', '');
				}
				if(web_template_data[no]['IsLink']==1) $('#holiday_form div[value=url]').show().find('input[name=Url]').val(web_template_data[no]['Url']).attr('notnull', ''); //链接
				if(web_template_data[no]['IsPro']==1) $('#holiday_form div[value=products]').show();//列表
				
				$('#holiday_form input').filter('#products_btn').attr({'num':web_template_data[no]['ProList'],'max':web_template_data[no]['ListNum']})
				.end().filter('[name=PicPath], [name=PicPathHide]').val(web_template_data[no]['PicPath'])
				.end().filter('[name=Title]').focus();
				
				$('#set_config .img_tips_number').text(web_template_data[no]['Size']);
				$('#PicDetail>a').remove();
				$('#PicDetail').append(frame_obj.upload_img_detail($('#holiday_form input[name=PicPath]').val())).children('.upload_btn').hide();
				$('#type_hide').val(0);
			}
			$('#number_hide').val(no);
		});
		
		//加载默认内容
		$('#web_template .mod').eq(0).click();
		
		$('#products_btn').click(function(){
			window.location='./?m=sales&a=holiday&d=products&theme='+$('#theme_hide').val()+'&num='+$(this).attr('num')+'&max='+$(this).attr('max');
		});
		
		$('#holiday_form .btn_cancel').live('click', function(){
			window.location='./?m=sales&a=holiday';
		});
		
		$('#holiday_form').live('submit', function(){return false;});
		$('#holiday_form input:submit').live('click', function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};
			$(this).attr('disabled', true);
			
			$.post('?', $('#holiday_form').serialize(), function(data){
				$('#holiday_form input:submit').attr('disabled', false);
				if(data.ret!=1){
					global_obj.win_alert(data.msg);
				}else{
					var lang_str = '',
						web_lang = $('input[name=HideLang]').val();
					if(web_lang) lang_str = '&lang='+web_lang;
					window.location='./?m=sales&a=holiday&d=edit&theme='+data.msg[0]+lang_str;
				}
			}, 'json');
		});
		
		$('#holiday_set_form').live('submit', function(){return false;});
		$('#holiday_set_form input:submit').live('click', function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};
			$(this).attr('disabled', true);
			return false;
			$.post('?', $('#holiday_set_form').serialize(), function(data){
				$('#holiday_set_form input:submit').attr('disabled', false);
				if(data.ret!=1){
					global_obj.win_alert(data.msg);
				}else{
					window.location='./?m=sales&a=holiday&d=edit&theme='+data.msg[0];
				}
			}, 'json');
		});
		$('.lang_box .lang').click(function(){
			$('input[name=HideLang]').val($(this).attr('data-lang'));
			$('#holiday_form input:submit').click();
		});
	},
	//--------- 节日模板 end ----------//
	
	//--------- 产品促销 start ----------//
	sales_list_init:function(){
		sales_obj.package_frame_init();
		sales_obj.sales_global.del_action='sales.sales_del_bat';
		sales_obj.sales_global.order_action='sales.sales_edit_myorder';
		sales_obj.sales_global.init();
		sales_obj.batch_edit('./?m=sales&a=sales');
	},
	
	sales_edit_init:function(){
		$('#sales_edit_form input[name=PromotionTime]').daterangepicker({showDropdowns:true});
		$('#sales_edit_form input[name="PromotionType"]').click(function(){
			if($(this).val()=='0'){
				$('#sales_edit_form .promotion_money').show().find('input').removeAttr('disabled');
				$('#sales_edit_form .promotion_discount').hide().find('input').attr('disabled', 'disabled');
			}else{
				$('#sales_edit_form .promotion_money').hide().find('input').attr('disabled', 'disabled');
				$('#sales_edit_form .promotion_discount').show().find('input').removeAttr('disabled');
			}
		});
		
		$('#sales_edit_form').live('submit', function(){return false;});
		$('#sales_edit_form input:submit').live('click', function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};
			$(this).attr('disabled', true);
			
			$.post('?', $('#sales_edit_form').serialize(), function(data){
				$('#sales_edit_form input:submit').attr('disabled', false);
				if(data.ret!=1){
					global_obj.win_alert(data.msg);
				}else{
					window.location='./?m=sales&a=sales';
				}
			}, 'json');
		});
	},
	//--------- 产品促销 end ----------//
	
	//--------- 限时秒杀 start ----------//
	seckill_list_init:function(){
		sales_obj.package_frame_init();
		sales_obj.sales_global.del_action='sales.seckill_del_bat';
		sales_obj.sales_global.order_action='sales.seckill_edit_myorder';
		sales_obj.sales_global.init();
		sales_obj.batch_edit('./?m=sales&a=seckill');
	},
	
	seckill_edit_init:function(){
		$('#seckill_edit_form input[name=PromotionTime]').daterangepicker({showDropdowns:true});
		
		$('#seckill_edit_form').live('submit', function(){return false;});
		$('#seckill_edit_form input:submit').live('click', function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};
			$(this).attr('disabled', true);
			
			$.post('?', $('#seckill_edit_form').serialize(), function(data){
				$('#seckill_edit_form input:submit').attr('disabled', false);
				if(data.ret!=1){
					global_obj.win_alert(data.msg);
				}else{
					window.location='./?m=sales&a=seckill';
				}
			}, 'json');
		});
	},
	//--------- 限时秒杀 end ----------//
	
	//--------- 团购购买 start ----------//
	tuan_list_init:function(){
		sales_obj.sales_global.del_action='sales.tuan_del_bat';
		sales_obj.sales_global.order_action='sales.tuan_edit_myorder';
		sales_obj.sales_global.init();
		sales_obj.batch_edit('./?m=sales&a=tuan');
	},
	
	tuan_edit_init:function(){
		$('#tuan_edit_form input[name=PromotionTime]').daterangepicker({showDropdowns:true});
		
		$('#tuan_edit_form').live('submit', function(){return false;});
		$('#tuan_edit_form input:submit').live('click', function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};
			$(this).attr('disabled', true);
			
			$.post('?', $('#tuan_edit_form').serialize(), function(data){
				$('#tuan_edit_form input:submit').attr('disabled', false);
				if(data.ret!=1){
					global_obj.win_alert(data.msg);
				}else{
					window.location='./?m=sales&a=tuan';
				}
			}, 'json');
		});
	},
	//--------- 团购购买 end ----------//
	
	//--------- 产品组合购买 start ----------//
	package_list_init:function(){
		frame_obj.select_all($('#combination .r_con_table input[name=select_all]'), $('#combination .r_con_table input[name=select]')); //批量操作
		frame_obj.del_bat($('.r_nav .del'), $('#combination .r_con_table input[name=select]'), sales_obj.package_del_bat_callback);
		
		$('#package_search_form').submit(function(){return false;});
		$('#package_search_form input:submit').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};
			$(this).attr('disabled', true);
			window.location='./?m=sales&a=package&'+$('#package_search_form').serialize();
		});
		
		$('#combination .del').off().click(function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				window.location=$this.attr('href');
			}, 'confirm');
			return false;
		});
	},
	
	package_del_bat_callback:function(id_list){
		var $this=$(this);
		global_obj.win_alert(lang_obj.global.del_confirm, function(){
			window.location='./?do_action=sales.package_del_bat&group_pid='+id_list;
		}, 'confirm');
		return false;
	},
	//--------- 产品组合购买 end ----------//
	
	//--------- 产品促销购买 start --------//
	promotion_list_init:function(){
		frame_obj.select_all($('#combination .r_con_table input[name=select_all]'), $('#combination .r_con_table input[name=select]')); //批量操作
		frame_obj.del_bat($('.r_nav .del'), $('#combination .r_con_table input[name=select]'), sales_obj.promotion_del_bat_callback);
		
		$('#promotion_search_form').submit(function(){return false;});
		$('#promotion_search_form input:submit').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};
			$(this).attr('disabled', true);
			window.location='./?m=sales&a=promotion&'+$('#promotion_search_form').serialize();
		});
		
		$('#combination .del').off().click(function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				window.location=$this.attr('href');
			}, 'confirm');
			return false;
		});
	},
	
	promotion_del_bat_callback:function(id_list){
		var $this=$(this);
		global_obj.win_alert(lang_obj.global.del_confirm, function(){
			window.location='./?do_action=sales.promotion_del_bat&group_pid='+id_list;
		}, 'confirm');
		return false;
	},
	//--------- 产品促销购买 end ----------//
	
	//--------- 优惠券部分 start ----------//
	coupon_list_init:function(){
		var arr=[
			{"name":lang_obj.global.n_y[0],"val":0},
			{"name":lang_obj.global.n_y[1],"val":1}
		];
		frame_obj.select_all($('input[name=select_all]'), $('input[name=select]')); //批量操作*/
		frame_obj.del_bat($('.r_nav .del'),$('#coupon .r_con_table input[name=select]'),sales_obj.del_bat_callback);
		
		//搜索
		$('#coupon_search_form').submit(function(){return false;});
		$('#coupon_search_form input:submit').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};
			$(this).attr('disabled', true);
			window.location='./?m=sales&a=coupon&'+$('#coupon_search_form').serialize();
		});
		
		$('#coupon .del').off().click(function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				window.location=$this.attr('href');
			}, 'confirm');
			return false;
		});
	},
	coupon_edit_init:function(){
		$('input[name=DeadLine]').daterangepicker({showDropdowns:true});//时间插件
		sales_obj.change_coupon_type(); //更改优惠券的模式 0.折扣 1.现金
		$('input[name=CouponWay]').click(function(){ //会员领取的时候不需要设置限制条件和生成数量
			if($(this).val()>0){
				$('.rows.couponway').hide();	
			}else{
				$('.rows.couponway').show();
			}
		});
		
		$('#coupon_form').submit(function(){return false;});
		$('#coupon_form input:submit').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};
			$(this).attr('disabled', true);
			$.post('?', $('#coupon_form').serialize(), function(data){
				$('#coupon_form input:submit').attr('disabled', false);
				if(data.ret!=1){
					global_obj.win_alert(data.msg);
				}else{
					window.location='./?m=sales&a=coupon';
				}
			}, 'json');
		});
		
		var tools_global={
			init: function(){
				$('.range_tools .current_list em').off().on('click', function(){ //删除选项
					$(this).parent().remove();
					tools_global.cur($(this).attr('data-type'));
					tools_global.save($(this).attr('data-type'));
				});
			},
			menu: function($obj, $tools, $type, $data){
				//返回按钮
				$obj.find('.btn_back').attr({'data-type':$data['data-type'], 'data-id':$data['data-id'], 'data-back-pro':$data['data-back-pro']});
				//点击选项
				$obj.find('.range_search_list .item').off().on('click', function(){
					//if($(this).attr('data-individual')==1) return false; //子类目，不触发
					if($(this).attr('data-individual')==1){
						$(this).find('.children').click();
						return false;
					}
					var s_type=$(this).attr('data-type'),
						s_id=$(this).attr('data-id');
					if($(this).hasClass('current')){ //取消
						$(this).removeClass('current');
						if($tools.find('.current_list[data-type='+s_type+'] .item[data-id='+s_id+']').size()){
							$tools.find('.current_list[data-type='+s_type+'] .item[data-id='+s_id+'] em').click();
						}
					}else{ //勾选
						$(this).addClass('current');
						if($tools.find('.current_list[data-type='+s_type+'] .item[data-id='+s_id+']').size()<1){
							var html='<div class="item" data-id="'+s_id+'"><i class="icon icon_'+s_type+'"></i><span>'+$(this).find('span').text()+'</span><em data-type="'+s_type+'">X</em></div>';
							if(s_id=='-1' || (s_id>0 && $tools.find('.current_list[data-type='+s_type+'] .item[data-id=-1]').length)){
								$tools.find('.current_list[data-type='+s_type+']').html(html);
							}else{
								$tools.find('.current_list[data-type='+s_type+']').append(html);
							}
							tools_global.init();
							tools_global.cur(s_type);
							tools_global.save(s_type);
						}
					}
					return false;
				});
				//产品分类 下一级选项
				$obj.find('.range_search_list .children').off().on('click', function(){
					if($(this).parent().attr('data-individual')==1) return false; //子类目，不触发
					tools_global.load_post($obj, {'Type':$type, 'CateId':$(this).parent().attr('data-id')}, function(data){
						tools_global.menu($obj, $tools, $type, data.msg.Back);
					});
					return false;
				});
				//子目录
				$obj.find('.range_search_list .item[data-individual=1] .children').off().on('click', function(){
					var s_type=$(this).parent().attr('data-type');
					tools_global.load_post($obj, {'Type':s_type, 'Search':'', 'IsCate':(s_type=='products_category'?1:0), 'IsUser':(s_type=='user'?1:0)}, function(data){
						tools_global.menu($obj, $tools, s_type, data.msg.Back);
						if(s_type=='user' || s_type=='level'){
							var s_obj=$('input[name=SearchUser]');
						}else{
							var s_obj=$('input[name=SearchApply]');
						}
						s_obj.attr('data-type', s_type).parent().show().prev('.btn_back').show();
					});
					return false;
				});
				//返回
				$obj.find('.btn_back').off().on('click', function(){
					var s_type=$(this).attr('data-type');
					var is_back_pro=$(this).attr('data-back-pro');
					if(is_back_pro==1){
						var json={'Type':s_type, 'CateId':$(this).attr('data-id')}
					}else{
						var json={'Type':s_type, 'Search':'', 'IsLevel':1}
					}
					tools_global.load_post($obj, json, function(data){
						tools_global.menu($obj, $tools, s_type, data.msg.Back);
						if(s_type=='user' || s_type=='level'){
							var s_obj=$('input[name=SearchUser]');
						}else{
							var s_obj=$('input[name=SearchApply]');
						}
						s_obj.attr('data-type', s_type).parent().hide().prev('.btn_back').hide();
					});
					return false;
				});
			},
			load_post: function(obj, data, callback){ //数据传递
				obj.find('.range_search_skin').show();
				obj.find('.range_search_list').html(' ');
				setTimeout(function(){
					$.post('./?do_action=sales.coupon_range_tools', data, function(result){
						if(result.ret==1){
							obj.find('.range_search_skin').hide();
							obj.find('.range_search_list').html(result.msg.Html);
							callback(result);
						}
					}, 'json');
				}, 500);
			},
			cur: function(type){ //勾选显示
				var obj='.range_user', val='', i=0;
				if(type=='products_category' || type=='products' || type=='tags'){
					obj='.range_apply';
				}
				$(obj+' .current_list').each(function(){
					if($(this).find('.item').size()>0){
						val+=(i?', ':'')+lang_obj.manage.sales.coupon[$(this).attr('data-type')];
						++i;
					}
				});
				$(obj+' .range_search>input').val(val);
			},
			save: function(type){ //数据保存记录
				var val='|', obj='', name='';
				if(type=='products_category'){
					obj='.range_apply';
					name='CateId';
				}else if(type=='products'){
					obj='.range_apply';
					name='ProId';
				}else if(type=='tags'){
					obj='.range_apply';
					name='TagId';
				}else if(type=='level'){
					obj='.range_user';
					name='LevelId';
				}else{
					obj='.range_user';
					name='UserId';
				}
				$(obj+' .current_list[data-type='+type+'] .item').each(function(){
					val+=$(this).attr('data-id')+'|';
				});
				$('input[name='+name+']').val(val);
			}
		}
		//触发下拉框焦点
		$('.range_tools .range_search>input').focus(function(){
			var $tools=$(this).parents('.range_tools'),
				$obj=$tools.find('.range_search_menu'),
				$type=$obj.find('.form_input').attr('data-type'),
				$data={'data-type':$obj.find('.btn_back').attr('data-type'), 'data-id':$obj.find('.btn_back').attr('data-id'), 'data-back-pro':$obj.find('.btn_back').attr('data-back-pro')};
			tools_global.menu($obj, $tools, $type, $data);
			if($('.range_search_fixed').is(':visible')){ //如果有多余的下拉框展开，默认全都收起来
				$('.range_search_fixed').hide();
			}
			$(this).next().show();
		});
		//搜索文本框
		$('.range_tools .price_input>input').keydown(function(e){
			var key=window.event?e.keyCode:e.which;
			if(key==13){ //回车按键
				$(this).next('.last').click();
				return false;
			}
		});
		//搜索按钮
		$('.range_tools .price_input .last').click(function(){
			var $tools		= $(this).parents('.range_tools'),
				$obj		= $(this).parents('.range_search_menu'),
				$search		= $obj.find('.form_input').val(),
				$type		= $obj.find('.form_input').attr('data-type');
			tools_global.load_post($obj, {'Search':$search, 'Type':$type}, function(data){
				tools_global.menu($obj, $tools, $type, data.msg.Back);
			});
		});
		//取消下拉框焦点
		$(document)[0].addEventListener('click', function(e){
			var $obj=$(e.target);
			if(!$obj.parents('.range_search').length && $('.range_search_fixed').is(':visible')){
				$('.range_search_fixed').hide();
			}
		}, false);
		tools_global.init();
		$('.range_user .price_input .last').click().parent().hide().prev('.btn_back').hide(); //默认点击一次会员搜索，并隐藏整个搜索框和返回按钮
		$('.range_apply .price_input .last').click().parent().hide().prev('.btn_back').hide(); //默认点击一次产品搜索，并隐藏整个搜索框和返回按钮
	},
	change_coupon_type:function(){ //更改优惠券的模式 0.折扣 1.现金
		$('#coupon_form input[name="CouponType"]').click(function(){
			var value = $(this).val();
			if(value=='0'){
				$('#coupon_form .discount').show();
				$('#coupon_form .money').hide();
			}else{
				$('#coupon_form .discount').hide();
				$('#coupon_form .money').show();
			}
		})
	},
	del_bat_callback:function(id_list){
		var $this=$(this);
		global_obj.win_alert(lang_obj.global.del_confirm, function(){
			window.location='./?do_action=sales.coupon_del_dat&group_cid='+id_list;
		}, 'confirm');
		return false;
	},
	coupon_explode_init:function(){
		$('input[name=DeadLine]').daterangepicker({showDropdowns:true});//时间插件
		$('#edit_form input[name="CouponType"]').click(function(){
			if($(this).val()=='1'){
				$('#edit_form .discount').removeClass('none');
				$('#edit_form .money').addClass('none');
			}else if($(this).val()=='2'){
				$('#edit_form .discount').addClass('none');
				$('#edit_form .money').removeClass('none');
			}else{
				$('#edit_form .discount, #edit_form .money').addClass('none');
			}
		});
		$obj=$('#edit_form');
		$obj.find('input:submit').click(function(){
			if(global_obj.check_form($obj.find('*[notnull]'), $obj.find('*[format]'))){return false;};
			$(this).attr('disabled', true);
			window.location='./?do_action=sales.coupon_explode'+$obj.serialize();
			$(this).attr('disabled', false);
		});
	},
	//--------- 优惠券部分 end ----------//

	//--------- 全场满减 end ----------//
	discount_init:function(){
		frame_obj.switchery_checkbox();
		$('input[name=DeadLine]').daterangepicker({showDropdowns:true});//时间插件
		$('#discount_form input[name="Type"]').click(function(){	//更改优惠券的模式 0.折扣 1.现金
			var value = $(this).val();
			if(value=='0'){
				$('#discount_list .discount').show();
				$('#discount_list .money').hide();
			}else{
				$('#discount_list .discount').hide();
				$('#discount_list .money').show();
			}
		});
		$('#add_discount').click(function(){//添加条件
			var Type=parseInt($('#discount_form input[name="Type"]:checked').val()),
				newrow=document.getElementById('discount_list').insertRow(-1);
			newcell=newrow.insertCell(-1);
			newcell.innerHTML=lang_obj.manage.sales.condition+':<span class="price_input"><b>'+ueeshop_config.currSymbol+'<div class="arrow"><em></em><i></i></div></b><input name="UseCondition[]" value="0" type="text" class="form_input" maxlength="10" size="5"></span>&nbsp;&nbsp;<span class="discount'+(Type==0?'':' none')+'">'+lang_obj.manage.sales.discount+':<span class="price_input"><input name="Discount[]" value="100" type="text" class="form_input" maxlength="3" size="5"><b class="last">%</b></span></span>&nbsp;&nbsp;<span class="money'+(Type==1?'':' none')+'">'+lang_obj.manage.sales.money+':<span class="price_input"><b>'+ueeshop_config.currSymbol+'<div class="arrow"><em></em><i></i></div></b><input name="Money[]" value="0" type="text" class="form_input" maxlength="5" size="5"></span></span> <a class="d_del" href="javascript:;" onclick="document.getElementById(\'discount_list\').deleteRow(this.parentNode.parentNode.rowIndex);"><img src="/static/ico/del.png" hspace="5" /></a>';
		});
		$('#discount_list .d_del').on('click', function(){
			$(this).parents('tr').remove();
		});
		$('#discount_form').submit(function(){return false;});
		$('#discount_form input:submit').click(function(){
			if(global_obj.check_form($('*[notnull]'))){return false;};
			$(this).attr('disabled', true);
			$.post('?', $('#discount_form').serialize(), function(data){
				$('#discount_form input:submit').attr('disabled', false);
				if(data.ret!=1){
					global_obj.win_alert(data.msg);
				}else{
					window.location='./?m=sales&a=discount';
				}
			}, 'json');
		});
	},
	//--------- 全场满减 end ----------//
	batch_edit:function(url){
		$('.r_nav .bat_close').on('click', function(){
			var id_list='';
			$('input[name=select]').each(function(index, element) {
				id_list+=$(element).get(0).checked?$(element).val()+'-':'';
            });
			if(id_list){
				id_list=id_list.substring(0,id_list.length-1);
				window.location=url+'&d=batch_edit&id_list='+id_list;
			}else{
				global_obj.win_alert(lang_obj.global.dat_select);
			}
		});
	}	
}