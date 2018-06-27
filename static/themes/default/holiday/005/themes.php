<?php !isset($c) && exit();?>
<?php
ob_start();
?>
<style>
#bodyer{background:url(<?=$theme_ary[0]['PicPath'];?>) no-repeat top center #fae99b;}
.pro_list_0{background-image:url(<?=$theme_ary[2]['PicPath'];?>);}
.pro_list_1{background-image:url(<?=$theme_ary[3]['PicPath'];?>);}
.pro_list_2{background-image:url(<?=$theme_ary[4]['PicPath'];?>);}
</style>
<div id="bodyer" class="full">
	<div class="holiday_header full"<?=$theme_ary[0]['Url']?' onClick="window.open(\''.$theme_ary[0]['Url'].'\');"':'';?>></div>
	<div id="main" class="wide">
		<?php
		$j=1;
		for($i=0; $i<2; ++$i){
			$no=$i+$j;
			$title=$theme_ary[$no]['Title'];
			$img=$theme_ary[$no]['PicPath'];
			$url=$theme_ary[$no]['Url'];
		?>
		<div class="pro_list list_0">
			<div class="list_body">
				<div class="t0 fl"><a href="<?=$url[0];?>" title="<?=$title[0];?>"><img src="<?=$img[0];?>" alt="<?=$title[0];?>" /></a></div>
				<div class="t0 fl"><a href="<?=$url[1];?>" title="<?=$title[1];?>"><img src="<?=$img[1];?>" alt="<?=$title[1];?>" /></a></div>
				<div class="t1 fl mr_0"><a href="<?=$url[2];?>" title="<?=$title[2];?>"><img src="<?=$img[2];?>" alt="<?=$title[2];?>" /></a></div>
				<div class="clear"></div>
				<div class="t0 fl"><a href="<?=$url[3];?>" title="<?=$title[3];?>"><img src="<?=$img[3];?>" alt="<?=$title[3];?>" /></a></div>
				<div class="t1 fl"><a href="<?=$url[4];?>" title="<?=$title[4];?>"><img src="<?=$img[4];?>" alt="<?=$title[4];?>" /></a></div>
				<div class="t0 fl mr_0"><a href="<?=$url[5];?>" title="<?=$title[5];?>"><img src="<?=$img[5];?>" alt="<?=$title[5];?>" /></a></div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
			$no=$i+$j+1;
			$title=$theme_ary[$no]['Title'];
			$img=$theme_ary[$no]['PicPath'];
			$url=$theme_ary[$no]['Url'];
		?>
		<div class="pro_list list_1">
			<div class="list_body">
				<div class="t2 fl"><a href="<?=$url[0];?>" title="<?=$title[0];?>"><img src="<?=$img[0];?>" alt="<?=$title[0];?>" /></a></div>
				<div class="list_box fl">
					<div class="t1 fl"><a href="<?=$url[1];?>" title="<?=$title[1];?>"><img src="<?=$img[1];?>" alt="<?=$title[1];?>" /></a></div>
					<div class="t0 fl mr_0"><a href="<?=$url[2];?>" title="<?=$title[2];?>"><img src="<?=$img[2];?>" alt="<?=$title[2];?>" /></a></div>
					<div class="clear"></div>
					<div class="t0 fl"><a href="<?=$url[3];?>" title="<?=$title[3];?>"><img src="<?=$img[3];?>" alt="<?=$title[3];?>" /></a></div>
					<div class="t1 fl mr_0"><a href="<?=$url[4];?>" title="<?=$title[4];?>"><img src="<?=$img[4];?>" alt="<?=$title[4];?>" /></a></div>
				</div>
			</div>
		</div>
		<?php
			$j=$no;
		}?>
	</div>
</div>
<?php
$theme_content=ob_get_contents();
ob_end_clean();
?>