/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

var products_obj={
	products_init:function(){
		frame_obj.del_init($('#products .r_con_table'));
		frame_obj.select_all($('input[name=select_all]'), $('input[name=select]')); //批量操作
		frame_obj.del_bat($('.r_nav .del'), $('input[name=select]'), function(id_list){
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('?', {do_action:'products.products_del_bat', group_proid:id_list}, function(data){
					if(data.ret==1){
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
		frame_obj.del_bat($('.r_nav .sold_in'), $('input[name=select]'), function(id_list){
			global_obj.win_alert(lang_obj.global.sold_in_confirm, function(){
				$.get('?', {do_action:'products.products_sold_bat', group_proid:id_list, sold:0}, function(data){
					if(data.ret==1){
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
			return false;
		}, lang_obj.global.dat_select);
		frame_obj.del_bat($('.r_nav .sold_out'), $('input[name=select]'), function(id_list){
			global_obj.win_alert(lang_obj.global.sold_out_confirm, function(){
				$.get('?', {do_action:'products.products_sold_bat', group_proid:id_list, sold:1}, function(data){
					if(data.ret==1){
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
			return false;
		}, lang_obj.global.dat_select);
		frame_obj.select_all($('.r_nav input[name=custom_all]'), $('.r_nav input[class=custom_list][disabled!=disabled]')); //批量操作
		frame_obj.submit_form_init($('.r_nav .ico form'), './?m=products&a=products');
		$('#products .r_con_table .copy').click(function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.copy_confirm, function(){
				$.get($this.attr('href'), function(data){
					if(data.ret==1){
						window.location='./?m=products&a=products&d=edit&ProId='+data.msg;
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
		$('.r_nav .bat_close').on('click', function(){
			var id_list='';
			$('input[name=select]').each(function(index, element) {
				id_list+=$(element).get(0).checked?$(element).val()+'-':'';
            });
			if(id_list){
				id_list=id_list.substring(0,id_list.length-1);
				window.location='./?m=products&a=products&d=batch_edit&proid_list='+id_list;
			}else{
				window.location='./?m=products&a=products&d=batch_edit'
			}
		});
		$('#products .r_con_table .category_select').on('dblclick', function(){
			var $obj=$(this),
				$cateid=$obj.attr('cateid'),
				$ProId=$obj.parents('tr').find('td:eq(0)>input').val(),
				$mHtml=$obj.html(),
				$sHtml=$('#category_select_hide').html(),
				$val;
			$obj.html($sHtml+'<span style="display:none;">'+$mHtml+'</span>');
			$cateid && $obj.find('select').val($cateid).focus();
			$obj.find('select').on('blur', function(){
				$val=$(this).val();
				if($val!=$cateid){
					$.post('?', 'do_action=products.products_edit_category&ProId='+$ProId+'&CateId='+$(this).val(), function(data){
						if(data.ret==1){
							$obj.html(data.msg);
							$obj.attr('cateid', $val);
						}
					}, 'json');
				}else{
					$obj.html($obj.find('span').html());
				}
			});
		});
		$('#products .r_con_table .price_input>div').on('dblclick', function(){
			var $obj=$(this),
				$price=$obj.attr('price'),
				$name=$obj.attr('class'),
				$ProId=$obj.parents('tr').find('td:eq(0)>input').val(),
				pHtml, $val;
			pHtml='<input name="'+$name+'" value="'+$price+'" type="text" class="form_input" size="5" maxlength="10" rel="amount" notnull />';
			$obj.find('span').html(pHtml);
			$obj.find('input').focus();
			$obj.find('input').on('blur', function(){
				$val=$(this).val();
				$.post('?', 'do_action=products.products_edit_price&ProId='+$ProId+'&Type='+$name+'&Price='+$(this).val(), function(data){
					if(data.ret==1){
						$obj.find('input').remove();
						$obj.find('span').html($val);
					}
				}, 'json');
			});
		});
		$('#products .r_con_table .myorder_select').on('dblclick', function(){
			var $obj=$(this),
				$number=$obj.attr('data-num'),
				$ProId=$obj.parents('tr').find('td:eq(0)>input').val(),
				$mHtml=$obj.html(),
				$sHtml=$('#myorder_select_hide').html(),
				$val;
			$obj.html($sHtml+'<span style="display:none;">'+$mHtml+'</span>');
			$number && $obj.find('select').val($number).focus();
			$obj.find('select').on('blur', function(){
				$val=$(this).val();
				if($val!=$number){
					$.post('?', 'do_action=products.products_edit_myorder&ProId='+$ProId+'&Number='+$(this).val(), function(data){
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
	},
	
	products_edit_init:function(){
		//初始化
		var IsOverseas=parseInt($('#IsOverseas').val());
		
		var resize=function(){
			//提交按钮固定于底部
			$('#edit_form input:submit.btn_ok').parent().parent().addClass('submit_btn_fixed');
			$('#edit_form .submit_btn_fixed').css({width:$('#main').width()-$('#main .menu').width(), left:$('#main .menu').width()});
			var mainHeight=$('#main').height()-$('#main .r_nav').height()-$('#edit_form .submit_btn_fixed').outerHeight()-20;
			$('#main .r_con_wrap').css({height:mainHeight});
			$('#edit_form').css({minHeight:mainHeight});
		}
		resize();
		$(window).resize(function(){resize();});
		
		$('.edit_form_part a').on('click', function(){
			var $name=$(this).attr('data-name');
			var obj = $('.pro_box_'+$name).get(0);
			$('#products').animate({scrollTop:parseInt(obj.offsetTop-obj.parentNode.offsetTop)}, 300);
			//$name=='seo_info' && frame_obj.rows_input();
		});
		
		products_obj.products_edit_load_attr();	//加载产品属性
		$('#edit_form input[name=PromotionTime]').daterangepicker({showDropdowns:true});
		$('#edit_form input[name=SoldOutTime]').daterangepicker({showDropdowns:true});
		$('#edit_form select[name=CateId]').on('change', function(){products_obj.products_edit_category_select($(this).val(), '');});	//产品分类选择
		$('#expand_btn').on('click', function(){
			var obj=$(this).nextAll('.expand_list');
			var category_sel=$('#edit_form select[name=CateId]').html();
			obj.append('<div><select name="ExtCateId[]">'+category_sel.replace(' selected','')+'</select><a class="close" href="javascript:;"><img src="/static/ico/no.png" /></a></div>');
			$('.expand_list .close').on('click', function(){
				$(this).parent().remove();
			});
		});
		$('.expand_list .close').on('click', function(){
			$(this).prev().remove();
			$(this).remove();
		});
		$('input[name=Price_1]').on('blur', function(){//商城价改变，批发价折扣也自动改变
			var $Price_1=parseFloat($(this).val());
			$('#wholesale_price_list input[name=Price\\[\\]]').each(function(){
				var $Price=parseFloat($(this).val());
				var $discount=0;
				if($Price_1>0){
					$discount=(($Price/$Price_1)*100).toFixed(3);
					$discount=$discount.lastIndexOf('.')>0 && $discount.length-$discount.lastIndexOf('.')-1>1?$discount.substring(0, $discount.lastIndexOf('.')+2):parseFloat($discount).toFixed(1);
					$discount<0 && ($discount=0);
					$discount>100 && ($discount=100);
				}
				isNaN($discount) && ($discount=0);
				$(this).siblings('.wholesale_discount').text($discount);
			});
		});
		$('#add_wholesale').click(function(){//添加批发价
			if($(this).parents('table').find('tr').size()>5) return false;
			var newrow=document.getElementById('wholesale_price_list').insertRow(-1);
			newcell=newrow.insertCell(-1);
			newcell.innerHTML=lang_obj.manage.products.qty+': <input type="text" name="Qty[]" value="" class="form_input" size="5" maxlength="5" rel="amount" /> '+lang_obj.manage.products.price+': '+ueeshop_config.currSymbol+'<input type="text" name="Price[]" value="" class="form_input" size="5" maxlength="10" rel="amount" /> <a href="javascript:;" onclick="document.getElementById(\'wholesale_price_list\').deleteRow(this.parentNode.parentNode.rowIndex);"><img src="/static/ico/del.png" hspace="5" /></a> '+lang_obj.manage.products.discount+': <span class="wholesale_discount">0</span>% <span class="tool_tips_ico" content="'+lang_obj.manage.products.wholesale_discount_notes+'"></span>';
			$('#main .r_con_wrap').tool_tips($(newcell).find('.tool_tips_ico'), {position:'horizontal', html:$(newcell).find('.tool_tips_ico').attr('content'), width:260}); //弹出提示
			$('#wholesale_price_list input[name=Price\\[\\]]').on('blur', function(){//批发价折扣
				var $Price_1=parseFloat($('input[name=Price_1]').val());
				var $discount=0;
				if($Price_1>0){
					$discount=(($(this).val()/$Price_1)*100).toFixed(3);
					$discount=$discount.lastIndexOf('.')>0 && $discount.length-$discount.lastIndexOf('.')-1>1?$discount.substring(0, $discount.lastIndexOf('.')+2):parseFloat($discount).toFixed(1);
					$discount<0 && ($discount=0);
					$discount>100 && ($discount=100);
				}
				$(this).siblings('.wholesale_discount').text($discount);
			});
		});
		$('#wholesale_price_list input[name=Price\\[\\]]').on('blur', function(){//批发价折扣
			var $Price_1=parseFloat($('input[name=Price_1]').val());
			var $discount=0;
			if($Price_1>0){
				$discount=(($(this).val()/$Price_1)*100).toFixed(3);
				$discount=$discount.lastIndexOf('.')>0 && $discount.length-$discount.lastIndexOf('.')-1>1?$discount.substring(0, $discount.lastIndexOf('.')+2):parseFloat($discount).toFixed(1);
				$discount<0 && ($discount=0);
				$discount>100 && ($discount=100);
			}
			$(this).siblings('.wholesale_discount').text($discount);
		});
		$('#wholesale_price_list .w_del').on('click', function(){
			$(this).parents('tr').remove();
		});
		/* 产品图片上传 */
		frame_obj.mouse_click($('.multi_img .upload_btn, .pic_btn .edit'), 'pro', function($this){ //产品主图点击事件
			var $num=$this.parents('.img').attr('num');
			frame_obj.photo_choice_init('PicUpload_'+$num, '', 'PicDetail .img[num;'+$num+']', 'products', 10, 'do_action=products.products_img_del&Model=products');
		});
		$('.multi_img input[name=PicPath\\[\\]]').each(function(){
			if($(this).attr('save')==1){
				$(this).parent().append(frame_obj.upload_img_detail($(this).attr('data-value'))).children('.upload_btn').hide();
			}
		});
		$('.multi_img').dragsort({
			dragSelector:'dl',
			dragSelectorExclude:'',
			placeHolderTemplate:'<dl class="img placeHolder"></dl>',
			scrollSpeed:5
		});
		frame_obj.mouse_click($('.pic_btn .del'), 'proDel', function($this){ //产品主图删除点击事件
			var $obj=$this.parents('.img'),
				$num=parseInt($obj.attr('num')),
				$path=$obj.find('input[name=PicPath\\[\\]]').val();
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.ajax({
					url:'./?do_action=products.products_img_del&Model=products&Path='+$path+'&Index='+$num+'&ProId='+$('#ProId').val(),
					success:function(data){
						json=eval('('+data+')');
						$('#PicDetail dl[num='+json.msg[0]+'] .preview_pic .upload_btn').show();
						$('#PicDetail dl[num='+json.msg[0]+'] .preview_pic a').remove();
						$('#PicDetail dl[num='+json.msg[0]+'] .preview_pic input[name=PicPath\\[\\]]').val('').attr('save', 0);
					}
				});
			}, 'confirm');
		});
		
		//单位
		$('.unit_box .add_unit').click(function(){
			var $obj=$('.unit_box');
			if($obj.hasClass('show')){
				$obj.removeClass('show');
			}else{
				$obj.addClass('show');
			}
		});
		$('.unit_box .list .item').click(function(){
			$('input[name=Unit]').val($(this).find('span').text());
			$('.unit_box').removeClass('show');
		});
		$('.unit_box .list .item>em').click(function(){
			var o=$(this), key=o.parent().attr('data-key');
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.post('?', {"do_action":"products.products_unit_del", "Key":key, "Unit":o.prev().text()}, function(data){
					json=eval('('+data+')');
					if(json.ret==1){
						o.parent().remove();
					}
					return false;
				});
			}, 'confirm');
			return false;
		});
		
		$('#edit_form .valign .choice_btn').click(function(){
			var $this=$(this);
			$this.addClass('current').siblings().removeClass('current');
			$this.children('input').attr('checked', true);
		});
		frame_obj.switchery_checkbox(function(obj){
			if(obj.find('input[name=SoldOut]').length){
				$('#sold_out_div').css('display', 'none');
			}else if(obj.find('input[name=IsSoldOut]').length){
				obj.nextAll('.sold_in_time').css('display', '');
			}else if(obj.find('input[name=IsPromotion]').length){
				$('#promotion_div').css('display', '');
			}else if(obj.find('input[name=IsDefaultReview]').length){
				$('#default_review_div').css('display', '');
			}else if(obj.find('input[name=IsPacking]').length){
				$('#packing_div').css('display', '');
			}else if(obj.find('input.desc_tab_btn').length){
				obj.next().addClass('show').removeClass('hide');
			}else if(obj.find('input[name=IsCombination]').length){ //开启组合
				$('#AttrId_0').show().siblings('tbody').hide();
				$('#AttrId_0 input').attr('disabled', false);
				$('#AttrId_0 tr').each(function(){
					$(this).find('td:gt(0)').show();
				});
				$('#attribute_ext thead').find('td:eq(1), td:eq(2), td:gt(3)').show();
				$('#attribute_ext thead td:eq(3)').text(lang_obj.manage.products.price+'('+ueeshop_config.currSymbol+')'); //价格
				$('#attribute_ext tbody[id!=AttrId_0] tr').each(function(){
					$(this).find('td:eq(3) input').attr('disabled', true);
					$(this).find('td:eq(1), td:eq(2), td:gt(3)').show().find('input').attr('disabled', false);
				});
				$('#AttrId_XXX td, tbody.contents td:eq(1), tbody.contents td:eq(2), tbody.contents td:gt(3)').show();
				if(IsOverseas==1){ //开启海外仓功能
					$('#AttrId_Overseas, #overseas_rows').show();
				}
				
				if($('#attribute_ext_box .tab_box_row .tab_box_btn').length>0){ //有选择发货地
					$('#attribute_ext_box .tab_box_row.show').show();
					$('#attribute_ext .relation_box').each(function(){
						if($(this).hasClass('tab_txt')){
							$(this).show().find('input').attr('disabled', false);
						}else{
							$(this).hide().find('input').attr('disabled', true);
						}
					});
				}else{
					$('#attribute_ext_box .tab_box_row.show').hide();
					$('#attribute_ext .relation_box').each(function(){
						if($(this).hasClass('tab_txt')){
							$(this).hide().find('input').attr('disabled', true);
						}else{
							$(this).show().find('input').attr('disabled', false);
						}
					});
				}
				
				products_obj.products_edit_attr_init($('.attribute .choice_btn.current input').attr('data'), $('.attribute .choice_btn.current input').attr('id'), '', 1);
			}
		}, function(obj){
			if(obj.find('input[name=SoldOut]').length){
				$('#sold_out_div').css('display', '');
			}else if(obj.find('input[name=IsSoldOut]').length){
				obj.nextAll('.sold_in_time').css('display', 'none');
			}else if(obj.find('input[name=IsPromotion]').length){
				$('#promotion_div').css('display', 'none');
			}else if(obj.find('input[name=IsDefaultReview]').length){
				$('#default_review_div').css('display', 'none');
			}else if(obj.find('input[name=IsPacking]').length){
				$('#packing_div').css('display', 'none');
			}else if(obj.find('input.desc_tab_btn').length){
				obj.next().addClass('hide').removeClass('show');
			}else if(obj.find('input[name=IsCombination]').length){ //关闭组合
				$('#AttrId_0').hide().siblings('tbody').show();
				$('#AttrId_0 .group').remove();
				$('#attribute_ext thead').find('td:eq(1), td:eq(2), td:gt(3)').hide();
				$('#attribute_ext thead td:eq(3)').text(lang_obj.manage.products.mark_up+'('+ueeshop_config.currSymbol+')');//加价
				$('#attribute_ext tbody[id!=AttrId_0] tr').each(function(){
					$(this).find('td:eq(3) input').attr('disabled', false);
					$(this).find('td:eq(1), td:eq(2), td:gt(3)').hide().find('input').attr('disabled', true);
				});
				$('#AttrId_XXX td:eq(1),#AttrId_XXX td:eq(2), #AttrId_XXX td:gt(3),#attribute_tmp tbody.contents td:eq(1),#attribute_tmp tbody.contents td:eq(2),#attribute_tmp tbody.contents td:gt(3)').hide();
				$('#AttrId_Overseas, #overseas_rows').hide();
				
				$('#attribute_ext_box .tab_box_row.show').hide();
				$('#attribute_ext .relation_box').each(function(){
					if($(this).hasClass('tab_txt')){
						$(this).hide().find('input').attr('disabled', true);
					}else{
						$(this).show().find('input').attr('disabled', false);
					}
				});
				$('#AttrId_0 input').attr('disabled', true);
			}
		});
		$('#edit_form input[name="PromotionType"]').click(function(){
			if($(this).val()=='0'){
				$('#edit_form .promotion_money').show().find('input').removeAttr('disabled');
				$('#edit_form .promotion_discount').hide().find('input').attr('disabled', 'disabled');
			}else{
				$('#edit_form .promotion_money').hide().find('input').attr('disabled', 'disabled');
				$('#edit_form .promotion_discount').show().find('input').removeAttr('disabled');
			}
		})
		$('#edit_form .other_btns .choice_btn').click(function(){
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
		frame_obj.submit_form_init($('#edit_form'), './?m=products&a=products', function(){
			var $name='';
			$('#edit_form *[notnull]').each(function(){
				if($.trim($(this).val())==''){
					$name=$(this).parents('.pro_box').attr('data-name');
					$('.edit_form_part a[data-name='+$name+']').click();
					return false;
				}
			});
			return true;
		}, '', function(result){
			if(result.ret==1){
				if(result.msg.jump){ //保存产品资料
					window.location=result.msg.jump;
				}else if(result.msg.tips){ //保存草稿箱
					global_obj.win_alert(result.msg.tips, '', 'alert');
					$('#ProId').val(result.msg.ProId);
					$('#save_drafts').val(0);
				}else{
					window.location.reload();
				}
			}else{
				$('#edit_form').find('input:submit').attr('disabled', false);
				global_obj.win_alert(result.msg, '', 'alert');
			}
		});
		frame_obj.submit_form_init($('#model_edit_form'), '', function(){
			var lang='', obj, num;
			$('.tab_txt').each(function(){
				obj=$(this);
				lang=obj.attr('lang');
				num=0;
				$(this).find('*[notnull]').each(function(){
					if($.trim($(this).val())=='') ++num;
				});
				if(num>0){
					obj.parent().find('.tab_box_row .tab_box_btn[data-lang='+lang+']').css({'animation':'null .3s 2 0s linear forwards', '-webkit-animation':'null .3s 2 0s linear forwards'});
				}else{
					obj.parent().find('.tab_box_row .tab_box_btn[data-lang='+lang+']').removeAttr('style');;
				}
			});
			return true;
		}, '', function(result){
			$('#model_edit_form input:submit').attr('disabled', false);
			if(result.ret==1){
				var AttrId=result.msg.AttrId,
					CateId=parseInt($('#edit_form select[name=CateId]').val()),
					IsCartAttr=parseInt($('#model_edit_form').attr('data-cart'));
				$('#model_edit_form .btn_cancel').click();
				$.post('?', {"do_action":"products.products_get_attr", "AttrId":AttrId, "CateId":CateId, "Type":1, "IsCartAttr":IsCartAttr}, function(data){//普通属性
					json=eval('('+data+')');
					if(json.ret==1){
						if(IsCartAttr){ //购物车属性
							$('#edit_form .attribute').attr('attrid', json.msg[2]).append(json.msg[0].toString());
							$('#all_attr').val(json.msg[1].toString());
							$('#attribute_hide, input[name=ParentId]').val(json.msg[2]);
							if(!$('#edit_form input[id=attribute_save_'+json.msg[2]+']').length) $('#edit_form').append('<input type="hidden" id="attribute_save_'+json.msg[2]+'" value="" />');
							products_obj.products_edit_load_attr();
						}else{ //普通属性
							$('#edit_form .attribute_list').attr('attrid', json.msg[2]).append(json.msg[0].toString());
							products_obj.products_edit_load_attr();
						}
					}
					return false;
				});
			}else{
				alert(result.msg);
			}
		});
		frame_obj.submit_form_init($('#attribute_edit_form'), '', '', '', function(result){
			var AttrId=result.msg.AttrId,
				VId=result.msg.VId,
				CateId=parseInt($('#edit_form select[name=CateId]').val()),
				IsCartAttr=parseInt($('#attribute_edit_form').attr('data-cart'));
			$('#attribute_edit_form input:submit').attr('disabled', false);
			$('#attribute_edit_form .btn_cancel').click();
			$.post('?', {"do_action":"products.products_get_attr", "AttrId":AttrId, "CateId":CateId, "VId":VId, "Type":2, "IsCartAttr":IsCartAttr}, function(data){//普通属性
				json=eval('('+data+')');
				if(json.ret==1){
					$('#edit_form .add_customize[data-id='+AttrId+']').before(json.msg[0].toString());
					if(IsCartAttr){ //购物车属性
						$('#all_attr').val(json.msg[1].toString());
						if(!$('#edit_form input[id=attribute_save_'+json.msg[2]+']').length) $('#edit_form').append('<input type="hidden" id="attribute_save_'+json.msg[2]+'" value="" />');
						products_obj.products_edit_load_attr();
						$('#Attr_'+VId).parent().click(); //默认立即勾选
					}else{ //普通属性
						products_obj.products_edit_load_attr();
					}
				}
				return false;
			});
		});
		//平台导流
		$('.platform_box .item').click(function(){
			$(this).toggleClass('item_cur');
			$('.platform_rows').eq($(this).index()).toggle().find('input').val('');
		});
		//自动存草稿箱
		$('.drafts_btn').off().on('click', function(){ //存草稿
			$('#save_drafts').val(1);
			$('#edit_form input:submit').click();
		});
		if($('.drafts_btn').length && parseInt($('#ProId').val())==0){
			var Time=10*60*1000; //10分钟
			setTimeout(function(){ //打开产品添加页面，过了3分钟后，自动存草稿
				$('.drafts_btn').click();
			}, Time);
		}
	},
	
	products_edit_load_attr:function(){	//产品属性显示
		$('.attribute_list .choice_btn, .attribute .choice_btn').off().on('click', function(){
			var obj=$(this).find('input');
			if($(this).hasClass('current')){
				$(this).removeClass('current');
				obj.attr('checked', false);
			}else{
				$(this).addClass('current');
				obj.attr('checked', true);
			}
			products_obj.products_edit_attr_init(obj.attr('data'), obj.attr('id'), '', 1);
		});
		$('.overseas .choice_btn').off().on('click', function(){
			var obj=$(this).find('input');
			if($(this).hasClass('current')){
				$(this).removeClass('current');
				obj.attr('checked', false);
			}else{
				$(this).addClass('current');
				obj.attr('checked', true);
			}
			products_obj.products_edit_attr_init(obj.attr('data'), obj.attr('id'), '', 1);
		});
		/* 属性自定义项编辑弹出框 */
		$('.add_customize').off().on('click', function(){
			var $AttrId=parseInt($(this).attr('data-id')),
				$AttrName=$(this).parent().prev().text(),
				$IsCartAttr=parseInt($(this).attr('data-cart')),
				$obj=$('.box_attribute_edit');
			frame_obj.pop_form($obj);
			frame_obj.rows_input();
			$obj.find('form').attr("data-cart", $IsCartAttr); //是否购物车属性
			$obj.find('.attribute_name').text($AttrName); //属性名称
			$obj.find('.rows input').val(''); //名称
			$obj.find('input[name=AttrId]').val($AttrId); //属性ID
			return false;
		});
	},
	
	products_edit_synchronize:function(){ //产品属性同步按钮
		$('#edit_form .synchronize_btn').off().on('click', function(){
			var $num=parseInt($(this).attr('data-num')),
				$obj=$(this).parents('tr'),
				$value=$obj.next('tr').find('td:eq('+($num+1)+') input').val();
			$obj.siblings('tr').find('td:eq('+($num+1)+') input').val($value);
		});
		/* 加价单选按钮 */
		$('#attribute_ext_box .switchery').off().on('click', function(){
			if($(this).hasClass('checked')){
				$(this).removeClass('checked').find('input').attr('checked', false);
				if($(this).hasClass('btn_increase_all')){ //总舵
					$(this).parents('tbody').find('.switchery').removeClass('checked').find('input').attr('checked', false);
					$('#attribute_tmp .btn_increase_all').removeClass('checked').find('input').attr('checked', false);
					$('#attribute_tmp .contents .switchery').removeClass('checked').find('input').attr('checked', false);
				}else{ //分舵
					$(this).parents('tbody').find('tr:gt(0) .switchery').not('.checked').length>0 && $(this).parents('tbody').find('.btn_increase_all').removeClass('checked').find('input').attr('checked', false);
				}
			}else{
				$(this).addClass('checked').find('input').attr('checked', true);
				if($(this).hasClass('btn_increase_all')){ //总舵
					$(this).parents('tbody').find('.switchery').addClass('checked').find('input').attr('checked', true);
					$('#attribute_tmp .btn_increase_all').addClass('checked').find('input').attr('checked', true);
					$('#attribute_tmp .contents .switchery').addClass('checked').find('input').attr('checked', true);
				}else{ //分舵
					$(this).parents('tbody').find('tr:gt(0) .switchery').not('.checked').length==0 && $(this).parents('tbody').find('.btn_increase_all').addClass('checked').find('input').attr('checked', true);
				}
			}
		});
		$('#attribute_ext table').each(function(){
			var obj=$(this).find('tbody');
			if(obj.find('tr:gt(0) .switchery').not('.checked').length==0){
				obj.find('.btn_increase_all').addClass('checked').find('input').attr('checked', true);
			}else{
				obj.find('.btn_increase_all').removeClass('checked').find('input').attr('checked', false);
			}
		});
		/* 批量操作 */
		$('#attribute_ext_box .btn_batch').off().on('click', function(){
			var $obj=$('#box_batch_edit'), $batch_data=new Object, Html;
			$('#attribute_ext_box .batch_edit').html('');
			$(this).next('.batch_edit').html($obj.html()).show()
			$('#edit_form .attribute>.rows').each(function(){
				$Name=$(this).children('label').text(); //属性名称
				$id=$(this).find('.add_customize').attr('data-id'); //属性ID
				$batch_data[$id]=$Name;
			});
			$('.batch_edit>.rows .input').each(function(){
				Html='';
				$Name=$(this).attr('data-name');
				for(k in $batch_data){
					Html+='<p><input type="radio" name="'+$Name+'" value="'+k+'" /> 相同'+$batch_data[k]+'同步</p>';
				}
				$(this).html(Html);
			});
			$('.batch_edit .btn_batch_submit').off().on('click', function(){
				var o=$(this).parents('.batch_edit'), data=new Object, input_data=new Object, $name, $value;
				input_data['Price']=3;
				input_data['Stock']=4;
				o.find('.rows input:radio:checked').each(function(){
					$name=$(this).attr('name').replace('Batch', '');
					data[$name]=Object();
					data[$name]['ID']=$(this).val();
					data[$name]['No']=o.parents('tr').attr('data-attr-id-'+$(this).val());
					data[$name]['Value']=o.parents('tr').find('td:eq('+input_data[$name]+') input:text').val();
				});
				for(k in data){
					$value=k=='Price'?parseFloat(data[k]['Value']).toFixed(2):data[k]['Value'];
					$('#attribute_ext tr[data-attr-id-'+data[k]['ID']+'='+data[k]['No']+']').find('td:eq('+input_data[k]+') input:text').val($value);
				}
				$(this).parents('.batch_edit').html('').hide();
			});
			$('.batch_edit .btn_batch_cancel').off().on('click', function(){
				$(this).parents('.batch_edit').html('').hide();
			});
			$(document).off().on('click', function(e){
				if($('.batch_edit:visible').length){
					var $obj=$('.batch_edit:visible').parent();
					if(!(e.target==$obj[0] || $.contains($obj[0], e.target)) && $('.batch_edit:visible').length){
						$('.batch_edit').html('').hide();
					}
				}
			});
		});
		/* 单个属性 */
		/*if($('#AttrId_0').length==0){
			//!$('input[name=IsCombination]').is(':checked') && $('input[name=IsCombination]').parent().click();
			$('input[name=IsCombination]').parents('.box_combination').hide();
		}
		if($('#AttrId_0').length){
			$('input[name=IsCombination]').parents('.box_combination').show();
		}*/
	},
	
	products_edit_category_select:function(value, ProId){
		var AttrId=parseInt($('#edit_form .attribute').attr('attrid'));
		$('#model_edit_form input[name=CateId]').val(value);
		$.post('?', {"do_action":"products.products_get_attr", "AttrId":AttrId, "CateId":value, "ProId":ProId, "IsCartAttr":0}, function(data){//普通属性
			json=eval('('+data+')');
			if(json.ret==1){
				$('#edit_form .attribute_list').attr('attrid', json.msg[2]).html(json.msg[0].toString());
				products_obj.products_edit_load_attr();
			}
			return false;
		});
		$.post('?', {"do_action":"products.products_get_attr", "AttrId":AttrId, "CateId":value, "ProId":ProId, "IsCartAttr":1}, function(data){//购物车属性
			json=eval('('+data+')');
			if(json.ret==1){
				$('#edit_form .attribute').attr('attrid', json.msg[2]).html(json.msg[0].toString());
				$('#all_attr').val(json.msg[1].toString());
				$('#attribute_hide, input[name=ParentId]').val(json.msg[2]);
				$('#attribute_show').text(json.msg[3]?json.msg[3]:lang_obj.manage.products.category_tips);
				
				$('#check_attr').val('');
				$('#attribute_ext_box, #color_box').addClass('hide');
				$('#attribute_ext tbody, #color_box .relation_box tbody').remove();
				
				if(!$('#edit_form input[id=attribute_save_'+json.msg[2]+']').length) $('#edit_form').append('<input type="hidden" id="attribute_save_'+json.msg[2]+'" value="" />');
				products_obj.products_edit_load_attr();
				if(json.msg[4]){ //执行返回函数
					eval(json.msg[4]);
				}else{
					var save_attr=global_obj.json_encode_data($('#attribute_save_'+json.msg[2]).val());
					var key='';
					if(save_attr){
						for(k in save_attr){
							key=k;
							for(i=0; i<save_attr[key].length; ++i){
								if($('#Attr_'+key+'_'+save_attr[key][i]).length){
									$('#Attr_'+key+'_'+save_attr[key][i]).parent('.choice_btn').click();
								}
							}
						}
					}
				}
			}
			return false;
		});
	},
	
	products_edit_attr_init:function(data, id, value, ischeck){
		var dataObj=eval("("+data+")");
		var obj=$('input[id="'+id+'"]');
		var val_ary=value.split(",");
		var all_attr=global_obj.json_encode_data($('#all_attr').val());
		var ext_attr=global_obj.json_encode_data($('#ext_attr').val());
		var attrid=parseInt($('#attribute_hide').val());
		var IsCombination=$('input[name=IsCombination]').is(':checked');
		var IsOverseas=parseInt($('#IsOverseas').val());
		if(ischeck==0 || obj.is(':checked')){
			obj.parent().addClass('current');
		}else{
			obj.parent().removeClass('current');
		}
		var check_attr=global_obj.json_encode_data($('#check_attr').val());
		if(!check_attr) check_attr=new Object();
		var save_attr=global_obj.json_encode_data($('#attribute_save_'+attrid).val());
		if(!save_attr) save_attr=new Object();
		var check_attr_len=save_attr_len=0;
		if(ischeck==0 || obj.is(':checked')){
			for(k in check_attr[dataObj.AttrId]){//删除为已重复的属性
				if(check_attr[dataObj.AttrId][k]==dataObj.Num){
					check_attr[dataObj.AttrId].splice(k, 1);
				}
			}
			if(!check_attr[dataObj.AttrId]){
				check_attr[dataObj.AttrId]=new Array();
				check_attr[dataObj.AttrId][0]=dataObj.Num;
			}else{
				check_attr[dataObj.AttrId][check_attr[dataObj.AttrId].length]=dataObj.Num;
			}
			if(!save_attr[dataObj.AttrId]){
				save_attr[dataObj.AttrId]=new Array();
				save_attr[dataObj.AttrId][0]=dataObj.Num;
			}else{
				if(!global_obj.in_array(dataObj.Num, save_attr[dataObj.AttrId])){
					save_attr[dataObj.AttrId][save_attr[dataObj.AttrId].length]=dataObj.Num;
				}
			}
		}else{
			for(k in check_attr[dataObj.AttrId]){//删除为空的属性
				if(check_attr[dataObj.AttrId].length<2){
					check_attr[dataObj.AttrId]=undefined;
					delete check_attr[dataObj.AttrId];
					break;
				}else if(check_attr[dataObj.AttrId][k]==dataObj.Num){
					check_attr[dataObj.AttrId].splice(k, 1);
					break;
				}
			}
			for(k in save_attr[dataObj.AttrId]){//删除为空的属性
				if(save_attr[dataObj.AttrId].length<2){
					save_attr[dataObj.AttrId]=undefined;
					delete save_attr[dataObj.AttrId];
					break;
				}else if(save_attr[dataObj.AttrId][k]==dataObj.Num){
					save_attr[dataObj.AttrId].splice(k, 1);
					break;
				}
			}
		}
		dataObj.Cart==1 && $('#check_attr').val(global_obj.json_decode_data(check_attr));
		$('#attribute_save_'+attrid).val(global_obj.json_decode_data(save_attr));
		for(k in check_attr){ //统计勾选的属性项目数量
			check_attr_len+=1;
		}
		//开启海外仓，允许默认海外仓填写；关闭海外仓，不允许默认海外仓填写
		if(dataObj.Cart==1 && (IsOverseas==1 || (IsOverseas==0 && dataObj.Num!='Ov:1')) && (ischeck==0 || obj.is(':checked'))){
			if($('#AttrId_'+dataObj.AttrId).length==0){
				var html_t=$('#attribute_tmp .column').html();
				html_t=html_t.replace('XXX', dataObj.AttrId).replace('Column', dataObj.Column.replace(/@8#/g, "'"));
				$('#attribute_ext .relation_box').append(html_t);
			}
			if($('#attribute_ext tr[id="VId_'+dataObj.Num+'"]').length==0){
				var html_c=$('#attribute_tmp .contents').html();
				var p_v=val_ary[0]?val_ary[0]:0; //价格
				var s_v=val_ary[1]?val_ary[1]:0; //库存
				var w_v=val_ary[2]?val_ary[2]:0; //重量
				var u_v=val_ary[3]?val_ary[3]:''; //SKU
				var is=val_ary[4]?val_ary[4]:0; //加价
				if(dataObj.AttrId=='Overseas'){ //发货地
					html_c=html_c.replace(/XXX/g,dataObj.Num).replace('Name', dataObj.Name).replace('p_v', p_v).replace('s_v', s_v).replace('w_v', w_v).replace('u_v', u_v);
				}else{
					html_c=html_c.replace(/XXX/g,dataObj.Num).replace('Name', dataObj.Name.replace(/@8#/g, "'")).replace('p_v', p_v).replace('s_v', s_v).replace('w_v', w_v).replace('u_v', u_v);
				}
				if(is==1){ //勾选
					html_c=html_c.replace('class="switchery"', 'class="switchery checked"').replace('type="checkbox"', 'type="checkbox" checked=""');
				}
				$('#AttrId_'+dataObj.AttrId).append(html_c);
			}
		}else{
			if($('#attribute_ext tr[id="VId_'+dataObj.Num+'"]').length>0){
				$('#attribute_ext tr[id="VId_'+dataObj.Num+'"]').remove();
			}
			if($('#AttrId_'+dataObj.AttrId+' tr').length==1){
				$('#AttrId_'+dataObj.AttrId).remove();
			}
		}
		//发货地选项的显示 Start
		$('#AttrId_Overseas').hide();
		$('#attribute_ext_box .tab_box_row.show').hide();
		if(dataObj.Cart==1 && IsCombination==1){
			if(check_attr['Overseas'] && check_attr_len==1 && IsOverseas==1){ //仅有发货地
				$('#AttrId_Overseas').show();
			}else if(check_attr['Overseas']){
				$('#attribute_ext_box .tab_box_row.show').show();
			}
			$('#attribute_ext .relation_box').each(function(){
				if($(this).hasClass('tab_txt')){
					$(this).remove();
				}else{
					$(this).show().find('input').attr('disabled', false);
				}
			});
		}
		//发货地选项的显示 End
		//组合属性 Start
		if(dataObj.Cart==1 && IsCombination==1){
			var attr_value_ary=new Object();
			//统计组合属性是否已经组合
			if(check_attr_len>1){
				for(k in all_attr){
					IsCombination && $('#AttrId_'+k).hide().find('input').attr('disabled', true);
					attr_value_ary[k]=global_obj.json_encode_data(all_attr[k]);
				}
				//列出属性
				var attr_ary=new Array(), key_ary=new Array();
				for(k in check_attr){
					key_ary.push(k);
					for(var i=0; i<check_attr[k].length; i++){
						attr_ary.push(k+'_'+check_attr[k][i]);
					}
				}
				//记录属性的名称
				var attr_name_ary=new Object();
				var attr_n;
				for(k in attr_value_ary){
					for(kk in attr_value_ary[k]){
						attr_n=kk;
						//attr_name_ary[parseInt(isNaN(attr_n) ? attr_n.substr(1,attr_n.length-2): attr_n)]=attr_value_ary[k][kk];
						attr_name_ary[attr_n]=attr_value_ary[k][kk];
					}
				}
				//组合属性
				var attr_arr=ary_0=ary_1=new Array();
				var oversea_attr_arr=new Array();
				function CartAttr($arr, $oversea_attr, $num){
					var _arr=new Array();
					if($num==0){
						for(j in check_attr[key_ary[$num]]){
							$arr.push(check_attr[key_ary[$num]][j]);
						}
					}else{
						for(i in $arr){
							if(key_ary[$num]=='Overseas'){ //发货地
								_arr.push(String($arr[i])); //转换成字符串
							}else{
								for(j in check_attr[key_ary[$num]]){
									_arr.push($arr[i]+'_'+check_attr[key_ary[$num]][j]);
								}
							}
						}
						if(key_ary[$num]=='Overseas'){ //发货地
							for(j in check_attr[key_ary[$num]]){
								$oversea_attr[check_attr[key_ary[$num]][j]]=_arr;
							}
						}
						$arr=_arr;
					}
					++$num;
					if($num<check_attr_len){
						CartAttr($arr, $oversea_attr, $num);
					}else{
						attr_arr=$arr;
						oversea_attr_arr=$oversea_attr;
					}
				}
				CartAttr(attr_arr, oversea_attr_arr, 0);
				
				if($('#AttrId_0').length==0){
					var html_t=$('#attribute_tmp .column').html();
					html_t=html_t.replace('XXX',0).replace('Column', lang_obj.manage.products.group_attr);
					$('#attribute_ext .relation_box').append(html_t);
				}
				$('#AttrId_0 .group').remove();
				if(oversea_attr_arr && Object.keys(oversea_attr_arr).length>0){ //包含有发货地
					var html_oversea='', html_c, html_attr, name, p_v, s_v, w_v, is;
					var html_contents=$('#attribute_tmp .contents').html();
					var html_relation_box=$('#attribute_ext .relation_box:eq(0)').html();
					$('#attribute_ext .relation_box').hide().find('input').attr('disabled', true);
					for(key in oversea_attr_arr){
						var insert='',
							$Attr_obj=$('#attribute_ext .tab_txt[data-oversea="'+key+'"]');
							$AttrId_0=$Attr_obj.find('#AttrId_0');
						html_oversea+='<a class="tab_box_btn fl" data-oversea="'+key+'">'+attr_name_ary[key]+'</a>'; //选项卡头部
						if($Attr_obj.length){ //已存在
							$Attr_obj.html(html_relation_box);
						}else{ //不存在
							$('#attribute_ext').append('<table border="0" cellpadding="5" cellspacing="0" class="relation_box tab_txt" data-oversea="'+key+'">'+html_relation_box+'</table>');
						}
						$('#attribute_ext .tab_txt[data-oversea="'+key+'"] tbody[id!=AttrId_0]').remove();
						var i=0;
						for(i in oversea_attr_arr[key]){
							if(i>499) break;
							if(oversea_attr_arr[key][i].indexOf('_')>0){
								ary=oversea_attr_arr[key][i].split('_');
								ary.sort(function(a,b){ return a-b });
								ary_str=ary.join('_');
							}else{
								ary=new Array(oversea_attr_arr[key][i]);
								ary_str=oversea_attr_arr[key][i];
							}
							ary_str+='_'+key;
							if($('#AttrId_'+ary_str).length==0){
								html_c=html_contents;
								html_attr='';
								name='';
								overseas='';
								for(j in ary){
									name+=(j==0?'':' + ')+attr_name_ary[ary[j]].replace(/@8#/g, "'");
								}
								val_ary=ext_attr[ary_str];
								for(k in check_attr){
									for(j in ary){
										if(global_obj.in_array(ary[j], check_attr[k])){
											html_attr+=' data-attr-id-'+k+'='+ary[j];
										}
									}
								}
								if(!val_ary) val_ary=[0,0,0,'',0];
								p_v=val_ary[0]?val_ary[0]:0; //价格
								s_v=val_ary[1]?val_ary[1]:0; //库存
								w_v=val_ary[2]?val_ary[2]:0; //重量
								u_v=val_ary[3]?val_ary[3]:''; //SKU
								is=val_ary[4]?val_ary[4]:0; //加价
								html_c=html_c.replace(/XXX/g, ary_str).replace('Name', name).replace('p_v', parseFloat(p_v).toFixed(2)).replace('s_v', s_v).replace('w_v', w_v).replace('u_v', u_v);
								html_c=html_c.replace('attr_txt=""', html_attr);
								if(is==1){ //勾选
									html_c=html_c.replace('class="switchery"', 'class="switchery checked"').replace('type="checkbox"', 'type="checkbox" checked=""');
								}
								insert+=html_c;
							}
						}
						$('#attribute_ext .tab_txt[data-oversea="'+key+'"] #AttrId_0').append(insert).find('tr:gt(0)').addClass('group');
						if($('#attribute_ext .tab_txt[data-oversea="'+key+'"] #AttrId_0 tr').length==1){
							$('#attribute_ext .tab_txt[data-oversea="'+key+'"] #AttrId_0').remove();
						}
						if($('#attribute_ext .tab_txt[data-oversea="'+key+'"] #AttrId_0').length && !IsCombination){
							$('#attribute_ext .tab_txt[data-oversea="'+key+'"] #AttrId_0').hide();
						}
					}
					$('#attribute_ext_box .tab_box_row').html(html_oversea);
					$('#attribute_ext_box .tab_box_row .tab_box_btn').off().on('click', function(){
						var $name=$(this).attr('data-oversea');
						$(this).addClass('current').siblings().removeClass('current');
						$('#attribute_ext .tab_txt[data-oversea="'+$name+'"]').show().siblings('.tab_txt').hide();
					});
					$('#attribute_ext_box .tab_box_row .tab_box_btn').eq(0).click();
				}else{ //木有发货地
					var insert='', html_c, html_attr, name, overseas, p_v, s_v, w_v, is;
					var html_contents=$('#attribute_tmp .contents').html();
					var number=0;
					for(var i=0; i<attr_arr.length; ++i){
						if(i>499) break;
						ary=attr_arr[i].split('_');
						ary.sort(function(a,b){ return a-b });
						ary_str=ary.join('_')+'_Ov:1';
						if($('#AttrId_'+ary_str).length==0){
							html_c=html_contents;
							html_attr='';
							name='';
							overseas='';
							for(j in ary){
								/*if(ary[j].indexOf('Ov:')!=-1){ //发送地
									overseas=attr_name_ary[ary[j]];
								}else{
									name+=(j==0?'':' + ')+attr_name_ary[ary[j]].replace(/@8#/g, "'")
								}*/
								name+=(j==0?'':' + ')+attr_name_ary[ary[j]].replace(/@8#/g, "'");
							}
							val_ary=ext_attr[ary_str];
							for(k in check_attr){
								for(j in ary){
									if(global_obj.in_array(ary[j], check_attr[k])){
										html_attr+=' data-attr-id-'+k+'='+ary[j];
									}
								}
							}
							if(!val_ary) val_ary=[0,0,0,'',0];
							p_v=val_ary[0]?val_ary[0]:0; //价格
							s_v=val_ary[1]?val_ary[1]:0; //库存
							w_v=val_ary[2]?val_ary[2]:0; //重量
							u_v=val_ary[3]?val_ary[3]:''; //SKU
							is=val_ary[4]?val_ary[4]:0; //加价
							html_c=html_c.replace(/XXX/g, ary_str).replace('Name', name).replace('Overseas', overseas).replace('p_v', parseFloat(p_v).toFixed(2)).replace('s_v', s_v).replace('w_v', w_v).replace('u_v', u_v);
							html_c=html_c.replace('attr_txt=""', html_attr);
							if(is==1){ //勾选
								html_c=html_c.replace('class="switchery"', 'class="switchery checked"').replace('type="checkbox"', 'type="checkbox" checked=""');
							}
							insert+=html_c;
						}
						++number;
					}
					$('#AttrId_0').append(insert).find('tr:gt(0)').addClass('group');
					if(attr_arr.length>500){ //超出限制数量
						alert('所选择的组合属性数量超出上限500个！');
					}
					if($('#AttrId_0 tr').length==1){
						$('#AttrId_0').remove();
					}
					if($('#AttrId_0').length && !IsCombination){
						$('#AttrId_0').hide();
					}
				}
			}else{
				for(k in all_attr){
					$('#AttrId_'+k).show().find('input').attr('disabled', false);
				}
				$('#AttrId_0').remove();
				if(IsOverseas==0){ //关闭发货地，单独设置不显示
					$('#AttrId_Overseas').hide();
				}
			}
		}
		products_obj.products_edit_synchronize();
		//组合属性 End
		//颜色属性 Start
		var id_c=dataObj.Num;
		if(dataObj.Color==1 && (ischeck==0 || obj.is(':checked'))){
			if($('#ColorId_'+id_c).length==0){
				var ProId=$('#ProId').val();
				$.ajax({
					url:'./?do_action=products.products_get_color&ProId='+ProId+'&AttrId='+dataObj.AttrId+'&ColorId='+dataObj.Num+'&Name='+dataObj.Name,
					success:function(data){
						json=eval('('+data+')');
						var html_c=$('#color_tmp .contents').html();
						html_c=html_c.replace(/XXX/g, id_c).replace('Name', dataObj.Name.replace(/@8#/g, "'")).replace('Content', json.msg[0]);
						$('#color_ext').append(html_c);
						$('#color_box').removeClass('hide');
						frame_obj.mouse_click($('#ColorDetail_'+id_c+' .upload_btn, #ColorDetail_'+id_c+' .pic_btn .edit'), 'proColor', function($this){ //产品颜色图点击事件
							var $num=$this.parents('.img').attr('num');
							frame_obj.photo_choice_init('ColorId_'+id_c, "input[name='ColorPath["+id_c+"][]']", 'ColorDetail_'+id_c+' .img[num;'+$num+']', 'products', 10, 'do_action=products.products_img_del&Model=products');
						});
						frame_obj.mouse_click($('#ColorDetail_'+id_c+' .pic_btn .del'), 'proColorDel', function($this){ //产品颜色图删除点击事件
							var $obj=$this.parents('.img'),
								$num=parseInt($obj.attr('num')),
								$path=$obj.find('input:hidden').val();
							global_obj.win_alert(lang_obj.global.del_confirm, function(){
								$.ajax({
									url:'./?do_action=products.products_img_del&Model=products&Path='+$path+'&Index='+$num,
									success:function(data){
										json=eval('('+data+')');
										$('#ColorDetail_'+id_c+' dl[num='+json.msg[0]+'] .preview_pic .upload_btn').show();
										$('#ColorDetail_'+id_c+' dl[num='+json.msg[0]+'] .preview_pic a').remove();
										$('#ColorDetail_'+id_c+' dl[num='+json.msg[0]+'] .preview_pic input:hidden').val('').attr('save', 0);
									}
								});
							}, 'confirm');
						});
						$('#ColorDetail_'+id_c).dragsort({
							dragSelector:'dl',
							dragSelectorExclude:'',
							placeHolderTemplate:'<dl class="img placeHolder"></dl>',
							scrollSpeed:5
						});
					}
				});
			}
		}else{
			if($('#ColorId_'+id_c).length>0){
				$('#ColorId_'+id_c).remove();
			}
		}
		//颜色属性 End
		if($('#attribute_ext tbody tr').size()){
			$('#attribute_ext_box').removeClass('hide');
		}else{
			$('#attribute_ext_box').addClass('hide');
		}
		if($('#color_ext tbody tr').size()){
			$('#color_box').removeClass('hide');
		}else{
			$('#color_box').addClass('hide');
		}
	},
	
	batch_edit_init:function(){
		if($('#edit_form').size()){
			$('#edit_form .type .choice_btn').click(function(){
				var $this=$(this);
				$this.addClass('current').siblings().removeClass('current');
				$this.children('input').attr('checked', true);
				$('#edit_form .choice_box .rows').eq($this.index()).show().siblings().hide();
			});
			frame_obj.submit_form_init($('#edit_form'),'','','',function(data){
				if(data.ret==1){
					global_obj.win_alert(data.msg, function(){
						window.location.href='?m=products&a=products';
					});
				}else{
					global_obj.win_alert(data.msg);
				}
			});
		}else{
			$('.r_con_form input[name=SoldOutTime\\[\\]]').daterangepicker({showDropdowns:true});
			frame_obj.switchery_checkbox(function(obj){
				if(obj.find('.SoldOutInput').length){
					$('#sold_out_div').css('display', 'none');
				}else if(obj.find('.IsSoldOutInput').length){
					obj.nextAll('.sold_in_time').css('display', '');
				}
			}, function(obj){
				if(obj.find('.SoldOutInput').length){
					$('#sold_out_div').css('display', '');
				}else if(obj.find('.IsSoldOutInput').length){
					obj.nextAll('.sold_in_time').css('display', 'none');
				}
			});
			$('.r_con_form .other .choice_btn').click(function(){
				var $this=$(this);
				if($this.children('input').is(':checked')){
					$this.removeClass('current');
					$this.children('input').attr('checked', false);
				}else{
					$this.addClass('current');
					$this.children('input').attr('checked', true);
				}
			});
			frame_obj.submit_form_init($('form[name=batch_form]'), '', '', '', function(data){
				if(data.ret==1){
					global_obj.win_alert(data.msg, function(){
						window.location.reload();
					});
				}
				$('form[name=batch_form] input:submit').attr('disabled', false);
			});
	
			/*frame_obj.switchery_checkbox(function(obj){
				if(obj.find('input[name=SoldOut]').length){
					var $index=obj.index();
					obj.parents('.rows').find('.switchery:not(:eq('+$index+'))').removeClass('checked').find('input').attr('checked', false);
				}
			}, function(obj){
				if(obj.find('input[name=SoldOut]').length){
					var $index=obj.index();
					obj.parents('.rows').find('.switchery:not(:eq('+$index+'))').addClass('checked').find('input').attr('checked', true);
				}
			});
			$('form[name=batch_price_form] select[name=Model]').change(function(){
				var $text=$(this).find('option:selected').text();
				$('form[name=batch_price_form] .price_input .model').each(function(){
					$(this).text($text);
				});
			});
			frame_obj.submit_form_init($('form[name=batch_price_form]'), '', '', '', function(data){
				if(data.ret==1){
					global_obj.win_alert(data.msg);
				}else{
					global_obj.win_alert(lang_obj.global.set_error);
				}
				$('form[name=batch_price_form] input:submit').attr('disabled', false);
			});
			frame_obj.submit_form_init($('form[name=batch_sold_out_form]'), '', '', '', function(data){
				$('form[name=batch_sold_out_form] input:submit').attr('disabled', false);
				if(data.ret==1){
					global_obj.win_alert(data.msg);
				}else{
					global_obj.win_alert(lang_obj.global.set_error);
				}
			});
			frame_obj.submit_form_init($('form[name=batch_move_category_form]'), '', '', '', function(data){
				$('form[name=batch_move_category_form] input:submit').attr('disabled', false);
				if(data.ret==1){
					global_obj.win_alert(data.msg);
				}else{
					global_obj.win_alert(data.msg?data.msg:lang_obj.global.set_error);
				}
			});*/
		}
	},
	
	category_init:function(){
		frame_obj.del_init($('#category .r_con_table'));
		frame_obj.select_all($('input[name=select_all]'), $('input[name=select]')); //批量操作
		frame_obj.del_bat($('.r_nav .del'), $('input[name=select]'), function(id_list){
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('?', {do_action:'products.category_del_bat', group_cateid:id_list}, function(data){
					if(data.ret==1){
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
		$('#category .r_con_table tbody').dragsort({
			dragSelector:'tr',
			dragSelectorExclude:'a, td[data!=move_myorder]',
			placeHolderTemplate:'<tr class="placeHolder"></tr>',
			scrollSpeed:5,
			dragEnd:function(){
				var data=$(this).parent().children('tr').map(function(){
					return $(this).attr('cateid');
				}).get();
				$.get('?', {do_action:'products.category_order', sort_order:data.join('|')});
			}
		});
		$('select[name=UnderTheCateId]').change(function(){
			if($(this).val()){
				$('select[name=AttrId]').parents('.rows').hide();
			}else{
				$('select[name=AttrId]').parents('.rows').show();
			}
		});
		frame_obj.switchery_checkbox(function(obj){
			if(obj.find('input.desc_tab_btn').length){
				obj.next().addClass('show').removeClass('hide');
			}
		}, function(obj){
			if(obj.find('input.desc_tab_btn').length){
				obj.next().addClass('hide').removeClass('show');
			}
		});
		/* 产品分类图片上传 */
		$('#PicUpload, .upload_pic .edit').on('click', function(){frame_obj.photo_choice_init('PicUpload', 'form input[name=PicPath]', 'PicDetail', '', 1);});
		if($('form input[name=PicPath]').attr('save')==1){
			$('#PicDetail').append(frame_obj.upload_img_detail($('form input[name=PicPath]').val())).children('.upload_btn').hide();
		}
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
		/* 标题与标签 */
		$('#edit_form').on('click', '.open', function(){
			if($(this).hasClass('close')){
				$(this).removeClass('close').text(lang_obj.global.open);
				$('.seo_hide').slideUp(300);
			}else{
				$(this).addClass('close').text(lang_obj.global.pack_up);
				$('.seo_hide').slideDown(300);
			}
			return false;
		});
		frame_obj.submit_form_init($('#edit_form'), './?m=products&a=category');
	},
	
	model_init:function(){
		frame_obj.del_init($('#model .r_con_table'));
		frame_obj.select_all($('input[name=select_all]'), $('input[name=select]')); //批量操作
		frame_obj.del_bat($('.r_nav .del'), $('input[name=select]'), function(id_list){
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('?', {do_action:'products.model_del_bat', group_attrid:id_list}, function(data){
					if(data.ret==1){
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
		$('#model .r_con_table .copy').click(function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.copy_confirm, function(){
				$.get($this.attr('href'), function(data){
					if(data.ret==1){
						window.location='./?m=products&a=model&d=model_edit&AttrId='+data.msg;
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
		
		$('#model .attr_list').dragsort({
			dragSelector:'dl',
			dragSelectorExclude:'a, dd[class!=attr_ico]',
			placeHolderTemplate:'<dl class="attr_box placeHolder"></dl>',
			scrollSpeed:5,
			dragEnd:function(){
				var data=$(this).parents('.attr_list').find('.edit').map(function(){
					return $(this).attr('data-id');
				}).get();
				$.get('?', {do_action:'products.model_order', group_attrid:data.join('-')});
			}
		});
		$('#model .attr_box').hover(function(){
			$(this).children('.attr_menu').stop(true, true).animate({'right':0}, 200);
		}, function(){
			$(this).children('.attr_menu').stop(true, true).animate({'right':-51}, 200);
		});
		/* 属性分类添加/编辑弹出框 */
		$('.r_nav .ico .add, .r_con_table td>.edit').on('click', function(){
			var $id=parseInt($(this).attr('data-id')),
				$obj=$('.box_category_edit');
			frame_obj.pop_form($obj);
			frame_obj.rows_input();
			if($id){ //编辑
				var $data=$.evalJSON($(this).parents('tr').attr('data'));
				$obj.find('.rows input').each(function(){ //名称
					$(this).val($data[$(this).attr('Name')]);
				});
				$obj.find('input[name=AttrId]').val($id); //id
				$obj.find('.t>h1>span').text(lang_obj.global.edit); //编辑框标题
			}else{ //添加
				$obj.find('.rows input').val(''); //名称
				$obj.find('input[name=AttrId]').val(0); //id
				$obj.find('.t>h1>span').text(lang_obj.global.add); //编辑框标题
			}
			return false;
		});
		/* 提交 */
		frame_obj.submit_form_init($('#edit_form'), './?m=products&a=model', function(){
			var lang='', obj, num;
			$('.tab_txt').each(function(){
				obj=$(this);
				lang=obj.attr('lang');
				num=0;
				$(this).find('*[notnull]').each(function(){
					if($.trim($(this).val())=='') ++num;
				});
				if(num>0){
					obj.parent().find('.tab_box_row .tab_box_btn[data-lang='+lang+']').css({'animation':'null .3s 2 0s linear forwards', '-webkit-animation':'null .3s 2 0s linear forwards'});
				}else{
					obj.parent().find('.tab_box_row .tab_box_btn[data-lang='+lang+']').removeAttr('style');;
				}
			});
			return true;
		}, '', '', 1);
		frame_obj.submit_form_init($('#category_edit_form'), './?m=products&a=model');
	},
	
	attr_init:function(){
		/* 属性编辑弹出框 */
		$('.attr_add .add, .attr_box .edit').on('click', function(){
			var $id=parseInt($(this).attr('data-id')),
				$is_cartattr=parseInt($(this).attr('data-cart'));
				$obj=$('.box_model_edit');
			frame_obj.pop_form($obj);
			frame_obj.rows_input();
			if($id){ //编辑
				var $data=$.evalJSON($(this).parents('dl.attr_box').attr('data')),
					$value=$.evalJSON($(this).parents('dl.attr_box').attr('data-value')),
					$data_value=$txt='', o=new Object;
				!$value && ($value=new Object);
				$obj.find('.rows:eq(0) input').each(function(){ //名称
					//$(this).val($data[$(this).attr('Name')]);
					$(this).val(global_obj.htmlspecialchars_decode($data[$(this).attr('Name')]));
				});
				$.post('?', {do_action:'products.model_category_select', 'ParentId':0, 'AttrId':$id}, function(data){
					$('#edit_form .rows:eq(1) .input').html(data); //分类所属
				});
				$obj.find('input[name=CartAttr]').attr('checked', (parseInt($data['CartAttr'])?true:false)); //选项
				if(($obj.find('input[name=ColorAttr]').is(':checked')?1:0)!=$data['ColorAttr']) $obj.find('input[name=ColorAttr]').click(); //颜色属性
				if(parseInt($data['CartAttr'])){
					$('#color_attr_box').css({"display":"inline"});
					$obj.find('.input_type').css({"display":"none"});
					$obj.find('.rows.tab_box').css('display', '').find('input').attr({'notnull':'notnull', 'disabled':false});
				}else{
					$('#color_attr_box').css({"display":"none"});
					$obj.find('.input_type').css({"display":"block"});
					if($('form input[name=Type]:checked').val()==0) $obj.find('.rows.tab_box').css('display', 'none').find('input').attr('disabled', true).removeAttr('notnull');
				}
				$obj.find('input[name=Type][value='+$data['Type']+']').click(); //录入方式
				$obj.find('.tab_txt').each(function(){ //选择列表
					$(this).find('.attr_item:gt(0)').remove();
					$(this).find('.attr_item:eq(0) .not_input input').val('');
					$(this).find('.attr_item:eq(0)').find('.del').hide().next().show();
					for(k in $value){
						if(k<($value.length-1)){
							$(this).find('.attr_item:eq('+k+')').after($(this).find('.attr_item:eq('+k+')').prop('outerHTML'));
						}
						//$(this).find('.attr_item:eq('+k+') .not_input input').val($value[k]['Value_'+$(this).attr('lang')]);
						$(this).find('.attr_item:eq('+k+') .not_input input').val(global_obj.htmlspecialchars_decode($value[k]['Value_'+$(this).attr('lang')]));
						$(this).find('.attr_item:eq('+k+')').find('.del').show().next().hide();
					}
					$(this).find('.attr_item:last').find('.add').show();
					$value.length==1 && $(this).find('.attr_item:eq(0)').find('.del').hide();
				});
				$obj.find('input[name=AttrId]').val($id); //id
				$obj.find('.t>h1>span').text(lang_obj.global.edit); //编辑框标题
			}else{ //添加
				var $ParentId=$(this).parents('tr').attr('data-attr-id');
				if($('input[name=ParentId]').length){
					//产品编辑页
					$ParentId=parseInt($('input[name=ParentId]').val()); //产品编辑页的隐藏域	
					$ParentId==0 && $obj.find('.rows:eq(1) .input').html(''); //没有相应的属性分类，删掉下拉
				}
				$obj.find('.rows:eq(0) input').val(''); //名称
				if($ParentId){
					$.post('?', {do_action:'products.model_category_select', 'ParentId':$ParentId, 'AttrId':0}, function(data){
						$obj.find('.rows:eq(1) .input').html(data); //分类所属
						$obj.find('select[name=ParentId]').val($ParentId);
					});
				}
				$obj.find('input[name=CartAttr]').attr('checked', false); //选项
				if(($obj.find('input[name=ColorAttr]').is(':checked')?1:0)!=0){ //颜色属性
					$obj.find('input[name=ColorAttr]').click();
				}
				$('#color_attr_box').css({"display":"none"});
				$obj.find('.input_type').css({"display":"block"});
				if($('form input[name=Type]:checked').val()==0){
					$obj.find('.rows.tab_box').css('display', 'none').find('input').attr('disabled', true).removeAttr('notnull');
				}
				$obj.find('input[name=Type][value=0]').click(); //录入方式
				if($('input[name=ParentId]').length){
					//产品编辑页
					if($is_cartattr==1){ //购物车属性
						$obj.find('form').attr("data-cart", 1);
						$obj.find('.rows:eq(2)').show();
						$obj.find('input[name=CartAttr]').attr('checked', true);
						$('#color_attr_box').css({"display":"inline-block"});
						$('#cart_attr_box, .input_type').css({"display":"none"});
						$obj.find('.rows.tab_box').css('display', '').find('input').attr({'notnull':'notnull', 'disabled':false});
					}else{ //普通属性
						$obj.find('form').attr("data-cart", 0);
						$obj.find('.rows:eq(2)').hide();
					}
				}
				$obj.find('.tab_txt').each(function(){ //选择列表
					$(this).find('.attr_item:gt(0)').remove();
					$(this).find('.attr_item:eq(0) .not_input input').val('');
					$(this).find('.attr_item:eq(0)').find('.del').hide().next().show();
				});
				$obj.find('input[name=AttrId]').val(0); //id
				$obj.find('.t>h1>span').text(lang_obj.global.add); //编辑框标题
			}
			return false;
		});
		/* 选项 */
		$('.box_model_edit form input[name=CartAttr]').click(function(){
			if($(this).is(':checked')){
				$('#color_attr_box').css({"display":"inline"});
				$('.input_type').css({"display":"none"});
				$('.box_model_edit .rows.tab_box').css('display', '').find('input').attr({'notnull':'notnull', 'disabled':false});
			}else{
				$('#color_attr_box').css({"display":"none"});
				$('.input_type').css({"display":"block"});
				if($('form input[name=Type]:checked').val()==0) $('.box_model_edit .rows.tab_box').css('display', 'none').find('input').attr('disabled', true).removeAttr('notnull');
			}
		});
		/* 录入方式 */
		$('.box_model_edit form input[name=Type]').click(function(){
			var value=$(this).val();
			if(value==1){
				$('.box_model_edit .rows.tab_box').css('display', '').find('input').attr({'notnull':'notnull', 'disabled':false});
			}else{
				$('.box_model_edit .rows.tab_box').css('display', 'none').find('input').attr('disabled', true).removeAttr('notnull');
			}
		});
		/* 选项卡效果 */
		$('.box_model_edit .tab_box_btn').on('click', function(){
			var $num=$(this).index();
			$(this).addClass('current').siblings().removeClass('current');
			$(this).parent().nextAll('.tab_txt_'+$num).show().siblings('.tab_txt').hide();
		});
		$('.box_model_edit .tab_box_row').each(function(){
			$(this).children('.tab_box_btn').eq(0).click();
		});
		/* 属性选项事件 */
		$('.box_model_edit .tab_box .tab_txt').on('click', '.add', function(){ //添加帮助选项
			var $box=$(this).parent('.attr_item'),
				$num=$box.index(),
				$obj=$(this).parents('.tab_txt');
			$('.box_model_edit .tab_box .tab_txt').each(function(){
				$(this).find('.attr_item:eq('+$num+')').after($(this).find('.attr_item:eq('+$num+')').prop('outerHTML'));
				$(this).find('.attr_item:eq('+($num+1)+')').siblings().find('.add').hide();
				$(this).find('.attr_item:eq('+($num+1)+') .not_input input').val('');
				$(this).find('.attr_item:eq('+$num+'), .attr_item:eq('+($num+1)+')').find('.del').show();
			});
		}).on('click', '.del', function(){ //删除帮助选项
			var $box=$(this).parent('.attr_item'),
				$num=$box.index(),
				$obj=$(this).parents('.tab_txt');
			if($obj.find('.attr_item').size()==2){
				$('.box_model_edit .tab_box .tab_txt').each(function(){
					$(this).find('.attr_item:eq('+$num+')').remove();
					$(this).find('.attr_item:eq(0) .del').hide();
					$(this).find('.attr_item:eq(0) .add').show();
				});
			}else{
				$('.box_model_edit .tab_box .tab_txt').each(function(){
					$(this).find('.attr_item:eq('+$num+')').remove();
					$(this).find('.attr_item:last .add').show();
					$(this).find('.attr_item:last').siblings().find('.add').hide();
				});
			}
		});
	},
	
	business_global:{
		del_action:'',
		order_action:'',
		init:function(){
			frame_obj.del_init($('#business .r_con_table')); //删除事件
			frame_obj.select_all($('#business .r_con_table input[name=select_all]'), $('#business .r_con_table input[name=select]')); //批量操作
			/* 批量删除 */
			frame_obj.del_bat($('.r_nav .del'), $('#business .r_con_table input[name=select]'), function(id_list){
				var $this=$(this);
				global_obj.win_alert(lang_obj.global.del_confirm, function(){
					$.get('?', {do_action:products_obj.business_global.del_action, group_id:id_list}, function(data){
						if(data.ret==1){
							window.location.reload();
						}
					}, 'json');
				}, 'confirm');
				return false;
			});
			/* 批量排序 */
			frame_obj.del_bat($('.r_nav .order'), $('#business .r_con_table input[name=select]'), function(id_list){
				var $this=$(this),
					$checkbox,
					my_order_str='';
				$('#business .myorder select').each(function(index, element){
					$checkbox=$(element).parents('tr').find(':checkbox');
					if($checkbox.length && $checkbox.get(0).checked){;
						my_order_str+=$(element).val()+'-';
					}
				});
				global_obj.win_alert(lang_obj.global.my_order_confirm, function(){
					$.get('?', {do_action:products_obj.business_global.order_action, group_id:id_list, my_order_value:my_order_str}, function(data){
						if(data.ret==1){
							window.location.reload();
						}
					}, 'json');
				}, 'confirm');
				return false;
			}, lang_obj.global.dat_select);
		}
	},
	
	business_category_init:function(){
		products_obj.business_global.del_action='products.business_category_del_bat';
		products_obj.business_global.order_action='products.business_category_my_order';
		products_obj.business_global.init();
	},
	
	business_category_edit_init:function(){
		frame_obj.submit_form_init($('#edit_form'), './?m=products&a=business&d=category');
	},
	
	business_init:function(){
		products_obj.business_global.del_action='products.business_del_bat';
		products_obj.business_global.order_action='products.business_my_order';
		products_obj.business_global.init();
		$('#business .r_con_column .switchery').click(function(){
			var $this=$(this);
			$.get('?', 'do_action=products.business_uesd', function(data){
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
	
	business_edit_init:function(){
		/* 资质证书上传 */
		$('#ImgUpload, .upload_img .edit').on('click', function(){frame_obj.photo_choice_init('ImgUpload', 'form input[name=ImgPath]', 'ImgDetail', '', 1);});
		if($('form input[name=ImgPath]').attr('save')==1){
			$('#ImgDetail').append(frame_obj.upload_img_detail($('form input[name=ImgPath]').val())).children('.upload_btn').hide();
		}
		$('.upload_img .del').on('click', function(){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.ajax({
					url:'./?do_action=action.file_del&PicPath='+$this.prev().attr('href'),
					success:function(){
						$('#ImgDetail').children('a').remove();
						$('#ImgDetail').children('.upload_btn').show();
						$('form input[name=ImgPath]').val('');
					}
				});
			}, 'confirm');
			return false;
		});
		/* 合作凭证上传 */
		$('#PicUpload, .upload_pic .edit').on('click', function(){frame_obj.photo_choice_init('PicUpload', 'form input[name=PicPath]', 'PicDetail', '', 1);});
		if($('form input[name=PicPath]').attr('save')==1){
			$('#PicDetail').append(frame_obj.upload_img_detail($('form input[name=PicPath]').val())).children('.upload_btn').hide();
		}
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
		/* 提交 */
		frame_obj.submit_form_init($('#edit_form'), './?m=products&a=business');
	},
	
	review_init:function(){
		frame_obj.del_init($('#review .r_con_table'));
		frame_obj.select_all($('.r_con_wrap input[name=select_all]'), $('.r_con_wrap input[name=select]')); //批量操作
		frame_obj.del_bat($('.r_nav .del'), $('#review .r_con_table input[name=select]'), function(id_list){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('?', {do_action:'products.review_del_bat', group_rid:id_list}, function(data){
					if(data.ret==1){
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
	},
	
	review_reply_init:function(){
		frame_obj.submit_form_init($('#edit_form'), './?m=products&a=review&d=reply&RId='+$('input[name=RId]').val());
	},
	
	/*
	upload_init:function(){
		frame_obj.file_upload($('#ExcelUpload'), '', '', 'file_upload', true, 1, function(filepath, count){
			$('#excel_path').val(filepath);
		}, '*.csv;*.xlsx;*.xls');
		frame_obj.submit_form_init($('#edit_form'), '', '', '', function(data){
			if(data.ret==2){
				$('#explode_progress').append(data.msg[1]);
				$('#edit_form input[name=Number]').val(data.msg[0]);
				$('#edit_form .submit_btn').attr('disabled', false).click();
			}else if(data.ret==1){
				$('#explode_progress').append(data.msg);
			}else{
				global_obj.win_alert(data.msg);
			}
		});
	},
	*/
	
	upload_new_init:function(){
		/*
		frame_obj.file_upload($('#ExcelUpload'), '', '', 'file_upload', true, 1, function(filepath, count){
			$('#excel_path').val(filepath);
		}, '*.csv;*.xlsx;*.xls');
		*/
		$('form[name=upload_form]').fileupload({
			url: '/manage/?do_action=action.file_upload_plugin&size=file',
			//acceptFileTypes: /^application\/vnd\.(ms-excel|openxmlformats-officedocument.spreadsheetml.sheet)$/i, //csv xlsx xls
			acceptFileTypes: /^application\/(vnd.ms-excel|vnd.openxmlformats-officedocument.spreadsheetml.sheet|csv|xlsx|xls)$/i, //csv xlsx xls
			callback: function(filepath, count){
				$('#excel_path').val(filepath);
			}
		});
		$('form[name=upload_form]').fileupload(
			'option',
			'redirect',
			window.location.href.replace(/\/[^\/]*$/, '/cors/result.html?%s')
		);
		frame_obj.submit_form_init($('#edit_form'), '', '', '', function(data){
			if(data.ret==2){
				$('#explode_progress').append(data.msg[1]);
				$('#edit_form input[name=Number]').val(data.msg[0]);
				$('#edit_form .submit_btn').attr('disabled', false).click();
			}else if(data.ret==1){
				$('#explode_progress').append(data.msg);
			}else{
				global_obj.win_alert(lang_obj.global.set_error);
			}
		});
	},
	
	watermark_init:function(){
		frame_obj.submit_form_init($('#edit_form'), '', '', '', function(data){
			if(data.ret==2){
				$('#explode_progress').append(data.msg[1]);
				$('#edit_form input[name=Number]').val(data.msg[0]);
				$('#edit_form .submit_btn').attr('disabled', false).click();
			}else if(data.ret==1){
				$('#explode_progress').append(data.msg);
			}else{
				global_obj.win_alert(lang_obj.global.set_error);
			}
		});
	},
	
	aliexpress_sync_init:function(){
		var step_0=function(){
			var ajaxTimeout=$.ajax({
				url:'./?do_action=products.aliexpress_sync&step=0',
				type:'get',
				timeout:10000,
				dataType:'json',
				success:function(data){
					$('.sync').html($('.sync').html()+data.msg.msg);
					data.ret==1 && step_1();
				},
				complete:function(XMLHttpRequest,status){
					if(status=='timeout'){
						ajaxTimeout.abort();
						step_0();
					}
				}
			});
		}
		var step_1=function(){	//获取产品的基本资料
			var ajaxTimeout=$.ajax({
				url:'./?do_action=products.aliexpress_sync&step=1',
				type:'get',
				timeout:10000,
				dataType:'json',
				success:function(data){
					$('.sync').html($('.sync').html()+data.msg.msg).scrollTop(1000000);
					if(data.ret==1){
						data.msg.step==2?step_2():step_1();
					}
				},
				complete:function(XMLHttpRequest,status){
					if(status=='timeout'){
						ajaxTimeout.abort();
						step_1();
					}
				}
			});
		}
		var step_2=function(){	//获取产品的详细资料
			var ajaxTimeout=$.ajax({
				url:'./?do_action=products.aliexpress_sync&step=2',
				type:'get',
				timeout:30000,
				dataType:'json',
				success:function(data){
					$('.sync').html($('.sync').html()+data.msg.msg).scrollTop(1000000);
					data.ret==1 && step_2();
				},
				complete:function(XMLHttpRequest,status){
					if(status=='timeout'){
						ajaxTimeout.abort();
						step_2();
					}
				}
			});
		}
		$('.btn_ok').click(function(){
			$(this).prop('disabled', true);
			step_0();
		});
	},
	
	set_init:function(){
		$('#set .switchery').click(function(){
			var $this=$(this),
				$key=$this.attr('data')
				$checked_ary=new Object(),
				check=0;
			if($key=='myorder' && !$('input[name=Number]').val()){
				global_obj.win_alert(lang_obj.manage.products.prefix_tips);
				return false;
			}
			$('#set .switchery').each(function(){
				$checked_ary[$(this).attr('data')]=($key==$(this).attr('data')?($(this).hasClass('checked')?0:1):($(this).hasClass('checked')?1:0));
			});
			$checked=global_obj.json_decode_data($checked_ary);
			$.post('?', 'do_action=products.set&Checked='+$checked, function(data){
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
		$('#set').on('click', '.set', function(){
			var Title=$(this).parents('.box').find('.title').text();
			var Txt=$(this).parent().next('.txt').html();
			if(Txt){
				global_obj.div_mask();
				$('body').prepend('<div id="global_win_alert"><button class="close">X</button><h1>'+Title+'</h1><div class="list">'+Txt+'</div></div>');
				$('#global_win_alert').css({
					'position':'fixed',
					'left':$(window).width()/2-230,
					'top':'30%',
					'background':'#fff',
					'border':'1px solid #ccc',
					'opacity':0.95,
					'width':560,
					'z-index':100000,
					'border-radius':'8px',
					'padding':0
				}).children('.close').css({
					'float':'right',
					'padding':0,
					'line-height':'100%',
					'font-size':18,
					'margin-right':17,
					'opacity':0.2,
					'cursor':'pointer',
					'background':'none',
					'border':0,
					'font-weight':'bold',
					'color':'#000',
				}).siblings('h1').css({
					'margin':'10px 0 0 30px',
					'font-size':16,
					'font-weight':'bold',
				}).siblings('div.list').css({
					'width':500,
					'padding':'10px 10px 30px',
					'margin':'0 auto',
				}).children('div').css({
					'height':40,
					'line-height':'40px',
				}).children('.txt_name').css({
					'width':125,
					'display':'inline-block',
				});
				$('#global_win_alert').on('click', '.close', function(){
					$('#global_win_alert').remove();
					global_obj.div_mask(1);
				}).on('click', '.choice_btn', function(){
					var $this=$(this),
						$key=$this.attr('data'),
						$val=$this.attr('value'),
						$name=$this.parent().attr('name');
						$checked_ary=new Object;
					$this.parents('.list').find('span').each(function(){
						if($(this).hasClass('current')){
							$checked_ary[$(this).attr('data')]=$(this).attr('value');
						}
					});
					$checked_ary[$key]=$val;
					$checked=global_obj.json_decode_data($checked_ary);
					$.post('?', 'do_action=products.set_ext&Name='+$name+'&Checked='+$checked, function(data){
						if(data.ret==1){
							$this.addClass('current').siblings().removeClass('current');
							$this.children('input').attr('checked', true);
							$('#set .txt .choice_btn[data='+$key+'][value='+$val+']').addClass('current').siblings().removeClass('current');
							var ary=global_obj.json_encode_data(data.msg.replace(/\\/g, ''));
							for(v in ary){
								$('.show_list .choice_btn[data='+v+'][value='+ary[v]+']').addClass('current').siblings().removeClass('current');
							}
						}else{
							global_obj.win_alert(lang_obj.global.set_error);
						}
					}, 'json');
				});
			}
			return false;
		});
		$('.sub_btn').on('click', function(){
			$obj=$(this).parent();
			if($obj.hasClass('number')){//产品编号自动排序
				var $Val=$(this).prev('input').val();
				if($Val){
					$.post('?', 'do_action=products.set_products_number_prefix&Value='+$Val, function(data){
						if(data.ret==1){
							global_obj.win_alert(data.msg);
						}else{
							global_obj.win_alert(lang_obj.global.set_error);
						}
					}, 'json');
				}else{
					global_obj.win_alert(lang_obj.manage.products.prefix_tips);
					return false;
				}
			}else if($obj.hasClass('item')){//产品自定义单位
				var $Val=$(this).prev('input').val();
				if($Val){
					$.post('?', 'do_action=products.set_products_item&Value='+$Val, function(data){
						if(data.ret==1){
							global_obj.win_alert(data.msg);
						}else{
							global_obj.win_alert(lang_obj.global.set_error);
						}
					}, 'json');
				}else{
					global_obj.win_alert(lang_obj.manage.products.prefix_tips);
					return false;
				}
			}else{//产品收藏
				var $Val0=parseInt($obj.find('input:eq(0)').val());
				var $Val1=parseInt($obj.find('input:eq(1)').val());
				if($Val0>=0 && $Val1>=0){
					$.post('?', 'do_action=products.set_products_favorite_range&Value0='+$Val0+'&Value1='+$Val1, function(data){
						if(data.ret==1){
							global_obj.win_alert(data.msg);
						}else{
							global_obj.win_alert(lang_obj.global.set_error);
						}
					}, 'json');
				}else{
					global_obj.win_alert(lang_obj.manage.products.prefix_tips);
					return false;
				}
			}
		});
	},
	
	explode_init:function (){
		frame_obj.submit_form_init($('#edit_form'), '', '', '', function(data){
			if(data.ret==2){
				$('#explode_progress').append(data.msg[1]);
				$('#edit_form input[name=Number]').val(data.msg[0]);
				$('#edit_form .submit_btn').click();
			}else if(data.ret==1){
				$('#edit_form input:submit').attr('disabled', false); //初始化
				$('#edit_form input[name=Number]').val(0); //初始化
				window.location='./?do_action=products.products_explode_down&Status=ok';
			}else{
				global_obj.win_alert(lang_obj.global.set_error);
			}
		});
	},
	
	tags_init:function(){
		frame_obj.del_init($('#tags .r_con_table'));
		frame_obj.select_all($('input[name=select_all]'), $('input[name=select]')); //批量操作
		frame_obj.del_bat($('.r_nav .del'), $('input[name=select]'), function(id_list){
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('?', {do_action:'products.tags_del_bat', group_tid:id_list}, function(data){
					if(data.ret==1){
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
		$('#tags_edit,.tags_name_edit').on('click',function(){
			var obj=$('.box_tags_edit');
			var tid=$(this).parent().parent('tr').attr('tid');
			if(tid){	//编辑
				var data=$.evalJSON($(this).parents('tr').attr('data'));
				obj.find('.rows input').each(function(){ //名称
					$(this).val(data[$(this).attr('Name')]);
				});
				obj.find('input[name=TId]').val(tid);	//tid
				obj.find('.t>h1>span').text(lang_obj.global.edit); //编辑框标题
				
			}else{	//添加
				obj.find('.rows input').val(''); //名称
				obj.find('input[name=TId]').val(0); //tid
				obj.find('.t>h1>span').text(lang_obj.global.add); //编辑框标题
			}
			frame_obj.pop_form(obj);
			frame_obj.rows_input();
		});
		frame_obj.submit_form_init($('#tags_edit_form'), '', '', '', function(data){
			window.location.reload();	
		});
	},
	
	tags_set:function(){
		var h=$('#main').height()-$('.r_nav').outerHeight();
		$('.list_box .lefter .p_frame').height(h-100);
		$('.list_box_righter .p_frame').height(h-150);
		//搜索
		$('.list_box_righter').on('click','#search_btn', function(){
			var _this=$(this);
			_this.attr('disabled', true);
			$.ajax({
				type:'post',
				url:'./?m=products&a=tags&d=set&'+$('#search_form').serialize(),
				async:false,
				success:function(data){
					$('.list_box_righter').html($(data).find('.list_box_righter').html());
					$('.list_box_righter .p_frame').height(h-150);
					prod_display();
				}
			});
			return false;
		});
		//翻页
		$('.list_box_righter').on('click','#turn_page_oth a', function(){
			$.ajax({
				type:'post',
				url:$(this).attr('href'),
				async:false,
				success:function(data){
					$('.list_box_righter').html($(data).find('.list_box_righter').html());
					$('.list_box_righter .p_frame').height(h-150);
					prod_display();
				}
			});
			return false;
		});
		//点击动作
		var obj=$('.list_box .lefter .p_frame');
		var proid_obj=$('#proid_hide');
		var prod_display=function(){
			var value=proid_obj.val().split('|');
			for(var key in value){
				if(!value[key]) continue;
				
				$('.list_box_righter #product_item_'+value[key]).hide();
			}
		}
		$('.list_box_righter').on('click','.product_item',function(){
			var id='lefter_'+$(this).attr('id');
			if(obj.find('#'+id).size()) return false;
			if(obj.find('.p_related_notice').size()) obj.find('.p_related_notice').remove();
			obj.prepend('<div id="'+id+'" class="product_item lefter_product_item" proid="'+$(this).attr('proid')+'">'+$(this).html()+'</div>');
			var proid_hide=proid_obj.val();
			if(proid_hide){
				proid_obj.val(proid_hide+$(this).attr('proid')+'|');
			}else{
				proid_obj.val('|'+$(this).attr('proid')+'|');
			}
			$(this).hide();
		});
		$('.list_box .lefter').on('click','.product_item',function(){
			var proid_ary=proid_obj.val().split('|');
			if(proid_ary.length>3){	//只有一个产品
				var proid_hide=proid_obj.val();
				proid_hide=proid_hide.replace($(this).attr('proid')+'|','');
				proid_obj.val(proid_hide);
			}else{
				proid_obj.val('');
			}
			$(this).remove();
			$('.list_box_righter #product_item_'+$(this).attr('proid')).show();
		});
		//提交
		$('#tags_form').submit(function(){return false;});
		$('#tags_form .submit_btn').on('click',function(){
			var _this=$(this);
			_this.attr('disabled', true);
			$.post('?', $('#tags_form').serialize(), function(data){
				if(data.ret==1){
					window.location='?m=products&a=tags';
				}
			}, 'json');
		});
	}
}