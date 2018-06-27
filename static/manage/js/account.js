/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

var account_obj={
	login_init:function(){
		if($(window).width()<=1024){
			$('#login').css('margin-top', 80);
		}else if($(window).width()<=1280){
			$('#login').css('margin-top', 120);
		}else if($(window).width()<=1440){
			$('#login').css('margin-top', 160);
		}
		$('form').submit(function(){return false;});
		$('input:submit').click(function(){
			var flag=false;
			$('#UserName, #Password').each(function(){
				if($(this).val()==''){
					$(this).focus();
					flag=true;
					return false;
				}
			});
			if(flag){return;}
			$('#login h2').html(lang_obj.manage.account.log_in);
			$(this).attr('disabled', true);
			$.post('?', $('form').serialize(), function(data){
				$('input:submit').attr('disabled', false);
				if(data.ret==1){
					$('#login h2').html(lang_obj.manage.account.log_in_ok);
					window.top.location='./';
				}else{
					$('#login h2').html(data.msg);
				};
			}, 'json');
		});
	},
	
	index_init:function(){
		/*$.post('?', 'do_action=account.ueeshop_web_get_service_data', function(data){
			if(data.ret==1){
				$('#account .home_tab .service_server').html(data.msg.server);
				$('#user_spread_box .service_spread').html(data.msg.spread);
				if(data.msg.trial==1){
					$('#user_service_box .service_name').html(data.msg.service.Contacts);
					$('#user_service_box .service_email').html(data.msg.service.Email);
					$('#user_service_box .service_qq').html(data.msg.service.QQ);
					$('#user_service_box .service_work_time').html(data.msg.service.WorkTime);
					$('#user_service_box .service_phone').html(data.msg.service.Telephone);
					$('#user_service_box .service_wechat').html(data.msg.service.Wechat);
					$('#user_service_box .service_complaint').html(data.msg.service.Complain);

					$('#user_service_box .service_expired').html(data.msg.expired);
					$('#account .home_tab .service_backup_time').html(data.msg.backup);
				}else{
					//$('#user_service_box').remove();
				}
			}
		},'json');*/
		
		function ueeshop_web_get_data(k){
			$.post('?', 'do_action=account.ueeshop_web_get_data&key='+k, function(data){
				if(data.ret==1){
					var source_list='',
						area_data=new Object(),
						area=data.msg.area,
						source=data.msg.source,
						sourceCount=(data.msg.source).length,
						order_total_count=data.msg.order_total_count,
						order_total_price=data.msg.order_total_price,
						s_order_total_price=data.msg.s_order_total_price;
					
					//来源信息
					for(i=0; i<sourceCount; i++){
						if(i>=5) break;
						source_list+='<div class="data_left">'+source[i].engine+'</div><div class="data_right"><div class="process" percent="'+(source[i].rate*100).toFixed(2)+'">'+source[i].pv+'</div></div><div class="clear"></div>';
					}
					
					$('#account .home_traffic .traffic_ip h2').html(data.msg.ip);
					$('#account .home_traffic .traffic_pv h2').html(data.msg.pv);
					$('#account .home_source .box_container').html(source_list);
					//订单信息
					$('#account .home_traffic .traffic_order h2').html(order_total_count);
					$('#account .home_traffic .traffic_sales h2').html(s_order_total_price);
					$('#account .home_traffic .traffic_sales .traffic').attr('title', order_total_price);
					
					$('#account .home_traffic .traffic_time a').removeClass('current').eq(data.msg.key).addClass('current');
					
					$('#account .home_source .box_container .data_right').each(function(){
						var $obj=$(this).children('.process'),
							width=parseInt($obj.attr('percent')),
							width_to=width>=100?100:width+2;
						$obj.animate({'width':width_to+'%'}, 300, function(){
							$obj.animate({'width':width+'%'}, 300, function(){
								return false;
							});
						});
					});
					$('#account .home_source .box_container .data_right').mouseenter(function(){
						var $obj=$(this).children('.process'),
							width=parseInt($obj.attr('percent')),
							width_to=width>=100?100:width+5;
						$obj.animate({'width':width_to+'%'}, 100, function(){
							$obj.animate({'width':width+'%'}, 100, function(){
								return false;
							});
						});
					});
					
					//地区信息
					for(k in area){
						area_data[country_acronym_data[area[k]['country']]]=area[k]['pv'];
					}
					if($('#world_map').length){
						$('#world_map').html('').vectorMap({
							map: 'world_en',
							backgroundColor: '#FFFFFF',
							borderColor: '#FFFFFF',
							color: '#9FD5F1',
							hoverOpacity: 0.7,
							selectedColor: '#666666',
							enableZoom: true,
							showTooltip: true,
							values: area_data,
							normalizeFunction: 'polynomial',
							onLabelShow: function(event, label, code){
								if(area_data[code]){
									label.html(label.text()+'<br />'+area_data[code]);
								}
							}
						});
					}
				}
			},'json');
		}
		ueeshop_web_get_data(0);
		$('#account .home_traffic .traffic_time a').click(function(){ueeshop_web_get_data($(this).index());});
	}
}