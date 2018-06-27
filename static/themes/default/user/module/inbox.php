<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

$MId=(int)$_GET['MId'];
$UserId=$_SESSION['User']['UserId'];
$d=$_GET['d'];
echo ly200::load_static('/static/js/plugin/lightbox/js/lightbox.min.js','/static/js/plugin/lightbox/css/lightbox.min.css');
?>
<div id="lib_user_inbox" class="clearfix" type="<?=$d;?>">
	<?php
	if($MId){
		$back_url="/account/{$d}/";
		$row=str::str_code(db::get_one('user_message', "MId='$MId'".($d=='outbox'?" and UserId='$UserId'":" and UserId like '%|{$UserId}|%'")));
		!$row && js::location($back_url);
		$is_read=0;
		if($row['IsRead'] && $row['Type']==1){
			$userid_ary=array_flip(explode('|', $row['UserId']));
			$isread_ary=explode('|', $row['IsRead']);
			$key=$userid_ary[$UserId];
			$is_read=$isread_ary[$key];
			if($is_read!=1){
				$isread_ary[$key]=1;
				$IsRead=implode('|', $isread_ary);
				db::update('user_message', "MId='$MId'", array('IsRead'=>$IsRead));
			}
		}
		?>
        <a href="javascript:javascript :history.back(-1);" class="user_back"><?=$c['lang_pack']['user'][$d];?></a>
		<div class="msg_view">
        	<h3 class="title"><?=$row['Subject'];?></h3>
			<div class="date"><?=date('M d,Y H:i:s', $row['AccTime']);?></div>
			<div class="content">
            	<?=str::format($row['Content']);?>
            </div>
            <?php if($row['PicPath']){?>
                <div class="images">
                    <a class="light_box_pic" href="<?=$row['PicPath'];?>"><img class="pic" src="<?=$row['PicPath']?>"></a>
                </div>
	        <?php }?>
		</div>
	<?php }else{?>
		<script type="text/javascript">$(document).ready(function(){user_obj.inbox_init()});</script>
		<?php if($d=='write'){ ?>
			<script type="text/javascript">$(document).ready(function(){user_obj.write_inbox()});</script>
			<a href="javascript:javascript :history.back(-1);" class="user_back"><?=$c['lang_pack']['user']['writeInbox'];?></a>
			<div class="menu<?=$d=='write'?'':' hide';?>">
				<form id="inbox_form" name="inbox_form" class="inbox_form user_form" method="post" enctype="multipart/form-data" >
					<div class="rows">
						<label><?=$c['lang_pack']['user']['subject'];?>:</label>
						<span class="input"><input type="text" name="Subject" placeholder="<?=$c['lang_pack']['user']['subject'];?>" value="" class="form_input" size="50" maxlength="100" notnull="" /></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label><?=$c['lang_pack']['user']['content'];?>:</label>
						<span class="input"><textarea name="Content" placeholder="<?=$c['lang_pack']['user']['content'];?>" class="form_text" notnull=""></textarea></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label><?=$c['lang_pack']['user']['image'];?>:</label>
						<div class="input upload_box">
	                        <input class="upload_file" id="upload_file" type="file" name="PicPath" onchange="loadImg(this);" accept="image/gif,image/jpeg,image/png">
	                        <div id="pic_show" class="pic_box"></div>
	                    </div>
	                    <div class="submit">
	                    	<input type="submit" class="submit_btn" name="submit_button" value="<?=$c['lang_pack']['user']['submit'];?>" />
	                    </div>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
					<input type="hidden" name="do_action" value="user.write_inbox" />
				</form>
			</div>
		<?php }else{ ?>
			<div id="user_heading" class="fl">
				<h2><?=$c['lang_pack']['user']['inboxTitle'];?></h2>
			</div>
			<?php include('include/message_menu.php'); ?>
			<div class="clear"></div>
			<div class="blank20"></div>
			<div class="inbox_menu">
				<ul class="menu_title">
					<li>
						<a href="/account/inbox/" hidefocus="true"<?=$d=='inbox'?' class="current FontBorderColor"':'';?>>
							<?php 
								if($user_msg_len) echo '<span></span>';
							?>
							<?=$c['lang_pack']['user']['inbox'];?>
						</a>
					</li>
					<li>
						<a href="/account/outbox/" hidefocus="true"<?=$d=='outbox'?' class="current FontBorderColor"':'';?>>
							<?=$c['lang_pack']['user']['outbox'];?>
						</a>
					</li>
					<li class="write"><a href="/account/write/" hidefocus="true"><?=$c['lang_pack']['user']['writeInbox'];?></a></li>
				</ul>
				<div class="menu_content">
					<div class="menu<?=$d=='inbox'?'':' hide';?>"><div id="inbox_list" page="1"></div></div>
					<div class="menu<?=$d=='outbox'?'':' hide';?>"><div id="outbox_list" page="1"></div></div>
				</div>
			</div>
		<?php } ?>
	<?php }?>
</div>