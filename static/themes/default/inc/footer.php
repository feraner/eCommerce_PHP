<?php !isset($c) && exit();?>
<?php
if(!file::check_cache('footer.html')){
	ob_start();
?>
<div id="footer_outer">
	<div id="service" class="wide clearfix">
		<?php
		$article_category_row=str::str_code(db::get_limit('article_category', 'UId="0," and CateId not in(1,99)', "CateId, Category{$c['lang']}", $c['my_order'].'CateId asc', 0, 2));
		foreach((array)$article_category_row as $k=>$v){
		?>
		<dl class="fore_<?=$k;?> fl">
			<dt><?=$v['Category'.$c['lang']];?></dt>
			<dd>
				<?php
				$article_row=str::str_code(db::get_limit('article', "CateId='{$v['CateId']}'", "AId, Title{$c['lang']}, PageUrl, Url", $c['my_order'].'AId desc', 0, 5));
				foreach((array)$article_row as $v2){
				?>
				<a href="<?=ly200::get_url($v2, 'article');?>" title="<?=$v2['Title'.$c['lang']];?>"><?=str::str_echo($v2['Title'.$c['lang']], 30, 0, '..');?></a>
				<?php }?>
			</dd>
		</dl>
		<?php }?>
		<dl class="fore_2 fl">
			<dt><?=$c['lang_pack']['newsletter_title'];?></dt>
			<dd class="newsletter">
            <form id="newsletter_form">
				<?=$c['lang_pack']['newsletter_notes'];?>
				<input type="text" class="text" name="Email" value="" notnull="" format="Email" />
				<input type="submit" class="button FontBgColor" value="<?=$c['lang_pack']['newsletter_btn'];?>" />
			</form>
            </dd>
		</dl>
		<?php
		$info_first=str::str_code(db::get_one('info_category', 'UId="0,"', "CateId, UId, Category{$c['lang']}", $c['my_order'].'CateId asc'));
		if($info_first){
		?>
		<dl class="fore_3 fl">
			<dt><?=$info_first['Category'.$c['lang']];?></dt>
			<dd>
				<?php
				$info_row=str::str_code(db::get_limit('info', "CateId in(select CateId from info_category where UId='0,{$info_first['CateId']},') or CateId='{$info_first['CateId']}'", "InfoId, Title{$c['lang']}, Url, PageUrl", $c['my_order'].'InfoId desc', 0, 5));
				foreach((array)$info_row as $v){
				?>
				<a href="<?=ly200::get_url($v, 'info');?>" title="<?=$v['Title'.$c['lang']];?>"><?=str::str_echo($v['Title'.$c['lang']], 25, 0, '..');?></a>
				<?php }?>
			</dd>
		</dl>
        <?php }?>
	</div>
	<div id="footer" class="wide clearfix">
		<?php 
            $share_count = 0;
            foreach($c['follow'] as $v){
                if(!$c['config']['global']['ShareMenu'][$v]) continue;
                $share_count++;
            }
            if($share_count){
            ?>
			<div class="follow_us_box clearfix">
				<div class="follow_title"><?=$c['lang_pack']['followUs'];?>:</div>
				<div class="follow_content">
					<?php
					$icon_follow_type=0;
					include("{$c['static_path']}inc/follow_us.php");
					?>
				</div>
			</div>
		<?php } ?>
		<div class="nav">
			<?php
			$nav_row=db::get_value('config', "GroupId='themes' and Variable='FooterData'", 'Value');
			$nav_data=str::json_data($nav_row, 'decode');
			foreach((array)$nav_data as $k=>$v){
				$nav=ly200::nav_style($v);
				if(!$nav['Name']) continue;
			?>
			<?=$k?'|':'';?><a href="<?=$nav['Url'];?>"<?=$nav['Target'];?>><?=$nav['Name'];?></a>
			<?php }?>
		</div>
        <div class="foot_pay"><?=ly200::partners();?></div>
        <div class="foot_copy copyright"><?=$c['config']['global']['CopyRight']['CopyRight'.$c['lang']];?><?=$c['powered_by']?' &nbsp;&nbsp;&nbsp;&nbsp; '.$c['powered_by']:'';?></div>
	</div>
</div>
<?php
	include("{$c['static_path']}/inc/chat.php");
	echo ly200::out_put_third_code();

	$cache_contents=ob_get_contents();
	ob_end_clean();
	file::write_file(ly200::get_cache_path($c['theme'], 0), 'footer.html', $cache_contents);
}
include(ly200::get_cache_path($c['theme']).'footer.html');
?>
