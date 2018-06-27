<?php !isset($c) && exit();?>
<?php
$c['FunVersion']<1 && js::location('/');

$theme='default';

//读取博客设置
$blog_set_row=array();
$set_row=db::get_all('config', "GroupId='blog'");
foreach($set_row as $v){
	$blog_set_row[$v['Variable']]=$v['Value'];
}
$Nav=str::json_data(htmlspecialchars_decode($blog_set_row['NavData']), 'decode');

//加载博客内容
ob_start();
include("{$theme}/{$a}.php");
$blog_page_contents=ob_get_contents();
ob_end_clean();
echo $blog_page_contents;
?>