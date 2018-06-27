<?php !isset($c) && exit();?>
<?php
if(!file::check_cache('special_offer.html')){
	ob_start();
?>
<div id="special_offer" class="sidebar">
    <h2 class="b_title FontColor"><?=$c['lang_pack']['special_offer'];?></h2>
    <div class="b_main">
        <?php
        $row=str::str_code(db::get_limit('products', "IsPromotion=1 and ({$c['time']} between StartTime and EndTime)".$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 5));
		$row_len=count($row);
        foreach((array)$row as $k=>$v){
            $url=ly200::get_url($v, 'products');
            $img=ly200::get_size_img($v['PicPath_0'], '240x240');
            $name=$v['Name'.$c['lang']];
			$price_ary=cart::range_price_ext($v);
			$promotion_discount=@intval(sprintf('%01.2f', ($v['Price_1']-$price_ary[0])/$v['Price_1']*100));
			if($v['PromotionType']) $promotion_discount=100-$v['PromotionDiscount'];
        ?>
        <dl class="pro_item clearfix<?=$k+1==$row_len?' last':'';?>">
			<dt>
				<a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a>
				<em class="icon_discount DiscountBgColor"><b><?=$promotion_discount;?></b>%<br />OFF</em>
			</dt>
			<dd class="pro_name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
			<dd class="pro_price">
				<em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>"></span>
				<?php if($c['config']['products_show']['Config']['price']){?><del><em class="currency_data"></em><span class="price_data" data="<?=$v['Price_1'];?>"></span></del><?php }?>
			</dd>
        </dl>
        <?php }?>
		<a class="b_bottom FontColor" href="/Hot-Sales/"><?=$c['lang_pack']['see_more'];?>»</a>
    </div>
</div>
<?php 
	$cache_contents=ob_get_contents();
	ob_end_clean();
	file::write_file(ly200::get_cache_path($c['theme'], 0), 'special_offer.html', $cache_contents);
}
include(ly200::get_cache_path($c['theme']).'special_offer.html');
?>
