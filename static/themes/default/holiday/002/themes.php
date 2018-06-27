<?php !isset($c) && exit();?>
<?php
$where='0';
for($i=0; $i<3; ++$i){
	$proid_obj[$i] && $where.=','.implode(',', $proid_obj[$i]);
}
$pro_ary=array();
$pro_row=str::str_code(db::get_all('products', "ProId in($where)", '*', 'ProId desc'));
foreach((array)$pro_row as $v) $pro_ary[$v['ProId']]=$v;

ob_start();
?>
<style>
#bodyer{background:url(<?=$theme_ary[0]['PicPath'];?>) no-repeat top center #722388;}
.holiday_discount{background:url(<?=$theme_ary[1]['PicPath'];?>) no-repeat bottom center;}
.pro_list_0 .pro_list_head{background:url(<?=$theme_ary[7]['PicPath'];?>) no-repeat center center;}
.pro_list_1 .pro_list_head{background:url(<?=$theme_ary[8]['PicPath'];?>) no-repeat center center;}
.pro_list_2 .pro_list_head{background:url(<?=$theme_ary[9]['PicPath'];?>) no-repeat center center;}
</style>
<div id="bodyer" class="full">
	<div class="holiday_first"<?=$theme_ary[0]['Url']?' onClick="window.open(\''.$theme_ary[0]['Url'].'\');"':'';?>></div>
	<div class="holiday_discount"<?=$theme_ary[1]['Url']?' onClick="window.open(\''.$theme_ary[1]['Url'].'\');"':'';?>></div>
	<div class="holiday_theme wide">
		<div class="item_odd fl"><a href="<?=$theme_ary[2]['Url'];?>"><img src="<?=$theme_ary[2]['PicPath'];?>" /></a></div>
		<div class="box fl">
			<div class="item_even"><a href="<?=$theme_ary[3]['Url'];?>"><img src="<?=$theme_ary[3]['PicPath'];?>" /></a></div>
			<div class="item_even"><a href="<?=$theme_ary[4]['Url'];?>"><img src="<?=$theme_ary[4]['PicPath'];?>" /></a></div>
		</div>
		<div class="item_odd fl"><a href="<?=$theme_ary[5]['Url'];?>"><img src="<?=$theme_ary[5]['PicPath'];?>" /></a></div>
		<div class="item_odd fl mr_0"><a href="<?=$theme_ary[6]['Url'];?>"><img src="<?=$theme_ary[6]['PicPath'];?>" /></a></div>
		<div class="clear"></div>
	</div>
	<div class="even_bg full">
		<div class="pro_list pro_list_0 wide">
			<div class="pro_list_head"></div>
			<div class="pro_list_body">
				<?php
				$no_where='0';
				foreach((array)$proid_obj[0] as $k=>$v){
					$row=$pro_ary[$v];
					if($row['EndTime']<$c['time']){
						$row=str::str_code(db::get_one('products', "IsPromotion=1 and EndTime>'{$c['time']}' and ProId not in($no_where)", '*', 'ProId desc'));
						$row['ProId'] && $no_where.=", {$row['ProId']}";
					}
					$url=ly200::get_url($row, 'products');
					$img=ly200::get_size_img($row['PicPath_0'], '240x240');
					$name=$row['Name'.$c['lang']];
					$price_ary=cart::range_price_ext($row);
				?>
				<dl class="pro_box fl<?=($k+1)%4==0?' last':'';?>">
					<dd class="time" endtime="<?=date('Y/m/d H:i:s', $row['EndTime']);?>"></dd>
					<dd class="img"><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a></dd>
					<dd class="name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
					<dd class="view">
						<div class="price cur_price"><?=$c['lang_pack']['holiday']['price']; ?>: <b><em class="currency_data"></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>"></span></b></div>
						<div class="price old_price"><?=$c['lang_pack']['holiday']['lprice']; ?>: <em class="currency_data"></em><span class="price_data PriceColor" data="<?=$row['Price_0'];?>"></span></div>
						<a class="buy_btn fr" href="<?=$url;?>"><span><?=$c['lang_pack']['holiday']['buy_it']; ?></span></a>
					</dd>
				</dl>
				<?php }?>
				<div class="blank25"></div>
			</div>
		</div>
	</div>
	<div class="pro_list pro_list_1 wide">
		<div class="pro_list_head"></div>
		<div class="pro_list_body">
			<?php
			foreach((array)$proid_obj[1] as $k=>$v){
				$row=$pro_ary[$v];
				$url=ly200::get_url($row, 'products');
				$img=ly200::get_size_img($row['PicPath_0'], '240x240');
				$name=$row['Name'.$c['lang']];
				$price_0=(float)$row['Price_0'];
				$price_ary=cart::range_price_ext($row);
				$discount=($price_0?($price_0-$price_ary[0]):0)/((float)$price_0?$price_0:1)*100;
				$discount=sprintf('%01.1f', $discount);
			?>
			<dl class="pro_item fl<?=(($k+1)%4==0 || ($k+1)==8)?' mr_0':'';?>">
				<dt><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a></dt>
				<dd class="pro_name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
				<dd class="pro_price"><em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>"></span></dd>
				<dd class="pro_view"><?=$discount;?>% off</dd>
			</dl>
			<?php
				echo (($k+1)%4==0 || ($k+1)==8)?'<div class="blank25"></div>':'';
			}?>
			<div class="clear"></div>
		</div>
	</div>
	<div class="even_bg full">
		<div class="pro_list pro_list_1 wide">
			<div class="pro_list_head"></div>
			<div class="pro_list_body">
				<?php
				foreach((array)$proid_obj[2] as $k=>$v){
					$row=$pro_ary[$v];
					$url=ly200::get_url($row, 'products');
					$img=ly200::get_size_img($row['PicPath_0'], '240x240');
					$name=$row['Name'.$c['lang']];
					$price_0=(float)$row['Price_0'];
					$price_ary=cart::range_price_ext($row);
					$discount=($price_0?($price_0-$price_ary[0]):0)/((float)$price_0?$price_0:1)*100;
					$discount=sprintf('%01.1f', $discount);
				?>
				<dl class="pro_item fl<?=(($k+1)%4==0 || ($k+1)==8)?' mr_0':'';?>">
					<dt><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a></dt>
					<dd class="pro_name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
					<dd class="pro_price"><em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>"></span></dd>
					<dd class="pro_view"><?=$discount;?>% off</dd>
				</dl>
				<?php
					echo (($k+1)%4==0 || ($k+1)==8)?'<div class="blank25"></div>':'';
				}?>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
<?=ly200::load_static("/static/themes/default/holiday/{$theme}/js/template.js");?>
<?php
$theme_content=ob_get_contents();
ob_end_clean();
?>