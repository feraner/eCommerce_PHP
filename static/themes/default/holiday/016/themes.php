<?php !isset($c) && exit();?>
<?php
$where='0';
for($i=0; $i<4; ++$i){
	$proid_obj[$i] && $where.=','.implode(',', $proid_obj[$i]);
}
$pro_ary=array();
$pro_row=str::str_code(db::get_all('products', "ProId in($where)", '*', 'ProId desc'));
foreach((array)$pro_row as $v) $pro_ary[$v['ProId']]=$v;

ob_start();
?>
<style>
#bodyer{background:url(<?=$theme_ary[0]['PicPath'];?>) no-repeat top center;}
.pro_list_0{background-image:url(<?=$theme_ary[2]['PicPath'];?>);}
.pro_list_1{background-image:url(<?=$theme_ary[3]['PicPath'];?>);}
.pro_list_2{background-image:url(<?=$theme_ary[4]['PicPath'];?>);}
</style>
<div id="bodyer" class="full">
	<div id="main_top" class="full"<?=$theme_ary[0]['Url']?' onClick="window.open(\''.$theme_ary[0]['Url'].'\');"':'';?>></div>
	<div id="main_bg" class="full">
		<div id="main" class="wide">
			<div class="holiday_discount">
				<div class="holiday_discount_head"><?=$theme_ary[1]['Title'];?></div>
				<div class="holiday_discount_body">
					<?php
					$pro_len=count($proid_obj[0]);
					foreach((array)$proid_obj[0] as $k=>$v){
						$proid=$v;
						$url=ly200::get_url($pro_ary[$proid], 'products');
						$img=ly200::get_size_img($pro_ary[$proid]['PicPath_0'], '240x240');
						$name=$pro_ary[$proid]['Name'.$c['lang']];
						$price_ary=cart::range_price_ext($pro_ary[$proid]);
					?>
					<dl class="pro_box fl">
						<dd class="time" endtime="<?=date('Y/m/d H:i:s', $pro_ary[$proid]['EndTime']);?>"></dd>
						<dd class="img"><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a></dd>
						<dd class="name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
						<dd class="view">
							<div class="price cur_price"><?=$c['lang_pack']['holiday']['price']; ?>: <b><em class="currency_data"></em><span class="price_data" data="<?=$price_ary[0];?>"></span></b></div>
							<div class="price old_price"><?=$c['lang_pack']['holiday']['lprice']; ?>: <em class="currency_data"></em><span class="price_data" data="<?=$pro_ary[$proid]['Price_0'];?>"></span></div>
							<a class="buy_btn fr" href="<?=$url;?>"><span><?=$c['lang_pack']['holiday']['buy_it']; ?></span></a>
						</dd>
					</dl>
					<?php
						echo (($k+1)%4==0 || ($k+1)==$pro_len)?'<div class="blank25"></div>':'';
					}?>
				</div>
			</div>
			<?php
			for($i=0; $i<3; ++$i){
				$num=$i+1;
			?>
			<div class="pro_list pro_list_<?=$i;?>">
				<div class="click_box"<?=$theme_ary[$i+2]['Url']?' onClick="window.open(\''.$theme_ary[$i+2]['Url'].'\');"':'';?>></div>
				<?php
				$pro_len=count($proid_obj[$num]);
				foreach((array)$proid_obj[$num] as $k=>$v){
					$proid=$v;
					$url=ly200::get_url($pro_ary[$proid], 'products');
					$img=ly200::get_size_img($pro_ary[$proid]['PicPath_0'], '240x240');
					$name=$pro_ary[$proid]['Name'.$c['lang']];
					$price_0=(float)$pro_ary[$proid]['Price_0'];
					$price_ary=cart::range_price_ext($pro_ary[$proid]);
					$discount=($price_0?($price_0-$price_ary[0]):0)/((float)$price_0?$price_0:1)*100;
					$discount=sprintf('%01.1f', $discount);
				?>
				<dl class="pro_item fl<?=$k==0?' first':'';?>">
					<dt><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a></dt>
					<dd class="pro_name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
					<dd class="pro_price"><em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>"></span></dd>
					<dd class="pro_view"><?=$discount;?>% off</dd>
				</dl>
				<?php
					echo ($k==2 || ($k+1)==$pro_len)?'<div class="blank25"></div>':''; 
				}?>
			</div>
			<?php }?>
		</div>
	</div>
</div>
<?=ly200::load_static("/static/themes/default/holiday/{$theme}/js/template.js");?>
<?php
$theme_content=ob_get_contents();
ob_end_clean();
?>