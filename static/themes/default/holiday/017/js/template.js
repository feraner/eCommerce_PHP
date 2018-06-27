(function($){
	$(".pro_list_0 .list_menu > a").click(function(){
		var $this=$(this),
			$index=$this.index();
		$this.addClass("current").siblings().removeClass("current");
		$this.parents(".pro_list_0").children(".list_body").children("div").eq($index).show().siblings().hide();
		return false;
	});
	
	$('.main_top, .banner_1, .banner_2').click(function(){
		window.top.location.href=$(this).attr('data-url');
	});
})(jQuery);