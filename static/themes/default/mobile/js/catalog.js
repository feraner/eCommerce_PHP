/*
 * 广州联雅网络
 */
(function($){
	$(function(){
		$('.category_list .item .cate_1').off().on('tap', function(e){
			$(this).parent().toggleClass('on');
		});
	});
})(jQuery);
