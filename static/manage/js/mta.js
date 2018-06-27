/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
var mta_obj={
	visits_init:function(){
		mta_obj.nav_condition(function(time_s, time_e, terminal, compare){
			var par='do_action=mta.api_get_data&Action=ueeshop_analytics_get_visits_data&TimeS='+time_s+'&TimeE='+time_e+'&Terminal='+terminal+'&Compare='+compare;
			$.post('./', par, function(data){
				$('.data_list .pv').html(data.msg.total.pv);
				$('.data_list .average_pv').html(data.msg.total.average_pv);
				$('.data_list .uv').html(data.msg.total.uv);
				$('.data_list .ip').html(data.msg.total.ip);
				var html='';
				$.each(data.msg.detail, function(key, value){
					html+='<tr>\
						<td nowrap="nowrap" valign="top">'+value['title']+'</td>\
						<td nowrap="nowrap">'+value['pv']+'<div class="compare compare_pv">0</div></td>\
						<td nowrap="nowrap">'+value['average_pv']+'<div class="compare compare_average_pv">0</div></td>\
						<td nowrap="nowrap">'+value['ip']+'<div class="compare compare_ip">0</div></td>\
						<td nowrap="nowrap">'+value['uv']+'<div class="compare compare_uv">0</div></td>\
					</tr>';
				});
				$('.detail .data_table tbody').html(html);
				mta_obj.highcharts($('.line_charts'), data.msg.line_charts);
				mta_obj.highcharts($('.browser_charts'), data.msg.browser_charts);
				if($('.nav .compared').hasClass('checked')){
					$('.compare').show();
					$('.data_list .compare_pv').html(data.msg.compare.total.pv);
					$('.data_list .compare_pv_img').removeClass('down up').addClass(data.msg.total.pv>=data.msg.compare.total.pv?'up':'down');
					$('.data_list .compare_average_pv').html(data.msg.compare.total.average_pv);
					$('.data_list .compare_average_pv_img').removeClass('down up').addClass(data.msg.total.average_pv>=data.msg.compare.total.average_pv?'up':'down');
					$('.data_list .compare_uv').html(data.msg.compare.total.uv);
					$('.data_list .compare_uv_img').removeClass('down up').addClass(data.msg.total.uv>=data.msg.compare.total.uv?'up':'down');
					$('.data_list .compare_ip').html(data.msg.compare.total.ip);
					$('.data_list .compare_ip_img').removeClass('down up').addClass(data.msg.total.ip>=data.msg.compare.total.ip?'up':'down');
					$('.detail .data_table tbody tr').each(function(index){
						var d=data.msg.compare.detail[index];
						$(this).find('.compare_pv').html(d.pv);
						$(this).find('.compare_average_pv').html(d.average_pv);
						$(this).find('.compare_uv').html(d.uv);
						$(this).find('.compare_ip').html(d.ip);
					});
				}else{
					$('.compare').hide();
				}
			}, 'json');
		});
	},
	
	visits_referrer_init:function(){
		$('#mta_detail_data .back').width($('#mta .nav').width());
		mta_obj.nav_condition(function(time_s, time_e, terminal, compare){
			var lang=$('body').hasClass('en')?'en':'zh-cn';
			var par='do_action=mta.api_get_data&Action=ueeshop_analytics_get_visits_referrer_data&TimeS='+time_s+'&TimeE='+time_e+'&Terminal='+terminal+'&Compare='+compare+'&lang='+lang;
			$.post('./', par, function(data){
				/*总览*/
				var html='';
				$.each(data.msg.total, function(key, value){
					html+='<tr data-key="'+key+'">\
						<td nowrap="nowrap" valign="top"><a href="javascript:;" class="'+key+'">'+value['title']+'</a></td>\
						<td nowrap="nowrap">'+value['pv']+'<div class="compare compare_pv">0</div></td>\
						<td nowrap="nowrap">'+value['average_pv']+'<div class="compare compare_average_pv">0</div></td>\
						<td nowrap="nowrap">'+value['ip']+'<div class="compare compare_ip">0</div></td>\
						<td nowrap="nowrap">'+value['uv']+'<div class="compare compare_uv">0</div></td>\
					</tr>';
				});
				$('.total .data_table tbody').html(html);	
				/*搜索引擎*/
				var html='';
				$.each(data.msg.detail.search_engine, function(key, value){
					html+='<tr>\
						<td nowrap="nowrap" valign="top"><a href="'+value['title']+'" target="_blank">'+value['title']+'</a></td>\
						<td nowrap="nowrap">'+value['pv']+'<div class="compare compare_pv">0</div></td>\
						<td nowrap="nowrap">'+value['average_pv']+'<div class="compare compare_average_pv">0</div></td>\
						<td nowrap="nowrap">'+value['ip']+'<div class="compare compare_ip">0</div></td>\
						<td nowrap="nowrap">'+value['uv']+'<div class="compare compare_uv">0</div></td>\
					</tr>';
				});
				$('.detail .search_engine tbody').html(html);	
				/*分享平台*/
				var html='';
				$.each(data.msg.detail.share_platform, function(key, value){
					html+='<tr>\
						<td nowrap="nowrap" valign="top"><a href="'+value['title']+'" target="_blank">'+value['title']+'</a></td>\
						<td nowrap="nowrap">'+value['pv']+'<div class="compare compare_pv">0</div></td>\
						<td nowrap="nowrap">'+value['average_pv']+'<div class="compare compare_average_pv">0</div></td>\
						<td nowrap="nowrap">'+value['ip']+'<div class="compare compare_ip">0</div></td>\
						<td nowrap="nowrap">'+value['uv']+'<div class="compare compare_uv">0</div></td>\
					</tr>';
				});
				$('.detail .share_platform tbody').html(html);	
				/*其他*/
				var html='';
				$.each(data.msg.detail.other, function(key, value){
					html+='<tr>\
						<td nowrap="nowrap" valign="top"><a href="'+value['title']+'" target="_blank">'+value['title']+'</a></td>\
						<td nowrap="nowrap">'+value['pv']+'<div class="compare compare_pv">0</div></td>\
						<td nowrap="nowrap">'+value['average_pv']+'<div class="compare compare_average_pv">0</div></td>\
						<td nowrap="nowrap">'+value['ip']+'<div class="compare compare_ip">0</div></td>\
						<td nowrap="nowrap">'+value['uv']+'<div class="compare compare_uv">0</div></td>\
					</tr>';
				});
				$('.detail .other tbody').html(html);	

				mta_obj.highcharts($('.line_charts'), data.msg.line_charts);
				if($('.nav .compared').hasClass('checked')){
					$('.compare').show();
					/*总览*/
					$('.total .data_table tbody tr').each(function(index){
						var key=$(this).attr('data-key');
						var d=data.msg.compare.total[key];
						$(this).find('.compare_pv').html(d.pv);
						$(this).find('.compare_average_pv').html(d.average_pv);
						$(this).find('.compare_uv').html(d.uv);
						$(this).find('.compare_ip').html(d.ip);
					});
					/*搜索引擎*/
					$('.detail .search_engine tbody tr').each(function(index){
						var d=data.msg.compare.detail.search_engine[index];
						$(this).find('.compare_pv').html(d.pv);
						$(this).find('.compare_average_pv').html(d.average_pv);
						$(this).find('.compare_uv').html(d.uv);
						$(this).find('.compare_ip').html(d.ip);
					});
					/*分享平台*/
					$('.detail .share_platform tbody tr').each(function(index){
						var d=data.msg.compare.detail.share_platform[index];
						$(this).find('.compare_pv').html(d.pv);
						$(this).find('.compare_average_pv').html(d.average_pv);
						$(this).find('.compare_uv').html(d.uv);
						$(this).find('.compare_ip').html(d.ip);
					});
					/*其他*/
					$('.detail .other tbody tr').each(function(index){
						var d=data.msg.compare.detail.other[index];
						$(this).find('.compare_pv').html(d.pv);
						$(this).find('.compare_average_pv').html(d.average_pv);
						$(this).find('.compare_uv').html(d.uv);
						$(this).find('.compare_ip').html(d.ip);
					});
				}else{
					$('.compare').hide();
				}
			}, 'json');
		});
		$('#mta_total_data .data_table tbody').delegate('tr td a', 'click', function(){
			var class_name=$(this).attr('class');
			if(class_name!='enter_directly'){
				$('#mta_detail_data .detail table').hide();
				$('#mta_detail_data .detail table.'+class_name).show();
				$('#mta_total_data').slideUp(500);
				$('#mta_detail_data').slideDown(500);
			}
		});
		$('#mta_detail_data .back a').click(function(){
			$('#mta_total_data').slideDown(100);
			$('#mta_detail_data').slideUp(100);
			$('#mta_detail_data .detail table').hide(800);
		});
	},
	
	visits_conversion_init:function(){
		mta_obj.nav_condition(function(time_s, time_e, terminal, compare){
			var par='do_action=mta.get_visits_conversion_data&Action=ueeshop_analytics_get_visits_conversion_data&TimeS='+time_s+'&TimeE='+time_e+'&Terminal='+terminal+'&Compare='+compare;
			$.post('./', par, function(data){
				if(data.ret==1){
					/*转化率*/
					//$('.data_list .ratio').text(data.msg.ratio.uv);
					$('.data_list .ratio').text(data.msg.ratio.complete_ratio);
					$('.data_list .ratio_uv').text(data.msg.ratio.uv);
					$('.data_list .ratio_addtocart').text(data.msg.ratio.addtocart);
					$('.data_list .ratio_operate_addtocart').text(data.msg.ratio.addtocart_ratio);
					$('.data_list .ratio_placeorder').text(data.msg.ratio.placeorder);
					$('.data_list .ratio_operate_placeorder').text(data.msg.ratio.placeorder_ratio);
					$('.data_list .ratio_complete').text(data.msg.ratio.complete);
					$('.data_list .ratio_operate_complete').text(data.msg.ratio.complete_ratio);
					/*直接输入*/
					//$('.data_list .enter_directly').text(data.msg.enter_directly.uv);
					$('.data_list .enter_directly').text(data.msg.enter_directly.complete_ratio);
					$('.data_list .enter_directly_uv').text(data.msg.enter_directly.uv);
					$('.data_list .enter_directly_addtocart').text(data.msg.enter_directly.addtocart);
					$('.data_list .enter_directly_operate_addtocart').text(data.msg.enter_directly.addtocart_ratio);
					$('.data_list .enter_directly_placeorder').text(data.msg.enter_directly.placeorder);
					$('.data_list .enter_directly_operate_placeorder').text(data.msg.enter_directly.placeorder_ratio);
					$('.data_list .enter_directly_complete').text(data.msg.enter_directly.complete);
					$('.data_list .enter_directly_operate_complete').text(data.msg.enter_directly.complete_ratio);
					/*分享平台*/
					//$('.data_list .share_platform').text(data.msg.share_platform.uv);
					$('.data_list .share_platform').text(data.msg.share_platform.complete_ratio);
					$('.data_list .share_platform_uv').text(data.msg.share_platform.uv);
					$('.data_list .share_platform_addtocart').text(data.msg.share_platform.addtocart);
					$('.data_list .share_platform_operate_addtocart').text(data.msg.share_platform.addtocart_ratio);
					$('.data_list .share_platform_placeorder').text(data.msg.share_platform.placeorder);
					$('.data_list .share_platform_operate_placeorder').text(data.msg.share_platform.placeorder_ratio);
					$('.data_list .share_platform_complete').text(data.msg.share_platform.complete);
					$('.data_list .share_platform_operate_complete').text(data.msg.share_platform.complete_ratio);
					/*搜索引擎*/
					//$('.data_list .search_engine').text(data.msg.search_engine.uv);
					$('.data_list .search_engine').text(data.msg.search_engine.complete_ratio);
					$('.data_list .search_engine_uv').text(data.msg.search_engine.uv);
					$('.data_list .search_engine_addtocart').text(data.msg.search_engine.addtocart);
					$('.data_list .search_engine_operate_addtocart').text(data.msg.search_engine.addtocart_ratio);
					$('.data_list .search_engine_placeorder').text(data.msg.search_engine.placeorder);
					$('.data_list .search_engine_operate_placeorder').text(data.msg.search_engine.placeorder_ratio);
					$('.data_list .search_engine_complete').text(data.msg.search_engine.complete);
					$('.data_list .search_engine_operate_complete').text(data.msg.search_engine.complete_ratio);
					/*其他*/
					//$('.data_list .other').text(data.msg.other.uv);
					$('.data_list .other').text(data.msg.other.complete_ratio);
					$('.data_list .other_uv').text(data.msg.other.uv);
					$('.data_list .other_addtocart').text(data.msg.other.addtocart);
					$('.data_list .other_operate_addtocart').text(data.msg.other.addtocart_ratio);
					$('.data_list .other_placeorder').text(data.msg.other.placeorder);
					$('.data_list .other_operate_placeorder').text(data.msg.other.placeorder_ratio);
					$('.data_list .other_complete').text(data.msg.other.complete);
					$('.data_list .other_operate_complete').text(data.msg.other.complete_ratio);
					
					
					if($('.nav .compared').hasClass('checked')){	/*对比数据*/
						var compare=data.msg.compare;
						$('.compare').show();
						/*转化率*/
						//$('.data_list .compare_ratio').text(compare.ratio.uv);
						$('.data_list .compare_ratio').text(compare.ratio.complete_ratio);
						$('.data_list .compare_ratio_uv').text(compare.ratio.uv);
						$('.data_list .compare_ratio_addtocart').text(compare.ratio.addtocart);
						$('.data_list .compare_ratio_operate_addtocart').text(compare.ratio.addtocart_ratio);
						$('.data_list .compare_ratio_placeorder').text(compare.ratio.placeorder);
						$('.data_list .compare_ratio_operate_placeorder').text(compare.ratio.placeorder_ratio);
						$('.data_list .compare_ratio_complete').text(compare.ratio.complete);
						$('.data_list .compare_ratio_operate_complete').text(compare.ratio.complete_ratio);
						/*直接输入*/
						//$('.data_list .compare_enter_directly').text(compare.enter_directly.uv);
						$('.data_list .compare_enter_directly').text(compare.enter_directly.complete_ratio);
						$('.data_list .compare_enter_directly_uv').text(compare.enter_directly.uv);
						$('.data_list .compare_enter_directly_addtocart').text(compare.enter_directly.addtocart);
						$('.data_list .compare_enter_directly_operate_addtocart').text(compare.enter_directly.addtocart_ratio);
						$('.data_list .compare_enter_directly_placeorder').text(compare.enter_directly.placeorder);
						$('.data_list .compare_enter_directly_operate_placeorder').text(compare.enter_directly.placeorder_ratio);
						$('.data_list .compare_enter_directly_complete').text(compare.enter_directly.complete);
						$('.data_list .compare_enter_directly_operate_complete').text(compare.enter_directly.complete_ratio);
						/*分享平台*/
						//$('.data_list .compare_share_platform').text(compare.share_platform.uv);
						$('.data_list .compare_share_platform').text(compare.share_platform.complete_ratio);
						$('.data_list .compare_share_platform_uv').text(compare.share_platform.uv);
						$('.data_list .compare_share_platform_addtocart').text(compare.share_platform.addtocart);
						$('.data_list .compare_share_platform_operate_addtocart').text(compare.share_platform.addtocart_ratio);
						$('.data_list .compare_share_platform_placeorder').text(compare.share_platform.placeorder);
						$('.data_list .compare_share_platform_operate_placeorder').text(compare.share_platform.placeorder_ratio);
						$('.data_list .compare_share_platform_complete').text(compare.share_platform.complete);
						$('.data_list .compare_share_platform_operate_complete').text(compare.share_platform.complete_ratio);
						/*搜索引擎*/
						//$('.data_list .compare_search_engine').text(compare.search_engine.uv);
						$('.data_list .compare_search_engine').text(compare.search_engine.complete_ratio);
						$('.data_list .compare_search_engine_uv').text(compare.search_engine.uv);
						$('.data_list .compare_search_engine_addtocart').text(compare.search_engine.addtocart);
						$('.data_list .compare_search_engine_operate_addtocart').text(compare.search_engine.addtocart_ratio);
						$('.data_list .compare_search_engine_placeorder').text(compare.search_engine.placeorder);
						$('.data_list .compare_search_engine_operate_placeorder').text(compare.search_engine.placeorder_ratio);
						$('.data_list .compare_search_engine_complete').text(compare.search_engine.complete);
						$('.data_list .compare_search_engine_operate_complete').text(compare.search_engine.complete_ratio);
						/*其他*/
						//$('.data_list .compare_other').text(compare.other.uv);
						$('.data_list .compare_other').text(compare.other.complete_ratio);
						$('.data_list .compare_other_uv').text(compare.other.uv);
						$('.data_list .compare_other_addtocart').text(compare.other.addtocart);
						$('.data_list .compare_other_operate_addtocart').text(compare.other.addtocart_ratio);
						$('.data_list .compare_other_placeorder').text(compare.other.placeorder);
						$('.data_list .compare_other_operate_placeorder').text(compare.other.placeorder_ratio);
						$('.data_list .compare_other_complete').text(compare.other.complete);
						$('.data_list .compare_other_operate_complete').text(compare.other.complete_ratio);
					}else{
						$('.compare').hide();
					}
				}
			}, 'json');
		});
	},
	
	visits_country_init:function(){
		mta_obj.nav_condition(function(time_s, time_e, terminal, compare){
			var par='do_action=mta.api_get_data&Action=ueeshop_analytics_get_visits_country_data&TimeS='+time_s+'&TimeE='+time_e+'&Terminal='+terminal+'&Compare='+compare;
			$.post('./', par, function(data){
				var html='';
				$.each(data.msg.detail, function(key, value){
					html+='<tr>\
						<td nowrap="nowrap" valign="top">'+value['title']+'</td>\
						<td nowrap="nowrap">'+value['pv']+'<div class="compare compare_pv">0</div></td>\
						<td nowrap="nowrap">'+value['average_pv']+'<div class="compare compare_average_pv">0</div></td>\
						<td nowrap="nowrap">'+value['ip']+'<div class="compare compare_ip">0</div></td>\
						<td nowrap="nowrap">'+value['uv']+'<div class="compare compare_uv">0</div></td>\
					</tr>';
				});
				$('.detail .data_table tbody').html(html);
				mta_obj.highcharts($('.line_charts'), data.msg.line_charts);
				mta_obj.highcharts($('.country_charts'), data.msg.country_charts);
				if($('.nav .compared').hasClass('checked')){
					$('.compare').show();
					$('.detail .data_table tbody tr').each(function(index){
						var d=data.msg.compare.detail[index];
						$(this).find('.compare_pv').html(d.pv);
						$(this).find('.compare_average_pv').html(d.average_pv);
						$(this).find('.compare_uv').html(d.uv);
						$(this).find('.compare_ip').html(d.ip);
					});
				}else{
					$('.compare').hide();
				}
			}, 'json');
		});
	},
	
	funnel_init:function(){
		mta_obj.nav_condition(function(time_s, time_e, terminal, compare){
			var par='do_action=mta.api_get_data&Action=ueeshop_analytics_get_funnel_data&TimeS='+time_s+'&TimeE='+time_e+'&Terminal='+terminal+'&Compare='+compare;
			$.post('./', par, function(data){
				mta_obj.highcharts($('.funnel_charts'), data.msg.line_charts);
				var pre_pv=data.msg.detail['index'];
				pre_pv==0 && $('.data_table tbody tr:eq(1) td:eq(1)').html('0%');
				$.each(['index', 'products_list', 'products_detail', 'cart', 'checkout', 'checkout_success'], function(i, n){
					$('.data_table tbody tr:eq(0) td:eq('+(i+1)+')').html(data.msg.detail[n]);
					if(i>0){
						var ratio=pre_pv==0?0:(data.msg.detail[n]/pre_pv*100).toFixed(2);
						$('.data_table tbody tr:eq(1) td:eq('+(i+1)+')').html(ratio+'%');
						pre_pv=data.msg.detail[n];
					}
				});
				$('.data_table tbody tr:eq(0) td:eq(1)').html(data.msg.detail.index);
			}, 'json');
		});
	},
	
	orders_init:function(){
		mta_obj.nav_condition(function(time_s, time_e, terminal, compare){
			var lang=$('body').hasClass('en')?'en':'zh-cn';
			var par='do_action=mta.get_orders_data&Action=ueeshop_analytics_get_order_visits_data&TimeS='+time_s+'&TimeE='+time_e+'&Terminal='+terminal+'&Compare='+compare+'&lang='+lang;
			$.post('./', par, function(data){
				var obj=$('#mta .order_data_list');
				var total=data.msg.total;
				obj.find('.order_price').html(total.total_price);
				obj.find('.order_count').html(total.order_count);
				obj.find('.order_unit_price').html(total.order_unit_price);
				obj.find('.customer_price').html(total.order_customer_price);
				obj.find('.order_customer').html(total.order_customer_count);
				obj.find('.ratio').html(total.ratio);
				obj.find('.visit_customer').html(total.visit_customer);
				obj.find('.discount_price').html(total.discount_price);
				obj.find('.use_times').html(total.coupon_count);
				/*国家*/
				var html='';
				if(data.msg.detail.country){
					$.each(data.msg.detail.country, function(key, value){
						html+='<tr data-key="'+key+'">\
							<td nowrap="nowrap" valign="top">'+value['title']+'</td>\
							<td nowrap="nowrap">'+value['price']+'<div class="compare compare_country_price">0</div></td>\
							<td nowrap="nowrap">'+value['count']+'<div class="compare compare_country_order_count">0</div></td>\
							<td nowrap="nowrap">'+value['average_price']+'<div class="compare compare_average_price">0</div></td>\
						</tr>';
					});
				}
				$('.country .data_table tbody').html(html);
				/*支付*/
				var html='';
				if(data.msg.detail.payment){
					$.each(data.msg.detail.payment, function(key, value){
						html+='<tr data-key="'+key+'">\
							<td nowrap="nowrap" valign="top">'+value['title']+'</td>\
							<td nowrap="nowrap">'+value['price']+'<div class="compare compare_country_price">0</div></td>\
							<td nowrap="nowrap">'+value['count']+'<div class="compare compare_country_order_count">0</div></td>\
							<td nowrap="nowrap">'+value['average_price']+'<div class="compare compare_average_price">0</div></td>\
						</tr>';
					});
				}
				$('.payment .data_table tbody').html(html);
				
				
				if($('.nav .compared').hasClass('checked')){
					$('.compare').show();
					var compareTotal=data.msg.compare.total;
					obj.find('.compare_order_price').html(compareTotal.total_price);
					obj.find('.compare_order_count').html(compareTotal.order_count);
					obj.find('.compare_order_unit_price').html(compareTotal.order_unit_price);
					obj.find('.compare_customer_price').html(compareTotal.order_customer_price);
					obj.find('.compare_order_customer').html(compareTotal.order_customer_count);
					obj.find('.compare_ratio').html(compareTotal.ratio);
					obj.find('.compare_visit_customer').html(compareTotal.visit_customer);
					obj.find('.compare_discount_price').html(compareTotal.discount_price);
					obj.find('.compare_use_times').html(compareTotal.coupon_count);
					/*国家*/
					$('.country .data_table tbody tr').each(function(index){
						var key=$(this).attr('data-key');
						var d=data.msg.compare.detail.country[key];
						if(d){
							$(this).find('.compare_country_price').html(d.price);
							$(this).find('.compare_country_order_count').html(d.count);
							$(this).find('.compare_average_price').html(d.average_price);
						}
					});
					/*支付*/
					$('.payment .data_table tbody tr').each(function(index){
						var key=$(this).attr('data-key');
						var d=data.msg.compare.detail.payment[key];
						if(d){
							$(this).find('.compare_country_price').html(d.price);
							$(this).find('.compare_country_order_count').html(d.count);
							$(this).find('.compare_average_price').html(d.average_price);
						}
					});

				}else{
					$('.compare').hide();
				}
				
				if(data.msg.orders_charts){
					$('.orders_charts').show();
					mta_obj.highcharts($('.orders_charts'), data.msg.orders_charts);
				}else{
					$('.orders_charts').hide();
				}
			}, 'json');
		});
	},
	
	orders_repurchase_init:function(){
		mta_obj.nav_condition(function(_, _, terminal, _, _, mta_cycle){
			var par='do_action=mta.get_orders_repurchase_data&Cycle='+mta_cycle+'&Terminal='+terminal;
			$.post('./', par, function(data){
				mta_obj.highcharts($('.repurchase_charts'), data.msg.repurchase_charts);
			}, 'json');
		});
	},
	
	orders_paid_init:function(){
		$('.nav .mta_cycle a').click(function(){$('input[name=TimeS]').val('');});
		mta_obj.nav_condition(function(time_s, time_e, terminal, compare, _, mta_cycle){
			var par='do_action=mta.get_orders_paid_data&TimeS='+time_s+'&TimeE='+time_e+'&Terminal='+terminal+'&Compare='+compare+'&MtaCycle='+mta_cycle;
			$.post('./', par, function(data){
				$('input[name=TimeS]').val(data.msg.time_s);
				$('input[name=TimeE]').val(data.msg.time_e);
				mta_obj.highcharts($('.orders_charts'), data.msg.orders_paid_charts);
			}, 'json');
		});
	},
	
	products_sales_init:function(){
		mta_obj.nav_condition(function(time_s, _, terminal){
			var page=parseInt($('#sales-list').attr('data-page'));
			if(!page) page=1;
			var par='do_action=mta.get_products_sales_data&TimeS='+time_s+'&Terminal='+terminal+'&page='+page;
			$.post('./', par, function(data){
				if(data.ret==1){
					var strData='';
					$.each(data.msg, function(key, value){
						strData+='<li class="list" data-id="'+value['ProId']+'">';
							/*序号*/
							strData+='<div class="number">'+value['No']+'</div>';
							/*产品*/
							strData+='<div class="products">';
								strData+='<div class="img pic_box"><img src="'+value['PicPath']+'" /><span></span></div>';
								strData+='<div class="info">';
									strData+='<p><a href="'+value['Url']+'" target="_blank">'+value['Name']+'</a></p>';
									strData+='<p>'+lang_obj.manage.sales.proNumber+': '+value['Number']+'</p>';
									strData+='<p>'+lang_obj.manage.mta.order_count+': '+value['OrderCount']+'</p>';
									strData+='<p>'+lang_obj.manage.mta.buy_count+': '+value['BuyCount']+'</p>';
								strData+='</div>';
							strData+='</div>';
							/*国家*/
							strData+='<div class="country">';
								strData+='<p>'+value['Country']+'</p>';
								strData+='<p>'+lang_obj.manage.mta.order_count+': '+value['CountryOrderCount']+' ('+value['CountryOrderRate']+')</p>';
								strData+='<p>'+lang_obj.manage.mta.buy_count+': '+value['CountryBuyCount']+' ('+value['CountryBuyRate']+')</p>';
							strData+='</div>';
							/*同时购买*/
							strData+='<div class="related">';
								if(value['Related']['ProId']){
									strData+='<div class="info">';
										strData+='<p><a href="'+value['Related']['Url']+'" target="_blank">'+value['Related']['Name']+'</a></p>';
										strData+='<p>'+lang_obj.manage.sales.proNumber+': '+value['Related']['Number']+'</p>';
										strData+='<p>'+lang_obj.manage.mta.order_count+': '+value['Related']['OrderCount']+'</p>';
										strData+='<p>'+lang_obj.manage.mta.buy_count+': '+value['Related']['BuyCount']+'</p>';
									strData+='</div>';
								}else{
									strData+='&nbsp;';
								}
							strData+='</div>';
							/*更多*/
							strData+='<div class="operate"><a href="javascript:;"></a></div>';
							strData+='<div class="clear"></div>';
							/*国家、同时购买产品排行*/
							strData+='<div class="detail_list"></div>';
							strData+='<div class="clear"></div>';
						strData+='</li>';
					});
					$('#sales-list').html(strData);
				}
			}, 'json');
		}, true);
		
		$('#sales-list').delegate('.list .operate a', 'click', function(){
			var obj=$(this).parent().siblings('.detail_list');
			if(obj.hasClass('on')){
				obj.removeClass('on').slideUp();
			}else{
				var parent=$(this).parent().parent();
				parent.siblings().find('.detail_list').removeClass('on').slideUp();
				if(parent.hasClass('load-detail-data')){
					obj.addClass('on').slideDown();
				}else{
					var id=$(this).parent().parent().attr('data-id');
					var time_s=$('input[name=TimeS]').val();
					var terminal=$('.nav .terminal a.cur').attr('rel');
					var par='do_action=mta.get_products_sales_related_data&ProId='+id+'&TimeS='+time_s+'&Terminal='+terminal;
					$.post('./', par, function(data){
						if(data.ret==1){
							/* Country */
							var strData='<ul class="related_box fl"><li class="title">'+lang_obj.manage.mta.country+'</li>';
							$.each(data.msg.country, function(key, value){
								strData+='<li>';
									strData+='<div class="data">';
										strData+='<span>'+value['No']+'.</span>';
										strData+='<span>'+value['Country']+'</span>';
										strData+='<span>'+lang_obj.manage.mta.order_count+': '+value['CountryOrderCount']+' ('+value['CountryOrderRate']+')</span>';
										strData+='<span>'+lang_obj.manage.mta.buy_count+': '+value['CountryBuyCount']+' ('+value['CountryBuyRate']+')</span>';
									strData+='</div>';
									strData+='<div class="clear"></div>';
								strData+='</li>';
							});
							strData+='</ul>';
							/*Also Buy*/
							if(data.msg.related){
								strData+='<ul class="related_box fr"><li class="title">'+lang_obj.manage.mta.also_buy+'</li>';
								$.each(data.msg.related, function(key, value){
									strData+='<li>';
										strData+='<div class="no">'+value['No']+'</div>';
										strData+='<div class="data">';
											strData+='<p><a href="'+value['Url']+'" target="_blank">'+value['Name']+'</a></p>';
											strData+='<p>'+lang_obj.manage.sales.proNumber+': '+value['Number']+'</p>';
											strData+='<p>'+lang_obj.manage.mta.order_count+': '+value['OrderCount']+'</p>';
											strData+='<p>'+lang_obj.manage.mta.buy_count+': '+value['BuyCount']+'</p>';
										strData+='</div>';
										strData+='<div class="clear"></div>';
									strData+='</li>';
								});
								strData+='</ul>';							
							}
							strData+='<div class="clear"></div>';							
							
							obj.html(strData).addClass('on').slideDown();
							parent.addClass('load-detail-data');
						}
					},'json');
				}
			}
		});
	},
	
	user_init:function(){
		var trend=true;
		mta_obj.nav_condition(function(){
			var par='do_action=mta.get_user_data';
			if(trend) par=par+'&trend=1';
			$.post('./', par, function(data){
				$('.data_list .new_member').html(data.msg.total.new);
				$('.data_list .active_member').html(data.msg.total.active);
				$('.data_list .core_member').html(data.msg.total.core);
				$('.data_list .total_member').html(data.msg.total.total);

				if(trend){
					trend=false;
					//会员趋势图
					data.msg.trend_charts && mta_obj.highcharts($('.trend_charts'), data.msg.trend_charts);
					data.msg.country_charts && mta_obj.highcharts($('.country_charts'), data.msg.country_charts);
					/***********************************会员性别、等级(start)***********************************/
					$('#mta .user_detail li .rate, #mta .user_detail li .data dd').html('');
					$('#mta .user_detail li .rate').radialIndicator({radius: 58, barColor: '#53a18e', barWidth: 7, percentage: true, fontSize: '36', fontColor: '#333333'});
					
					$('#mta .user_detail').show();
					$('#mta .user_detail li').removeClass('single double').hide();
					if(data.msg.user_detail.gender){
						var $i=0;
						var html='';
						$('#mta .user_detail li.gender').show().addClass('single');
						$.each(data.msg.user_detail.gender, function(key, value){
							$i++==0 && $('#mta .user_detail li.gender .rate').data('radialIndicator').animate(value); 
							html+='<div title="'+value+'%">'+key+'<font>( '+value+'% )</font></div>';
						});
						$('#mta .user_detail li.gender .data dd').html(html);
					}
					if(data.msg.user_detail.level){
						var $i=0;
						var html='';
						$('#mta .user_detail li.level').show().addClass('single');
						$.each(data.msg.user_detail.level, function(key, value){
							$i++==0 && $('#mta .user_detail li.level .rate').data('radialIndicator').animate(value); 
							html+='<div title="'+value+'%">'+key+'<font>( '+value+'% )</font></div>';
						});
						$('#mta .user_detail li.level .data dd').html(html);
					}
					if($('#mta .user_detail li.single').length==2){
						$('#mta .user_detail li').removeClass('single').addClass('double');
					}
					/***********************************会员性别、等级(end)***********************************/
				}
			}, 'json');
		});
	},
	
	nav_condition:function(callback, not_limit){
		if(not_limit){//不限制日期选择
			$('.nav').find('input[name=TimeS],input[name=TimeE]').daterangepicker({
				timePicker: false,
				format: 'YYYY-MM-DD'
			});
		}else{//限制日期选择
			var mydate = new Date();
			var y = mydate.getFullYear();
			var m = mydate.getMonth()+1;
			var d = mydate.getDate();
			var maxD=y+'-'+m+'-'+d;
			var limitM=6;//限制只可以选择最近6个月
			if(m-limitM<1){
				y=y-1;
				m=m+12-limitM;
			}else{
				m=m-limitM;
			}
			var minD=y+'-'+m+'-'+d;
			
			$('.nav').find('input[name=TimeS],input[name=TimeE]').daterangepicker({
				minDate: minD,
				maxDate: maxD,
				timePicker: false,
				format: 'YYYY-MM-DD'
			});
		}
		
		
		$('.nav .time a').click(function(){
			$('.nav .compared').removeClass('checked');
			$('.nav .compared_input').hide();
			$('input[name=TimeS]').val('');
			$('input[name=TimeE]').val('').removeAttr('notnull');
		});
		$('.nav dl a').click(function(){
			$(this).parent().parent().find('a').removeClass('cur');
			$(this).addClass('cur');
			mta_obj.nav_condition_callback(callback);
		});
		$('.nav .compared').click(function(){
			$(this).toggleClass('checked');
			if($(this).hasClass('checked')){
				$('.nav .compared_input').show();
				$('input[name=TimeE]').attr('notnull', 'notnull');
			}else{
				$('.nav .compared_input').hide();
				$('input[name=TimeE]').removeAttr('notnull');
			}
		});
		$('.nav .btn_ok').click(function(){
			if(global_obj.check_form($('input[notnull]'))){return false;};
			$('.nav .time a').removeClass('cur');
			mta_obj.nav_condition_callback(callback);
		});
		global_obj.data_posting(1, lang_obj.global.loading);
		mta_obj.nav_condition_callback(callback);
		global_obj.data_posting(0);
	},
	
	nav_condition_callback:function(callback){
		var time_s=$('.nav .time a.cur').size()?$('.nav .time a.cur').attr('rel'):$('input[name=TimeS]').val();
		var time_e=$('.nav .compared').hasClass('checked')?$('input[name=TimeE]').val():'';
		var terminal=$('.nav .terminal a.cur').attr('rel');
		var compare=$('.nav .compared').hasClass('checked')?1:0;
		var mta_method=$('.nav .mta_method a.cur').attr('rel');
		var mta_cycle=$('.nav .mta_cycle a.cur').attr('rel');
		callback(time_s, time_e, terminal, compare, mta_method, mta_cycle);
	},
	
	highcharts:function(obj, data){
		Highcharts.setOptions({
			lang:{thousandsSep:''}
		});
		obj.highcharts($.extend(true, {
			chart:{height:500},
			colors:['#53a18e', '#8085e9', '#90ed7d', '#f7a35c', '#91e8e1',  '#f15c80', '#e4d354', '#8d4653'],
            title:{
				text:null,
				margin:10,
				style:{fontSize:'14px', color:'#555'}
			},
			tooltip:{pointFormat:'{point.y}'},
			legend:{enabled:false},
			plotOptions:{
				bar:{dataLabels:{enabled:true}},
				column:{dataLabels:{enabled:true}},
				line:{dataLabels:{enabled:true}},
				spline:{dataLabels:{enabled:true}},
				pie:{dataLabels:{enabled:true}},
				pyramid:{dataLabels:{enabled:true}}
			},
			yAxis:[{
				title:{text:null},
				min:0,
				lineWidth:1,
				tickWidth:10,
				gridLineColor:'#C0D0E0'
			}]
        }, data));
	}
}