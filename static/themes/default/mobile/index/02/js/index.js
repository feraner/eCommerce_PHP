/*
 * 广州联雅网络
 */
(function ($){
	$(function (){
		new Swipe(document.getElementById('banner_box'), {
			speed:500,
			auto:10000
		});
		var newsletter = $('#newsletter');
		$('input[name=submit]', newsletter).on('tap', function (){
			var email = $('input[name=Email]', newsletter).val();
			
			if (/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/.test(email)){
				newsletter.submit(function(e) {return false;});
				$.post('/init.html', newsletter.serialize(), function (data){
					if (data.status==1){
						alert('Added to subscribe successful!');
					}else{
						alert('"' + email + '" This mailbox already exists subscription!');
					}
				}, 'json');
			}else{
				alert('Please fill in the correct Email address!');
			}
		});
	})
})(jQuery);
