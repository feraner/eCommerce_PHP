<?php !isset($c) && exit();?>
<div class="pro_list">
	<?php
	$list_ProId_ary=array();
	foreach($products_list_row[0] as $k=>$v){
		$list_ProId_ary[]=$v['ProId'];
		if($v['PromotionType']) $promotion_discount=100-$v['PromotionDiscount'];
		$url=$c['mobile_url'].ly200::get_url($v, 'products');
		$img=ly200::get_size_img($v['PicPath_0'], '240x240');
		$name=$v['Name'.$c['lang']];
		$price_ary=cart::range_price_ext($v);
		$rating=($v['IsDefaultReview'] && $v['DefaultReviewRating'])?(int)$v['DefaultReviewRating']:(int)$v['Rating'];
		$total_rating=($v['IsDefaultReview'] && $v['DefaultReviewTotalRating'])?$v['DefaultReviewTotalRating']:$v['TotalRating'];
		$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
		$promotion_discount=@round(sprintf('%01.2f', ($v['Price_1']-$price_ary[0])/$v['Price_1']*100));
		if($v['PromotionType']) $promotion_discount=100-$v['PromotionDiscount'];
	?>
	<div class="ui_border_b item clean">
		<div class="desc fl">
			<div class="n1"><a href="<?=$url;?>" title="<?=$name;?>"><?=$name;?></a></div>
			<div class="n2"><?=$v['Prefix'].$v['Number'];?></div>
			<div class="price clean">
            	<?=cart::iconv_price(0, 1);?><span class="price_data" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0], 2);?></span>
                <?php if($c['config']['products_show']['Config']['price']){?>
                	<del><?=cart::iconv_price(0, 1);?><span class="price_data" data="<?=($is_promition && $v['PromotionType']==1)?$v['Price_1']:$v['Price_0'];?>"><?=cart::iconv_price((($is_promition && $v['PromotionType']==1)?$v['Price_1']:$v['Price_0']), 2);?></span></del>
				<?php }?>
            </div>
			<?php if($rating){?>
				<div class="star"><?=html::mobile_review_star($rating);?><span>(<?=$total_rating;?>)</span></div>
			<?php }?>
		</div>
		<div class="img fr pic_box">
			<a href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img?>" /><span></span></a>
			<?php if($is_promition && $promotion_discount){?><em class="icon_discount DiscountBgColor"><b><?=$promotion_discount;?></b>%<br />OFF</em><em class="icon_discount_foot DiscountBorderColor"></em><?php }?>
			<em class="icon_seckill DiscountBgColor"><?=$c['lang_pack']['products']['sale'];?></em>
		</div>
	</div>
	<?php }?>
</div>