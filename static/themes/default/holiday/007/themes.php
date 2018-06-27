<?php !isset($c) && exit();?>
<?php
$where='0';
for($i=0; $i<2; ++$i){
	$proid_obj[$i] && $where.=','.implode(',', $proid_obj[$i]);
}
$pro_ary=array();
$pro_row=str::str_code(db::get_all('products', "ProId in($where)", '*', 'ProId desc'));
foreach((array)$pro_row as $v) $pro_ary[$v['ProId']]=$v;

ob_start();
?>
<style>
#bodyer{background:url(<?=$theme_ary[0]['PicPath'];?>) no-repeat top center #99261c;}
.pro_list_0 .list_head{background:url(<?=$theme_ary[1]['PicPath'];?>) no-repeat center center;}
.pro_list_1 .list_head{background:url(<?=$theme_ary[2]['PicPath'];?>) no-repeat center center;}
</style>
<div id="bodyer" class="full">
	<div class="holiday_header full"<?=$theme_ary[0]['Url']?' onClick="window.open(\''.$theme_ary[0]['Url'].'\');"':'';?>></div>
	<div id="main" class="wide">
		<?php
		for($i=0; $i<2; ++$i){
		?>
			<div class="pro_list pro_list_<?=$i;?>">
				<div class="list_head"<?=$theme_ary[$i+1]['Url']?' onClick="window.open(\''.$theme_ary[$i+1]['Url'].'\');"':'';?>><?=$theme_ary[1]['Title'];?></div>
				<div class="list_body">
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
						<dl class="pro_item fl">
							<dt><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a></dt>
							<dd class="pro_name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
							<dd class="pro_price"><em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>"></span></dd>
							<dd class="pro_view"><?=$discount;?>% off</dd>
						</dl>
					<?php
						echo (($k+1)%5==0 || ($k+1)==$pro_len)?'<div class="blank25"></div>':'';
					}?>
				</div>
			</div>
		<?php }?>
	</div>
</div>
<?=ly200::load_static("/static/themes/default/holiday/{$theme}/js/template.js");?>
<?php
$theme_content=ob_get_contents();
ob_end_clean();
?>