<?php !isset($c) && exit();?>
<?php
if(!file::check_cache('footer.html')){
	ob_start();
	
	$help_category_row=str::str_code(db::get_limit('article_category', 'UId="0," and CateId not in(1,99)', "CateId, Category{$c['lang']}", $c['my_order'].'CateId asc', 0, 3));
?>
<div id="footer">
	<?php /* ?>
	<div class="foot_logo">
		<div class="wide clean">
			<div class="logo FontBgColor pic_box"><img src="<?=$c['config']['global']['LogoPath'];?>" alt="<?=$c['config']['global']['SiteName'];?>" /><span></span></div>
		</div>
	</div> <?php */ ?>
	<div class="foot wide clean">
		<div class="foot_menu fcu fl">
        	<div class="foot_menu_hd"><?=$c['lang_pack']['contactUs'];?></div>
			<div class="list">
				<div class="fcu_item"><strong><?=$c['lang_pack']['address'];?>:</strong><?=$c['config']['global']['ContactMenu'][0];?></div>
				<div class="fcu_item"><strong><?=$c['lang_pack']['phone'];?>:</strong><?=$c['config']['global']['ContactMenu'][1];?></div>
				<div class="fcu_item"><strong><?=$c['lang_pack']['email'];?>:</strong><a href="mailto:<?=$c['config']['global']['ContactMenu'][3];?>"><?=$c['config']['global']['ContactMenu'][3];?></a></div>
			</div>
        </div>
		<?php
		foreach((array)$help_category_row as $v){
		?>
		<div class="foot_menu fl">
        	<div class="foot_menu_hd"><?=$v['Category'.$c['lang']];?></div>
            <ul class="list">
            	<?php 
				$help_row=str::str_code(db::get_limit('article', "CateId='{$v['CateId']}'", "AId, Title{$c['lang']}, PageUrl, Url", $c['my_order'].'CateId asc', 0, 5));
				foreach((array)$help_row as $vv){
				?>
            	<li><a href="<?=ly200::get_url($vv, 'article');?>" title="<?=$vv['Title'.$c['lang']];?>"><em></em><?=$vv['Title'.$c['lang']];?></a></li>
                <?php }?>
            </ul>
        </div>
		<?php }?>
		<div class="clear"></div>
	</div>
</div>
<?php 
    $share_count = 0;
    foreach($c['follow'] as $v){
        if(!$c['config']['global']['ShareMenu'][$v]) continue;
        $share_count++;
    }
    if($share_count){
    ?>
	<div class="follow_us_box clearfix">
		<div class="wide clean">
			<?php
			$icon_follow_type=2;
			include("{$c['static_path']}inc/follow_us.php");
			?>
		</div>
	</div>
<?php } ?>
<div id="copyright">
	<div class="wide clean">
		<div class="cp"><?=ly200::partners();?></div>
		<div class="blank12"></div>
		<span class="copyright"><?=$c['config']['global']['CopyRight']['CopyRight'.$c['lang']];?> &nbsp;&nbsp;&nbsp;&nbsp; <?=$c['powered_by'];?></span>
    </div>
</div>
<?php include("{$c['static_path']}inc/chat.php");?>
<?php
	echo ly200::out_put_third_code();

	$cache_contents=ob_get_contents();
	ob_end_clean();
	file::write_file(ly200::get_cache_path($c['theme'], 0), 'footer.html', $cache_contents);
}
include(ly200::get_cache_path($c['theme']).'footer.html');
?>
