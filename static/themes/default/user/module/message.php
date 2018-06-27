<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

$MId=(int)$_GET['MId'];
?>
<div id="lib_user_msg" class="clearfix">
	<?php
	if($MId){
		$row=str::str_code(db::get_one('message', "MId='$MId'"));
		?>
		<a href="javascript:javascript :history.back(-1);" class="user_back"><?=$c['lang_pack']['mobile']['notice'];?></a>
		<div class="msg_view">
	    	<h3 class="title"><?=$row['Title'];?></h3>
			<div class="date"><?=date('M d,Y H:i:s', $row['AccTime']);?></div>
			<div class="content">
	        	<?=str::str_code($row['Content'], 'htmlspecialchars_decode');?>
	        </div>
		</div>
	<?php
	}else{
		$g_page=(int)$_GET['page'];
		$page_count=10;//显示数量
		$row=db::get_limit_page('message', 1, '*', 'MId desc', $g_page, $page_count);
		if($row[1]){
			$row_count=count($row[0]);
			$query_string=ly200::query_string('m,a,page');
	?>
	<div id="user_heading" class="fl">
		<h2><?=$c['lang_pack']['user']['messageTitle'];?></h2>
	</div>
	<?php include('include/message_menu.php'); ?>
	<div class="clear"></div>
	<ul class="menu_title">
		<li>
			<a href="/account/outbox/" hidefocus="true" class="current FontBorderColor">
				<?=$c['lang_pack']['mobile']['notice']; ?>
			</a>
		</li>
	</ul>
	<ul class="msg_list_box">
		<?php
		foreach((array)$row[0] as $k=>$v){
			$url="/account/message/view{$v['MId']}.html";
			$title=$v['Title'];
			$content = $v['Content'];
			$content = strip_tags($content);//去除html标签     
			?>
	        <li class="sys_bg_button">
	        	<a href="<?=$url; ?>" title="<?=$title; ?>">
		        	<span class="time fr"><?=date('M d,Y', $v['AccTime']);?> <br /> <?=date('H:i:s', $v['AccTime']);?></span>
		        	<span class="title"><?=$title; ?></span>
		        	<span class="content">
		        		<?=$content; ?>
		        	</span>
	        	</a>
	        	<?php /*
	        	<a href="<?=$url; ?>" class="view" title="<?=$title; ?>"><?=$c['lang_pack']['user']['view']; ?></a>*/ ?>
	        </li>
		<?php }?>
	</ul>
	<div class="blank20"></div>
	<div id="turn_page"><?=ly200::turn_page_html($row[1], $row[2], $row[3], $query_string, $c['lang_pack']['user']['previous'], $c['lang_pack']['user']['next'], 3, '.html', $html=1);?></div>
	<?php
		}
	}?>
</div>