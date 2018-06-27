/*
 * Powered by ueeshop.com		http://www.ueeshop.com
 * 广州联雅网络科技有限公司		020-83226791
 */

var tuan_obj={
	parameter:{
		prolist:$("#prolist"),
		animate:1,
		cateid:0,
		typ:'this',
		page:1
	},
	tuan_init:function(){
		$("#group_menu").off().on("change", function(){
			var $type=$(this).val();
			$(".tuan_title").text($(this).find("option:selected").text());
			tuan_obj.parameter.page=1;
			tuan_obj.parameter.cateid=$(this).attr("data");
			tuan_obj.parameter.typ=$type;
			tuan_obj.ajax_tuan_list(1);
		});
		$("#group_menu option:eq(0)").attr("selected", true).change();
		
		$(".tuan_menu .category_fixed>a").off().on("tap", function(){
			$(this).addClass("current").siblings().removeClass("current");
			tuan_obj.parameter.page=1;
			tuan_obj.parameter.cateid=$(this).attr("data");
			tuan_obj.ajax_tuan_list(1);
			return false;
		});
		
		$(".tuan_btn .btn_more").off().on("tap", function(){
			var $this=$(this),
				$Obj=$(".tuan_menu .category"),
				$CateHeight=$(".tuan_menu .category_fixed").outerHeight();
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
		if($(".tuan_menu .category_fixed").outerHeight()>90){
			$(".tuan_btn").show();
		}
		
		tuan_obj.ajax_tuan_list(1);
	},
	ajax_tuan_init:function(){
		tuan_obj.parameter.prolist.find(".btn_view").off().on("tap", function(){
			$(this).remove();
			tuan_obj.ajax_tuan_list();
		});
		
		tuan_obj.parameter.prolist.find(".item").off().on("tap", function(){
			if($(this).attr('data-url')){
				window.top.location=$(this).attr('data-url');
			}
			return false;
		});
		
		if(tuan_obj.parameter.prolist.find(".content_more").length){
			tuan_obj.parameter.animate=0; //没有产品了，不再查询
			setTimeout(function(){
				tuan_obj.parameter.prolist.find(".content_more").fadeOut();
			}, 2000);
		}
		
		tuan_obj.parameter.prolist.find(".time_box_"+tuan_obj.parameter.page).each(function(){
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
	ajax_tuan_list:function(clear){
		if(tuan_obj.parameter.animate){
			if(clear){
				tuan_obj.parameter.prolist.html("");
			}
			tuan_obj.parameter.animate=0;
			tuan_obj.parameter.prolist.loading();
			$(".loading_msg").css({"top":0, "position":"initial", "width":"auto", "height":"4rem", "background-position":"center"});
			$.post("/ajax/tuan_default.html", {page:tuan_obj.parameter.page, CateId:tuan_obj.parameter.cateid, typ:tuan_obj.parameter.typ, r:Math.random()}, function(result){
				if(result){
					tuan_obj.parameter.prolist.append(result).unloading();
					tuan_obj.ajax_tuan_init();
					tuan_obj.parameter.page+=1;
					tuan_obj.parameter.animate=1;
				}
			});
		}
	}
};

/*
(function($){
	var list_box= $('#list_box'),
		page	= 1,
		t		= 1;
	ajax_tuan_list();//首次运行
	
	function ajax_tuan_init(){
		$('#list_box .btn_view').off().on('tap', function(){
			$(this).remove();
			ajax_tuan_list();
		});
		
		if($("#list_box .content_more").length){
			t=0;//没有产品了，不再查询
			setTimeout(function(){
				$("#list_box .content_more").fadeOut();
			}, 2000);
		}
		
		$('.time_page'+(page-1), list_box).each(function(k, v){
			var time=new Date();
			$(this).genTimer({
				beginTime: ueeshop_config.date,
				targetTime: $(this).attr("endTime"),
				callback: function(e){
					this.html(e);
				}
			});
		});
		
		$('#list_box .item').on('tap', function(){
			window.top.location.href=$(this).attr('data-url');
			return false;
		});
	}
	
	function ajax_tuan_list(){
		if(t){
			t=0;
			list_box.loading();
			$(".loading_msg").css({"top":0, "position":"initial", "width":"auto", "height":'4rem', "background-position":"center"});
			$.get("/ajax/tuan_default.html", {page:page, r:Math.random()}, function(d){
				if(d){
					page++;
					t=1;
					list_box.append(d).unloading();
					ajax_tuan_init();
				}
			});//
		}// if t end
	}
})(jQuery);
*/