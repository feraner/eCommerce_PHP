<?php !isset($c) && exit();?>
<div id="b_footer">
	<div class="wrap">
    	<?php if($c['config']['global']['Blog']){?><div class="row"><?=$c['config']['global']['Blog'];?></div><?php }?>
    	<div class="row"><?=substr_count(ly200::get_domain(), 'vgcart.com')?'':$c['powered_by'];?></div>
    </div>
</div>