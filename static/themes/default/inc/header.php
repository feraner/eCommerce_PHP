<?php !isset($c) && exit();?>
<?php
include("{$c['static_path']}inc/header.php");
?>
<div id="top_bar_outer">
	<div id="top_bar" class="wide">
    	<?php if($c['config']['global']['HeaderContent']['HeaderContent'.$c['lang']]){?><div class="freeship fl"><?=$c['config']['global']['HeaderContent']['HeaderContent'.$c['lang']];?></div><?php }?>
		<ul class="crossn fr">
			<li class="block fl"><?php include("{$c['static_path']}/inc/sign_in.php");?></li>
			<?php if($c['FunVersion']){?><li class="block fl"><?php include("{$c['static_path']}/inc/currency.php");?></li><?php }?>
			<?php if(($c['FunVersion']>1 && count($c['config']['global']['Language'])>1) || ($c['config']['translate']['IsTranslate']==1 && count($c['config']['translate']['TranLangs']))){?>
            	<li class="block fl"><?php include("{$c['static_path']}/inc/language.php");?></li>
			<?php }?>
		</ul>
		<div class="clear"></div>
	</div>
</div>
<div class="clear"></div>
<div id="header">
	<div class="wide">
		<div class="logo fl"><h1><a href="/"><img src="<?=$c['config']['global']['LogoPath'];?>" alt="<?=$c['config']['global']['SiteName'];?>" /></a></h1></div>
		<div class="header_cart down_header_cart fr" lang="<?=$c['lang'];?>">
			<a rel="nofollow" class="cart_inner" href="/cart/"><span class="cart_count"><?=(int)$c['shopping_cart']['TotalQty'];?></span><span class="cart_text"><?=$c['lang_pack']['cartStr'];?></span></a>
			<div class="cart_note"></div>
		</div>
		<div class="search ajax_search fr">
            <form action="/search/" method="get" class="form">
                <input type="text" class="text fl" placeholder="<?=$c['config']['global']['SearchTips']["SearchTips{$c['lang']}"];?>" name="Keyword" value="<?=$Keyword;?>" autocomplete="off" notnull />
                <input type="submit" class="button fr FontBgColor" value="" />
                <div class="clear"></div>
            </form>
		</div>
		<div class="clear"></div>
	</div>
</div>
<div id="nav_outer" class="NavBgColor">
	<div id="nav" class="wide"<?=$m=='index'?' page="index"':'';?>><?php include('products_catalog.php');?></div>
</div>