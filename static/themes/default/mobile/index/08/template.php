<?php !isset($c) && exit();?>
<div class="wrapper">
	<div class="banner clean" id="banner_box">
    	<ul>
            <?php
			$ad_ary=ly200::ad_custom(0, 103);
            for($i=$sum=0; $i<$ad_ary['Count']; ++$i){
				if(!is_file($c['root_path'].$ad_ary['PicPath'][$i][$ad_ary['Lang']])) continue;
				$url=$ad_ary['Url'][$i][$ad_ary['Lang']];
				$sum++;
            ?>
            	<li><a href="<?=$url?$url:'javascript:;';?>"><img src="<?=$ad_ary['PicPath'][$i][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][$i][$ad_ary['Lang']];?>" /></a></li>
            <?php }?>
        </ul>
    </div>
    <div class="home_pro_touch" id="touch0">
    	<div class="title"><?=$c['lang_pack']['mobile']['feat_pro'];?></div>
    	<div class="list clean">
        	<?php
			$products_row=str::str_code(db::get_limit('products', 'IsBestDeals=1 and IsIndex=1'.$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 6));
			foreach((array)$products_row as $v){
				$url=$c['mobile_url'].ly200::get_url($v, 'products');
				$img=ly200::get_size_img($v['PicPath_0'], '240x240');
				$name=$v['Name'.$c['lang']];
				$price_ary=cart::range_price_ext($v);
				$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
				$promotion_discount=@intval(sprintf('%01.2f', ($v['Price_1']-$price_ary[0])/$v['Price_1']*100));
				if($v['PromotionType']) $promotion_discount=100-$v['PromotionDiscount'];
			?>
				<div class="item fl">
					<div class="img pic_box">
						<a href="<?=$url;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /></a><span></span>
						<?php if($is_promition){?><em class="icon_discount DiscountBgColor"><b><?=$promotion_discount;?></b>%<br />OFF</em><em class="icon_discount_foot DiscountBorderColor"></em><?php }?>
						<em class="icon_seckill DiscountBgColor"><?=$c['lang_pack']['products']['sale'];?></em>
					</div>
					<div class="name"><a href="<?=$url;?>" title="<?=$name;?>"><?=$name;?></a></div>
					<div class="price">
						<span class="price_data" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0]);?></span> <?=$_SESSION['Currency']['Currency'];?>
					</div>
				</div>
            <?php }?>
        </div>
    </div>
    <div class="home_pro_touch" id="touch1">
    	<div class="title"><?=$c['lang_pack']['mobile']['best_sell'];?></div>
    	<div class="list clean">
        	<?php
			$products_row=str::str_code(db::get_limit('products', 'IsHot=1 and IsIndex=1'.$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 6));
			foreach((array)$products_row as $v){
				$url=$c['mobile_url'].ly200::get_url($v, 'products');
				$img=ly200::get_size_img($v['PicPath_0'], '240x240');
				$name=$v['Name'.$c['lang']];
				$price_ary=cart::range_price_ext($v);
				$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
				$promotion_discount=@round(sprintf('%01.2f', ($v['Price_1']-$price_ary[0])/$v['Price_1']*100));
				if($v['PromotionType']) $promotion_discount=100-$v['PromotionDiscount'];
			?>
				<div class="item fl">
					<div class="img pic_box">
						<a href="<?=$url;?>"><img src="<?=$img;?>" /></a><span></span>
						<?php if($is_promition && $promotion_discount){?><em class="icon_discount DiscountBgColor"><b><?=$promotion_discount;?></b>%<br />OFF</em><em class="icon_discount_foot DiscountBorderColor"></em><?php }?>
						<em class="icon_seckill DiscountBgColor"><?=$c['lang_pack']['products']['sale'];?></em>
					</div>
					<div class="name"><a href="<?=$url;?>" title="<?=$name;?>"><?=$name;?></a></div>
					<div class="price">
						<span class="price_data" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0]);?></span> <?=$_SESSION['Currency']['Currency'];?>
					</div>
				</div>
            <?php }?>
        </div>
    </div>
    <div class="home_pro">
    	<?php
		$products_row=str::str_code(db::get_limit('products', 'IsIndex=1 and IsHot=0 and IsBestDeals=0'.$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 4));
		$len=count($products_row);
		foreach((array)$products_row as $k=>$v){
			$url=$c['mobile_url'].ly200::get_url($v, 'products');
			$img=ly200::get_size_img($v['PicPath_0'], '240x240');
			$name=$v['Name'.$c['lang']];
			$price_ary=cart::range_price_ext($v);
			$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
			$promotion_discount=@round(sprintf('%01.2f', ($v['Price_1']-$price_ary[0])/$v['Price_1']*100));
			if($v['PromotionType']) $promotion_discount=100-$v['PromotionDiscount'];
		?>
			<?php if($k%2==0){?><div class="home_box clean"><?php }?>
			<div class="small_pro fl">
				<div class="c">
					<div class="img pic_box">
						<a href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" /></a><span></span>
						<?php if($is_promition && $promotion_discount){?><em class="icon_discount DiscountBgColor"><b><?=$promotion_discount;?></b>%<br />OFF</em><em class="icon_discount_foot DiscountBorderColor"></em><?php }?>
						<em class="icon_seckill DiscountBgColor"><?=$c['lang_pack']['products']['sale'];?></em>
					</div>
					<div class="proname"><a href="<?=$url;?>" title="<?=$name;?>"><?=$name;?></a></div>
					<div class="price">
						<span class="price_data" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0]);?></span> <?=$_SESSION['Currency']['Currency'];?>
					</div>
				</div>
			</div>
			<?php if(($k+1)%2==0 || $k==$len-1){?></div><?php }?>
		<?php
		}?>
    </div>
</div>