<?php !isset($c) && exit();?>
<?php
$review_cfg=str::json_data(db::get_value('config', "GroupId='products_show' and Variable='review'", 'Value'), 'decode');
if($review_cfg['range']==1 && !$_SESSION['User']['UserId']) ly200::js_location('/account/');

$ProId=(int)$_GET['ProId'];
$products_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
if(!$products_row){
	@header('HTTP/1.1 404');
	exit;
}

$pro_url=ly200::get_url($products_row, 'products');
if(!$c['config']['products_show']['Config']['review']) ly200::js_location($pro_url);
$pro_img=ly200::get_size_img($products_row['PicPath_0'], '240x240');
$pro_name=$products_row['Name'.$c['lang']];
$price_ary=cart::range_price_ext($products_row);
$CateId=(int)$products_row['CateId'];
$category_row=str::str_code(db::get_one('products_category', "CateId='$CateId'"));

//在“订单产品评论时间上限”的时间里，所有的订单信息和订单产品信息
$orders_row=db::get_all('orders_products_list', "ProId='{$ProId}' and OrderId in(select OrderId from orders where UserId='{$_SESSION['User']['UserId']}' and OrderStatus=6 and {$c['time']}<PayTime+{$c['orders']['review']})", "LId, OrderId, ProId");
$orders_ary=array();
foreach((array)$orders_row as $v){
	if(!$orders_ary[$v['OrderId']]){
		$orders_ary[$v['OrderId']]=1;
	}else{
		$orders_ary[$v['OrderId']]+=1;
	}
}
//检查此会员三个月内的已经评论过的产品评论
$replyed_ary=array();
$replyed_time=86400*90;
$replyed_row=db::get_all('products_review', '1'.where::equal(array('ProId'=>$ProId, 'UserId'=>$_SESSION['User']['UserId']))." and ({$c['time']}-{$replyed_time})<AccTime", 'OrderId');
foreach((array)$replyed_row as $v){
	if(!$replyed_ary[$v['OrderId']]){
		$replyed_ary[$v['OrderId']]=1;
	}else{
		$replyed_ary[$v['OrderId']]+=1;
	}
}
//统计允许评论的订单信息
$used_ary=array();
foreach((array)$orders_ary as $k=>$v){
	if(!$replyed_ary[$k] || ($replyed_ary[$k] && $replyed_ary[$k]<$v)){//没有评论过 或者 已评论次数小于可评论总数
		$used_ary[]=$k;
	}
}
//获取订单号
$IsReview=0;
$OId=$_GET['OId'];
if($OId){//指定订单号
	$OrderId=(int)db::get_value('orders', "OId='$OId'", 'OrderId');
	if($OrderId && in_array($OrderId, $used_ary)){//可以评论
		$IsReview=1;
	}
}else{//系统指定最新可评论的订单号
	$OrderId=(int)max($used_ary);
	if($OrderId){//可以评论
		$IsReview=1;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=ly200::seo_meta();?>
<?php include("{$c['static_path']}/inc/static.php");?>
</head>

<body class="lang<?=$c['lang'];?>">
<?php include("{$c['theme_path']}/inc/header.php");?>
<div id="main" class="wide">
	<div id="location"><?=$c['lang_pack']['position'];?>: <a href="/"><?=$c['lang_pack']['home'];?></a><?=ly200::get_web_position($category_row, 'products_category');?></div>
	<div id="pro_review">
		<div class="left fl">
			<div class="title"><?=$c['lang_pack']['write_a_review'];?></div>
			<?php
			if($review_cfg['range']==2 || $IsReview>0){
			?>
				<form id="review_form" action="/account/" method="post">
					<h4><?=$c['lang_pack']['rating'];?>: <span class="star star_b0 star_h0"></span><input type="hidden" id="rating" name="Rating" value="" notnull /><p class="error_info"></p></h4>
					<?php if(!$_SESSION['User']['FirstName'] && !$_SESSION['User']['LastName']){?>
						<label><?=$c['lang_pack']['name'];?>:</label>
						<div>
							<input type="text" name="Name" class="form_input review_text" id="review_title" maxlength="50" value="" notnull />
							<p class="error_info"></p>
						</div>
					<?php }?>
					
					<label><?=$c['lang_pack']['review_content'];?>:</label>
					<div>
						<textarea name="Content" class="review_content" id="review_content" maxlength="5000" notnull></textarea>
						<p class="error_info"></p>
						<p><?=$c['lang_pack']['remaining'];?>:<b id="review_content_char">5000</b> <?=$c['lang_pack']['remaining_note'];?></p>
					</div>
					
					<div>
						<iframe src="/static/themes/default/products/review/review_img.php" name="reviews_img" id="reviews_img" width="500" frameborder="0" height="60" scrolling="no"></iframe>
						<p class="upfile_condition"><?=$c['lang_pack']['picture_tips'];?></p>
					</div>
					
					<?php if($review_cfg['code']==1){?>
						<div class="row">
							<label for="<?=$c['lang_pack']['user']['SecurityCode'];?>"><?=$c['lang_pack']['user']['SecurityCode'];?></label>
							<input name="Code" id="Code" class="lib_txt fl" type="text" size="10" maxlength="4" notnull />&nbsp;&nbsp;&nbsp;<?=v_code::create('review');?>
							<div class="clear"></div>
						</div>
						<div class="blank15"></div>
					<?php }?>
					
					<input type="submit" class="button" value="<?=$c['lang_pack']['submit'];?>" />
					<input type="hidden" name="ProId" value="<?=$ProId;?>" />
					<input type="hidden" name="OrderId" value="<?=$_SESSION['User']['UserId']?$OrderId:0;?>" />
					<input type="hidden" name="BackUrl" value="<?=$pro_url;?>" />
					<input type="hidden" name="do_action" value="user.submit_review" />
				</form>
			<?php
			}else{
				echo '<p class="error">'.$c['lang_pack']['review_tips_0'].'</p>';
			}?>
		</div>
		<div class="right fr">
			<h2 class="info_title"><?=$c['lang_pack']['beign_review'];?></h2>
			<div class="goods_view">
				<dl class="clearfix">
					<dt class="fl"><a class="pic_box" href="<?=$pro_url;?>" title="<?=$pro_name;?>"><img src="<?=$pro_img;?>" alt="<?=$pro_name;?>" /><span></span></a></dt>
					<dd>
						<p class="name"><a href="<?=$pro_url;?>" title="<?=$pro_name;?>"><?=$pro_name;?></a></p>
						<?php if($c['config']['products_show']['Config']['price']){?>
							<p class="old_price"><em class="currency_data"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data" data="<?=$products_row['Price_0'];?>"><?=cart::iconv_price($products_row['Price_0'], 2);?></span></p>
						<?php }?>
						<p class="price"><em class="currency_data FontColor"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data FontColor" data="<?=$price_ary[0];?>"><?=cart::iconv_price($price_ary[0], 2);?></span></p>
					</dd>
				</dl>
				<div class="clearfix">
					<p class="rating"><?=$c['lang_pack']['average_rating'];?>:</p>
					<span class="star star_b<?=ceil($products_row['Rating']);?>"></span> <strong class="score"><?=ceil($products_row['Rating']);?></strong> <a href="<?=$pro_url;?>" class="nums">(<?=$products_row['TotalRating'];?> <?=$c['lang_pack']['reviews'];?>)</a>
				</div>
			</div>
		</div>
		<div class="blank12"></div>
	</div>
	<div class="blank12"></div>
</div>
<?php include("{$c['theme_path']}/inc/footer.php");?>
<?=ly200::load_static('/static/js/plugin/products/review.js');?>
</body>
</html>