/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

var cart_obj={
	cart_init:{
		paypal_data:{
			payment: {
				transactions: [
					{
						"amount": {
							"total": "0.00",
							"currency": "USD",
							"details": { "subtotal":"0.00", "tax":"0.00", "shipping":"0.00", "handling_fee":"0.00", "shipping_discount":"0.00", "insurance":"0.00" }
						},
						"item_list":{ "items":[] }
					}
				],
				"note_to_payer": "Contact us for any questions on your order."
			}
		},
		paypal_result:{ "OId":"", "CUSTOM":"" },
		paypal_checkout_init:function(Type){
			//Paypal快捷支付弹窗
			var CId='0',
				ProId='0',
				Qty='1',
				Attr='',
				s_url='';
			if(Type=='shipping_cost'){ //产品详细页
				s_url='&Type=shipping_cost&ProId='+$('#ProId').val()+'&Qty='+$('#quantity').val()+'&Attr='+$('#attr_hide').val()+'&proType='+$('input[name=products_type]').val()+'&SId='+$('input[name=SId]').val();
				ProId = $('input[name=ProId]').val();
				Qty = $('input[name=Qty]').val();
				Attr = $('input[name=Attr]').val();
			}else{ //购物车
				if($('.cartFrom .itemFrom input[name=select_all]').get(0).checked && !$('.cartFrom .itemFrom input[name=select]').not(':checked').length && !$('.cartFrom .itemFrom tbody tr').attr('cid')){//全选
					CId='';
				}else if($('.cartFrom .itemFrom input[name=select]:checked').length){//部分已选
					$('.cartFrom .itemFrom input[name=select]:checked').each(function(){
						CId+=','+$(this).val();
					});
				}else if($('.cartFrom .itemFrom tbody tr').attr('cid')!=''){//Butnow
					CId+=','+$('.cartFrom .itemFrom tbody tr').attr('cid');
				}else{
					alert('Please select at least one item!');
					$(this).blur().attr('disabled', false);
					return false;
				}
				s_url='&CId='+CId;
			}
			$.post('/?do_action=cart.check_low_consumption&t='+Math.random(), {'CId':CId,'ProId':ProId,'Qty':Qty,'Attr':Attr}, function(data){ //最低消费金额判断
				if(data.ret==1){ //符合
					$.ajax({
						type: "POST",
						url: "/?do_action=cart.get_excheckout_country"+s_url,
						dataType: "json",
						success: function(data){
							if(data.ret==1){
								var c=data.msg.country,
									h=data.msg.hot_country,
									country_select='',
									defaultCId=226,
									shoppingCId=0,
									s=0, CountryAry=new Object;
								h.length>0 && (country_select+='<optgroup label="---------">');
								for(i=0; i<h.length; i++){ //热门国家
									if(h[i].IsDefault==1) defaultCId=h[i].CId;
									defaultCId==h[i].CId && (s=1);
									CountryAry=$.evalJSON(h[i].CountryData);
									if(!CountryAry){ //丢失了国家名称
										CountryAry=new Object;
										CountryAry[ueeshop_config.lang]='';
									}
									country_select+='<option value="'+h[i].CId+'" '+(defaultCId==h[i].CId?'selected':'')+'>'+CountryAry[ueeshop_config.lang]+'</option>';
								}
								h.length>0 && (country_select+='</optgroup><optgroup label="---------">');
								for(i=0; i<c.length; i++){ //国家列表
									if(c[i].IsDefault==1) defaultCId=c[i].CId;
									CountryAry=$.evalJSON(c[i].CountryData);
									if(!CountryAry){ //丢失了国家名称
										CountryAry=new Object;
										CountryAry[ueeshop_config.lang]='';
									}
									country_select+='<option value="'+c[i].CId+'" '+(s==0 && defaultCId==c[i].CId?'selected':'')+'>'+CountryAry[ueeshop_config.lang]+'</option>';
								}
								h.length>0 && (country_select+='</optgroup>');
								if($('input=[name=order_cid]').length) shoppingCId=$('input=[name=order_cid]').val();
								
								var OvIdStr='', j=0;
								for(k in data.msg.oversea){
									OvIdStr+=(j?',':'')+k;
									++j;
								}
								
								var CartProductPrice=parseFloat(data.msg.CartProductPrice);
								var DiscountPrice=parseFloat(data.msg.DiscountPrice);
								
								var excheckout_html='';
								excheckout_html+='<div id="paypal_checkout_module" class="alert_choose">';
								excheckout_html+=	'<div class="box_bg"></div><a class="noCtrTrack BuyNowBgColor btn_close" id="choose_close"></a>';
								excheckout_html+=	'<div class="choose_content"><form name="paypal_checkout_form" method="POST" action="/payment/paypal_excheckout/do_payment/?utm_nooverride=1">';
								excheckout_html+=		'<h2><div class="box_select"><select name="CId" class="country_list"><option value="0">'+lang_obj.products.select_country+'</option>'+country_select+'</select></div></h2>';
								excheckout_html+=		'<div class="country_error">'+lang_obj.cart.paypal_tips+'</div>';
								excheckout_html+=		'<div id="shipping_method_list">';
								for(k in data.msg.oversea){
									excheckout_html+=		'<div class="oversea" data-id="'+k+'"><div class="title">'+data.msg.oversea[k]+'</div><ul class="list"></ul></div>';
								}
								excheckout_html+=		'</div>';
								
								
								//if(data.msg.IsCreditCard==1){ //开启信用卡支付
								//	excheckout_html+=	'<div class="footRegion footTotal">';
								//	excheckout_html+=		'<span class="choose_price total"><strong>'+lang_obj.orders.order_total+':</strong><span></span></span>';
								//}else{
									excheckout_html+=	'<div class="footRegion">';
									excheckout_html+=		'<span class="choose_price total"><strong>'+lang_obj.orders.order_total+':</strong><span></span></span>';
									excheckout_html+=		'<input class="btn btn-success btn_global sys_shadow_button BuyNowBgColor" id="excheckout_button" type="submit" value="'+lang_obj.cart.continue_str+'" />';
								//}
								if(data.msg.v>=1){//达到使用优惠券版本
									excheckout_html+='<div class="coupon_box_position"><span class="cou_btn">';
									if(c.coupon!='' && c.cutprice>0){
										excheckout_html+='-'+ueeshop_config.currency_symbols+$('html').currencyFormat(c.cutprice, ueeshop_config.currency);
									}else{
										excheckout_html+=lang_obj.global.coupon
									}
									excheckout_html+='</span>';
									var c=data.msg.coupon, code='', price=0;
									if(c.coupon!='' && c.cutprice>0){
										code=c.coupon, price=c.cutprice;
									}
									excheckout_html+='<div class="coupon_box clearfix">';
										excheckout_html+='<div class="code_input clearfix" style="display:'+(price>0?'none':'block')+';">';
											excheckout_html+='<input type="text" name="couponCode" class="box_input" placeholder="'+lang_obj.excheckout.apply+' '+lang_obj.excheckout.coupon_code+'" autocomplete="off" /><div class="btn_coupon_submit btn_global sys_shadow_button BuyNowBgColor" id="coupon_apply">'+lang_obj.global.submit+'</div>';
										excheckout_html+='</div>';
										excheckout_html+='<p class="code_error">'+lang_obj.excheckout.coupon_error+'</p>';
										excheckout_html+='<div class="code_valid clearfix" id="code_valid" style="display:'+(price>0?'block':'none')+';">';
											excheckout_html+='<div class="code_valid_content">'+lang_obj.excheckout.coupon_txt.replace('"<strong></strong>"', '"<strong>'+c.coupon+'</strong>"')+' '+lang_obj.excheckout.discount_txt+' <strong>'+ueeshop_config.currency_symbols+$('html').currencyFormat(c.cutprice, ueeshop_config.currency)+'</strong> ('+lang_obj.excheckout.exp_date+': <strong>'+c.end+'</strong>)</div>';
											excheckout_html+='<a href="javascript:;" class="btn_coupon_remove btn_global sys_shadow_button BuyNowBgColor" id="removeCoupon">'+lang_obj.excheckout.remove+'</a>';
										excheckout_html+='</div>';
										excheckout_html+='<input type="hidden" name="order_coupon_code" value="'+code+'" cutprice="'+price+'" />';
									excheckout_html+='</div></div>';
								}
								excheckout_html+=		'</div>';
								//if(data.msg.IsCreditCard==1){
								//	excheckout_html+=	'<div id="paypal_payment_container"></div>';
								//}
								excheckout_html+=		'<input id="paypal_payment_option" value="paypal_express" type="radio" name="paymentMethod" title="PayPal Express Checkout" class="radio" style="display:none;" checked />';
								excheckout_html+=		'<input type="hidden" name="ProductPrice" value="'+CartProductPrice+'" />';
								excheckout_html+=		'<input type="hidden" name="DiscountPrice" value="'+DiscountPrice+'" />';
								excheckout_html+=		'<input type="hidden" name="ShippingMethodType" value="[]" />';
								excheckout_html+=		'<input type="hidden" name="ShippingPrice" value="[]" />';
								excheckout_html+=		'<input type="hidden" name="ShippingInsurancePrice" value="[]" />';
								excheckout_html+=		'<input type="hidden" name="ShippingExpress" value="[]" />';
								excheckout_html+=		'<input type="hidden" name="ShippingOversea" value="'+OvIdStr+'" />';
								excheckout_html+=		'<input type="hidden" name="ShoppingCId" value="'+shoppingCId+'" />';
								excheckout_html+=		'<input type="hidden" name="CartCId" value="'+CId+'" />';
								excheckout_html+=		'<input type="hidden" name="SourceType" value="'+Type+'" />';
								excheckout_html+=		'<input type="hidden" name="ProInfo" value="'+encodeURI(s_url)+'" />';
								excheckout_html+=		'<input type="hidden" name="IsCreditCard" value="'+data.msg.IsCreditCard+'" />';
								excheckout_html+=		'<input type="hidden" name="AdditionalFee" value="'+data.msg.AdditionalFee+'" />';
								excheckout_html+=	'</form></div>';
								excheckout_html+='</div>';
								excheckout_html+='<script>window.paypalCheckoutReady=function(){ paypal.checkout.setup("'+ueeshop_config.PaypalExcheckout+'", {button:"excheckout_button", environment:"production", condition: function(){ return document.getElementById("paypal_payment_option").checked === true;} }); };</script>';
								$('#paypal_checkout_module').length && $('#paypal_checkout_module').remove();
								$('body').prepend(excheckout_html);
								if(c.coupon!='' && c.cutprice>0){
									$('.coupon_box_position .cou_btn').text('-'+ueeshop_config.currency_symbols+$('html').currencyFormat(c.cutprice, ueeshop_config.currency));
								}
								$('.coupon_box_position .cou_btn').click(function(){
									if($('.coupon_box_position').hasClass('cur')){
										$('.coupon_box_position').removeClass('cur');
									}else{
										$('.coupon_box_position').addClass('cur');
									}
								});
								$('#paypal_checkout_module .total span').text(ueeshop_config.currency_symbols + $('html').currencyFormat(CartProductPrice-DiscountPrice, ueeshop_config.currency));
								$('#paypal_checkout_module').css({left:$(window).width()/2-285, 'top':'20%'});
								global_obj.div_mask();

								if($('#paypal_checkout_module #shipping_method_list .oversea').length==1){//仅有一个海外仓信息，标题不显示
									$('#paypal_checkout_module #shipping_method_list .oversea>.title').addClass('hide');
								}
								
								cart_obj.cart_init.get_shipping_methods(defaultCId, CId, (Type=='shipping_cost'?s_url:''));
								
								//选择国家操作
								$('html').on('change', 'form[name=paypal_checkout_form] select[name=CId]', function(){
									cart_obj.cart_init.get_shipping_methods($(this).val(), CId, (Type=='shipping_cost'?s_url:''));
								});
								
								/********** 暂不使用
								if(data.msg.IsCreditCard==1){ //开启信用卡支付
									cart_obj.cart_init.paypal_data.payment.transactions[0].amount.currency=ueeshop_config.currency;
									cart_obj.cart_init.paypal_data.payment.transactions[0].item_list.items=new Array();
									for(i in data.msg.Item){ //循环产品参数
										cart_obj.cart_init.paypal_data.payment.transactions[0].item_list.items[i]={
											"name": data.msg.Item[i].Name,
											"quantity": data.msg.Item[i].Qty,
											"price": data.msg.Item[i].Price,
											"currency": ueeshop_config.currency
										};
									}
									$("#paypal_payment_container").loading();
									$(".loading_msg").css("top", 10);
									$.getScript("//www.paypalobjects.com/api/checkout.js", function(){
										$("#paypal_payment_container").unloading();
										paypal.Button.render({
											env: 'sandbox',
											style: { layout:'vertical', size:'medium', shape:'rect', color:'blue' },
											client: {
												sandbox:    'AZDxjDScFpQtjWTOUtWKbyN_bDt4OgqaF4eYXlewfBP4-8aqX3PiV8e1GWU6liB2CUXlkA59kJXE7M6R'
												//production: 'AbHstG_eYqXot5AH26BV1s3dMq770y3JRHUqZlE5v0MSyYHwyL6lYaKIkLsaETx5C7b0sRQy7Bf0WalI'
											},
											funding: { allowed:[paypal.FUNDING.CARD, paypal.FUNDING.CREDIT], disallowed:[] },
											payment: function(data, actions){
												if($('#paypal_checkout_module input[name=SourceType]').val()=='shipping_cost'){ //产品详细页
													$.post('/', $('#goods_form').serialize()+'&do_action=cart.additem&IsBuyNow=1&back=1&excheckout=1', function(data){
														data=$.evalJSON(data);
														if(data.ret==1){ //添加购物车
															//快捷支付统计
															analytics_click_statistics(1);//暂时统计为添加购物车事件
															parseInt(ueeshop_config.FbPixelOpen)==1 && $('html').fbq_addtocart(data.msg.item_price);
															$.post('/?do_action=cart.paypal_checkout_payment_log&'+$('form[name=paypal_checkout_form]').serialize(), cart_obj.cart_init.paypal_data.payment.transactions[0], function(result){ //提交前的数据记录
																if(result.ret==1){
																	cart_obj.cart_init.paypal_result.OId=result.msg.OId;
																	cart_obj.cart_init.paypal_result.CUSTOM=result.msg.CUSTOM;
																}
															}, 'json');
														}else if(data.ret==2){ //最低额
															var tips=(lang_obj.cart.consumption).replace('%low_price%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.low_price, ueeshop_config.currency)).replace('%difference%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.difference, ueeshop_config.currency));
															global_obj.new_win_alert(tips, function(){ $('a.processing').removeClass('processing').addClass('checkoutBtn') });
															alert($('.win_alert .win_tips').text());
															$('.win_alert').remove();
															return false;
														}else{
															alert(lang_obj.cart.shipping_method_tips);
															return false;
														}
													});
												}else{
													$.post('/?do_action=cart.paypal_checkout_payment_log&'+$('form[name=paypal_checkout_form]').serialize(), cart_obj.cart_init.paypal_data.payment.transactions[0], function(result){ //提交前的数据记录
														if(result.ret==1){
															cart_obj.cart_init.paypal_result.OId=result.msg.OId;
															cart_obj.cart_init.paypal_result.CUSTOM=result.msg.CUSTOM;
														}
													}, 'json');
												}
												return actions.payment.create(cart_obj.cart_init.paypal_data);
											},
											onAuthorize: function(data, actions){
												return actions.payment.get().then(function(data){ //Get the payment details
													data.web_info=new Object;
													data.web_info.OId=cart_obj.cart_init.paypal_result.OId;
													data.web_info.CUSTOM=cart_obj.cart_init.paypal_result.CUSTOM;
													$.post('/?do_action=cart.paypal_checkout_complete_log', data, function(result){ //提交前的数据记录
														if(result.ret==1){
															return actions.payment.execute().then(function(data){ //Execute the payment
																$.post('/?do_action=cart.paypal_checkout_success_log&OId='+cart_obj.cart_init.paypal_result.OId, data, function(result){ //确认付款成功
																	window.top.location='/cart/success/'+cart_obj.cart_init.paypal_result.OId+'.html';
																});
															});
														}else{
															window.alert(result.msg);
														}
													}, 'json');
												});
											}
										}, '#paypal_payment_container');
									});
								}
								*/
							}
						}
					});
				}else{ //不符合
					var tips=(lang_obj.cart.consumption).replace('%low_price%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.low_price, ueeshop_config.currency)).replace('%difference%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.difference, ueeshop_config.currency));
					global_obj.new_win_alert(tips, function(){ $('a.processing').removeClass('processing').addClass('checkoutBtn') });
					return false;
				}
			}, 'json');
	
			//选择快递操作
			$('html').on('click', 'form[name=paypal_checkout_form] input[name=SId]', function(){
				var price=parseFloat($(this).attr('price'));
				var insurance=parseFloat($(this).attr('insurance'));
				
				$('form[name=paypal_checkout_form] input[name=ShippingMethodType]').val($(this).attr('ShippingType'));
				$('#shipping_method_list li.insurance span.price').text(ueeshop_config.currency_symbols + $('html').currencyFormat(insurance.toFixed(2), ueeshop_config.currency));
				
				$('form[name=paypal_checkout_form] input[name=ShippingExpress]').val($(this).attr('method'));
				$('form[name=paypal_checkout_form] input[name=ShippingPrice]').val(price.toFixed(2));
				
				insurance=$('#__ShippingInsurance').attr('checked')=='checked' ? insurance : 0;
				$('form[name=paypal_checkout_form] input[name=ShippingInsurancePrice]').val(insurance.toFixed(2));
				//$('form[name=paypal_checkout_form] input[name=ShippingPrice]').val(price.toFixed(2));
			});
			
			$('html').on('click', '#__ShippingInsurance', function(){
				var obj=$('form[name=paypal_checkout_form] input[name=SId]:checked')
				var insurance=0;
				if($('#__ShippingInsurance').attr('checked')=='checked'){
					var insurance = parseFloat(obj.attr('insurance'));
				}
				$('form[name=paypal_checkout_form] input[name=ShippingInsurancePrice]').val(insurance.toFixed(2));
			});
			
			//使用优惠券
			$('html').off().on('click', '#paypal_checkout_module #coupon_apply', function(){
				var coupon=$('#paypal_checkout_module input[name=couponCode]').val();
				if(coupon!=''){
					var price=parseFloat($('input[name=ProductPrice]').val());
					var order_discount_price=parseFloat($('input[name=DiscountPrice]').val());
					var order_cid=$('input[name=order_cid]').val();
					$.post('/?do_action=cart.ajax_get_coupon_info', '&coupon='+coupon+'&price='+price+'&order_discount_price='+order_discount_price+'&order_cid='+(order_cid?order_cid:'')+($('input[name=ProInfo]').length?$('input[name=ProInfo]').val():''), function(data){
						var cutprice=parseFloat(data.msg.cutprice);
						if(data.msg.status==1){
							$('.coupon_box .code_input, .coupon_box .code_error').hide();
							$('#CouponCharge').show();
							$('input[name=order_coupon_code]').val(data.msg.coupon).attr('cutprice', cutprice);
							$('#code_valid').slideDown(200);
							$('#code_valid strong').eq(0).text(data.msg.coupon);
							$('#code_valid strong').eq(1).text(ueeshop_config.currency_symbols+$('html').currencyFormat(cutprice.toFixed(2), ueeshop_config.currency));
							$('#code_valid strong').eq(2).text(data.msg.end);
							$('.coupon_box_position').removeClass('cur');
							$('.coupon_box_position .cou_btn').text('-'+ueeshop_config.currency_symbols+$('html').currencyFormat(cutprice.toFixed(2), ueeshop_config.currency));
						}else{
							$('.coupon_box .code_error').show().find('strong').text(data.msg.coupon);
						}
						cart_obj.cart_init.total_price();
					}, 'json');
				}
			});
			
			//删除优惠券
			$('html').on('click', '#paypal_checkout_module #removeCoupon', function(){
				$('input[name=order_coupon_code]').val('').attr('cutprice', '0.00');
				$('.coupon_box .code_input').show(200).find('input').val('');
				$('.coupon_box .code_valid').hide().find('strong').text('');
				$('#CouponCharge').hide();
				$.post('/?do_action=cart.remove_coupon');
				cart_obj.cart_init.total_price();
				$('.coupon_box_position').removeClass('cur');
				$('.coupon_box_position .cou_btn').text(lang_obj.global.coupon);
			});
			
			//优惠券下拉选择
			$('html').on('focus keyup paste mousedown', '#paypal_checkout_module input[name=couponCode]', function(){
				var $This	= $(this),
					$Obj	= $This.parent();
				$.post('/ajax/ajax_coupon.html', {'keyword':$(this).val()}, function(data){
					$Obj.find('.coupon_content_box').remove();
					$Obj.find('input[name=couponCode]').before(data);
					$('.coupon_content_box .item').on('click', function(){
						$('input[name=couponCode]').val($(this).attr('data-number'));
						$('.coupon_content_box').remove();
					});
				});
				$('.coupon_box .code_input').on('mouseleave', function(){
					$(this).parent().find('.coupon_content_box').remove();
				});
			});
			
			//关闭快捷支付
			$('html').on('click', '#paypal_checkout_module .btn_close', function(){
				if($('#paypal_checkout_module').length){
					$('#paypal_checkout_module').remove();
					global_obj.div_mask(1);
					$('button.paypal_checkout_button').removeAttr('disabled');
				}
			});
			
			//提交快捷支付
			$('html').on('submit', 'form[name=paypal_checkout_form]', function(){
				var obj=$('form[name=paypal_checkout_form]');
				obj.find('input[type=submit]').attr('disabled', 'disabled').blur();
				if(!obj.find('input[name=SId]:checked').val() && obj.find('input[name=ShippingMethodType]').val()=='[]'){
					alert(lang_obj.cart.shipping_method_tips);
					$('#excheckout_button').removeAttr('disabled');
					return false;
				}
				
				if($('#paypal_checkout_module input[name=SourceType]').val()=='shipping_cost'){ //产品详细页
					$.post('/', $('#goods_form').serialize()+'&do_action=cart.additem&IsBuyNow=1&back=1&excheckout=1', function(data){
						data=$.evalJSON(data);
						if(data.ret==1){
							//快捷支付统计
							analytics_click_statistics(1);//暂时统计为添加购物车事件
							parseInt(ueeshop_config.FbPixelOpen)==1 && $('html').fbq_addtocart(data.msg.item_price);
							$('#paypal_checkout_module').hide();
							$('button.paypal_checkout_button').removeAttr('disabled');
							global_obj.div_mask(1);
						}else if(data.ret==2){
							var tips=(lang_obj.cart.consumption).replace('%low_price%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.low_price, ueeshop_config.currency)).replace('%difference%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.difference, ueeshop_config.currency));
							global_obj.new_win_alert(tips, function(){ $('a.processing').removeClass('processing').addClass('checkoutBtn') });
							alert($('.win_alert .win_tips').text());
							$('.win_alert').remove();
							$('#excheckout_button').removeAttr('disabled');
							return false;
						}else{
							alert(lang_obj.cart.shipping_method_tips);
							$('#excheckout_button').removeAttr('disabled');
							return false;
						}
					});
				}else{
					$('#paypal_checkout_module').hide();
					$('button.paypal_checkout_button').removeAttr('disabled');
					global_obj.div_mask(1);
				}
			});
		},
		
		total_price:function(){
			//Paypal快捷支付 价格显示
			var $TotalPrice=$('#paypal_checkout_module .total span');
			var CartProductPrice=parseFloat($('#paypal_checkout_module input[name=ProductPrice]').val());
			var DiscountPrice=parseFloat($('#paypal_checkout_module input[name=DiscountPrice]').val());
			var couponPrice=parseFloat($('input[name=order_coupon_code]').attr('cutprice'));
			var Fee=parseFloat($('#paypal_checkout_module input[name=AdditionalFee]').val());
			if(isNaN(Fee)) Fee=0;
			//运费计算
			var inputPrice=$('#paypal_checkout_module input[name=ShippingPrice]'),
				inputPrice_ary={},
				shipPrice=0;
			inputPrice.val()!='[]' && (inputPrice_ary=$.evalJSON(inputPrice.val()));
			for(k in inputPrice_ary){
				shipPrice+=parseFloat(inputPrice_ary[k]);
			}
			//保险费计算
			var inputInsurancePrice=$('#paypal_checkout_module input[name=ShippingInsurancePrice]'),
				inputInsurancePrice_ary={},
				insurancePrice=0;
			inputInsurancePrice.val()!='[]' && (inputInsurancePrice_ary=$.evalJSON(inputInsurancePrice.val()));
			$('#shipping_method_list .oversea').each(function(){
				var obj=$(this).find('li.insurance input:checkbox'),
					OvId=$(this).attr('data-id');
				if(obj.is(':checked')){
					insurancePrice+=parseFloat(inputInsurancePrice_ary['OvId_'+OvId]);
				}
			});
			//手续费
			var totalAmount=parseFloat(CartProductPrice-DiscountPrice+shipPrice+insurancePrice-couponPrice);
			var feePrice=totalAmount*(Fee/100);
			if(feePrice<0) feePrice=0;
			//总价格计算
			$TotalPrice.text(ueeshop_config.currency_symbols+$('html').currencyFormat((totalAmount+feePrice).toFixed(2), ueeshop_config.currency));
			//信用卡支付
			if($('#paypal_checkout_module input[name=IsCreditCard]').val()==1){
				if(ueeshop_config.currency=='TWD' || ueeshop_config.currency=='JPY'){ //取整
					cart_obj.cart_init.paypal_data.payment.transactions[0].amount.total=parseInt((totalAmount+feePrice));
					cart_obj.cart_init.paypal_data.payment.transactions[0].amount.details.subtotal=parseInt(CartProductPrice);
					cart_obj.cart_init.paypal_data.payment.transactions[0].amount.details.insurance=parseInt(insurancePrice);
					cart_obj.cart_init.paypal_data.payment.transactions[0].amount.details.shipping=parseInt(shipPrice);
					cart_obj.cart_init.paypal_data.payment.transactions[0].amount.details.shipping_discount='-'+parseInt((DiscountPrice+couponPrice));
					cart_obj.cart_init.paypal_data.payment.transactions[0].amount.details.handling_fee=parseInt(feePrice);
					cart_obj.cart_init.paypal_data.payment.transactions[0].amount.details.tax=0;
				}else{
					cart_obj.cart_init.paypal_data.payment.transactions[0].amount.total=(totalAmount+feePrice).toFixed(2);
					cart_obj.cart_init.paypal_data.payment.transactions[0].amount.details.subtotal=CartProductPrice.toFixed(2);
					cart_obj.cart_init.paypal_data.payment.transactions[0].amount.details.insurance=insurancePrice.toFixed(2);
					cart_obj.cart_init.paypal_data.payment.transactions[0].amount.details.shipping=shipPrice.toFixed(2);
					cart_obj.cart_init.paypal_data.payment.transactions[0].amount.details.shipping_discount='-'+(DiscountPrice+couponPrice).toFixed(2);
					cart_obj.cart_init.paypal_data.payment.transactions[0].amount.details.handling_fee=feePrice.toFixed(2);
				}
			}
		},
		
		get_shipping_methods:function(CId, CartId, ProInfo){
			//Paypal快捷支付 快递显示
			$.post('/?do_action=cart.get_shipping_methods', "CId="+CId+(CartId?'&order_cid='+CartId:'')+(ProInfo?ProInfo:''), function(data){
				if(data.ret==1){
					var rowObj, rowStr;
					for(OvId in data.msg.info){
						rowStr='';
						if(parseInt(data.msg.IsInsurance)){
							rowStr+='<li class="insurance"><label for="__ShippingInsurance['+OvId+']">';
							rowStr+=	'<input type="checkbox" id="__ShippingInsurance['+OvId+']" name="ShippingInsurance['+OvId+']" value="1">';
							rowStr+=	'<strong>'+lang_obj.cart.insurance+'</strong>';
							rowStr+=	'<span class="price"></span>';
							rowStr+='</label></li>';
						}
						for(i=0; i<data.msg.info[OvId].length; i++){
							rowObj=data.msg.info[OvId][i];
							if(parseFloat(rowObj.ShippingPrice)<0) continue;
							rowStr+='<li class="shipping" name="'+rowObj.Name.toUpperCase()+'">';
							rowStr+=	'<span class="name">';
							rowStr+=		'<input type="radio" name="SId['+OvId+']" value="'+rowObj.SId+'" method="'+rowObj.Name+'" price="'+ rowObj.ShippingPrice+'" insurance="'+rowObj.InsurancePrice+'" ShippingType="'+rowObj.type+'" cid="'+CId+'" />';
							rowStr+=		'<label>'+rowObj.Name+'</label>';
							if(rowObj.ShippingPrice>0){
								rowStr+='<span class="price">'+ueeshop_config.currency_symbols+$('html').currencyFormat(rowObj.ShippingPrice, ueeshop_config.currency)+'</span>';
							}else{
								rowStr+='<span class="price free_shipping">'+lang_obj.products.free_shipping+'</span>';
							}
							rowStr+='	</span>';
							rowStr+=	(rowObj.Brief?'<span class="brief" title="'+rowObj.Brief+'">'+rowObj.Brief+'</span>':'');
							rowStr+=	'<div class="clear"></div>';
							rowStr+='</li>';
							//使用API接口
							if(rowObj.IsAPI>0){
								var $CId=$('form[name=paypal_checkout_form] select[name=CId]').val();
								$('#shipping_method_list .oversea[data-id='+OvId+'] .list li[name="'+rowObj.Name.toUpperCase()+'"] input').attr('disabled', true);
								$.post('/?do_action=cart.ajax_get_api_info', "OvId="+OvId+"&CId="+$CId+"&Name="+rowObj.Name.toUpperCase()+"&IsAPI="+rowObj.IsAPI+"&cCId="+CId+(CartId?'&order_cid='+CartId:'')+(ProInfo?ProInfo:''), function(data){
									var $apiObj=$('#shipping_method_list .oversea[data-id='+data.msg.OvId+'] .list li[name="'+data.msg.Name+'"]');
									if(data.ret==1){
										$apiObj.find('.price').text(data.msg.Price>0 ? ueeshop_config.currency_symbols+$('html').currencyFormat(parseFloat(data.msg.Price)*ueeshop_config.currency_rate, ueeshop_config.currency) : lang_obj.products.free_shipping).parent().find('input').attr({'price':parseFloat(data.msg.Price)*ueeshop_config.currency_rate, 'disabled':false});
									}else{
										$Price=$apiObj.find('input').attr('price');
										$apiObj.find('.price').text($Price>0 ? ueeshop_config.currency_symbols+$('html').currencyFormat($Price, ueeshop_config.currency) : lang_obj.products.free_shipping);
									}
								}, 'json');
							}
						}
						$('#shipping_method_list .oversea[data-id='+OvId+'] .list').html(rowStr);
					}
					//价格显示
					$('#shipping_method_list li.shipping input:radio').change(function(){
						var express=$(this).attr('method'),
							type=$(this).attr('ShippingType'),
							shipPrice=parseFloat($(this).attr('price')),
							insurance=parseFloat($(this).attr('insurance')),
							OvId=$(this).parents('.oversea').attr('data-id');
						$(this).parents('ul.list').find('.insurance .price').text(ueeshop_config.currency_symbols+$('html').currencyFormat(insurance, ueeshop_config.currency));
						//运费记录
						var inputExpress=$('#paypal_checkout_module input[name=ShippingExpress]'),
							inputType=$('#paypal_checkout_module input[name=ShippingMethodType]'),
							inputPrice=$('#paypal_checkout_module input[name=ShippingPrice]'),
							inputInsurancePrice=$('#paypal_checkout_module input[name=ShippingInsurancePrice]'),
							inputExpress_ary={}, inputType_ary={}, inputPrice_ary={}, inputInsurancePrice_ary={};
							
						inputExpress.val()!='[]' && (inputExpress_ary=$.evalJSON(inputExpress.val()));
						inputExpress_ary['OvId_'+OvId]=express;
						inputExpress.val($.toJSON(inputExpress_ary));
				
						inputType.val()!='[]' && (inputType_ary=$.evalJSON(inputType.val()));
						inputType_ary['OvId_'+OvId]=type;
						inputType.val($.toJSON(inputType_ary));
						
						inputPrice.val()!='[]' && (inputPrice_ary=$.evalJSON(inputPrice.val()));
						inputPrice_ary['OvId_'+OvId]=shipPrice;
						inputPrice.val($.toJSON(inputPrice_ary));
						
						if($(this).parents('ul.list').find('.insurance input:checkbox').is(':checked')){
							inputInsurancePrice.val()!='[]' && (inputInsurancePrice_ary=$.evalJSON(inputInsurancePrice.val()));
							inputInsurancePrice_ary['OvId_'+OvId]=insurance;
							inputInsurancePrice.val($.toJSON(inputInsurancePrice_ary));
						}
						
						cart_obj.cart_init.total_price();
					});
					$('#shipping_method_list li.insurance input:checkbox').change(function(){
						var obj=$(this).parents('ul.list').find('.shipping input:radio:checked'),
							OvId=$(this).parents('.oversea').attr('data-id'),
							inputInsurancePrice=$('#paypal_checkout_module input[name=ShippingInsurancePrice]'),
							inputInsurancePrice_ary={},
							insurance=0;
						if($(this).length>0 && $(this).is(':checked')){
							insurance=parseFloat(obj.attr("insurance"));
						}
						inputInsurancePrice.val()!='[]' && (inputInsurancePrice_ary=$.evalJSON(inputInsurancePrice.val()));
						inputInsurancePrice_ary['OvId_'+OvId]=insurance;
						inputInsurancePrice.val($.toJSON(inputInsurancePrice_ary));
						cart_obj.cart_init.total_price();
					});
					//默认点击第一个
					$('#shipping_method_list .oversea').each(function(){
						$(this).find('li.shipping:eq(0) input:radio').click().change();
						$(this).find('li.insurance input:checkbox').click().change();
					});
				}else{
					$('form[name=paypal_checkout_form] input[name=ShippingExpress]').val('[]');
					$('form[name=paypal_checkout_form] input[name=ShippingMethodType]').val('[]');
					//$('form[name=paypal_checkout_form] input[name=ShippingInsurance]').val('');
					$('form[name=paypal_checkout_form] input[name=ShippingInsurancePrice]').val('[]');
					$('form[name=paypal_checkout_form] input[name=ShippingPrice]').val('[]');
					//$('#shipping_method_list').html('');
					$('#shipping_method_list .oversea .list').html('');
				}
				//没有快递选项，把提交按钮和优惠券，都隐藏起来
				if($('form[name=paypal_checkout_form] input[name=ShippingExpress]').val()=='[]'){
					$('#excheckout_button, .ex_coupon').hide();
				}else{
					$('#excheckout_button, .ex_coupon').show();
				}
			}, 'json');
		},
		
		ajax_get_coupon_info:function(code){
			//下单页面 优惠券显示
			var str='';
			if($('input[name=order_products_info]').length){ str='&jsonData='+$('input[name=order_products_info]').val();}
			var price=parseFloat($('#PlaceOrderFrom').attr('amountprice'));
			var userprice=parseFloat($('#PlaceOrderFrom').attr('userprice'));
			var order_discount_price=parseFloat($('input[name=order_discount_price]').val());
			var order_cid=$('input[name=order_cid]').val();
			$.post("/?do_action=cart.ajax_get_coupon_info", '&coupon='+code+'&price='+price+str+'&userprice='+userprice+'&order_discount_price='+order_discount_price+'&order_cid='+(order_cid?order_cid:''), function(data){
				if(data.msg.status==1){
					var cutprice=parseFloat(data.msg.cutprice);
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
					//订单各项价格的计算
					cart_obj.cart_init.order_price_charge({
						'Amount'		: $('#PlaceOrderFrom').attr('amountPrice'), //产品总价
						'UserPrice'		: $('#PlaceOrderFrom').attr('userPrice'), //会员优惠
						'DiscountPrice'	: $('input[name=order_discount_price]').val(), //全场满减
						'CutPrice'		: cutprice, //优惠券优惠
						'ShippingPrice'	: shipPrice, //快递运费
						'InsurancePrice': insurancePrice, //快递保险费
						'Fee'			: $('#ot_fee').attr('data-fee'), //手续费
						'Affix'			: $('#ot_fee').attr('data-affix'), //手续附加费
					});
					//优惠券处理
					$('.coupon_box .code_input, .coupon_box .code_error').hide();
					$('#CouponCharge').show();
					$('input[name=order_coupon_code]').val(data.msg.coupon).attr('cutprice', cutprice);
					$('#code_valid').slideDown(200);
					var codeKey=(data.msg.type==1?'<span class="price"><span class="symbols">'+ueeshop_config.currency_symbols+'</span>'+$('html').currencyFormat(cutprice.toFixed(2), ueeshop_config.currency)+'</span>':'<span class="discount">'+(100-data.msg.discount)+'</span><p>% OFF<\/p>');
					$('#code_valid .code_valid_key').html(codeKey);
					$('#code_valid strong').eq(0).text(data.msg.coupon);
					$('#code_valid strong').eq(1).text(data.msg.end);
				}else{
					$('.coupon_box .code_error').show().find('strong').text(data.msg.coupon);
				}
			}, 'json');
		},
		
		price_change:function(Data){
			//购物车列表 产品价格改变
			var $Count			= parseInt(Data.Count),
				$TotalPrice		= parseFloat(Data.TotalPrice),
				$DiscountPrice	= parseFloat(Data.CutPrice), //满额减价
				$Unit			= $Count>1?'itemsCount':'itemCount',
				$UserRatio		= parseInt($('.cutprice_box').attr('userRatio')), //会员优惠折扣比率
				$UserPrice		= $TotalPrice-($TotalPrice*($UserRatio/100)),
				$CutPrice		= 0,
				$LowPrice		= parseFloat($('#low_price_hidden').val()), //最低消费金额
				$Difference		= 0;
			if($UserPrice || $DiscountPrice){
				if($DiscountPrice>$UserPrice) $CutPrice=$DiscountPrice; //全场满减优惠
				else $CutPrice=$UserPrice; //会员优惠
			}
			$('.cutprice_box .product_price_value').text(ueeshop_config.currency_symbols+$('html').currencyFormat($CutPrice, ueeshop_config.currency)); //优惠价格
			$('.total_box .product_price_value').text(ueeshop_config.currency_symbols+$('html').currencyFormat($TotalPrice, ueeshop_config.currency)); //产品总价格
			$('.product_total_price .product_count').text(lang_obj.cart[$Unit].replace('%num%', $Count)); //产品总数量
			$('.product_total_price strong').text(ueeshop_config.currency_symbols+$('html').currencyFormat(($TotalPrice-$CutPrice).toFixed(2), ueeshop_config.currency));
			$('.cartFrom input[name=CartProductPrice]').val($TotalPrice);
			$('.cartFrom input[name=DiscountPrice]').val($CutPrice);
			if($('.total_box').length && $CutPrice>0){ //购物车列表页 优惠栏目
				$('.cutprice_box, .total_box').show();
			}else{
				$('.cutprice_box, .total_box').hide();
			}
			if($LowPrice>0 && ($TotalPrice-$CutPrice)<$LowPrice){ //最低消费界限判断
				$Difference=$LowPrice-($TotalPrice-$CutPrice);
			}
			$('.button_info .tips .price_1').text(ueeshop_config.currency_symbols+$('html').currencyFormat($Difference, ueeshop_config.currency)); //距离最低消费金额相差
			if($('.button_info .tips').length && $Difference>0){ //购物车列表页 最低消费栏目
				$('.button_info .tips').show();
			}else{
				$('.button_info .tips').hide();
			}
		},
		
		quantity_change:function(Obj, Type){
			//购物车列表 产品数量改变
			if(Obj.find('input[name=Qty\\[\\]]').attr('disabled')=='disabled'){ //禁止执行
				return false;
			}
			var $Qty	= Math.abs(parseInt(Obj.find('input[name=Qty\\[\\]]').val())),
				$Start	= Obj.find('.prod_quantity').attr('start'),
				$sQty	= Obj.find("input[name=S_Qty\\[\\]]").val(),
				$CId	= Obj.find("input[name=CId\\[\\]]").val(),
				$ProId	= Obj.find("input[name=ProId\\[\\]]").val(),
				$Data	= '',
				$CIdStr	= '',
				$MinTips= 0;
			if(isNaN($Qty)){
				$Qty=1;
			}else{
				if(Type==1){ //加
					$Qty+=1;
				}else if(Type==-1){ //减
					$Qty-=1;
				}
				!$Qty && ($Qty=1) && ($MinTips=1);
				$Qty<$Start && ($Qty=$Start) && ($MinTips=1);
			}
			Obj.find('input[name=Qty\\[\\]]').val($Qty);
			if($MinTips==1){ //低过起订量
				global_obj.win_alert_auto_close(lang_obj.products.warning_MOQ);
			}
			if($Qty==$sQty){
				return;
			}
			cart_obj.cart_init.data_posting(true, lang_obj.cart.processing);
			if($('.cartFrom .itemFrom input[name=select]:checked').length){ //已选的产品
				$CIdStr='0';
				$('.cartFrom .itemFrom input[name=select]:checked').each(function(){
					$CIdStr+=','+$(this).val();
				});
			}
			$Data={'Qty':$Qty, 'CId':$CId, 'ProId':$ProId, 'CIdAry':$CIdStr};
			cart_obj.cart_init.modify_quantity(Obj, $Data);
		},
		
		modify_quantity:function(Obj, Data){
			//购物车列表 产品数量改变执行函数
			var $sQty		= Obj.find("input[name=S_Qty\\[\\]]").val(),
				$IsBuyNow	= $('#lib_cart').hasClass('buynow_content')?1:0,
				$MaxTips	= 0,
				$QtyTips	= 0;
			$.post('/?do_action=cart.modify&BuyNow='+$IsBuyNow+'&t='+Math.random(), Data, function(data){
				if(data.ret==1 || data.ret==2){
					if(data.ret==2) window.location.reload();
					if(Obj.find("input[name=Qty\\[\\]]").val()!=data.msg.qty){ //高过库存
						$MaxTips=1;
						$QtyTips=data.msg.qty;
					}
					Obj.find("input[name=Qty\\[\\]]").val(data.msg.qty);
					Obj.find("input[name=S_Qty\\[\\]]").val(data.msg.qty);
					for(k in data.msg.price){
						$(".itemFrom tbody tr[cid="+k+"] .prod_price p").text(ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.price[k], ueeshop_config.currency));
						$(".itemFrom tbody tr[cid="+k+"] .prod_operate p").text(ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.amount[k], ueeshop_config.currency)).attr('price', data.msg.amount[k]);
					}
					cart_obj.cart_init.price_change({ //购物车列表的价格控制
						'Count'		: data.msg.total_count,
						'TotalPrice': data.msg.total_price,
						'CutPrice'	: data.msg.cutprice
					});
					if(data.msg.FullCondition[0]==1){
						$('.fullcoupon').show().html(data.msg.FullCondition[1]);
					}else{
						$('.fullcoupon').hide();
					}
					if($IsBuyNow==1){
						//BuyNow页面
						$ShipObj=$('.information_address .address_list .item:eq(0) input[name=shipping_address_id]');
						if($ShipObj.length){
							cart_obj.cart_init.get_shipping_method_from_country($ShipObj.attr('data-cid'));
						}
						if($('#total_weight').length) $('#total_weight').text(data.msg.total_weight.toFixed(3));
						//优惠券处理 消费条件不足
						if(parseInt(data.msg.IsCoupon)==0){
							$('input[name=order_coupon_code]').val('').attr('cutprice', '0.00');
							$('.coupon_box .code_input').show(200).find('input').val('');
							$('.coupon_box .code_valid').hide().find('strong').text('');
							$('#CouponCharge').hide();
							global_obj.new_win_alert(lang_obj.cart.coupon_price_tips);
						}
						//价格计算初始化
						var $Amount		= parseFloat(data.msg.total_price), //产品总价
							$UserRatio	= parseInt($('#PlaceOrderFrom').attr('userRatio')), //会员优惠折扣比率
							$UserPrice	= $Amount-($Amount*($UserRatio/100)),
							$Count		= parseInt(data.msg.total_count),
							$Unit		= $Count>1?'itemsCount':'itemCount';
						$('#PlaceOrderFrom').attr('amountPrice', $Amount);
						$('#ot_weight').text(parseFloat(data.msg.total_weight).toFixed(3));
						$('#ot_subtotal').text($('html').currencyFormat($Amount.toFixed(2), ueeshop_config.currency));
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
						//订单各项价格的计算
						cart_obj.cart_init.order_price_charge({
							'Amount'		: $('#PlaceOrderFrom').attr('amountPrice'), //产品总价
							'UserPrice'		: $UserPrice, //会员优惠
							'DiscountPrice'	: data.msg.cutprice, //全场满减
							'CutPrice'		: $('input[name=order_coupon_code]').attr('cutprice'), //优惠券优惠
							'ShippingPrice'	: shipPrice, //快递运费
							'InsurancePrice': insurancePrice, //快递保险费
							'Fee'			: $('#ot_fee').attr('data-fee'), //手续费
							'Affix'			: $('#ot_fee').attr('data-affix'), //手续附加费
						});
						$('.order_summary .total .product_count').text(lang_obj.cart[$Unit].replace('%num%', $Count)); //产品总数量
						$('.order_summary .total strong').text(ueeshop_config.currency_symbols+$('html').currencyFormat(parseFloat(data.msg.total_price).toFixed(2), ueeshop_config.currency));
						//优惠券处理
						parseInt(data.msg.IsCoupon) && $('input[name=order_coupon_code]').val() && cart_obj.cart_init.ajax_get_coupon_info($('input[name=order_coupon_code]').val());
					}
				}else{
					if(Obj.find("input[name=Qty\\[\\]]").val()!=$sQty){ //高过库存
						$MaxTips=1;
						$QtyTips=$sQty;
					}
					Obj.find("input[name=Qty\\[\\]]").val($sQty);
				};
				if($MaxTips==1){ //高过库存
					$('#data_posting').remove();
					global_obj.win_alert_auto_close(lang_obj.products.warning_stock.replace('%num%', $QtyTips));
				}
				if($('input[name=order_shipping_address_cid]').length){
					var cid=parseInt($('input[name=order_shipping_address_cid]').val());
					if(cid<=0){
						cid=parseInt($('#country').val());
					}
					cart_obj.cart_init.get_shipping_method_from_country(cid);
				}
				cart_obj.cart_init.data_posting(false);
			}, 'json');
		},
		
		modify_select:function(){
			//购物车列表 勾选产品执行函数
			var $cutArr=$.evalJSON($('.cartFrom input[name=DiscountPrice]').attr('data-value')), //全场满减优惠 列表
				$cutType=parseInt($('.cartFrom input[name=DiscountPrice]').attr('data-type')), //全场满减优惠 类型
				$DiscountPrice=0,
				$TotalPrice=0,
				$Count=0;
			$('.cartFrom .itemFrom input[name=select]:checked').each(function(){
				$TotalPrice+=parseFloat($(this).parents('tr').find('.prod_operate p').attr('price'));
				$Count+=parseInt($(this).parents('tr').find("input[name=Qty\\[\\]]").val());
			});
			if($cutArr){ //计算全场满减优惠
				for(k in $cutArr){
					if($TotalPrice<k) break;
					$DiscountPrice=$cutType==1?$cutArr[k][1]:($TotalPrice*(100-$cutArr[k][0])/100);
				}
			}
			cart_obj.cart_init.price_change({ //购物车列表的价格控制
				'Count'		: $Count,
				'TotalPrice': $TotalPrice.toFixed(2),
				'CutPrice'	: $DiscountPrice
			});
		},
		
		attribute_check_stock:function(){
			//购物车列表 检查产品属性的库存情况
			var attr_len=$('ul.attributes li').length,
				ext_attr=$.evalJSON($('#ext_attr').val()), //扩展属性
				$attrStock=parseInt($('#attrStock').val());
			
			if($attrStock){ //开启了0是库存为空的设定
				var ext_ary=new Object, ary=new Object, cur, stock_ary=new Object;
				for(k in ext_attr){
					ary=k.split('_');
					for(k2 in ary){
						if(!stock_ary[ary[k2]]) stock_ary[ary[k2]]=0;
					}
					if(ext_attr[k][1]>0){
						for(k2 in ary){
							if(ary.length!=attr_len) continue;
							stock_ary[ary[k2]]+=1;
						}
					}
				}
				for(k in stock_ary){
					if(stock_ary[k]<1){
						if($('option[value="'+k+'"]').length) $('option[value="'+k+'"]').addClass('hide hide_fixed').get(0).disabled=true;
					}else{
						if($('option[value="'+k+'"]').length) $('option[value="'+k+'"]').removeClass('hide hide_fixed').get(0).disabled=false;
					}
				}
			}
		},
		
		attribute_edit:function(){
			//购物车列表 产品属性的处理函数
			var VId, attr_id, attr_ary=new Object,
				attr_hide=$('#attr_hide'),
				attr_len=$('ul.attributes li').length,
				ext_attr=$.evalJSON($('#ext_attr').val()),//扩展属性
				$attrStock=parseInt($('#attrStock').val()),
				$IsCombination=$('ul.attributes').attr('data-combination');//是否开启规格组合
			
			$('.itemFrom .prod_edit .attributes').on('change', 'select', function(){//选择属性下拉
				VId=$(this).val();
				attr_id=$(this).attr('attr');
				if(attr_hide.val() && attr_hide.val()!='[]'){
					attr_ary=$.evalJSON(attr_hide.val());
				}
				if(VId){
					attr_ary[attr_id]=VId;
				}else{//选择默认选项，清除对应ID
					delete attr_ary[attr_id];
				}
				attr_hide.val($.toJSON(attr_ary));
				
				var i=0, cur_attr='';
				for(k in attr_ary){
					cur_attr+=(i?'_':'')+attr_ary[k];
					++i;
				}
				
				if($attrStock && $IsCombination==1){
					if(attr_hide.val()=='[]' || attr_hide.val()=='{}'){//组合属性都属于默认选项
						cart_obj.cart_init.attribute_check_stock(); //检查当前所有属性库存的情况
					}else if(ext_attr && ext_attr!='[]'){//判断组合属性库存状态
						var select_ary=new Array, i=-1, ext_ary=new Object, ary=new Object, cur, no_stock_ary=new Object;
						for(k in attr_ary){
							select_ary[++i]=attr_ary[k];
						}
						if(select_ary.length == attr_len-1){ //勾选数 比 属性总数 少一个
							var no_attrid=0, attrid=0, _select_ary, key;
							$('ul.attributes li').each(function(){
								attrid=$(this).children('select').attr('attr');
								if(!attr_ary[attrid]){
									no_attrid=attrid; //没有勾选的属性ID
								}
							});
							$('#attr_'+no_attrid).find('option:gt(0)').each(function(){
								value=$(this).attr('value');
								_select_ary=new Array;
								for(k in select_ary){
									_select_ary[k]=select_ary[k];
								}
								_select_ary[select_ary.length]=value;
								_select_ary.sort(function(a, b){
									if(a.indexOf('Ov:')!=-1 || b.indexOf('Ov:')!=-1){
										a=99999999;
									}else if(b.indexOf('Ov:')!=-1){
										b=99999999;
									}
									return a - b;
								});
								key=_select_ary.join('_');
								if(ext_attr[key][1]==0){
									if($('option[value="'+value+'"]').length) $('option[value="'+value+'"]').addClass('hide').get(0).disabled=true;
								}else{
									if($('option[value="'+value+'"]').length) $('option[value="'+value+'"]').removeClass('hide').get(0).disabled=false;
								}
								if(VId==''){ //取消操作
									$('ul.attributes li').each(function(){
										if($(this).children('select').attr('attr')!=attr_id && $(this).find('option.hide').length){
											$(this).find('option.hide').not('.hide_fixed').removeClass('hide').get(0).disabled=false;
										}
									});
								}
							});
						}else if(select_ary.length == attr_len && attr_len!=1){ //勾选数 跟 属性总数 一致
							for(k in ext_attr){
								ary=k.split('_');
								for(k2 in ary){
									if(!no_stock_ary[ary[k2]]) no_stock_ary[ary[k2]]=0;
								}
								cur=0;
								for(k2 in select_ary){
									if(global_obj.in_array(select_ary[k2], ary) && ary.length==attr_len){ //找出包含自身的关联项数据，不一致的属性数量数据也排除掉
										++cur;
									}
								}
								if(cur && cur>=(select_ary.length-1) && select_ary.length==attr_len){ //“数值里已有的选项数量”跟“已勾选的选项数量”一致
									if(ext_attr[k][1]==0){
										for(k2 in ary){
											if(global_obj.in_array(ary[k2], select_ary)) continue;
											if(!no_stock_ary[ary[k2]]){
												no_stock_ary[ary[k2]]=1;
											}else{
												no_stock_ary[ary[k2]]+=1;
											}
										}
									}
								}
							}
							for(k in no_stock_ary){
								if(!global_obj.in_array(k, select_ary) && no_stock_ary[k]>0){
									if($('option[value="'+k+'"]').length) $('option[value="'+k+'"]').addClass('hide').get(0).disabled=true;
								}else{
									if($('option[value="'+k+'"]').length) $('option[value="'+k+'"]').removeClass('hide').get(0).disabled=false;
								}
							}
						}else{ //勾选数 大于 1
							$('ul.attributes li').each(function(){
								$(this).find('option.hide').not('.hide_fixed').removeClass('hide').attr('disabled', false);
							});
						}
					}
				}
			});
			
			if($('.prod_edit .attributes li select').length){
				$('.prod_edit .attributes li select').each(function(){
					$(this).find('option:selected').change();
				});
			}
			
			//确定提交
			$('.itemFrom .prod_edit .add').on('click', function(){
				var $IsBuyNow	= $('#lib_cart').hasClass('buynow_content')?1:0,
					$Remark		= $(this).parents('.prod_edit').find('textarea').val();
					$Data		= '',
					$Attr		= Object(),
					$CIdStr		= '';
				$(this).parents('.prod_edit').find('select').each(function(){ //按照下拉框的排列顺序
					$Attr['_'+$(this).attr('attr')]=$(this).val();
				});
				if($('.itemFrom input[name=select]:checked').length){ //已选的产品
					$CIdStr='0';
					$('.itemFrom input[name=select]:checked').each(function(){
						$CIdStr+=','+$(this).val();
					});
				}
				$Data={'CId':$('#CId').val(), 'ProId':$('#ProId').val(), 'Attr':$Attr, 'CIdAry':$CIdStr, 'Remark':$Remark};
				$.post('/?do_action=cart.modify_attribute&BuyNow='+$IsBuyNow+'&t='+Math.random(), $Data, function(data){
					if(data.ret==1){ //更改属性 和 备注
						var cutprice	= $('.cutprice_p').length?parseFloat($('.cutprice_p').attr('price')):0,
							tr			= $('input[name=CId\\[\\]][value='+data.msg.CId+']').parents('tr'),
							attr		= $.evalJSON(data.msg.Property),
							qty			= parseInt(data.msg.qty),
							remark		= $.trim(data.msg.Remark);
						for(i in attr){
							tr.find('.prod_info_detail .prod_attr>p[class="attr_'+i+'"]').text((i=='Overseas'?lang_obj.products.ships_from:i)+': '+attr[i]);
						}
						tr.find('.prod_info_detail dt img').attr('src', data.msg.PicPath);
						tr.find("input[name=Qty\\[\\]]").val(qty);
						tr.find("input[name=S_Qty\\[\\]]").val(qty);
						if(remark){
							tr.find('.prod_info_detail p.remark').show().find('span').text(remark);
						}else{
							tr.find('.prod_info_detail p.remark').hide().find('span').text('');
						}
						
						for(k in data.msg.itemprice){
							$(".itemFrom tbody tr[cid="+k+"] .prod_price p").text(ueeshop_config.currency_symbols+$('html').currencyFormat(parseFloat(data.msg.itemprice[k]).toFixed(2), ueeshop_config.currency));
							$(".itemFrom tbody tr[cid="+k+"] .prod_operate p").text(ueeshop_config.currency_symbols+$('html').currencyFormat(parseFloat(data.msg.amount[k]).toFixed(2), ueeshop_config.currency)).attr('price', data.msg.amount[k]);
						}
						
						cart_obj.cart_init.price_change({ //购物车列表的价格控制
							'Count'		: data.msg.total_count,
							'TotalPrice': data.msg.total_price,
							'CutPrice'	: data.msg.cutprice
						});
						if($IsBuyNow==1){
							//BuyNow页面
							$ShipObj=$('.information_address .address_list .item:eq(0) input[name=shipping_address_id]');
							if($ShipObj.length){
								cart_obj.cart_init.get_shipping_method_from_country($ShipObj.attr('data-cid'));
							}
							if($('#total_weight').length) $('#total_weight').text(data.msg.total_weight.toFixed(3));
							//优惠券处理 消费条件不足
							if(parseInt(data.msg.IsCoupon)==0){
								$('input[name=order_coupon_code]').val('').attr('cutprice', '0.00');
								$('.coupon_box .code_input').show(200).find('input').val('');
								$('.coupon_box .code_valid').hide().find('strong').text('');
								$('#CouponCharge').hide();
								global_obj.new_win_alert(lang_obj.cart.coupon_price_tips);
							}
							//价格计算初始化
							var $Amount		= parseFloat(data.msg.total_price), //产品总价
								$UserRatio	= parseInt($('#PlaceOrderFrom').attr('userRatio')), //会员优惠折扣比率
								$UserPrice	= $Amount-($Amount*($UserRatio/100)),
								$Count		= parseInt(data.msg.total_count),
								$Unit		= $Count>1?'itemsCount':'itemCount';
							$('#PlaceOrderFrom').attr('amountPrice', $Amount);
							$('#ot_weight').text(parseFloat(data.msg.total_weight).toFixed(3));
							$('#ot_subtotal').text($('html').currencyFormat($Amount.toFixed(2), ueeshop_config.currency));
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
							//订单各项价格的计算
							cart_obj.cart_init.order_price_charge({
								'Amount'		: $('#PlaceOrderFrom').attr('amountPrice'), //产品总价
								'UserPrice'		: $UserPrice, //会员优惠
								'DiscountPrice'	: data.msg.cutprice, //全场满减
								'CutPrice'		: $('input[name=order_coupon_code]').attr('cutprice'), //优惠券优惠
								'ShippingPrice'	: shipPrice, //快递运费
								'InsurancePrice': insurancePrice, //快递保险费
								'Fee'			: $('#ot_fee').attr('data-fee'), //手续费
								'Affix'			: $('#ot_fee').attr('data-affix'), //手续附加费
							});
							$('.order_summary .total .product_count').text(lang_obj.cart[$Unit].replace('%num%', $Count)); //产品总数量
							$('.order_summary .total strong').text(ueeshop_config.currency_symbols+$('html').currencyFormat(parseFloat(data.msg.total_price).toFixed(2), ueeshop_config.currency));
							//优惠券处理
							parseInt(data.msg.IsCoupon) && $('input[name=order_coupon_code]').val() && cart_obj.cart_init.ajax_get_coupon_info($('input[name=order_coupon_code]').val());
						}
					}else if(data.ret==2){ //只改备注
						var tr			= $('input[name=CId\\[\\]][value='+data.msg.CId+']').parents('tr'),
							remark		= $.trim(data.msg.Remark);
						if(remark){
							tr.find('.prod_info_detail p.remark').show().find('span').text(remark);
						}else{
							tr.find('.prod_info_detail p.remark').hide().find('span').text('');
						}
					};
					$('.prod_edit').html('').hide();
				}, 'json');
			});
			
			//关闭页面
			$('.itemFrom .prod_edit .cancel').on('click', function(){
				$(this).parents('.prod_edit').html('').hide();
			});
		},
		
		get_shipping_method_from_country:function(CId){
			//下单页面 更改快递显示
			if(!CId){
				cart_obj.cart_init.set_shipping_method(1, -1, 0, '', 0);
				return false;
			}
			var dataVal="CId="+CId;
			
			/*if($('form input[name=order_products_info]').val()){
				dataVal+=$('form input[name=shipping_method_where]').val();
				dataVal+='&Attr='+$('form input[name=shipping_method_where]').attr('attr');
			}*/
			if($('input[name=order_cid]').val()) dataVal+='&order_cid='+$('input[name=order_cid]').val();
			
			$('#submitCart').hide();
			
			$.post('/?do_action=cart.get_shipping_methods', dataVal, function(data){
				if(data.ret==1){
					var rowObj, rowStr, j=0;
					for(OvId in data.msg.info){
						if(!$('.information_shipping .shipping[data-id='+OvId+']').length) continue;//没有这个海外仓选项
						rowStr='';
						j=1;
						for(i=0; i<data.msg.info[OvId].length; i++){
							rowObj=data.msg.info[OvId][i];
							if(parseFloat(rowObj.ShippingPrice)<0) continue;
							rowStr+='<li name="'+rowObj.Name.toUpperCase()+'"'+(++j%2==0?' class="odd"':'')+'>';
							rowStr+=	'<span class="name">';
							rowStr+=		'<input type="radio" name="_shipping_method['+OvId+']" value="'+rowObj.SId+'" price="'+ rowObj.ShippingPrice+'" insurance="'+rowObj.InsurancePrice+'" ShippingType="'+rowObj.type+'" cid="'+CId+'" />';
							if(rowObj.Logo){
								rowStr+='<img src="'+rowObj.Logo+'" alt="'+rowObj.Name+'" />';
							}
							rowStr+=		'<label>'+rowObj.Name+'</label>';
							
							if(rowObj.IsAPI>0){
								if(rowObj.ShippingPrice>0){
									rowStr+='<span class="price waiting"></span>';
								}else{
									rowStr+='<span class="price free_shipping">'+lang_obj.products.free_shipping+'</span>';
								}
							}else{
								if(rowObj.ShippingPrice>0){
									rowStr+='<span class="price">'+ueeshop_config.currency_symbols+$('html').currencyFormat(rowObj.ShippingPrice, ueeshop_config.currency)+'</span>';
								}else{
									rowStr+='<span class="price free_shipping">'+lang_obj.products.free_shipping+'</span>';
								}
							}
							
							rowStr+=	'</span>';
							rowStr+=	'<span class="brief" title="'+rowObj.Brief+'">'+rowObj.Brief+'</span>';
							rowStr+=	'<div class="clear"></div>';
							rowStr+='</li>';
							
							if(rowObj.IsAPI>0){ //使用API接口
								var $AId=$('input[name=order_shipping_address_aid]').val(),
									$CId=$('input[name=order_shipping_address_cid]').val();
								$('.information_shipping .shipping[data-id='+OvId+'] .shipping_method_list li[name="'+rowObj.Name.toUpperCase()+'"] input').attr('disabled', true);
								$.post('/?do_action=cart.ajax_get_api_info', 'OvId='+OvId+'&AId='+$AId+'&CId='+$CId+'&Name='+rowObj.Name.toUpperCase()+'&IsAPI='+rowObj.IsAPI+dataVal, function(data){
									var $apiObj=$('.information_shipping .shipping[data-id='+data.msg.OvId+'] .shipping_method_list li[name="'+data.msg.Name+'"]');
									if(data.ret==1){
										$apiObj.find('.price').removeClass('waiting').text(data.msg.Price>0 ? ueeshop_config.currency_symbols+$('html').currencyFormat(parseFloat(data.msg.Price)*ueeshop_config.currency_rate, ueeshop_config.currency) : lang_obj.products.free_shipping).parent().find('input').attr({'price':parseFloat(data.msg.Price)*ueeshop_config.currency_rate, 'disabled':false});
									}else{
										$Price=$apiObj.find('input').attr('price');
										$apiObj.find('.price').removeClass('waiting').text($Price>0 ? ueeshop_config.currency_symbols+$('html').currencyFormat($Price, ueeshop_config.currency) : lang_obj.products.free_shipping);
									}
									if($('input[name="order_shipping_api\\['+data.msg.IsAPI+'\\]"]').length){
										if(data.msg.Price>0) $('input[name="order_shipping_api\\['+data.msg.IsAPI+'\\]"]').val(data.msg.Price);
										else $('input[name="order_shipping_api\\['+data.msg.IsAPI+'\\]"]').remove();
									}else{
										data.msg.Price>0 && $('#PlaceOrderFrom').append('<input type="hidden" name="order_shipping_api['+data.msg.IsAPI+']" value="'+data.msg.Price+'" />');
									}
								}, 'json');
							}
						}
						$('.information_shipping .shipping[data-id='+OvId+'] .shipping_method_list').html(rowStr);
						if(rowStr==''){
							$('.information_shipping .shipping[data-id='+OvId+'] .shipping_method_list').html(''); //清空内容
							cart_obj.cart_init.set_shipping_method(OvId, -1, 0, '', 0);
						}else{
							$('.information_shipping .shipping[data-id='+OvId+'] .shipping_method_list li:eq(0)').click(); //默认点击第一个选项
						}
					}
					$('.information_shipping .shipping').not('.hide').each(function(){ //检查海外仓的快递数据是否存在
						OvId=$(this).attr('data-id');
						if(!data.msg.info[OvId]){ //这个海外仓没有相应的快递数据
							$('.information_shipping .shipping[data-id='+OvId+'] .shipping_method_list').html(''); //清空内容
							cart_obj.cart_init.set_shipping_method(OvId, -1, 0, '', 0);
						}
					});
				}else{
					$('.shipping_method_list').html(''); //清空内容
					cart_obj.cart_init.set_shipping_method(1, -1, 0, '', 0);
				}
				$('#submitCart').show();
			}, 'json');
		},
		
		set_shipping_method:function(OvId, SId, price, type, insurance){
			//下单页面 选择运费
			if(SId==-1){
				global_obj.new_win_alert(lang_obj.cart.no_delivery);
			}
			
			//运费记录
			var inputSId=$('input[name=order_shipping_method_sid]'),
				inputType=$('input[name=order_shipping_method_type]'),
				inputPrice=$('input[name=order_shipping_price]'),
				inputInsurance=$('input[name=order_shipping_insurance]'),
				inputSId_ary={}, inputType_ary={}, inputPrice_ary={}, inputInsurance_ary={}, inputInsurancePrice_ary={};
			
			inputSId.val()!='[]' && (inputSId_ary=$.evalJSON(inputSId.val()));
			inputSId_ary['OvId_'+OvId]=SId;
			inputSId.val($.toJSON(inputSId_ary));
	
			inputType.val()!='[]' && (inputType_ary=$.evalJSON(inputType.val()));
			inputType_ary['OvId_'+OvId]=type;
			inputType.val($.toJSON(inputType_ary));
			
			inputPrice.val()!='[]' && (inputPrice_ary=$.evalJSON(inputPrice.val()));
			inputPrice_ary['OvId_'+OvId]=price;
			inputPrice.val($.toJSON(inputPrice_ary));
			
			var shipPrice=0; //运费
			for(k in inputPrice_ary){
				shipPrice+=parseFloat(inputPrice_ary[k]);
			}
			
			//保险费记录
			var obj=$('.information_shipping .shipping[data-id='+OvId+']'),
				v=obj.find('.shipping_insurance').attr('checked')=='checked'?1:0;
			obj.find('.insurance .price em').text($('html').currencyFormat(insurance, ueeshop_config.currency));
			insurance=parseFloat(insurance);
			insurance=v==1?insurance:0;
			
			inputInsurance.val()!='[]' && (inputInsurance_ary=$.evalJSON(inputInsurance.val()));
			inputInsurance_ary['OvId_'+OvId]=v;
			inputInsurance.val($.toJSON(inputInsurance_ary));
			
			inputInsurance.attr('price')!='[]' && (inputInsurancePrice_ary=$.evalJSON(inputInsurance.attr('price')));
			inputInsurancePrice_ary['OvId_'+OvId]=insurance;
			inputInsurance.attr('price', $.toJSON(inputInsurancePrice_ary));
			if(SId==-1){
				inputSId.val('[]')
				inputType.val('[]')
				inputPrice.val('[]')
				inputInsurance.val('[]').attr('price', '[]');
			}
			var insurancePrice=0, insuranceShow=0;
			for(k in inputInsurance_ary){
				if(inputInsurance_ary[k]==1){
					insurancePrice+=parseFloat(inputInsurancePrice_ary[k]);
					insuranceShow=1;
				}
			};
			cart_obj.cart_init.show_shipping_insurance(insuranceShow);
			cart_obj.cart_init.show_shipping_info(OvId);
			
			//订单各项价格的计算
			cart_obj.cart_init.order_price_charge({
				'Amount'		: $('#PlaceOrderFrom').attr('amountPrice'), //产品总价
				'UserPrice'		: $('#PlaceOrderFrom').attr('userPrice'), //会员优惠
				'DiscountPrice'	: $('input[name=order_discount_price]').val(), //全场满减
				'CutPrice'		: $('input[name=order_coupon_code]').attr('cutprice'), //优惠券优惠
				'ShippingPrice'	: shipPrice, //快递运费
				'InsurancePrice': insurancePrice, //快递保险费
				'Fee'			: $('#ot_fee').attr('data-fee'), //手续费
				'Affix'			: $('#ot_fee').attr('data-affix'), //手续附加费
			});
		},
		
		return_payment_list:function(totalAmount, feePrice){
			//下单页面 判断支付下拉显示内容
			var minPrice=maxPrice=0;
			if(feePrice>0) totalAmount=parseFloat((totalAmount-feePrice).toFixed(2));
			$('.payment_row').each(function(){
				minPrice=parseFloat($(this).attr('min'));
				maxPrice=parseFloat($(this).attr('max'));
				if((!minPrice && !maxPrice) || (maxPrice?(totalAmount>=minPrice && totalAmount<=maxPrice):(totalAmount>=minPrice))){
					$(this).show();
				}else{
					$(this).hide();
				}
			});
		},
		
		show_shipping_info:function(OvId){
			//下单页面 快递信息的显示
			var $Obj=$('.information_shipping .shipping[data-id='+OvId+']'),
				$shipObj=$Obj.find('.title .shipping_info'),
				$radioObj=$Obj.find('input:radio:checked'),
				$Type=($Obj.find('.shipping_insurance').attr('checked')=='checked'?1:0),
				$Price=parseFloat($radioObj.attr('price')),
				$Insurance=parseFloat($radioObj.attr('insurance')),
				$sPrice=$Price+($Type==1?$Insurance:0);
			if($radioObj.length){ //快递信息存在
				$shipObj.find('.error').css('display', 'none');
				$shipObj.find('.name').text($radioObj.next('label').text());
				if($sPrice==0){
					$shipObj.find('.price').text(lang_obj.products.free_shipping).addClass('free_shipping');
				}else{
					$shipObj.find('.price').text(ueeshop_config.currency_symbols+$('html').currencyFormat($sPrice, ueeshop_config.currency)).removeClass('free_shipping');
				}
			}else{ //不存在
				$shipObj.find('.error').css('display', 'inline-block');
				$shipObj.find('.name').text('');
				$shipObj.find('.price').text('').removeClass('free_shipping');
			}
			
		},
		
		show_shipping_insurance:function(v){
			//下单页面 快递保险费的显示
			if(v==1) $('#ShippingInsuranceCombine').show().prev().hide();
			else $('#ShippingInsuranceCombine').hide().prev().show();
		},
		
		order_price_charge:function(Data){
			//下单页面 订单各项价格的计算
			var $Amount			= parseFloat(Data.Amount), //产品总价
				$UserPrice		= parseFloat(Data.UserPrice), //会员优惠
				$DiscountPrice	= parseFloat(Data.DiscountPrice), //全场满减
				$CutPrice		= parseFloat(Data.CutPrice), //优惠券优惠
				$ShippingPrice	= parseFloat(Data.ShippingPrice), //快递运费
				$InsurancePrice	= parseFloat(Data.InsurancePrice), //快递保险费
				$Fee			= parseFloat(Data.Fee), //手续费
				$Affix			= parseFloat(Data.Affix), //手续附加费
				$TotalPrice		= 0, //最终价格
				$FeePrice		= 0; //付款手续费
			isNaN($Fee) && ($Fee=0);
			isNaN($Affix) && ($Affix=0);
			
			if($UserPrice && $DiscountPrice){
				if($DiscountPrice>$UserPrice) $UserPrice=0;
				else $DiscountPrice=0;
			}
			if($UserPrice){ //会员优惠
				$('#MemberCharge').show();
				$('#DiscountCharge').hide();
			}else if($DiscountPrice){ //全场满减
				$('#MemberCharge').hide();
				$('#DiscountCharge').show();
			}else{ //没有优惠
				$('#MemberCharge, #DiscountCharge').hide();
			}
			$('#PlaceOrderFrom').attr('userPrice', $UserPrice.toFixed(2));
			$('input[name=order_discount_price]').val($DiscountPrice);
			$TotalPrice=$Amount-$UserPrice-$CutPrice-$DiscountPrice+$ShippingPrice+$InsurancePrice; //最终价格
			$FeePrice=parseFloat($TotalPrice*($Fee/100))+$Affix; //付款手续费
			$FeePrice<0 && ($FeePrice=0);
			$('#ot_user').text($('html').currencyFormat($UserPrice.toFixed(2), ueeshop_config.currency));
			$('#ot_shipping').text($('html').currencyFormat($ShippingPrice.toFixed(2), ueeshop_config.currency));
			$('#ot_combine_shippnig_insurance').text($('html').currencyFormat(($ShippingPrice+$InsurancePrice).toFixed(2), ueeshop_config.currency));
			$('#ot_coupon').text($('html').currencyFormat($CutPrice.toFixed(2), ueeshop_config.currency));
			$('#ot_subtotal_discount').text($('html').currencyFormat($DiscountPrice.toFixed(2), ueeshop_config.currency));
			$('#ot_fee').text($('html').currencyFormat($FeePrice.toFixed(2), ueeshop_config.currency)).attr({'data-fee':$Fee, 'data-affix':$Affix});
			$('#ot_total').text($('html').currencyFormat(($TotalPrice+$FeePrice).toFixed(2), ueeshop_config.currency));
			
			if($Fee>0 || $Affix>0){ //判断“手续费栏目”是否显示
				$('#ServiceCharge').show();
			}else{
				$('#ServiceCharge').hide();
			}
			cart_obj.cart_init.return_payment_list($TotalPrice); //判断支付下拉显示内容
		},
		
		get_state_from_country:function(CId){
			//收货地址 国家和省份的显示
			$.ajax({
				url:"/account/",
				async:false,
				type:"POST",
				data:{"CId": CId, do_action:'user.select_country'},
				dataType:"json",
				success:function (data){
					if(data.ret==1){
						d=data.msg.contents;
						if(d==-1){
							$('#zoneId').css({'display':'none'}).find('select').attr('disabled', 'disabled').removeAttr('notnull');
							$('#state').css({'display':'block'}).find('input').removeAttr('disabled');
						}else{
							$('#zoneId').css({'display':'block'}).find('select').removeAttr('disabled').attr('notnull', '');
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
						$('#countryCode').val('+'+data.msg.code);
						if(data.msg.cid==30){
							$('#taxCode').css({'display':'block'}).find('select, input').removeAttr('disabled');
							$('#taxCode').find('input').attr('notnull', 'notnull');
							$('#tariffCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled');
							$('#tariffCode').find('input').removeAttr('notnull');
						}else if(data.msg.cid==211){
							$('#tariffCode').css({'display':'block'}).find('select, input').removeAttr('disabled');
							$('#tariffCode').find('input').attr('notnull', 'notnull');
							$('#taxCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled');
							$('#taxCode').find('input').removeAttr('notnull');
						}else{
							$('#taxCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled');
							$('#tariffCode').css({'display':'none'}).find('select, input').attr('disabled', 'disabled');
							$('#taxCode, #tariffCode').find('input').removeAttr('notnull');
						}
						return true;
					}
				}
			});
		},
		
		set_default_address:function(AId, NotUser){
			$.ajax({
				url:"/",
				async:false,
				type:'post',
				data:{'do_action':'user.get_addressbook', 'AId':AId, 'NotUser':NotUser},
				dataType:'json',
				success:function(data){
					if(data.ret==1){
						$('input[name=edit_address_id]').val(data.msg.address.AId);
						$('input[name=FirstName]').val(data.msg.address.FirstName);
						$('input[name=LastName]').val(data.msg.address.LastName);
						$('input[name=AddressLine1]').val(data.msg.address.AddressLine1);
						$('input[name=AddressLine2]').val(data.msg.address.AddressLine2);
						$('input[name=City]').val(data.msg.address.City);
						
						var index=$('select[name=country_id]').find('option[value='+data.msg.address.CId+']').eq(0).attr('selected', 'selected').index();
						$('#country_chzn a span').text(data.msg.country.Country);
						$('#country_chzn ul.chzn-results li.group-option').eq(index).addClass('result-selected');
						cart_obj.cart_init.get_state_from_country(data.msg.address.CId);
						if(data.msg.address.CId==30 || data.msg.address.CId==211){
							$('select[name=tax_code_type]').find('option[value='+data.msg.address.CodeOption+']').attr('selected', 'selected');
							$('input[name=tax_code_value]').attr('maxlength', (data.msg.address.CodeOption==1?11:14)).val(data.msg.address.TaxCode);
						}
						
						if(data.msg.country.HasState==1){
							$('#zoneId div a span').text(data.msg.address.StateName);
							var sindex=$('select[name=Province]').find('option[value='+data.msg.address.SId+']').attr('selected', 'selected').index();
							$('#zoneId ul.chzn-results li.group-option').eq(sindex-1).addClass('result-selected');
						}else{
							$('input[name=State]').val(data.msg.address.State);
						}
						
						$('input[name=ZipCode]').val(data.msg.address.ZipCode);
						$('input[name=CountryCode]').val('+'+data.msg.address.CountryCode);
						$('input[name=PhoneNumber]').val(data.msg.address.PhoneNumber);
						
					}else if(data.ret==2){
						$('input[name=edit_address_id], input[name=FirstName], input[name=LastName], input[name=AddressLine1], input[name=AddressLine2], input[name=City], input[name=tax_code_value], input[name=State], input[name=ZipCode], input[name=CountryCode], input[name=PhoneNumber]').val('');
	
						var index=$('select[name=country_id]').find('option[value='+data.msg.country.CId+']').eq(0).attr('selected', 'selected').index();
						$('#country_chzn a span').text(data.msg.country.Country);
						$('#country_chzn ul.chzn-results li.group-option').eq(index).addClass('result-selected');
						cart_obj.cart_init.get_state_from_country(data.msg.country.CId);
					}else{
						global_obj.new_win_alert(data.msg.error);
					}
					
					$('#ShipAddrFrom .input_box_txt').each(function(){
						if($.trim($(this).val())!=''){
							$(this).parent().addClass('filled');
						}else{
							$(this).parent().removeClass('filled');
						}
					});
				}
			});
		},
		
		checkout_no_login:function(json){
			$.post('/?do_action=cart.set_no_login_address', json?json:$('.user_address_form').serialize(), function(data){
				if(data.ret==1){
					$('#PlaceOrderFrom').attr('nologin', data.msg.info);
					var Html='';
					Html+=	'<input type="radio" name="shipping_address_id" id="address_0" value="0" data-cid="'+data.msg.v.CId+'" checked />';
					Html+=	'<p class="clearfix"><strong>'+data.msg.v.FirstName+' '+data.msg.v.LastName+'</strong><a href="javascript:;" class="edit_address_info btn_global sys_bg_button">'+lang_obj.global.edit+'</a></p>';
					Html+=	'<p class="address_line">'+data.msg.v.AddressLine1+' '+(data.msg.v.AddressLine2?data.msg.v.AddressLine2+'':'')+'</p>';
					Html+=	'<p>'+data.msg.v.City+', '+(data.msg.v.StateName?data.msg.v.StateName:data.msg.v.State)+' '+data.msg.v.Country+' ('+data.msg.v.ZipCode+')</p>';
					Html+=	'<p>'+data.msg.v.CountryCode+' '+data.msg.v.PhoneNumber+'</p>';
					
					if($('.information_address .address_list .item:eq(0)').length){ //存在
						$('.information_address .address_list .item:eq(0)').html(Html);
					}else{ //不存在
						$('.information_address .address_list').html('<div class="item odd current">'+Html+'</div<');
					}
					$('.address_default').html(Html).show(500);
					$('#ShipAddrFrom').slideUp(500);
					$('input[name=order_shipping_address_aid]').val(0);
					$('input[name=order_shipping_address_cid]').val(data.msg.v.CId);
					cart_obj.cart_init.get_shipping_method_from_country(data.msg.v.CId);
				}
			}, 'json');
		},
		
		data_posting:function(display, tips){
			if(display){
				var $Width=$(window).width(),
					$SideObj=$('.list_information, .information_product'),
					$SideWidth=$SideObj.width(),
					$SideLeft=$SideObj.offset().left,
					$BoxLeft=0;
				$BoxLeft=$Width/2-(206/2);
				$('.list_information, .information_product').prepend('<div id="data_posting"><img src="/static/ico/data_posting.gif" width="16" height="16" align="absmiddle" />&nbsp;&nbsp;'+tips+'</div>');
				$('#data_posting').css({'width':'188px', 'height':'24px', 'line-height':'24px', 'padding':'0 8px', 'overflow':'hidden', 'background-color':'#ddd', 'border':'1px #bbb solid', 'position':'fixed', 'top':'40%', 'left':$BoxLeft, 'z-index':10001});
			}else{
				setTimeout('$("#data_posting").remove();', 500);
			}
		},
		
		payment_ready:function(OId){
			var $Obj=$('#payment_ready'),
				$WinWidth=$(window).width(),
				$BoxWidth=$Obj.outerWidth();
			global_obj.div_mask();
			$Obj.show().css({'left':($WinWidth/2-$BoxWidth/2)});
			setTimeout(function(){
				$.post('/?do_action=cart.payment_ready', {'OId':OId}, function(data){
					if(data.ret==1){ //线上付款
						window.top.location.href='/cart/payment/'+OId+'.html?utm_nooverride=1';
					}else if(data.ret==2){ //线下付款
						window.top.location.href='/cart/complete/'+OId+'.html?utm_nooverride=1';
					}else if(data.ret==0){
						window.top.location.href='/cart/success/'+OId+'.html';
					}else{
						alert('出错!');
					}
				}, 'json');
			}, 1000);
		}
	},
	
	list_init:function(){
		//更改购物车列表的产品数量
		$('.itemFrom .quantity_box .cut, .itemFrom .quantity_box .add').click(function(){
			var $Parent=$(this).parents('tr'),
				$Type=$(this).hasClass('add')?1:-1;
			cart_obj.cart_init.quantity_change($Parent, $Type);
		});
		$('.itemFrom .quantity_box input[name=Qty\\[\\]]').bind({
			'keyup paste':function(){
				p=/[^\d]/g;
				$(this).val($(this).val().replace(p, ''));
			},
			'blur':function(){
				cart_obj.cart_init.quantity_change($(this).parents('tr'), 0);
			}
		});
		/*$(document).on('click', function(e){
			if($(e.target).attr('name')!='Qty[]'){
				$('.itemFrom .quantity_box input[name=Qty\\[\\]]').blur();
			}
		});*/
		
		//删除购物车按钮
		$('.operate_delete').click(function(){
			var $Url=$(this).attr('data-url');
			global_obj.new_win_alert(lang_obj.global.del_confirm, function(){
				window.top.location.href=$Url;
			}, 'confirm');
			return false;
		});
		
		//批量删除购物车按钮
		$('.btn_remove').click(function(){
			var $CIdStr='0';
			if($('.itemFrom input[name=select]:checked').length){
				$('.itemFrom input[name=select]:checked').each(function(){
					$CIdStr+=','+$(this).val();
				});
			}else{
				global_obj.new_win_alert(lang_obj.cart.batch_remove_select);
				return false;
			}
			global_obj.new_win_alert(lang_obj.global.del_confirm, function(){
				$.post('/?do_action=cart.bacth_remove&t='+Math.random(), {'cid_list':$CIdStr}, function(data){
					if(data.ret==1){
						global_obj.new_win_alert(lang_obj.cart.batch_remove_success, function(){ window.top.location.href='/cart/' }, '', undefined, '');
					}else{
						global_obj.new_win_alert(lang_obj.cart.batch_remove_error);
					}
				}, 'json');
			}, 'confirm');
			return false;
		});
		
		//清空全部问题产品按钮
		$('.btn_remove_invalid').click(function(){
			var $CIdStr='0';
			if($('.itemFrom input[name=select].null').length){
				$('.itemFrom input[name=select].null').each(function(){
					$CIdStr+=','+$(this).val();
				});
			}else{
				global_obj.new_win_alert(lang_obj.cart.batch_remove_select);
				return false;
			}
			$.post('/?do_action=cart.bacth_remove&t='+Math.random(), {'cid_list':$CIdStr}, function(data){
				if(data.ret==1){
					global_obj.new_win_alert(lang_obj.cart.batch_remove_success, function(){ window.top.location.href='/cart/' }, '', undefined, '');
				}else{
					global_obj.new_win_alert(lang_obj.cart.batch_remove_error);
				}
			}, 'json');
			return false;
		});
		
		//返回购物按钮
		$('.btn_continue').click(function(){
			window.top.location.href='/';
		});
		
		//收藏产品
		$('.operate_wish').click(function(){
			var $Obj=$(this),
				$ProId=$(this).attr('data-proid');
			if($Obj.hasClass('current')){ //取消收藏
				$.get('/account/favorite/remove'+$ProId+'.html', {isjson:1}, function(data){
					if(data.ret==1){ //添加收藏
						$('.operate_wish[data-proid='+$ProId+']').removeClass('current');
					}
				}, 'json');
			}else{ //添加收藏
				$.get('/account/favorite/add'+$ProId+'.html', '', function(data){
					if(data.ret==1){ //添加收藏
						$('.operate_wish[data-proid='+$ProId+']').addClass('current');
						if(parseInt(ueeshop_config.FbPixelOpen)==1 && data.ret==1){//收藏成功
							//When a product is added to a wishlist.
							fbq('track', 'AddToWishlist', {content_ids:'['+data.msg.Num+']', content_name:data.msg.Name, currency:data.msg.Currency,value:'0.00'});
						}
					}else if(data.ret==0){ //已收藏
						$('.operate_wish[data-proid='+$ProId+']').removeClass('current');
					}else{
						user_obj.set_form_sign_in('', '', 1);
						$('form[name=signin_form]').append('<input type="hidden" name="comeback" value="global_obj.div_mask(1);$(\'#signin_module\').remove();$(\'.add_favorite[data='+ProId+']\').click();" />');
					}
				}, 'json');
			}
		});
		
		//勾选事件：主动勾选
		$('.itemFrom .btn_checkbox').on('click', function(){
			var $checked=true,
				$obj=$(this).next('input');
			if($obj.attr('disabled')=='disabled'){ //禁止勾选
				return false;
			}
			if($(this).next('input:checked').length){
				$obj.attr('checked', false).removeAttr('checked');
				$(this).removeClass('current');
				$checked=false;
			}else{
				$obj.attr('checked', true);
				$(this).addClass('current');
			}
			if($obj.attr('name')=='select_all'){ //全选
				$('.itemFrom input[name=select]').not('.null').each(function(index, element) {
					if($checked==true){
						$(element).attr('checked', true);
						$(element).prev().addClass('current');
					}else{
						$(element).attr('checked', false).removeAttr('checked');
						$(element).prev().removeClass('current');
					}
				});
			}else{ //部分勾选
				if($('.itemFrom input[name=select]:checked').not('.null').length==$('.itemFrom input[name=select]').not('.null').length){
					$('.itemFrom input[name=select_all]').attr('checked', true);
					$('.itemFrom input[name=select_all]').prev().addClass('current');
				}else{
					$('.itemFrom input[name=select_all]').attr('checked', false).removeAttr('checked');
					$('.itemFrom input[name=select_all]').prev().removeClass('current');
				};
			}
			cart_obj.cart_init.modify_select();
			return false;
		});
		
		//勾选事件：全选
		$('.cartFrom .itemFrom').on('click', 'input[name=select_all]', function(){
			$('.cartFrom .itemFrom input[name=select]').not('.null').each(function(index, element) {
				$(element).get(0).checked=$('.cartFrom .itemFrom input[name=select_all]').get(0).checked?'checked':'';
            });
			cart_obj.cart_init.modify_select();
		}).on('click', 'input[name=select]', function(){ //部分勾选
			if($('.cartFrom .itemFrom input[name=select]:checked').not('.null').length==$('.cartFrom .itemFrom input[name=select]').not('.null').length){
				$('.cartFrom .itemFrom input[name=select_all]').get(0).checked='checked';
            }else{
				$('.cartFrom .itemFrom input[name=select_all]').get(0).checked='';
			};
			cart_obj.cart_init.modify_select();
		});
		
		//勾选事件：默认执行
		if(!$('#lib_cart').hasClass('buynow_content')){ //除了立即购买页面
			$('.cartFrom .itemFrom input:checkbox:not(.null)').each(function(){ //重新默认全部勾选
				if($(this).is(':checked')===false) $(this).prev().click();
				if($(this).is(':checked')===true && !$(this).prev().hasClass('current')) $(this).prev().addClass('current');
			});
		}
		
		//更新购物车产品属性
		$('.itemFrom tbody tr .prod_operate .operate_edit').click(function(){
			var $Obj	= $(this).parents('tr'),
				$QtyObj	= $Obj.find('.prod_quantity'),
				$CId	= $QtyObj.find('input[name=CId\\[\\]]').val(),
				$ProId	= $QtyObj.find('input[name=ProId\\[\\]]').val(),
				$Content= $Obj.find('.prod_edit'),
				$Height	= $Obj.height()-16,
				$Remark	= $Obj.find('.prod_info .remark>span').html(),
				$Data	= {"CId":$CId, "ProId":$ProId, "Remark":$Remark};
			$Obj.siblings().find('.prod_edit').html('').hide();
			$Content.removeAttr('style').show();
			$Content.loading();
			$('.loading_msg').css({'top':0, 'position':'initial', 'width':'auto', 'height':150, 'background-position':'center'});
			setTimeout(function(){
				$.ajax({
					url:'/ajax/cart_modify_attribute.html',
					async:false,
					type:'post',
					data:$Data,
					dataType:'html',
					success:function(result){
						if(result){
							$Content.html('').append(result).unloading();
							if($Content.outerHeight(true)<$Height){
								$Content.css('height', $Height);
							}
						}else{
							$Content.html('').append('<div class="blank25"></div><div id="loading_tips">'+lang_obj.seckill.no_products+'</div>').unloading();
						}
						cart_obj.cart_init.attribute_edit();
					}
				});
			}, 500);
		});
		
		//下一步 Checkout
		$('.btn_checkout').click(function(){
			$('.btn_checkout').addClass('btn_processing').text(lang_obj.cart.processing_str);
			if($('.itemFrom input[name=select_all]').get(0).checked && !$('.itemFrom input[name=select]').not(':checked').length){ //全选
				$.post('/?do_action=cart.check_low_consumption&t='+Math.random(), '', function(data){ //最低消费金额判断
					if(data.ret==1){ //符合
						setTimeout(function(){window.location.href='/cart/checkout.html'}, 1000);
					}else{ //不符合
						var tips=(lang_obj.cart.consumption).replace('%low_price%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.low_price, ueeshop_config.currency)).replace('%difference%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.difference, ueeshop_config.currency));
						global_obj.new_win_alert(tips, function(){ $('.btn_checkout').removeClass('btn_processing').text(lang_obj.cart.checkout_str) });
					}
				}, 'json');
			}else if($('.itemFrom input[name=select]:checked').length){ //部分已选
				var CId='0';
				$('.itemFrom input[name=select]:checked').each(function(){ CId+='.'+$(this).val() });
				$.post('/?do_action=cart.check_low_consumption&t='+Math.random(), {'CId':CId}, function(data){ //最低消费金额判断
					if(data.ret==1){ //符合
						setTimeout(function(){window.location.href='/cart/checkout.html?CId='+CId}, 1000);
					}else{ //不符合
						var tips=(lang_obj.cart.consumption).replace('%low_price%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.low_price, ueeshop_config.currency)).replace('%difference%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.difference, ueeshop_config.currency));
						global_obj.new_win_alert(tips, function(){ $('.btn_checkout').removeClass('btn_processing').text(lang_obj.cart.checkout_str) });
					}
				}, 'json');
			}else{
				global_obj.new_win_alert('Please select at least one item!', function(){ $('.btn_checkout').removeClass('btn_processing').text(lang_obj.cart.checkout_str) });
			}
		});
		
		//Paypal快捷支付
		$('.btn_paypal_checkout, .paypal_checkout_button').click(function(){
			var CId='0';
			$(this).blur().attr('disabled', 'disabled');
			if(ueeshop_config['TouristsShopping']==0 && ueeshop_config['UserId']==0){ //游客状态
				$(this).loginOrVisitors('', 1, function(){
					$('.btn_paypal_checkout, .paypal_checkout_button').removeAttr('disabled');
				}, 'global_obj.div_mask(1);$(\'#signin_module\').remove();cart_obj.cart_init.paypal_checkout_init();ueeshop_config[\'UserId\']=1;');
			}else{
				cart_obj.cart_init.paypal_checkout_init();
			}
			$(this).removeAttr('disabled');
			return false;
		});
		
		//底部的选项卡
		$('.cart_prod .title>a').on('click', function(){
			var $type=$(this).attr('data-type');
			$(this).addClass('FontColor').siblings().removeClass('FontColor');
			$(this).parents('.cart_prod').find('.pro_list').eq($type).show().siblings().hide();
		});
		$('.cart_prod .title>a:eq(0)').click();
		
		//右侧栏目
		var right_position=function(){
			var $ScrollTop=$(window).scrollTop(),
				$SideObj=$('.list_information'),
				$SideTop=$SideObj.offset().top,
				$SideHeight=$SideObj.outerHeight();
				$Obj=$('.list_summary'),
				$BoxHeight=$Obj.height(),
				$BoxLeft=$Obj.offset().left;
			if($ScrollTop>$SideTop){
				$Obj.css({'position':'fixed', 'top':0, 'left':$BoxLeft});
				if((($ScrollTop-$SideTop)+$BoxHeight)>$SideHeight){
					$Obj.css({'top':-($ScrollTop+$BoxHeight-$SideHeight-$SideTop)});
				}
			}else{
				$Obj.removeAttr('style');
			}
		}
		if($('.list_summary').length){
			$(window).scroll(function(){
				right_position();
			});
			$(document).ready(function(){
				right_position();
			});
		}
	},
	
	checkout_init:function(){
		//收货地址
		$('.information_address .address_list .item').click(function(){
			var $Input	= $(this).find('input'),
				$Value	= $Input.val(),
				$CId	= parseInt($Input.attr('data-cid'));
			$(this).addClass('current').siblings().removeClass('current');
			$Input.attr('checked', true);
			$('.address_default').html($(this).html()).show(500);
			$('.address_default .edit_address_info').addClass('btn_global sys_bg_button');
			$('.address_default input').attr({'checked':false, 'id':''});
			$('.address_list').hide();
			$Value>0 && $('input[name=order_shipping_address_aid]').val($Value);
			$CId>0 && $('input[name=order_shipping_address_cid]').val($CId);
			cart_obj.cart_init.get_shipping_method_from_country($CId);
		});
		if($('.information_address .address_list .item').size()>0){ //有收货地址信息
			$('.information_address .address_list .item:eq(0)').click(); //默认点击第一个
			$('#ShipAddrFrom').hide();
		}
		if($('.information_address .address_list .item').size()<2){ //收货地址选项小于2个，更多地址按钮隐藏起来
			$('.information_address .address_button i, #moreAddress').hide();
		}
		$('#addAddress').click(function(){
			cart_obj.cart_init.set_default_address(0);
			$('.address_default, .address_list, .address_button').hide();
			$('#ShipAddrFrom').slideDown(500);
			$('#ShipAddrFrom input[notnull], #ShipAddrFrom select[notnull]').removeClass('null');
			$('#ShipAddrFrom p.error').hide();
		});
		$('#moreAddress').click(function(){ //更多地址 or 收起
			var $Obj=$('.address_list');
			if($Obj.hasClass('address_show')){ //隐藏
				$(this).text(lang_obj.cart.moreAddress);
				$('.address_default').show(500);
				$Obj.removeClass('address_show').hide();
			}else{ //显示
				$(this).text(lang_obj.cart.foldAddress);
				$('.address_default').hide();
				$Obj.addClass('address_show').slideDown(500);
			}
			return false;
		});
		$('.information_address').delegate('.edit_address_info', 'click', function(){
			if($('.user_address_form input[name=typeAddr]').val()==0){ //会员状态
				var $AId=$(this).parents('.item').find('input').val();
				cart_obj.cart_init.set_default_address($AId);
			}else{ //非会员状态
				$('input[name=order_shipping_address_aid]').val(-1);
				cart_obj.cart_init.set_default_address(0, 1);
			}
			$('.address_default, .address_list, .address_button').hide();
			$('#ShipAddrFrom').slideDown(500);
			$('#ShipAddrFrom input[notnull], #ShipAddrFrom select[notnull]').removeClass('null');
			$('#ShipAddrFrom p.error').hide();
			return false;
		});
		$('#cancel_address').click(function(){
			$('.address_default, .address_button').show(500);
			$('#ShipAddrFrom').slideUp(500);
		});
		if(address_perfect==1){ //非会员 或者 缺失收货地址
			var $AId=0;
			$('input[name=order_shipping_address_aid]').val(-1);
			$('.address_default, .address_list, .address_button').hide();
			$('#ShipAddrFrom').slideDown(500);
			$('#cancel_address').hide();
			address_perfect_aid>0 && ($AId=address_perfect_aid);
			cart_obj.cart_init.set_default_address($AId); //大于0就是缺失收货地址 等于0就是非会员
			var CId=$('#country').find('option:selected').val();
			cart_obj.cart_init.get_shipping_method_from_country(CId);
		}
		
		//收货地址的编辑
		var address_rq_mark=true;
		$('.user_address_form').submit(function(){ return false; });
		$('#save_address').on('click', function(){
			if(address_rq_mark && !$('#save_address').hasClass('disabled')){
				var $notnull=$('.user_address_form input[notnull], .user_address_form select[notnull]'),
					$TypeAddr=parseInt($('.user_address_form input[name=typeAddr]').val())==1?1:0,
					$errorObj=new Object;
				$('#save_address').addClass('disabled');
				address_rq_mark=false;
				setTimeout(function(){
					var status=0;
					$notnull.each(function(){
						$errorObj=($(this).attr('name')=='PhoneNumber'?$(this).parent().parent().next('p.error'):$(this).parent().next('p.error'));
						if($.trim($(this).val())==''){
							$(this).addClass('null');
							$errorObj.text(lang_obj.user.address_tips.PleaseEnter.replace('%field%', $(this).attr('placeholder'))).show();
							status++;
							if(status==1){
								$('body,html').animate({scrollTop:$(this).offset().top-20}, 500);
							}
						}else{
							$(this).removeClass('null');
							$errorObj.hide();
						}
					});
					$('.user_address_form input[format][notnull]').each(function(){
						$errorObj=$(this).parent().next('p.error');
						$format=$(this).attr('format').split('|');
						if($format[0]=='Length' && $.trim($(this).val()).length!=parseInt($format[1])){
							$(this).addClass('null');
							$errorObj.text(lang_obj.format.length.replace('%num%', $format[1])).show();
							status++;
							if(status==1){
								$('body,html').animate({scrollTop:$(this).offset().top-20}, 500);
							}
						}else{
							$(this).removeClass('null');
							$errorObj.hide();
						}
					});
					if(status){ //检查表单
						address_rq_mark=true;
						$('#save_address').removeClass('disabled');
						return false;
					}
					if($TypeAddr==1){
						cart_obj.cart_init.checkout_no_login();
						$('.address_default').show(500);
					}else{
						$.post('/account/', $('.user_address_form').serialize()+'&do_action=user.addressbook_mod', function(data){
							if(data.ret==1){
								window.top.location.reload();
							}
						}, 'json');
					}
					address_rq_mark=true;
					$('#save_address').removeClass('disabled');
				}, 100);
			}
			return false;
		});
		$('.chzn-container-single .chzn-search').css('height', $('.chzn-container-single .chzn-search input').height());
		$('a.chzn-single').off().on('click', function(){
			$(this).parent().next('p.errorInfo').text('');
			if($(this).hasClass('chzn-single-with-drop')){
				$(this).blur().removeClass('chzn-single-with-drop').next().css({'left':'-9000px'}).parent().removeClass('chzn-container-active').css('z-index', '0').find('li.result-selected').removeClass('highlighted');
			}else{
				$(this).blur().addClass('chzn-single-with-drop').next().css({'left':'0', 'top':'41px'}).parent().addClass('chzn-container-active').css('z-index', '11').find('li.result-selected').addClass('highlighted');
				if(!$('#country_chzn li.group-result:eq(0)').next('li.group-option').length) $('#country_chzn li.group-result').hide();
			}
		});
		$('.chzn-results li.group-option').live('mouseover', function(){
			$(this).parent().find('li').removeClass('highlighted');
			$(this).addClass('highlighted');
		}).live('mouseout', function(){
			$(this).removeClass('highlighted');
		});
		$('#country_chzn li.group-option').click(function(){	//Select Country
			var obj		= $('#country_chzn li.group-option').removeClass('result-selected').index($(this)),
				s_cid	= $('select[name=country_id]').val();
			$(this).addClass('result-selected').parents('.chzn-drop').removeAttr('style').parent().removeClass('chzn-container-active').children('a').removeClass('chzn-single-with-drop').find('span').text($(this).text());
			$('#country option').eq(obj+1).attr('selected', 'selected').siblings().removeAttr('selected');
			var cid	= $('select[name=country_id]').val();
			(s_cid!=cid) && cart_obj.cart_init.get_state_from_country(cid);	//change country
		});
		$('#zoneId li.group-option').live('click', function(){
			var obj=$('#zoneId li.group-option').removeClass('result-selected').index($(this));
			$(this).addClass('result-selected').parents('.chzn-drop').removeAttr('style').parent().removeClass('chzn-container-active').children('a').removeClass('chzn-single-with-drop').find('span').text($(this).text());
			$('#zoneId select>option').eq(obj+1).attr('selected', 'selected').siblings().removeAttr('selected');
		});
		$(document).click(function(e){ 
			e	= window.event || e; // 兼容IE7
			obj	= $(e.srcElement || e.target);
			if(!$(obj).is("#country_chzn, #country_chzn *")){ 
				$('#country_chzn').removeClass('chzn-container-active').css('z-index', '0').children('a').blur().removeClass('chzn-single-with-drop').end().children('.chzn-drop').css({'left':'-9000px'}).find('input').val('').parent().next().find('.group-option').addClass('active-result');
			} 
			if(!$(obj).is("#zoneId .chzn-container, #zoneId .chzn-container *")){ 
				$('#zoneId .chzn-container').removeClass('chzn-container-active').css('z-index', '0').children('a').blur().removeClass('chzn-single-with-drop').end().children('.chzn-drop').css({'left':'-9000px'}).find('input').val('').parent().next().find('.group-option').addClass('active-result');
			} 
		});
		jQuery.expr[':'].Contains=function(a,i,m){
			return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase())>=0;
		};
		function filterList(input, list){ 
			$(input).change(function(){
				var filter=$(this).val();
				if(filter){
					$matches=$(list).find('li:Contains('+filter+')');
					$('li', list).not($matches).removeClass('active-result');
					$matches.addClass('active-result');
				}else {
					$(list).find('li').addClass('active-result');
				}
				return false;
			})
			.keyup(function(){
				$(this).change();
			});
		}
		filterList('#country_chzn .chzn-search input', $('#country_chzn .chzn-results'));
		filterList('#zoneId .chzn-search input', $('#zoneId .chzn-results'));
		
		//弹出提示
		$('.delivery_ins').each(function(){
			$('#main').tool_tips($(this), {position:'vertical', html:$(this).attr('content'), width:260});
		});
		
		//快递方式
		$('.information_shipping .shipping .title').click(function(){
			var $Obj=$(this).parent();
			if($Obj.hasClass('current')){ //隐藏
				$Obj.removeClass('current');
				$Obj.find('.list').slideUp();
			}else{ //展开
				$Obj.addClass('current');
				$(this).next('.list').slideDown();
			}
		});
		if($('.information_shipping .shipping').size()>0){ //有快递方式信息
			$('.information_shipping .shipping:eq(0) .title').click(); //默认点击第一个
		}
		
		//选择快递方式
		$('.shipping_method_list').delegate('li', 'click', function(){ 
			var obj=$(this).find('input:radio'),
				OvId=obj.parents('.list').parent().attr('data-id'),
				SId=obj.val(),
				type=obj.attr('ShippingType'),
				price=obj.attr('price'),
				insurance=obj.attr('insurance'),
				inputCId=$('#PlaceOrderFrom input[name=order_shipping_address_cid]'),
				inputSId=$('#PlaceOrderFrom input[name=order_shipping_method_sid]'),
				inputType=$('#PlaceOrderFrom input[name=order_shipping_method_type]'),
				inputPrice=$('#PlaceOrderFrom input[name=order_shipping_price]'),
				inputSId_ary={},inputType_ary={}, inputPrice_ary={};
			
			inputSId.val()!='[]' && (inputSId_ary=$.evalJSON(inputSId.val()));
			inputType.val()!='[]' && (inputType_ary=$.evalJSON(inputType.val()));
			inputPrice.val()!='[]' && (inputPrice_ary=$.evalJSON(inputPrice.val()));
			
			obj.parent().parent().siblings().find('input').removeAttr('checked');
			obj.attr('checked', 'checked');
			cart_obj.cart_init.set_shipping_method(OvId, SId, price, type, insurance);
		});
		
		//选择快递保险费
		$('.shipping_insurance').click(function(){
			var $This=$(this),
				$Type=$This.attr('checked')=='checked'?1:0;
			
			//运费计算
			var inputPrice=$('#PlaceOrderFrom input[name=order_shipping_price]'),
				inputPrice_ary={},
				shipPrice=0;
			inputPrice.val()!='[]' && (inputPrice_ary=$.evalJSON(inputPrice.val()));
			for(k in inputPrice_ary){
				shipPrice+=parseFloat(inputPrice_ary[k]);
			}
			
			//保险费计算
			var OvId=$This.parents('.shipping').attr('data-id'),
				insurance=$Type==1?parseFloat($This.parents('.shipping').find('input:radio:checked').attr('insurance')):0;
				inputInsurance=$('#PlaceOrderFrom input[name=order_shipping_insurance]'),
				inputInsurance_ary={}, inputInsurancePrice_ary={},
				insurancePrice=0, insuranceShow=0;
			if(isNaN(insurance)) insurance=0;
			
			inputInsurance.val()!='[]' && (inputInsurance_ary=$.evalJSON(inputInsurance.val()));
			inputInsurance_ary['OvId_'+OvId]=$Type;
			inputInsurance.val($.toJSON(inputInsurance_ary));
			
			inputInsurance.attr('price')!='[]' && (inputInsurancePrice_ary=$.evalJSON(inputInsurance.attr('price')));
			inputInsurancePrice_ary['OvId_'+OvId]=insurance;
			inputInsurance.attr('price', $.toJSON(inputInsurancePrice_ary));
			
			for(k in inputInsurance_ary){
				if(inputInsurance_ary[k]==1){
					insurancePrice+=parseFloat(inputInsurancePrice_ary[k]);
					insuranceShow=1;
				}
			};
			cart_obj.cart_init.show_shipping_insurance(insuranceShow);
			cart_obj.cart_init.show_shipping_info(OvId);
			
			//订单各项价格的计算
			cart_obj.cart_init.order_price_charge({
				'Amount'		: $('#PlaceOrderFrom').attr('amountPrice'), //产品总价
				'UserPrice'		: $('#PlaceOrderFrom').attr('userPrice'), //会员优惠
				'DiscountPrice'	: $('input[name=order_discount_price]').val(), //全场满减
				'CutPrice'		: $('input[name=order_coupon_code]').attr('cutprice'), //优惠券优惠
				'ShippingPrice'	: shipPrice, //快递运费
				'InsurancePrice': insurancePrice, //快递保险费
				'Fee'			: $('#ot_fee').attr('data-fee'), //手续费
				'Affix'			: $('#ot_fee').attr('data-affix'), //手续附加费
			});
		});	
		
		//付款方式
		$('.information_payment .icon_shipping_title').click(function(){
			if($(this).hasClass('current')){
				$(this).removeClass('current');
				$('.information_payment .payment_list:gt(0), .information_payment .payment_contents:gt(0)').hide();
			}else{
				$(this).addClass('current');
				$('.information_payment .payment_list, .information_payment .payment_contents').show();
			}
		});
		$('.payment_list .payment_row').click(function(){
			var $ID=$(this).attr('value');
			$(this).find('input').attr('checked', true);
			$(this).parents('.information_payment').find('.payment_row').removeClass('current');
			$(this).addClass('current');
			$('.payment_contents .payment_note[data-id!='+$ID+']').slideUp(); //收起全部
			$('.payment_contents .payment_note[data-id='+$ID+']').slideDown();
			$('#PlaceOrderFrom input[name=order_payment_method_pid]').val($(this).attr('value'));
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
			//订单各项价格的计算
			cart_obj.cart_init.order_price_charge({
				'Amount'		: $('#PlaceOrderFrom').attr('amountPrice'), //产品总价
				'UserPrice'		: $('#PlaceOrderFrom').attr('userPrice'), //会员优惠
				'DiscountPrice'	: $('input[name=order_discount_price]').val(), //全场满减
				'CutPrice'		: $('input[name=order_coupon_code]').attr('cutprice'), //优惠券优惠
				'ShippingPrice'	: shipPrice, //快递运费
				'InsurancePrice': insurancePrice, //快递保险费
				'Fee'			: $('.payment_contents .payment_note[data-id='+$ID+']').attr('data-fee'), //手续费
				'Affix'			: $('.payment_contents .payment_note[data-id='+$ID+']').attr('data-affix'), //手续附加费
			});
		});
		$('.payment_list .payment_row:visible').eq(0).click();
		
		//优惠券
		var coupon_ajax_mark=true,
			couponCode=$('input[name=order_coupon_code]').val();
		if(couponCode!='') cart_obj.cart_init.ajax_get_coupon_info(couponCode);
		$('#coupon_apply').on('click', function(){ //优惠券提交
			var code=$('input[name=couponCode]').val();
			if(code && coupon_ajax_mark){
				cart_obj.cart_init.ajax_get_coupon_info(code);
			}else{
				$('input[name=couponCode]').addClass('null');
				setTimeout(function(){
					$('input[name=couponCode]').removeClass('null');
				}, 2000);
			}
		});
		$('input[name=couponCode]').on('focus keyup paste mousedown', function(){ //优惠券下拉选择
			var $This	= $(this),
				$Obj	= $This.parent();
			$.post('/ajax/ajax_coupon.html', {'keyword':$(this).val()}, function(data){
				$Obj.find('.coupon_content_box').remove();
				$Obj.append(data);
				$('.coupon_content_box .item').on('click', function(){
					$('input[name=couponCode]').val($(this).attr('data-number'));
					$('.coupon_content_box').remove();
				});
			});
			$('.coupon_box .code_input').on('mouseleave', function(){
				$(this).parent().find('.coupon_content_box').remove();
			});
		});
		$('.btn_coupon_remove').on('click', function(){ //优惠券取消
			$('input[name=order_coupon_code]').val('').attr('cutprice', '0.00');
			$('.coupon_box .code_input').show(200).find('input').val('');
			$('.coupon_box .code_valid').hide().find('strong').text('');
			$('#CouponCharge').hide();
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
			//订单各项价格的计算
			cart_obj.cart_init.order_price_charge({
				'Amount'		: $('#PlaceOrderFrom').attr('amountPrice'), //产品总价
				'UserPrice'		: $('#PlaceOrderFrom').attr('userPrice'), //会员优惠
				'DiscountPrice'	: $('input[name=order_discount_price]').val(), //全场满减
				'CutPrice'		: 0, //优惠券优惠
				'ShippingPrice'	: shipPrice, //快递运费
				'InsurancePrice': insurancePrice, //快递保险费
				'Fee'			: $('#ot_fee').attr('data-fee'), //手续费
				'Affix'			: $('#ot_fee').attr('data-affix'), //手续附加费
			});
			//取消
			$.post('/?do_action=cart.remove_coupon');
		});
		
		//下订单
		$('#orderFormSubmit').click(function(){
			var $obj=$(this);
			$obj.addClass('btn_processing').val(lang_obj.cart.processing_str).attr('disabled', 'disabled');
			
			var Email		= $('input[name=Email]'),
				EmailVal	= $.trim(Email.val()),
				addrId		= $('input[name=order_shipping_address_aid]'),
				countryId	= $('input[name=order_shipping_address_cid]'),
				ShipId		= $('input[name=order_shipping_method_sid]'),
				PayId		= $('input[name=order_payment_method_pid]');
			
			if(Email.length){ //检查邮箱地址
				if(EmailVal=='' || (EmailVal && /^\w+[a-zA-Z0-9-.+_]+@[a-zA-Z0-9-.+_]+\.\w*$/.test(Email.val())==false)){
					Email.addClass('null').next('p.error').text(lang_obj.user.reg_error.EmailFormat).show();
					$('body, html').animate({scrollTop:Email.offset().top-20}, 500);
					$obj.removeClass('btn_processing').val(lang_obj.cart.order_str).removeAttr('disabled');
					return false;
				}
			}
			
			if($('.itemFrom tr.null').length){ //检查是否存在错误产品
				$('body, html').animate({scrollTop:$('.itemFrom').offset().top}, 500);
				global_obj.new_win_alert(lang_obj.cart.attribute_error);
				$obj.removeClass('btn_processing').val(lang_obj.cart.order_str).removeAttr('disabled');
				return false;
			}
			
			if(addrId.val()==-1 || countryId.val()==-1){ //检查收货地址
				$('body, html').animate({scrollTop:$('.information_address').offset().top}, 500);
				global_obj.new_win_alert(lang_obj.cart.address_error);
				$obj.removeClass('btn_processing').val(lang_obj.cart.order_str).removeAttr('disabled');
				return false;
			}
			if(ShipId.val()==-1){ //检查运费方式
				$('body, html').animate({scrollTop:$('.information_shipping').offset().top}, 500);
				global_obj.new_win_alert(lang_obj.cart.shipping_error);
				$obj.removeClass('btn_processing').val(lang_obj.cart.order_str).removeAttr('disabled');
				return false;
			}
			if(PayId.val()==-1){ //检查运费方式
				$('body, html').animate({scrollTop:$('.information_payment').offset().top}, 500);
				global_obj.new_win_alert(lang_obj.cart.payment_error);
				$obj.removeClass('btn_processing').val(lang_obj.cart.order_str).removeAttr('disabled');
				return false;
			}
			
			var Attr='';
			if($('.user_address_form input[name=typeAddr]').val()==1){
				Attr='&Email='+EmailVal+$('#PlaceOrderFrom').attr('nologin');
			}
			var Remark='';
			if($('.itemFrom input[name=Remark\\[\\]]').length){
				$('.itemFrom input[name=Remark\\[\\]]').each(function(){
					Remark+='&Remark_'+$(this).attr('proid')+'_'+$(this).attr('cid')+'='+$(this).val();
				});
			}
			setTimeout(function(){
				$.post('/?do_action=cart.placeorder', $('#PlaceOrderFrom').serialize()+Attr+Remark, function(data){
					if(data.ret==1){
						//Place an Order 生成订单 统计
						analytics_click_statistics(5);
						parseInt(ueeshop_config.FbPixelOpen)==1 && $('html').fbq_checkout();
						cart_obj.cart_init.payment_ready(data.msg.OId);
						//window.top.location.href='/cart/complete/'+data.msg.OId+'.html?utm_nooverride=1';
						return false;
					}else if(data.ret==-1){
						$('body, html').animate({scrollTop:$('.information_address').offset().top}, 500);
						global_obj.new_win_alert(lang_obj.cart.address_error);
					}else if(data.ret==-2){
						$('body, html').animate({scrollTop:$('.information_shipping').offset().top}, 500);
						global_obj.new_win_alert(lang_obj.cart.shipping_error);
					}else if(data.ret==-3){
						$('body, html').animate({scrollTop:$('.information_payment').offset().top}, 500);
						global_obj.new_win_alert(lang_obj.cart.payment_error);
					}else if(data.ret==-4){
						global_obj.new_win_alert(lang_obj.cart.product_error, function(){ window.location.reload(); });
					}else if(data.ret==-5){
						$('body, html').animate({scrollTop:$('.cartFrom').offset().top}, 500);
						global_obj.new_win_alert(lang_obj.cart.low_error+': '+data.msg, '', '', undefined, undefined, lang_obj.global.ok);
					}else if(data.ret==-6){
						var arr=data.msg.split(',');
						for(i in arr){
							if(!$('.cartFrom tr[cid='+arr[i]+'].error').length){
								$('.cartFrom tr[cid='+arr[i]+']').addClass('error').find('.prod_info_detail .invalid').show();
							}
						}
						$('body, html').animate({scrollTop:$('.cartFrom').offset().top}, 500);
						global_obj.new_win_alert(lang_obj.cart.stock_error);
					}
					$obj.removeClass('btn_processing').val(lang_obj.cart.order_str).removeAttr('disabled');
				}, 'json');
			}, 1000);
			return false;
		});
	},
	
	complete_init:function(){
		$('#lib_cart .complete').delegate('a.payButton', 'click', function(){
			$('.payment_info').slideUp(300).siblings('.payment_form').slideDown(500);
		});
		
		$('.payment_form').delegate('#Cancel', 'click', function(){
			$('.payment_info').slideDown(300).siblings('.payment_form').slideUp(500);
		});
		
		$('#PaymentForm').delegate('input[name=SentMoney]', 'keypress keyup', function(){// keydown
			$(this).val(($(this).val()).replace(/[^\d.]/g, ''));
		});
		$('#PaymentForm').delegate('input[name=MTCNNumber]', 'keypress keyup', function(){// keydown
			$(this).val(($(this).val()).replace(/[^\d]/g, ''));
		});
		$('#PaymentForm').delegate('input,select', 'click', function(){
			$(this).removeAttr('style');
		});
		
		var pay_rq_mark=true;
		$('#PaymentForm').submit(function(){ return false; });
		$('#paySubmit').on('click', function(){
			if(pay_rq_mark && !$('#paySubmit').hasClass('disabled')){
				var $notnull=$('#PaymentForm input[notnull], #PaymentForm select[notnull]'),
					$errorObj=new Object, $format=new Object;
				$('#paySubmit').addClass('disabled');
				pay_rq_mark=false;
				setTimeout(function(){
					var status=0;
					$notnull.each(function(){
						$errorObj=$(this).parent().next('p.error');
						if($.trim($(this).val())==''){
							$(this).addClass('null');
							$errorObj.text(lang_obj.user.address_tips.PleaseEnter.replace('%field%', $(this).attr('placeholder'))).show();
							status++;
							if(status==1){
								$('body,html').animate({scrollTop:$(this).offset().top-20}, 500);
							}
						}else{
							$(this).removeClass('null');
							$errorObj.hide();
						}
					});
					$('#PaymentForm input[format]').each(function(){
						$errorObj=$(this).parent().next('p.error');
						$format=$(this).attr('format').split('|');
						if($format[0]=='Length' && $.trim($(this).val()).length!=parseInt($format[1])){
							$(this).addClass('null');
							$errorObj.text(lang_obj.format.length.replace('%num%', $format[1])).show();
							status++;
							if(status==1){
								$('body,html').animate({scrollTop:$(this).offset().top-20}, 500);
							}
						}else{
							$(this).removeClass('null');
							$errorObj.hide();
						}
					});
					if(status){ //检查表单
						pay_rq_mark=true;
						$('#paySubmit').removeClass('disabled');
						return false;
					}
					$.post('/?do_action=cart.offline_payment', $('#PaymentForm').serialize(), function(data){
						if(data.ret==1){
							window.top.location.reload();
						}else if(data.ret=='-1'){
							alert(lang_obj.payment.required_fields_tips);
						}else if(data.ret=='-2'){
							alert(lang_obj.payment.already_paid_tips);
						}else if(data.ret=='-3'){
							alert(lang_obj.payment.abnormal_tips);
						}
					}, 'json');
					pay_rq_mark=true;
					$('#paySubmit').removeClass('disabled');
				}, 100);
			}
			return false;
		});
		
		//提交编辑支付方式
		$('form[name=pay_edit_form]').submit(function(){ return false; });
		$('#pay_button').click(function(){
			var obj=$('form[name=pay_edit_form]');
			$(this).attr('disabled', 'disabled').blur();
			
			$.post('/?do_action=cart.orders_payment_update', obj.serialize(), function(data){
				window.top.location=$('form[name=pay_edit_form] input[name=BackLocation]').val();
			});
			return false;
		});
	},
	
	success_init:function(){
		cart_obj.cart_init.return_payment_list($('input[name=TotalPrice]').val()); //判断支付下拉显示内容
		
		//付款方式
		$('.information_payment .icon_shipping_title').click(function(){
			if($(this).hasClass('current')){
				$(this).removeClass('current');
				$('.information_payment .payment_list:gt(0), .information_payment .payment_contents:gt(0)').hide();
			}else{
				$(this).addClass('current');
				$('.information_payment .payment_list, .information_payment .payment_contents').show();
			}
		});
		$('.payment_list .payment_row').click(function(){
			var $ID=$(this).attr('value');
			$(this).find('input').attr('checked', true);
			$(this).parents('.information_payment').find('.payment_row').removeClass('current');
			$(this).addClass('current');
			$('.payment_contents .payment_note[data-id!='+$ID+']').slideUp(); //收起全部
			$('.payment_contents .payment_note[data-id='+$ID+']').slideDown();
			
			var $TotalPrice=parseFloat($('input[name=TotalPrice]').val()), //订单金额
				$Fee=parseFloat($('.payment_contents .payment_note[data-id='+$ID+']').attr('data-fee')), //手续费
				$Affix=parseFloat($('.payment_contents .payment_note[data-id='+$ID+']').attr('data-affix')), //手续附加费
				$FeePrice=0;
			isNaN($Fee) && ($Fee=0);
			isNaN($Affix) && ($Affix=0);
			$FeePrice=parseFloat($TotalPrice*($Fee/100))+$Affix; //付款手续费
			$FeePrice<0 && ($FeePrice=0);
			$('#ot_total').text(ueeshop_config.currency_symbols+$('html').currencyFormat(($TotalPrice+$FeePrice).toFixed(2), ueeshop_config.currency));
		});
		$('.payment_list .payment_row:visible').eq(0).click();
		
		//提交编辑付款方式
		$('form[name=pay_edit_form]').submit(function(){ return false; });
		$('#pay_button').click(function(){
			var obj=$('form[name=pay_edit_form]');
			$(this).attr('disabled', 'disabled').blur();
			
			$.post('/?do_action=cart.orders_payment_update', obj.serialize(), function(data){
				window.top.location.href=$('form[name=pay_edit_form] input[name=BackLocation]').val();
			});
			return false;
		});
		
		//提交注册会员
		$('form[name=account_form]').submit(function(){ return false; });
		$('.btn_create_account').click(function(){
			var obj=$('form[name=account_form]');
			$('.pwd_input').removeAttr('style');
			if($.trim($('.pwd_input').val())==''){
				$('.pwd_input').css('border-color', '#c00');
				return false;
			}
			$(this).attr('disabled', 'disabled').blur();
			$.post('/?do_action=cart.orders_create_account', obj.serialize(), function(data){
				if(data.ret==1){
					window.top.location.href='/account/';
				}else{
					global_obj.new_win_alert(lang_obj.global.set_error);
				}
			}, 'json');
			return false;
		});
	}
};