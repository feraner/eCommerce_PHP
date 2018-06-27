<?php
include('../../../../../inc/global.php');

$ProId=(int)$_GET['ProId'];
$ColorId=(int)$_GET['ColorId'];
$row=str::str_code(db::get_one('products_color', "ProId='$ProId' and VId='$ColorId' and VId>0 and PicPath_0!=''"));
$pro_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
if(!$row || !is_file($c['root_path'].$row['PicPath_0'])) $row=$pro_row;
?>
<div class="goods_pic">
	<ul class="clean">
		<?php
		for($i=0; $i<10; ++$i){
			$pic=$row['PicPath_'.$i];
			if(!is_file($c['root_path'].$pic)) continue;
		?>
		<li class="fl"><img src="<?=ly200::get_size_img($pic, '500x500');?>"></li>
		<?php }?>
	</ul>
	<div class="trigger clean">
		<?php
		for($i=0; $i<10; $i++){
			$pic=$row['PicPath_'.$i];
			if(!is_file($c['root_path'].$pic)) continue;
		?>
			<div class="item<?=$i==0?' FontBgColor':' off';?>"><?=$i;?></div>
		<?php }?>
	</div>
</div>
<div class="big_pic" style="display:none;"><img src="<?=ly200::get_size_img($row['PicPath_0'], '240x240');?>" class="normal" alt="<?=$pro_row['Name'.$c['Lang']];?>"></div><!-- 抛物线的div -->