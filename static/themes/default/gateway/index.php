<?php !isset($c) && exit();?>
<?php
$c['plugin']=new plugin('payment');//插件类(支付插件)
$a=='fp_pay' && $a='fashionpay';
if($c['plugin']->trigger($a, '__config', $d)=='enable'){//支付插件是否存在
	$c['plugin']->trigger($a, $d);//调用支付插件
}else{
	$file="{$c['default_path']}/{$m}/{$a}/{$d}.php";
	@is_file($file)?include($file):js::location('/');
}
