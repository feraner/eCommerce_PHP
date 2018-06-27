<?php !isset($c) && exit();?>
<?php
$ship_row=str::str_code(db::get_one('user_address_book as a left join country as c on a.CId=c.CId left join country_states as s on a.SId=s.SId', 'a.'.$c['where']['user']." and a.IsBillingAddress=0", 'a.*, c.Country, s.States as StateName', 'a.AccTime desc, a.AId desc'));
?>
<script type="text/javascript">$(function(){user_obj.user_index();});</script>
<div id="user">
	<div class="user_data clean">
		<div class="name FontBgColor">
			<strong><?=$user_row['Email'];?></strong>
		</div>
    </div>
    <div class="user_count clean ui_border_tb">
    	<a href="/cart/" class="box cart">
        	<div class="num"><?=db::get_row_count('shopping_cart', $c['where']['user']);?></div>
            <div class="link"><?=$c['lang_pack']['shoppingCart'];?></div>
        </a>
        <a href="/account/orders/" class="box order">
        	<div class="num"><?=db::get_row_count('orders', $c['where']['user']);?></div>
            <div class="link"><?=$c['lang_pack']['user']['all_orders'];?></div>
        </a>
    </div>
	<div class="divide_8px"></div>
    <aside class="user_menu ui_border_b">
    	<a href="/account/orders/" class="orders"><span class="ui_border_b"><strong><?=$c['lang_pack']['my_orders'];?></strong><em><i></i></em></span></a>
        <a href="/account/favorite/" class="favorite"><span class="ui_border_b"><strong><?=$c['lang_pack']['my_fav'];?></strong><em><i></i></em></span></a>
        <a href="/account/coupon/" class="coupon"><span class="ui_border_b"><strong><?=$c['lang_pack']['my_coupon'];?></strong><em><i></i></em></span></a>
        <a href="/account/address/" class="address"><span class="ui_border_b"><strong><?=$c['lang_pack']['my_address_book'];?></strong><em><i></i></em></span></a>
        <a href="/account/inbox/" class="inbox"><span class="ui_border_b"><strong><?=$c['lang_pack']['user']['inboxTitle'];?></strong><em><i></i></em></span></a>
		<a href="/account/setting/" class="setting"><span class="ui_border_b"><strong><?=$c['lang_pack']['user']['settingTitle'];?></strong><em><i></i></em></span></a>
		<a href="/account/password/" class="password"><span class="ui_border_b"><strong><?=$c['lang_pack']['user']['password'];?></strong><em><i></i></em></span></a>
    </aside>
	<div class="user_button">
		<a href="/account/logout.html" class="btn_global btn_sign_out FontColor FontBorderColor"><?=$c['lang_pack']['sign_out'];?></a>
	</div>
</div>