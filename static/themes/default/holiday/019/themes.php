<?php !isset($c) && exit();?>
<?php
ob_start();
?>
<style>
#bodyer .main_top{background:url(<?=$theme_ary[0]['PicPath'];?>) no-repeat top center;}
</style>
<div id="bodyer" class="full">
	<div class="main_top full"<?=$theme_ary[0]['Url']?' onClick="window.open(\''.$theme_ary[0]['Url'].'\');"':'';?>></div>
	<div class="main wide">
		<div class="banner_0"><a href="<?=$theme_ary[1]['Url'];?>" title="<?=$theme_ary[1]['Title'];?>"><img src="<?=$theme_ary[1]['PicPath'];?>" alt="<?=$theme_ary[1]['Title'];?>" /></a></div>
		<div class="banner_1"><a href="<?=$theme_ary[2]['Url'];?>" title="<?=$theme_ary[2]['Title'];?>"><img src="<?=$theme_ary[2]['PicPath'];?>" alt="<?=$theme_ary[2]['Title'];?>" /></a></div>
	
		<div class="pro_list pro_list_0">
			<div class="list_head"><?=$theme_ary[3]['Title'];?></div>
			<div class="list_body">
				<?php
				$title=$theme_ary[4]['Title'];
				$img=$theme_ary[4]['PicPath'];
				$url=$theme_ary[4]['Url'];
				for($i=0; $i<9; ++$i){
				?>
				<div class="item fl<?=($i+1)%3==0?' mr_0':'';?>"><a href="<?=$url[$i];?>" title="<?=$title[$i];?>"><img src="<?=$img[$i];?>" alt="<?=$title[$i];?>" /></a></div>
				<?php
					echo (($i+1)%3==0 || ($i+1)==9)?'<div class="blank25"></div>':'';
				}?>
			</div>
		</div>
		<div class="pro_list pro_list_1">
			<div class="list_head"><?=$theme_ary[5]['Title'];?></div>
			<div class="list_body">
				<?php
				$title=$theme_ary[6]['Title'];
				$img=$theme_ary[6]['PicPath'];
				$url=$theme_ary[6]['Url'];
				for($i=0; $i<8; ++$i){
				?>
				<div class="item fl"><a class="pic_box" href="<?=$url[$i];?>" title="<?=$title[$i];?>"><img src="<?=$img[$i];?>" alt="<?=$title[$i];?>" /><span></span></a></div>
				<?php
					echo (($i+1)%4==0 || ($i+1)==9)?'<div class="blank25"></div>':'';
				}?>
			</div>
		</div>
	</div>
</div>
<?=ly200::load_static("/static/themes/default/holiday/{$theme}/js/template.js");?>
<?php
$theme_content=ob_get_contents();
ob_end_clean();
?>