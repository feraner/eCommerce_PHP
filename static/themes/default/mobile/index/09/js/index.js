/*
 * 广州联雅网络
 */
(function ($){
	$(function (){
		new Swipe(document.getElementById('banner_box'), {
			speed:500,
			auto:10000,
			callback: function(){}
		});
		var _w = $(window).width();
		function touch_pro(id){
			var touch0 = $('#'+id);
			var list0 = $('.list', touch0);
			var item0 = $('.item', list0);
			var w0 = 0;
			item0.each(function(index, element) {
				w0 += Math.ceil($(element).outerWidth(true));
			});
			list0.width(w0);
			touch_nav(list0, w0, _w);
		}
		touch_pro('touch0');
		
	});
})(jQuery);
