<?php !isset($c) && exit();?>
<div class="pro_list">
	<?php
	$list_ProId_ary=array();
	foreach($products_list_row[0] as $k=>$v){
		$list_ProId_ary[]=$v['ProId'];
		$url=$c['mobile_url'].ly200::get_url($v, 'products');
		$img=ly200::get_size_img($v['PicPath_0'], '240x240');
		$name=$v['Name'.$c['lang']];
		$price_ary=cart::range_price_ext($v);
		$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
		$promotion_discount=@round(sprintf('%01.2f', ($v['Price_1']-$price_ary[0])/$v['Price_1']*100));
		if($v['PromotionType']) $promotion_discount=100-$v['PromotionDiscount'];
	?>
	<div class="item clean ui_border_radius">
		<div class="img fl pic_box">
			<a href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>"><span></span></a>
			<?php if($is_promition && $promotion_discount){?><em class="icon_discount DiscountBgColor"><b><?=$promotion_discount;?></b>%<br />OFF</em><em class="icon_discount_foot DiscountBorderColor"></em><?php }?>
		</div>
		<div class="desc">
			<div class="name ui_border_b"><a href="<?=$url;?>" title="<?=$name;?>"><?=$name;?></a></div>
			<div class="clean">
				<div class="price">
					<?=cart::iconv_price(0, 1);?><span class="price_data" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0], 2);?></span>
                    <?php if($c['config']['products_show']['Config']['price']){?>
                	<del><?=cart::iconv_price(0, 1);?><span class="price_data" data="<?=($is_promition && $v['PromotionType']==1)?$v['Price_1']:$v['Price_0'];?>"><?=cart::iconv_price((($is_promition && $v['PromotionType']==1)?$v['Price_1']:$v['Price_0']), 2);?></span></del>
					<?php }?>
                </div>
			</div>
		</div>
	</div>
	<?php }?>
</div>