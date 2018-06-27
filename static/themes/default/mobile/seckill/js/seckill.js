/*
 * Powered by ueeshop.com		http://www.ueeshop.com
 * 广州联雅网络科技有限公司		020-83226791
 */

var seckill_obj={
	parameter:{
		prolist:$("#prolist"),
		animate:1,
		cateid:0,
		typ:'dealing',
		page:1
	},
	seckill_init:function(){
		$("#deals_menu").off().on("change", function(){
			var $type=$(this).val();
			$(".seck_title").text($(this).find("option:selected").text());
			if($type=="dealing"){
				$(".seck_menu .category").show();
			}else{
				$(".seck_menu .category").hide();
			}
			seckill_obj.parameter.page=1;
			seckill_obj.parameter.cateid=$(this).attr("data");
			seckill_obj.parameter.typ=$type;
			seckill_obj.ajax_seckill_list(1);
		});
		$("#deals_menu option:eq(0)").attr("selected", true).change();
		
		$(".seck_menu .category_fixed>a").off().on("tap", function(){
			$(this).addClass("current").siblings().removeClass("current");
			seckill_obj.parameter.page=1;
			seckill_obj.parameter.cateid=$(this).attr("data");
			seckill_obj.ajax_seckill_list(1);
			return false;
		});
		
		$(".seck_btn .btn_more").off().on("tap", function(){
			var $this=$(this),
				$Obj=$(".seck_menu .category"),
				$CateHeight=$(".seck_menu .category_fixed").outerHeight();
			if($Obj.hasClass("cate_show")){ //隐藏
				$Obj.removeClass("cate_show");
				$Obj.animate({"height":69.6}, 500, function(){
					$Obj.removeAttr("style");
					$this.removeClass("hide").find("span").html(lang_obj.global.display[0]);
				});
			}else{ //显示
				$Obj.addClass("cate_show");
				$Obj.animate({"height":$CateHeight}, 500, function(){
					$this.addClass("hide").find("span").html(lang_obj.global.display[1]);
				});
			}
			return false;
		});
		if($(".seck_menu .category_fixed").outerHeight()>90){
			$(".seck_btn").show();
		}
		
		var seckill_timer=new Object; //秒杀页面计时器ID，防止时间乱跳
		seckill_obj.ajax_seckill_list(1);
	},
	ajax_seckill_init:function(){
		seckill_obj.parameter.prolist.find(".btn_view").off().on("tap", function(){
			$(this).remove();
			seckill_obj.ajax_seckill_list();
		});
		
		seckill_obj.parameter.prolist.find(".item").off().on("tap", function(){
			if($(this).attr('data-url')){
				window.top.location=$(this).attr('data-url');
			}
			return false;
		});
		
		if(seckill_obj.parameter.prolist.find(".content_more").length){
			seckill_obj.parameter.animate=0; //没有产品了，不再查询
			setTimeout(function(){
				seckill_obj.parameter.prolist.find(".content_more").fadeOut();
			}, 2000);
		}
		
		seckill_obj.parameter.prolist.find(".time_box_"+seckill_obj.parameter.page).each(function(){
			var obj=$(this).find("span"),
				time=new Date(),
				proid=obj.attr("data-proId");
			obj.genTimer({
				beginTime: ueeshop_config.date,
				targetTime: obj.attr("data-endTime"),
				callback: function(e){
					$("#flashsale_"+proid).html(e);
				}
			});
		});
	},
	ajax_seckill_list:function(clear){
		if(seckill_obj.parameter.animate){
			if(clear){
				seckill_obj.parameter.prolist.html("");
				for(i in seckill_timer){
					clearInterval(seckill_timer[i]);//清除计时器，防止时间乱跳
				}
			}
			seckill_obj.parameter.animate=0;
			seckill_obj.parameter.prolist.loading();
			$(".loading_msg").css({"top":0, "position":"initial", "width":"auto", "height":"4rem", "background-position":"center"});
			$.post("/ajax/seckill_default.html", {page:seckill_obj.parameter.page, CateId:seckill_obj.parameter.cateid, typ:seckill_obj.parameter.typ, r:Math.random()}, function(result){
				if(result){
					seckill_obj.parameter.prolist.append(result).unloading();
					seckill_obj.ajax_seckill_init();
					seckill_obj.parameter.page+=1;
					seckill_obj.parameter.animate=1;
				}
			});
		}
	}
};