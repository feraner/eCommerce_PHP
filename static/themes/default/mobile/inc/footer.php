<?php !isset($c) && exit();?>
<?php
$ShareMenuAry=$c['config']['global']['ShareMenu'];
?>
<footer>
	<div id="prolist_mask_footer"></div>
	<div class="footer_top clean"></div>
	<?php /* ?>
	<div class="follow_us_list">
		<ul>
			<?php
			foreach($c['follow'] as $v){
				if(!$ShareMenuAry[$v]) continue;
			?>
				<li><a rel="nofollow" class="icon_follow_<?=strtolower($v);?>" href="<?=$ShareMenuAry[$v];?>" target="_blank" title="<?=$v;?>"><?=$v;?></a></li>
			<?php }?>
		</ul>
	</div>
	<div class="newsletter_box">
		<h3><?=$c['lang_pack']['mobile']['email_sign_up'];?></h3>
		<div class="newsletter_main">
			<form id="newsletter_form">
				<input type="text" name="Email" value="" placeholder="<?=$c['lang_pack']['newsletterTips'];?>..." class="form_text" format="Email" notnull />
				<input type="submit" value="<?=$c['lang_pack']['ok'];?>" class="form_button FontBgColor" />
			</form>
		</div>
	</div><?php */ ?>
	<ul class="footer_list ui_border_t">
		 <?php 
        $help_category_row=str::str_code(db::get_limit('article_category', 'UId="0," and CateId not in(1,99) and CateId = 2', "CateId, Category{$c['lang']}", $c['my_order'].'CateId asc', 0, 5));
        foreach((array)$help_category_row as $v){
        ?>
			<li class="ui_border_b">
				<a href="javascript:;" class="list_close help_click"><span class="title"><?=$v['Category'.$c['lang']];?></span><em></em><i></i></a>
				<ul class="help_list clean">
					<?php 
					$help_row=str::str_code(db::get_limit('article', "CateId='{$v['CateId']}'", "AId, Title{$c['lang']}, PageUrl, Url", $c['my_order'].'AId desc', 0, 5));
					foreach((array)$help_row as $vv){
					?>
					<li><a href="<?=ly200::get_url($vv, 'article');?>" title="<?=$vv['Title'.$c['lang']];?>"><?=$vv['Title'.$c['lang']];?></a></li>
					<?php }?>
				</ul>
			</li>
        <?php }?>
	</ul>
	<nav>
		<?php
		$nav_row=db::get_value('config', "GroupId='themes' and Variable='FooterData'", 'Value');
		$nav_data=str::json_data($nav_row, 'decode');
		foreach((array)$nav_data as $k=>$v){
			$nav=ly200::nav_style($v);
			if(!$nav['Name']) continue;
			if($nav['Url']=='/holiday.html' || $nav['Url']=='/blog/' || $nav['Url']=='/sitemap.html') continue; //临时取消节目模板 博客 网站地图
		?>
			<a href="<?=$nav['Url'];?>" class="font_col"<?=$nav['Target'];?>><?=$nav['Name'];?></a>
		<?php }?>
	</nav>
	<?=ly200::partners();?>
	<section class="font_col border_col copyright"><?=$c['config']['global']['CopyRight']['CopyRight'.$c['lang']]?$c['config']['global']['CopyRight']['CopyRight'.$c['lang']].'<br />':'';?><?=$c['powered_by']!=''?$c['powered_by']:'';?></section>
</footer>
<?=ly200::out_put_third_code();?>