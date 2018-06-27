<?php !isset($c) && exit();?>
<?php
$info_category_ary=array();
$info_category_code=str::str_code(db::get_all('info_category', '1', '*', $c['my_order'].'CateId asc'));
foreach((array)$info_category_code as $v){ $info_category_ary[$v['UId']][$v['CateId']]=$v; } //所有文章分类的信息

$CateId=(int)$_GET['CateId'];
$InfoId=(int)$_GET['InfoId'];
if($InfoId){
	$info_row=str::str_code(db::get_one('info', "InfoId='$InfoId'"));
	$CateId=(int)$info_row['CateId'];
	$CateId && $category_row=str::str_code(db::get_one('info_category', "CateId='$CateId'"));
	if($category_row['UId']!='0,'){
		$TopCateId=category::get_top_CateId_by_UId($category_row['UId']);
	}
	$info_content_row=str::str_code(db::get_one('info_content', "InfoId='$InfoId'"));
	$Column=$info_row['Title'.$c['lang']]; //标题
	$seo_txt=$Column.','.$category_row['Category'.$c['lang']]; //SEO内容
	$spare_ary=array('SeoTitle'=>$seo_txt, 'SeoKeyword'=>$seo_txt, 'SeoDescription'=>$seo_txt); //SEO
}else{
	$where='1';//条件
	$query_string=ly200::get_query_string(ly200::query_string('m, a, CateId, page'));
	$UId=db::get_value('info_category', "CateId='$CateId'", 'UId');
	$CateId && $where.=" and (CateId in(select CateId from info_category where UId like '{$UId}{$CateId},%') or CateId='{$CateId}')";
	$info_row=str::str_code(db::get_all('info', $where, "InfoId, CateId, Title{$c['lang']}, AccTime", $c['my_order'].'CateId asc', $page, $page_count));
	$Column=$info_category_ary[$UId][$CateId]['Category'.$c['lang']];
	$spare_ary=array('SeoTitle'=>$Column); //SEO
	if($UId!='0,'){ //非大类
		$TopCateId=category::get_top_CateId_by_UId($UId);
		$TopCategory_row=str::str_code(db::get_one('info_category', "CateId='$TopCateId'"));
		$spare_ary['SeoKeyword']=$spare_ary['SeoDescription']=$Column.','.$TopCategory_row['Category'.$c['lang']];
	}else{ //大类
		$subcateStr='';
		$subcate_row=db::get_limit('info_category', "UId like '0,{$CateId},%'", 'Category'.$c['lang'], $c['my_order'].'CateId asc', 0, 20);
		foreach((array)$subcate_row as $v){ $subcateStr.=','.$v['Category'.$c['lang']]; }
		$spare_ary['SeoKeyword']=$spare_ary['SeoDescription']=$Column.$subcateStr;
	}
}
if(!$info_row){
	@header('HTTP/1.1 404');
	exit;
}
?>
<!DOCTYPE HTML>
<html lang="<?=substr($c['lang'], 1);?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<?=ly200::seo_meta($InfoId?$info_row:$info_category_ary[$UId][$CateId], $spare_ary);?>
<?php include("{$c['mobile']['theme_path']}inc/resource.php");?>
</head>

<body>
<?php include("{$c['mobile']['theme_path']}inc/header.php");?>
<div class="wrapper">
	<?=html::mobile_crumb('<em><i></i></em><a href="javascript:;">'.$Column.'</a>');?>
    <div class="art_content">
		<?php
		if($InfoId){
			echo str_replace('%nbsp;', ' ', str::str_code($info_content_row['Content'.$c['lang']], 'htmlspecialchars_decode'));
		}else{
		?>
		<ul class="info_list">
			<?php
			foreach($info_row as $k=>$v){
			?>
			<li class="clean">
				<a href="<?=ly200::get_url($v, 'info');?>" title="<?=$v['Title'.$c['lang']];?>"><?=$v['Title'.$c['lang']];?></a>
				<span class="time"><?=date('Y-m-d', $v['AccTime']);?></span>
			</li>
			<?php }?>
		</ul>
		<?php }?>
    </div>
</div>
<?php include("{$c['mobile']['theme_path']}inc/footer.php");?>
</body>
</html>