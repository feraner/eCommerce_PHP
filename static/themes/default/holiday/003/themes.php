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
.holiday_header{background:url(<?=$theme_ary[0]['PicPath'];?>) no-repeat top center;}
.pro_list_0 .list_head{background:url(<?=$theme_ary[1]['PicPath'];?>) no-repeat center 47px;}
.pro_list_1 .list_head{background:url(<?=$theme_ary[2]['PicPath'];?>) no-repeat center 47px;}
</style>
<div id="bodyer" class="full">
	<div class="holiday_header full"<?=$theme_ary[0]['Url']?' onClick="window.open(\''.$theme_ary[0]['Url'].'\');"':'';?>></div>
	<div class="holiday_discount full">
		<div class="wide">
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
						<div class="price cur_price"><?=$c['lang_pack']['holiday']['price']; ?>: <b><em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>"></span></b></div>
						<div class="price old_price"><?=$c['lang_pack']['holiday']['lprice']; ?>: <em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$pro_ary[$proid]['Price_0'];?>"></span></div>
						<a class="buy_btn fr" href="<?=$url;?>"><span><?=$c['lang_pack']['holiday']['buy_it']; ?></span></a>
					</dd>
				</dl>
				<?php
					echo (($k+1)%4==0 || ($k+1)==$pro_len)?'<div class="blank25"></div>':'';
				}?>
			</div>
		</div>
	</div>
	<div class="pro_list wide">
		<div class="list_head"><?=$theme_ary[2]['Title'];?></h2></div>
		<div class="list_body">
			<?php
			$pro_len=count($proid_obj[1]);
			foreach((array)$proid_obj[1] as $k=>$v){
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
			<?php }?>
			<div class="blank9"></div>
		</div>
	</div>
	<div id="main" class="full">
		<div class="middle_bg full"></div>
		<div class="wide">
			<div class="holiday_banner banner_0">
				<div class="banner_head">More discount coupon redemption orders</div>
				<div class="banner_body">
					<div class="banner_box fl">
						<div class="t0 fl"><a href="<?=$theme_ary[3]['Url'][0];?>" title="<?=$theme_ary[3]['Title'][0];?>"><img src="<?=$theme_ary[3]['PicPath'][0];?>" alt="<?=$theme_ary[3]['Title'][0];?>" /></a></div>
						<div class="t1 fl"><a href="<?=$theme_ary[3]['Url'][1];?>" title="<?=$theme_ary[3]['Title'][1];?>"><img src="<?=$theme_ary[3]['PicPath'][1];?>" alt="<?=$theme_ary[3]['Title'][1];?>" /></a></div>
						<div class="t2 fl"><a href="<?=$theme_ary[3]['Url'][2];?>" title="<?=$theme_ary[3]['Title'][2];?>"><img src="<?=$theme_ary[3]['PicPath'][2];?>" alt="<?=$theme_ary[3]['Title'][2];?>" /></a></div>
						<div class="t3 fl"><a href="<?=$theme_ary[3]['Url'][3];?>" title="<?=$theme_ary[3]['Title'][3];?>"><img src="<?=$theme_ary[3]['PicPath'][3];?>" alt="<?=$theme_ary[3]['Title'][3];?>" /></a></div>
					</div>
					<div class="t4 fl"><a href="<?=$theme_ary[3]['Url'][4];?>" title="<?=$theme_ary[3]['Title'][4];?>"><img src="<?=$theme_ary[3]['PicPath'][4];?>" alt="<?=$theme_ary[3]['Title'][4];?>" /></a></div>
				</div>
			</div>
			<div class="holiday_banner banner_1">
				<div class="banner_head">More discount coupon redemption orders</div>
				<div class="banner_body">
					<div class="t4 fl"><a href="<?=$theme_ary[4]['Url'][0];?>" title="<?=$theme_ary[4]['Title'][0];?>"><img src="<?=$theme_ary[4]['PicPath'][0];?>" alt="<?=$theme_ary[4]['Title'][0];?>" /></a></div>
					<div class="banner_box fl">
						<div class="t0 fl"><a href="<?=$theme_ary[4]['Url'][1];?>" title="<?=$theme_ary[4]['Title'][1];?>"><img src="<?=$theme_ary[4]['PicPath'][1];?>" alt="<?=$theme_ary[4]['Title'][1];?>" /></a></div>
						<div class="t1 fl"><a href="<?=$theme_ary[4]['Url'][2];?>" title="<?=$theme_ary[4]['Title'][2];?>"><img src="<?=$theme_ary[4]['PicPath'][2];?>" alt="<?=$theme_ary[4]['Title'][2];?>" /></a></div>
						<div class="t2 fl"><a href="<?=$theme_ary[4]['Url'][3];?>" title="<?=$theme_ary[4]['Title'][3];?>"><img src="<?=$theme_ary[4]['PicPath'][3];?>" alt="<?=$theme_ary[4]['Title'][3];?>" /></a></div>
						<div class="t3 fl"><a href="<?=$theme_ary[4]['Url'][4];?>" title="<?=$theme_ary[4]['Title'][4];?>"><img src="<?=$theme_ary[4]['PicPath'][4];?>" alt="<?=$theme_ary[4]['Title'][4];?>" /></a></div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?=ly200::load_static("/static/themes/default/holiday/{$theme}/js/template.js");?>
<?php
$theme_content=ob_get_contents();
ob_end_clean();
?>