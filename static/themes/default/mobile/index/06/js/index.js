/*
 * 广州联雅网络
 */

$(function(){
	var ospan=$('.banner .btn span');
	new Swipe(document.getElementById('banner_box'), {
		speed:500,
		auto:10000,
		callback: function(){
			ospan.removeClass("on").eq(this.index).addClass("on");
		}
	});
});
