<?php !isset($c) && exit();?>
<?php
$cur_lang=str_replace('-', '_', substr($c['lang'], 1));
if(in_array($c['lang_oth'], $c['config']['global']['Language']) || reset(explode('.', $_SERVER['HTTP_HOST']))=='www'){
	$dir=preg_replace('/^'.reset(explode('.', $_SERVER['HTTP_HOST'])).'\./i', '', $_SERVER['HTTP_HOST']);
}else{
	$dir=$_SERVER['HTTP_HOST'];
}
!(int)$c['config']['translate']['IsTranslate'] && $c['config']['translate']['TranLangs']=array();

$language_ary=array_merge($c['config']['global']['Language'], $c['config']['translate']['TranLangs']);
$lang_len=count($language_ary);
$len=abs($lang_len/10);

$translate_url=urlencode(ly200::get_domain().$_SERVER['REQUEST_URI']);
$from_lang=$cur_lang=='cn'?'zh-cn':($cur_lang=='jp'?'ja':$cur_lang);
$lang_link="https://translate.google.com/translate?sl=$from_lang&tl=%s&u=";
?>
<dl>
	<?php
	if($lang_len<12){
	?>
		<dt class="<?=($c['FunVersion']<2 && !count($c['config']['translate']['TranLangs']))?'not_dd':'';?>"><?=$c['lang_name'][$cur_lang];?></dt>
		<dd class="language lang">
			<?php
			foreach($c['config']['global']['Language'] as $v){
				$l=$v=='zh_tw'?'zh-tw':$v;
				if($v==$cur_lang) continue;
				$dir_url='http://'.($l==$c['config']['global']['LanguageDefault']?'':$l.'.').$dir.($_SERVER['REQUEST_URI']!='/'?$_SERVER['REQUEST_URI']:'');
			?>
				<a rel="nofollow" href='<?=$dir_url;?>'><?php if(is_file($c['root_path'].$c['config']['global']['LanguageFlag'][$v])){?><img src="<?=$c['config']['global']['LanguageFlag'][$v];?>" alt="<?=$c['lang_name'][$v];?>" /><?php }?><?=$c['lang_name'][$v];?></a>
			<?php }?>
			<?php foreach($c['config']['translate']['TranLangs'] as $v){?>
					<a rel="nofollow" href="<?=sprintf($lang_link, $v).$translate_url;?>" target="_blank" title="<?=$c['translate'][$v][1];?>"><?=$c['translate'][$v][1];?></a>
			<?php }?>
		</dd>
	<?php
	}else{
	?>
		<dt class="btn_language not_dd FontColor"><?=$c['lang_name'][$cur_lang];?></dt>
	<?php }?>
</dl>
<div id="pop_lang_currency" class="hide">
	<button class="shopbox_close"><span>×</span></button>
	<div class="shopbox_wrap">
		<div class="shopbox_skin pop_skin">
			<h4><?=$c['lang_pack']['language'];?></h4>
			<?php
			$k=0;
			for($i=0; $i<$len; ++$i){
			?>
			<ul class="lang_item">
				<?php
				for($j=($k*10); $j<(($k+1)*10); ++$j){
					$v=$language_ary[$j];
					$name=$c['lang_name'][$v]?$c['lang_name'][$v]:$c['translate'][$v][1];
				?>
				<li data-lang="<?=$language_ary[$j];?>"<?=$cur_lang==$v?' class="current"':'';?>><?=$name;?></li>
				<?php }?>
			</ul>
			<?php
				echo $i==4?'<div class="blank15"></div>':'';
				++$k;
			}?>
			<div class="blank15"></div>
		</div>
		<div class="shopbox_bot">
			<div class="pop_currency">
				<span class="pop_currency_title"><?=$c['lang_pack']['chooseCurrency'];?>:</span>
				<a rel="nofollow" class="btn_currency" href="javascript:;" data-currency="<?=$_SESSION['Currency']['Currency'];?>"><?php if(is_file($c['root_path'].$_SESSION['Currency']['FlagPath'])){?><img src="<?=$_SESSION['Currency']['FlagPath'];?>" alt="<?=$_SESSION['Currency']['Currency'];?>" /><?php }?><?=$_SESSION['Currency']['Currency'];?><em></em></a>
				<ul class="pop_currency_menu">
					<?php
					$currency_row=db::get_all('currency', 'IsUsed=1');
					foreach((array)$currency_row as $v){
					?>
						<li><a rel="nofollow" href="javascript:;" data="<?=$v['Currency'];?>"><?php if(is_file($c['root_path'].$v['FlagPath'])){?><img src="<?=$v['FlagPath'];?>" alt="<?=$v['Currency'];?>" /><?php }?><?=$v['Currency'];?></a></li>
					<?php }?>
				</ul>
			</div>
			<a class="btn btn_success btn_save FontBgColor"><?=$c['lang_pack']['save'];?></a>
			<a class="btn btn_cancel"><?=$c['lang_pack']['cart']['cancel'];?></a>
		</div>
	</div>
</div>