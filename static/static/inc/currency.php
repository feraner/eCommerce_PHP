<?php !isset($c) && exit();?>
<?php
if(!file::check_cache('currency.html')){
	ob_start();
        $w='IsUsed=1';
        $currency_row=db::get_all('currency', $w);
?>
<div class="fl"><strong><?=$c['lang_pack']['currency'];?>:</strong></div>
<dl class="fl <?=count($currency_row)>1?'':'crossn_currency_none'?>">
    <dt><strong id="currency" class="FontColor"></strong></dt>
    <dd class="currency">
        <?php foreach((array)$currency_row as $v){?>
            <a rel="nofollow" href="javascript:;" data="<?=$v['Currency'];?>"><?php if(is_file($c['root_path'].$v['FlagPath'])){?><img src="<?=$v['FlagPath'];?>" alt="<?=$v['Currency'];?>" /><?php }?><?=$v['Currency'];?></a>
        <?php }?>
    </dd>
</dl>
<?php 
	$cache_contents=ob_get_contents();
	ob_end_clean();
	file::write_file(ly200::get_cache_path($c['theme'], 0), 'currency.html', $cache_contents);
}
include(ly200::get_cache_path($c['theme']).'currency.html');
?>