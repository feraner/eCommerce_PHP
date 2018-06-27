<?php !isset($c) && exit();?>
<div class="w970">
	<?php if(is_file($c['root_path'].$blog_set_row['Banner'])){?>
		<img src="<?=$blog_set_row['Banner'];?>" />
	<?php }?>
</div>