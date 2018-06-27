<?php !isset($c) && exit();?>
<?php
if($cfg_module_ary['IsLeftbar']['Category']) include("{$c['theme_path']}inc/catalog.php");
?>
<?php if($attr_ary && $cfg_module_ary['Narrow']==1 && $a=='products'){?>
<div class="narrow_by">
	<div class="cate_title FontBorderColor"><strong><?=$c['lang_pack']['narrow_by'];?></strong><a href="<?='?'.ly200::get_query_string(ly200::query_string('m, a, CateId, page, Price, Narrow'));?>" class="clear_all"><?=$c['lang_pack']['clear'];?></a></div>
	<?php
	foreach((array)$attr_ary as $v){
	?>
	<dl>
		<dt><em class="FontBorderColor"></em><strong><?=$v['Name'.$c['lang']];?></strong></dt>
		<dd>
			<?php
			foreach((array)$all_value_ary[$v['AttrId']] as $k2=>$v2){
				$url=ly200::get_narrow_url($no_narrow_url, $Narrow_ary, $k2);
				$num=ly200::get_narrow_pro_count($Narrow_ary, $k2, $CateId);
				if(!$num) continue;
			?>
				<a href="<?=$url;?>" hidefocus="true"<?=in_array($k2, $Narrow_ary)?' class="current"':'';?>>
					<em class="ns_icon_checkbox"></em>
					<span><?=$v2['Value'.$c['lang']];?><i>(<?=$num;?>)</i></span>
				</a>
			<?php }?>
		</dd>
	</dl>
	<?php }?>
</div>
<?php }?>
<?php
if($cfg_module_ary['IsLeftbar']['Hot']) include("{$c['theme_path']}inc/what_hot.php");
if($cfg_module_ary['IsLeftbar']['Special']) include("{$c['theme_path']}inc/special_offer.php");
if($cfg_module_ary['IsLeftbar']['Popular']) include("{$c['theme_path']}inc/popular_search.php");
?>
<div class="blank15"></div>
<?php
if($cfg_module_ary['IsLeftbar']['Banner']){
	echo ly200::ad(7);
}?>
<div class="blank20"></div>