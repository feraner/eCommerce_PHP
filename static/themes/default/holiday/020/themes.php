<?php !isset($c) && exit();?>
<?php
ob_start();
?>
<style>
#bodyer .main_top{background:url(<?=$theme_ary[0]['PicPath'];?>) no-repeat top center;}
</style>
<div id="bodyer" class="full">
	<div class="main_top full"></div>
	<div class="main wide">
		<div>
			<div class="box_one fl"><a href="<?=$theme_ary[1]['Url'];?>" title="<?=$theme_ary[1]['Title'];?>"><img src="<?=$theme_ary[1]['PicPath'];?>" alt="<?=$theme_ary[1]['Title'];?>" /></a></div>
			<div class="box_one fl mr_0"><a href="<?=$theme_ary[2]['Url'];?>" title="<?=$theme_ary[2]['Title'];?>"><img src="<?=$theme_ary[2]['PicPath'];?>" alt="<?=$theme_ary[2]['Title'];?>" /></a></div>
			<div class="blank25"></div>
		</div>
		<div>
			<div class="box_to fl"><a href="<?=$theme_ary[3]['Url'];?>" title="<?=$theme_ary[3]['Title'];?>"><img src="<?=$theme_ary[3]['PicPath'];?>" alt="<?=$theme_ary[3]['Title'];?>" /></a></div>
			<div class="box_to fl"><a href="<?=$theme_ary[4]['Url'];?>" title="<?=$theme_ary[4]['Title'];?>"><img src="<?=$theme_ary[4]['PicPath'];?>" alt="<?=$theme_ary[4]['Title'];?>" /></a></div>
			<div class="box_to fl mr_0"><a href="<?=$theme_ary[5]['Url'];?>" title="<?=$theme_ary[5]['Title'];?>"><img src="<?=$theme_ary[5]['PicPath'];?>" alt="<?=$theme_ary[5]['Title'];?>" /></a></div>
			<div class="blank25"></div>
		</div>
		<div class="banner"><a href="<?=$theme_ary[6]['Url'];?>" title="<?=$theme_ary[6]['Title'];?>"><img src="<?=$theme_ary[6]['PicPath'];?>" alt="<?=$theme_ary[6]['Title'];?>" /></a></div>
		<div class="blank25"></div>
		<div class="list_box">
			<div class="t1 fl"><a href="<?=$theme_ary[7]['Url'];?>" title="<?=$theme_ary[7]['Title'];?>"><img src="<?=$theme_ary[7]['PicPath'];?>" alt="<?=$theme_ary[7]['Title'];?>" /></a></div>
			<div class="fl">
				<div class="t2 fl"><a href="<?=$theme_ary[8]['Url'];?>" title="<?=$theme_ary[8]['Title'];?>"><img src="<?=$theme_ary[8]['PicPath'];?>" alt="<?=$theme_ary[8]['Title'];?>" /></a></div>
				<div class="t2 fl mr_0"><a href="<?=$theme_ary[9]['Url'];?>" title="<?=$theme_ary[9]['Title'];?>"><img src="<?=$theme_ary[9]['PicPath'];?>" alt="<?=$theme_ary[9]['Title'];?>" /></a></div>
				<div class="clear"></div>
				<div class="t2 fl"><a href="<?=$theme_ary[10]['Url'];?>" title="<?=$theme_ary[10]['Title'];?>"><img src="<?=$theme_ary[10]['PicPath'];?>" alt="<?=$theme_ary[10]['Title'];?>" /></a></div>
				<div class="t2 fl mr_0"><a href="<?=$theme_ary[11]['Url'];?>" title="<?=$theme_ary[11]['Title'];?>"><img src="<?=$theme_ary[11]['PicPath'];?>" alt="<?=$theme_ary[11]['Title'];?>" /></a></div>
				<div class="clear"></div>
			</div>
			<div class="blank25"></div>
		</div>
		<div class="banner"><a href="<?=$theme_ary[12]['Url'];?>" title="<?=$theme_ary[12]['Title'];?>"><img src="<?=$theme_ary[12]['PicPath'];?>" alt="<?=$theme_ary[12]['Title'];?>" /></a></div>
		<div class="blank25"></div>
	</div>
</div>
<?=ly200::load_static("/static/themes/default/holiday/{$theme}/js/template.js");?>
<?php
$theme_content=ob_get_contents();
ob_end_clean();
?>