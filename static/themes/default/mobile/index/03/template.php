<?php !isset($c) && exit();?>
<div class="wrapper">
	<?php
	$ad_ary=ly200::ad_custom(0, 87);
    for($i=0; $i<$ad_ary['Count']; ++$i){
		if(!is_file($c['root_path'].$ad_ary['PicPath'][$i][$ad_ary['Lang']])) continue;
		$url=$ad_ary['Url'][$i][$ad_ary['Lang']];
	?>
    <div class="homeitem clean">
    	<a href="<?=$url?$url:'javascript:;';?>"><img src="<?=$ad_ary['PicPath'][$i][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][$i][$ad_ary['Lang']];?>" /></a>
    </div>
    <?php }?>
</div>
