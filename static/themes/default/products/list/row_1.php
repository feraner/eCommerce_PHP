<?php !isset($c) && exit();?>
<div id="prod_list" class="list prod_list clearfix">
	<?php
	foreach((array)$products_list_row[0] as $v){
		$url=ly200::get_url($v, 'products');
		$img=ly200::get_size_img($v['PicPath_0'], '240x240');
		$name=$v['Name'.$c['lang']];
		$price_ary=cart::range_price_ext($v);
		$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
		$promotion_discount=@round(sprintf('%01.2f', ($v['Price_1']-$price_ary[0])/$v['Price_1']*100));
		if($v['PromotionType']) $promotion_discount=100-$v['PromotionDiscount'];
		$rating=($v['IsDefaultReview'] && $v['DefaultReviewRating'])?(int)$v['DefaultReviewRating']:ceil($v['Rating']);
		$total_rating=($v['IsDefaultReview'] && $v['DefaultReviewTotalRating'])?$v['DefaultReviewTotalRating']:$v['TotalRating'];
	?>
	<dl class="flat_pro_item pro_item clearfix">
		<dt>
			<a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a>
			<?php if($is_promition && $promotion_discount){?><em class="icon_discount DiscountBgColor"><b><?=$promotion_discount;?></b>%<br />OFF</em><em class="icon_discount_foot DiscountBorderColor"></em><?php }?>
			<em class="icon_seckill DiscountBgColor"><?=$c['lang_pack']['products']['sale'];?></em>
		</dt>
		<dd class="desc_box">
			<div class="pro_name"><a href="<?=$url;?>" title="<?=$name;?>"><?=$name;?></a></div>
			<div class="pro_price">
				<em class="currency_data PriceColor"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0], 2);?></span>
				<?php if($c['config']['products_show']['Config']['price']){?><del><em class="currency_data"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data" data="<?=($is_promition && $v['PromotionType']==1)?$v['Price_1']:$v['Price_0'];?>"><?=cart::iconv_price((($is_promition && $v['PromotionType']==1)?$v['Price_1']:$v['Price_0']), 2);?></span></del><?php }?>
			</div>
			<div class="free_shipping"><?=$v['IsFreeShipping']?$c['lang_pack']['products']['freeShipping']:'';?></div>
			<div><?=$v['BriefDescription'.$c['lang']];?> <a class="detail" href="<?=$url;?>"><?=$c['lang_pack']['products']['prodDetail'];?>»</a></div>
			<div class="pro_view">
				<?php if($c['config']['products_show']['Config']['review'] && $total_rating){?><span class="star star_s<?=$rating;?>"></span><a class="review_count" href="<?=$url;?>#review_box">(<?=$total_rating;?>)</a><?php }?>
				<?php if($c['config']['products_show']['Config']['favorite']){?><span class="favorite add_favorite" data="<?=$v['ProId'];?>"><i class="icon_heart"></i>(<?=$v['FavoriteCount'];?>)</span><?php }?>
			</div>
		</dd>
	</dl>
	<?php }?>
</div>
<?php if($products_list_row[3]>1){?>
	<div id="turn_page"><?=ly200::turn_page_html($products_list_row[1], $products_list_row[2], $products_list_row[3], $no_page_url, $c['lang_pack']['previous'], $c['lang_pack']['next']);?></div>
<?php }?>