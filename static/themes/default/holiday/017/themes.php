<?php !isset($c) && exit();?>
<?php
$where='0';
for($i=0; $i<4; $i++){
	$proid_obj[$i] && $where.=','.implode(',', $proid_obj[$i]);
}
$pro_ary=array();
$pro_row=str::str_code(db::get_all('products', "ProId in($where)", '*', 'ProId desc'));
foreach((array)$pro_row as $v) $pro_ary[$v['ProId']]=$v;

ob_start();
?>
<style>
#bodyer{background:url(<?=$theme_ary[0]['PicPath'];?>) no-repeat top center;}
#bodyer .banner_1{background:url(<?=$theme_ary[5]['PicPath'];?>) no-repeat top center;}
#bodyer .banner_2{background:url(<?=$theme_ary[6]['PicPath'];?>) no-repeat center 29px #fff;}
</style>
<div id="bodyer" class="full">
	<div class="main_top wide" data-url="<?=$theme_ary[0]['Url'];?>"></div>
	<div class="main wide">
		<div class="pro_list pro_list_0">
			<div class="list_menu">
				<?php for($i=0; $i<3; ++$i){?>
				<a href="javascript:void(0);"<?=$i==0?' class="current"':'';?>><?=$theme_ary[$i+1]['Title'];?></a>
				<?php }?>
			</div>
			<div class="list_body">
				<?php
				for($i=0; $i<3; ++$i){
				?>
				<div style="display:<?=$i==0?'block':'none';?>">
					<?php
					$pro_len=count($proid_obj[$i]);
					foreach((array)$proid_obj[$i] as $k=>$v){
						$proid=$v;
						$url=ly200::get_url($pro_ary[$proid], 'products');
						$img=ly200::get_size_img($pro_ary[$proid]['PicPath_0'], '240x240');
						$name=$pro_ary[$proid]['Name'.$c['lang']];
						$price_0=(float)$pro_ary[$proid]['Price_0'];
						$price_ary=cart::range_price_ext($pro_ary[$proid]);
						$discount=($price_0?($price_0-$price_ary[0]):0)/((float)$price_0?$price_0:1)*100;
						$discount=sprintf('%01.1f', $discount);
					?>
					<dl class="pro_item fl<?=$k==3?' bd_none':'';?>">
						<dt><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a></dt>
						<dd class="pro_discount"><?=$discount;?>% Sale</dd>
						<dd class="pro_name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
						<dd class="pro_price">
							<div class="pro_price_0"><em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_0;?>"></span></div>
							<div class="pro_price_1"><em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>"></span></div>
						</dd>
					</dl>
					<?php }?>
					<div class="clear"></div>
				</div>
				<?php }?>
			</div>
		</div>
	</div>
	<div class="main_middle full">
		<div class="main wide">
			<div class="pro_list pro_list_oth">
				<div class="list_menu"><a href="javascript:void(0);" class="current"><?=$theme_ary[4]['Title'];?></a></div>
				<div class="list_body">
					<div class="list_box fl">
						<?php
						$pro_len=count($proid_obj[3]);
						foreach((array)$proid_obj[3] as $k=>$v){
							$proid=$v;
							$url=ly200::get_url($pro_ary[$proid], 'products');
							$img=ly200::get_size_img($pro_ary[$proid]['PicPath_0'], '240x240');
							$name=$pro_ary[$proid]['Name'.$c['lang']];
							$price_0=$pro_ary[$proid]['Price_0'];
							$price_ary=cart::range_price_ext($pro_ary[$proid]);
						?>
						<dl class="pro_item fl">
							<dt><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a></dt>
							<dd class="pro_name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
							<dd class="pro_price">
								<div class="pro_price_0"><em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_0;?>"></span></div>
								<div class="pro_price_1"><em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>"></span></div>
							</dd>
						</dl>
						<?php }?>
						<div class="clear"></div>
					</div>
					<div class="fl"><a href="<?=$theme_ary[4]['Url'];?>" title="<?=$theme_ary[4]['Title'];?>"><img src="<?=$theme_ary[4]['PicPath'];?>" alt="<?=$theme_ary[4]['Title'];?>" /></a></div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
	<div class="banner_1 full" data-url="<?=$theme_ary[5]['Url'];?>"></div>
	<div class="banner_2 full" data-url="<?=$theme_ary[6]['Url'];?>"></div>
</div>
<?=ly200::load_static("/static/themes/default/holiday/{$theme}/js/template.js");?>
<?php
$theme_content=ob_get_contents();
ob_end_clean();
?>