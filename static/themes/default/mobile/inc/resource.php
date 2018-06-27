<?php !isset($c) && exit();?>
<?php
echo ly200::load_static(
	"{$c['mobile']['tpl_dir']}css/global.css",
	"{$c['mobile']['tpl_dir']}css/style.css",
	"{$c['mobile']['tpl_dir']}js/jquery-min.js",
	'/static/js/global.js',
	"{$c['mobile']['tpl_dir']}js/rye-touch.js",
	"{$c['mobile']['tpl_dir']}js/global.js",
	"{$c['mobile']['tpl_dir']}lang/{$c['lang']}/css/style.css",
	'/static/js/lang/'.substr($c['lang'], 1).'.js'
);

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