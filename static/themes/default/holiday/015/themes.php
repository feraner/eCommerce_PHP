<?php !isset($c) && exit();?>
<?php
ob_start();
?>
<style>
#main_top{background:url(<?=$theme_ary[0]['PicPath'];?>) no-repeat top center;}
.box_0 .box_head{background:url(<?=$theme_ary[3]['PicPath'];?>) no-repeat center center;}
.box_1 .box_head{background:url(<?=$theme_ary[5]['PicPath'];?>) no-repeat center center;}
.box_2 .box_head{background:url(<?=$theme_ary[7]['PicPath'];?>) no-repeat center center;}
.box_3 .box_head{background:url(<?=$theme_ary[9]['PicPath'];?>) no-repeat center center;}
</style>
<div id="bodyer" class="full">
	<div id="main_top" class="full"<?=$theme_ary[0]['Url']?' onClick="window.open(\''.$theme_ary[0]['Url'].'\');"':'';?>></div>
	<div id="main" class="wide">
		<div class="banner_0"><a href="<?=$theme_ary[1]['Url'];?>" title="<?=$theme_ary[1]['Title'];?>"><img src="<?=$theme_ary[1]['PicPath'];?>" alt="<?=$theme_ary[1]['Title'];?>" /></a></div>
		<div class="banner_1"><a href="<?=$theme_ary[2]['Url'];?>" title="<?=$theme_ary[2]['Title'];?>"><img src="<?=$theme_ary[2]['PicPath'];?>" alt="<?=$theme_ary[2]['Title'];?>" /></a></div>
		<div class="box box_0">
			<div class="box_head"></div>
			<div class="box_body">
				<?php
				$title=$theme_ary[4]['Title'];
				$img=$theme_ary[4]['PicPath'];
				$url=$theme_ary[4]['Url'];
				for($i=0; $i<4; ++$i){
				?>
				<dl class="item fl<?=$i==3?' mr_0':'';?>">
					<dt><a href="<?=$url[$i];?>" title="<?=$title[$i];?>"><img src="<?=$img[$i];?>" alt="<?=$title[$i];?>" /></a></dt>
					<dd><?=$title[$i];?></dd>
				</dl>
				<?php }?>
				<div class="clear"></div>
			</div>
		</div>
		<div class="box box_1">
			<div class="box_head"></div>
			<div class="box_body">
				<?php
				$title=$theme_ary[6]['Title'];
				$img=$theme_ary[6]['PicPath'];
				$url=$theme_ary[6]['Url'];
				for($i=0; $i<7; ++$i){
				?>
				<dl class="item fl<?=$i==1?' item_long':'';?><?=($i==2 || $i==6)?' mr_0':'';?>">
					<dt><a href="<?=$url[$i];?>" title="<?=$title[$i];?>"><img src="<?=$img[$i];?>" alt="<?=$title[$i];?>" /></a></dt>
					<dd><?=$title[$i];?></dd>
				</dl>
				<?php
					echo $i==2?'<div class="blank25"></div>':'';
				}?>
				<div class="clear"></div>
			</div>
		</div>
		<div class="box box_2">
			<div class="box_head"></div>
			<div class="box_body">
				<?php
				$title=$theme_ary[8]['Title'];
				$img=$theme_ary[8]['PicPath'];
				$url=$theme_ary[8]['Url'];
				for($i=0; $i<3; ++$i){
				?>
				<dl class="item item_middle fl<?=$i==2?' mr_0':'';?>">
					<dt><a href="<?=$url[$i];?>" title="<?=$title[$i];?>"><img src="<?=$img[$i];?>" alt="<?=$title[$i];?>" /></a></dt>
					<dd><?=$title[$i];?></dd>
				</dl>
				<?php }?>
				<div class="clear"></div>
			</div>
		</div>
		<div class="box box_3">
			<div class="box_head"></div>
			<div class="box_body">
				<?php
				$title=$theme_ary[10]['Title'];
				$img=$theme_ary[10]['PicPath'];
				$url=$theme_ary[10]['Url'];
				for($i=0; $i<3; ++$i){
				?>
				<dl class="item item_middle fl<?=$i==2?' mr_0':'';?>">
					<dt><a href="<?=$url[$i];?>" title="<?=$title[$i];?>"><img src="<?=$img[$i];?>" alt="<?=$title[$i];?>" /></a></dt>
					<dd><?=$title[$i];?></dd>
				</dl>
				<?php }?>
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