<?php !isset($c) && exit();?>
<div id="top_outer">
	<div class="wide">
    	<a href="/" class="home"><?=$c['lang_pack']['products']['home'];?></a>
		<ul class="crossn fr">
			<li class="block fl"><?php include("{$c['static_path']}/inc/sign_in.php");?></li>
			<?php if($c['FunVersion']){?><li class="block fl"><?php include("{$c['static_path']}/inc/currency.php");?></li><?php }?>
			<li class="fl"><a href="/help/"><?=$c['lang_pack']['help'];?></a></li>
			<?php if(($c['FunVersion']>1 && count($c['config']['global']['Language'])>1) || ($c['config']['translate']['IsTranslate']==1 && count($c['config']['translate']['TranLangs']))){?>
            	<li class="block fl"><?php include("{$c['static_path']}/inc/language.php");?></li>
			<?php }?>
		</ul>
		<div class="clear"></div>
	</div>
</div>
<div class="clear"></div>