/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

var orders_obj={
	orders_init:function(){
		frame_obj.del_init($('#orders .r_con_table'));
		frame_obj.select_all($('input[name=select_all]'), $('input[name=select]')); //批量操作
		frame_obj.del_bat($('.r_nav .del'), $('input[name=select]'), function(id_list){
			var $this=$(this);
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('?', {do_action:'orders.orders_del_bat', group_orderid:id_list}, function(data){
					if(data.ret==1){
						window.location.reload();
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
		frame_obj.select_all($('.r_nav input[name=custom_all]'), $('.r_nav input[class=custom_list][disabled!=disabled]')); //批量操作
		frame_obj.submit_form_init($('.r_nav .ico form'), './?m=orders&a=orders');
		$('#orders .r_con_table .print').click(function(){
			var id=$(this).attr('orderid');
			global_obj.div_mask();
			$('body').prepend('<div id="global_win_alert"><button class="close">X</button><div><button class="btn print_1">'+lang_obj.manage.set.print_1+'</button></div></div>');/*<button class="btn print_0">'+lang_obj.manage.set.print_0+'</button>*/
			$('#global_win_alert').css({
				'position':'fixed',
				'left':$(window).width()/2-200,
				'top':'30%',
				'background':'#fff',
				'border':'1px solid #ccc',
				'opacity':0.95,
				'width':400,
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
			}).siblings('div').css({
				'width':340,
				'padding':'30px 10px',
				'margin':'0 auto',
			}).children('button').css({
				'width':146,
				'height':40,
				'line-height':'40px',
				'text-align':'center',
				'border':'1px #b7b7b7 solid',
				'background':'#37bd9c',
				'color':'#fff',
				'cursor':'pointer',
				'margin':'0 12px',
				'font-size':14,
				'font-weight':'bold'
			});
			$('#global_win_alert').on('click', '.close', function(){
				$('#global_win_alert').remove();
				global_obj.div_mask(1);
			}).on('click', '.print_0', function(){
				$('#global_win_alert').remove();
				global_obj.div_mask(1);
				window.open('./?m=orders&a=orders&d=print&iframe=1&Type=0&OrderId='+id);
			}).on('click', '.print_1', function(){
				$('#global_win_alert').remove();
				global_obj.div_mask(1);
				window.open('./?m=orders&a=orders&d=print&iframe=1&Type=1&OrderId='+id);
			});
			return false;
		});
		$('#orders .r_con_table .sales_select').on('dblclick', function(){
			var $obj=$(this),
				$salesid=$obj.attr('data-id'),
				$OrderId=$obj.parents('tr').find('td:eq(0)>input').val(),
				$mHtml=$obj.html(),
				$sHtml=$('#sales_select_hide').html(),
				$val;
			$obj.html($sHtml+'<span style="display:none;">'+$mHtml+'</span>');
			$salesid && $obj.find('select').val($salesid).focus();
			$obj.find('select').on('blur', function(){
				$val=$(this).val();
				if($val && $val!=$salesid){
					$.post('?', 'do_action=orders.orders_edit_sales&OrderId='+$OrderId+'&SalesId='+$(this).val(), function(data){
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
	
	orders_import_init:function(){
		/*
		frame_obj.file_upload($('#ExcelUpload'), '', '', 'file_upload', true, 1, function(filepath, count){
			$('#excel_path').val(filepath);
			$('#file_path').text('文件已上传');
		}, '*.csv;*.xlsx;*.xls');
		*/
		$('form[name=import_form]').fileupload({
			url: '/manage/?do_action=action.file_upload_plugin&size=file',
			//acceptFileTypes: /^application\/vnd\.(ms-excel|openxmlformats-officedocument.spreadsheetml.sheet)$/i, //csv xlsx xls
			acceptFileTypes: /^application\/(vnd.ms-excel|vnd.openxmlformats-officedocument.spreadsheetml.sheet|csv|xlsx|xls)$/i, //csv xlsx xls
			callback: function(filepath, count){
				$('#excel_path').val(filepath);
				$('#file_path').text('文件已上传');
			}
		});
		$('form[name=import_form]').fileupload(
			'option',
			'redirect',
			window.location.href.replace(/\/[^\/]*$/, '/cors/result.html?%s')
		);
		frame_obj.submit_form_init($('form[name=import_form]'), '', '', '', function(data){
			if(data.ret==2){
				$('#explode_progress_import').append(data.msg[1]);
				$('form[name=import_form] input[name=Number]').val(data.msg[0]);
				$('form[name=import_form] .submit_btn').attr('disabled', false).click();
			}else if(data.ret==1){
				$('#explode_progress_import').append(data.msg);
			}else{
				global_obj.win_alert(lang_obj.global.set_error);
			}
		});
	},
	
	orders_global_init:function(){
		$('input[name=Time]').daterangepicker({
			showDropdowns:true,
			format:'YYYY/MM/DD'
		});
		
		var bindCheck=function(obj, type){
			var $this=obj.children('input'),
				value=$this.val(),
				OrderIdVal=$('#orders input[name=OrderIdStr]').val();
			if(type==false || (!type && $this.attr('checked'))){
				$this.attr('checked', false);
				if(global_obj.in_array(value, OrderIdVal.split('|'))){
					$('#orders input[name=OrderIdStr]').val(OrderIdVal.replace('|'+value+'|', '|'));
				}
			}else{
				$this.attr('checked', true);
				if(!global_obj.in_array(value, OrderIdVal.split('|'))){
					$('#orders input[name=OrderIdStr]').val(OrderIdVal+value+'|');
				}
			}
		}
		window.orders_list_work=function(){
			$('.orders_list input:checkbox').click(function(){
				bindCheck($(this).parent(), $(this)[0].checked);
				if($('.orders_list input:checkbox').size()==$('.orders_list input:checkbox:checked').size()){
					$('input[name=selectAll]').val(1);
				}else{
					$('input[name=selectAll]').val(0);
				}
			});
			
			$('.orders_list li').each(function(){//默认全部勾选
				bindCheck($(this), true);
			});
			$('input[name=selectAll]').val(1);
		}
		orders_list_work();
		
		$('#orders .btn_select_all').off().on('click', function(){
			var checked=$('input[name=selectAll]').val()==1?false:true;
			$('input[name=selectAll]').val(checked?1:0);
			$('.orders_list li').each(function(){
				bindCheck($(this), checked);
			});
		});
	},
	
	orders_explode_reload:function(){
		var orders_h=$('#orders').outerHeight(),
			search_h=$('.export_left .rows:eq(0)').outerHeight(),
			button_h=$('.export_left .rows:eq(2)').outerHeight(),
			rows_hd_h=$('.export_right .rows_hd').outerHeight();
		$('#orders .orders_list').css({'height':(orders_h-search_h-button_h-16-2)});
		
		$('#orders .export_menu').css({'height':(orders_h-rows_hd_h-40-2)});
		$('#orders .export_menu').jScrollPane();
	},
	
	orders_explode_init:function(){
		orders_obj.orders_global_init();
		orders_obj.orders_explode_reload();
		$(window).resize(function(){
			orders_obj.orders_explode_reload();
		});
		
		$('#orders .export_menu .jspPane').dragsort({
			dragSelector:'li',
			dragSelectorExclude:'a',
			placeHolderTemplate:'<li class="placeHolder"></li>',
			scrollSpeed:5
		});
		
		$('#orders .btn_save').off().on('click', function(){
			var data=$('#orders .export_menu li').map(function(){
				return $(this).children('input').val();
			}).get();
			$.post('?', $('form[name=export_form]').serialize()+'&do_action=orders.orders_explode_menu&sort_order='+data.join('|'), function(data){
				if(data.ret==1){
					global_obj.win_alert(data.msg);
				}
			}, 'json');
			return false;
		});
		
		$('#orders .orders_search .btn_ok').off().on('click', function(){
			$.ajax({
				type:'GET',
				url:'?m=orders&a=export&Keyword='+$('input[name=Keyword]').val()+'&Time='+$('input[name=Time]').val()+'&Status='+$('select[name=Status]').val()+'&TimeType='+$('select[name=TimeType]').val(),
				async:false,
				success:function(data){
					$('#explode_box').html($(data).find('#explode_box').html());
					orders_obj.orders_explode_reload();
					orders_list_work();
				}
			});
			return false;
		});
		
		$('#orders .orders_search input[name=Keyword]').keydown(function(e){
			var key=window.event?e.keyCode:e.which;
			if(key==13){
				$('#orders .orders_search .btn_ok').click();
				return false;
			}
		});
		
		frame_obj.submit_form_init($('form[name=export_form]'), '', '', '', function(data){
			if(data.ret==2){
				$('#explode_progress_export').append(data.msg[1]);
				$('form[name=export_form] input[name=Number]').val(data.msg[0]);
				$('form[name=export_form] .submit_btn').click();
			}else if(data.ret==1){
				$('form[name=export_form]').find('input:submit').attr('disabled', false); //初始化
				$('form[name=export_form] input[name=Number]').val(0); //初始化
				window.location='./?do_action=orders.orders_explode_down&Status=ok';
			}else{
				global_obj.win_alert(lang_obj.global.set_error);
			}
		});
	},
	
	orders_check_reload:function(){
		var orders_h=$('#orders').outerHeight(),
			payment_h=$('#orders .rows:eq(0)').outerHeight(),
			search_h=$('#orders .rows:eq(1)').outerHeight(),
			button_h=$('#orders .rows:eq(3)').outerHeight();
		$('#orders .orders_list').css({'height':(orders_h-payment_h-search_h-button_h-16-2)});
	},
	
	orders_check_init:function(){
		orders_obj.orders_global_init();
		orders_obj.orders_check_reload();
		$(window).resize(function(){
			orders_obj.orders_check_reload();
		});
		
		$('#orders .orders_search .btn_ok').off().on('click', function(){
			$.ajax({
				type:'GET',
				url:'?m=orders&a=check&Keyword='+$('input[name=Keyword]').val()+'&Time='+$('input[name=Time]').val()+'&Status='+$('select[name=Status]').val(),
				async:false,
				success:function(data){
					$('#explode_box').html($(data).find('#explode_box').html());
					orders_obj.orders_check_reload();
					orders_list_work();
				}
			});
			return false;
		});
		
		frame_obj.submit_form_init($('form[name=check_form]'));
	},
	
	orders_view:function(){
		$('#orders .baseinfo .orderinfo li:odd').addClass('child_2n');
		
		orders_obj.orders_address();
		
		var $infoHeight=Math.max($('.shipping').height(), $('.orderinfo').height());
		$('.shipping, .orderinfo').css('min-height', $infoHeight);
		
		orders_obj.orders_time_line_complete($('#order_status_module').attr('status'));
		$('.form_status input.shipping_time').daterangepicker({
			singleDatePicker:true,
			showDropdowns:true,
			timePicker:false,
			format:'YYYY-MM-DD'
		});
		
		$('#orders button.cancel').click(function(){
			$('.form_address').slideUp(300);
			$('.form_shipping').slideUp(300);
			$('.form_orders').slideUp(300);
			$('.form_status').slideUp(300);
			$('.form_track_no').slideUp(300);
			$('.form_remarks').slideUp(300);
			$('.form_remark_log').slideUp(300);
			$('.baseinfo').slideDown(300);
		});
		$('.baseinfo').delegate('a.edit', 'click', function(){
			$('.baseinfo').slideUp(300);
			if($(this).hasClass('address')){
				orders_obj.set_address();
				$('.form_address').slideDown(300).css('overflow', 'visible');
			}else if($(this).hasClass('ship')){
				orders_obj.set_shipping_info();
				$('.form_shipping').slideDown(300);
			}else if($(this).hasClass('orders')){
				orders_obj.set_orders_info();
				$('.form_orders').slideDown(300);
			}else if($(this).hasClass('status')){
				$('.form_status').slideDown(300);
			}else if($(this).hasClass('track_no')){
				$('.form_track_no').slideDown(300);
			}else if($(this).hasClass('remarks')){
				$('.form_remarks').slideDown(300);
			}else if($(this).hasClass('remark_log')){
				$('.form_remark_log').slideDown(300);
			}
		});
		
		$('.form_shipping').delegate('select.shipping_method_sid', 'change', function(){
			var obj=$(this).find('option:selected'),
				type=obj.attr('data-type'),
				insurance=obj.attr('data-insurance');
			$(this).siblings('input:hidden').val(type).siblings('.shipping_method_insurance').text(ueeshop_config.currency+insurance);
		});
		$('#modShipping').click(function(){
			var error=0, type;
			$('.form_shipping select.shipping_method_sid').each(function(){ //检测是否选择发货方式
				type=$(this).siblings('input:hidden').val();
				if($(this).val()==-1 || ($(this).val()==0 && type=='')){
					$(this).addClass('red');
					error=1;
				}
			});
			if(error==1) return false;
			$(this).attr('disabled', 'disabled');
			$.post('./?do_action=orders.orders_mod_shipping', $('.form_shipping').serialize(), function(data){
				if(data.ret==1){
					var json=data.msg.info,
						currency=ueeshop_config.currency;
					$('.shipping_info .shipping_price').text(currency+(parseFloat(json.ShippingPrice)).toFixed(2));
					$('.shipping_info .shipping_insurance').text(currency+(parseFloat(json.ShippingInsurancePrice)).toFixed(2));
					if(!json.ShippingExpress && json.ShippingMethodSId==0 && !json.ShippingMethodType){ //多个发货地
						for(k in json.ShippingOvSId){
							$('.shipping_info li[data-name-id="'+k+'"]').find('.shipping_name').text(json.ShippingOvExpress[k]);
							$('.shipping_info li[data-price-id="'+k+'"]').find('.shipping_price').text(currency+(parseFloat(json.ShippingOvPrice[k])).toFixed(2)).siblings('.shipping_insurance').text(currency+(parseFloat(json.ShippingOvInsurancePrice[k])).toFixed(2));
						}
					}else{ //单个发货地
						$('.shipping_info .shipping_name').html(json.ShippingExpress);
					}
					$('.baseinfo').slideDown(300);
					$('.form_shipping').attr('data', data.msg.text).slideUp(300);
					$('#orders_shipping_price').text(currency+(parseFloat(json.ShippingPrice)+parseFloat(json.ShippingInsurancePrice)).toFixed(2));
					$('#orders_total_price').text(currency+(parseFloat(json.TotalAmount)).toFixed(2));
				}
			}, 'json');
			$(this).removeAttr('disabled');
			return false;
		});

		$('.form_orders').delegate(
			'input[name=ProductPrice], input[name=Discount], input[name=ShippingPrice], input[name=ShippingInsurancePrice], input[name=PayAdditionalFee]',
			'keyup paste change',
			function(){
				p=/[^\d\.]/g;
				var v=$(this).val();
				value=v.replace(p, '');
				$(this).val(value);
				if(value==''){
					$(this).val(0);
				}
				
				orders_obj.set_fee_info();
			}
		);
		$('form[name=form_orders]').submit(function(){
			if(global_obj.check_form($('form[name=form_orders] *[notnull]'))){return false;};
			$('#modOrders').attr('disabled', 'disabled');
			
			$.post('./?do_action=orders.orders_mod_info', $('form[name=form_orders]').serialize(), function(data){
				$('#modOrders').removeAttr('disabled');
				if(data.status==1){					
					currency=ueeshop_config.currency;
					$('#orders_product_price').text(currency+parseFloat(data.info.ProductPrice).toFixed(2));
					if(parseFloat(data.info.DiscountPrice)>0){
						$('#orders_discount_price').text(currency+(parseFloat(data.info.DiscountPrice)).toFixed(2));
					}else{
						$('#orders_discount').text(parseFloat(data.info.Discount)+'%');
					}
					$('#orders_user_discount').text(parseFloat(data.info.UserDiscount)+'%');
					$('#orders_shipping_price').text(currency+(parseFloat(data.info.ShippingPrice)+parseFloat(data.info.ShippingInsurancePrice)).toFixed(2));
					if(parseFloat(data.info.CouponPrice)>0){
						$('#orders_coupon').text(currency+(parseFloat(data.info.CouponPrice)).toFixed(2));
					}else{
						$('#orders_coupon').text((100-parseFloat(data.info.CouponDiscount)*100)+'%');
					}
					$('#orders_handing_fee').text(currency+(parseFloat(data.info.HandingFee)).toFixed(2));
					$('#orders_total_price').text(currency+parseFloat(data.info.TotalAmount).toFixed(2));
					$('.shipping_info .shipping_price').text(currency+(parseFloat(data.info.ShippingPrice)).toFixed(2));
					$('.shipping_info .shipping_insurance').text(currency+(parseFloat(data.info.ShippingInsurancePrice)).toFixed(2));
					
					$('.baseinfo').slideDown(300);
					$('form[name=form_orders]').attr('data', data.text).slideUp(300);
					$('.form_shipping').attr('data', data.shipping);					
				}
			}, 'json');
			return false;
		});
		
		$('form[name=form_status]').delegate('input[name=OrderStatus]', 'click', function(){
			if($(this).val()==7){
				$('form[name=form_status] input[name=TrackingNumber]').removeAttr('notnull');
			}else if($(this).val()==5){
				$('form[name=form_status] input[name=TrackingNumber]').attr('notnull', '');
			}
		});
		$('form[name=form_status]').submit(function(){
			if(global_obj.check_form($('form[name=form_status] *[notnull]'))){return false;};
			$('#modStatus').attr('disabled', 'disabled');
			$.post('./?do_action=orders.orders_mod_status', $('form[name=form_status]').serialize(), function(data){
				$('#modStatus').removeAttr('disabled');
				if(data.status==1){
					window.location.reload();					
				}else{
					global_obj.win_alert(data.msg);
				}
			}, 'json');
			return false;
		});
		
		$('form[name=form_track_no]').submit(function(){
			if(global_obj.check_form($('form[name=form_track_no] *[notnull]'))){return false;};
			$('#modTrackNo').attr('disabled', 'disabled');
			$.post('./?do_action=orders.orders_track_no', $('form[name=form_track_no]').serialize(), function(data){
				$('#modTrackNo').removeAttr('disabled');
				if(data.status==1){
					$('.baseinfo').slideDown(300);
					$('form[name=form_track_no]').slideUp(300);
					$('#tracking_number_module font').text($('form[name=form_track_no] input[name=TrackingNumber]').val());
					//window.location.reload();					
				}else{
					global_obj.win_alert(data.msg);
				}
			}, 'json');
			return false;
		});
		
		$('form[name=form_remarks]').submit(function(){
			if(global_obj.check_form($('form[name=form_remarks] *[notnull]'))){return false;};
			$('#modRemarks').attr('disabled', 'disabled');
			
			$.post('./?do_action=orders.orders_remarks', $('form[name=form_remarks]').serialize(), function(data){
				$('#modRemarks').removeAttr('disabled');
				if(data.status==1){
					$('.baseinfo').slideDown(300);
					$('form[name=form_remarks]').slideUp(300);
					$('#remarks_module font').text($('form[name=form_remarks] textarea[name=Remarks]').val());
					//window.location.reload();					
				}else{
					global_obj.win_alert(data.msg);
				}
			}, 'json');
			return false;
		});
		
		$('form[name=form_remark_log]').submit(function(){
			if(global_obj.check_form($('form[name=form_remark_log] *[notnull]'))){return false;};
			$('#modRemarkLog').attr('disabled', 'disabled');
			
			$.post('./?do_action=orders.orders_remark_log', $('form[name=form_remark_log]').serialize(), function(data){
				$('#modRemarkLog').removeAttr('disabled');
				if(data.status==1){
					$('.baseinfo').slideDown(300);
					$('form[name=form_remark_log]').slideUp(300);
				}else{
					global_obj.win_alert(data.msg);
				}
			}, 'json');
			return false;
		});
		
		//产品编辑
		frame_obj.del_init($('#orders_products_list')); //订单产品列表
		
		$('.btn_prod_mod').click(function(){
			var $this=$(this);
			if(global_obj.check_form($('#orders_products_form *[notnull]'))){return false;};
			$this.attr('disabled', true);
			$.post('?', $('#orders_products_form').serialize(), function(data){
				$this.attr('disabled', false);
				if(data.ret!=1){
					global_obj.win_alert(data.msg);
				}else{
					window.location=$('#back_action').val();
				}
			}, 'json');
		});
	},
	
	orders_time_line_complete:function(status){	//订单状态轴
		$('.complete').css('width', ($('.time-line-bg').width()*(status-1)/6));
		$(window).resize(function(){
			$('.complete').css('width', ($('.time-line-bg').width()*(status-1)/6));
		});
	},
	
	/********地址模块（开始）********/
	set_address:function(){
		/******************设置默认地址（开始）***********************/
		var address=jQuery.parseJSON($('.form_address').attr('data'));
		$('input[name=FirstName]').val(address.FirstName).parent().next().find('input[name=LastName]').val(address.LastName)
		$('input[name=AddressLine1]').val(address.AddressLine1);
		$('input[name=AddressLine2]').val(address.AddressLine2);
		$('input[name=City]').val(address.City);
		
		var index=$('select[name=country_id]').find('option[value='+address.CId+']').eq(0).attr('selected', 'selected').index();
		$('#country_chzn a span').text(address.Country);
		$('#country_chzn ul.chzn-results li.group-option').eq(index).addClass('result-selected');
		orders_obj.get_state_from_country(address.CId);
		if(address.CId==30||address.CId==211){
			$('select[name=tax_code_type]').find('option[value='+address.CodeOptionId+']').attr('selected', 'selected');
			$('input[name=tax_code_value]').attr('maxlength', (address.CodeOption==1?11:(address.CodeOption==2?14:12))).val(address.TaxCode);
		}
		if(address.SId>0){
			$('#zoneId div a span').text(address.State);
			var sindex=$('select[name=Province]').find('option[value='+address.SId+']').attr('selected', 'selected').index();
			$('#zoneId ul.chzn-results li.group-option').eq(sindex-1).addClass('result-selected');
		}else{
			$('input[name=State]').val(address.State);
		}
		$('input[name=ZipCode]').val(address.ZipCode);
		$('input[name=CountryCode]').val(address.CountryCode).next().find('input[name=PhoneNumber]').val(address.PhoneNumber);
		/******************设置默认地址（结束）***********************/
	},
	
	orders_address:function(){
		/******************地址验证（开始）***********************/
		$('.chzn-container-single .chzn-search').css('height', $('.chzn-container-single .chzn-search input').height());
		
		$('a.chzn-single').click(function(){
			$(this).parent().next('p.errorInfo').text('');
			if($(this).hasClass('chzn-single-with-drop')){
				$(this).blur().removeClass('chzn-single-with-drop').next().css({'left':'-9000px'}).parent().removeClass('chzn-container-active').css('z-index', '0').find('li.result-selected').removeClass('highlighted');
			}else{
				$(this).blur().addClass('chzn-single-with-drop').next().css({'left':'0', 'top':'27px'}).parent().addClass('chzn-container-active').css('z-index', '10').find('li.result-selected').addClass('highlighted');
			}
		});
		
		$('.chzn-results li.group-option').live('mouseover', function(){
			$(this).parent().find('li').removeClass('highlighted');
			$(this).addClass('highlighted');
		}).live('mouseout', function(){
			$(this).removeClass('highlighted');
		});
		
		$('#country_chzn li.group-option').click(function(){	//Select Country
			var obj=$('#country_chzn li.group-option').removeClass('result-selected').index($(this));
			var s_cid=$('select[name=country_id]').val();
			$(this).addClass('result-selected').parent().parent().css({'left':'-9000px'}).parent().removeClass('chzn-container-active').children('a').removeClass('chzn-single-with-drop').find('span').text($(this).text()).parent().parent().prev().find('option').eq(obj+1).attr('selected', 'selected');
			
			var cid=$('select[name=country_id]').val();
			(s_cid!=cid) && orders_obj.get_state_from_country(cid);	//change country
		});
		
		$('#zoneId li.group-option').live('click', function(){
			var obj=$('#zoneId li.group-option').removeClass('result-selected').index($(this));
			$(this).addClass('result-selected').parent().parent().css({'left':'-9000px'}).parent().removeClass('chzn-container-active').children('a').removeClass('chzn-single-with-drop').find('span').text($(this).text()).parent().parent().prev().find('option').eq(obj+1).attr('selected', 'selected');
		});
			
		$(document).click(function(e){ 
			e = window.event || e; // 兼容IE7
			obj = $(e.srcElement || e.target);
			if (!$(obj).is("#country_chzn, #country_chzn *")) { 
				$('#country_chzn').removeClass('chzn-container-active').css('z-index', '0').children('a').blur().removeClass('chzn-single-with-drop').end().children('.chzn-drop').css({'left':'-9000px'}).find('input').val('').parent().next().find('.group-option').addClass('active-result');
			} 
			if (!$(obj).is("#zoneId .chzn-container, #zoneId .chzn-container *")) { 
				$('#zoneId .chzn-container').removeClass('chzn-container-active').css('z-index', '0').children('a').blur().removeClass('chzn-single-with-drop').end().children('.chzn-drop').css({'left':'-9000px'}).find('input').val('').parent().next().find('.group-option').addClass('active-result');
			} 
		});
		
		//JS search result from tagert tags
		jQuery.expr[':'].Contains = function(a,i,m){
			return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase())>=0;
		};
		function filterList(input, list) { 
			$(input)
			.change( function () {
				var filter = $(this).val();
				if(filter) {
					$matches = $(list).find('li:Contains(' + filter + ')');
					$('li', list).not($matches).removeClass('active-result');
					$matches.addClass('active-result');
				}else {
					$(list).find("li").addClass('active-result');
				}
				return false;
			})
			.keyup( function () {
				$(this).change();
			});
		}
		filterList("#country_chzn .chzn-search input", $("#country_chzn .chzn-results"));
		filterList("#zoneId .chzn-search input", $("#zoneId .chzn-results"));
		


		$('#useAddress').click(function(){
			if(!check_form_address()){return false;}
			$(this).attr('disabled', 'disabled');
			$.post('./?do_action=orders.orders_mod_address', $('.form_address').serialize(), function(data){
				if(data.status==1){
					var address = data.info.address;
					address=address.replace(/\\'/g, "'");
					$('.baseinfo .address_info li:eq(0)').find('span').html(data.info.name).parent().next().next().find('span').html(address).parent().next().next().find('span').html(data.info.zipcode).parent().next().next().find('span').html(data.info.phone);
					$('.baseinfo').slideDown(300);
					$('.form_address').attr('data', data.text).slideUp(300);
				}
			}, 'json');
			
			$(this).removeAttr('disabled');
			return false;
		});
		
		$('input[name=FirstName], input[name=LastName], input[name=PhoneNumber], input[name=AddressLine1], input[name=City], input[name=ZipCode]').focus(function(){$(this).next('p.errorInfo').text('');});
		$('select[name=tax_code_type]').change(function(){
			maxlen=$(this).val()==1?11:($(this).val()==2?14:12);
			$(this).next('input[name=tax_code_value]').attr('maxlength', maxlen).val('');
		});
		
		function check_form_address(){
			firstname=$('.form_address input[name=FirstName]');
			lastname=$('.form_address input[name=LastName]');
			address=$('.form_address input[name=AddressLine1]');
			city=$('.form_address input[name=City]');
			state=$('.form_address select[name=Province]');
			country=$('.form_address select[name=country_id]');
			taxcode=$('.form_address #taxCodeValue');
			tariff=$('.form_address #tariffCodeValue');
			zipcode=$('.form_address input[name=ZipCode]');
			phone=$('.form_address input[name=PhoneNumber]');
			firstnameTips=lastnameTips=addressTips=cityTips=stateTips=countryTips=taxTips=tariffTips=zipTips=phoneTips='';
			
			if(firstname.val()=='')
				firstnameTips=lang_obj.user.address_tips.firstname
			else if(firstname.val().length<2)
				firstnameTips=lang_obj.user.address_tips.firstname_length

			if(lastname.val()=='')
				lastnameTips=lang_obj.user.address_tips.lastname
			else if(lastname.val().length<2)
				lastnameTips=lang_obj.user.address_tips.lastname_length

			if(address.val()=='')
				addressTips=lang_obj.user.address_tips.address
			else if(address.val().length<5)
				addressTips=lang_obj.user.address_tips.address_length

			if(city.val()=='')
				cityTips=lang_obj.user.address_tips.city
			else if(city.val().length<3)
				cityTips=lang_obj.user.address_tips.city_length

			if(country.val()==-1)
				countryTips=lang_obj.user.address_tips.country
			
			if(typeof(state.attr("disabled"))=="undefined" && state.val()==-1)	//typeof($("#aid").attr("rel"))=="undefined"
				stateTips=lang_obj.user.address_tips.state
			
			if(typeof(taxcode.attr("disabled"))=="undefined"){
				str=taxcode.prev().val()==1?'CPF':'CNPJ';
				taxlen=taxcode.attr('maxlength');
				if(taxcode.val()=='')
					taxTips=lang_obj.user.address_tips.taxcode.replace('%str%', str)
				else if(taxcode.val().length<taxlen)
					taxTips=lang_obj.user.address_tips.taxcode_length.replace('%str%', str).replace('%taxlen%', taxlen);
			}
			
			if(typeof(tariff.attr("disabled"))=="undefined"){
				str=tariff.prev().val()==3?'Personal ID':'VAT ID';
				if(tariff.val()=='')
					tariffTips=lang_obj.user.address_tips.tariff.replace('%str%', str)
				else if(tariff.val().length<12)
					tariffTips=lang_obj.user.address_tips.tariff_length.replace('%str%', str)
			}

			if(zipcode.val()=='')
				zipTips=lang_obj.user.address_tips.zip
			else if(zipcode.val().length<4)
				zipTips=lang_obj.user.address_tips.zip_length

			if(phone.val()=='')
				phoneTips=lang_obj.user.address_tips.phone
			else if(!(/^[0-9\-]+$/g.test(phone.val())))//phone.val()==''
				phoneTips=lang_obj.user.address_tips.phone_format
			else if(phone.val().replace('-', '').length<7)
				phoneTips=lang_obj.user.address_tips.phone_length
			
			
			firstname!='' && firstname.next('p.errorInfo').text(firstnameTips);
			lastname!='' && lastname.next('p.errorInfo').text(lastnameTips);
			address!='' && address.next('p.errorInfo').text(addressTips);
			city!='' && city.next('p.errorInfo').text(cityTips);
			state!='' && state.next().next('p.errorInfo').text(stateTips);
			country!='' && country.next().next('p.errorInfo').text(countryTips);
			taxcode!='' && taxcode.next('p.errorInfo').text(taxTips);
			tariff!='' && tariff.next('p.errorInfo').text(tariffTips);
			zipcode!='' && zipcode.next('p.errorInfo').text(zipTips);
			phone!='' && phone.next('p.errorInfo').text(phoneTips);
			
			if(firstnameTips==''&&lastnameTips==''&&addressTips==''&&cityTips==''&&stateTips==''&&countryTips==''&&taxTips==''&&tariffTips==''&&zipTips==''&&phoneTips=='')
				return true;
			else
				return false;
		}
		
		//选择paypal收货地址信息
		$('#orders .baseinfo .btn_use').click(function(){
			$(this).attr('disabled', 'disabled');
			$.post('./?do_action=orders.orders_mod_paypal_address', {'OrderId':$(this).attr('date-id'), 'IsUse':$(this).attr('date-use')}, function(data){
				if(data.ret==1){
					window.location.reload();
				}
			}, 'json');
			$(this).removeAttr('disabled');
			return false;
		});
		/******************地址验证（结束）***********************/
	},
	
	get_state_from_country:function(cid){
		/******************国家选择（开始）***********************/
		$.ajax({
			url:"./?do_action=orders.select_country",
			async:false,
			type:"POST",
			data:{"CId": cid},
			dataType:"json",
			success: function(data){
				if(data.status==1){
					d=data.contents;
					if(d==-1){
						$('#zoneId').css({'display':'none'}).find('select').attr('disabled', 'disabled');
						$('#state').css({'display':'table-row'}).find('input').removeAttr('disabled');
					}else{
						$('#zoneId').css({'display':'table-row'}).find('select').removeAttr('disabled');
						$('#state').css({'display':'none'}).find('input').attr('disabled', 'disabled');
						str='';
						var vselect='<option value="-1"></option>';
						var vli='';
						for(i=0;i<d.length;i++){
							vselect+='<option value="'+d[i]['SId']+'">'+d[i]['States']+'</option>';
							vli+='<li class="group-option active-result">'+d[i]['States']+'</li>';
						}
						$('#zoneId select').html(vselect);
						$('#zoneId ul').html(vli);
						$('#zoneId .chzn-container a span').text(lang_obj.global.selected+'---');
					}
					$('#countryCode').val('+'+data.code);
					$('#phoneSample span').text(data.code);
					if(data.cid==30){
						$('#taxCode').css({'display':'table-row'}).find('select, input').removeAttr('disabled');
						$('#tariffCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled').parent().find('p.errorInfo').text('');
					}else if(data.cid==211){
						$('#tariffCode').css({'display':'table-row'}).find('select, input').removeAttr('disabled');
						$('#taxCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled').parent().find('p.errorInfo').text('');
					}else{
						$('#taxCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled').parent().find('p.errorInfo').text('');
						$('#tariffCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled').parent().find('p.errorInfo').text('');
					}
				}
			}
		});
		/******************国家选择（结束）***********************/
	},
	/********地址模块（结束）********/
	
	set_orders_info:function(){
		/******************设置订单信息（开始）***********************/
		var orders=jQuery.parseJSON($('.form_orders').attr('data'));
		var shipping=jQuery.parseJSON($('.form_shipping').attr('data'));
		$('.form_orders input').removeAttr('style');
		
		if(orders.DiscountPrice>0){
			$('input[name=DiscountPrice]').val(orders.DiscountPrice);
			$('input[name=Discount]').val(0);
			$('.form_orders .discount_price').show();
			$('.form_orders .discount').hide();
		}else{
			$('input[name=DiscountPrice]').val(0);
			$('input[name=Discount]').val(orders.Discount);
			$('.form_orders .discount_price').hide();
			$('.form_orders .discount').show();
		}
		
		if(orders.CouponPrice>0){
			$('input[name=CouponPrice]').val(orders.CouponPrice);
			$('input[name=CouponDiscount]').val(0);
			$('.form_orders .coupon_price').show();
			$('.form_orders .coupon_discount').hide();
		}else{
			$('input[name=CouponPrice]').val(0);
			$('input[name=CouponDiscount]').val(orders.CouponDiscount*100);
			$('.form_orders .coupon_price').hide();
			$('.form_orders .coupon_discount').show();
		}
		
		$('input[name=ProductPrice]').val(orders.ProductPrice);
		$('input[name=UserDiscount]').val(100-orders.UserDiscount);
		$('input[name=ShippingPrice]').val(shipping.ShippingPrice);
		$('input[name=ShippingInsurancePrice]').val(shipping.ShippingInsurancePrice);
		$('input[name=PayAdditionalFee]').val(orders.PayAdditionalFee);
		$('input[name=PayAdditionalAffix]').val(orders.PayAdditionalAffix);
		
		orders_obj.set_fee_info();
		/******************设置订单信息（结束）***********************/
	},
	
	set_fee_info:function(){
		currency=ueeshop_config.currency;
		coupondiscount=parseFloat($('input[name=CouponDiscount]').val());
		couponprice=parseFloat($('input[name=CouponPrice]').val());
		productprice=parseFloat($('.form_orders input[name=ProductPrice]').val());
		discount=parseFloat($('.form_orders input[name=Discount]').val());
		discountprice=parseFloat($('.form_orders input[name=DiscountPrice]').val());
		userdiscount=parseFloat($('.form_orders input[name=UserDiscount]').val());
		shippingprice=parseFloat($('.form_orders input[name=ShippingPrice]').val());
		shippinginsuranceprice=parseFloat($('.form_orders input[name=ShippingInsurancePrice]').val());
		payadditionalfee=parseFloat($('.form_orders input[name=PayAdditionalFee]').val());
		payadditionalAffix=parseFloat($('.form_orders input[name=PayAdditionalAffix]').val());
		
		/*手续费显示*/
		var feeprice=(productprice*((100-discount)/100)*((100-userdiscount)/100)*((100-coupondiscount)/100) + shippingprice + shippinginsuranceprice - couponprice - discountprice) * (payadditionalfee/100);
		var feetext="(" + currency + productprice + " * " + (100-discount) + "% * " + (100-userdiscount) + "% * " + (100-coupondiscount) + "% + " + currency + shippingprice + " + " + currency + shippinginsuranceprice + " - " + currency + couponprice + " - " + currency + discountprice + ") * " + payadditionalfee+"% = " + currency + feeprice.toFixed(2);
		$('#orders_fee_value').text(feetext);
		
		/*订单总额显示*/
		var totalprice=(productprice*((100-discount)/100)*((100-userdiscount)/100)*((100-coupondiscount)/100) + shippingprice + shippinginsuranceprice - couponprice - discountprice) * ((100+payadditionalfee)/100)+payadditionalAffix;
		$('#orders_amount_value').text(totalprice.toFixed(2));
	},
	
	set_shipping_info:function(){	//后台订单更新，获取发货方式
		var shipData=jQuery.parseJSON($('.form_shipping').attr('data'));
		$.post('./?do_action=orders.orders_shipping_method', 'OrderId='+shipData.OrderId, function(data){
			if(data.ret==1){
				var rowObj, rowStr, selected, start, SId, isInsurance;
				for(OvId in data.msg.info){
					rowStr='';
					start=0;
					if(parseInt(data.msg.isOverseas)==1){
						SId=shipData.ShippingOvSId[OvId];
						isInsurance=shipData.ShippingOvInsurance[OvId];
					}else{
						SId=shipData.ShippingMethodSId;
						isInsurance=shipData.ShippingInsurance;
					}
					for(i=0; i<data.msg.info[OvId].length; i++){
						rowObj=data.msg.info[OvId][i];
						if(parseFloat(rowObj.ShippingPrice)<0) continue;
						selected='';
						if(SId==rowObj.SId){
							selected='selected';
							start=i;
						}
						rowStr+='<option value="'+rowObj.SId+'" data-type="'+rowObj.type+'" data-insurance="'+rowObj.InsurancePrice+'"  '+selected+'>'+rowObj.Name.toUpperCase()+' - '+ueeshop_config.currency+rowObj.ShippingPrice+'</option>';
					}
					$('.form_shipping select[name=ShippingMethodSId\\['+OvId+'\\]]').html(rowStr).siblings('input:hidden').val(data.msg.info[OvId][start].type).siblings('.shipping_method_insurance').text(ueeshop_config.currency+data.msg.info[OvId][start].InsurancePrice).siblings(':checkbox').attr('checked', (isInsurance?true:false));
				}
			}
		}, 'json');
	},
	
	waybill_init:function(){
		$('.btn_more').on('click', function(){
			if($(this).hasClass('current')){
				$(this).removeClass('current').text(lang_obj.global.open);
				$(this).parents('table').find('.more_product').hide();
			}else{
				$(this).addClass('current').text(lang_obj.global.pack_up);
				$(this).parents('table').find('.more_product').show();
			}
			return false;
		});
		
		$('.waybill_products_list input[name=Waybill\\[\\]]').change(function(){
			var $val=parseInt($(this).val()), $max=parseInt($(this).attr('data-max'));
			$val<0 && ($val=0);
			$val>$max && ($val=$max);
			$(this).val($val);
		});
		
		$('.waybill_products_list input[name=ShippingTime]').daterangepicker({
			singleDatePicker:true,
			showDropdowns:true,
			timePicker:false,
			format:'YYYY-MM-DD'
		});
		
		$('.shipped_submit_btn').click(function(){
			var $obj=$(this).parents('table'),
				$orderid=$obj.attr('orderid'),
				$number=$obj.attr('number'),
				$OvId=$obj.attr('data-id'),
				$data=Object();
			if(global_obj.check_form($obj.find('*[notnull]'), $obj.find('*[format]'), 1)){return false;};
			
			$obj.find('.ship_info input').each(function(){
				$data[$(this).attr('name')]=$(this).val();
			});
			$.post('./?do_action=orders.orders_shipped', {'Data':$data, 'OvId':$OvId, 'Number':$number, 'OrderId':$orderid}, function(data){
				if(data.ret==1){
					window.location.reload();
				}else{
					alert('操作失败！');
				}
			}, 'json');
			return false;
		});
		
		$('.waybill_submit_btn').click(function(){
			var $obj=$(this).parents('table'),
				$orderid=$obj.attr('orderid'),
				$number=$obj.attr('number'),
				$data=Object();
			$obj.find('input[name=Waybill\\[\\]]').each(function(){
				if($(this).val()>0){
					$data[$(this).attr('data-lid')]=$(this).val();
				}
			});
			$.post('./?do_action=orders.orders_products_waybill', {'Data':$data, 'Number':$number, 'OrderId':$orderid}, function(data){
				if(data.ret==1){
					window.location.reload();
				}else{
					alert('没有产品数据！');
				}
			}, 'json');
			return false;
		});
	},
	
	business_init:function(obj){
		obj=global_obj.json_encode_data(obj);
		$('#orders_products_list td span[bid]').each(function(){
			$(this).text(obj[$(this).attr('bid')]);
		});
	}
}