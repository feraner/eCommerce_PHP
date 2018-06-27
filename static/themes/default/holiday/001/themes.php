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
.holiday_header{background:url(<?=$theme_ary[0]['PicPath'];?>) no-repeat top center #b90f34;}
.pro_list_0 .list_head{background:url(<?=$theme_ary[1]['PicPath'];?>) no-repeat center 47px;}
.pro_list_1 .list_head{background:url(<?=$theme_ary[2]['PicPath'];?>) no-repeat center 47px;}
</style>
<div id="bodyer" class="full">
	<div class="holiday_header full"<?=$theme_ary[0]['Url']?' onClick="window.open(\''.$theme_ary[0]['Url'].'\');"':'';?>></div>
	<div id="main" class="wide">
		<?php
		for($i=0; $i<2; ++$i){
		?>
		<div class="pro_list pro_list_<?=$i;?>">
			<div class="list_head"><h2><?=$theme_ary[1]['Title'];?></h2></div>
			<div class="list_body">
				<?php
				$pro_len=count($proid_obj[$i]);
				foreach((array)$proid_obj[$i] as $k=>$v){
					$proid=$v;
					$row=$pro_ary[$proid];
					$url=ly200::get_url($row, 'products');
					$img=ly200::get_size_img($row['PicPath_0'], '240x240');
					$name=$row['Name'.$c['lang']];
					$old_price=(float)$row['Price_0'];
					$price_ary=cart::show_price($row);
					$is_promition=($row['IsPromotion'] && $row['StartTime']<$c['time'] && $c['time']<$row['EndTime'])?1:0;
					$is_promition && $old_price=(float)$row['Price_1'];
					$discount=@intval(sprintf('%01.2f', ($old_price-$price_ary[0])/$old_price*100));
				?>
				<dl class="pro_item fl<?=($k+1)%5==0?' last':'';?>">
					<dt><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a></dt>
					<dd class="pro_name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
					<dd class="pro_price"><em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>"></span></dd>
					<dd class="pro_view"><?=$discount.'% off';?></dd>
				</dl>
				<?php
					echo (($k+1)%5==0 || ($k+1)==$pro_len)?'<div class="blank25"></div>':'';
				}?>
			</div>
		</div>
		<?php }?>
	</div>
	<div class="holiday_discount full">
		<div class="wide">
			<div class="holiday_discount_head"></div>
			<div class="holiday_discount_body">
				<div class="banner"><a href="<?=$theme_ary[3]['Url'][0];?>" title="<?=$theme_ary[3]['Title'][0];?>"><img src="<?=$theme_ary[3]['PicPath'][0];?>" /></a></div>
				<?php
				$j=1;
				for($i=0; $i<10; ++$i){
				?>
				<dl class="item fl<?=$i<5?' top':'';?>"><?php if($i==0 || $i>3){?><a href="<?=$theme_ary[3]['Url'][$j];?>" title="<?=$theme_ary[3]['Title'][$j];?>"><img src="<?=$theme_ary[3]['PicPath'][$j];?>" /></a><?php }?></dl>
				<?php
					echo (($i+1)%5==0 || ($i+1)==10)?'<div class="clear"></div>':'';
					($i==0 || $i>3) && ++$j;
				}?>
			</div>
		</div>
	</div>
</div>
<?php
$theme_content=ob_get_contents();
ob_end_clean();
?>