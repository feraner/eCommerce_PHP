/*
 * 广州联雅网络
 */
var analytics_click_statistics; //统计的初始化变量

(function($){
	
	window.touch_nav=function(pos, posW, win_w){
		var startPos = {};
		var MovePos = {};
		var isScrolling = 0;
		var _mL = 0;
			
		pos.get(0).ontouchstart = function (e){
			start(e);
		};
		pos.get(0).ontouchmove = function (e){
			move(e);
		}
		pos.get(0).ontouchend = function (e){
			end(e);
		}
		
		function start(e){
			startPos = {x:e.touches[0].pageX,y:e.touches[0].pageY,time:+new Date};
			isScrolling = 0;
			_mL = parseFloat(pos.css('margin-left'));
		}
		function move(e){
			if(e.targetTouches.length > 1 || e.scale && e.scale !== 1) return;
			MovePos = {x:e.touches[0].pageX - startPos.x,y:e.touches[0].pageY - startPos.y};
			isScrolling = Math.abs(MovePos.x) < Math.abs(MovePos.y) ? 1:0;
			if(isScrolling==1){
				pos.get(0).ontouchmove = function (){};
			}else{
				pos.get(0).ontouchmove = function (e2){
					e2.preventDefault();
					var marL = e2.touches[0].pageX-startPos.x;
					pos.css('margin-left', marL+_mL);
				};
			}
		}
		function end(e){
			_mL = parseFloat(pos.css('margin-left'));
			if (_mL>0){
				pos.animate({marginLeft:0}, 200);
			}else if (posW+_mL<win_w){
				if (posW>win_w){
					pos.animate({marginLeft:win_w-posW}, 200);
				}else{
					pos.animate({marginLeft:0}, 200);
				}
			}
			pos.get(0).ontouchmove = function (e){
				move(e);
			}
		}
	};
	
	Number.prototype.formatMoney=function(places, decimal, thousand){
		places=!isNaN(places=Math.abs(places))?places:2;
		thousand=thousand || ',';
		decimal=decimal || '.';
		var number=this,
			negative=number<0?'-':'',
			i=parseInt(number=Math.abs(+number || 0).toFixed(places), 10)+'',
			j=(j=i.length)>3?j%3:0;
		return negative+(j?i.substr(0, j)+thousand:'')+i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousand)+(places?decimal+Math.abs(number-i).toFixed(places).slice(2):'');
	};
	
	//提示框
	$.fn.tips_box=function(tips, type, callback){ //type success:提示成功 error:提示失败 confirm:选择框
		var type=(typeof(arguments[1])=='undefined')?'confirm':arguments[1],
			html='';
		$('#div_mask, .win_alert').remove();//优先清空多余的弹出框
		if(type=='success'){ //提示成功
			html='<div class="tips_success">'+tips+'</div>';
			$('body').prepend(html);
			setTimeout(function(){
				$('.tips_success').fadeOut(1000, function(){ $(this).remove(); });
			}, 1000);
		}else if(type=='error'){ //提示失败
			html='<div class="tips_error attr_null">'+tips+'</div>';
			$('body').prepend(html);
			setTimeout(function(){
				$('.tips_error').fadeOut(1000, function(){ $(this).remove(); });
			}, 1000);
		}else{ //选择框
			global_obj.div_mask();
			html='<div class="tips_confirm">';
				html+='<div class="tips_header ui_border_b"><button class="btn_close">x</button></div>';
				html+='<div class="tips_body"><div class="tips_text">'+tips+'</div></div>';
				html+='<div class="tips_footer">';
					html+='<button class="btn_global btn_cancel">'+lang_obj.global.n_y[0]+'</button><button class="btn_global btn_sure FontBgColor">'+lang_obj.global.n_y[1]+'</button>';
				html+='</div>';
			html+='</div>';
			$('body').prepend(html);
			if(type=='confirm'){
				$('.tips_confirm').delegate('.btn_close, .btn_cancel', 'tap click', function(){
					$('#div_mask').fadeOut(500, function(){ $(this).remove(); });
					$('.tips_confirm').fadeOut(500, function(){ $(this).remove(); });
				}).delegate('.btn_sure', 'tap click', function(){
					$.isFunction(callback) && callback();
					$('.tips_confirm .btn_close').click();
				});
			}
		}
		return false;
	},
	
	//返回顶部
	$.fn.toTop=function(){
		if($(window).scrollTop()>60){
			this.fadeIn().css('display', 'block');
		}else{
			this.fadeOut();
		}
	}
	
	//loading加载效果
	$.fn.loading=function(e){
		e=$.extend({opacity:.5, size:"big"}, e);
		$(this).each(function(){
			if($(this).hasClass("masked")) return;
			var obj=$(this);
			var l=$('<div class="loading"></div>').css("opacity", 0);
			obj.addClass("masked").append(l);
			var lb=$('<div class="loading_msg loading_big"></div>').appendTo(obj);
			lb.css({
				top: (obj.height() / 2 - (lb.height() + parseInt(lb.css("padding-top")) + parseInt(lb.css("padding-bottom"))) / 2)*0.01+'rem',
				left: (obj.width() / 2 - (lb.width() + parseInt(lb.css("padding-left")) + parseInt(lb.css("padding-right"))) / 2)*0.01+'rem'
			});
		});
		return this;
	}
	//取消loading加载效果
	$.fn.unloading=function(){
		$(this).each(function(){
			$(this).find(".loading_msg, .loading").remove();
			$(this).removeClass("masked");
		});
	}
	
	//购买流程，跳转到会员登录框或者访客继续付款
	$.fn.loginOrVisitors=function(){
		var obj=$(this), result;
		if(ueeshop_config['TouristsShopping']==0 && ueeshop_config['UserId']==0/* && global_obj.getCookie('loginOrVisitors')!='ok'*/){ //通过
			result=false;
		}else{ //不通过
			result=true;
		}
		return result;
	}
	
	//计算秒杀价格
	$.fn.seckillPrice=function(){
		var $seckillData='0';
		$('html .price_data').each(function(){
			$proid=$(this).attr('keyid');
			if($proid) $seckillData+=','+$proid;
		});
		if($seckillData!='0'){
			$.post('/', 'do_action=action.seckill&ProId='+$seckillData, function(data){
				if(data.ret==1){
					for(k in data.msg){
						$('.price_data[keyid='+k+']').text(data.msg[k]);
						$('.price_data[keyid='+k+']').parents('.item').find('.icon_seckill').show().siblings('.icon_discount, .icon_discount_foot').hide();
					}
				}
			}, 'json');
		}
	}
	
	//货币格式显示
	$.fn.currencyFormat=function(price, currency){
		var result=0;
		price=parseFloat(price);
		switch(currency){
			case 'USD':
			case 'GBP':
			case 'CAD':
			case 'AUD':
			case 'CHF':
			case 'HKD':
			case 'ILS':
			case 'MXN':
			case 'CNY':
			case 'SAR':
			case 'SGD':
			case 'NZD':
			case 'AED':
				result=price.formatMoney(2, '.', ','); break;
			case 'RUB':
				result=price.formatMoney(2, ',', ' '); break;
			case 'EUR':
			case 'BRL':
			case 'ARS':
				result=price.formatMoney(2, ',', '.'); break;
			case 'CLP':
			case 'NOK':
			case 'DKK':
			case 'COP':
				result=price.formatMoney(0, '', '.'); break;
			case 'JPY':
			case 'SEK':
			case 'KRW':
			case 'INR':
				result=price.formatMoney(0, '', ','); break;
			default:
				result=price.formatMoney(2, '.', ','); break;
		}
		return result;
	}
	
	//分享插件
	$.fn.shareThis=function(type, title, url){
		var image=back_url=encode_url="";
		if(url==undefined){
			url=window.location.href;
		}
		if(url.indexOf("#")>0){
			url=url.substring(0, url.indexOf("#"));
		}
		if(type=="pinterest"){
			//image=window.location.protocol+'//'+window.location.host+$(".big_box .big_pic>img").attr("src");
			//var url=$(".big_box .big_pic>img").attr("src");
			if(url.indexOf('ueeshop.ly200-cdn.com')!=-1){
				image=$(".big_box .big_pic>img").attr("src");
			}else{
				image=window.location.protocol+'//'+window.location.host+$(".big_box .big_pic>img").attr("src");
			}
		}
		if(image!="" && image!=undefined){
			image=encodeURIComponent(image);
		}
		e_url=encodeURIComponent(url);
		title=encodeURIComponent(title);
		switch(type){
			case "delicious":
				back_url = "https://delicious.com/post?title=" + title + "&url=" + e_url;
				break;
			case "digg":
				back_url = "http://digg.com/submit?phase=2&url=" + e_url + "&title=" + title + "&bodytext=&topic=tech_deals";
				break;
			case "reddit":
				back_url = "http://reddit.com/submit?url=" + e_url + "&title=" + title;
				break;
			case "furl":
				back_url = "http://www.furl.net/savedialog.jsp?t=" + title + "&u=" + e_url;
				break;
			case "rawsugar":
				back_url = "http://www.rawsugar.com/home/extensiontagit/?turl=" + e_url + "&tttl=" + title;
				break;
			case "stumbleupon":
				back_url = "http://www.stumbleupon.com/submit?url=" + e_url + "&title=" + title;
				break;
			case "blogmarks":
				break;
			case "facebook":
				back_url = "http://www.facebook.com/share.php?src=bm&v=4&u=" + e_url + "&t=" + title;
				break;
			case "technorati":
				back_url = "http://technorati.com/faves?sub=favthis&add=" + e_url;
				break;
			case "spurl":
				back_url = "http://www.spurl.net/spurl.php?v=3&title=" + title + "&url=" + e_url;
				break;
			case "simpy":
				back_url = "http://www.simpy.com/simpy/LinkAdd.do?title=" + title + "&href=" + e_url;
				break;
			case "ask":
				break;
			case "google":
				back_url = "http://www.google.com/bookmarks/mark?op=edit&output=popup&bkmk=" + e_url + "&title=" + title;
				break;
			case "netscape":
				back_url = "http://www.netscape.com/submit/?U=" + e_url + "&T=" + title + "&C=";
				break;
			case "slashdot":
				back_url = "http://slashdot.org/bookmark.pl?url=" + url + "&title=" + title;
				break;
			case "backflip":
				back_url = "http://www.backflip.com/add_page_pop.ihtml?title=" + title + "&url=" + e_url;
				break;
			case "bluedot":
				back_url = "http://bluedot.us/Authoring.aspx?u=" + e_url + "&t=" + title;
				break;
			case "kaboodle":
				back_url = "http://www.kaboodle.com/za/selectpage?p_pop=false&pa=url&u=" + e_url;
				break;
			case "squidoo":
				back_url = "http://www.squidoo.com/lensmaster/bookmark?" + e_url;
				break;
			case "twitter":
				back_url = "https://twitter.com/intent/tweet?status=" + title + ":+" + e_url;
				break;
			case "pinterest":
				back_url = "http://pinterest.com/pin/create/button/?url=" + e_url + "&media=" + image + "&description=" + title;
				break;
			case "vk":
				back_url = "http://vk.com/share.php?url=" + url;
				break;
			case "bluedot":
				back_url = "http://blinkbits.com/bookmarklets/save.php?v=1&source_url=" + e_url + "&title=" + title;
				break;
			case "blinkList":
				back_url = "http://blinkbits.com/bookmarklets/save.php?v=1&source_url=" + e_url + "&title=" + title;
				break;
			case "linkedin":
				back_url = "http://www.linkedin.com/cws/share?url=" + e_url + "&title=" + title;
				break;
			case "googleplus":
				back_url = "https://plus.google.com/share?url=" + e_url;
				break;
		}
		window.open(back_url, "bookmarkWindow");
	}
	
	//倒计时插件
	$.fn.genTimer=function(e){
		function u(e){
			var t=Math.floor(e/n),
				r=Math.floor((e-t*n)/36e5),
				i=Math.floor((e-t*n-r*1e3*60*60)/6e4),
				s=Math.floor((e-t*n-r*1e3*60*60-i*1e3*60)/1e3);
			return {hours:("0"+r).slice(-2), minutes:("0"+i).slice(-2), seconds:("0"+s).slice(-2), dates:t}
		}
		
		var t={
				beginTime:new Date,
				day_label:"day",
				days_label:"days",
				unitWord:{hours:":", minutes:":", seconds:""},
				type:"day",
				callbackOnlyDatas:!1
			},
			n=864e5,
			r=$.extend({}, t, e),
			i=this;
			
		r.targetTime=r.targetTime.replace(/\-/g, "/");
		var s=new Date(r.targetTime)-new Date(r.beginTime),
		o=function(){
			if(s<0){
				r.callback.call(i, r.callbackOnlyDatas ? {hours:"00", minutes:"00", seconds:"00",dates:0}: "00"+r.unitWord.hours+"00"+r.unitWord.minutes+"00");
				clearInterval(i.interval);
			}else{
				var e=u(s);
				if(r.callbackOnlyDatas) r.callback.call(i, e);
				else if(r.type=="day") s>=n*2 ? r.callback.call(i, '<span class="day_count">'+e.dates+'</span><span class="day">'+r.days_label+'</span><span class="day_seconds">'+e.hours+r.unitWord.hours+e.minutes+r.unitWord.minutes+e.seconds+r.unitWord.seconds+"</span>") : s>=n ? r.callback.call(i, '<span class="day_count">'+e.dates+'</span><span class="day">'+r.day_label+'</span><span class="day_seconds">'+e.hours+r.unitWord.hours+e.minutes+r.unitWord.minutes+e.seconds+r.unitWord.seconds+"</span>") : r.callback.call(i, '<span class="seconds">'+e.hours+r.unitWord.hours+e.minutes+r.unitWord.minutes+e.seconds+r.unitWord.seconds+"</span>");
				else if(r.type=="diffNoDay"){
					var t=e.hours;
					s>=n && (t=Number(e.dates*24)+Number(e.hours));
					r.callback.call(i, '<span class="hours">'+t+'</span><span class="miniutes">'+r.unitWord.hours+e.minutes+'</span><span class="senconds">'+r.unitWord.minutes+e.seconds+r.unitWord.seconds+"</span>");
				}else{
					var t=e.hours;
					s>=n && (t=Number(e.dates*24)+Number(e.hours));
					r.callback.call(i, '<span class="seconds">'+t+r.unitWord.hours+e.minutes+r.unitWord.minutes+e.seconds+r.unitWord.seconds+"</span>");
				}
			}
			s-=1e3
		};
		i.interval=setInterval(o, 1e3);
		if(typeof(seckill_timer)=='object'){
			seckill_timer.push(i.interval);//秒杀页面计时器ID，防止时间乱跳
		}
		o();
		return this
	}
	
	$.fn.extend({
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
	
	//图片lightbox效果
	$.fn.show_image=function(){
		var size=0;
		var index=$(this).index();
		var html='';
		html+='<div id="global_show_image"><div class="global_show_img_container"><ul>';
		$(this).parent().find('.show_image').each(function(){
			html+='<li><img src="'+$(this).attr('href')+'"/><span></span></li>';
			size++;
		});
		html+='</ul></div></div>';
		$('body').prepend(html);
		$('#global_show_image').css({'width':'100%','height':'100%','z-index':'10001','position':'fixed'});
		$('#global_show_image .global_show_img_container').css({'width':'90%','height':'90%','margin':'5%','position':'relative','overflow':'hidden'});
		$('#global_show_image ul').css({'width':(100*size)+'%','height':'100%','position':'absolute','left':'-'+(100*index)+'%','top':'0'});
		var li_width=(100/size);
		$('#global_show_image ul li').css({'width':li_width+'%','height':'100%','float':'left','text-align':'center','background':'url(/static/themes/default/mobile/images/loading.gif) center no-repeat','background-size':'20px'});
		$('#global_show_image ul li img').css('vertical-align','middle');
		$('#global_show_image ul li span').css({'height':'100%','display':'inline-block','vertical-align':'middle'});
		global_obj.div_mask();
		if(size>1){
			var touchStartX = 0;
			var touchEndX = 0;
			$('#global_show_image li').on('touchmove',function(event){
				event.preventDefault();
				event.stopPropagation();
			});
			$('#global_show_image li').on('touchstart',function(event){
				touchStartX=event.originalEvent.changedTouches[0].pageX;
			});
			$('#global_show_image li').on('touchend',function(event){
				event.preventDefault();
				event.stopPropagation();
				touchEndX=event.originalEvent.changedTouches[0].pageX;
				if(touchEndX-touchStartX>30){	//右滑动	//30像素偏移值
					index = --index > 0 ? index : 0;
					$('#global_show_image ul').animate({'left':'-'+(100*index)+'%'});
				}else if(touchEndX-touchStartX<-30){	//左滑动	//30像素偏移值
					index = ++index < size ? index : size-1;
					$('#global_show_image ul').animate({'left':'-'+(100*index)+'%'});
				}else{	//点击取消
					$('#global_show_image').remove();
					global_obj.div_mask(1);
				}
			});
		}else{ //单张图片
			$('#global_show_image li').on('touchend',function(event){ //点击取消
				$('#global_show_image').remove();
				global_obj.div_mask(1);
			});
		}
	}
	
	$(function(){
		var $to_top=$('.btn_top');
		$to_top.toTop();
		$(window).scroll(function(){
			$to_top.toTop();
		});
		$to_top.on('tap', function(){
			$('html, body').animate({'scrollTop':0}, 400);
		});
		
		if($('#detail_top').length){
			if($('#header_fill').length){//fixed
				$('#header_fix').css({'top':$('#detail_top').outerHeight()-1});
			}else{
				$('#header_fix').css({'marginTop':$('#detail_top').outerHeight()-1});
			}
		}
		
		/******************* 导航/底部菜单 start *******************/
		var $pop_up=$('.pop_up'),
			$container=$('.pop_up_container'),
			$height;
		
		$('header aside .i1 a,#u_header a.menu').on('click', function(){
			global_obj.div_mask();
			$height=$(window).height();
			$('.nav_side').css('visibility', 'visible').addClass('show');
			$('html, html body').css({'height':$height, 'overflow':'hidden'});
			var h=$(window).outerHeight()-$('.nav_side .user').outerHeight(true)-$('.nav_side .search').outerHeight()-$('.nav_side .currency').outerHeight();
			$('.nav_side .menu_list').css({'height':h, 'max-height':h});
			
			$('#div_mask').off().on('click', function(){
				$('.nav_side>.close').click();
				return false;
			});
		});
		
		//搜索框
		$('header .search>form').submit(function(){
			var o=$(this).find('input[type=search]');
			o.removeClass('form_null');
			if(o.val()==''){
				o.addClass('form_null');
				return false;
			}
		});
		
		//头部导航
		$pop_up.find('.close').off().on('tap, click', function(e){
			e.stopPropagation();
			var o=$(this).parents('.pop_up');
			o.removeClass('show');
			setTimeout(function(){
				o.css('visibility', 'hidden');
			}, 40);
			if(o.hasClass('nav_side')){//头部导航
				$('.category_side,.footer_side').removeClass('show').css('visibility', 'hidden');
			}
			if(!o.hasClass('category_side') && !o.hasClass('footer_side')){
				$('html, html body').css({'height':'auto', 'overflow':'auto'});
				setTimeout(function(){
					global_obj.div_mask(1);
				}, 40);
			}
		});
		$container.find('.btn_all_category').on('tap', function(){
			$('.category_side').css('visibility', 'visible').addClass('show');
			var h=$(window).outerHeight()-$('.category_side .category_title').outerHeight(true)-$('.category_side .search').outerHeight();
			$('.category_side .menu_list').css({'height':h, 'max-height':h});
		});
		$container.find('.menu_list .item a').on('tap', function(e){
			e.stopPropagation();
			window.top.location.href=$(this).attr('href');
        });
		$container.find('.menu_list .item a').on('click', function(e){
			return false;
		});
		$container.find('div.menu_list .son>a').on('tap', function(){
			if($(this).parent().hasClass('open')){
				$(this).parent().removeClass('open');
				$(this).siblings('.menu_son').stop(true, false).slideUp();
			}else{
				$(this).parent().addClass('open');
				$(this).siblings('.menu_son').stop(true, false).slideDown();
			}
        });
		$container.find('.menu_list .isub .navsub .next').on('tap', function(){ //下级弹窗
			if($(this).hasClass('on')){
				$(this).removeClass('on');
			}else{
				$('body,html').scrollTop(0);
				$(this).addClass('on');
			}
		});
		$container.find('.menu_list .isub .navsub .nextwd').on('tap', function(e){
			e.stopPropagation();
		});
		$container.find('.menu_list .isub .navsub .nextwd .nclose').on('tap', function(){
			$(this).parent().parent().parent().removeClass('on');
		});
		//二级弹窗js
		$container.find('.menu_list .navsub .subitem .nextwd .next0').on('tap', function(){
			if ($(this).hasClass('on')){
				$(this).removeClass('on');
				$(this).siblings('.next1').stop(true, false).slideUp();
			}else{
				$(this).addClass('on');
				$(this).siblings('.next1').stop(true, false).slideDown();
			}
		});
		$container.find('.menu_list .navsub .subitem .nextwd .t1bg').on('tap', function(){
			if ($(this).hasClass('on')){
				$(this).removeClass('on');
				$(this).siblings('.next2').stop(true, false).slideUp();
			}else{
				$(this).addClass('on');
				$(this).siblings('.next2').stop(true, false).slideDown();
			}
		});
		
		//订阅
		$('#newsletter_form').submit(function(){
			var $Email=$('#newsletter_form input[name=Email]');
			if($Email.val()==''){
				$Email.addClass('null').next('p.error').text(lang_obj.user.reg_error.PleaseEnter.replace('%field%', $Email.attr('data-field'))).show();
				status+=1;
			}
			if($Email.val() && /^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/.test($Email.val())==false){
				$Email.addClass('null').next('p.error').text(lang_obj.user.reg_error.EmailFormat).show();
				status+=1;
			}
			if(status){
				return false;
			}
			$(this).find('input[type=submit]').attr('disabled', 'disabled');
			
			$.post('/', 'do_action=action.newsletter&'+$(this).serialize(), function(data){
				if(data.ret==1){
					$('html').tips_box(lang_obj.newsletter.success, 'success', function(){
						$('#newsletter input[name=Email]').val('');
					});
				}else{
					$('html').tips_box('"'+data.msg+'" '+lang_obj.newsletter.exists, 'error');
				}
			}, 'json');
			
			$(this).find('input[type=submit]').removeAttr('disabled');
			return false;
		});
		
		$('footer .footer_list>li>a.help_click').on('tap', function(e){
			if($(this).hasClass('list_close')){
				$(this).removeClass('list_close').parent().children('.help_list').show();
			}else{
				$(this).addClass('list_close').parent().children('.help_list').hide();
			}
		});
		
		//选择语言 货币
		$('.nav_container .currency>a[class!=noclick]').on('tap', function(e){
			$('.footer_side').css('visibility', 'visible').addClass('show');
			var h=$(window).outerHeight()-$('.category_side .category_title').outerHeight(true)-$('.category_side .search').outerHeight();
			$('.footer_side .menu_list').css({'height':h, 'max-height':h});
		});
		$('.footer_side .menu_list a.currency_item').on('tap', function(){
			var val=$(this).attr('data');
			$.post('/', 'do_action=action.currency&currency='+val, function(data){
				if(data.ret==1){
					window.top.location.reload();
				}
			}, 'json');
			return false;
		});
		/******************* 导航/底部菜单 end *******************/
		
		//面包屑
		var page_title = $('.page_title');
		var pos = $('.page_title .pos');
		if (pos.length){
			var win_w = _w;
			var posW = parseInt($('.column', pos).outerWidth(true))+parseInt(page_title.css('padding-left'))*2;
			if (posW>page_title.width()){
				touch_nav(pos, posW, win_w);
			}
		}
		
		//秒杀价格
		$('html').seckillPrice();
		
		//浮动在线客服
		$('#float_chat .btn_chat').on('click', function(e){
			$('#float_chat .inner_chat').css('margin-top', function(){
				global_obj.div_mask();
				$(this).css('display', 'block');
				return -($(this).outerHeight(true)/2);
			});
		});
		$('#float_chat .chat_close').on('click', function (e){
			global_obj.div_mask(1);
			$('#float_chat .inner_chat').css('display', 'none');
		});
	});
	
})(jQuery);
