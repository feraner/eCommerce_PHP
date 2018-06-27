<?php !isset($c) && exit();?>
<?php
if(!file::check_cache('what_hot.html')){
	ob_start();
?>
<div id="what_hot" class="sidebar">
    <h2 class="b_title FontColor"><?=$c['lang_pack']['whats_hot'];?></h2>
    <div class="b_main">
        <?php
        $row=str::str_code(db::get_limit('products', 'IsHot=1'.$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 3));
        foreach((array)$row as $k=>$v){
            $url=ly200::get_url($v, 'products');
            $img=ly200::get_size_img($v['PicPath_0'], '240x240');
            $name=$v['Name'.$c['lang']];
			$price_ary=cart::range_price_ext($v);
        ?>
        <dl class="pro_item clearfix">
            <dt class="fl"><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a></dt>
            <dd class="fl pro_info">
                <div class="pro_name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></div>
                <div class="pro_price"><em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>"></span></div>
            </dd>
        </dl>
        <?php }?>
		<a class="b_bottom FontColor" href="/Hot-Sales/"><?=$c['lang_pack']['see_more'];?>»</a>
    </div>
</div>
<?php 
	$cache_contents=ob_get_contents();
	ob_end_clean();
	file::write_file(ly200::get_cache_path($c['theme'], 0), 'what_hot.html', $cache_contents);
}
include(ly200::get_cache_path($c['theme']).'what_hot.html');
?>
