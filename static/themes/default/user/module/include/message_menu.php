<div class="message_list fr">
	<?php if($c['FunVersion']){ ?>
		<?php 
			$user_prod_msg=db::get_row_count('user_message',"UserId='{$_SESSION['User']['UserId']}' and Module='products' and IsReply=1");
		?>
		<a href="/account/inbox/" class="sys_bg_button <?=$a=='inbox' ? 'cur' : ''; ?>"><?=$c['lang_pack']['user']['inboxTitle'];?><?=$user_msg_len ? '<span>'.$user_msg_len.'</span>' : ''; ?></a>
		<a href="/account/products/" class="sys_bg_button <?=$a=='products' ? 'cur' : ''; ?>"><?=$c['lang_pack']['user']['productsTitle'];?><?=$user_prod_msg ? '<span>'.$user_prod_msg.'</span>' : ''; ?></a>
	<?php } ?>
	<a href="/account/message/" class="sys_bg_button <?=$a=='message' ? 'cur' : ''; ?>"><?=$c['lang_pack']['mobile']['notice']; ?></a>
</div>