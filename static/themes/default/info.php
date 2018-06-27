<?php !isset($c) && exit();?>
<?php
$info_category_ary=array();
$info_category_code=str::str_code(db::get_all('info_category', '1', '*', $c['my_order'].'CateId asc'));
foreach((array)$info_category_code as $v){ $info_category_ary[$v['UId']][$v['CateId']]=$v; } //所有文章分类的信息

$current_page='info';
$CateId=(int)$_GET['CateId'];
$InfoId=(int)$_GET['InfoId'];
if($InfoId){ //文章详细
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
}else{ //文章列表
	$page=(int)$_GET['page'];
	$page_count=20; //显示数量
	!$CateId && $CateId=(int)db::get_value('info_category', 'UId="0,"', 'CateId', $c['my_order'].'CateId asc');
	$UId=db::get_value('info_category', "CateId='$CateId'", 'UId');
	$where="1 and (CateId in(select CateId from info_category where UId like '{$UId}{$CateId},%') or CateId='$CateId')"; //条件
	$info_row=str::str_code(db::get_limit_page('info', $where, "InfoId, CateId, Title{$c['lang']}, EditTime, AccTime, Url", $c['my_order'].'InfoId desc', $page, $page_count));
	$Column=$info_category_ary[$UId][$CateId]['Category'.$c['lang']]; //标题
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
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
echo ly200::seo_meta($InfoId?$info_row:$info_category_ary[$UId][$CateId], $spare_ary);
include("{$c['static_path']}/inc/static.php");
?>
</head>

<body class="lang<?=$c['lang'];?>">
<?php include("{$c['theme_path']}/inc/header.php");?>
<div id="main" class="wide">
	<div class="blank20"></div>
	<div class="side_left fl">
		<div class="help_menu">
			<?php
			foreach((array)$info_category_ary['0,'] as $v){
				$url=ly200::get_url($v, 'info_category');
				$name=$v['Category'.$c['lang']];
			?>
				<div class="help_title"><a<?=$CateId==$v['CateId']?' class="current"':'';?> hidefocus="true" href="<?=$url;?>" title="<?=$name;?>"><?=$name;?></a></div>
				<?php if($info_category_ary["0,{$v['CateId']},"]){?>
					<ul class="help_list">
						<?php
						foreach((array)$info_category_ary["0,{$v['CateId']},"] as $v2){
							$url=ly200::get_url($v2, 'info_category');
							$name=$v2['Category'.$c['lang']];
						?>
							<li><a<?=$CateId==$v2['CateId']?' class="current"':'';?> hidefocus="true" href="<?=$url;?>" title="<?=$name;?>"><?=$name;?></a></li>
						<?php }?>
					</ul>
			<?php
				}
			}?>
		</div>
	</div>
	<div class="side_right fr right_main">
		<div class="main_title"><?=$Column;?></div>
		<div class="main_content">
			<?php if($InfoId){?>
				<div class="editor_txt">
					<?=str_replace('%nbsp;', ' ', str::str_code($info_content_row['Content'.$c['lang']], 'htmlspecialchars_decode'));?>
                    <?php if($c['config']['content_show']['Config']['share']){?>
						<div class="blank9"></div>
                        <div class="clean">
                        	<!-- Go to www.addthis.com/dashboard to customize your tools -->
                        	<div class="addthis_sharing_toolbox"></div>
							<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-559f74332bfa6ac0" async="async"></script>
                        </div>
					<?php }?>
                </div>
			<?php }else{?>
				<ul class="info_list">
					<?php
					foreach((array)$info_row[0] as $k=>$v){
					?>
						<li><a href="<?=ly200::get_url($v, 'info');?>" title="<?=$v['Title'.$c['lang']];?>"><?=$v['Title'.$c['lang']];?></a><span class="time"><?=date('Y-m-d', ($v['EditTime']?$v['EditTime']:$v['AccTime']));?></span></li>
					<?php }?>
				</ul>
				<div class="blank20"></div>
				<div id="turn_page"><?=ly200::turn_page_html($info_row[1], $info_row[2], $info_row[3], $no_page_url, $c['lang_pack']['previous'], $c['lang_pack']['next']);?></div>
			<?php }?>
		</div>
	</div>
	<div class="blank25"></div>
</div>
<?php include("{$c['theme_path']}/inc/footer.php");?>
</body>
</html>