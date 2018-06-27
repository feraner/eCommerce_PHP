<?php !isset($c) && exit();?>
<div class="w970 nav clean">
	<?php
    $Nav=str::json_data(htmlspecialchars_decode($blog_set_row['NavData']), 'decode');
	foreach((array)$Nav as $k=>$v){
	?>
    <div class="item fl"><a href="<?=$v[1];?>" class="fl" target="_blank"><?=$v[0];?></a></div>
    <?php }?>
</div><!-- end of .nav -->