<?php !isset($c) && exit();?>
<?php
$c['FunVersion']<1 && js::location('/');

//当前使用的节目模板
$current_page='holiday';
$holiday_row=db::get_one('sales_holiday', 'IsUsed=1');
$theme=$holiday_row['Number'];
$proid_obj=str::json_data($holiday_row['ProId'], 'decode');

$currency_url='?'.ly200::query_string('currency');

//加载内容格式
$theme_object=file_get_contents("{$c['default_path']}holiday/{$theme}/themes{$c['lang']}.json");
$theme_ary=str::json_data($theme_object, 'decode');

include("{$theme}/themes.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=ly200::seo_meta();?>
<?php include("{$c['static_path']}/inc/static.php");?>
<?=ly200::load_static( "/static/themes/default/css/holiday.css", "/static/themes/default/holiday/{$theme}/css/style.css");?>
<?php include("{$c['static_path']}inc/header.php");?>
</head>

<body class="w_1200 lang<?=$c['lang'];?>">
<?php
include("{$c['static_path']}inc/top_outer.php");
echo $theme_content;
include("{$c['theme_path']}inc/footer.php");
?>
</body>
</html>