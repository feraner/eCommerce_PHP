/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

$.fn.extend({
	//放大镜插件
	magnify:function(t){
		t=$.extend({blankHeadHeight:0,detailWidth:348,detailHeight:348,detailLeft:458,featureImgRect:"350x350",large:"v"},t);
		
		var n=!1,
			win_left=$(window).scrollLeft(),
			win_top=$(window).scrollTop(),
			u=$("img", this).width(),
			a=$("img", this).height(),
			c=$(this).children("a"),
			narmal_pic=$(this).find(".normal"),
			h='<div class="detail_img_box" style="width:'+t.detailWidth+'px;height:'+t.detailHeight+'px;left:'+t.detailLeft+'px;"><img class="detail_img" onerror="$.imgOnError(this)"></div><div class="rect_mask"></div>';
		$(h).appendTo(this);
		
		var d=this.find(".detail_img_box"),
			v=d.find("img"),
			m=this.find(".rect_mask"),
			g=this,
			w=function($){
				d.hide();
				m.hide();
				m.css("top","-9999px");
				d.css("top","-9999px");
				n=!1
			};
		
		$(this).mouseleave(w).mousemove(function(h){
			if(!n){
				if(!c.attr("href")) return;
				var p=c.attr("href");
				v.attr("src", p);
				s=$(this).offset().left;
				o1=$(this).offset().top;
				o2=$(this).parent().parent().offset().top;
				v_top=o1-o2;
				d.css({top:t.blankHeadHeight-v_top});
				n=!0
			}
			d.css({'width':(v.width()<t.detailWidth?v.width():t.detailWidth),'height':(v.height()<t.detailHeight?v.height():t.detailHeight)});
			u=narmal_pic.width();
			a=narmal_pic.height();
			f=u*(t.detailWidth/v.width()>1?1:t.detailWidth/v.width());
			l=a*(t.detailHeight/v.height()>1?1:t.detailHeight/v.height());
			m.css({"width":f,"height":l});
			d.css({left:t.detailLeft-parseInt(d.parent().parent().css("left"))});
			if(h.clientX+win_left>u+s) return $(this).trigger("mouseleave");
			var g=h.clientX+win_left-s,
				w=h.clientY+win_top-o1;
			g<f/2?g=0:g>u-f/2?g=u-f:g-=f/2;
			w<l/2?w=0:w>a-l/2?w=a-l:w-=l/2;
			m.css({left:g, top:w});
			v.css({left:-(t.detailWidth/f)*g, top:-(t.detailHeight/l)*w, "max-width":"inherit", "max-height":"inherit"});
			d.show();
			m.show()
		});
		
		$(window).on("scroll", function(t){
			win_left=$(window).scrollLeft();
			win_top=$(window).scrollTop();
		});
	},
	
	//购买数量增减
	set_amount:function(e){
		var t=this,
			n=t.find(".qty_num");
		
		t.on("blur", ".qty_num", function(){
			e=$.evalJSON($(".quantity_box").attr("data"));
			if(!e) e=$.extend({min:1,max:99999,count:1});
			var num=parseInt($(this).val(), 10);
			if(!/^\d+$/.test(num)){
				global_obj.new_win_alert('Quantity entered must be a number!');
				$(this).val(e.min).focus();
				return !1;
			}
			if(!/^[\d]+$/.test($(this).val())){
				$(this).val(num).focus();
			}
			if($(this).val()==""){
				return e.count;
			}else{
				var Max=parseInt($('#quantity').attr('stock'));
				if(isNaN(num) || e.min>num || num>Max || num>e.max){
					if(num<e.min){ //低过起订量
						e.count=e.min;
						e.count>Max && (e.count=Max); //防止 起订量 > 最大购买数量
						global_obj.new_win_alert(lang_obj.products.warning_MOQ);
					}else if(num>Max){ //高过最大购买数量
						e.count=Max;
						global_obj.new_win_alert(lang_obj.products.warning_Max.replace('%num%', Max));
					}else if(num>e.max){ //高过库存
						e.count=e.max;
						global_obj.new_win_alert(lang_obj.products.warning_stock.replace('%num%', e.max));
					}else{ //不是数字
						global_obj.new_win_alert(lang_obj.products.warning_number);
					}
				}else{
					e.count=num;
					return void 0;
				}
				n.val(e.count);
				t.get_price(e.count);
				return !1;
			}
		}).on("keyup", ".qty_num", function(){
			t.get_price($(this).val());
		}).on('keypress', ".qty_num", function(e){ //回车事件
			if(e.keyCode==13){
				$('#addtocart_button').click();
				return false;
			}
		});
		$('#btn_add, #btn_cut').on('click', function(){ //渐加or渐减事件
			var num=parseInt(n.val(), 10),
				e=$.evalJSON($(".quantity_box").attr("data")),
				Max=parseInt(n.attr('stock')),
				value=$(this).attr('id')=='btn_add'?1:-1;
			num=isNaN(num)?1:num;
			num+=value;
			if(num<e.min){ //低过起订量
				num=e.min;
				num>Max && (num=Max); //防止 起订量 > 最大购买数量
				global_obj.new_win_alert(lang_obj.products.warning_MOQ);
			}else if(num>Max){ //高过最大购买数量
				num=Max;
				global_obj.new_win_alert(lang_obj.products.warning_Max.replace('%num%', Max));
			}else if(num>e.max){ //高过库存
				num=e.max;
				global_obj.new_win_alert(lang_obj.products.warning_stock.replace('%num%', e.max));
			}
			n.val(num);
			t.get_price(num);
		});
	},
	
	//总价格整理
	get_price:function(count){
		var p=parseFloat($("#ItemPrice").val()),//目前单价
			old=parseFloat($("#ItemPrice").attr('old')),//目前市场价
			curP=parseFloat($("#ItemPrice").attr("initial")),//产品商城价
			isSales=parseFloat($("#ItemPrice").attr("sales")),//是否开启产品促销
			salesP=parseFloat($("#ItemPrice").attr("salesPrice")),//产品促销现金
			disCount=parseInt($("#ItemPrice").attr("discount")),//产品促销折扣
			secKill=parseInt($("input[name=SId]").val()),//产品秒杀
			isTuan=$("input:hidden#IsTuan").length?parseInt($("input:hidden#IsTuan").val()):0,//产品团购
			attr_hide=$.evalJSON($("#attr_hide").val()),//属性
			attr_len=$("#attr_hide").val().split(",").length,
			ext_attr=$.evalJSON($("#ext_attr").val()),//扩展属性
			IsCombination=$('ul.attributes').attr('data-combination'),//是否开启规格组合
			cPrice=price=wholesalePrice=wholesaleDiscount=_value=0,
			ary=new Array,
			i, s='';
		count=parseInt(count);
		if($(".prod_info_wholesale").length) var wholesale_attr=$.evalJSON($(".prod_info_wholesale").attr("data"));
		
		if(wholesale_attr){//加入批发价
			for(k in wholesale_attr){
				if(count<parseInt(k)){
					break;
				}else{
					wholesalePrice=p=parseFloat(wholesale_attr[k]);
					wholesaleDiscount=parseFloat($('.prod_info_wholesale .pw_column[data-num='+k+'] .pw_td:eq(1)').attr('data-discount'));
				}
			}
			if(!wholesalePrice || (!isSales && curP<wholesalePrice)) p=curP;
		}
		$("#ItemPrice").val(p.toFixed(2));
		
		$('.prod_info_wholesale .pw_column').each(function(){
			_value=parseFloat($(this).find('.pw_td:eq(1)').attr('data-price'));
			_value*=parseFloat(ueeshop_config.currency_rate);
			$(this).find('.pw_td:eq(1)').text(ueeshop_config.currency_symbols+_value.toFixed(2));
		});
		
		if(attr_len && ext_attr && ext_attr!='[]'){//加入属性价格
			if(IsCombination==1){//规格组合
				i=0;
				for(k in attr_hide){
					ary[i]=attr_hide[k];
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
				if(s && ext_attr[s] && attr_len==$("ul.attributes li").length){
					if(parseInt(ext_attr[s][4])){ //加价
						price=parseFloat(ext_attr[s][0]);
					}else{ //单价
						if(secKill==0 && isTuan==0){ //不能是秒杀产品 或者 团购产品
							p=parseFloat(ext_attr[s][0]);
							wholesaleDiscount && (p*=1-wholesaleDiscount);
							$('.prod_info_wholesale .pw_column').each(function(){
								_value=$(this).find('.pw_td:eq(1)').attr('data-discount');
								//$discount=((1-_value)*100).toFixed(3);
								$discount=(_value*100).toFixed(3);
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
			cPrice=cPrice*disCount/100;
		}
		
		var num=String((cPrice*parseFloat(ueeshop_config.currency_rate)).toFixed(3)),
			num_to=String((cOld*parseFloat(ueeshop_config.currency_rate)).toFixed(3)),
			cur_price=num.lastIndexOf('.')>0 && num.length-num.lastIndexOf('.')-1>2?num.substring(0, num.lastIndexOf('.')+3):parseFloat(num).toFixed(2),
			del_price=num_to.lastIndexOf('.')>0 && num_to.length-num_to.lastIndexOf('.')-1>2?num_to.substring(0, num_to.lastIndexOf('.')+3):parseFloat(num_to).toFixed(2);
		$('#cur_price').text(ueeshop_config.currency_symbols+$('html').currencyFormat(cur_price, ueeshop_config.currency));
		$('.prod_info_price .price_0 del').text(ueeshop_config.currency+' '+ueeshop_config.currency_symbols+$('html').currencyFormat(del_price, ueeshop_config.currency));
		
		if($('.price_0').length && $('.price_1 .save_price').length){
			var dis=(cOld-cPrice)/cOld*100;
			if(dis>0){
				$('.price_1 .save_price').show();
				$('.price_1 .save_p').text(ueeshop_config.currency_symbols+$('html').currencyFormat(((cOld-cPrice)*parseFloat(ueeshop_config.currency_rate)).toFixed(2), ueeshop_config.currency));
				//$('.price_1 .save_style').text('('+Math.round((dis>0 && dis<1)?1:dis)+'% Off)');
				if($('#shopbox_outer').length){
					$('.price_1 .save_style').text(Math.round((dis>0 && dis<1)?1:dis)+'% OFF');
				}else{
					$('.price_1 .save_style').text('('+Math.round((dis>0 && dis<1)?1:dis)+'% Off)');
				}
			}else{
				$('.price_1 .save_price').hide();
			}
		}
		
		$CId=$('#CId').val();
		get_shipping_methods($CId, 1);
		
		//处理组合那一块
		var qty_data=$.evalJSON($(".quantity_box").attr("data"));
		$('.group_promotion .promotion_body .master .prod_price>input').attr({'curprice':cur_price*qty_data.min, 'oldprice':del_price*qty_data.min});
		$('.group_promotion .promotion_body.gp_list_purchase .group_curprice .price_data').attr('data', cur_price*qty_data.min);
		$('.group_promotion .promotion_body.gp_list_purchase .group_oldprice .price_data').attr('data', del_price*qty_data.min);
		$('.group_promotion .promotion_body.gp_list_purchase .group_saveprice .price_data').attr('data', parseFloat(del_price*qty_data.min)-parseFloat(cur_price*qty_data.min));
		$('.group_promotion .promotion_body.gp_list_purchase').each(function(){
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
	
	//检查当前属性库存的情况
	check_stock:function(){
		var attr_len, ext_attr,
			$attrStock=parseInt($("#attrStock").val()),
			tagName=this.get(0).tagName.toLowerCase(),
			$defaultStock=parseInt($("#quantity").attr('stock')),//产品默认库存
			option_ary=new Array, i=0;
		if(tagName=='ul'){ //主产品属性
			attr_len=this.find('li').length;
			if(attr_len && $("#ext_attr").length) ext_attr=$.evalJSON($("#ext_attr").val());//扩展属性
		}else{ //组合产品属性
			attr_len=this.find('dd').length;
			if(attr_len && this.find(".ext_attr").length) ext_attr=$.evalJSON(this.find(".ext_attr").val());//扩展属性
		}
		if(attr_len && $defaultStock==0){//产品总库存为0，就是缺货状态，所有属性不能勾选
			if(tagName=='dl' || this.find('li select').length){ //下拉
				this.find('option').addClass('hide hide_fixed').get(0).disabled=true;
			}else{ //按钮
				this.find('span').addClass('out_stock out_stock_fixed');
			}
		}
		//统计可选选项
		if(tagName=='dl' || this.find('li select').length){ //下拉
			//this.find('option[value="'+k+'"]')
			this.find('option').each(function(){
				option_ary[++i]=$(this).attr('value');
			});
		}else{ //按钮
			this.find('.attr_show span').each(function(){
				option_ary[++i]=$(this).attr('value');
			});
		}
		if(attr_len && $attrStock && $defaultStock>0){ //开启了0是库存为空的设定
			var ext_ary=new Object, ary=new Object, cur, stock_ary=new Object, _ary=new Object, j=0;
			for(k in ext_attr){
				ary=k.split('_');
				if(ary.length!=attr_len) continue;
				for(k2 in ary){
					if(!stock_ary[ary[k2]]) stock_ary[ary[k2]]=0;
				}
				if(ext_attr[k][1]>0){
					j=0;
					_ary=new Object;
					for(k2 in ary){
						if(!global_obj.in_array(ary[k2], option_ary)){
							j=1;
						}
						_ary[ary[k2]]=1;
					}
					if(j==0){
						for(k2 in _ary){
							stock_ary[k2]+=1;
						}
					}
				}
			}
			if(tagName=='dl' || this.find('li select').length){ //下拉
				for(k in stock_ary){
					if(stock_ary[k]<1){
						if(this.find('option[value="'+k+'"]').length) this.find('option[value="'+k+'"]').addClass('hide hide_fixed').get(0).disabled=true;
					}else{
						if(this.find('option[value="'+k+'"]').length) this.find('option[value="'+k+'"]').removeClass('hide hide_fixed').get(0).disabled=false;
					}
				}
			}else{ //按钮
				for(k in stock_ary){
					if(stock_ary[k]<1){
						if(this.find('span[value="'+k+'"]').length) this.find('span[value="'+k+'"]').addClass('out_stock out_stock_fixed');
					}else{
						if(this.find('span[value="'+k+'"]').length) this.find('span[value="'+k+'"]').removeClass('out_stock out_stock_fixed');
					}
				}
			}
		}
	},
	
	//添加购物车抛物线插件
	fly:function(t, callback){
		var e = this,
			t = $.extend({autoPlay:!0, vertex_Rtop:20, speed:1.2, start:{}, end:{}, onEnd:$.noop}, t),
			f = $(e),
			obj = {
				init:function(t){
					obj.setOptions(t);
					!!t.autoPlay && obj.move(t);
				},
				setOptions:function(t){
					var c=t,
						s=c.start,
						d=c.end;
						
					f.css({
						"border-top-left-radius": "50%",
						"border-top-right-radius": "50%",
						"border-bottom-right-radius": "50%",
						"border-bottom-left-radius": "50%",
						"width": 50,
						"height": 50,
						"background-image": "url("+$('.big_pic .normal').attr('src')+")",
						"background-size": "100%",
						"background-repeat": "no-repeat",
						"margin-top": 0,
						"margin-left": 0,
						"position": "absolute",
						"z-index": "1000000"
					}).appendTo("body"),
					null!=d.width && null!=d.height && $.extend(!0, s, {width:f.width(), height:f.height()});
					
					var h = Math.min(s.top, d.top) - Math.abs(s.left - d.left) / 3;
					h < c.vertex_Rtop && (h = Math.min(c.vertex_Rtop, Math.min(s.top, d.top)));
					
					var i = Math.sqrt(Math.pow(s.top - d.top, 2) + Math.pow(s.left - d.left, 2)),
						j = Math.ceil(Math.min(Math.max(Math.log(i) / .05 - 75, 30), 100) / c.speed),
						k = s.top == h ? 0 : -Math.sqrt((d.top - h) / (s.top - h)),
						l = (k * s.left - d.left) / (k - 1),
						m = d.left == l ? 0 : (d.top - h) / Math.pow(d.left - l, 2);
						
					$.extend(!0, c, {count:-1, steps:j, vertex_left:l, vertex_top:h, curvature:m});
				},
				move:function(t){
					var s = t.start,
						len = t.count,
						step = t.steps,
						d = t.end,
						h = s.left + (d.left - s.left) * len / step,
						i = 0 == t.curvature ? s.top + (d.top - s.top) * len / step: t.curvature * Math.pow(h - t.vertex_left, 2) + t.vertex_top;
					if(null != d.width && null != d.height){
						var j = step / 2,
							k = d.width - (d.width - s.width) * Math.cos(j > len ? 0 : (len - j) / (step - j) * Math.PI / 2),
							l = d.height - (d.height - s.height) * Math.cos(j > len ? 0 : (len - j) / (step - j) * Math.PI / 2);
						f.css({width:k+"px", height:l+"px", "font-size":Math.min(k, l)+"px"});
					}
					
					if(len==-1){
						f.css({left:h+"px", top:i+"px"});
						t.count++;
						obj.move(t);
					}else{
						f.animate({left:h+"px", top:i+"px"}, 5, function(){
							t.count++;
							//$('.search_r').html($.toJSON(t));
							if(len<step){
								obj.move(t);
							}else{
								t='';
								obj.destory();
							}
						});
					}
				},
				destory:function(){
					f.remove();
					callback();
				}
			}
		obj.init(t);
	}
});

(function($){
	var k=!1,
	a=function(){
		b();
		h();
		d();
		f();
		g();
		m();
		n();
		excheckout();
		tuan();
		custom();
	},
	b=function(){//价格显示
		set_amount=$(".quantity_box").set_amount();
		
		//检查当前所有属性库存的情况
		if($('ul.attributes').attr('data-combination')==1 && $('ul.attributes').attr('data-stock')==1){//规格组合
			$('ul.attributes').check_stock();
		}
		$('.group_promotion .promotion_body li dl.attribute[data-combination=1]').each(function(){
			$(this).check_stock();
		});
	},
	c=function(){//大图定位
		var $bigPic=$(".detail_pic"),
			$picShell=$bigPic.find(".pic_shell");
			$bigBox=$picShell.find(".big_box");
		
		$bigBox.css({width:$picShell.width(), height:$picShell.height()});
		$bigBox.css({height:$bigBox.find('.magnify .big_pic img').height()});
		pleft=($picShell.width()-$bigBox.find('.magnify .big_pic img').width())/2;
		ptop=($picShell.height()-$bigBox.height())/2;
		$bigBox.css({width:$bigBox.find('.magnify .big_pic img').width(), left:pleft, top:ptop});
	},
	h=function(){//大图loading
		$(".detail_left").height(350).loading();
		$.ajax({
			url:"/ajax/"+($(".detail_left").hasClass("prod_gallery_x")?"goods_detail_pic_row":"goods_detail_pic")+".html",
			async:false,
			type:'get',
			data:{"ProId":$("#ProId").val(), "ColorId":$(".colorid").length?$(".colorid").val():$(".attr_value").val(), "IsSeckill":$("input:hidden#IsSeckill").val(), "IsTuan":$("input:hidden#IsTuan").val()},
			dataType:'html',
			success:function(result){
				if(result){
					$(".detail_left").html(result);
				}
			}
		});
		
		$(".detail_left").height("auto").unloading();
		if(!$('#shopbox_outer').length){ //不是产品详细弹出框访问
			n_data=$.evalJSON($(".detail_pic .magnify").attr("data"));
			if($("input:hidden#IsSeckill").length){ //秒杀详情页
				if($(window).width()>=1250){
					magnify=$(".magnify").magnify({detailWidth:500,detailHeight:500,detailLeft:526});
				}else{
					magnify=$(".magnify").magnify({detailWidth:397,detailHeight:397,detailLeft:415});
				}
			}else if($("input:hidden#IsTuan").length){ //团购详情页
				
			}else{
				if($(window).width()>=1250){
					magnify=$(".magnify").magnify($.extend({detailWidth:453,detailHeight:453,detailLeft:465}, n_data));
				}else{
					magnify=$(".magnify").magnify($.extend({detailWidth:390,detailHeight:390,detailLeft:345}, n_data));
				}
			}
		}else{
			$(".big_pic").attr('href', 'javascript:;');
		}
		$(".big_pic img").load(function(){
			c();
		});
		d();
		
		if(!$('#shopbox_outer').length){
			$(window).resize(function(){ //网站宽度变动，更新宽度
				if($("input:hidden#IsSeckill").length){ //秒杀详情页
					if($(window).width()>=1250){
						magnify=$(".magnify").magnify({detailWidth:500,detailHeight:500,detailLeft:526});
					}else{
						magnify=$(".magnify").magnify({detailWidth:397,detailHeight:397,detailLeft:415});
					}
				}else if($("input:hidden#IsTuan").length){ //团购详情页
					
				}else{
					if($(window).width()>=1250){
						magnify=$(".magnify").magnify($.extend({detailWidth:453,detailHeight:453,detailLeft:465}, n_data));
					}else{
						magnify=$(".magnify").magnify($.extend({detailWidth:390,detailHeight:390,detailLeft:345}, n_data));
					}
				}
			});
		}
	},
	d=function(){//小图列表
		var $bigPic=$(".detail_pic"),
			$small=$bigPic.find('.small_carousel'),
			r, k;
		
		if($("input:hidden#IsSeckill").length){
			//秒杀详情页
			$small.carousel({itemsPerMove:1,height:376,width:60,duration:200,vertical:1,step:1});
			if($('.detail_pic .small_carousel .item').size()>5){
				$('.detail_pic .small_carousel .btn').show();
			}
		}else if($("input:hidden#IsTuan").length){
			//团购详情页
			$small.carousel({itemsPerMove:1,height:530,width:90,duration:200,vertical:1,step:1});
			if($('.detail_pic .small_carousel .item').size()>5){
				$('.detail_pic .small_carousel .btn').show();
			}
		}else if($("#shopbox_outer").length){
			//列表产品详细页
			$small.carousel({itemsPerMove:1,height:91,width:400,duration:200,vertical:!1,step:1});
		}else{
			//产品详细页
			if($(".detail_left").hasClass("prod_gallery_x")){
				$small.carousel({itemsPerMove:1,height:378,width:74,duration:200,vertical:1,step:1});
			}else{
				$small.carousel({itemsPerMove:1,height:91,width:318,duration:200,vertical:!1,step:1});
			}
		}
		$bigPic.on("mouseenter",".item a",function(t){
			r=$bigPic.find(".current");
			var i=$(this).parent();
			if(!i.hasClass("current")){
				r.removeClass("current");
				r=i;
				r.addClass("current");
				$bigPic.find(".big_pic").attr("href", $('#shopbox_outer').length?'javascript:;':$(this).find("img").attr("mask"));
				$bigPic.find(".normal").attr("src", $(this).find("img").attr("normal"));
			}
			return false;
		});
	},
	f=function(){//产品属性、其他执行事件
		var VId, attr_id, attr_ary=new Object,
			attr_hide=$("#attr_hide"),
			attr_len=$("ul.attributes li").length,
			ext_attr=$.evalJSON($("#ext_attr").val()),//扩展属性
			$attrStock=parseInt($("#attrStock").val()),
			attrSelected=parseInt($("ul.attributes").attr('default_selected')),//默认选择
			$sku_box=$(".prod_info_sku"),//SKU显示
			$defaultStock=parseInt($("#quantity").attr('stock')),//产品默认库存
			$IsCombination=$('ul.attributes').attr('data-combination'),//是否开启规格组合
			$IsStock=$('ul.attributes').attr('data-stock');//是否开启无限属性
		
		$(".attributes").on("change", "select", function(){
			VId=$(this).val();
			attr_id=$(this).attr("attr");
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
			ary.sort(function(a,b){ return a-b });
			cur_attr=ary.join('_');
			if(!ext_attr[cur_attr] && cur_attr=='Ov:1'){
				stock=$defaultStock;
				$('#inventory_number').text(stock);
			}else if(cur_attr && ext_attr[cur_attr]){
				stock=(!$attrStock && ext_attr[cur_attr][1]<1)?$defaultStock:ext_attr[cur_attr][1];
				parseInt($('input[name=SId]').val()) && parseInt($('input[name=SId]').attr('stock'))<=stock && (stock=$('input[name=SId]').attr('stock'));
				$('#inventory_number').text(stock);
			}else{
				//$('#inventory_number').text(0); //清空库存数字
				stock=$defaultStock;
				$('#inventory_number').text($defaultStock);
			}
			if($IsCombination==0){//关闭规格组合，固定显示默认库存
				stock=$defaultStock;
				$('#inventory_number').text($defaultStock);
			}
			
			//SKU显示
			var SKU='';
			if($sku_box){
				SKU=(ext_attr[cur_attr] && ext_attr[cur_attr][3])?ext_attr[cur_attr][3]:$sku_box.attr('sku');
				$sku_box.children('span').text(SKU);
				$IsCombination==0 && $sku_box.children('span').text($sku_box.attr('sku'));//关闭规格组合，固定显示自身SKU
				SKU?$sku_box.show():$sku_box.hide();
			}
			
			if($attrStock && $IsCombination==1){
				if(attr_hide.val()=='[]' || attr_hide.val()=='{}'){//组合属性都属于默认选项
					$('ul.attributes').check_stock(); //检查当前所有属性库存的情况
					$('#inventory_number').text($defaultStock);//还原库存
					stock=$defaultStock;
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
						$('ul.attributes #attr_'+no_attrid).find('option:gt(0)').each(function(){
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
							if(ext_attr[key][1]==0 && $IsStock==1){
								if($('ul.attributes option[value="'+value+'"]').length) $('ul.attributes option[value="'+value+'"]').addClass('hide').get(0).disabled=true;
							}else{
								if($('ul.attributes option[value="'+value+'"]').length) $('ul.attributes option[value="'+value+'"]').removeClass('hide').get(0).disabled=false;
							}
							if(VId==''){ //取消操作
								$('ul.attributes li').each(function(){
									if($(this).children('select').attr('attr')!=attr_id){
										$(this).find('option.hide').not('.hide_fixed').removeClass('hide').removeAttr('disabled');
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
								if(ext_attr[k][1]==0 && $IsStock==1){
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
								if($('ul.attributes option[value="'+k+'"]').length) $('ul.attributes option[value="'+k+'"]').addClass('hide').get(0).disabled=true;
							}else{
								if($('ul.attributes option[value="'+k+'"]').length) $('ul.attributes option[value="'+k+'"]').removeClass('hide').get(0).disabled=false;
							}
						}
					}else{ //勾选数 大于 1
						$('ul.attributes li').each(function(){
							$(this).find('option.hide').not('.hide_fixed').removeClass('hide').attr('disabled', false);
						});
					}
				}
			}
			
			if($IsCombination==1 && attr_len==2 && $('#attr_Overseas').length && ext_attr && ext_attr!='[]'){ //自动更新“属性数1个+组合属性+海外仓”的属性选项价格情况
				var select_ary=new Array, i=-1, ext_ary=new Object, ary=new Object, OverseaVal='', target='';
				for(k in attr_ary){
					if(k=='Overseas'){ //海外仓
						select_ary[++i]=attr_ary[k];
					}else{ //普通属性
						OverseaVal=attr_ary['Overseas'];
					}
					++i;
				}
				if(i==attr_len){ //勾选数 跟 属性总数 一致
					for(k in ext_attr){
						ary=k.split('_');
						for(k2 in ary){
							if(k2==0 && !ext_ary[ary[k2]]){ //属性1+海外仓选项
								ext_ary[ary[k2]]=ext_attr[ary[k2]+'_'+OverseaVal][0];
							}
						}
					}
					for(k in ext_ary){
						target=$('ul.attributes option[value="'+k+'"]');
						if(ext_ary[k]>0){ //有价格
							target.html(target.attr('data-title')+' (+'+(ueeshop_config.currency_symbols+$('html').currencyFormat(ext_ary[k], ueeshop_config.currency))+')');
						}else{ //丢失了价格
							target.html(target.attr('data-title'));
						}
					}
				}
			}
			
			$qtyBox=$(".quantity_box");
			qty_data=$.evalJSON($qtyBox.attr("data"));
			if(qty_data.max!=stock){//更新属性库存
				if(!stock || stock<1) stock=$defaultStock;
				qty_data.max=stock;
				$qtyBox.attr("data", $.toJSON(qty_data));
			}
			var Max=parseInt($("#quantity").attr('stock'));
			if(parseInt($("#quantity").val())>Max && Max>0){ //最大购买量
				global_obj.new_win_alert(lang_obj.products.warning_Max.replace('%num%', Max));
				$("#quantity").val(Max);
			}else if(parseInt($("#quantity").val())>parseInt(qty_data.max) && parseInt(qty_data.max)>0){ //库存
				global_obj.new_win_alert(lang_obj.products.warning_stock.replace('%num%', qty_data.max));
				$("#quantity").val(qty_data.max);
			}
			$qtyBox.get_price($("#quantity").val());
			
			if($(this).hasClass('colorid')){//颜色图片属性
				h();
				$('.FontPicArrowColor').css('border-color', 'transparent transparent '+$('.FontColor').css('color')+' transparent');
				$('.FontPicArrowXColor').css('border-color', 'transparent transparent transparent '+$('.FontColor').css('color'));
			}
		}).on("click touchstart", "span", function(e){//增加ipad触屏事件
			e.preventDefault();
			if($(this).hasClass("out_stock")){return false;}
			var $this=$(this),
				$obj=$this.parents(".attr_show").find("input"),
				$attrStock=parseInt($("#attrStock").val());
			
			if($this.hasClass("selected")){//取消操作
				$this.removeClass("selected");
				VId='';
			}else{//勾选操作
				$this.parent().find('span').removeClass('form_select_tips').addClass('GoodBorderColor');
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
			ary.sort(function(a,b){ return a-b });
			cur_attr=ary.join('_');
			if(!ext_attr[cur_attr] && cur_attr=='Ov:1'){
				stock=$defaultStock;
				$('#inventory_number').text(stock);
			}else if(cur_attr && ext_attr[cur_attr]){
				stock=(!$attrStock && ext_attr[cur_attr][1]<1)?$defaultStock:ext_attr[cur_attr][1];
				parseInt($('input[name=SId]').val()) && parseInt($('input[name=SId]').attr('stock'))<=stock && (stock=$('input[name=SId]').attr('stock'));
				$('#inventory_number').text(stock);
			}else{
				//$('#inventory_number').text(0);//清空库存数字
				stock=$defaultStock;
				$('#inventory_number').text($defaultStock);
			}
			if($IsCombination==0){//关闭规格组合，固定显示默认库存
				stock=$defaultStock;
				$('#inventory_number').text($defaultStock);
			}
			
			//SKU显示
			var SKU='';
			if($sku_box){
				SKU=(ext_attr[cur_attr] && ext_attr[cur_attr][3])?ext_attr[cur_attr][3]:$sku_box.attr('sku');
				$sku_box.children('span').text(SKU);
				$IsCombination==0 && $sku_box.children('span').text($sku_box.attr('sku'));//关闭规格组合，固定显示自身SKU
				SKU?$sku_box.show():$sku_box.hide();
			}
			
			if($attrStock && $IsCombination==1 && attr_len>1){
				if(attr_hide.val()=='[]' || attr_hide.val()=='{}'){//组合属性都属于默认选项
					$('ul.attributes').check_stock(); //检查当前所有属性库存的情况
					$('#inventory_number').text($defaultStock);//还原库存
					stock=$defaultStock;
				}else if(ext_attr && ext_attr!='[]'){//判断组合属性库存状态
					var select_ary=new Array, i=-1, ext_ary=new Object, ary=new Object, cur, no_stock_ary=new Object;
					for(k in attr_ary){
						select_ary[++i]=attr_ary[k];
					}
					if(select_ary.length == attr_len-1){ //勾选数 比 属性总数 少一个
						var no_attrid=0, attrid=0, _select_ary, key;
						$('ul.attributes li').each(function(){
							attrid=$(this).children('.attr_value').attr('attr');
							if(!attr_ary[attrid]){
								no_attrid=attrid; //没有勾选的属性ID
							}
						});
						$('ul.attributes #attr_'+no_attrid).siblings('span').each(function(){
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
							if(ext_attr[key][1]==0 && $IsStock==1){
								if($('ul.attributes span[value="'+value+'"]').length) $('ul.attributes span[value="'+value+'"]').addClass('out_stock');
							}else{
								if($('ul.attributes span[value="'+value+'"]').length) $('ul.attributes span[value="'+value+'"]').removeClass('out_stock');
							}
							if(VId==''){ //取消操作
								$('ul.attributes li').each(function(){
									if($(this).children('.attr_value').attr('attr')!=attr_id){
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
								if(ext_attr[k][1]==0 && $IsStock==1){
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
								if($('ul.attributes span[value="'+k+'"]').length) $('ul.attributes span[value="'+k+'"]').addClass('out_stock');
							}else{
								if($('ul.attributes span[value="'+k+'"]').length) $('ul.attributes span[value="'+k+'"]').removeClass('out_stock');
							}
						}
					}else{ //勾选数 大于 1
						$('ul.attributes li').each(function(){
							$(this).find('span.out_stock').not('.out_stock_fixed').removeClass('out_stock');
						});
					}
				}
			}
			
			$qtyBox=$(".quantity_box");
			qty_data=$.evalJSON($qtyBox.attr("data"));
			if(qty_data.max!=stock){//更新属性库存
				if(!stock || stock<1) stock=$defaultStock;
				qty_data.max=stock;
				$qtyBox.attr("data", $.toJSON(qty_data));
			}
			var Max=parseInt($("#quantity").attr('stock'));
			if(parseInt($("#quantity").val())>Max && Max>0){ //最大购买量
				global_obj.new_win_alert(lang_obj.products.warning_Max.replace('%num%', Max));
				$("#quantity").val(Max);
			}else if(parseInt($("#quantity").val())>parseInt(qty_data.max) && parseInt(qty_data.max)>0){ //库存
				global_obj.new_win_alert(lang_obj.products.warning_stock.replace('%num%', qty_data.max));
				$("#quantity").val(qty_data.max);
			}
			$qtyBox.get_price($("#quantity").val());
			
			if($obj.hasClass('colorid')){//颜色图片属性
				h();
				$('.FontPicArrowColor').css('border-color', 'transparent transparent '+$('.FontColor').css('color')+' transparent');
				$('.FontPicArrowXColor').css('border-color', 'transparent transparent transparent '+$('.FontColor').css('color'));
			}
		});
		
		//发货地仅有“中国”一个，就自动默认执行
		if($(".attributes .attr_show").length){
			var obj=$('#attr_Overseas').parent().find("span").not(".out_stock").eq(0);
			if(!obj.hasClass('selected')){
				obj.click();
			}
		}
		if($(".attributes li select").length){
			var obj=$('#attr_Overseas').find("option[value!='']").not(".hide").eq(0);
			if(obj.is(':selected')===false){
				$('#attr_Overseas').find("option[value!='']").not(".hide").eq(0).attr("selected", "selected").change();
				$('#attr_Overseas').find("option:eq(0)").remove();
			}
		}
		
		//购物车属性以选择按钮显示，默认执行第一个选项
		if(attrSelected && $(".attributes .attr_show").length){
			$(".attributes .attr_show").each(function(){
				if($(this).find('input:hidden').attr('id')!='attr_Overseas'){
					$(this).find("span").not(".out_stock").eq(0).click();
				}
			});
		}
		if(attrSelected && $(".attributes li select").length){
			$(".attributes li select").each(function(){
				if($(this).attr('id')!='attr_Overseas'){
					$(this).find("option[value!='']").not(".hide").eq(0).attr("selected", "selected").change();
					$(this).find("option:eq(0)").remove();
				}
			});
		}
		
		//购物车属性提示框，关闭事件
		$('.attr_sure_close').click(function(){
			if($('.attributes_tips').length){
				$('.attributes').removeClass('attributes_tips');
			}
		});
		
		$('.prod_info_currency li').delegate('a', 'click', function(){
			var v=$(this).attr('data');
			$.post('/', 'do_action=action.currency&currency='+v, function(data){
				if(data.ret==1){
					window.top.location.reload();
				}
			}, 'json');
		});
		
		$(".prod_info_pdf").click(function(){//PDF打印
			var http_str = 'http';
			if(window.location.href.match("https")) http_str = 'https';
			$("#export_pdf").attr("src", http_str+"://pdfmyurl.com?url="+window.location.href.replace(/^http[s]?:\/\//, ""));
		});
		
		$(".prod_info_data a").click(function(){
			$("body, html").animate({scrollTop:$(".prod_description").offset().top}, 500);
			$(".prod_description .pd_title span[data="+$(this).attr("data")+"]").click();
		});
		
		$(".prod_description ul span").on("click", function(){
			//$(".prod_description .desc").eq($(this).parent().index()).removeClass("hide").siblings().addClass("hide");
			$(".prod_description .desc").eq($(this).parent().index()).removeClass("hide").siblings().addClass("hide");
			$(this).parent().addClass("current").siblings().removeClass("current");
		});
		
		$('.share_toolbox .share_s_btn').on('click', function(){//分享
			var $obj=$('.share_toolbox');
			if(!$(this).hasClass('share_s_more')){
				$(this).shareThis($(this).attr('data'), $obj.attr('data-title'), $obj.attr('data-url'));
			}
		});
	},
	g=function(){//提交验证
		$('#addtocart_button, #buynow_button, #paypal_checkout_button').css('display', 'block');//等待页面加载完才显示，以防客户直接打开出错误的页面。
		//到货通知
		$('#arrival_button').on('click', function(){
			$.post('/', 'do_action=action.arrival_notice&ProId='+$('#ProId').val(), function(data){
				if(data.ret){
					if(data.ret==1){
						global_obj.new_win_alert(lang_obj.cart.arrival_info_0, '', '', undefined, '');
					}else if(data.ret==2){
						global_obj.new_win_alert(lang_obj.cart.arrival_info_1);
					}else{
						global_obj.new_win_alert(lang_obj.cart.arrival_info_2);
					}
				}
			}, 'json');
		});
		
		//Buy Now
		$('#buynow_button').on('click', function(){
			var subObj=$("#goods_form"),
				$attr_name='',
				result=0;
			subObj.find("select").each(function(){
				if(!$(this).val()){
					if(!$attr_name) $attr_name=$(this).parents('li').attr('name');
					result=1;
				}
			});
			subObj.find("input.attr_value").each(function(){
				if(!$(this).val()){
					if(!$attr_name) $attr_name=$(this).parents('li').attr('name');
					result=1;
				}
			});
			
			$('.attributes').removeClass('attributes_tips');
			if(result){
				$('.attributes').addClass('attributes_tips');
				return false;
			}
			
			$.post('/', $('#goods_form').serialize()+'&do_action=cart.additem&IsBuyNow=1&back=1', function(data){
				if(data.ret==1){
					parseInt(ueeshop_config.FbPixelOpen)==1 && $('html').fbq_addtocart(data.msg.item_price);
					//BuyNow统计
					analytics_click_statistics(1);//暂时统计为添加购物车事件
					$.post('/?do_action=cart.check_low_consumption&t='+Math.random(), {'CId':data.msg.CId}, function(json){ //最低消费金额判断
						if(json.ret==1){ //符合
							if($('#shopbox_outer').length){//产品详细弹出框
								$(window.parent.window).get(0).location.href=data.msg.location;
							}else{//产品详细页
								window.location.href=data.msg.location;
							}
						}else{ //不符合
							var tips=(lang_obj.cart.consumption).replace('%low_price%', ueeshop_config.currency_symbols+$('html').currencyFormat(json.msg.low_price, ueeshop_config.currency)).replace('%difference%', ueeshop_config.currency_symbols+$('html').currencyFormat(json.msg.difference, ueeshop_config.currency));
							global_obj.new_win_alert(tips, function(){ $('#buynow_button').attr('disabled', false) }, '', undefined, 'await');
						}
					}, 'json');
				}else{
					$('#buynow_button').attr('disabled', false);
					if($('#shopbox_outer').length){//产品详细弹出框
						var parentObj=$(window.parent.document);
						parentObj.find('#shopbox').shopboxHide();
					}
				}
			}, 'json');
			return false;
		});
		
		$("#goods_form").submit(function(){return false});
		$('#addtocart_button').on('click', function(){
			var subObj=$("#goods_form"),
				$attr_name='',
				result=0;
			
			if($(this).attr('disabled')) return false;
			subObj.find("select").each(function(){
				if(!$(this).val()){
					if(!$attr_name) $attr_name=$(this).parents('li').attr('name');
					result=1;
				}
			});
			subObj.find(".attr_value").each(function(){
				if(!$(this).val()){
					if(!$attr_name) $attr_name=$(this).parents('li').attr('name');
					result=1;
				}
			});
			$('.attributes').removeClass('attributes_tips');
			if(result){
				$('.attributes').addClass('attributes_tips');
				return false;
			}
			
			$(this).attr('disabled', true);
			var offset = $('.cart_inner').offset(),
				btnLeft = $(this).offset().left+$(this).outerWidth(true)/3,
				btnTop = $(this).offset().top-$(this).outerHeight(true)-30,
				flyer = $('<div class="addtocart_flyer"></div>');
			
			$.post('/', $('#goods_form').serialize()+'&do_action=cart.additem&back=1', function(data){
				data=$.evalJSON(data);
				if(data.ret==2){
					$(this).attr('disabled', false);
					alert(data.msg);
				}else if(data.ret==1){
					//加入购物车统计
					analytics_click_statistics(1);
					parseInt(ueeshop_config.FbPixelOpen)==1 && $('html').fbq_addtocart(data.msg.item_price);
					if($('#shopbox_outer').length){//产品详细弹出框
						var parentObj=$(window.parent.document);
						parentObj.find('#shopbox').shopboxHide();

						var excheckout_html='<div class="new_win_alert addtocart_alert">';
								excheckout_html+='<div class="win_close"><button class="close"></button></div>';
								excheckout_html+='<div class="win_tips"><i class="icon_success_status"></i>'+lang_obj.cart.additem_0+'</div>';
								if(data.msg.FullCondition[0]==1){
									excheckout_html+='<div class="fulldis">'+data.msg.FullCondition[1]+'</div>';
								}
								excheckout_html+='<div class="win_btns"><a href="/cart/" class="btn btn_sure">'+lang_obj.cart.proceed_checkout+'</a>';
								excheckout_html+='<button class="btn btn_cancel">'+lang_obj.cart.return_shopping+'</button>';
								excheckout_html+='<div class="clear"></div>';
								excheckout_html+='</div>';
							excheckout_html+='</div>';
						
						parentObj.find('.new_win_alert').length && parentObj.find('.new_win_alert').remove();
						parentObj.find('body').prepend(excheckout_html);
						parentObj.find('.new_win_alert').css({left:$(window.parent.window).width()/2-220,top:'30%'});
						
						//关闭提示框
						parentObj.find('body').delegate('#div_mask, .new_win_alert .btn_cancel, .new_win_alert .close', 'click', function(){
							if(parentObj.find('.new_win_alert').length){
								parentObj.find('.new_win_alert').remove();
								parentObj.find('#div_mask').remove();
							}
						});
						parentObj.find('.header_cart .cart_count').html(data.msg.qty);
					}else{
						if(!$('.header_cart .cart_note').length){
							$('#addtocart_button').attr('disabled', false);
							$('.header_cart .cart_count').text(data.msg.qty);
							$('.header_cart .cart_count_price').text(data.msg.total_price);
							$('.header_cart .cart_note').html('');
							
							var excheckout_html='<div class="new_win_alert addtocart_alert">';
									excheckout_html+='<div class="win_close"><button class="close"></button></div>';
									excheckout_html+='<div class="win_tips"><i class="icon_success_status"></i>'+lang_obj.cart.additem_0+'</div>';
									if(data.msg.FullCondition[0]==1){
										excheckout_html+='<div class="fulldis">'+data.msg.FullCondition[1]+'</div>';
									}
									excheckout_html+='<div class="win_btns"><a href="/cart/" class="btn btn_sure">'+lang_obj.cart.proceed_checkout+'</a>';
									excheckout_html+='<button class="btn btn_cancel">'+lang_obj.cart.return_shopping+'</button>';
									excheckout_html+='<div class="clear"></div>';
									excheckout_html+='</div>';
								excheckout_html+='</div>';
							
							$('.new_win_alert').length && $('.new_win_alert').remove();
							$('body').prepend(excheckout_html);
							$('.new_win_alert').css({left:$(window).width()/2-200,top:'30%'});
							global_obj.div_mask();
							
							//关闭提示框
							$('body').delegate('#div_mask, .new_win_alert .btn_cancel, .new_win_alert .close', 'click', function(){
								if($('.new_win_alert').length){
									$('.new_win_alert').remove();
									$('#div_mask').remove();
								}
							});
							// flyer.fly({start:{left:btnLeft, top:btnTop, width:50, height:50}, end:{left:offset.left+30, top:offset.top, width:20, height:20}}, function(){
							// });
						}else{
							var h_c = $('.header_cart');
							$('#addtocart_button').attr('disabled', false);
							$('.header_cart .cart_count').text(data.msg.qty);
							$('.header_cart .cart_count_price').text(data.msg.total_price);
							$('.header_cart .cart_note').html('');
							h_c.removeAttr('is_animate').attr('status','1').mouseenter();
							var	top_y = h_c.offset().top+h_c.height()+parseInt(h_c.css('border-top-width'))+parseInt(h_c.css('border-bottom-width'))+parseInt(h_c.css('padding-top'))+parseInt(h_c.css('padding-bottom'));
							function auto_postion(){
								var scrolltop = document.documentElement.scrollTop || document.body.scrollTop;
								if(scrolltop>top_y){
									var right_x = $(window).width()-(h_c.offset().left+h_c.width()+parseInt(h_c.css('border-left-width'))+parseInt(h_c.css('border-right-width'))+parseInt(h_c.css('padding-left'))+parseInt(h_c.css('padding-right')));//固定定位距离右边的距离
									$('.header_cart .cart_note').css({'position':'fixed','top':'0','right':right_x+'px'});
								}else{
									$('.header_cart .cart_note').attr('style','');
									if(h_c.attr('status')){
										$('.header_cart .cart_note').show();
									}
								}
							}
							$(window).scroll(function(){
								auto_postion();
							});
							auto_postion();
							var cart_list = setTimeout(function(){
								h_c.removeAttr('status').removeAttr('is_animate').removeClass('header_active').find('.cart_note').hide();
							},3000);
							$('.down_header_cart').hover(function(){
								clearTimeout(cart_list);
							},function(){
								h_c.removeAttr('status').removeAttr('is_animate').removeClass('header_active').find('.cart_note').hide();
							});
						}
					}
				}else{
					$('#addtocart_button').attr('disabled', false);
					if($('#shopbox_outer').length){//产品详细弹出框
						var parentObj=$(window.parent.document);
						parentObj.find('#shopbox').shopboxHide();
					}
				}
			});
			return false;
		});
	},
	m=function(){//组合产品
		$('.gp_list_promotion').each(function(){//组合促销其中一个产品没有库存都要下架
			if(parseInt($(this).find('.not_prod_number').attr('value'))>0){
				//$(this).remove();
				//$('.gp_title span[data=promotion][data-id='+$(this).attr('data-id')+']').parent().remove();
			}
		});
		if(!$('.gp_list li').length) $('.group_promotion').remove();
		
		$('.group_promotion .gp_title span').off().on('click', function(){
			var $obj=$('.promotion_body[data-id='+$(this).attr('data-id')+']');
			var suits_ulW=$obj.find('.suits li').outerWidth(true)*($obj.find('.suits li').size());
			var suitsW=$('.group_promotion .suits').outerWidth(true);
			$obj.removeClass('hide').siblings().addClass('hide');
			$(this).parent().parent().find('span').removeClass('FontColor');
			$(this).addClass('FontColor').parent().addClass('current FontBorderColor').siblings().removeClass('current FontBorderColor');
			$obj.find('.suits ul').css({'width':suits_ulW});
			$obj.find('.suits').css({'overflow-x':(suits_ulW>suitsW?'scroll':'hidden')});
		});
		$('.group_promotion .gp_title li:eq(0)>span').click();
		
		$(window).resize(function(){//网站宽度变动，更新宽度
			$('.group_promotion .gp_title span').each(function(){
				var $obj=$('#gp_list_'+$(this).attr('data'));
				var suits_ulW=$obj.find('.suits li').outerWidth(true)*($obj.find('.suits li').size());
				$obj.find('.suits ul').css({'width':suits_ulW});
			});
		});
		
		$(".group_promotion .attribute").on("change", "select", function(){
			var VId, attr_id, cur_attr, attr_ary=new Object, s='', ary=new Array,
				i=stock=attr_price=0,
				$parent=$(this).parents('.attribute'),
				$attr_hide=$parent.children(".attr_hide"),
				ext_attr=$.evalJSON($parent.children(".ext_attr").val());//扩展属性
				attr_len=$parent.children("dd").length,//当前属性数量
				$attrStock=parseInt($("#attrStock").val()),
				$checkbox=$parent.parent().find('input:checkbox'),
				$isSeckill=parseInt($checkbox.attr('isSeckill')),
				price=parseFloat($checkbox.attr('price')),
				moq=parseInt($checkbox.attr('moq')),
				isSales=parseFloat($checkbox.attr("sales")),//是否开启产品促销
				salesP=parseFloat($checkbox.attr("salesPrice")),//产品促销现金
				disCount=parseInt($checkbox.attr("discount")),//促销折扣
				IsCombination=$parent.attr('data-combination');//是否开启规格组合
			
			VId=$(this).val();
			attr_id=$(this).attr("attr");
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
					ary.sort(function(a,b){ return a-b });
					s=ary.join('_');
					if(ext_attr[s] && attr_len==$parent.children('dd').length){
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
						//ext_value=ext_attr[attr_ary[k]];
						ext_value=ext_attr[attr_ary[k]+'_Ov:1'];
						if(ext_value) price+=parseFloat(ext_value[0])*parseFloat(ueeshop_config.currency_rate);//固定是加价
					}
				}
			}
			if(isSales && salesP && price>salesP){//当前价格与促销现金相冲突的情况下，以最低价优先
				price=salesP;
			}
			price=price+attr_price;
			if(!$isSeckill && disCount){//促销折扣(会员价+属性价格)
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
		
		$('.group_promotion .promotion_body').each(function(){
			$(this).find('.attribute').each(function(){
				if($(this).find('select').length){
					$(this).find("#attr_Overseas option[value!='']").not('.hide').eq(0).attr('selected', 'selected').change();
					$(this).find('#attr_Overseas option:eq(0)').remove();
				}
			});
		});
		
		$('.group_promotion').on('click', 'input:checkbox', function(){
			var $obj=$(this).parents('.promotion_body'),
				$totalPrice=0, $oldPrice=0, $num=0;
			
			$obj.find('input:checkbox:checked').each(function(){
				$totalPrice+=parseFloat($(this).attr('curprice'));
				$oldPrice+=parseFloat($(this).attr('oldprice'));
				$num+=1;
			});
			$obj.find('.group_nums').text($num-1);
			$obj.find('.group_curprice .price_data').text($('html').currencyFormat($totalPrice.toFixed(2), ueeshop_config.currency));
			$obj.find('.group_oldprice .price_data').text($('html').currencyFormat($oldPrice.toFixed(2), ueeshop_config.currency));
			$obj.find('.group_saveprice .price_data').text($('html').currencyFormat(($oldPrice-$totalPrice).toFixed(2), ueeshop_config.currency));
		});
		
		$('.group_promotion').on('click', '.gp_btn', function(){
			var $obj=$(this).parents('.promotion_body'),
				$PId=$obj.find('input[name=PId]').val(),
				$data_id=$obj.attr('data-id'),
				id_where=attr_where='', attr_obj=new Object, $num=0, $data;
			
			if($obj.hasClass('gp_list_promotion')){//组合促销
				var Attr=$obj.find('.master_attr_hide').val();
			}else{//组合购买
				//判断主产品
				var subObj=$('#goods_form'),
					result=0;
				subObj.find('select').each(function(){
					if(!$(this).val()){
						result=1;
					}
				});
				subObj.find('.attr_value').each(function(){
					if(!$(this).val()){
						result=1;
					}
				});
				$('.attributes').removeClass('attributes_tips');
				if(result){
					$('.attributes').addClass('attributes_tips');
					$('body,html').animate({scrollTop:subObj.find('.attributes').offset().top}, 500);
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
							$(this).css('border','1px #ccc solid');
							if(!$(this).val()){
								$(this).css('border','1px red solid');
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
					}else{
						if($('#shopbox_outer').length){//产品详细弹出框
							var parentObj=$(window.parent.document);
							parentObj.find('#shopbox').shopboxHide();
							
							user_obj.set_form_sign_in('parent');
						}else{
							user_obj.set_form_sign_in();
							$('form[name=signin_form]').append('<input type="hidden" name="comeback" value="global_obj.div_mask(1);$(\'#signin_module\').remove();$(\'.promotion_body[data-id='+$data_id+'] .gp_btn\').click();" />');
						}
					}
				}
			});
			return false;
		});
	}
	n=function(){
		/**** 运费查询 Start ****/
		$('#shipping_cost_button').click(function(){
			if($(this).attr('disabled')) return false;
			$(this).blur().attr('disabled', 'disabled');
			$.ajax({
				type: "GET",
				url: "/?do_action=cart.get_excheckout_country",
				data: '&Type=shipping_cost&ProId='+$('#ProId').val()+'&Qty='+$('#quantity').val()+'&Attr='+$('#attr_hide').val()+'&proType='+$('input[name=products_type]').val()+'&SId='+$('input[name=SId]').val(),
				dataType: "json",
				success: function(data){
					if(data.ret==1){
						var c=data.msg.country,
							h=data.msg.hot_country,
							country_select='',
							defaultCId=226,
							CId=parseInt($('#CId').val()),
							s=0, f, d;
						h.length>0 && (country_select+='<optgroup label="---------">');
						for(i=0; i<h.length; i++){ //热门国家
							if((!CId && h[i].IsDefault==1) || CId==h[i].CId) defaultCId=h[i].CId;
							defaultCId==h[i].CId && (s=1);
							f=h[i].FlagPath?' path="'+h[i].FlagPath+'"':'';
							d=$.evalJSON(h[i].CountryData);
							country_select+='<option value="'+h[i].CId+'" acronym="'+h[i].Acronym+'" '+f+(defaultCId==h[i].CId?'selected':'')+'>'+(d?d[ueeshop_config.lang]:h[i].Country)+'</option>';
						}
						h.length>0 && (country_select+='</optgroup><optgroup label="---------">');
						for(i=0; i<c.length; i++){ //国家列表
							if((!CId && c[i].IsDefault==1) || CId==c[i].CId) defaultCId=c[i].CId;
							f=c[i].FlagPath?' path="'+c[i].FlagPath+'"':'';
							d=$.evalJSON(c[i].CountryData);
							country_select+='<option value="'+c[i].CId+'" acronym="'+c[i].Acronym+'" '+f+(s==0 && defaultCId==c[i].CId?'selected':'')+'>'+(d?d[ueeshop_config.lang]:c[i].Country)+'</option>';
						}
						h.length>0 && (country_select+='</optgroup>');
						var ProductPrice=$('form[name=prod_info_form] input[name=ItemPrice]').val();

						var excheckout_html='<div id="alert_choose" class="alert_choose shipping_cost">';
								excheckout_html+='<div class="box_bg"></div><a class="noCtrTrack" id="choose_close"></a>';
								excheckout_html+='<div class="choose_content">';
									excheckout_html+='<form name="shipping_cost_form" target="_blank" method="POST" action="">';
										excheckout_html+='<h2><div class="box_select">';
											excheckout_html+='<select name="CId"><option value="0">'+lang_obj.products.select_country+'</option>'+country_select+'</select>';
										excheckout_html+='</div></h2>';
										excheckout_html+='<div id="shipping_method_list" class="payment_list"></div>';
										excheckout_html+='<p class="footRegion">';
											excheckout_html+='<input class="btn BuyNowBgColor" id="excheckout_button" type="submit" value="'+lang_obj.global.ok+'" />';
										excheckout_html+='</p>';
									excheckout_html+='<input type="hidden" name="ProductPrice" value="' + ProductPrice + '" /><input type="hidden" name="ShippingMethodType" value="" /><input type="hidden" name="ShippingPrice" value="0" /><input type="hidden" name="ShippingExpress" value="" /><input type="hidden" name="ShippingBrief" value="" /></form>';
								excheckout_html+='</div>';
							excheckout_html+='</div>';
						
						$('#alert_choose').length && $('#alert_choose').remove();
						$('body').prepend(excheckout_html);
						$('#alert_choose').css({left:$(window).width()/2-285,top:'20%'});
						
						global_obj.div_mask();
						get_shipping_methods(defaultCId);
						
						$('#choose_close, #div_mask, #exback_button').off().on('click', function(){
							if($('#alert_choose').length){
								$('#alert_choose').remove();
								global_obj.div_mask(1);
								$('#shipping_cost_button').removeAttr('disabled');
							}
						});
					}else{
						global_obj.new_win_alert(lang_obj.products.sign_in, function(){window.top.location='/account/login.html';});
					}
				}
			});
		});
		
		//选择国家操作
		$('html').on('change', 'form[name=shipping_cost_form] select[name=CId]', function(){
			get_shipping_methods($(this).val());
		});
		
		//选择快递操作
		$('html').on('click', 'form[name=shipping_cost_form] input[name=SId]', function(){
			var price=parseFloat($(this).attr('price'));
			var insurance=parseFloat($(this).attr('insurance'));
			$('form[name=shipping_cost_form] input[name=ShippingMethodType]').val($(this).attr('ShippingType'));
			$('#shipping_method_list .insurance span.price').text(ueeshop_config.currency_symbols + insurance.toFixed(2));
			
			$('form[name=shipping_cost_form] input[name=ShippingExpress]').val($(this).attr('method'));
			$('form[name=shipping_cost_form] input[name=ShippingBrief]').val($(this).attr('brief'));
			$('form[name=shipping_cost_form] input[name=ShippingPrice]').val(price.toFixed(2));
		});
		
		//提交运费查询
		$('html').on('submit', 'form[name=shipping_cost_form]', function(){
			var obj=$('form[name=shipping_cost_form]');
			obj.find('input[type=submit]').attr('disabled', 'disabled').blur();
			if(!obj.find('input[name=SId]:checked').val() && obj.find('input[name=ShippingMethodType]').val()==''){
				alert('Please select a shipping method!');
				$('#excheckout_button').removeAttr('disabled');
				return false;
			}
			
			var shipping_price=$('form[name=shipping_cost_form] input[name=ShippingPrice]').val();
			if(shipping_price>0){
				$('.shipping_cost_detail, .shipping_cost_info').css('display', '');
				$('.shipping_cost_error').css('display', 'none');
				$('.shipping_cost_price').text(ueeshop_config.currency_symbols+shipping_price);
			}else if(shipping_price==0){
				$('.shipping_cost_detail, .shipping_cost_info').css('display', '');
				$('.shipping_cost_error').css('display', 'none');
				$('.shipping_cost_price').text(lang_obj.products.free_shipping);
			}else{
				$('.shipping_cost_info').css('display', 'none');
				$('.shipping_cost_detail, .shipping_cost_error').css('display', '');
				$('.shipping_cost_price').text('--');
			}
			$('.shipping_cost_info .delivery_day').text($('form[name=shipping_cost_form] input[name=ShippingBrief]').val());
			var $selectObj=$('form[name=shipping_cost_form] select[name=CId] option:selected');
			var country_name=$selectObj.text();
			var acronym_name=$selectObj.attr('acronym');
			var flagpath=$selectObj.attr('path');
			var express_name=$('form[name=shipping_cost_form] input[name=ShippingExpress]').val();
			console.log(express_name);
			$('#CId').val($('form[name=shipping_cost_form] select[name=CId]').val());
			$('#CountryName').val(country_name);
			$('#CountryAcronym').val(acronym_name);
			$('#ShippingId').val($('form[name=shipping_cost_form] input[name=SId]:checked').val());
			
			$('#alert_choose').hide();
			if(flagpath){
				$('#shipping_flag').removeClass().html('<img src="'+flagpath+'" />');
			}else{
				$('#shipping_flag').removeClass().addClass('icon_flag flag_'+acronym_name.toLowerCase());
			}
			$('#shipping_cost_button').text(country_name+' '+lang_obj.products.via+' '+express_name).attr('title', country_name+' '+lang_obj.products.via+' '+express_name).removeAttr('disabled');
			global_obj.div_mask(1);
			
			return false;
		});
		
		$CId=$('#CId').val();
		get_shipping_methods($CId, 1);
		/**** 运费查询 End ****/
	},
	get_shipping_methods=function(CId, Method){
		$.post('/?do_action=cart.get_shipping_methods', 'CId='+CId+'&Type=shipping_cost&ProId='+$('#ProId').val()+'&Qty='+$('#quantity').val()+'&Attr='+$('#attr_hide').val(), function(data){
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
							var sed='checked';
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
							var sed='';
						}
						/*
						<div class="item"><input type="radio" name="PId" value="1" fee="10.00" affix="10.00" checked="checked"><span class="pic_box"><img src="/u_file/1509/photo/70138653c5.jpg"><span></span></span><span class="name">Paypal aa ( +$14.65 )</span></div>*/
						rowStr+='<div class="item" name="'+rowObj.Name.toUpperCase()+'"><label for="__SId__'+i+'">';
						rowStr+=	'<input type="radio" id="__SId__'+i+'" name="SId" value="'+rowObj.SId+'" method="'+rowObj.Name +'" price="'+rowObj.ShippingPrice+'" brief="'+ rowObj.Brief+'" insurance="'+ rowObj.InsurancePrice+'" ShippingType="'+rowObj.type+'" '+sed+' />';
						rowStr+=	'<span class="name">'+rowObj.Name +'</span>';
						if(rowObj.IsAPI>0){
							if(rowObj.ShippingPrice>0){
								rowStr+='<span class="price">--</span>';
							}else rowStr+='<span class="price">'+lang_obj.products.free_shipping+'</span>';
						}else{
							rowStr+='<span class="price">'+(rowObj.ShippingPrice>0?ueeshop_config.currency_symbols+rowObj.ShippingPrice:lang_obj.products.free_shipping)+'</span>';
						}
						rowStr+=	'</label>';
						rowStr+=	'<div class="desc">'+rowObj.Brief+'</div>';
						rowStr+='</div>';
						++j;
					}
				}
				rowObj=data.msg.info[OvId][start]; //重新定义覆盖
				if(_SId==0 && rowObj){ //没有任何快递勾选的情况，自动勾选第一个选项
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
					$('#shipping_flag').addClass('flag_'+$('#CountryAcronym').val().toLowerCase());
					$('#shipping_cost_button').text($('#CountryName').val()+' '+lang_obj.products.via+' '+shipMethod).attr('title', $('#CountryName').val()+' '+lang_obj.products.via+' '+shipMethod);
					$('#ShippingId').val(SId);
				}else{
					if(rowStr!=''){
						var checkObj=$(rowStr).find('input[name=SId]:checked');
						if(checkObj.attr('method')!=shipMethod){
							shipType=checkObj.attr('ShippingType');
							shipMethod=checkObj.attr('method');
							ShippingInsurance=1;
							shipPrice=parseFloat(checkObj.attr('price'));
							insurance=parseFloat(checkObj.attr('insurance'));
							shipBrief=checkObj.attr('brief');
						}else{
							shipType=rowObj.type;
							shipMethod=rowObj.Name;
							ShippingInsurance=1;
							shipPrice=parseFloat(rowObj.ShippingPrice);
							insurance=parseFloat(rowObj.InsurancePrice);
							shipBrief=rowObj.Brief;
						}
					}else{
						rowStr+='<div class="item"><strong>'+lang_obj.products.no_optional+'!</strong></div>';
					}
					
					$('form[name=shipping_cost_form] input[name=ShippingExpress]').val(shipMethod);
					$('form[name=shipping_cost_form] input[name=ShippingMethodType]').val(shipType);
					$('form[name=shipping_cost_form] input[name=ShippingPrice]').val(shipPrice.toFixed(2));
					$('form[name=shipping_cost_form] input[name=ShippingBrief]').val(shipBrief);
					$('#shipping_method_list').html(rowStr);
				}
				
				start!=0 && SId!=rowObj.SId && $('form[name=shipping_cost_form] .item:eq(0) input[name=SId]').click(); //原来勾选的选项丢失，并且不是默认第一个
			}else{
				$('.shipping_cost_info').css('display', 'none');
				$('.shipping_cost_detail, .shipping_cost_error').css('display', '');
				$('.shipping_cost_price').text('--');
				$('#shipping_flag').addClass('flag_'+$('#CountryAcronym').val().toLowerCase());
				$('#shipping_cost_button').text($('#CountryName').val()).attr('title', $('#CountryName').val());
				$('#shipping_method_list').html('');
			}
		}, 'json');
	},
	excheckout=function(){ //paypal快捷支付部分
		$('#paypal_checkout_button').click(function(){
			var subObj=$("#goods_form"),
				$attr_name='',
				result=0;
			subObj.find("select").each(function(){
				if(!$(this).val()){
					if(!$attr_name) $attr_name=$(this).parents('li').attr('name');
					result=1;
				}
			});
			subObj.find("input.attr_value").each(function(){
				if(!$(this).val()){
					if(!$attr_name) $attr_name=$(this).parents('li').attr('name');
					result=1;
				}
			});
			
			$('.attributes').removeClass('attributes_tips');
			if(result){
				$('.attributes').addClass('attributes_tips');
				return false;
			}
			
			$('#paypal_checkout_button').attr('disabled', 'disabled');
			if(ueeshop_config['TouristsShopping']==0 && ueeshop_config['UserId']==0){ //游客状态
				$(this).loginOrVisitors('', 1, function(){
					$('#paypal_checkout_button').removeAttr('disabled');
				}, 'global_obj.div_mask(1);$(\'#signin_module\').remove();cart_obj.cart_init.paypal_checkout_init();ueeshop_config[\'UserId\']=1;');
			}else{
				cart_obj.cart_init.paypal_checkout_init('shipping_cost');
			}
			$(this).removeAttr('disabled');
			return false;
		});
	},
	tuan=function(){ //团购页
		if($("input:hidden#IsTuan").length){ //团购详情页
			var right_position=function(){
				var $ScrollTop=$(window).scrollTop(),
					$SideObj=$('#tuan .detail_side'),
					$SideTop=$SideObj.offset().top,
					$SideHeight=$SideObj.outerHeight();
					$Obj=$('#tuan .detail_right'),
					$BoxHeight=$Obj.height();
				if($ScrollTop>$SideTop){
					if((($ScrollTop-$SideTop)+$BoxHeight)>$SideHeight){
						$Obj.css({'margin-top':$SideHeight-$BoxHeight});
					}else{
						$Obj.css({'margin-top':$ScrollTop-$SideTop});
					}
				}else{
					$Obj.removeAttr('style');
				}
			}
			
			$(window).scroll(function(){
				right_position();
			});
			$(document).ready(function(){
				right_position();
			});
		}
	},
	custom=function(){ //列表详细
		if($('.prod_info_wholesale .pw_table_box').length){ //批发价
			var $pwItemWidth=$('.prod_info_wholesale .pw_column').outerWidth(),
				$pwItemCount=$('.prod_info_wholesale .pw_column').size();
			$('.prod_info_wholesale .pw_table_box').css({'width':$pwItemWidth*$pwItemCount});
			$('.prod_info_wholesale .pw_btn').click(function(){
				var $pwShow=parseInt($('.prod_info_wholesale .pw_table_box').attr('data-show')),
					$pwSort=$('.prod_info_wholesale .pw_table_box').attr('data-sort'),
					$pwNumber=0;
				if($pwSort=='right'){
					$pwNumber=$pwShow+1;
					$('.prod_info_wholesale .pw_table_box').animate({'margin-left': '-'+$pwItemWidth*($pwNumber-3)}, 200, function(){
						$(this).attr('data-show', $pwNumber);
						if($pwNumber==$pwItemCount){
							$(this).attr('data-sort', 'left');
							$('.prod_info_wholesale .pw_btn').addClass('return');
						}
					});
				}else{
					$pwNumber=$pwShow-1;
					$('.prod_info_wholesale .pw_table_box').animate({'margin-left': '-'+$pwItemWidth*($pwNumber-3)}, 200, function(){
						$(this).attr('data-show', $pwNumber);
						if($pwNumber==3){
							$(this).attr('data-sort', 'right');
							$('.prod_info_wholesale .pw_btn').removeClass('return');
						}
					});
				}
			});
			
			if($pwItemCount<3){
				var tableWidth=$('.prod_info_wholesale .pw_table').outerWidth();
				$('.prod_info_wholesale .pw_table_box').width(tableWidth);
				$('.prod_info_wholesale .pw_column, .prod_info_wholesale .pw_td').width((tableWidth/$pwItemCount)-2);
			}
		}
		
		$('.prod_info_actions .view_btn').click(function(){
			window.top.location.href=$(this).attr('href');
			return false;
		});
	}
	
	a();
	
	$('.FontPicArrowColor').css('border-color', 'transparent transparent '+$('.FontColor').css('color')+' transparent');
	$('.FontPicArrowXColor').css('border-color', 'transparent transparent transparent '+$('.FontColor').css('color'));
	$('.prod_description .desc>table, .prod_description .desc div>table, .prod_description .desc p>table, .prod_description .desc span>table').css('width', '100%');
	
})(jQuery);