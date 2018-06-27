<?php !isset($c) && exit();?>
<?php
ob_start();
?>
<style>
#main_top{background:url(<?=$theme_ary[0]['PicPath'];?>) no-repeat top center;}
.box_list_0 .box_head{background:url(<?=$theme_ary[3]['PicPath'];?>) no-repeat center center;}
.box_list_1 .box_head{background:url(<?=$theme_ary[5]['PicPath'];?>) no-repeat center center;}
</style>
<div id="bodyer" class="full">
	<div id="main_top" class="full">
		<div class="wide"></div>
		<a href="<?=$theme_ary[0]['Url'];?>" title="<?=$theme_ary[0]['Title'];?>" class="click_url_0">&nbsp;</a>
		<div class="top_banner fl"><a href="<?=$theme_ary[1]['Url'];?>" title="<?=$theme_ary[1]['Title'];?>"><img src="<?=$theme_ary[1]['PicPath'];?>" alt="<?=$theme_ary[1]['Title'];?>" style="float:right;" /></a></div>
		<div class="top_banner fl"><a href="<?=$theme_ary[2]['Url'];?>" title="<?=$theme_ary[2]['Title'];?>"><img src="<?=$theme_ary[2]['PicPath'];?>" alt="<?=$theme_ary[2]['Title'];?>" /></a></div>
		<div class="clear"></div>
	</div>
	<div id="main_bg" class="f">
		<div id="main">
			<div class="box_list box_list_0 fl">
				<div class="box_head"></div>
				<div class="box_body">
					<?php
					$title=$theme_ary[4]['Title'];
					$img=$theme_ary[4]['PicPath'];
					$url=$theme_ary[4]['Url'];
					?>
					<div class="item t1 fl"><a href="<?=$url[0];?>" title="<?=$title[0];?>"><img src="<?=$img[0];?>" alt="<?=$title[0];?>" /></a></div>
					<div class="item t2 fl"><a href="<?=$url[1];?>" title="<?=$title[1];?>"><img src="<?=$img[1];?>" alt="<?=$title[1];?>" /></a></div>
					<div class="item t3 fl"><a href="<?=$url[2];?>" title="<?=$title[2];?>"><img src="<?=$img[2];?>" alt="<?=$title[2];?>" /></a></div>
					<div class="clear"></div>
					<div class="item t4 fl"><a href="<?=$url[3];?>" title="<?=$title[3];?>"><img src="<?=$img[3];?>" alt="<?=$title[3];?>" /></a></div>
				</div>
			</div>
			<div class="box_list box_list_1 fl">
				<div class="box_head"></div>
				<div class="box_body">
					<?php
					$title=$theme_ary[6]['Title'];
					$img=$theme_ary[6]['PicPath'];
					$url=$theme_ary[6]['Url'];
					?>
					<div class="item t1 fl"><a href="<?=$url[0];?>" title="<?=$title[0];?>"><img src="<?=$img[0];?>" alt="<?=$title[0];?>" /></a></div>
					<div class="item t2 fl"><a href="<?=$url[1];?>" title="<?=$title[1];?>"><img src="<?=$img[1];?>" alt="<?=$title[1];?>" /></a></div>
					<div class="item t3 fr"><a href="<?=$url[2];?>" title="<?=$title[2];?>"><img src="<?=$img[2];?>" alt="<?=$title[2];?>" /></a></div>
					<div class="clear"></div>
					<div class="item t4 fl"><a href="<?=$url[3];?>" title="<?=$title[3];?>"><img src="<?=$img[3];?>" alt="<?=$title[3];?>" /></a></div>
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