<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
?>
<div id="lib_user_menu">
	<h3 class="title"><?=$c['lang_pack']['user']['indexTitle'];?></h3>
	<ul>
		<li><a href="/account/" <?=$a=='index' ? 'class="cur"' : ''; ?>><?=$c['lang_pack']['user']['basicTitle'];?></a></li>
		<li><a href="/account/orders/" <?=$a=='order' ? 'class="cur"' : ''; ?>><?=$c['lang_pack']['user']['orderTitle'];?><?php if($c['FunVersion'] && $user_order_row) echo "<b>$user_order_row</b>";?></a></li>
		<li><a href="/account/review/" <?=$a=='review' ? 'class="cur"' : ''; ?>><?=$c['lang_pack']['user']['reviewTitle'];?></a></li>
		<li><a href="/account/favorite/" <?=$a=='favorite' ? 'class="cur"' : ''; ?>><?=$c['lang_pack']['user']['favoriteTitle'];?></a></li>
		<li><a href="/account/coupon/" <?=$a=='coupon' ? 'class="cur"' : ''; ?>><?=$c['lang_pack']['user']['couponTitle'];?></a></li>
		<li><a href="/account/address/" <?=$a=='address' ? 'class="cur"' : ''; ?>><?=$c['lang_pack']['user']['addressTitle'];?></a></li>
		<li><a href="/account/setting/" <?=$a=='setting' ? 'class="cur"' : ''; ?>><?=$c['lang_pack']['user']['settingTitle'];?></a></li>
		<li><a href="/account/inbox/" <?=$a=='message' || $a=='products' || $a=='inbox' ? 'class="cur"' : ''; ?>><?=$c['lang_pack']['user']['messageTitle'];?></a></li>
		<li><a href="/account/logout.html"><?=$c['lang_pack']['user']['signOut'];?></a></li>
	</ul>
</div>