<?php !isset($c) && exit();?>
<?php
$ShareMenuAry=$c['config']['global']['ShareMenu'];
?>
<div class="follow_us_list follow_us_type_<?=(int)$icon_follow_type;?> clearfix">
	<ul>
    	<?php
		foreach($c['follow'] as $v){
			if(!$ShareMenuAry[$v]) continue;
		?>
			<li><a rel="nofollow" class="icon_follow_<?=strtolower($v);?>" href="<?=$ShareMenuAry[$v];?>" target="_blank" title="<?=$v;?>"><?=$v;?></a></li>
        <?php }?>
	</ul>
</div>