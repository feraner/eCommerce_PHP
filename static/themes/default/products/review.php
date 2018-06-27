<?php !isset($c) && exit();?>
<?php
$ProId=(int)$_GET['ProId'];
$products_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
if(!$products_row){
	@header('HTTP/1.1 404');
	exit;
}

$pro_url=ly200::get_url($products_row, 'products');
$Name=$products_row['Name_en'];
$price_ary=cart::range_price_ext($products_row);
$CateId=(int)$products_row['CateId'];
$category_row=str::str_code(db::get_one('products_category', "CateId='$CateId'"));

$Rating=($products_row['IsDefaultReview'] && $products_row['DefaultReviewRating'])?(int)$products_row['DefaultReviewRating']:ceil($products_row['Rating']);
$TotalRating=($products_row['IsDefaultReview'] && $products_row['DefaultReviewTotalRating'])?$products_row['DefaultReviewTotalRating']:$products_row['TotalRating'];

$is_review=db::get_row_count('products_review', "ProId='$ProId'")?false:true;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=ly200::seo_meta();?>
<?php include("{$c['static_path']}/inc/static.php");?>
<?=ly200::load_static('/static/js/plugin/lightbox/css/lightbox.min.css');?>
</head>

<body class="lang<?=$c['lang'];?>">
<?php include("{$c['theme_path']}/inc/header.php");?>
<div id="main" class="wide">
	<div id="location"><?=$c['lang_pack']['position'];?>: <a href="/"><?=$c['lang_pack']['home'];?></a><?=ly200::get_web_position($category_row, 'products_category');?></div>
	<div id="pro_detail">
		<div class="review_left fl">
			<div class="goods review_goods">
				<h2 class="goods_title"><?=$c['lang_pack']['customer_review'];?></h2>
				<div class="goods_view">
					<dl class="clearfix">
						<dt class="fl"><a class="pic_box" href="<?=$pro_url;?>" title="<?=$products_row['Name'.$c['lang']];?>"><img src="<?=ly200::get_size_img($products_row['PicPath_0'], '168x168');?>" /><span></span></a></dt>
						<dd>
							<p class="name"><a href="<?=$pro_url;?>" title="<?=$products_row['Name'.$c['lang']];?>"><?=$products_row['Name'.$c['lang']];?></a></p>
							<?php if($c['config']['products_show']['Config']['price']){?><p class="old_price"><em class="currency_data"></em><span class="price_data" data="<?=$products_row['Price_0'];?>"></span></p><?php }?>
							<p class="price"><em class="currency_data FontColor"></em><span class="price_data FontColor" data="<?=$price_ary[0];?>"></span></p>
						</dd>
					</dl>
				</div>
			</div>
			<?php include('review/review_box.php');?>
		</div>
		<div class="review_right fr">
			<div id="may_like" class="sidebar">
				<h2 class="b_title"><?=$c['lang_pack']['you_may_like'];?></h2>
				<div class="b_list">
					<?php
					$row=str::str_code(db::get_limit('products', '1'.$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 9));
					$len=count($row);
					foreach((array)$row as $k=>$v){
						$url=ly200::get_url($v, 'products');
						$img=ly200::get_size_img($v['PicPath_0'], '240x240');
						$name=$v['Name'.$c['lang']];
						$price_ary=cart::range_price_ext($v);
					?>
					<dl class="pro_item clearfix<?=$k+1==$len?' last':'';?>">
						<dt class="fl"><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a></dt>
						<dd class="fl pro_info">
							<div class="pro_name"><a href="<?=$url;?>" title="<?=$name;?>"><?=mb_substr($name, 0, 45);?>..</a></div>
							<div class="pro_price"><em class="currency_data FontColor"></em><span class="price_data FontColor" data="<?=$price_ary[0];?>"></span></div>
						</dd>
					</dl>
					<?php }?>
				</div>
			</div>
		</div>
		<div class="blank12"></div>
	</div>
	<div class="blank12"></div>
</div>
<?php include("{$c['theme_path']}/inc/footer.php");?>
<?=ly200::load_static('/static/js/plugin/products/review.js', '/static/js/plugin/lightbox/js/lightbox.min.js');?>
</body>
</html>