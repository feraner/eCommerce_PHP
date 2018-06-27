<?php !isset($c) && exit();?>
<?php
$sahre_data=str::json_data(db::get_value('config', "GroupId='global' and Variable='Share'", 'Value'), 'decode');
?>
<div class="share_toolbox clearfix" data-title="<?=$Name;?>" data-url="<?=ly200::get_domain().$_SERVER['REQUEST_URI'];?>">
	<ul>
		<?php
		for($i=0; $i<4; ++$i){
		?>
			<li><a href="javascript:;" rel="nofollow" class="share_s_btn share_s_<?=$sahre_data[$i];?>" data="<?=$sahre_data[$i];?>"><?=$sahre_data[$i];?></a></li>
		<?php }?>
		<li>
			<a href="javascript:;" rel="nofollow" class="share_s_btn share_s_more">More</a>
			<div class="share_hover">
				<?php
				foreach($sahre_data as $k=>$v){
					if($k<4) continue;
				?>
				<a href="javascript:;" rel="nofollow" class="share_s_btn" id="<?=$v;?>" data="<?=$v;?>"><i class="share_s_<?=$v;?>"></i><span><?=$v;?></span></a>
				<?php }?>
			</div>
		</li>
	</ul>
</div>