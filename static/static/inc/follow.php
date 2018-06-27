<?php !isset($c) && exit();?>
<?php
$ShareMenuAry=$c['config']['global']['ShareMenu'];
?>
<div class="follow_toolbox clearfix">
	<ul>
    	<?php
		foreach($c['share'] as $v){
			if(!$ShareMenuAry[$v]) continue;
		?>
			<li><a rel="nofollow" class="follow_<?=strtolower($v);?>" href="<?=$ShareMenuAry[$v];?>" target="_blank" title="<?=$v;?>"><?=$v;?></a></li>
        <?php }?>
	</ul>
</div>