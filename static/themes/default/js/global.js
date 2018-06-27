/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

//前端显示方式(0:自适应 1:窄屏 2:宽屏)
$.fn.webDisplay=function(type){
	if(type==0){
		if($(window).width()>=1280){$('body').addClass('w_1200');}
		$(window).resize(function(){
			if($(window).width()>=1280){
				$('body').addClass('w_1200');
			}else{
				$('body').removeClass('w_1200');
			}
		});
	}else if(type==2){
		$('body').addClass('w_1200');
	}
}

//loading加载效果
$.fn.loading=function(e){
	e=$.extend({opacity:.5,size:"big"},e);
	$(this).each(function(){
		if($(this).hasClass("masked")) return;
		var obj=$(this);
		var l=$('<div class="loading"></div>').css("opacity", 0);
		obj.addClass("masked").append(l);
		//var lb=$('<div class="loading_msg loading_big"></div>').appendTo(obj);
		var lb=$('<div class="loading_msg loading_'+e.size+'"></div>').appendTo(obj);
		lb.css({
			top: obj.height() / 2 - (lb.height() + parseInt(lb.css("padding-top")) + parseInt(lb.css("padding-bottom"))) / 2,
			left: obj.width() / 2 - (lb.width() + parseInt(lb.css("padding-left")) + parseInt(lb.css("padding-right"))) / 2
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

//滚动插件
$.fn.carousel=function(e){
	e=$.extend({itemsPerMove:2,duration:1e3,vertical:!1,specification:"",width:0,height:0,step:1,preCtrEntity:"pre_arrow",nextCtrEntity:"next_arrow"},e);
	var t=this,
		n=t.find(".viewport"),
		r=n.find(".list"),
		i,s,o,u,a,f=!1,
		l={
			init:function(){
				var oFirst=r.children(":first"),
					oLast=r.children(":last"),
					l,c,list_len=r.children().length;
				
				if(e.vertical){	//判断滚动方式
					l=Math.max(oFirst.outerHeight(!0), oLast.outerHeight(!0));
					i=l*e.itemsPerMove;
					c=oFirst.outerHeight(!0)-oFirst.outerHeight();
					t.addClass("vertical").css({height:e.height||i-c, width:e.width||oFirst.outerWidth(!0)});
					r.height(l*list_len);
					if(l*list_len>(e.height || i-c)){
						s={scrollTop:"-="+i};
						o={scrollTop:i};
						u={scrollTop:"-="+i*e.step};
						a={scrollTop:i*e.step};
						this.bind_event();
					}
				}else{
					l=Math.max(oFirst.outerWidth(!0), oLast.outerWidth(!0));
					i=l*e.itemsPerMove;
					c=oFirst.outerWidth(!0)-oFirst.outerWidth();
					t.addClass("horizontal").css({height:e.height||oFirst.outerHeight(!0), width:e.width||i-c});
					r.width(l*list_len);
					if(l*list_len>(e.width || i-c)){
						s={scrollLeft:"-="+i};
						o={scrollLeft:"+="+i};
						u={scrollLeft:"-="+i*e.step};
						a={scrollLeft:i*e.step};
						this.bind_event();
					}
				}
			},
			step_prev:function(t){
				if(f) return;f=!0;
				for(var o=0;o<e.itemsPerMove;o++)r.prepend(r.children(":last"));
				n[e.vertical?"scrollTop":"scrollLeft"](i).stop().animate(s,{
					duration:e.duration,
					complete:function(){
						l.current(0);
						t-=1;
						f=!1;
						t>0 && l.step_prev(t);
					}
				});
			},
			step_next:function(t){
				if(f) return;
				f=!0;
				n.stop().animate(o, {
					duration:e.duration,
					complete:function(){
						l.current(1);
						l.repeatRun(function(){
							r.children(":last").after(r.children(":first"))
						}, e.itemsPerMove);
						e.vertical?n.scrollTop(0):n.scrollLeft(0);
						t-=1;
						f=!1;
						t>0 && l.step_next(t);
					}
				})
			},
			moveSlide:function(t){
				t==="next"?this.step_next(e.step):this.step_prev(e.step)
			},
			repeatRun:function(e,t){
				for(var n=0; n<t; n++) e()
			},
			bind_event:function(){
				t.find(".btn").on("click", function(e){
					l.moveSlide($(this).hasClass("prev")?"prev":"next")
				});
			},
			current:function(t){
				var b=r.find("li.current");
				t?b.next().addClass('current').siblings().removeClass('current'):b.prev().addClass('current').siblings().removeClass('current');
				b=r.find("li.current");
				$(".detail_pic .big_pic").attr("href", $('#shopbox_outer').length?'javascript:;':b.find("img").attr("mask"));
				$(".detail_pic .normal").attr("src", b.find("img").attr("normal"));
			}
		}
	l.init();
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
	if (typeof(seckill_timer)=='object'){
		seckill_timer.push(i.interval);//秒杀页面计时器ID，防止时间乱跳
	}
	o();
	return this
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
		image=$(".big_box .big_pic>img").attr("src");
		if(image.indexOf('ueeshop.ly200-cdn.com')!=-1){
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

//价格显示插件
$.fn.priceShow=function(){
	var $price=0;
	$(this).find('.price_data').each(function(){
		$price=($(this).attr('data')*parseFloat(ueeshop_config.currency_rate)).toFixed(3);
		$price=$price.substr(0, $price.length-1);
		$price=$('html').currencyFormat($price, ueeshop_config.currency);
		$(this).text($price);
	});
}

//关闭产品详细弹出框，this:#shopbox
$.fn.shopboxHide=function(){
	var obj=$(this);
	if(obj.length){
		//obj.find('.shopbox_wrap').animate({opacity:0}, {
		obj.animate({opacity:0}, {
			duration: 250,
			easing: 'swing',
			complete: function(){
				obj.parents('body').removeClass('hidden').css('margin-right', 0);
				obj.find('.shopbox_frame').hide().attr('src', '//about:blank').end().empty();
				global_obj.div_mask(1);
				obj.remove();
			}
		});
	}
}

//检查登录状态
$.fn.checkUser=function(){
	$.post('/?do_action=user.check_user&t='+Math.random(), '', function(data){
		if(data.ret==1){
			ueeshop_config['UserId']=data.msg['UserId']; //更新登录状态
		}
	}, 'json');
}

//购买流程，弹出会员登录框或者访客继续付款
$.fn.loginOrVisitors=function(url, type, cancelback){
	var comeback=(typeof(arguments[3])=='undefined')?'':arguments[3];//登录成功后执行函数
	var obj=$(this);
	if(ueeshop_config['TouristsShopping']==0 && ueeshop_config['UserId']==0/* && global_obj.getCookie('loginOrVisitors')!='ok'*/){
		user_obj.set_form_sign_in('', url, type);
		user_obj.sign_in_init(cancelback);
		comeback && $('form[name=signin_form]').append('<input type="hidden" name="comeback" value="'+comeback+'" />');
		//global_obj.setCookie('loginOrVisitors', 'ok', 86400);
	}else{
		if(type==1) window.location.href=url;
		return false;
	}
}

function loadImg(obj){
    //获取文件  
    var file = obj.files[0];  
    //创建读取文件的对象  
    var reader = new FileReader();  
    //为文件读取成功设置事件  
    reader.onload=function(e) {    
        var imgFile = e.target.result;
        window.document.getElementById("pic_show").innerHTML='<img src="'+imgFile+'" alt=""><span></span>';
    }; 
    //正式读取文件  
    reader.readAsDataURL(file);
}

$(document).ready(function(){
	//浏览器语言跳转
	if(navigator.userLanguage){
        CurLang=navigator.userLanguage.substring(0,2).toLowerCase();
    }else{
        CurLang=navigator.language.substring(0,2).toLowerCase();
    }
	
	//通告栏关闭
	$('#top_banner .top_banner_close').click(function(){
		$('#top_banner').slideUp(500);
	});
	
	//头部栏目设置
	$('#top_bar .crossn li').each(function(){
		var dd=$(this).find('dd.user');
        if(dd.length){
			var oWidth=($(this).width()-22);
			dd.css('width', oWidth+'px');
		}
    });
	$('#currency').text(ueeshop_config.currency);
	$('.currency_data').text(ueeshop_config.currency_symbols);
	$('html').priceShow();
	
	//搜索下拉
	$('.ajax_search input[name=Keyword]').keyup(function(){
        var Keyword = $(this).val(),
         	left = parseInt($('.ajax_search').css("border-left-width")),
         	right = parseInt($('.ajax_search').css("border-right-width")),
         	top = parseInt($('.ajax_search').css("border-top-width")),
         	bottom = parseInt($('.ajax_search').css("border-bottom-width"));
        if(Keyword){
            $.post('/ajax/ajax_search.html',{'Keyword':Keyword},function(data){
                $('.ajax_search .search_content_box').remove();
            	$('.ajax_search').append(data);
                $('.ajax_search .search_content_box').css('top',$('.ajax_search form').height()+bottom+'px');
                $('.ajax_search .search_content_box').css('left',-left+'px');
                $('.ajax_search .search_content_box').css('width',$('.ajax_search form').width()-2+left+right+'px');
            });
        }
        $('.ajax_search').mouseleave(function(){
        	$('.ajax_search .search_content_box').remove();
        });
    });
	
	//秒杀价格
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
					$('.price_data[keyid='+k+']').parents('.prod_box').find('.icon_seckill').show().siblings('.icon_discount, .icon_discount_foot').hide();
					/**************************** 临时 ********************************/
					$('.price_data[keyid='+k+']').parents('.pro_item').find('.icon_seckill').show().siblings('.icon_discount, .icon_discount_foot').hide();
					/**************************** 临时 ********************************/
				}
			}
		}, 'json');
	}
	
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
	
	//产品列表页筛选
	$("#more_prop").click(function(){
		$(this).hide();
		$("#less_prop").show();
		$('[overshow=true]').show();
	});
	$("#less_prop").click(function(){
		$(this).hide();
		$("#more_prop").show();
		$('[overshow=true]').hide();
	});
	
	//搜索框
	$('#header .search form').submit(function(){if(global_obj.check_form($(this).find('*[notnull]'))){return false;}});
	
	//货币切换
	$('#top_bar_outer .currency, #top_outer .currency, #toper .currency, #header .currency').delegate('a', 'click', function(){
		var v=$(this).attr('data');
		$.post('/', 'do_action=action.currency&currency='+v, function(data){
			if(data.ret==1){
				window.top.location.reload();
			}
		}, 'json');
	});
	
	//购物车
	$(".header_cart").hover(function(){
		if(!$(this).attr('is_animate')){
			var $this=$(this),
				$lang=$this.attr('lang');
				$note=$this.find(".cart_note");
				
			$this.addClass("header_active");
			$note.show();
			//if(!$note.html()){
				$.ajax({
					url:"/ajax/shopping_cart.html",//"/?m=ajax&a=shopping_cart",
					async:false,
					type:'get',
					dataType:'html',
					success:function(result){
						if(result){
							$note.html(result);
						}
					}
				});
			//}
			$(this).attr('is_animate',1);
		}
	}, function(){
		if(!$(this).attr('status')){
			$(this).removeAttr('is_animate').removeClass("header_active").find(".cart_note").hide();
		}else{
			setTimeout(function(){
				$(this).removeAttr('status').removeAttr('is_animate').removeClass("header_active").find(".cart_note").hide();
			},10000);
		}
	});
	
	//订阅
	$('#newsletter_form').submit(function(){
		if(global_obj.check_form($(this).find('*[notnull]'), $(this).find('*[format]'), 0, 1)){return false;}
		$(this).find('input[type=submit]').attr('disabled', 'disabled');
		
		$.post('/', 'do_action=action.newsletter&'+$(this).serialize(), function(data){
			if(data.ret==1){
				global_obj.new_win_alert(lang_obj.newsletter.success, function(){
					$('#newsletter input[name=Email]').val('');
				}, '', undefined, '');
			}else{
				global_obj.new_win_alert('"'+data.msg+'" '+lang_obj.newsletter.exists);
			}
		}, 'json');
		
		$(this).find('input[type=submit]').removeAttr('disabled');
		return false;
	});
	
	//在线浮动客服
	$('#float_chat .chat_box>a').click(function(){
		if(!$(this).hasClass('WeChat')){
			var Url=$(this).attr('href');
			window.open(Url, 'online', 'height=400,width=400,top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=no,location=no, status=no');
		}
		return false;
	});
	$('#go_top').click(function(){
		$("html, body").animate({"scrollTop":0}, 700);
	});
	
	//订单直播
	if($('.order_live').length){
		var $i=0,
			$box=$('.order_live .order_live_bd'),
			$bdHeight=$('.order_live .order_live_scroll').height(),
			OrderLiveFun=function(){
							$boxHeight=$box.find('li:eq(0)').outerHeight()
							if($box.scrollTop()>$boxHeight){
								$box.find('li:eq(0)').clone().appendTo('.order_live .order_live_scroll');
								$box.find('li:eq(0)').remove();
								$i=0;
							}
							$box.scrollTop(++$i);
						};
		
		if($bdHeight>$box.height()+50){	
			var OrderLive=setInterval(OrderLiveFun, 50);
			
			$box.off().on("mouseenter", function(){
				clearInterval(OrderLive);
			}).on("mouseleave", function(){
				OrderLive=setInterval(OrderLiveFun, 50);
			});
		}
	}
	
	//首页产品特效
	var $effectsVal=$(".prod_list").attr("effects");
	if($effectsVal==1){
		$(".prod_list .prod_box").off().on("mouseenter", function(){
			$(this).addClass('hover_1');
		}).on("mouseleave", function(){
			$(this).removeClass('hover_1');
		});
	}else if($effectsVal==2){
		$(".prod_list .prod_box").off().on("mouseenter", function(){
			$(this).addClass('hover_2');
		}).on("mouseleave", function(){
			$(this).removeClass('hover_2');
		});
	}else if($effectsVal==3){
		$(".prod_list .prod_box").off().on("mouseenter", function(){
			$(this).addClass('hover_3');
		}).on("mouseleave", function(){
			$(this).removeClass('hover_3');
		});
	}else if($effectsVal==4){
		$(".prod_list .prod_box").off().on("mouseenter", function(){
			$(this).addClass('hover_4');
		}).on("mouseleave", function(){
			$(this).removeClass('hover_4');
		});
	}else if($effectsVal==5){
		$(".prod_list .prod_box").off().on("mouseenter", function(){
			$(this).children(".prod_box_pic").addClass("pic_enlarge");
		}).on("mouseleave", function(){
			$(this).children(".prod_box_pic").removeClass("pic_enlarge");
		});
	}else if($effectsVal==6){
		$(".prod_list .prod_box").off().on("mouseenter", function(){
			if($(this).find(".thumb_hover").length){
				$(this).find(".thumb").stop(true, true).animate({opacity:0}, 300);
				$(this).find(".thumb_hover").stop(true, true).animate({opacity:1}, 300);
			}
		}).on("mouseleave", function(){
			if($(this).find(".thumb_hover").length){
				$(this).find(".thumb").stop(true, true).animate({opacity:1}, 300);
				$(this).find(".thumb_hover").stop(true, true).animate({opacity:0}, 300);
			}
		});
	}
	
	//添加收藏夹
	$('html').on('click', '.add_favorite', function(){
		var ProId=$(this).attr("data");
		$.get('/account/favorite/add'+ProId+'.html', '', function(data){
			if(data.ret==1 || data.ret==0){
				var excheckout_html='<div id="prompt_box">';
					excheckout_html+='<div class="box_bg"></div><a class="noCtrTrack" id="prompt_close">×</a>';
					excheckout_html+='<div id="prompt_content">';
					if(data.ret==1){
						excheckout_html+='<div class="cart_view"><p>'+lang_obj.user.favorite_success+'</p></div>';
					}else{
						excheckout_html+='<div class="cart_view"><p>'+lang_obj.user.favorite_saved+'</p></div>';
					}
					excheckout_html+='<p class="footRegion">';
						excheckout_html+='<a href="javascript:;" class="btn btn-success" id="prompt_button">'+lang_obj.cart.return_shopping+'</a><a href="/account/favorite/" class="btn btn-success">'+lang_obj.user.go_to_view+'</a>';
					excheckout_html+='</p>';
					excheckout_html+='</div>';
				excheckout_html+='</div>';
				if(parseInt(ueeshop_config.FbPixelOpen)==1 && data.ret==1){//收藏成功
					//When a product is added to a wishlist.
					fbq('track', 'AddToWishlist', {content_ids:'['+data.msg.Num+']', content_name:data.msg.Name, currency:data.msg.Currency, value:'0.00'});
				}
				$('#prompt_box').length && $('#prompt_box').remove();
				$('body').prepend(excheckout_html);
				$('#prompt_box').css({left:$(window).width()/2-220});
				if(!$('#div_mask').length) global_obj.div_mask();
			}else{
				user_obj.set_form_sign_in('', '', 1);
				$('form[name=signin_form]').append('<input type="hidden" name="comeback" value="global_obj.div_mask(1);$(\'#signin_module\').remove();$(\'.add_favorite[data='+ProId+']\').click();" />');
			}
		}, 'json');
	});
	$('html').on('click', '#prompt_close, #prompt_button', function(){
		if($('#prompt_box').length){
			$('#prompt_box').remove();
			global_obj.div_mask(1);
		}
	});
	
	//语言和货币选择弹出框
	$('html').on('click', '.btn_language', function(){
		var obj={
			html:'',
			source:$('#pop_lang_currency').html()
		}
		obj.html='<div id="shopbox">'+obj.source+'</div>';
		$('#shopbox').length && $('#shopbox').remove();
		var scrollRight=window.innerWidth-$(window).width();
		$('body').addClass('hidden').css('margin-right', scrollRight).prepend(obj.html);
		if(!$('#div_mask').length) global_obj.div_mask();
	}).on('click', '.lang_item li', function(){
		$(this).parents('.pop_skin').find('.lang_item li').removeClass('current');
		$(this).addClass('current');
	}).on('click', '.btn_currency', function(){
		var $obj=$(this).parents('.pop_currency');
		if($obj.hasClass('pop_open')){
			$obj.removeClass('pop_open');
		}else{
			$obj.addClass('pop_open');
		}
		
		$(document)[0].addEventListener('click', function(e){
			var $obj=$(e.target);
			console.log($obj.parents('#shopbox').find('.pop_currency').hasClass('pop_open'));
			if(!$obj.parent().hasClass('btn_currency') && !$obj.hasClass('btn_currency') && $('#shopbox .pop_currency').hasClass('pop_open')){
				$('#shopbox .pop_currency').removeClass('pop_open');
			}
		}, false);
	}).on('click', '.pop_currency_menu a', function(){
		$('#shopbox .btn_currency').attr('data-currency', $(this).attr('data')).html($(this).html()+'<em></em>');
	}).on('click', '.btn_save', function(){
		var $lang=$('#shopbox .lang_item li.current').attr('data-lang'),
			$code=$('#shopbox .btn_currency').attr('data-currency');
		$.post('/', 'do_action=action.change_language_currency&language='+$lang+'&currency='+$code, function(data){
			if(data.ret==1){
				window.location.href=data.msg;
			}else if((data.ret==2)){
				window.open(data.msg);
			}
		}, 'json');
	});
	
	//添加购物车
	$('html').on('click', '.add_cart', function(){
		var obj={
			html:'',
			ProId:$(this).attr("data")
		}
		obj.html='<div id="shopbox">';
			obj.html+='<button class="shopbox_close"><span>×</span></button>';
			obj.html+='<div class="shopbox_wrap"><div class="shopbox_skin"><div class="shopbox_inner"><iframe id="shopbox_frame" name="shopbox_frame" class="shopbox_frame" frameborder="0" vspace="0" hspace="0" scrolling="0" src="/custom-products/'+obj.ProId+'.html" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe></div></div></div>';
		obj.html+='</div>';
		
		$('#shopbox').length && $('#shopbox').remove();
		var scrollRight=window.innerWidth-$(window).width();
		$('body').addClass('hidden').css('margin-right', scrollRight).prepend(obj.html);
		if(!$('#div_mask').length) global_obj.div_mask();
	});
	
	//共用弹出框的关闭事件
	$('html').on('click', '#shopbox .shopbox_close, #shopbox .btn_cancel, #div_mask', function(){
		$('#shopbox').shopboxHide();
	});
	
	//分享弹出框
	$('html').on('click', '.share_this', function(){
		var share=['facebook', 'google', 'twitter', 'vk', 'linkedin', 'googleplus', 'digg', 'reddit', 'stumbleupon', 'delicious', 'pinterest'],
			data=$.evalJSON($(this).attr("data")),
			html='';
		html='<div id="share_box">';
			html+='<button class="share_close"><span>×</span></button>';
			html+='<div class="share_content">';
				html+='<div class="share_hd"><span class="share_title">Share</span><span class="page_title">'+data.title+'</span><span class="page_url">'+data.url+'</span></div>';
				html+='<ul class="share_list">';
					for(k in share){
						html+='<li><button class="share_button share_'+share[k]+'" data="'+share[k]+'"></button><span class="share_label">'+share[k]+'</span></li>';
					}
				html+='</ul>';
			html+='</div>';
		html+='</div>';
		
		$('#share_box').length && $('#share_box').remove();
		$('body').prepend(html);
		if(!$('#div_mask').length) global_obj.div_mask();
		
		$('body').delegate('#share_box .share_list button', 'click', function(){
			$(this).shareThis($(this).attr('data'), data.title, data.url);
		});
	});
	$('html').on('click', '#share_box .share_close, #div_mask', function(){
		if($('#share_box').length){
			$('#share_box').remove();
			global_obj.div_mask(1);
		}
	});
	
	//产品详细页面询盘
	$('.product_inquiry').on('click',function(){
		if(ueeshop_config['UserId'] || $(this).data('user')){
			global_obj.div_mask();
			var html='<div id="inbox_popup">';
					html+='<div class="inbox_popup_close">×</div>';
					html+='<div class="inbox_popup_param">';
						html+='<div class="blank20"></div>';
						html+='<div class="inbox_popup_img pic_box"><img src="'+$('#prod_detail .big_pic img').attr('src')+'" /><span></span></div>';
						html+='<div class="inbox_popup_name">'+$('#prod_detail .detail_right h2').text()+'</div>';
						html+='<div class="clear"></div>';
					html+='</div>';
					html+='<div class="blank30"></div>';
					html+='<form id="inbox_popup_form" name="inbox_popup_form" method="post" enctype="multipart/form-data">';
						html+='<div class="inbox_popup_container">';
							html+='<div class="inbox_popup_txt">'+lang_obj.user.content+':</div>';
							html+='<div class="inbox_popup_input"><textarea class="inbox_popup_form_textarea" name="Content" notnull></textarea></div>';
							html+='<div class="clear"></div>';
						html+='</div>';
						html+='<div class="blank30"></div>';
						html+='<div class="inbox_popup_container">';
							html+='<div class="inbox_popup_txt">'+lang_obj.user.image+':</div>';
							html+='<div class="inbox_popup_input">';
								html+='<div class="inbox_popup_upload upload_box">';
									html+='<div class="clear"></div>';
									html+='<input class="upload_file" id="upload_file" type="file" name="PicPath" onchange="loadImg(this);" accept="image/gif,image/jpeg,image/png" />';
									html+='<div id="pic_show" class="pic_box"></div>';
								html+='</div>';
							html+='</div>';
							html+='<div class="clear"></div>';
						html+='</div>';
						html+='<div class="blank20"></div><div class="blank15"></div>';
						html+='<div class="inbox_popup_container">';
							html+='<input class="inbox_popup_form_submit" type="submit" value="'+lang_obj.user.send+'" />';
							html+='<input type="hidden" name="ProId" value="'+$(this).data('proid')+'">';
							html+='<input type="hidden" name="do_action" value="user.product_inquiry">';
						html+='</div>';
					html+='</form>';
				html+='</div>';
			$('body').prepend(html);
			$('#inbox_popup .inbox_popup_close').click(function(){
				$(this).parent().remove();
				global_obj.div_mask(1);
			});
			$('#inbox_popup form').submit(function(){if(global_obj.check_form($(this).find('*[notnull]'))){return false;};});
		}else{
			user_obj.set_form_sign_in('', window.location.href, 1);
		}
	});
	
	//新版文本框 临时
	$(document.body)
	$('.input_box input, .input_box textarea').on('change keyup blur force-reset', function(e){
		var n = $(e.currentTarget),
       		i = n.val(),
			o = n.closest('.input_box');
		(o.length || "force-reset" === e.type) && (i.length > 0 && "force-reset" !== e.type ? o.addClass('filled') : o.removeClass('filled'))
		/*
		if($(this).val().length>0){
			$(this).parent().addClass('filled');
		}else{
			$(this).parent().removeClass('filled');
		}
		*/
	});
});