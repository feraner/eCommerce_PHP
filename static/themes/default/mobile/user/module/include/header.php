<?php !isset($c) && exit();?>
<?php
$user_title=array(
	'index'		=>	$c['lang_pack']['mobile']['my_account'],
	'order'		=>	$c['lang_pack']['my_orders'],
	'favorite'	=>	$c['lang_pack']['my_fav'],
	'coupon'	=>	$c['lang_pack']['my_coupon'],
	'address'	=>	$c['lang_pack']['my_address_book'],
	'inbox'		=>	$c['lang_pack']['user']['inboxTitle'],
	'setting'	=>	$c['lang_pack']['user']['settingTitle'],
	'password'	=>	$c['lang_pack']['user']['password']
);
?>
<div id="u_header">
	<?php if($a!='index'){?><a class="back" href="javascript:history.back(-1);"></a><?php }?>
    <a class="menu" href="javascript:;"></a>
    <a class="cart" href="/cart/"><?=(int)$c['shopping_cart']['TotalQty']?'<i class="FontBgColor"></i>':''?></a>
    <div class="title"><?=$user_title[$a]?></div>
</div>
