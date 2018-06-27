/*
 * 广州联雅网络
 */
(function ($){
	$(function (){
		$('.h-container h2').on('tap', function (e){
			var parent = $(this).parent('.h-container');
			if (parent.hasClass('on')){
				parent.removeClass('on');
				$('.list', parent).slideUp(200);
			}else{
				parent.addClass('on');
				$('.list', parent).slideDown(200);
			}
			
		});
		var ospan = $('.banner .btn span');
		new Swipe(document.getElementById('banner_box'), {
			speed:500,
			auto:10000,
			callback: function(){
				ospan.removeClass("on").eq(this.index).addClass("on");
			}
		});
		
	});
})(jQuery);
