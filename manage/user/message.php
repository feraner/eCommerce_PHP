<?php !isset($c) && exit();?>
<?php
manage::check_permit('user', 1, array('a'=>'message'));//检查权限

$permit_ary=array(
	'add'	=>	manage::check_permit('user', 0, array('a'=>'message', 'd'=>'add')),
	'edit'	=>	manage::check_permit('user', 0, array('a'=>'message', 'd'=>'edit')),
	'del'	=>	manage::check_permit('user', 0, array('a'=>'message', 'd'=>'del'))
);
?>
<div class="r_nav">
	<h1>{/module.user.message/}</h1>
	<div class="turn_page"></div>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<input type="hidden" name="m" value="user" />
				<input type="hidden" name="a" value="message" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=user&a=message&d=edit" label="{/global.add/}"></a></li><?php }?>
			<?php if($permit_ary['edit']){?><li><a class="tip_ico_down explode" href="./?do_action=user.message_explode" label="{/global.explode/}"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
</div>
<div id="message" class="r_con_wrap">
	<?php if($c['manage']['do']=='index'){?>
		<script type="text/javascript">$(document).ready(function(){user_obj.message_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['del']){?><td width="5%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
					<td width="45%" nowrap="nowrap">{/inbox.title/}</td>
					<td width="25%" nowrap="nowrap">{/inbox.manager/}</td>
					<td width="15%" nowrap="nowrap">{/global.time/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="10%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$Keyword=str::str_code($_GET['Keyword']);
				$where='1';//条件
				$page_count=10;//显示数量
				$Keyword && $where.=" and Title like '%{$Keyword}%'";
				$msg_row=str::str_code(db::get_limit_page('message', $where, '*', 'MId desc', (int)$_GET['page'], $page_count));
				$i=1;
				foreach($msg_row[0] as $v){
				?>
				<tr>
					<?php if($permit_ary['del']){?><td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['MId'];?>" class="va_m" /></td><?php }?>
					<td nowrap="nowrap"><?=$v['Title'];?></td>
					<td nowrap="nowrap"><?=$_SESSION['Manage']['UserName'];?></td>
					<td nowrap="nowrap"><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td nowrap="nowrap">
							<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=user&a=message&d=edit&MId=<?=$v['MId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
							<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=user.message_del&MId=<?=$v['MId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
						</td>
					<?php }?>
				</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($msg_row[1], $msg_row[2], $msg_row[3], '?'.ly200::query_string('page').'&page=');?></div>
	<?php
	}else{
		$MId=(int)$_GET['MId'];
		$msg_row=str::str_code(db::get_one('message', "MId='$MId'"));
	?>
		<?=ly200::load_static('/static/js/plugin/ckeditor/ckeditor.js');?>
		<script type="text/javascript">$(document).ready(function(){user_obj.message_edit_init()});</script>
		<form id="edit_form" class="r_con_form">
			<h3 class="rows_hd"><?=$MId?'{/global.edit/}':'{/global.add/}';?>{/inbox.message.message/}</h3>
			<div class="rows">
				<label>{/inbox.title/}</label>
				<span class="input"><input type="text" name="Title" value="<?=$msg_row['Title'];?>" class="form_input" size="50" maxlength="100" notnull="" /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/inbox.manager/}</label>
				<span class="input"><?=$_SESSION['Manage']['UserName'];?></span>
				<div class="clear"></div>
			</div>
            <div class="rows">
				<label>{/inbox.content/}</label>
				<span class="input"><?=manage::Editor('Content', $msg_row['Content']);?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=user&a=message" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" id="MId" name="MId" value="<?=$MId;?>" />
			<input type="hidden" name="do_action" value="user.message_edit" />
		</form>
	<?php }?>
</div>