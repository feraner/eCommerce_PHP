<?php !isset($c) && exit();?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=ly200::seo_meta();?>
<?php include("{$c['static_path']}inc/static.php");?>
<?=ly200::load_static("/static/themes/{$c['theme']}/css/index.css");?>
</head>

<body class="index lang<?=$c['lang'];?>">
<?php include("{$c['theme_path']}inc/header.php");?>
<?php
if(!file::check_cache('index.html')||1){
	ob_start();
?>
<div class="wide main">
	<?php /*
	<div class="lefter fl">
		<?php
		if(!file::check_cache('index_catalog.html')){
			ob_start();
		
			if(!$allcate_ary){
				$allcate_row=str::str_code(db::get_all('products_category', '1', '*',  $c['my_order'].'CateId asc'));
				$allcate_ary=array();
				foreach((array)$allcate_row as $k=>$v){
					$allcate_ary[$v['UId']][]=$v;
				}
			}
			!is_array($UId_ary) && $UId_ary=array();
		?>
			<div class="side_category sidebar">
				<div class="cate_title b_title"><?=$c['lang_pack']['prodCategory'];?></div>
				<div class="cate_menu b_main">
					<?php if(count($allcate_ary["0,"])){?>
					<ul>
						<?php 
						foreach((array)$allcate_ary["0,"] as $k=>$v){
							$data_ary=array();
							if(!$v['IsIndex']) continue;
							if(count($allcate_ary["0,{$v['CateId']},"])){
								foreach((array)$allcate_ary["0,{$v['CateId']},"] as $kk=>$vv){
									if(!$vv['IsIndex']) continue;
									$data_ary[$kk]['text']=htmlspecialchars($vv['Category'.$c['lang']], ENT_QUOTES, 'UTF-8');
									$data_ary[$kk]['url']=ly200::get_url($vv);
									if(count($allcate_ary["{$vv['UId']}{$vv['CateId']},"])){
										$children=array();
										foreach((array)$allcate_ary["{$vv['UId']}{$vv['CateId']},"] as $kkk=>$vvv){
											if(!$vvv['IsIndex']) continue;
											$children[$kkk]['text']=htmlspecialchars($vvv['Category'.$c['lang']], ENT_QUOTES, 'UTF-8');
											$children[$kkk]['url']=ly200::get_url($vvv);
											
										}
										$data_ary[$kk]['children']=$children;
									}
								}
							}
							$data=str::json_data($data_ary);
						?>
							<li data='<?=$data;?>'>
								<h2>
									<a href="<?=ly200::get_url($v);?>" title="<?=$name=$v['Category'.$c['lang']];?>"><?=$name=$v['Category'.$c['lang']];?></a>
									<?php if(count($data_ary)){?><em class="NavArrowColor"></em><?php }?>
								</h2>
							</li>
						<?php }?>
					</ul>
					<?php }?>
				</div>
			</div>
		<?php 
			$cache_contents=ob_get_contents();
			ob_end_clean();
			file::write_file(ly200::get_cache_path($c['theme'], 0), 'index_catalog.html', $cache_contents);
		}
		include(ly200::get_cache_path($c['theme']).'index_catalog.html');
		?>
	</div>*/ ?>
	<div class="righter fr">
		<div class="index_banner fl"><?=ly200::ad(1);?></div>
		<ul class="banner_list fr clean">
			<?php 
			$ad_ary=ly200::ad_custom(2);
			for($i=0; $i<$ad_ary['Count']; ++$i){
				$url=$ad_ary['Url'][$i][$ad_ary['Lang']];
			?>
			<li>
				<?php if($url){?><a href="<?=$url;?>" target="_blank"><?php }?>
				<?php if(is_file($c['root_path'].$ad_ary['PicPath'][$i][$ad_ary['Lang']])){?><img src="<?=$ad_ary['PicPath'][$i][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][$i][$ad_ary['Lang']];?>" /><?php }?>
				<?php if($url){?></a><?php }?>
			</li>
			<?php }?>
		</ul>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
	<div class="index_main">
		<?php
		$cate_row=str::str_code(db::get_limit('products_category', 'Dept=1 and IsIndex=1', "CateId,UId,Category{$c['lang']},BriefDescription{$c['lang']},PicPath",  $c['my_order'].'CateId asc', 0, 4));
		foreach((array)$cate_row as $key=>$value){
			$_w = category::get_search_where_by_CateId($value['CateId'], 'products_category');
			$products_list_row=str::str_code(db::get_limit('products', $_w.' and IsIndex=1'.$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 5));
		?>
		<div class="index_prod index_prod_list clean">
			<div class="index_prod_hd clean"><h2><?=$value['Category'.$c['lang']];?></h2><div class="brief"><?=$value['BriefDescription'.$c['lang']];?></div><div class="img"><?php if(is_file($c['root_path'].$value['PicPath'])){?><img src="<?=$value['PicPath'];?>" /><?php }?></div></div>
			<div class="index_prod_bd">
				<?php
				foreach((array)$products_list_row as $k=>$v){
					$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
					$url=ly200::get_url($v, 'products');
					$img=ly200::get_size_img($v['PicPath_0'], '240x240');
					$name=$v['Name'.$c['lang']];
					$price_ary=cart::range_price_ext($v);
					$price_0=$v["Price_{$is_promition}"];
					$rating=($v['IsDefaultReview'] && $v['DefaultReviewRating'] && (float)$v['Rating']==0)?(int)$v['DefaultReviewRating']:(int)$v['Rating'];
					$total_rating=($v['IsDefaultReview'] && $v['DefaultReviewTotalRating'] && (int)$v['TotalRating']==0)?$v['DefaultReviewTotalRating']:$v['TotalRating'];
				?>
				<dl class="item fl<?=$k?'':' first';?>">
					<dd class="img pic_box">
						<a href="<?=$url;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /></a><span></span>
						<?php if($is_promition){?><em class="icon_discount DiscountBgColor"><b><?=@round(sprintf('%01.2f', ($v['Price_1']-$price_ary[0])/$v['Price_1']*100));?></b>%<br />OFF</em><em class="icon_discount_foot DiscountBorderColor"></em><?php }?>
						<em class="icon_seckill DiscountBgColor"><?=$c['lang_pack']['products']['sale'];?></em>
					</dd>
					<dd class="name"><a href="<?=$url;?>"><?=$name;?></a></dd>
					<dd class="price"><?php if($c['config']['products_show']['Config']['price']){?><del><em class="currency_data"></em><span class="price_data" data="<?=$price_0;?>"></span></del> <?php }?><em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>" keyid="<?=$v['ProId'];?>"></span></dd>
				</dl>
				<?php }?>
			</div>
		</div>
		<?php }?>
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
<?php include("{$c['theme_path']}inc/footer.php");?>
</body>
</html>