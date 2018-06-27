<?php !isset($c) && exit();?>
<div id="lib_user_crumb" class="widget">
	<ul class="crumb_box clearfix">
		<li class="home"><a href="/" title="<?=$c['lang_pack']['user']['home'];?>"><?=$c['lang_pack']['user']['home'];?><i></i></a></li>
		<?php if($a!='index'){?><li class="crumb1"><a href="/account/" title="<?=$c['lang_pack']['user']['indexTitle'];?>"><?=$c['lang_pack']['user']['indexTitle'];?><i></i></a></li><?php }?>
		<?php if($a){?><li class="crumb2<?=($a && !$_GET['OId'])?' root':'';?>"><a href="/account/<?=$a!='index'?$a.($a=='order'?'s':'').'/':'';?>" title="<?=$c['lang_pack']['user'][$a.'Title'];?>"><?=$c['lang_pack']['user'][$a.'Title'];?><i></i></a></li><?php }?>
		<?php if($_GET['OId']){?><li class="root"><a href="/account/orders/view<?=$_GET['OId'];?>.html" title="<?=$_GET['OId'];?>"><?=$_GET['OId'];?><i></i></a></li><?php }?>
	</ul>
</div>