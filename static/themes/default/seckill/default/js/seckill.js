/*
 * Powered by ueeshop.com		http://www.ueeshop.com
 * 广州联雅网络科技有限公司		020-83226791
 */

var seckill_obj={
	seckill_init:function(){
		$("#seck_title").delegate("a", "click", function(){
			var $type=$(this).attr("data-type");
			$(this).addClass("current").siblings().removeClass("current");
			if($type=="dealing"){
				$(".seck_menu .category").show();
			}else{
				$(".seck_menu .category").hide();
			}
			ajax_seckill_list({"page":0, "CateId":0, "Sort":$(".seck_sort a.current").attr("data-sort"), "typ":$type}, 1);
			return false;
		});
		
		$(".seck_menu .category").delegate("a", "click", function(){
			$(this).addClass("current").siblings().removeClass("current");
			ajax_seckill_list({"page":0, "CateId":$(this).attr("data"), "Sort":$(".seck_sort a.current").attr("data-sort"), "typ":$("#seck_title .current").attr("data-type")}, 1);
			return false;
		});
		
		$(".seck_sort").delegate("a", "click", function(){
			var $children=$(this).children("i"),
				$num=parseInt($(this).index())+1,
				$sort=$(this).attr("data-sort");
			$(this).addClass("current").siblings().removeClass("current").children("i").attr("class", "icon_sort");
			if($num==1){
				if($children.hasClass("icon_sort_up")){
					$children.attr("class", "icon_sort_down");
					$sort="1d";
				}else if($children.hasClass("icon_sort_down")){
					$children.attr("class", "icon_sort_up");
					$sort="1a";
				}else{
					$children.attr("class", "icon_sort_up");
					$sort="1a";
				}
			}else{
				if($children.hasClass("icon_sort")){
					$children.attr("class", "icon_sort_down");
					$sort=$num+"d";
				}else{
					$children.attr("class", "icon_sort");
					$sort=$num;
				}
			}
			$(this).attr("data-sort", $sort);
			ajax_seckill_list({"page":0, "CateId":$(this).attr("data"), "Sort":$sort, "typ":$("#seck_title .current").attr("data-type")}, 1);
			return false;
		});
		
		ajax_seckill_list({"page":0, "CateId":$(".seck_menu .category a.current").attr("data"), "Sort":$(".seck_sort a.current").attr("data-sort"), "typ":$("#seck_title .current").attr("data-type")}, 1);
		
		/*
		$(document).scroll(function(){
			var $winTop		= $(window).scrollTop(),
				$winH		= $(window).height(),
				$listTop	= $("#prolist").offset().top,
				$listHeight = $("#prolist").outerHeight(),
				$loadNum	= $listTop+$listHeight-$winH-30;
			
			Num=parseInt($("#prolist").attr("Num"));
			if($winTop>=$loadNum && Num==0){
				$("#prolist").attr("Num", "1");
				var page=parseInt($("#prolist ul:last").attr("page"));
				if(!isNaN(page)){
					var Total=parseInt($("#prolist ul:last").attr("total"));
					if(page>=Total){
						return false;
					}
					ajax_seckill_list({"page":page+1, "CateId":$(".seck_menu .category a.current").attr("data"), "typ":$("#seck_title .current").attr("data-type")}, 0);
				}
			}
		});
		*/
		
		function ajax_seckill_list(data, clear){
			clear && $("#prolist").html("");
			$("#prolist").loading();
			$(".loading_msg").css({"top":0, "position":"initial", "width":"auto", "height":523, "background-position":"center"});
			setTimeout(function(){
				$.ajax({
					url:"/ajax/seckill_default.html",
					async:false,
					type:"post",
					data:data,
					dataType:"html",
					success:function(result){
						if(result){
							$("#prolist").append(result).unloading();
							$("#prolist").attr("Num", "0");
							
							$("#turn_page").delegate("a", "click", function(){
								var page=$(this).attr("href").replace('Page_', '');
								ajax_seckill_list({"page":page, "CateId":$(".seck_menu .category a.current").attr("data"), "typ":$("#seck_title .current").attr("data-type")}, 1);
								return false;
							});
						}else{
							var html='<div class="blank25"></div><div id="loading_tips">"+lang_obj.seckill.no_products+"</div>';
							$("#prolist").append(html).unloading();
							$("#prolist").attr("Num", "1");
						}
					}
				});
			}, 500);
		}
	}
};
