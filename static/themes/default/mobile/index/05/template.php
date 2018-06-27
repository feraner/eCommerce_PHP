<?php !isset($c) && exit();?>
<div class="wrapper homebg">
	<div class="banner clean" id="banner_box">
    	<ul>
            <?php
			$ad_ary=ly200::ad_custom(0, 89);
			for($i=$sum=0; $i<$ad_ary['Count']; ++$i){
				if(!is_file($c['root_path'].$ad_ary['PicPath'][$i][$ad_ary['Lang']])) continue;
				$url=$ad_ary['Url'][$i][$ad_ary['Lang']];
				$sum++;
            ?>
            <li><a href="<?=$url?$url:'javascript:;';?>"><img src="<?=$ad_ary['PicPath'][$i][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][$i][$ad_ary['Lang']];?>" /></a></li>
            <?php }?>
        </ul>
        <div class="btn">
        	<?php for($i=0; $i<$sum; ++$i){?>
            <span class="<?=$i==0?'on':'';?>"></span>
            <?php }?>
        </div>
    </div>
    <div class="htitle"><?=$c['lang_pack']['mobile']['best_deals'];?></div>
	<?php
	$ad_ary=ly200::ad_custom(0, 98);
	?>
    <div class="home_box">
    	<div class="big_pro">
        	<div class="img"><a href="<?=$ad_ary['Url'][0][$ad_ary['Lang']];?>"><img src="<?=$ad_ary['PicPath'][0][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][0][$ad_ary['Lang']];?>" /></a></div>
            <div class="proname"><a href="<?=$ad_ary['Url'][0][$ad_ary['Lang']];?>"><?=$ad_ary['Name'][0][$ad_ary['Lang']];?></a></div>
        </div>
    </div>
    <?php
	$products_list_row=str::str_code(db::get_limit('products', 'IsBestDeals=1 and IsIndex=1'.$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 4));
	$len=count($products_list_row);
	foreach($products_list_row as $k=>$v){
		$url=ly200::get_url($v, 'products');
		$img=ly200::get_size_img($v['PicPath_0'], '240x240');
		$name=$v['Name'.$c['lang']];
		$price_ary=cart::range_price_ext($v);
		$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
		$promotion_discount=@round(sprintf('%01.2f', ($v['Price_1']-$price_ary[0])/$v['Price_1']*100));
		if($v['PromotionType']) $promotion_discount=100-$v['PromotionDiscount'];
	?>
        <?php if($k%2==0){?><div class="home_box clean"><?php }?>
		<div class="small_pro item fl">
			<div class="c">
				<div class="img pic_box">
					<a href="<?=$url;?>"><img src="<?=$img;?>" /><span></span></a>
					<?php if($is_promition && $promotion_discount){?><em class="icon_discount DiscountBgColor"><b><?=$promotion_discount;?></b>%<br />OFF</em><em class="icon_discount_foot DiscountBorderColor"></em><?php }?>
					<em class="icon_seckill DiscountBgColor"><?=$c['lang_pack']['products']['sale'];?></em>
				</div>
				<div class="proname"><a href="<?=$url;?>"><?=$name;?></a></div>
				<div class="price">
					<span class="price_data" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0]);?></span> <?=$_SESSION['Currency']['Currency'];?>
				</div>
			</div>
		</div>
        <?php if(($k+1)%2==0 || $k==$len-1){?></div><?php }?>
    <?php }?>
    <div class="htitle"><?=$c['lang_pack']['mobile']['hot_sale'];?></div>
	<?php
	$ad_ary=ly200::ad_custom(0, 99);
	?>
    <div class="home_box">
    	<div class="big_pro">
        	<div class="img"><a href="<?=$ad_ary['Url'][0][$ad_ary['Lang']];?>"><img src="<?=$ad_ary['PicPath'][0][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][0][$ad_ary['Lang']];?>" /></a></div>
            <div class="proname"><a href="<?=$ad_ary['Url'][0][$ad_ary['Lang']];?>"><?=$ad_ary['Name'][0][$ad_ary['Lang']];?></a></div>
        </div>
    </div>
    <?php
	$products_list_row=str::str_code(db::get_limit('products', 'IsHot=1 and IsIndex=1'.$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 4));
    $len=count($products_list_row);
	foreach($products_list_row as $k=>$v){
		$url=ly200::get_url($v, 'products');
		$img=ly200::get_size_img($v['PicPath_0'], '240x240');
		$name=$v['Name'.$c['lang']];
		$price_ary=cart::range_price_ext($v);
		$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
		$promotion_discount=@round(sprintf('%01.2f', ($v['Price_1']-$price_ary[0])/$v['Price_1']*100));
		if($v['PromotionType']) $promotion_discount=100-$v['PromotionDiscount'];
	?>
        <?php if($k%2==0){?><div class="home_box clean"><?php }?>
		<div class="small_pro item fl">
			<div class="c">
				<div class="img pic_box">
					<a href="<?=$url;?>"><img src="<?=$img;?>" /><span></span></a>
					<?php if($is_promition && $promotion_discount){?><em class="icon_discount DiscountBgColor"><b><?=$promotion_discount;?></b>%<br />OFF</em><em class="icon_discount_foot DiscountBorderColor"></em><?php }?>
					<em class="icon_seckill DiscountBgColor"><?=$c['lang_pack']['products']['sale'];?></em>
				</div>
				<div class="proname"><a href="<?=$url;?>"><?=$name;?></a></div>
				<div class="price">
					<span class="price_data" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0]);?></span> <?=$_SESSION['Currency']['Currency'];?>
				</div>
			</div>
		</div>
        <?php if(($k+1)%2==0 || $k==$len-1){?></div><?php }?>
    <?php }?>
</div>