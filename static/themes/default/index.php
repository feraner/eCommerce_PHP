<?php !isset($c) && exit();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=ly200::seo_meta();?>
<?php include("{$c['static_path']}/inc/static.php");?>
<?=ly200::load_static("/static/themes/{$c['theme']}/css/index.css");?>
</head>

<body class="lang<?=$c['lang'];?>">
<?php include("{$c['default_path']}/inc/header.php");?>
<?php
if(!file::check_cache('index.html')){
	ob_start();
?>
<div id="banner">
	<div class="wide">
		<dl class="banner">
			<dt><?=ly200::ad(1);?></dt>
			<dd>
				<ul>
					<?php
					$ad_ary=ly200::ad_custom(2);
					for($i=0; $i<$ad_ary['Count']; ++$i){
						$url=$ad_ary['Url'][$i][$ad_ary['Lang']];
					?>
					<li<?=$i==1?' class="middle"':'';?>>
						<?php if($url){?><a href="<?=$url;?>" target="_blank"><?php }?>
							<div class="img"><img src="<?=$ad_ary['PicPath'][$i][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][$i][$ad_ary['Lang']];?>" /></div>
							<h2 class="FontColor"><?=$ad_ary['Name'][$i][$ad_ary['Lang']];?></h2>
							<span><?=$ad_ary['Brief'][$i][$ad_ary['Lang']];?></span>
						<?php if($url){?></a><?php }?>
					</li>
					<?php }?>
				</ul>
			</dd>
		</dl>
	</div>
</div>
<div id="main" class="wide">
    <div class="pro_left fl">
		<?php if((int)$c['config']['global']['RecentOrders']) include("{$c['static_path']}/inc/order_live.php");?>
        <?php include("{$c['default_path']}/inc/what_hot.php");?>
		<?php include("{$c['default_path']}/inc/special_offer.php");?>
        <?php include("{$c['default_path']}/inc/popular_search.php");?>
		<div class="blank20"></div>
		<?=ly200::ad(3);?>
		<div class="blank20"></div>
		<?=ly200::ad(4);?>
    </div>
    <div class="pro_right fr">
        <div class="prod_list" effects="<?=$c['config']['global']['Effects'];?>">
			<div class="title">
				<h3 class="fl"><?=$c['lang_pack']['best_deals'];?></h3>
				<a href="/Best-Deals/" class="fr"><?=$c['lang_pack']['more'];?> >></a>
			</div>
			<div class="blank15"></div>
			<?php
			$products_list_row=str::str_code(db::get_limit('products', '1'.where::equal(array('IsBestDeals'=>'1', 'IsIndex'=>'1')).$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 8));
			foreach((array)$products_list_row as $k=>$v){
				$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
				$url=ly200::get_url($v, 'products');
				$name=$v['Name'.$c['lang']];
				$price_ary=cart::range_price_ext($v);
				$price_0=$v["Price_{$is_promition}"];
				$RatingCount=$v['TotalRating'];
				$Rating=@ceil($v['Rating']);
				if((int)$v['IsDefaultReview']){
					$RatingCount+=$v['DefaultReviewTotalRating'];
					$Rating= $RatingCount ? @ceil(($v['Rating']*$v['TotalRating']+$v['DefaultReviewRating']*$v['DefaultReviewTotalRating'])/$RatingCount) : 5;
				}
				$content='
					<div class="pro_name"><a href="'.$url.'" title="'.$name.'">'.str::str_echo($name, 45, 0, '..').'</a></div>
					<div class="pro_price">
						<em class="currency_data PriceColor">'.$_SESSION['Currency']['Symbol'].'</em><span class="price_data PriceColor" data="'.$price_ary[0].'" keyid="'.$v['ProId'].'">'.cart::iconv_price($price_ary[0], 2).'</span>'.($c['config']['products_show']['Config']['price']?'<del><em class="currency_data">'.$_SESSION['Currency']['Symbol'].'</em><span class="price_data" data="'.$price_0.'">'.cart::iconv_price($price_0, 2).'</span></del>':'').'
					</div>
					<div class="free_shipping">'.($v['IsFreeShipping']?$c['lang_pack']['free_shipping']:'').'</div>
					<div class="pro_view">'.
						($c['config']['products_show']['Config']['review']?'<span class="star star_s'.(int)$Rating.'"></span><a class="review_count" href="'.$url.'#review_box">('.$RatingCount.')</a>':'').
						(($c['config']['products_show']['Config']['favorite'] && $c['config']['global']['Effects']!=3)?'<span class="favorite add_favorite" data="'.$v['ProId'].'"><i class="icon_heart"></i>('.$v['FavoriteCount'].')</span>':'').'
					</div>
				';
				echo ly200::product_effects($c['config']['global']['Effects'], $k, $v, $content);
			}
			?>
			<div class="clear"></div>
		</div>
	
		<div class="ad"><?=ly200::ad(5);?></div>
		<div class="prod_list" effects="<?=$c['config']['global']['Effects'];?>">
			<div class="title">
				<h3 class="fl"><?=$c['lang_pack']['hot_sale'];?></h3>
				<a href="/Hot-Sales/" class="fr"><?=$c['lang_pack']['more'];?> >></a>
			</div>
			<div class="blank15"></div>
			<?php
			$products_list_row=str::str_code(db::get_limit('products', '1'.where::equal(array('IsHot'=>'1', 'IsIndex'=>'1')).$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 8));
			foreach((array)$products_list_row as $k=>$v){
				$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
				$url=ly200::get_url($v, 'products');
				$name=$v['Name'.$c['lang']];
				$price_ary=cart::range_price_ext($v);
				$price_0=$v["Price_{$is_promition}"];
				$RatingCount=$v['TotalRating'];
				$Rating=@ceil($v['Rating']);
				if((int)$v['IsDefaultReview']){
					$RatingCount+=$v['DefaultReviewTotalRating'];
					$Rating= $RatingCount ? @ceil(($v['Rating']*$v['TotalRating']+$v['DefaultReviewRating']*$v['DefaultReviewTotalRating'])/$RatingCount) : 5;
				}
				$content='
					<div class="pro_name"><a href="'.$url.'" title="'.$name.'">'.str::str_echo($name, 45, 0, '..').'</a></div>
					<div class="pro_price">
						<em class="currency_data PriceColor">'.$_SESSION['Currency']['Symbol'].'</em><span class="price_data PriceColor" data="'.$price_ary[0].'" keyid="'.$v['ProId'].'">'.cart::iconv_price($price_ary[0], 2).'</span>'.($c['config']['products_show']['Config']['price']?'<del><em class="currency_data">'.$_SESSION['Currency']['Symbol'].'</em><span class="price_data" data="'.$price_0.'">'.cart::iconv_price($price_0, 2).'</span></del>':'').'
					</div>
					<div class="free_shipping">'.($v['IsFreeShipping']?$c['lang_pack']['free_shipping']:'').'</div>
					<div class="pro_view">'.
						($c['config']['products_show']['Config']['review']?'<span class="star star_s'.(int)$Rating.'"></span><a class="review_count" href="'.$url.'#review_box">('.$RatingCount.')</a>':'').
						(($c['config']['products_show']['Config']['favorite'] && $c['config']['global']['Effects']!=3)?'<span class="favorite add_favorite" data="'.$v['ProId'].'"><i class="icon_heart"></i>('.$v['FavoriteCount'].')</span>':'').'
					</div>
				';
				echo ly200::product_effects($c['config']['global']['Effects'], $k, $v, $content);
			}
			?>
			<div class="clear"></div>
		</div>
		<div class="ad"><?=ly200::ad(6);?></div>
		<div class="prod_list" effects="<?=$c['config']['global']['Effects'];?>">
			<div class="title">
				<h3 class="fl"><?=$c['lang_pack']['new_arrival'];?></h3>
				<a href="/New-Arrivals/" class="fr"><?=$c['lang_pack']['more'];?> >></a>
			</div>
			<div class="blank15"></div>
			<?php
			$products_list_row=str::str_code(db::get_limit('products', '1'.where::equal(array('IsNew'=>'1', 'IsIndex'=>'1')).$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 8));
			foreach((array)$products_list_row as $k=>$v){
				$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
				$url=ly200::get_url($v, 'products');
				$name=$v['Name'.$c['lang']];
				$price_ary=cart::range_price_ext($v);
				$price_0=$v["Price_{$is_promition}"];
				$RatingCount=$v['TotalRating'];
				$Rating=@ceil($v['Rating']);
				if((int)$v['IsDefaultReview']){
					$RatingCount+=$v['DefaultReviewTotalRating'];
					$Rating= $RatingCount ? @ceil(($v['Rating']*$v['TotalRating']+$v['DefaultReviewRating']*$v['DefaultReviewTotalRating'])/$RatingCount) : 5;
				}
				$content='
					<div class="pro_name"><a href="'.$url.'" title="'.$name.'">'.str::str_echo($name, 45, 0, '..').'</a></div>
					<div class="pro_price">
						<em class="currency_data PriceColor">'.$_SESSION['Currency']['Symbol'].'</em><span class="price_data PriceColor" data="'.$price_ary[0].'" keyid="'.$v['ProId'].'">'.cart::iconv_price($price_ary[0], 2).'</span>'.($c['config']['products_show']['Config']['price']?'<del><em class="currency_data">'.$_SESSION['Currency']['Symbol'].'</em><span class="price_data" data="'.$price_0.'">'.cart::iconv_price($price_0, 2).'</span></del>':'').'
					</div>
					<div class="free_shipping">'.($v['IsFreeShipping']?$c['lang_pack']['free_shipping']:'').'</div>
					<div class="pro_view">'.
						($c['config']['products_show']['Config']['review']?'<span class="star star_s'.(int)$Rating.'"></span><a class="review_count" href="'.$url.'#review_box">('.$RatingCount.')</a>':'').
						(($c['config']['products_show']['Config']['favorite'] && $c['config']['global']['Effects']!=3)?'<span class="favorite add_favorite" data="'.$v['ProId'].'"><i class="icon_heart"></i>('.$v['FavoriteCount'].')</span>':'').'
					</div>
				';
				echo ly200::product_effects($c['config']['global']['Effects'], $k, $v, $content);
			}
			?>
			<div class="clear"></div>
		</div>
    </div>
	<div class="clear"></div>
</div>
<?php 
	$cache_contents=ob_get_contents();
	ob_end_clean();
	file::write_file(ly200::get_cache_path($c['theme'], 0), 'index.html', $cache_contents);
}
include(ly200::get_cache_path($c['theme']).'index.html');
?>
<?php include("{$c['default_path']}inc/footer.php");?>
<?php
//首页特效跟随主色调
$cfg_module_style=db::get_value('config_module', 'IsDefault=1', 'StyleData');
$cfg_module_style=str::json_data($cfg_module_style, 'decode');
?>
<style>
.prod_box.hover_1 .prod_box_pic{ border:1px solid <?=$cfg_module_style['FontColor']?>;}
.prod_box.hover_1 .prod_box_info{ background:<?=$cfg_module_style['FontColor']?>;}
.prod_box .prod_box_view{ background:<?=$cfg_module_style['FontColor']?>;}
.prod_box.hover_1 .prod_box_view{ border-top:1px solid #fff;}
.prod_box .prod_box_button{ border-top:1px #fff solid;}
.prod_box .prod_box_button>div{ border-right:1px solid #fff;}
</style>
</body>
</html>