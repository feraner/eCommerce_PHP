<?php !isset($c) && exit();?>
<?php
$CateId=(int)$_GET['CateId'];
$Keyword=$_GET['Keyword'];
$date=(int)$_GET['date'];
$where='1';//条件
$page_count=10;//显示数量
$CateId && $where.=' and '.category::get_search_where_by_CateId($CateId, 'blog_category');
$Keyword && $where.=" and Title like '%$Keyword%'";
if($date){
	if(date('m', $date)==12){
		$next_m=mktime(0,0,0,1,1,date('Y',$date)+1);
	}else $next_m=mktime(0,0,0,date('m',$date)+1,1,date('Y',$date));
	$where.=" and AccTime BETWEEN $date and $next_m";
}
$blog_row=str::str_code(db::get_limit_page('blog', $where, '*', $c['my_order'].'AId desc', (int)$_GET['page'], $page_count));

//SEO
if((int)$CateId){
	$category=str::str_code(db::get_value('blog_category', "CateId='$CateId'", 'Category_en'));
	$seo_row=array(
		'SeoTitle'.$c['lang']		=>	$category,
		'SeoKeyword'.$c['lang']		=>	$category,
		'SeoDescription'.$c['lang']	=>	$category
	);
}elseif((int)$date){
	$spare_ary=array(
		'SeoTitle'		=>	date('F Y', $date),
		'SeoKeyword'	=>	date('F Y', $date),
		'SeoDescription'=>	date('F Y', $date)
	);
}else{
	$seo_row=str::str_code(db::get_one('meta', "Type='blog'"));
	$spare_ary=array(
		'SeoTitle'		=>	'Blog',
		'SeoKeyword'	=>	'Blog',
		'SeoDescription'=>	'Blog'
	);
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<?=ly200::seo_meta($seo_row, $spare_ary);?>
<?php include("inc/static.php");?>
</head>

<body>
<div class="wrapper bgWhite">
	<?php include('inc/header.php');?>
    <?php include('inc/banner.php');?>
    <?php include('inc/nav.php');?>
    <div class="w970 clean overhide">
    	<div class="lside fl">
        	<?php foreach((array)$blog_row[0] as $k=>$v){?>
				<div class="section">
					<h2><?=$v['Title'];?></h2>
					<div class="info">By <?=$v['Author'];?> | <?=date('d F Y', $v['AccTime']);?> | <?=(int)db::get_row_count('blog_review', "AId='{$v['AId']}'");?> Comments</div>
					<div class="content"><?=str::format($v['BriefDescription']);?></div>
					<a href="<?=ly200::get_url($v, 'blog');?>" target="_blank" class="read">Continue Reading >></a>
				</div>
            <?php }?>
            <div class="blank20"></div>
			<div id="turn_page"><?=ly200::turn_page_html($blog_row[1], $blog_row[2], $blog_row[3], '', 'Previous', 'Next');?></div>
        </div>
        <div class="rside fr">
        	<?php include('inc/right_side.php');?>
        </div>
    </div>
    <?php include('inc/footer.php');?>
</div>
</body>
</html>