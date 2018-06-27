/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
var products_list_obj={
	init:function(){
		products_list_obj.price_limit($('#minprice'), $('#maxprice'), $('#submit_btn'));
		
		var $effectsVal=$("#prod_list").attr("effects");
		if($effectsVal==1){
			$("#prod_list .prod_box").off().on("mouseenter", function(){
				$(this).addClass('hover_1');
			}).on("mouseleave", function(){
				$(this).removeClass('hover_1');
			});
		}else if($effectsVal==2){
			$("#prod_list .prod_box").off().on("mouseenter", function(){
				$(this).addClass('hover_2');
			}).on("mouseleave", function(){
				$(this).removeClass('hover_2');
			});
		}else if($effectsVal==3){
			$("#prod_list .prod_box").off().on("mouseenter", function(){
				$(this).addClass('hover_3');
			}).on("mouseleave", function(){
				$(this).removeClass('hover_3');
			});
		}else if($effectsVal==4){
			$("#prod_list .prod_box").off().on("mouseenter", function(){
				$(this).addClass('hover_4');
			}).on("mouseleave", function(){
				$(this).removeClass('hover_4');
			});
		}else if($effectsVal==5){
			$("#prod_list .prod_box").off().on("mouseenter", function(){
				$(this).children(".prod_box_pic").addClass("pic_enlarge");
			}).on("mouseleave", function(){
				$(this).children(".prod_box_pic").removeClass("pic_enlarge");
			});
		}else if($effectsVal==6){
			$("#prod_list .prod_box").off().on("mouseenter", function(){
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
	},
	
	price_limit:function(min_obj, max_obj, btn_obj){
		btn_obj.click(function(){
			var url=$(this).next().val(),
				p0=min_obj.val()?parseFloat(min_obj.val()):0,
				p1=max_obj.val()?parseFloat(max_obj.val()):0;
			
			if(p0>=0 && p1>=0){
				if(p0>0 && p1>0 && p0>p1){
					min_obj.val(p1);
					max_obj.val(p0);
					p0=parseFloat(min_obj.val()),
					p1=parseFloat(max_obj.val());
				}
				if(p0==0 && p1==0){
					window.location=url;
				}else{
					window.location=url+'&Price='+p0+'-'+p1;
				}
			}
		});
	}
}