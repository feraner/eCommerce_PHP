<?php !isset($c) && exit();?>
<?php
$file="{$c['default_path']}products/{$a}.php";
if($a=='goods_detail_pic' || $a=='goods_detail_pic_row'){ //产品详细页的主图加载
	$file="{$c['default_path']}products/detail/{$a}.php";
}
@is_file($file)?include($file):js::location('/');
?>