<?php !isset($c) && exit();?>
<?php
if(!file::check_cache('popular_search.html')){
	ob_start();
?>
<div class="sidebar">
    <h2 class="b_title FontColor"><?=$c['lang_pack']['popular_search'];?></h2>
    <div class="pop_search">
        <?php
        $search_row=str::str_code(db::get_all('popular_search', '1=1', '*', 'SId desc'));
		foreach ($search_row as $k=>$v){?><?=$k?', ':'';?><a href="<?=$v['Url']?$v['Url']:"/search/?Keyword={$v['Name'.$c['lang']]}";?>"><?=$v['Name'.$c['lang']];?></a><?php }?>
    </div>
</div>
<?php 
	$cache_contents=ob_get_contents();
	ob_end_clean();
	file::write_file(ly200::get_cache_path($c['theme'], 0), 'popular_search.html', $cache_contents);
}
include(ly200::get_cache_path($c['theme']).'popular_search.html');
?>
