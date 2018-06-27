/*
 * 广州联雅网络
 */

jQuery(function($){
	
	$.fn.extend({
		//总价格整理
		get_price:function(count){
			var p=parseFloat($("#ItemPrice").val()),//目前单价
				old=parseFloat($("#ItemPrice").attr('old')),//目前市场价
				curP=parseFloat($("#ItemPrice").attr("initial")),//产品会员价
				isSales=parseFloat($("#ItemPrice").attr("sales")),//是否开启产品促销
				salesP=parseFloat($("#ItemPrice").attr("salesPrice")),//产品促销现金
				disCount=parseInt($("#ItemPrice").attr("discount")),//产品促销折扣
				secKill=parseInt($("input[name=SId]").val()),//产品秒杀
				isTuan=parseInt($("input[name=TId]").val()),//产品团购
				attr_hide=$.evalJSON($("#attr_hide").val()),//属性
				attr_len=$("#attr_hide").val().split(",").length,
				ext_attr=$.evalJSON($("#ext_attr").val()),//扩展属性
				IsCombination=parseInt($("#IsCombination").val()),//是否开启规格组合
				price=0,
				wholesalePrice=0,
				wholesaleDiscount=0,
				_value=0,
				ary=new Array,
				i, s='';
			count=parseInt(count);
			if($(".wholesale_list").length) var wholesale_attr=$.evalJSON($(".wholesale_list").attr("data"));
			
			if(wholesale_attr){//加入批发价
				for(k in wholesale_attr){
					if(count<parseInt(k)){
						break;
					}else{
						wholesalePrice=p=parseFloat(wholesale_attr[k]);
						wholesaleDiscount=parseFloat($('.wholesale_list dd[data-num='+k+'] .wprice').attr('data-discount'));
					}
				}
				if(!wholesalePrice || curP<wholesalePrice) p=curP;
			}
			$("#ItemPrice").val(p.toFixed(2));
			
			$('.wholesale_list dd').each(function(){
				_value=$(this).find('.wprice').attr('data-price');
				$(this).find('.wprice').text(ueeshop_config.currency_symbols+parseFloat(_value).toFixed(2));
			});
			
			if(attr_len && ext_attr && ext_attr!='[]'){//加入属性价格
				if(IsCombination==1){//规格组合
					i=0;
					for(k in attr_hide){
						ary[i]=attr_hide[k];
						i+=1;
					}
					//ary.sort(function(a,b){ return a-b });
					ary.sort(function(a, b){
						if(a.indexOf('Ov:')!=-1){
							a=99999999;
						}
						if(b.indexOf('Ov:')!=-1){
							b=99999999;
						}
						return a - b;
					});
					s=ary.join('_');
					if(ext_attr[s] && attr_len==$('.attr_show').length){
						if(parseInt(ext_attr[s][4])){ //加价
							price=parseFloat(ext_attr[s][0]);
						}else{ //单价
							if(secKill==0 && isTuan==0){ //不能是秒杀产品 或者 团购产品
								p=parseFloat(ext_attr[s][0]);
								wholesaleDiscount && (p*=1-wholesaleDiscount);
								$('.prod_info_wholesale .pw_column').each(function(){
									_value=$(this).find('.pw_td:eq(1)').attr('data-discount');
									$discount=((1-_value)*100).toFixed(3);
									$discount=$discount.lastIndexOf('.')>0 && $discount.length-$discount.lastIndexOf('.')-1>1?$discount.substring(0, $discount.lastIndexOf('.')+2):parseFloat($discount).toFixed(1);
									$(this).find('.pw_td:eq(1)').text($discount+'% Off');
								});
							}
						}
					}
				}else{
					var ext_value='';
					for(k in attr_hide){//循环已勾选的属性参数
						ext_value=ext_attr[attr_hide[k]];
						if(ext_value) price+=parseFloat(ext_value[0]);//固定是加价
					}
				}
			}
			
			if(isSales && salesP && p>salesP){//批发价和促销现金相冲突的情况下，以最低价优先
				p=salesP;
			}
			
			cPrice=(p+price).toFixed(2);
			cOld=(old+price).toFixed(2);
			if(isSales && disCount){//促销折扣(会员价+属性价格)
				cPrice=cPrice*(disCount/100);
			}
			
			var cur_price=(cPrice*parseFloat(ueeshop_config.currency_rate)).toFixed(2),
				del_price=(cOld*parseFloat(ueeshop_config.currency_rate)).toFixed(2);
			if(!isNaN(cPrice)) $('.cur_price').html('<span>'+ueeshop_config.currency+' '+ueeshop_config.currency_symbols+'</span>'+$('html').currencyFormat(cur_price, ueeshop_config.currency));
			$('.prod_info_price .price_0 del').html(ueeshop_config.currency_symbols+$('html').currencyFormat(del_price, ueeshop_config.currency));
			if($('.price_0').length && $('.last_price .save_price').length){
				var dis=(cOld-cPrice)/cOld*100;
				if(dis>0){
					$('.last_price .save_price').show();
					$('.last_price .save_p').text(ueeshop_config.currency_symbols+$('html').currencyFormat(((cOld-cPrice)*parseFloat(ueeshop_config.currency_rate)).toFixed(2), ueeshop_config.currency));
					$('.last_price .save_style').text('('+parseInt((dis>0 && dis<1)?1:dis)+'% Off)');
				}else{
					$('.last_price .save_price').hide();
				}
			}
			
			$CId=$('#CId').val();
			get_shipping_methods($CId, 1);
			
			//处理组合那一块
			var qty_data=$.evalJSON($(".prod_info_qty").attr("data"));
			$('#detail_sale_layer .promotion_body .master .prod_price>input').attr('curprice', cur_price*qty_data.min);
			$('#detail_sale_layer .promotion_body.gp_list_purchase .group_curprice .price_data').attr('data', cur_price*qty_data.min);
			$('#detail_sale_layer .promotion_body.gp_list_purchase .group_oldprice .price_data').attr('data', del_price*qty_data.min);
			$('#detail_sale_layer .promotion_body.gp_list_purchase .group_saveprice .price_data').attr('data', parseFloat(del_price*qty_data.min)-parseFloat(cur_price*qty_data.min));
			$('#detail_sale_layer .promotion_body.gp_list_purchase').each(function(){
				var $totalPrice=0, $oldPrice=0;
				$(this).find('input:checkbox:checked').each(function(){
					$totalPrice+=parseFloat($(this).attr('curprice'));
					$oldPrice+=parseFloat($(this).attr('oldprice'));
				});
				$(this).find('.group_curprice .price_data').text($('html').currencyFormat($totalPrice.toFixed(2), ueeshop_config.currency));
				$(this).find('.group_oldprice .price_data').text($('html').currencyFormat($oldPrice.toFixed(2), ueeshop_config.currency));
				$(this).find('.group_saveprice .price_data').text($('html').currencyFormat(($oldPrice-$totalPrice).toFixed(2), ueeshop_config.currency));
			});
		},
		
		//购买数量增减
		set_amount:function(e){
			var t=this,
				n=t.find("#quantity");
				
			t.on("blur", "#quantity", function(){
				e=$.evalJSON($(".prod_info_qty").attr("data"));
				if(!e) e=$.extend({min:1,max:99999,count:1});
				var num=parseInt($(this).val(), 10);
				if(!/^\d+$/.test($(this).val())){
					//$('html').tips_box('Quantity entered must be a number!', 'error');
					$(this).val(e.count);
				}
				if(!/^[^\d]+$/.test($(this).val())){
					$(this).val(num).focus();
				}
				if($(this).val()==""){
					return e.count;
				}else{
					var Max=parseInt($('#quantity').attr('data-stock'));
					if(isNaN(num) || e.min>num || num>Max || num>e.max){
						if(num<e.min){ //低过起订量
							e.count=e.min;
							e.count>Max && (e.count=Max); //防止 起订量 > 最大购买数量
							$('html').tips_box(lang_obj.products.warning_MOQ, 'error');
						}else if(num>Max){ //高过最大购买数量
							e.count=Max;
							$('html').tips_box(lang_obj.products.warning_Max.replace('%num%', Max), 'error');
						}else if(num>e.max){ //高过库存
							e.count=e.max;
							$('html').tips_box(lang_obj.products.warning_stock.replace('%num%', e.max), 'error');
						}else{ //不是数字
							$('html').tips_box(lang_obj.products.warning_number, 'error');
						}
					}else{
						e.count=num;
						return void 0;
					}
					n.val(e.count);
					t.get_price(e.count);
					return !1;
				}
			}).on("keyup", "#quantity", function(){
				t.get_price($(this).val());
			}).on("click", ".add, .cut", function(){
				e=$.evalJSON($(".prod_info_qty").attr("data"));
				if(!e) e=$.extend({min:1,max:99999,count:1});
				var num=parseInt(n.val(), 10);
				var value=$(this).hasClass('add')?1:-1;
				var Max=parseInt($('#quantity').attr('data-stock'));
				num = num?num:1;
				num += value;
				if(num<e.min){ //低过起订量
					num=e.min;
					num>Max && (num=Max); //防止 起订量 > 最大购买数量
					$('html').tips_box(lang_obj.products.warning_MOQ, 'error');
				}else if(num>Max){ //高过最大购买数量
					num=Max;
					$('html').tips_box(lang_obj.products.warning_Max.replace('%num%', Max), 'error');
				}else if(num>e.max){ //高过库存
					num=e.max;
					$('html').tips_box(lang_obj.products.warning_stock.replace('%num%', e.max), 'error');
				}
				n.val(num);
				t.get_price(num);
			});
		},
		
		//检查当前属性库存的情况
		check_stock:function(){
			var attr_len=$('.attr_show').length,
				ext_attr=$.evalJSON($("#ext_attr").val()),//扩展属性
				$attrStock=parseInt($("#attrStock").val()),
				tagName=this.get(0).tagName.toLowerCase();
			
			if(attr_len && $attrStock){ //开启了0是库存为空的设定
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
						if(this.find('span[value="'+k+'"]').length) this.find('span[value="'+k+'"]').addClass('out_stock out_stock_fixed');
					}else{
						if(this.find('span[value="'+k+'"]').length) this.find('span[value="'+k+'"]').removeClass('out_stock out_stock_fixed');
					}
				}
			}
		}
	});
	
	set_amount=$('.prod_info_qty').set_amount();
	
	//检查当前所有属性库存的情况
	if($('.attr_show').length && parseInt($('#IsCombination').val())==1){
		$('.attr_show').check_stock();
	}
	
	//属性事件
	var num, attr_id,
		attr_ary		= new Object,
		goods_form		= $('#goods_form'),
		attr_select		= $(".attr_select", goods_form),
		cart_tips		= $('#cart_tips'),
		cart_arrt_tips	= $('#cart_arrt_tips'),
		attr_hide		= $("#attr_hide"),
		attr_len		= $(".gr .select").length,
		ext_attr		= $.evalJSON($("#ext_attr").val()),//扩展属性
		$attrStock		= parseInt($("#attrStock").val());
	
	var o={
		goods_form: $('#goods_form'),
		prod_info_name: $('.prod_info_name'),
		attr_show: $('.attr_show'),
		prod_info_qty: $('.prod_info_qty'),
		quantity: $('#quantity'),
		prod_info_actions: $('.prod_info_actions'),
		cart_tips: $('#cart_tips'),
		cart_arrt_tips: $('#cart_arrt_tips'),
		attr_hide: $("#attr_hide"),
		ext_attr: $("#ext_attr"),
		attrStock: $("#attrStock"),
		is_combination: $('#IsCombination'),
		is_default_selected: $('#IsDefaultSelected'),
	};
	
	var VId, attr_id, attr_ary=new Object,
		attr_len=o.attr_show.length,
		ext_attr=$.evalJSON(o.ext_attr.val()),//扩展属性
		$attrStock=parseInt(o.attrStock.val()),
		//$sku_box=$(".prod_info_sku"),//SKU显示
		$defaultStock=parseInt(o.quantity.attr('data-stock')),//产品默认库存
		$IsCombination=parseInt(o.is_combination.val());//是否开启规格组合
		attrSelected=parseInt(o.is_default_selected.val());//默认选择
	
	o.attr_show.on("click ontouchstart", "span", function(e){//增加ipad触屏事件
		e.preventDefault();
		if($(this).hasClass("out_stock")){return false;}
		var $this=$(this),
			$obj=$this.parents(".attr_show").find("input");
		
		if($this.hasClass("selected")){//取消操作
			$this.removeClass("selected");
			VId='';
		}else{//勾选操作
			$this.parent().find('span').removeClass('form_select_tips');
			$this.addClass("selected").siblings().removeClass("selected");
			VId=$(this).attr("value");
		}
		$obj.val(VId);
		attr_id=$obj.attr("attr");
		if(attr_hide.val() && attr_hide.val()!='[]'){
			attr_ary=$.evalJSON(attr_hide.val());
		}
		if(VId){
			attr_ary[attr_id]=VId;
		}else{//选择默认选项，清除对应ID
			delete attr_ary[attr_id];
		}
		attr_hide.val($.toJSON(attr_ary));
		
		//库存显示
		var i=stock=0,
			ary=new Array,
			cur_attr='';
		for(k in attr_ary){
			ary[i]=attr_ary[k];
			++i;
		}
		//ary.sort(function(a,b){ return a-b });
		ary.sort(function(a, b){
			if(a.indexOf('Ov:')!=-1){
				a=99999999;
			}
			if(b.indexOf('Ov:')!=-1){
				b=99999999;
			}
			return a - b;
		});
		cur_attr=ary.join('_');
		if(cur_attr=='Ov:1'){
			stock=$defaultStock;
			$('#inventory_number').text(stock);
		}else if(cur_attr && ext_attr[cur_attr]){
			stock=(!$attrStock && ext_attr[cur_attr][1]<1)?$defaultStock:ext_attr[cur_attr][1];
			parseInt($('input[name=SId]').val()) && parseInt($('input[name=SId]').attr('stock'))<=stock && (stock=$('input[name=SId]').attr('stock'));
			$('#inventory_number').text(stock);
		}else{
			//$('#inventory_number').text(0);//还原库存
			stock=$defaultStock;
			$('#inventory_number').text($defaultStock);
		}
		if($IsCombination==0){//关闭规格组合，固定显示默认库存
			stock=$defaultStock;
			$('#inventory_number').text($defaultStock);
		}
		
		if($attrStock && $IsCombination==1){
			if(attr_hide.val()=='[]' || attr_hide.val()=='{}'){//组合属性都属于默认选项
				$('.attr_show').check_stock(); //检查当前所有属性库存的情况
				$('#inventory_number').text($defaultStock);//还原库存
				stock=$defaultStock;
			}else if(ext_attr && ext_attr!='[]'){//判断组合属性库存状态
				var select_ary=new Array, i=-1, ext_ary=new Object, ary=new Object, cur, no_stock_ary=new Object;
				for(k in attr_ary){
					select_ary[++i]=attr_ary[k];
				}
				if(select_ary.length == attr_len-1){ //勾选数 比 属性总数 少一个
					var no_attrid=0, attrid=0, _select_ary, key;
					$('.attr_show').each(function(){
						attrid=$(this).find('.attr_value').attr('attr');
						if(!attr_ary[attrid]){
							no_attrid=attrid; //没有勾选的属性ID
						}
					});
					$('#attr_'+no_attrid).siblings('span').each(function(){
						value=$(this).attr('value');
						_select_ary=new Array;
						for(k in select_ary){
							_select_ary[k]=select_ary[k];
						}
						_select_ary[select_ary.length]=value;
						//_select_ary.sort(function(a, b){ return a - b });
						_select_ary.sort(function(a, b){
							if(a.indexOf('Ov:')!=-1){
								a=99999999;
							}
							if(b.indexOf('Ov:')!=-1){
								b=99999999;
							}
							return a - b;
						});
						key=_select_ary.join('_');
						if(ext_attr[key][1]==0){
							if($('span[value="'+value+'"]').length) $('span[value="'+value+'"]').addClass('out_stock');
						}else{
							if($('span[value="'+value+'"]').length) $('span[value="'+value+'"]').removeClass('out_stock');
						}
						if(VId==''){ //取消操作
							$('.attr_show').each(function(){
								if($(this).find('.attr_value').attr('attr')!=attr_id){
									$(this).find('span.out_stock').not('.out_stock_fixed').removeClass('out_stock');
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
							if($('span[value="'+k+'"]').length) $('span[value="'+k+'"]').addClass('out_stock');
						}else{
							if($('span[value="'+k+'"]').length) $('span[value="'+k+'"]').removeClass('out_stock');
						}
					}
				}else{ //勾选数 大于 1
					$('.attr_show').each(function(){
						$(this).find('span.out_stock').not('.out_stock_fixed').removeClass('out_stock');
					});
				}
			}
		}
		
		qty_data=$.evalJSON(o.prod_info_qty.attr("data"));
		if(parseFloat(qty_data.max)!=stock){//更新属性库存
			if(!stock || stock<1) stock=$defaultStock;
			qty_data.max=stock;
			o.prod_info_qty.attr("data", $.toJSON(qty_data));
		}
		var Max=parseInt($("#quantity").attr('data-stock'));
		if(parseInt(o.quantity.val())>Max){ //最大购买量
			$('html').tips_box(lang_obj.products.warning_MOQ.replace('%num%', Max), 'error');
			o.quantity.val(Max);
		}else if(parseInt(o.quantity.val())>parseInt(qty_data.max)){ //库存
			$('html').tips_box(lang_obj.products.warning_stock.replace('%num%', qty_data.max), 'error');
			o.quantity.val(qty_data.max);
		}
		o.quantity.get_price(o.quantity.val());
		
		if($(this).parent().find('.attr_value').hasClass('colorid')){//颜色图片属性
			$.ajax({
				url:"/ajax/goods_detail_pic.html",
				async:false,
				type:'get',
				data:{"ProId":$("#ProId").val(), "ColorId":$(".colorid").length?$(".colorid").val():$(".attr_value").val()},
				dataType:'html',
				success:function(result){
					if(result){
						$(".detail_pic").html(result);
						goods_pic();
						//small_pic();
					}
				}
			});
		}
	});
	
	//发货地仅有“中国”一个，就自动默认执行
	if($('.attr_show').length){
		var obj=$('#attr_Overseas').parent().find("span").not(".out_stock").eq(0);
		if(!obj.hasClass('selected')){
			obj.click();
		}
	}
	
	//购物车属性以选择按钮显示，默认执行第一个选项
	if(attrSelected && $('.attr_show').length){
		$(".attr_show").each(function(){
			if($(this).find('input:hidden').attr('id')!='attr_Overseas'){
				$(this).find("span").not(".out_stock").eq(0).click();
			}
		});
	}
	
	//加入购物车
	var addcart=true;
	$('#buynow_button').on('click', function(){
		if(addcart){
			var attr_null=0;
			o.goods_form.submit(function(){ return false; });
			o.attr_show.find(".attr_value").each(function(){
				if(!$(this).val()){
					attr_null++;
				}
			});
			if(attr_null){
				$(window).scrollTop(o.prod_info_name.offset().top);
				$('html').tips_box(lang_obj.cart.plz_sel_para, 'error');
				return;
			}
			addcart=false;
			$.post('/?do_action=cart.additem', o.goods_form.serialize()+'&IsBuyNow=1&back=1', function(data){
				addcart=true;
				if(data.ret==1){
					//BuyNow统计
					analytics_click_statistics(1);//暂时统计为添加购物车事件
					parseInt(ueeshop_config.FbPixelOpen)==1 && $('html').fbq_addtocart(data.msg.item_price);
					$.post('/?do_action=cart.check_low_consumption&t='+Math.random(), {'CId':data.msg.CId}, function(json){ //最低消费金额判断
						if(json.ret==1){ //符合
							window.top.location.href=data.msg.location;
						}else{ //不符合
							var tips=(lang_obj.cart.consumption).replace('%low_price%', ueeshop_config.currency_symbols+$('html').currencyFormat(json.msg.low_price, ueeshop_config.currency)).replace('%difference%', ueeshop_config.currency_symbols+$('html').currencyFormat(json.msg.difference, ueeshop_config.currency));
							$('html').tips_box(tips, 'error');
						}
					}, 'json');
				}else{
					window.top.location.href='/account/';
				}
			}, 'json');
		}
	});
	
	$('#addtocart_button').on('click', function(){
		if(addcart){
			var attr_null=0;
			o.goods_form.submit(function(){ return false; });
			o.attr_show.find(".attr_value").each(function(){
				if(!$(this).val()){
					attr_null++;
				}
			});
			if(attr_null){
				$(window).scrollTop(o.prod_info_name.offset().top);
				$('html').tips_box(lang_obj.cart.plz_sel_para, 'error');
				return false;
			}
			var offset	= $('header aside .i3').offset(),
				btnLeft	= $(this).offset().left+$(this).outerWidth(true)/3,
				btnTop	= $(this).offset().top-$(this).outerHeight(true)-30,
				flyer	= $('<div></div>');
				addcart	= false;
			$.post('/?do_action=cart.additem', o.goods_form.serialize()+'&back=1', function(data){
				if(data.ret==1){
					//加入购物车统计
					analytics_click_statistics(1);
					parseInt(ueeshop_config.FbPixelOpen)==1 && $('html').fbq_addtocart(data.msg.item_price);
					flyer.fly({start:{left:btnLeft, top:btnTop, width:50, height:50}, end:{left:offset.left, top:offset.top, width:20, height:20}}, function(){
						$('header .i3 .cart_count').text(data.msg.qty);
						//$('html').tips_box(lang_obj.cart.cart_tips, 'success');
						$('#tips_cart').show().find('.tips_cart_count').text(data.msg.qty);
						$('#tips_cart').find('.tips_cart_total').text(ueeshop_config.currency+' '+ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.price.toFixed(2), ueeshop_config.currency));
						if(data.msg.difference>0){ //最低消费金额
							$('#tips_cart').css('top', '29%');
							$('#tips_cart .consumption').show();
							$('#tips_cart .consumption span:eq(0)').text(ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.low_price, ueeshop_config.currency));
							$('#tips_cart .consumption span:eq(1)').text(ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.difference, ueeshop_config.currency));
						}else{
							$('#tips_cart').css('top', '40%');
							$('#tips_cart .consumption').hide();
						}
						global_obj.div_mask();
						addcart=true;
					});
				}else{
					addcart=true;
					if(data.msg){
						$('html').tips_box(data.msg, 'error');
					}else{
						window.location.href='/account/';
					}
				}
			}, 'json');
		}
	});
	
	$('#paypal_checkout_button').on('click', function(){
		if(addcart){
			var attr_null=0;
			o.goods_form.submit(function(){ return false; });
			o.attr_show.find(".attr_value").each(function(){
				if(!$(this).val()){
					attr_null++;
				}
			});
			if(attr_null){
				$(window).scrollTop(o.prod_info_name.offset().top);
				$('html').tips_box(lang_obj.cart.plz_sel_para, 'error');
				return;
			}
			addcart=false;
			$.post('/?do_action=cart.additem', o.goods_form.serialize()+'&IsBuyNow=1&back=1&excheckout=1', function(data){
				addcart=true;
				if(data.ret==1){
					parseInt(ueeshop_config.FbPixelOpen)==1 && $('html').fbq_addtocart(data.msg.item_price);
					$.post('/?do_action=cart.check_low_consumption&t='+Math.random(), {'CId':data.msg.CId}, function(json){ //最低消费金额判断
						if(json.ret==1){ //符合
							var $quickUrl='/cart/quick.html?CId='+data.msg.CId;
							setTimeout(function(){
								if($(this).loginOrVisitors()){
									window.top.location.href=$quickUrl;
								}else{
									window.top.location.href='/account/login.html?&jumpUrl='+decodeURIComponent($quickUrl);
								}
							}, 500);
						}else{ //不符合
							var tips=(lang_obj.cart.consumption).replace('%low_price%', ueeshop_config.currency_symbols+$('html').currencyFormat(json.msg.low_price, ueeshop_config.currency)).replace('%difference%', ueeshop_config.currency_symbols+$('html').currencyFormat(json.msg.difference, ueeshop_config.currency));
							$('html').tips_box(tips, 'error');
						}
					}, 'json');
				}else if(data.ret==2){
					var tips=(lang_obj.cart.consumption).replace('%low_price%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.low_price, ueeshop_config.currency)).replace('%difference%', ueeshop_config.currency_symbols+$('html').currencyFormat(data.msg.difference, ueeshop_config.currency));
					$('html').tips_box(tips, 'error');
				}else{
					window.top.location.href='/account/';
				}
			}, 'json');
		}
	});
	
	$('html').on('click', '#div_mask, .btn_return', function(){ //提示窗关闭事件
		$('#tips_cart').hide();
		global_obj.div_mask(1);
	});
	
	$(document).scroll(function(){
		var $winTop			= $(window).scrollTop(),
			$actionsTop		= o.prod_info_actions.offset().top,
			$actionsHeight	= o.prod_info_actions.outerHeight();
		
		if($winTop>$actionsTop+$actionsHeight && $('#goods_cart_btn:hidden').length){
			$('#goods_cart_btn').slideDown();
		}
		if($winTop<$actionsTop+$actionsHeight && $('#goods_cart_btn:visible').length){
			$('#goods_cart_btn').slideUp();
		}
	});
	//加入购物车 结束
	
	//添加收藏夹
	$(".add_favorite").on('click', function(){
		var ProId=$(this).attr("data");
		$.get('/account/favorite/add'+ProId+'.html', function(data){
			if(data.ret==1 || data.ret==0){
				if(data.ret==1){
					$('html').tips_box(lang_obj.user.favorite_success, 'success');
					if(parseInt(ueeshop_config.FbPixelOpen)==1){
						//When a product is added to a wishlist.
						fbq('track', 'AddToWishlist', {content_ids:'['+data.msg.Num+']', content_name:data.msg.Name, currency:data.msg.Currency, value:'0.00'});
					}
				}else{
					$('html').tips_box(lang_obj.user.favorite_saved, 'success');
				}
			}else{
				window.top.location.href='/account/';
			}
		}, 'json');
	});
	
	//产品详细页折扣倒计时
	$(".discount_count").find(".discount_time").each(function(){
		var time=new Date();
		$(this).genTimer({
			beginTime: ueeshop_config.date,
			targetTime: $(this).attr("endTime"),
			callback: function(e){
				this.html(e)
			}
		});
	});
	$(".prod_info_seckill, .prod_info_tuan").find(".flashsale_time").each(function(){
		var time=new Date();
		$(this).genTimer({
			beginTime: ueeshop_config.date,
			targetTime: $(this).attr("endTime"),
			callback: function(e){
				this.html(e)
			}
		});
	});
	
	//分享
	$('.share_toolbox .share_s_btn').on('click', function(){
		var $obj=$('.share_toolbox');
		if(!$(this).hasClass('share_s_more')){
			$(this).shareThis($(this).attr('data'), $obj.attr('data-title'), $obj.attr('data-url'));
		}
	});
	
	$.ajax({
		url:"/ajax/goods_detail_pic.html",
		async:false,
		type:'get',
		data:{"ProId":$("#ProId").val(), "ColorId":$(".colorid").length?$(".colorid").attr("attr")+''+$(".colorid").val():$(".attr_value").attr("attr")+''+$(".attr_value").val()},
		dataType:'html',
		success:function(result){
			if(result){
				$(".detail_pic").html(result);
			}
		}
	});
	
	//切换图片
	function goods_pic(){
		//切换图片
		var goods_pic	= $('.goods_pic'),
			olist		= $('.goods_pic ul'),
			oitem		= $('li', olist),
			pic			= $('img', oitem),
			oitemLen	= oitem.length,
			boxW		= $(window).width(),
			small_pic	= goods_pic.find('.trigger .item');
		
        olist.css({'width':boxW*oitemLen, 'display':'block'});
		oitem.css('width', boxW);
        
		if(oitemLen>1){
			//********** 拨动切换 **************
			var startX		= 0,
				endX		= 0,
				disX		= 0,//偏移量
				basicX		= boxW*0.15,//偏移量小于此值时还原
				i			= 0,//当前索引
				startML		= 0,
				str			= '',
				startPos	= {},
				MovePos		= {},
				isScrolling	= 0;
			
			function move(e){
				var touch=e.originalEvent.touches;
				goods_pic.css('background-image', 'none');
				if(touch.length>1 || e.originalEvent.scale && e.originalEvent.scale!==1) return;
				MovePos={x:touch[0].pageX-startPos.x, y:touch[0].pageY-startPos.y};
				isScrolling=Math.abs(MovePos.x)<Math.abs(MovePos.y)?1:0;
				if(isScrolling==0){ //左右
					e.preventDefault();
					disX=touch[0].pageX-startX;
					startML=-i*boxW;
					this.style.MozTransform=this.style.webkitTransform='translate3d('+(startML+disX)+'px,0,0)';
					this.style.msTransform=this.style.OTransform='translateX('+(startML+disX)+'px)';
				}else{ //上下
					olist.unbind("touchmove", move);
					olist.unbind("touchend", end);
				}
			}
			function end(e){
				var _x=Math.abs(disX);
				if(_x>=basicX){
					if(disX>0){//右移
						i--;
						if(i<0){
							i=0;
						}
					}else{//左移
						i++;
						if(i>=oitemLen){
							i=oitemLen-1;
						}
					}
				}
				small_pic.eq(i).addClass('FontBgColor').removeClass('off').siblings('.item').addClass('off').removeClass('FontBgColor');
				this.style.MozTransitionDuration=this.style.webkitTransitionDuration='0.3s';
				this.style.MozTransform=this.style.webkitTransform='translate3d('+ -(i*boxW) +'px,0,0)';
				this.style.msTransform=this.style.OTransform='translateX('+ -(i*boxW) +'px)';
				var img_h=$(this).find('li:eq('+i+') img').outerHeight();
				$('.goods_pic, .goods_pic ul').css({'height':img_h});
				startX=disX=_x=0;
			}
			olist.get(0).ontouchstart=function(e){
				startX=e.touches[0].pageX;
				this.style.MozTransitionDuration=this.style.webkitTransitionDuration=0;
				startPos={x:e.touches[0].pageX, y:e.touches[0].pageY, time:+new Date};
				isScrolling=0;
				olist.bind("touchmove", move);
				olist.bind("touchend", end);
			};
			//*********** 拨动切换 结束 ***********
		}
	}
	(function(){
		goods_pic();
		var windowWidth='',
			picHeight='';
		if(window.innerWidth){
			windowWidth=window.innerWidth;
		}else if((document.body) && (document.body.clientWidth)){
			windowWidth=document.body.clientWidth;
		}
		if(windowWidth>414) windowWidth=414;
		$('.goods_pic, .goods_pic ul, .goods_pic ul li').css({'height':windowWidth});
		$('.goods_pic ul li:eq(0) img').load(function(){
			picHeight=$(this).outerHeight();
			picHeight=(picHeight<windowWidth?picHeight:windowWidth);
			$('.goods_pic, .goods_pic ul, .goods_pic ul li').css({'height':picHeight});
		});
		$(window).resize(function(){
			goods_pic();
		});
	})();
	
	$('.wrapper .detail_desc img').each(function(){
		$(this).removeAttr('width').removeAttr('height').css({'max-width':'', 'width':'auto', 'height':'auto'});
	});
	
	/************************* 批发价拖动 Start *************************/
	if($('.list_wholesale').length){
		var _w=$(window).width();
		function touch_wholesale(){
			var touch0=$('.list_wholesale'),
				list0=touch0.find('.wholesale_list'),
				item0=touch0.find('.item'),
				w0=0;
			item0.each(function(index, element){
				w0+=Math.ceil($(element).outerWidth(true));
			});
			list0.width(w0);
			touch_nav(list0, w0, _w);
		}
		touch_wholesale();
	}
	/************************* 批发价拖动 End *************************/
	
	/************************* 产品弹窗 Start *************************/
	var detail_sale_size=function(){
		var H=$(window).height(),
			o=$('#detail_sale_layer');
		$('#detail_sale_layer').css({'height':H});
		
		o.find('.sale_box').css({'height':(o.height()-o.find('.layer_head').outerHeight(true))});
	}
	$('#detail_sale').on('click', function(){
		var H=$(window).height();
		$('#detail_sale_layer').addClass('show').fadeIn(500).animate('', 500, function(){
			detail_sale_size();
			$(this).css({'position':'absolute'});
			$(document.body).css({'height':H, 'overflow':'hidden'});
			$('#header_fix, .crumb, .detail_pic, .goods_info, .detail_desc, footer, #goods_cart_btn').hide();
			$('#detail_sale_layer').bind('touchmove', function(e){//禁止拖动
				e.preventDefault();
			});
			$('#detail_sale_layer .promotion_body').bind('touchmove', function(e){//允许拖动
				e.stopPropagation();
			});
		});
	});
	$(window).resize(function(){
		detail_sale_size();
	});
	
	$('#detail_shipping').on('click', function(){
		var H=$(window).height();
		$('#detail_shipping_layer').css({'height':H}).addClass('show').fadeIn(500).animate('', 500, function(){
			$(this).css({'position':'absolute'});
			$(document.body).css({'height':H, 'overflow':'hidden'});
			$('#header_fix, .crumb, .detail_pic, .goods_info, .detail_desc, footer, #goods_cart_btn').hide();
			$.ajax({
				type: "GET",
				url: "/?do_action=cart.get_excheckout_country",
				data: '&Type=shipping_cost&ProId='+$('#ProId').val()+'&Qty='+$('#quantity').val()+'&Attr='+$('#attr_hide').val()+'&proType='+$('input[name=products_type]').val()+'&SId='+$('input[name=SId]').val(),
				dataType: "json",
				success: function(data){
					if(data.ret==1){
						var c=data.msg.country;
						var country_select='';
						var defaultCId=226;
						for(i=0; i<c.length; i++){
							if((!CId && c[i].IsDefault==1) || CId==c[i].CId) defaultCId=c[i].CId;
							var s=defaultCId==c[i].CId?'selected':'';
							var f=c[i].FlagPath?' path="'+c[i].FlagPath+'"':'';
							var d=$.evalJSON(c[i].CountryData);
							country_select=country_select+'<option value="'+c[i].CId+'" data-acronym="'+c[i].Acronym+'" '+f+s+'>'+(d?d[ueeshop_config.lang]:c[i].Country)+'</option>';
						}
						$('form[name=shipping_cost_form] select[name=CId]').html('<option value="0">'+lang_obj.products.select_country+'</option>'+country_select);
						
						$CId=$('#CId').val();
						if($('form[name=shipping_cost_form] select[name=CId]').val()!=$CId){
							$('form[name=shipping_cost_form] select[name=CId]').val($CId).change();
						}
						get_shipping_methods($CId);
					}
				}
			});
		});
	});
	
	$('.prod_layer').on('click', '.layer_back', function(){
		var W=$(window).width(),
			$obj=$('.prod_layer');
		if($obj.hasClass('show')){//已开启
			$obj.removeClass('show').fadeOut(500).css({'position':'fixed', 'height':'100%'});
			$(document.body).css({'height':'auto', 'overflow':'auto'});
			$('#header_fix, .crumb, .detail_pic, .goods_info, .detail_desc, footer, #goods_cart_btn').show();
			$('body,html').scrollTop($('.detail_list').offset().top-200);
		}
	});
	/************************* 产品弹窗 End *************************/
	
	/************************* 组合产品 Start *************************/	
	$('#detail_sale_layer #promotion_menu').off().on('change', function(){
		var $obj=$('.promotion_body[data-id='+$(this).find('option:selected').attr('data-id')+']');
		$obj.removeClass('hide').siblings().addClass('hide');
		$('#detail_sale_layer .layer_title').text($(this).find('option:selected').text());
	});
	$('#detail_sale_layer #promotion_menu option:eq(0)').attr('selected', true).change();
	$('#promotion_menu>option').length<2 && $('#detail_sale_layer .detail_sale_menu').hide();
	
	$('#detail_sale_layer .attribute').on('change', 'select', function(){
		var VId, attr_id, cur_attr, attr_ary=new Object, s='', ary=new Array,
			i=stock=attr_price=0,
			$parent=$(this).parents('.attribute'),
			//secKill=parseInt($("input[name=SId]").val()),//产品秒杀
			$attr_hide=$parent.children('.attr_hide'),
			ext_attr=$.evalJSON($parent.children('.ext_attr').val());//扩展属性
			attr_len=$parent.children('dd').length,//当前属性数量
			$attrStock=parseInt($('#attrStock').val()),
			$checkbox=$parent.parents('li').find('input:checkbox'),
			isSeckill=parseInt($checkbox.attr('isSeckill')),
			price=parseFloat($checkbox.attr('price')),
			moq=parseInt($checkbox.attr('moq')),
			isSales=parseFloat($checkbox.attr("sales")),//是否开启产品促销
			salesP=parseFloat($checkbox.attr("salesPrice")),//产品促销现金
			disCount=parseInt($checkbox.attr("discount")),//促销折扣
			IsCombination=$parent.attr('data-combination');//是否开启规格组合
		VId=$(this).val();
		attr_id=$(this).attr('attr');
		if($attr_hide.val() && $attr_hide.val()!='[]'){
			attr_ary=$.evalJSON($attr_hide.val());
		}
		if(VId){
			attr_ary[attr_id]=VId;
		}else{//选择默认选项，清除对应ID
			delete attr_ary[attr_id];
		}
		$attr_hide.val($.toJSON(attr_ary));
		if(attr_len && ext_attr && ext_attr!='[]'){//加入属性价格
			if(IsCombination==1){//规格组合
				i=0;
				for(k in attr_ary){
					ary[i]=attr_ary[k];
					i+=1;
				}
				ary.sort(function(a, b){
					if(a.indexOf('Ov:')!=-1){
						a=99999999;
					}
					if(b.indexOf('Ov:')!=-1){
						b=99999999;
					}
					return a - b;
				});
				s=ary.join('_');
				if(s && ext_attr[s] && attr_len==$parent.children('dd').length){
					if(parseInt(ext_attr[s][4])){ //加价
						attr_price=parseFloat(ext_attr[s][0])*parseFloat(ueeshop_config.currency_rate);
					}else{ //单价
						if($isSeckill==0){ //不能是秒杀产品
							price=parseFloat(ext_attr[s][0])*parseFloat(ueeshop_config.currency_rate);
						}
					}
				}
			}else{
				var ext_value='';
				for(k in attr_ary){//循环已勾选的属性参数
					ext_value=ext_attr[attr_ary[k]];
					if(ext_value) price+=parseFloat(ext_value[0])*parseFloat(ueeshop_config.currency_rate);//固定是加价
				}
			}
		}
		if(isSales && salesP && price>salesP){//当前价格与促销现金相冲突的情况下，以最低价优先
			price=salesP;
		}
		price=price+attr_price;
		if(!isSeckill && disCount){//促销折扣(会员价+属性价格)
			price=price*(disCount/100);
		}
		$checkbox.attr('attrprice', attr_price).attr('curprice', (moq*price));//标注属性价格
		
		var _num=String(price*parseFloat(ueeshop_config.currency_rate).toFixed(3)),
			price=_num.lastIndexOf('.')>0 && _num.length-_num.lastIndexOf('.')-1>2?_num.substring(0, _num.lastIndexOf('.')+3):parseFloat(_num).toFixed(2);
		$parent.parent().find('.price_data').text($('html').currencyFormat(price, ueeshop_config.currency));//价格+属性价格
		
		if($attrStock && IsCombination==1){
			if($attr_hide.val()=='[]' || $attr_hide.val()=='{}'){//组合属性都属于默认选项
				$parent.check_stock(); //检查当前所有属性库存的情况
			}else if(ext_attr && ext_attr!='[]'){//判断组合属性库存状态
				var select_ary=new Array, i=-1, ext_ary=new Object, ary=new Object, cur, no_stock_ary=new Object;
				for(k in attr_ary){
					select_ary[++i]=attr_ary[k];
				}
				if(select_ary.length == attr_len-1){ //勾选数 比 属性总数 少一个
					var no_attrid=0, attrid=0, _select_ary, key;
					$parent.find('dd').each(function(){
						attrid=$(this).children('select').attr('attr');
						if(!attr_ary[attrid]){
							no_attrid=attrid; //没有勾选的属性ID
						}
					});
					$parent.find('#attr_'+no_attrid).find('option:gt(0)').each(function(){
						value=$(this).attr('value');
						_select_ary=new Array;
						for(k in select_ary){
							_select_ary[k]=select_ary[k];
						}
						_select_ary[select_ary.length]=value;
						_select_ary.sort(function(a, b){
							if(a.indexOf('Ov:')!=-1){
								a=99999999;
							}
							if(b.indexOf('Ov:')!=-1){
								b=99999999;
							}
							return a - b;
						});
						key=_select_ary.join('_');
						if(ext_attr[key][1]==0){
							$parent.find('option[value="'+value+'"]').addClass('hide').get(0).disabled=true;
						}else{
							$parent.find('option[value="'+value+'"]').removeClass('hide').get(0).disabled=false;
						}
						if(VId==''){ //取消操作
							$parent.find('li').each(function(){
								if($(this).children('select').attr('attr')!=attr_id){
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
							$parent.find('option[value="'+k+'"]').addClass('hide').get(0).disabled=true;
						}else{
							$parent.find('option[value="'+k+'"]').removeClass('hide').get(0).disabled=false;
						}
					}
				}else{ //勾选数 大于 1
					$parent.find('dd').each(function(){
						$(this).find('option.hide').not('.hide_fixed').removeClass('hide').attr('disabled', false);
					});
				}
			}
		}
		
		//处理组合总价格显示
		$parent.parents('.promotion_body').each(function(){
			var $totalPrice=0, $oldPrice=0;
			$(this).find('input:checkbox:checked').each(function(){
				$totalPrice+=parseFloat($(this).attr('curprice'));
				$oldPrice+=parseFloat($(this).attr('oldprice'));
			});
			$(this).find('.group_curprice .price_data').text($('html').currencyFormat($totalPrice.toFixed(2), ueeshop_config.currency));
			$(this).find('.group_oldprice .price_data').text($('html').currencyFormat($oldPrice.toFixed(2), ueeshop_config.currency));
			$(this).find('.group_saveprice .price_data').text($('html').currencyFormat(($oldPrice-$totalPrice).toFixed(2), ueeshop_config.currency));
		});
	});
	
	$('#detail_sale_layer .promotion_body').each(function(){
		$(this).find('.attribute').each(function(){
			if($(this).find('select').length){
				$(this).find("#attr_Overseas option[value!='']").not('.hide').eq(0).attr('selected', 'selected').change();
				$(this).find('#attr_Overseas option:eq(0)').remove();
			}
		});
	});
	
	$('#detail_sale_layer').on('click', '.check', function(){
		var $obj=$(this).parents('.promotion_body'),
			$self=$(this).find('.btn_checkbox'),
			$totalPrice=0, $oldPrice=0, $num=0;
		if($self.hasClass('current')){ //已勾选
			$self.removeClass('current').next().attr('checked', false);
		}else{ //未勾选
			$self.addClass('current').next()[0].checked='checked';
		}
		$obj.find('input:checkbox:checked').each(function(){ //统计已勾选
			$totalPrice+=parseFloat($(this).attr('curprice'));
			$oldPrice+=parseFloat($(this).attr('oldprice'));
			$num+=1;
		});
		$obj.find('.group_nums').text($num-1);
		$obj.find('.group_curprice .price_data').text($('html').currencyFormat($totalPrice.toFixed(2), ueeshop_config.currency));
		$obj.find('.group_oldprice .price_data').text($('html').currencyFormat($oldPrice.toFixed(2), ueeshop_config.currency));
		$obj.find('.group_saveprice .price_data').text($('html').currencyFormat(($oldPrice-$totalPrice).toFixed(2), ueeshop_config.currency));
	});
	
	$('#detail_sale_layer').on('click', '.gp_btn', function(){
		var $obj=$(this).parents('.promotion_body'),
			$PId=$obj.find('input[name=PId]').val(),
			$data_id=$obj.attr('data-id'),
			id_where=attr_where='', attr_obj=new Object, $num=0, $data;
		
		if($obj.hasClass('gp_list_promotion')){//组合促销
			var Attr=$obj.find('.master_attr_hide').val();
		}else{//组合购买
			//判断主产品
			var attr_null=0;
			o.attr_show.find(".attr_value").each(function(){
				if(!$(this).val()){
					attr_null++;
				}
			});
			if(attr_null){
				$(window).scrollTop(o.prod_info_name.offset().top);
				$('html').tips_box(lang_obj.cart.plz_sel_para, 'error');
				return false;
			}
			//判断组合产品
			var result_to=0;
			$obj.find('input:checkbox:checked').each(function(){
				id_where+=($num?',':'')+parseInt($(this).attr('proid'));
				$num+=1;
				//已勾选的产品，再判断其下拉属性是否已选
				if($(this).parents('li').find('select').length){
					$(this).parents('li').find('select').each(function(){
						$(this).parent().css('border-color', '#ddd');
						if(!$(this).val()){
							$(this).parent().css('border-color', 'red');
							result_to=1;
						}
					});
				}
				//获取产品勾选的属性
				if($(this).parents('li').find('.attr_hide').val()){
					attr_obj[$(this).attr('proid')]=$.evalJSON($(this).parents('li').find('.attr_hide').val());
				}
			});
			if(attr_obj) attr_where=$.toJSON(attr_obj);//合并所有产品属性
			if(result_to) return false;
			
			var Attr=$('#attr_hide').val();
		}
		
		$.ajax({
			url:'/',
			async:false,
			type:'post',
			data:{'ProId':id_where, 'PId':$PId, 'Attr':Attr, 'ExtAttr':attr_where, 'products_type':($obj.hasClass('gp_list_promotion')?4:3), 'do_action':'cart.additem', 'back':1},
			dataType:'json',
			success:function(result){
				if(result.ret==1){
					window.location='/cart/';
				}
			}
		});
		return false;
	});
	/************************* 组合产品 End *************************/
	
	/************************* 运费查询 Start *************************/
	//选择国家操作
	$('body').on('change', 'form[name=shipping_cost_form] select[name=CId]', function(){
		$('#shipping_flag').attr('class', 'icon_flag flag_'+$(this).find('option:selected').attr('data-acronym').toLowerCase());
		$('.shipping_cost_country .country_right .title_wrap').text($(this).find('option:selected').text());
		get_shipping_methods($(this).val());
	});
	
	//选择快递操作
	$('body').on('click', '#shipping_method_list>li', function(){
		$(this).addClass('current AddtoCartBgColor AddtoCartBorderColor').removeClass('item').siblings().removeClass('current AddtoCartBgColor AddtoCartBorderColor').addClass('item');
		$('form[name=shipping_cost_form] input[name=ShippingSId]').val($(this).attr('sid'));
		$('form[name=shipping_cost_form] input[name=ShippingMethodType]').val($(this).attr('ShippingType'));
		$('form[name=shipping_cost_form] input[name=ShippingPrice]').val(parseFloat($(this).attr('price')).toFixed(2));
		$('form[name=shipping_cost_form] input[name=ShippingExpress]').val($(this).attr('method'));
		$('form[name=shipping_cost_form] input[name=ShippingBrief]').val($(this).attr('brief'));
		$('form[name=shipping_cost_form]').submit();
	});
	
	//提交运费查询
	$('body').on('submit', 'form[name=shipping_cost_form]', function(){
		var obj=$('form[name=shipping_cost_form]'),
			btn=$('#excheckout_button');
		btn.attr('disabled', 'disabled').blur();
		if($('#shipping_method_list>li.current').length==0 || (!obj.find('input[name=ShippingSId]').val() && obj.find('input[name=ShippingMethodType]').val()=='')){
			$('html').tips_box('Please select a shipping method!', 'error');
			btn.removeAttr('disabled');
			return false;
		}
		var shipPrice=parseFloat($('form[name=shipping_cost_form] input[name=ShippingPrice]').val());
		shipPrice=shipPrice.toFixed(2);
		if(shipPrice>0){
			$('.shipping_cost_detail, .shipping_cost_info').css('display', '');
			$('.shipping_cost_error').css('display', 'none');
			$('.shipping_cost_price').text(ueeshop_config.currency_symbols+shipPrice);
		}else if(shipPrice==0){
			$('.shipping_cost_detail, .shipping_cost_info').css('display', '');
			$('.shipping_cost_error').css('display', 'none');
			$('.shipping_cost_price').text(lang_obj.products.free_shipping);
		}else{
			$('.shipping_cost_info').css('display', 'none');
			$('.shipping_cost_detail, .shipping_cost_error').css('display', '');
			$('.shipping_cost_price').text('--');
		}
		var shipBrief=$('form[name=shipping_cost_form] input[name=ShippingBrief]').val();
		var country_name=$('form[name=shipping_cost_form] select[name=CId] option:selected').text();
		var express_name=$('form[name=shipping_cost_form] input[name=ShippingExpress]').val();
		$('.shipping_cost_info .delivery_day').text(shipBrief);
		$('#shipping_cost_button').text(country_name+' '+lang_obj.products.via+' '+express_name).attr('title', country_name+' '+lang_obj.products.via+' '+express_name);
		$('#CId').val($('form[name=shipping_cost_form] select[name=CId]').val());
		$('#CountryName').val(country_name);
		$('#ShippingId').val($('#shipping_method_list .current').attr('sid'));
		
		btn.removeAttr('disabled');
		$('#detail_shipping_layer .layer_back').click();
		return false;
	});
	
	function get_shipping_methods(CId, Method){
		$.post('/?do_action=cart.get_shipping_methods', 'CId='+CId+'&Type=shipping_cost&ProId='+$('#ProId').val()+'&Qty='+$('input[name=Qty]').val()+'&Attr='+$('#attr_hide').val(), function(data){
			if(data.ret==1){
				var rowObj, rowStr, j;			
				var shipType=shipMethod='';
				var shipPrice=0;
				var SId=parseInt($('#ShippingId').val());
				var _SId=0;
				var start=-1;
				for(OvId in data.msg.info){
					rowStr='';
					j=0;
					for(i=0; i<data.msg.info[OvId].length; i++){
						rowObj=data.msg.info[OvId][i];
						if(parseFloat(rowObj.ShippingPrice)<0) continue;
						start==-1 && (start=i);
						if((rowObj.type=='' && ((!SId && j==0) || SId==rowObj.SId)) || (rowObj.type!='' && j==0)){
							var sed=' current AddtoCartBgColor AddtoCartBorderColor';
							_SId=SId=rowObj.SId;
							if(Method){
								shipType=rowObj.type;
								shipMethod=rowObj.Name;
								ShippingInsurance=1;
								shipPrice=parseFloat(rowObj.ShippingPrice);
								insurance=parseFloat(rowObj.InsurancePrice);
								shipBrief=rowObj.Brief;
							}
						}else{
							var sed=' item';
						}
						rowStr+='<li class="clean'+sed+'" name="'+rowObj.Name.toUpperCase()+'" sid="'+rowObj.SId+'" method="'+rowObj.Name+'" price="'+rowObj.ShippingPrice+'" brief="'+rowObj.Brief+'" insurance="'+rowObj.InsurancePrice+'" ShippingType="'+rowObj.type+'"><em></em>';
							if(rowObj.Name.toUpperCase()=='DHL' && rowObj.Shipping!=1000 && rowObj.IsAPI==1){
								if(rowObj.ShippingPrice>0){
									rowStr+='<span class="price">--</span>';
								}else rowStr+='<span class="price">'+ueeshop_config.currency_symbols+'0</span>';
							}else{
								rowStr+='<div class="price">'+(rowObj.ShippingPrice>0?ueeshop_config.currency_symbols+rowObj.ShippingPrice:ueeshop_config.currency_symbols+'0')+'</div>';
							}
						rowStr+=	'<div class="info">';
						rowStr+=		'<div class="name">'+rowObj.Name +'</div>';
						rowStr+=		'<div class="txt">'+rowObj.Brief+'</div>';
						rowStr+=	'</div>';
						rowStr+='</li>';
						++j;
					}
				}
				rowObj=data.msg.info[OvId][start]; //重新定义覆盖
				if(_SId==0){ //没有任何快递勾选的情况，自动勾选第一个选项
					SId=rowObj.SId;
				}
				if(Method){
					if(start==-1){
						rowObj={'SId':0, 'type':'', 'Name':'', 'ShippingPrice':-1, 'InsurancePrice':0, 'Brief':''}
						SId=0;
					}
					if(!shipMethod){
						shipType=rowObj.type;
						shipMethod=rowObj.Name;
						ShippingInsurance=1;
						shipPrice=parseFloat(rowObj.ShippingPrice);
						insurance=parseFloat(rowObj.InsurancePrice);
						shipBrief=rowObj.Brief;
					}
					shipPrice=shipPrice.toFixed(2);
					if(shipPrice>0){
						$('.shipping_cost_detail, .shipping_cost_info').css('display', '');
						$('.shipping_cost_error').css('display', 'none');
						$('.shipping_cost_price').text(ueeshop_config.currency_symbols+shipPrice);
					}else if(shipPrice==0){
						$('.shipping_cost_detail, .shipping_cost_info').css('display', '');
						$('.shipping_cost_error').css('display', 'none');
						$('.shipping_cost_price').text(lang_obj.products.free_shipping);
					}else{
						$('.shipping_cost_info').css('display', 'none');
						$('.shipping_cost_detail, .shipping_cost_error').css('display', '');
						$('.shipping_cost_price').text('--');
					}
					$('.shipping_cost_info .delivery_day').text(shipBrief);
					$('#shipping_cost_button').text($('#CountryName').val()+' '+lang_obj.products.via+' '+shipMethod).attr('title', $('#CountryName').val()+' '+lang_obj.products.via+' '+shipMethod);
					$('#ShippingId').val(SId);
				}else{
					if(rowStr!=''){
						$('#shipping_method_list').html(rowStr);
						var checkObj=$('#shipping_method_list li.current');
						shipType=checkObj.attr('ShippingType');
						shipMethod=checkObj.attr('method');
						shipPrice=parseFloat(checkObj.attr('price'));
						insurance=parseFloat(checkObj.attr('insurance'));
						shipBrief=checkObj.attr('brief');
						
						$('form[name=shipping_cost_form] input[name=ShippingExpress]').val(shipMethod);
						$('form[name=shipping_cost_form] input[name=ShippingMethodType]').val(shipType);
						$('form[name=shipping_cost_form] input[name=ShippingPrice]').val(shipPrice.toFixed(2));
						$('form[name=shipping_cost_form] input[name=ShippingBrief]').val(shipBrief);
					}else{
						rowStr+='<li><strong>'+lang_obj.products.no_optional+'!</strong></li>';
						$('#shipping_method_list').html(rowStr);
					}
				}
				
				start!=0 && SId!=rowObj.SId && $('form[name=shipping_cost_form] li:eq(0) input[name=SId]').click(); //原来勾选的选项丢失，并且不是默认第一
				
				$('#detail_shipping_layer .shipping_info_weight span').text(data.msg.total_weight);
			}else{
				$('#shipping_method_list').html('');
			}
		}, 'json');
	}
	
	$CId=$('#CId').val();
	get_shipping_methods($CId, 1);
	/************************* 运费查询 End *************************/
	
	/************************* 产品选项卡 Start *************************/
	$('.detail_desc .t').click(function(){
		if($(this).parent().hasClass('detail_close')){
			$(this).parent().removeClass('detail_close').children('.text').show();
		}else{
			$(this).parent().addClass('detail_close').children('.text').hide();
		}
	});
	/************************* 产品选项卡 End *************************/
	/*平台导流*/
	$('.platform_btn').click(function(){
		$(this).toggleClass('platform_btn_hover');
	});
	$('.editor_txt>table, .editor_txt div>table, .editor_txt p>table, .editor_txt span>table').css('width', '100%');

});