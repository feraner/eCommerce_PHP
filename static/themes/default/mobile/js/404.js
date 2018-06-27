jQuery(function($){
	var windowH=$(window).outerHeight(),
		headerH=$("header").outerHeight(),
		footerH=$("footer").outerHeight(),
		errorH=$("#error_page").outerHeight();
		
	if((headerH+footerH+errorH)<windowH){
		$("#error_page").css({"margin":((windowH-headerH-footerH-errorH)/2)+"px auto"});
	}
});