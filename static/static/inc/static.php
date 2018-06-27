<?php !isset($c) && exit();?>
<?php
echo ly200::load_static(
	'/static/css/global.css',
	'/static/themes/default/css/global.css',
	'/static/themes/default/css/user.css',
	"/static/themes/{$c['theme']}/css/style.css",
	'/static/js/jquery-1.7.2.min.js',
	'/static/js/lang/'.substr($c['lang'], 1).'.js',
	'/static/js/global.js',
	'/static/themes/default/js/global.js',
	'/static/themes/default/js/user.js',
	"/static/themes/{$c['theme']}/js/main.js"
);
$fontCss="/static/themes/{$c['theme']}/css/font.css";
if(is_file($c['root_path'].$fontCss)){
	echo '<link href="'.$fontCss.'" rel="stylesheet" type="text/css">';
}

if((int)$c['config']['Platform']['Facebook']['Pixel']['IsUsed']){
?>
	<!-- Facebook Pixel Code -->
	<script type="text/javascript">
	!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
	fbq('init', '<?=$c['config']['Platform']['Facebook']['Pixel']['Data']['PixelID'];?>');
	fbq('track', "PageView");
	</script>
	<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?=$c['config']['Platform']['Facebook']['Pixel']['Data']['PixelID'];?>&ev=PageView&noscript=1" /></noscript>
	<!-- End Facebook Pixel Code -->
<?php }?>