<?php !isset($c) && exit();?>
<?=ly200::load_static(
	'/static/css/global.css',
	'/static/themes/default/css/global.css',
	'/static/js/jquery-1.7.2.min.js',
	'/static/js/lang/'.substr($c['lang'], 1).'.js',
	'/static/js/global.js',
	'/static/themes/default/blog/default/css/style.css',
	'/static/themes/default/blog/default/js/blog.js'
);?>
