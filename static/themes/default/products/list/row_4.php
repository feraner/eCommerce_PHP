<?php !isset($c) && exit();?>
<div id="prod_list" class="list prod_list clearfix" effects="<?=$list_data['Effects'];?>">
	<?php
	foreach((array)$products_list_row[0] as $k=>$v){
		$url=ly200::get_url($v, 'products');
		$img=ly200::get_size_img($v['PicPath_0'], '240x240');
		$imgTo=ly200::get_size_img($v['PicPath_1'], '240x240');
		$name=$v['Name'.$c['lang']];
		$price_ary=cart::range_price_ext($v);
		$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
		$promotion_discount=@round(sprintf('%01.2f', ($v['Price_1']-$price_ary[0])/$v['Price_1']*100));
		if($v['PromotionType']) $promotion_discount=100-$v['PromotionDiscount'];
		$rating=($v['IsDefaultReview'] && $v['DefaultReviewRating'])?(int)$v['DefaultReviewRating']:ceil($v['Rating']);
		$total_rating=($v['IsDefaultReview'] && $v['DefaultReviewTotalRating'])?$v['DefaultReviewTotalRating']:$v['TotalRating'];
		$share_data=htmlspecialchars(str::json_data(array('title'=>$name, 'url'=>ly200::get_domain().$url)));
	?>
	<div class="prod_box prod_box_<?=$list_data['Effects'];?> fl<?=$k%4==0?' first':'';?>">
		<div class="prod_box_pic">
			<a class="pic_box" href="<?=$url;?>" title="<?=$name;?>">
				<img<?=$list_data['Effects']==6?' class="thumb"':''?> src="<?=$img;?>" alt="<?=$name;?>" /><span></span>
				<?php if($list_data['Effects']==4){?><span class="icon_eyes"></span><?php }?>
				<?php if($list_data['Effects']==6 && $imgTo){?><em class="thumb_hover"><img src="<?=$imgTo;?>" alt="<?=$name;?>" /><span></span></em><?php }?>
			</a>
			<?php if($is_promition && $promotion_discount){?><em class="icon_discount DiscountBgColor"><b><?=$promotion_discount;?></b>%<br />OFF</em><em class="icon_discount_foot DiscountBorderColor"></em><?php }?>
			<em class="icon_seckill DiscountBgColor"><?=$c['lang_pack']['products']['sale'];?></em>
		</div>
		<div class="prod_box_info">
			<div class="prod_box_inner">
				<h3 class="prod_name"><a href="<?=$url;?>" title="<?=$name;?>"><?=$name;?></a></h3>
				<div class="prod_price">
					<em class="currency_data PriceColor"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0], 2);?></span>
					<?php if($c['config']['products_show']['Config']['price']){?><del><em class="currency_data"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data" data="<?=($is_promition && $v['PromotionType']==1)?$v['Price_1']:$v['Price_0'];?>"><?=cart::iconv_price((($is_promition && $v['PromotionType']==1)?$v['Price_1']:$v['Price_0']), 2);?></span></del><?php }?>
				</div>
				<div class="free_shipping"><?=$v['IsFreeShipping']?$c['lang_pack']['products']['freeShipping']:'';?></div>
				<div class="prod_view">
					<?php if($c['config']['products_show']['Config']['review'] && $total_rating){?><span class="star star_s<?=$rating;?>"></span><a class="review_count" href="<?=$url;?>#review_box">(<?=$total_rating;?>)</a><?php }?>
					<?php if($c['config']['products_show']['Config']['favorite'] && $list_data['Effects']!=3){?><span class="favorite add_favorite" data="<?=$v['ProId'];?>"><i class="icon_heart"></i>(<?=$v['FavoriteCount'];?>)</span><?php }?>
				</div>
			</div>
			<?php if($list_data['Effects']==1){?>
			<div class="prod_box_view">
				<div class="prod_box_button">
					<div class="addtocart fr"><a href="javascript:;" rel="nofollow" class="add_cart" data="<?=$v['ProId'];?>"><?=$c['lang_pack']['products']['addToCart'];?></a></div>
					<?php if($c['config']['products_show']['Config']['favorite']){?><div class="wishlist fl"><a href="javascript:;" rel="nofollow" class="add_favorite" data="<?=$v['ProId'];?>"></a></div><?php }?>
					<div class="compare fl"><a href="javascript:;" rel="nofollow" class="share_this" data="<?=$share_data;?>"></a></div>
				</div>
			</div>
			<?php }elseif($list_data['Effects']==2){?>
			<div class="add_cart_box"><div class="add_cart_bg ProListBgColor"></div><a href="javascript:;" rel="nofollow" class="add_cart" data="<?=$v['ProId'];?>"><?=$c['lang_pack']['products']['addToCart'];?></a></div>
			<?php }elseif($list_data['Effects']==3){?>
			<div class="button_group">
				<div class="addtocart fl"><a href="javascript:;" rel="nofollow" class="add_cart ProListBgColor" data="<?=$v['ProId'];?>"><?=$c['lang_pack']['products']['addToCart'];?></a></div>
				<?php if($c['config']['products_show']['Config']['favorite']){?><div class="wishlist fr"><a href="javascript:;" rel="nofollow" class="add_favorite" data="<?=$v['ProId'];?>"></a></div><?php }?>
			</div>
			<?php }elseif($list_data['Effects']==4){?>
			<div class="prod_action">
				<?php if($c['config']['products_show']['Config']['favorite']){?><div class="wishlist fl"><a href="javascript:;" rel="nofollow" class="add_favorite" data="<?=$v['ProId'];?>"></a></div><?php }?>
				<div class="addtocart fl"><a href="javascript:;" rel="nofollow" class="add_cart" data="<?=$v['ProId'];?>"><?=$c['lang_pack']['products']['addToCart'];?></a></div>
				<div class="compare fr"><a href="javascript:;" rel="nofollow" class="share_this" data="<?=$share_data;?>"></a></div>
			</div>
			<?php }?>
		</div>
	</div>
	<?=($k+1)%4==0?'<div class="clear"></div>':'';?>
	<?php }?>
</div>
<?php if($products_list_row[3]>1){?>
	<?=($list_data['Effects']==3)?'<div class="blank25"></div>':'';?>
    <div class="blank20"></div>
	<div id="turn_page"><?=ly200::turn_page_html($products_list_row[1], $products_list_row[2], $products_list_row[3], $no_page_url, $c['lang_pack']['previous'], $c['lang_pack']['next']);?></div>
<?php }?>
