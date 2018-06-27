/*
 * 广州联雅网络
 */

(function($){
	var $List=$(".reviews_list");
	
	if($List.length){
		ajax_review_list({"page":0, "ProId":$List.attr("data-proid"), "Action":$List.attr("data-action")}, 1);
		
		$(document).scroll(function(){
			var $winTop		= $(window).scrollTop(),
				$winH		= $(window).height(),
				$listTop	= $List.offset().top,
				$listHeight	= $List.outerHeight(),
				$loadNum	= $listTop+$listHeight-$winH-30,
				$Num		= parseInt($List.attr('data-number'));
			
			if($winTop>=$loadNum && $Num==0){
				$List.attr('data-number', '1');
				var page=parseInt($List.attr("data-page"));
				if(!isNaN(page)){
					var Total=parseInt($List.attr('data-total'));
					if(page>=Total){
						return false;
					}
					$List.attr('data-page', page+1);
					ajax_review_list({"page":page+1, "ProId":$List.attr("data-proid"), "Action":$List.attr("data-action")}, 0);
				}
			}
		});
		
		function ajax_review_list(data, clear){
			clear && $List.html('');
			$List.loading();
			$(".loading_msg").css({"top":0, "position":"initial", "width":"auto", "height":'4rem', "background-position":"center"});
			setTimeout(function(){
				$.ajax({
					url:"/ajax/review_list.html",//"/?m=ajax&a=review_list",
					async:false,
					type:'post',
					data:data,
					dataType:'html',
					success:function(result){
						if(result){
							$List.append(result).unloading();
							$List.attr('data-number', '0');
						}else{
							// var html='<div class="content_blank">'+lang_obj.seckill.no_products+'</div>';
							$List.unloading();//.append(html)
							$List.attr('data-number', '1');
						}
					}
				});
			}, 300);
		}
	}
	
	/************************** 写评论 Start **************************/
	//star
	$('#review_form .review_star>span').click(function(e){
		var star=$(this).index()+1,
			parent=$(this).parent(),
			cl=parent.attr('class');
		parent.children('span').attr('class', 'star_0');
		parent.children('span:lt('+star+')').attr('class', 'star_1');
		parent.next().val(star);
		parent.attr('class', cl.replace(/review_star_\d/,'review_star_'+star));
	});
	
	//字长判断
	$.fn.checkCharLength=function(content){
		var e=$(this);
		e.change(function(event){
			var curLength=e.val().length;
			var maxlength=e.attr('maxlength');
			if(curLength>maxlength){
				e.val($.trim(e.val()).substr(0,maxlength)).trigger('change');
				return;
			}
			$(content).text(maxlength-curLength).parent().toggleClass('red', curLength>maxlength);
		}).keyup(function(){
			e.trigger('change');
		});
	}
	$('#review_content').checkCharLength('#review_content_char');
	
	//评论提交
	$('#review_form').submit(function(){
		var flag=0,
			e=$(this),
			error={
				rating_0:'Your price is required.',
				rating_1:'Your ease of use is required.',
				rating_2:'Your build quality is required.',
				rating_3:'Your usefulness is required.',
				rating_4:'Your overall rating is required.',
				rating:'Your rating is required.',
				review_title:'Your name is required.'
			};
		
		$(".error", e).html("");
		if(global_obj.check_form($('*[notnull]', e))) flag=1;
		$('*[notnull], e').each(function(){
			var id=$(this).attr('id');
			if(!$.trim($(this).val()) && error[id]){
				$(this).next().next().html(error[id]);
				flag=1;
			}
			if(id=="review_content" && $(this).val().length>5000){
				$(this).next().html("Remaining characters:Please don\'t exceed 5,000 characters.");
				flag=1;
			}
		});

		if(flag) return false;
		
		var iframe=window.frames["reviews_img"];
		var doc=iframe.contentDocument ? iframe.contentDocument : iframe.document;
		if(!$(doc).parent('div:hidden').length){//submit the images
			doc.forms[0].submit();
			e.find("input:submit").addClass('processing').text(lang_obj.cart.processing_str+'...');
			return false;
		}
		
		//e.find("input:submit").attr('disabled', true);
	});
	/************************** 写评论 End **************************/
	//点击图片
	$('.reviews_list').on('click','.item .show_image',function(){
		$(this).show_image();
		return false;
	});
})(jQuery);