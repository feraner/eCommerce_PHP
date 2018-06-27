/*
 * Powered by ueeshop.com		http://www.ueeshop.com
 * 广州联雅网络科技有限公司		020-83226791
 */

(function($, _w){
	_w.cart_obj={
		cart_list:function(){
			$('.qty_box .cut, .qty_box .add').on('tap', function(){
				var value	= $(this).hasClass('add')?1:-1,
					obj		= $(this).siblings('.qty').find('input'),
					qty		= Math.abs(parseInt(obj.val())),
					CId		= obj.attr('data-cid'),
					ProId	= obj.attr('data-proid'),
					start	= obj.attr('data-start'),
					s_qty	= $(obj).parent().parent().siblings('input[name="S_Qty[]"]').val();
				if(!qty || qty<0 || qty<start){
					$('html').tips_box(lang_obj.products.warning_number, 'error');
				}
				qty=qty?qty:1;
				qty+=value;
				qty=qty>0?qty:1;
				qty<start && (qty=start);
				if(s_qty==qty) return false;
				var query_string='&Qty='+qty+'&CId='+CId+'&ProId='+ProId;
				var cid_str='&CIdAry=';
				if($('.cart_list input[name=select]:checked').length){//部分已选
					cid_str+='0';
					$('.cart_list input[name=select]:checked').each(function(index, element){
						cid_str+=','+$(element).val();
					});
				}
				cart_obj.modify_cart_result(obj, query_string+cid_str, 1);
			});
			$('.qty_box .qty input').on('keyup paste', function(){
				p=/[^\d]/g;
				$(this).val($(this).val().replace(p, ''));
			}).on('blur', function(){
				var obj		= $(this),
					qty 	= Math.abs(parseInt(obj.val())),
					CId 	= obj.attr('data-cid'),
					ProId	= obj.attr('data-proid'),
					start	= obj.attr('data-start'),
					s_qty	= $(obj).parent().parent().siblings('input[name="S_Qty[]"]').val();
				if(!qty || qty<0 || qty<start){
					$('html').tips_box(lang_obj.products.warning_number, 'error');
				}
				qty=qty?qty:1;
				qty=qty>0?qty:1;
				qty<start && (qty=start);
				if(s_qty==qty) return false;
				var query_string='&Qty='+qty+'&CId='+CId+'&ProId='+ProId;
				var cid_str='&CIdAry=';
				if($('.cart_list input[name=select]:checked').length){//部分已选
					cid_str+='0';
					$('.cart_list input[name=select]:checked').each(function(index, element){
						cid_str+=','+$(element).val();
					});
				}
				cart_obj.modify_cart_result(obj, query_string+cid_str, 0);
			});
			$('.cart_list .item .del').on('tap', function(){ //购物车产品删除
				var url=$(this).attr('url');
				$('html').tips_box(lang_obj.cart.del_confirm, 'confirm', function(){
					$.get(url, function(data){
						if(data){
							window.location.reload();
						}
					});
				});
				return false;
			});
			$('.cart_list .check').on('tap', function(){ //购物车产品勾选
				if($(this).find('input[name=select]:checked').length){
					$(this).find('input[name=select]')[0].checked=false;
					$(this).find('.btn_checkbox').removeClass('current');
				}else{
					$(this).find('input[name=select]')[0].checked=true;
					$(this).find('.btn_checkbox').addClass('current');
				}
				cart_obj.select_cart_result();
				return false;
			});
			$('.cart_list input[name=select]').on('click', function(){ //购物车产品勾选
				$(this).parent('.check').click();
				return false;
			});
			$('.cart_list input[name=select]').each(function(){ //重新默认全部勾选
				if($(this).is(':checked')===false) $(this).get(0).checked='checked';
			});
			$('.cart_btn .checkout').on('tap', function(){ //Checkout
				/*if($('.cart_list .item.null').length){//检查是否存在错误产品
					$('html').tips_box(lang_obj.cart.attribute_error, 'error');
					return false;
				}*/
				var $this=$(this), Data=new Object;
				$('#cart .cart_list input[name=Remark\\[\\]]').each(function(){
					Data[$(this).attr('data-cid')]=$(this).val();
				});
				
				$this.addClass('processing').text(lang_obj.cart.processing_str+'...');
				var $checked_len=$('.cart_list input[name=select]:checked').length,
					$checkout_len=$('.cart_list input[name=select]').length,
					$query='?';
				if($checked_len){//部分已选
					if($checked_len!=$checkout_len){ //部分已选，不是全选
						var $CId='0';
						$('.cart_list input[name=select]:checked').each(function(index, element){
							$CId+='.'+$(element).val();
						});
						$query+='CId='+$CId;
					}
					$.post('/?do_action=cart.check_low_consumption&t='+Math.random(), '', function(data){ //最低消费金额判断
						if(data.ret==1){ //符合
							setTimeout(function(){
								$checkoutUrl='/cart/checkout.html'+$query;
								$this.removeClass('processing').text($this.attr('data-name'));
								if($(this).loginOrVisitors()){
									$.post('/?do_action=cart.checkout_submit&t='+Math.random(), Data, function(data){
										if(data.ret==1){
											window.location.href=$checkoutUrl;
											return false;
										};
									}, 'json');
								}else{
									window.top.location.href='/account/login.html?&jumpUrl='+decodeURIComponent($checkoutUrl);
									//window.location.href='/account/';
								}
							}, 500);
						}else{ //不符合
							var tips=(lang_obj.cart.consumption).replace('%low_price%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.low_price, ueeshop_config.currency)).replace('%difference%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.difference, ueeshop_config.currency));
							$('html').tips_box(tips, 'error');
							$this.removeClass('processing').text($this.attr('data-name'));
						}
					}, 'json');
				}else{
					$('html').tips_box(lang_obj.cart.checked_error, 'error');
					$this.removeClass('processing').text($this.attr('data-name'));
				}
				return false;
			});
			$('.cart_btn .paypal_checkout_button').on('tap', function(){ //Paypal快捷支付
				var $this=$(this);
				/*if($('.cart_list .item.null').length){ //检查是否存在错误产品
					$('html').tips_box(lang_obj.cart.attribute_error, 'error');
					return false;
				}*/
				$this.addClass('processing').text(lang_obj.cart.processing_str+'...');
				var $checked_len=$('.cart_list input[name=select]:checked').length,
					$checkout_len=$('.cart_list input[name=select]').length,
					$query='?';
				if($('.cart_list input[name=select]:checked').length){//部分已选
					if($checked_len!=$checkout_len){ //部分已选，不是全选
						var $CId='0';
						$('.cart_list input[name=select]:checked').each(function(index, element){
							$CId+='.'+$(element).val();
						});
						$query+='CId='+$CId;
					}
					$.post('/?do_action=cart.check_low_consumption&t='+Math.random(), {'CId':$CId}, function(data){ //最低消费金额判断
						if(data.ret==1){ //符合
							$quickUrl='/cart/quick.html'+$query;
							setTimeout(function(){
								$this.removeClass('processing').text('');
								if($(this).loginOrVisitors()){
									window.top.location.href=$quickUrl;
								}else{
									window.top.location.href='/account/login.html?&jumpUrl='+decodeURIComponent($quickUrl);
								}
							}, 500);
						}else{ //不符合
							var tips=(lang_obj.cart.consumption).replace('%low_price%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.low_price, ueeshop_config.currency)).replace('%difference%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.difference, ueeshop_config.currency));
							$('html').tips_box(tips, 'error');
							$this.removeClass('processing').text('');
						}
					}, 'json');
				}else{
					$('html').tips_box(lang_obj.cart.checked_error, 'error');
					$this.removeClass('processing').text('');
				}
				return false;
			});
		},
		
		//结算
		cart_checkout:function(){
			var CountryId=$('input[name=order_shipping_address_cid]').val()?$('input[name=order_shipping_address_cid]').val():$('form[name=paypal_excheckout]').find('option:selected').val();
			cart_obj.get_shipping_method_from_country(CountryId);
			var cart_price = cart_obj.cart_price_init();
			$('#ot_fee').text($('html').currencyFormat(cart_price.feePrice.toFixed(2), ueeshop_config.currency));
			$('#ot_total').text($('html').currencyFormat((cart_price.totalAmount+cart_price.free_Price).toFixed(2), ueeshop_config.currency));
			
			$('.payment_row').on('tap', function(e){
				e.stopPropagation(); //阻止JavaScript事件冒泡传递
				$(this).addClass('current').siblings().removeClass('current');
				$('.payment_row .payment_contents').slideUp();
				if($(this).find('.payment_contents .desc').size()) $(this).find('.payment_contents').slideDown();
				$('#PlaceOrderFrom input[name=order_payment_method_pid]').val($(this).attr('pid'));
				
				var fee=parseFloat($(this).find('.payment_contents').attr('fee'));
				var affix=parseFloat($(this).find('.payment_contents').attr('affix'));
				
				if(isNaN(fee)) fee=0;
				if(isNaN(affix)) affix=0;
				
				var amount=parseFloat($('#PlaceOrderFrom').attr('amountPrice'));	//产品总价
				var userPrice=parseFloat($('#PlaceOrderFrom').attr('userPrice'));	//会员优惠
				var discountPrice=parseFloat($('input[name=order_discount_price]').val());	//满额减价
				var cutprice=parseFloat($('input[name=order_coupon_code]').attr('cutprice'));	//折扣
				//var price=parseFloat($('input[name=order_shipping_price]').val());	//运费
				//var insurance=parseFloat($('input[name=order_shipping_insurance]').attr('price'));	//运费保险
				
				//运费计算
				var inputPrice=$('#PlaceOrderFrom input[name=order_shipping_price]'),
					inputPrice_ary={},
					shipPrice=0;
				inputPrice.val()!='[]' && (inputPrice_ary=$.evalJSON(inputPrice.val()));
				for(k in inputPrice_ary){
					shipPrice+=parseFloat(inputPrice_ary[k]);
				}
				//保险费计算
				var inputInsurance=$('#PlaceOrderFrom input[name=order_shipping_insurance]'),
					inputInsurance_ary={}, inputInsurancePrice_ary={},
					insurancePrice=0;
				inputInsurance.val()!='[]' && (inputInsurance_ary=$.evalJSON(inputInsurance.val()));
				inputInsurance.attr('price')!='[]' && (inputInsurancePrice_ary=$.evalJSON(inputInsurance.attr('price')));
				for(k in inputInsurance_ary){
					if(inputInsurance_ary[k]==1){
						insurancePrice+=parseFloat(inputInsurancePrice_ary[k]);
					}
				};
				
				var totalAmount=amount-userPrice+shipPrice+insurancePrice-cutprice-discountPrice;	//最终价格
				var feePrice=totalAmount*(fee/100)+affix;	//付款手续费
				if(feePrice<0) feePrice=0;
				
				$('#ot_fee').text($('html').currencyFormat(feePrice.toFixed(2), ueeshop_config.currency)).attr({'fee':fee, 'affix':affix});
				$('#ot_total').text($('html').currencyFormat((totalAmount*(1+fee/100)+affix).toFixed(2), ueeshop_config.currency));
				
				if(fee>0 || affix>0){
					$('#serviceCharge').show();
				}else{
					$('#serviceCharge').hide();
				}
            });
			
			/******************************** 收货地址 Start ********************************/
			var notnull			= $('#address_from input[notnull],#address_from select[notnull]'),
				address_list	= $('#address_list'),
				address_row		= $('.address_row', address_list),
				address_from	= $('#address_from'), //地址表单
				edit_btn		= $('.edit_address_info', address_row);//修改地址按钮
				
			if(address_perfect){
				$('#address_list .address_row.cur .edit_address_info').click();
				$('#useAddressBack').hide();
			}
			
			$('#country').change(function(e){
				var CId = $(this).find(':selected').val();
				user_obj.get_state_from_country(CId);
            });
			
			//提交地址
			var address_rq_mark=true;
			$('#useAddress').on('tap', function(){
				if(address_rq_mark && !$('#useAddress').hasClass('disabled')){
					$('#useAddress').addClass('disabled');
					address_rq_mark=false;
					notnull.removeClass('null');
					setTimeout(function(){
						var CountNull=0;
						notnull.each(function(index, element) {
							if($(element).val()==''){
								$(element).addClass('null');
								CountNull++;
							}
						});
						var Email=$('#address_from input[name=Email]');
						if(Email.length && /^\w+[a-zA-Z0-9-.+_]+@[a-zA-Z0-9-.+_]+\.\w*$/.test(Email.val())==false){
							Email.addClass('null');
							CountNull++;
						}
						if(CountNull){//检查表单
							address_rq_mark = true;
							$('#useAddress').removeClass('disabled');
							return;
						}
						var obj=$('#address_from form');
						var typeAddr=parseInt(obj.find('input[name=typeAddr]').val())==1?1:0;
						
						if(typeAddr==1){
							checkout_no_login();
						}else{
							$.post('/account/', obj.serialize()+'&do_action=user.addressbook_mod', function(data){
								if(data.ret==1){
									window.location.reload();
								}else{
									address_rq_mark = true;
									$('#useAddress').removeClass('disabled');
								}
							}, 'json');
							
						}
					}, 10);
				}
			});
			
			/******************************** 收货地址 End ********************************/
			
			/******************************** 运费 Start ********************************/
			var shippingObj=$('#shippingObj');
			
			shippingObj.on('tap click', '.shipping_row', function(e){
				e.stopPropagation(); //阻止JavaScript事件冒泡传递
				var OvId=$(this).parents('.oversea').attr('data-id'),
					SId=$(this).attr('data-sid'),
					type=$(this).attr('data-shippingtype'),
					price=$(this).attr('data-price'),
					insurance=$(this).attr('data-insurance'),
					express=$(this).attr('data-method'),
					inputCId=$('#PlaceOrderFrom input[name=order_shipping_address_cid]'),
					inputSId=$('#PlaceOrderFrom input[name=order_shipping_method_sid]'),
					inputType=$('#PlaceOrderFrom input[name=order_shipping_method_type]'),
					inputPrice=$('#PlaceOrderFrom input[name=order_shipping_price]'),
					inputSId_ary={},inputType_ary={}, inputPrice_ary={};
				
				inputSId.val()!='[]' && (inputSId_ary=$.evalJSON(inputSId.val()));
				inputType.val()!='[]' && (inputType_ary=$.evalJSON(inputType.val()));
				inputPrice.val()!='[]' && (inputPrice_ary=$.evalJSON(inputPrice.val()));
				
				/*if(inputSId_ary['OvId_'+OvId]==SId && inputType_ary['OvId_'+OvId]==type && $(this).attr('data-cid')==inputCId.val()){
					return false;
				}*/
				
				$(this).addClass('current').siblings().removeClass('current');
				cart_obj.set_shipping_method(OvId, SId, price, type, insurance, express);
			});
			
			$('#shippingObj ._shipping_insurance').on('change', function(){ //选择保险
				var v=$(this).is(':checked')?1:0;
				var inputPrice=$('#PlaceOrderFrom input[name=order_shipping_price]'),
					inputPrice_ary={},
					shipPrice=0;
				inputPrice.val()!='[]' && (inputPrice_ary=$.evalJSON(inputPrice.val()));
				for(k in inputPrice_ary){
					shipPrice+=parseFloat(inputPrice_ary[k]);
				}
				
				//保险费计算
				var OvId=$(this).parents('.oversea').attr('data-id'),
					insurance=v==1?parseFloat($(this).parents('.oversea').find('.shipping_row.current').attr('data-insurance')):0;
					inputInsurance=$('#PlaceOrderFrom input[name=order_shipping_insurance]'),
					inputInsurance_ary={}, inputInsurancePrice_ary={},
					insurancePrice=0, insuranceShow=0;
				if(isNaN(insurance)) insurance=0;
				
				inputInsurance.val()!='[]' && (inputInsurance_ary=$.evalJSON(inputInsurance.val()));
				inputInsurance_ary['OvId_'+OvId]=v;
				inputInsurance.val($.toJSON(inputInsurance_ary));
				$('input[name=ShippingInsurance]').length && $('input[name=ShippingInsurance]').val($.toJSON(inputInsurance_ary));
				
				inputInsurance.attr('price')!='[]' && (inputInsurancePrice_ary=$.evalJSON(inputInsurance.attr('price')));
				inputInsurancePrice_ary['OvId_'+OvId]=insurance;
				inputInsurance.attr('price', $.toJSON(inputInsurancePrice_ary));
				$('input[name=ShippingInsurancePrice]').length && $('input[name=ShippingInsurancePrice]').val($.toJSON(inputInsurancePrice_ary));
				
				for(k in inputInsurance_ary){
					if(inputInsurance_ary[k]==1){
						insurancePrice+=parseFloat(inputInsurancePrice_ary[k]);
						insuranceShow=1;
					}
				};
				cart_obj.show_shipping_price(insuranceShow);
				
				//价格显示
				var cart_price=cart_obj.cart_price_init();
				$('#ot_fee').text($('html').currencyFormat(cart_price.feePrice.toFixed(2), ueeshop_config.currency));
				$('#shipping_charges span').text($('html').currencyFormat(cart_price.price.toFixed(2), ueeshop_config.currency));
				$('#shipping_and_insurance span').text($('html').currencyFormat(cart_price.shippingPrice.toFixed(2), ueeshop_config.currency));
				$('#ot_total').text($('html').currencyFormat((cart_price.totalAmount+cart_price.feePrice).toFixed(2), ueeshop_config.currency));
			});
			
			/******************************** 运费 End ********************************/
			
			/******************************** 优惠券 Start ********************************/
			var coupon_ajax_mark=true,
				couponCode=$('input[name=order_coupon_code]').val();
			
			if(couponCode!='') ajax_get_coupon_info(couponCode);
			
			$('#coupon_apply').on('tap', function(){
				var code=$('input[name=couponCode]').val();
				if(code && coupon_ajax_mark){
					ajax_get_coupon_info(code);
				}else{
					$('input[name=couponCode]').addClass('null');
					setTimeout(function(){
						$('input[name=couponCode]').removeClass('null');
					}, 2000);
				}
			});
			
			$('#removeCoupon').on('tap', function (){
				$('#couponSavings, #code_valid').hide();
				$('.code_input').slideDown(200);
				$('#code_valid strong').text('');
				$('input[name=order_coupon_code]').val('').attr('cutprice', '0.00');
				$.post('/?do_action=cart.remove_coupon');
				var cart_price=cart_obj.cart_price_init();
				$('#ot_fee').text($('html').currencyFormat(cart_price.feePrice.toFixed(2), ueeshop_config.currency));
				$('#couponSavings .value span').text($('html').currencyFormat(cart_price.cutprice.toFixed(2), ueeshop_config.currency));
				$('#ot_total').text($('html').currencyFormat((cart_price.totalAmount+cart_price.feePrice).toFixed(2), ueeshop_config.currency));
				coupon_ajax_mark=true;
			});
			
			function ajax_get_coupon_info(code){
				coupon_ajax_mark=false;
				var price=parseFloat($('#PlaceOrderFrom').attr('amountPrice'));
				var userprice=parseFloat($('#PlaceOrderFrom').attr('userprice'));
				var order_discount_price=parseFloat($('input[name=order_discount_price]').val());
				var order_cid=$('input[name=CartCId]').val();
				$.post('/?do_action=cart.ajax_get_coupon_info', {coupon:code, price:price, order_discount_price:order_discount_price, userprice:userprice, order_cid:(order_cid?order_cid:'')}, function(data){
					if(data.msg.status==1){
						var cutprice=parseFloat(data.msg.cutprice)*ueeshop_config.currency_rate;
						$('input[name=couponCode]').val('');
						$('.code_input').hide(0);
						$('#couponSavings').slideDown(200);
						$('input[name=order_coupon_code]').val(data.msg.coupon).attr('cutprice', cutprice);
						var cart_price=cart_obj.cart_price_init();
						$('#couponSavings .value span').text(cart_price.cutprice.toFixed(2));
						$('#code_valid').slideDown(200);
						$('#code_valid strong').eq(0).text(data.msg.coupon);
						$('#code_valid strong').eq(1).text(ueeshop_config.currency + $('html').currencyFormat(cart_price.cutprice.toFixed(2), ueeshop_config.currency));
						$('#code_valid strong').eq(2).text(data.msg.end);
						$('#ot_fee').text($('html').currencyFormat(cart_price.feePrice.toFixed(2), ueeshop_config.currency));
						$('#ot_total').text($('html').currencyFormat((cart_price.totalAmount+cart_price.feePrice).toFixed(2), ueeshop_config.currency));
						coupon_ajax_mark=true;
					}else{
						$('html').tips_box((lang_obj.cart.coupon_tips_to).replace('%coupon%', code), 'error');
						coupon_ajax_mark=true;
					}
				}, 'json');
			}
			/******************************** 优惠券 End ********************************/
			
			/******************* Paypal Checkout Start *******************/
			$('#select_country select[name=CId]').on('change', function(){
				cart_obj.get_shipping_method_from_country($(this).val());//运费
			});
			
			//提交
			$('#paypal_checkout').on('click', function(){
				var obj=$('#PlaceOrderFrom');
				$(this).attr('disabled', 'disabled').blur();
				if(obj.find('input[name=order_products_attribute_error]').val()==1){//检查是否存在错误产品
					$('html').tips_box(lang_obj.cart.attribute_error, 'error');
					setTimeout(function(){window.location.href='/cart/';},2000);
					return false;
				}
				if(parseInt(obj.find('input[name=SId]').val())<1 && obj.find('input[name=ShippingMethodType]').val()==''){
					$('html').tips_box(lang_obj.cart.shipping_method_tips, 'error');
					$(this).removeAttr('disabled');
					return false;
				}
				//快捷支付统计
				analytics_click_statistics(1);//暂时统计为添加购物车事件
				obj.submit();
			});
			/******************* Paypal Checkout End *******************/
			
			//提交
			$('#cart_checkout').on('click touchstart', function(e){
				e.preventDefault();
				if ($(this).hasClass('processing')){
					return false;
				}
				var btnTxt=$(this).text();
				$(this).addClass('processing').text(lang_obj.cart.processing_str+'...');
				
				if($('#PlaceOrderFrom input[name=order_products_attribute_error]').val()==1){//检查是否存在错误产品
					$('html').tips_box(lang_obj.cart.attribute_error, 'error');
					$(this).removeClass('processing').text(btnTxt);
					setTimeout(function(){window.location.href='/cart/';},2000);
					return false;
				}

				$('#PlaceOrderFrom').submit(function(e){return false;});
				var Attr='';
				if($('#PlaceOrderFrom').attr('nologin')){
					Attr=$('#PlaceOrderFrom').attr('nologin');
				}
				if($('#address_from:visible').length){
					$('html').tips_box('Please save your shipping address data, then your order submission!', 'error');
					$(this).removeClass('processing').text(btnTxt);
					return false;
				}
				$.post('/?do_action=cart.placeorder', $('#PlaceOrderFrom').serialize()+Attr, function(data){
					if(data.ret==1){//成功
						//Place an Order 生成订单 统计
						analytics_click_statistics(5);
						parseInt(ueeshop_config.FbPixelOpen)==1 && $('html').fbq_checkout();
						window.location.href='/cart/complete/'+data.msg.OId+'.html?utm_nooverride=1';
					}else if(data.ret==-1){//地址错误
						$('html').tips_box(lang_obj.cart.address_error, 'error');
					}else if(data.ret==-2){//送货方式错误
						$('html').tips_box(lang_obj.cart.shipping_error, 'error');
					}else if(data.ret==-3){//支付方式错误
						$('html').tips_box(lang_obj.cart.payment_error, 'error');
						$('#paymentObj .box_select').css('border', '.0625rem #900 solid');
						$('body, html').animate({scrollTop:$('#paymentObj').offset().top}, 500);
						setTimeout(function(){ //3秒后自动清除
							$('#paymentObj .box_select').removeAttr('style');
						}, 3000);
					}else if(data.ret==-4){
						$('html').tips_box(lang_obj.cart.product_error, 'error');
					}else if(data.ret==-5){
						$('html').tips_box(lang_obj.cart.low_error+': '+data.msg, 'error');
					}else if(data.ret==-6){
						var arr=data.msg.split(',');
						for(i in arr){
							if(!$('.cart_item_list .item[cid='+arr[i]+'] .stock_error').length){
								$('.cart_item_list .item[cid='+arr[i]+'] .cart_attr_list').append('<p class="error stock_error">'+lang_obj.cart.prod_stock_error+'</p>');
							}
						}
						$('html').tips_box(lang_obj.cart.stock_error, 'error');
					}
					$('#cart_checkout').removeClass('processing').text(btnTxt);
				}, 'json');
			});
		},
		
		checkout_no_login:function(json){
			$.post('/?do_action=cart.set_no_login_address', json, function(data){
				if(data.ret==1){
					$('#PlaceOrderFrom').attr('nologin', data.msg.info);
					/*var html='<div class="address_row" data-aid="0" data-cid="'+data.msg.v.CId+'">';
							html+='<strong>'+data.msg.v.FirstName+' '+data.msg.v.LastName+' ('+data.msg.v.Country+')</strong>';
							html+='<p>'+(data.msg.v.StateName ? data.msg.v.StateName : data.msg.v.State)+', '+data.msg.v.City+', '+data.msg.v.AddressLine1+' '+(data.msg.v.AddressLine2 ? data.msg.v.AddressLine2+' ' : '');
							html+='<p>'+data.msg.v.ZipCode+'</p>';
						html+='</div>';
					*/
					cart_obj.get_shipping_method_from_country(data.msg.v.CId);
					$('#PlaceOrderFrom input[name=order_shipping_address_aid]').val(0).next('input[name=order_shipping_address_cid]').val(data.msg.v.CId);
					$('input[name=ShoppingCId]').length && $('input[name=ShoppingCId]').val(data.msg.v.CId);
					//cart_obj.checkout_init(parseInt(data.msg.v.CId)).get_shipping_method_from_country();
				}
				address_rq_mark=true;
			}, 'json');
		},
		
		modify_cart_result:function(obj, query_string, is_tips){
			if(cart_obj.update_cart_mark){
				cart_obj.update_cart_mark=false;
				$.post('/?do_action=cart.modify&t='+Math.random(), query_string, function(data){
					if(data.ret==1){
						cart_obj.update_cart_mark=true;
						//var price=$(obj).parents('.item').find('.price');
						if(is_tips && data.msg.qty==obj.val()){ //提示
							$('html').tips_box(lang_obj.products.warning_number, 'error');
						}
						obj.val(data.msg.qty);
						obj.parent().parent().siblings('input[name="S_Qty[]"]').val(data.msg.qty);
						//price.html(ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.price, ueeshop_config.currency));
						for(k in data.msg.price){
							$(".cart_list .item[cid="+k+"] .price").html(ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.price[k], ueeshop_config.currency));
						}
						
						var userRatio=parseInt($('.cart_total .savings').attr('userRatio')); //会员优惠折扣比率
						var userPrice=parseFloat(data.msg.total_price)-(parseFloat(data.msg.total_price)*(userRatio/100));
						var discountPrice=parseFloat(data.msg.cutprice); //满额减价
						var cutprice=0;
						userPrice=parseFloat(userPrice);
						if(userPrice || discountPrice){
							if(discountPrice>userPrice) cutprice=discountPrice;
							else cutprice=userPrice;
						}
						$('.cutprice_p').text('-'+ueeshop_config.currency_symbols+$('html').currencyFormat(cutprice, ueeshop_config.currency));
						if(cutprice){ //控制全场满减的显示
							$('.cart_total .savings').show();
						}else{
							$('.cart_total .savings').hide();
						}
						var punit=data.msg.total_count>1?'itemsCount':'itemCount';
						//$('.cart_total .total b').text(data.msg.total_count);
						$('.cart_total .total strong').html('('+lang_obj.cart[punit].replace('%num%', '<b>'+data.msg.total_count+'</b>')+')');
						$('.cart_total .total .p').html(ueeshop_config.currency_symbols+$('html').currencyFormat(parseFloat(data.msg.total_price)-cutprice, ueeshop_config.currency));
					}
				}, 'json');
			}
		},
		
		select_cart_result:function(){
			var $CId='0';
			$('.cart_list input[name=select]:checked').each(function(){
				$CId+=','+$(this).parents('.item').find("input[name=CId\\[\\]]").val();
			});
			$.post('/?do_action=cart.select&t='+Math.random(), 'CId='+$CId, function(data){
				if(data.ret==1){
					var total=parseFloat(data.msg.total_price);
					var userRatio=parseInt($('.cart_total .savings').attr('userRatio')); //会员优惠折扣比率
					var userPrice=total-(total*(userRatio/100));
					var discountPrice=parseFloat(data.msg.cutprice);
					var cutprice=0;
					if(userPrice && discountPrice){
						if(discountPrice>userPrice) cutprice=discountPrice;
						else cutprice=userPrice;
					}
					$('.cutprice_p').text('-'+ueeshop_config.currency_symbols+$('html').currencyFormat(cutprice, ueeshop_config.currency));
					if(cutprice){ //控制全场满减的显示
						$('.cart_total .savings').show();
					}else{
						$('.cart_total .savings').hide();
					}
					$('.cart_total .total b').text(data.msg.total_count);
					$('.cart_total .total .p').html(ueeshop_config.currency_symbols+$('html').currencyFormat(total-cutprice, ueeshop_config.currency));
				}
			}, 'json');
		},
		
		update_cart_mark:true,
		
		cart_price_init:function(){//返回价格
			//运费计算
			var inputPrice=$('#PlaceOrderFrom input[name=order_shipping_price]'),
				inputPrice_ary={},
				shipPrice=0;
			inputPrice.val()!='[]' && (inputPrice_ary=$.evalJSON(inputPrice.val()));
			for(k in inputPrice_ary){
				shipPrice+=parseFloat(inputPrice_ary[k]);
			}
			//保险费计算
			var inputInsurance=$('#PlaceOrderFrom input[name=order_shipping_insurance]'),
				inputInsurance_ary={}, inputInsurancePrice_ary={},
				insurancePrice=0;
			inputInsurance.val()!='[]' && (inputInsurance_ary=$.evalJSON(inputInsurance.val()));
			inputInsurance.attr('price')!='[]' && (inputInsurancePrice_ary=$.evalJSON(inputInsurance.attr('price')));
			for(k in inputInsurance_ary){
				if(inputInsurance_ary[k]==1){
					insurancePrice+=parseFloat(inputInsurancePrice_ary[k]);
				}
			};
			//总价计算
			var amount=parseFloat($('#PlaceOrderFrom').attr('amountPrice')); //产品总价
			var userPrice=parseFloat($('#PlaceOrderFrom').attr('userPrice')); //会员优惠
			var discountPrice=parseFloat($('input[name=order_discount_price]').val()); //满额减价
			var cutprice=parseFloat($('input[name=order_coupon_code]').attr('cutprice')); //折扣
			var shippingPrice=shipPrice+insurancePrice;	//运费+保险费
			var fee=parseFloat($('#ot_fee').attr('fee'));
			var affix=parseFloat($('#ot_fee').attr('affix'));
			if(isNaN(fee)) fee=0;
			if(isNaN(affix)) affix=0;
			var totalAmount=amount-userPrice+shippingPrice-cutprice-discountPrice; //最终价格
			var feePrice=totalAmount*(fee/100)+affix; //付款手续费
			if(feePrice<0) feePrice=0;
			return {totalAmount:totalAmount, amount:amount, userPrice:userPrice, discountPrice:discountPrice, cutprice:cutprice, price:shipPrice, insurance:insurancePrice, shippingPrice:shippingPrice, feePrice:feePrice};
		},
		
		complete:function(){ //线下付款
			var pay_form	= $('#pay_form'),
				rq_mark		= true,
				notnull		= $('*[notnull]', pay_form);
			$('#paybtn').on('tap', function(){
				if(rq_mark){
					notnull.removeClass('null');
					setTimeout(function(){
						var status=0;
						notnull.each(function(index, element){
							if($(element).val()==''){
								$(element).addClass('null');
								status=1;
							}else{
								$(element).removeClass('null');
							}
						});
						
						var reg={'Length':/^.*/};
						var tips={'Length':lang_obj.format.length};
						pay_form.find('*[format]').each(function(){
							var o=$(this);
							var s=o.attr('format').split('|');
							if((s[0]=='Length' && $.trim(o.val()).length!=parseInt(s[1])) || (s[0]!='Length' && reg[s[0]].test($.trim(o.val()))===false)){
								$('html').tips_box(tips[s[0]].replace('%num%', s[1]), 'error');
								o.addClass('null');
								status=1;
							}else{
								o.removeClass('null');
							}
						});
						
						if(status){
							return false;
						}
						//通过验证，提交数据
						rq_mark=false;
						$.post('/?do_action=cart.offline_payment', pay_form.serialize(), function(data){
							if(data.ret==1){
								window.location.href=window.location.href;
							}else{
								rq_mark=true;
							}
						}, 'json');
					}, 10);
				}
			});
		},

		get_shipping_method_from_country:function(CId){	//选择快递方式
			if(!CId){
				cart_obj.set_shipping_method(1, -1, 0, '', 0);
				return false;
			}
			var dataVal="CId="+CId;
	
			if($('form input[name=order_products_info]').val()){
				dataVal+=$('form input[name=shipping_method_where]').val();
				dataVal+='&Attr='+$('form input[name=shipping_method_where]').attr('attr');
			}
			if($('input[name=order_cid]').val()) dataVal+='&order_cid='+$('input[name=order_cid]').val();
			if($('input[name=CartCId]').val()) dataVal+='&order_cid='+$('input[name=CartCId]').val();
			
			var shippingObj=$('#shippingObj');
			if(shippingObj.data('shipping_methods'+CId)){
				var data = shippingObj.data('shipping_methods'+CId);
				set_shipping_price(data);
			}else{
				$.post('/?do_action=cart.get_shipping_methods', dataVal, function(data){
					if(data.ret==1){
						shippingObj.data('shipping_methods'+CId, data);//缓存
						set_shipping_price(data);
					}else{
						$('.shipping_list').html('');
						if(data.ret=='-1'){
							cart_obj.set_shipping_method(1, 0, 0, '', 0);
						}else{
							cart_obj.set_shipping_method(1, -1, 0, '', 0);
						}
					}
				}, 'json');
			}// if end
			function set_shipping_price(data){
				var rowObj, rowStr;
				for(OvId in data.msg.info){
					rowStr='';
					for(i=0; i<data.msg.info[OvId].length; i++){
						rowObj=data.msg.info[OvId][i];
						if(parseFloat(rowObj.ShippingPrice)<0) continue;
						rowStr+='<div class="shipping_row clean'+(i+1==data.msg.info[OvId].length?'':' ui_border_b')+'" data-price="'+rowObj.ShippingPrice+'" data-sid="'+rowObj.SId+'" data-insurance="'+rowObj.InsurancePrice+'" data-shippingtype="'+rowObj.type+'" data-method="'+rowObj.Name+'" data-cid="'+CId+'">';
						rowStr+=	'<div class="icon"><i class="FontBgColor"></i></div>';
						rowStr+=	'<div class="con">';
						rowStr+=		'<span>'+rowObj.Name+'</span>';
						if(rowObj.IsAPI==1 && rowObj.Name.toUpperCase()=='DHL' && rowObj.Shipping!=1000){
							if(rowObj.ShippingPrice>0){
								rowStr+='<span class="price waiting"></span>';
							}else{
								rowStr+='<span>'+lang_obj.products.free_shipping+'</span>';
							}
						}else{
							rowStr+='<span>'+(rowObj.ShippingPrice>0 ? ueeshop_config.currency_symbols+$('html').currencyFormat(rowObj.ShippingPrice, ueeshop_config.currency) : lang_obj.products.free_shipping)+'</span>';
						}
						rowStr+=		'<div class="clear"></div>';
						rowStr+=		'<span>'+rowObj.Brief+'</span>';
						rowStr+=	'</div>';
						rowStr+='</div>';
						
						if(rowObj.IsAPI==1 && rowObj.Name.toUpperCase()=='DHL' && rowObj.Shipping!=1000){
							$('#shipping_method_list li[name=DHL] input[name=_shipping_method]').attr('disabled', true);
							var AId=$('input[name=order_shipping_address_aid]').val();
							$.post('/?do_action=cart.ajax_get_api_info', 'AId='+AId, function(data){
								data=$.evalJSON(data);
								if(data.ret==1){
									$('#shipping_method_list li[name=DHL]>span.price').removeClass('waiting').text(ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg, ueeshop_config.currency)).parent().find('input[name=_shipping_method]').attr({'price':data.msg, 'disabled':false});
									$('#PlaceOrderFrom').append('<input type="hidden" name="order_shipping_DHL" value="'+data.msg+'" />');
								}else{
									ShippingPrice=$('#shipping_method_list li[name=DHL] input').attr('price');
									$('#shipping_method_list li[name=DHL]>span.price').removeClass('waiting').text(ShippingPrice>0 ? ueeshop_config.currency_symbols+$('html').currencyFormat(ShippingPrice, ueeshop_config.currency) : lang_obj.products.free_shipping);
								}
							}, 'html');
						}
					}
					$('#shippingObj .oversea[data-id="'+OvId+'"] .shipping_list').html(rowStr);
					if(rowStr==''){
						$('#shippingObj .oversea[data-id="'+OvId+'"] .shipping_list').html(''); //清空内容
						cart_obj.set_shipping_method(1, -1, 0, '', 0);
					}else{
						$('#shippingObj .oversea[data-id="'+OvId+'"] .shipping_list .shipping_row:eq(0)').click(); //默认点击第一个选项
					}
				}
			}
		},

		set_shipping_method:function(OvId, SId, price, type, insurance, express){ //选择运费
			if(SId==-1){
				$('#shippingObj .oversea[data-id="'+OvId+'"] .shipping_list').html('<span class="no_delivery">Sorry! No delivery!</span>');
			}
			
			//运费记录
			var inputExpress=$('input[name=ShippingExpress]'),
				inputSId=$('input[name=order_shipping_method_sid]'),
				inputType=$('input[name=order_shipping_method_type]'),
				inputPrice=$('input[name=order_shipping_price]'),
				inputInsurance=$('input[name=order_shipping_insurance]'),
				inputExpress_ary={}, inputSId_ary={}, inputType_ary={}, inputPrice_ary={}, inputInsurance_ary={}, inputInsurancePrice_ary={};
			
			inputSId.val()!='[]' && (inputSId_ary=$.evalJSON(inputSId.val()));
			inputSId_ary['OvId_'+OvId]=SId;
			inputSId.val($.toJSON(inputSId_ary));
			$('input[name=SId]').length && $('input[name=SId]').val($.toJSON(inputSId_ary));
	
			inputType.val()!='[]' && (inputType_ary=$.evalJSON(inputType.val()));
			inputType_ary['OvId_'+OvId]=type;
			inputType.val($.toJSON(inputType_ary));
			$('input[name=ShippingMethodType]').length && $('input[name=ShippingMethodType]').val($.toJSON(inputType_ary));
			
			inputPrice.val()!='[]' && (inputPrice_ary=$.evalJSON(inputPrice.val()));
			inputPrice_ary['OvId_'+OvId]=price;
			inputPrice.val($.toJSON(inputPrice_ary));
			$('input[name=ShippingPrice]').length && $('input[name=ShippingPrice]').val($.toJSON(inputPrice_ary));
			
			if(inputExpress.length){
				inputExpress.val()!='[]' && (inputExpress_ary=$.evalJSON(inputExpress.val()));
				inputExpress_ary['OvId_'+OvId]=express;
				inputExpress.val($.toJSON(inputExpress_ary));
			}
			
			var shipPice=0; //运费
			for(k in inputPrice_ary){
				shipPice+=parseFloat(inputPrice_ary[k]);
			}
			
			//保险费记录
			var obj=$('#shippingObj .oversea[data-id='+OvId+']'),
				v=obj.find('._shipping_insurance').is(':checked')?1:0;
			obj.find('.insurance_txt span').text($('html').currencyFormat(insurance, ueeshop_config.currency));
			insurance=parseFloat(insurance);
			insurance=v==1?insurance:0;
			
			inputInsurance.val()!='[]' && (inputInsurance_ary=$.evalJSON(inputInsurance.val()));
			inputInsurance_ary['OvId_'+OvId]=v;
			inputInsurance.val($.toJSON(inputInsurance_ary));
			$('input[name=ShippingInsurance]').length && $('input[name=ShippingInsurance]').val($.toJSON(inputInsurance_ary));
			
			inputInsurance.attr('price')!='[]' && (inputInsurancePrice_ary=$.evalJSON(inputInsurance.attr('price')));
			inputInsurancePrice_ary['OvId_'+OvId]=insurance;
			inputInsurance.attr('price', $.toJSON(inputInsurancePrice_ary));
			$('input[name=ShippingInsurancePrice]').length && $('input[name=ShippingInsurancePrice]').val($.toJSON(inputInsurancePrice_ary));
			
			var insurancePrice=0, insuranceShow=0;
			for(k in inputInsurance_ary){
				if(inputInsurance_ary[k]==1){
					insurancePrice+=parseFloat(inputInsurancePrice_ary[k]);
					insuranceShow=1;
				}
			};
			cart_obj.show_shipping_price(insuranceShow);
			
			var cart_price=cart_obj.cart_price_init();
			
			$('#ot_fee').text($('html').currencyFormat(cart_price.feePrice.toFixed(2), ueeshop_config.currency));
			$('#shipping_charges span').text($('html').currencyFormat(cart_price.price.toFixed(2), ueeshop_config.currency));
			$('#shipping_and_insurance span').text($('html').currencyFormat(cart_price.shippingPrice.toFixed(2), ueeshop_config.currency));
			$('#ot_total').text($('html').currencyFormat((cart_price.totalAmount+cart_price.feePrice).toFixed(2), ueeshop_config.currency));
			cart_obj.show_shipping_price(v);
			
			var minPrice=maxPrice=0;
			totalAmount=parseFloat((cart_price.totalAmount-cart_price.feePrice).toFixed(2));
			$('.payment_row').each(function(){
				minPrice=parseFloat($(this).attr('min'));
				maxPrice=parseFloat($(this).attr('max'));
				if(maxPrice?(totalAmount>=minPrice && totalAmount<=maxPrice):(totalAmount>=minPrice)){
					$(this).show();
				}else{
					$(this).hide();
				}
			});
		},

		show_shipping_price:function(v){
			if(v){//有保险
				$('#shipping_charges').hide(0);
				$('#shipping_and_insurance').show(0);
			}else{
				$('#shipping_charges').show(0);
				$('#shipping_and_insurance').hide(0);
			}
		}
	};
})(jQuery, window);
