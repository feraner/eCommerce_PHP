<?php !isset($c) && exit();?>
<?php
$ad_ary=ly200::ad_custom(0, 88);
?>
<div class="wrapper">
	<div class="banner clean">
		<?php if(is_file($c['root_path'].$ad_ary['PicPath'][0][$ad_ary['Lang']])){?>
			<a href="<?=$ad_ary['Url'][0][$ad_ary['Lang']]?$ad_ary['Url'][0][$ad_ary['Lang']]:'javascript:;';?>"><img src="<?=$ad_ary['PicPath'][0][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][0][$ad_ary['Lang']];?>" /></a>
		<?php }?>
	</div>
</div>
