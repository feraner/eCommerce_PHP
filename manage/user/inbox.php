<?php !isset($c) && exit();?>
<?php
manage::check_permit('user', 1, array('a'=>'inbox'));//检查权限

if(!$c['manage']['do'] || $c['manage']['do']=='index'){//重新指向订单分类页面
	$c['manage']['do']='products';
}

$c['manage']['do']=='others' && $type=(int)$_GET['Type'];	//0.收件箱 1.发件箱
$out=0;
$open_ary=array();
foreach($c['manage']['permit']['pc']['user']['inbox']['menu'] as $k=>$v){
	if(!manage::check_permit('user', 0, array('a'=>'inbox', 'd'=>$v))){
		if($v=='inbox' && $c['manage']['do']=='inbox') $out=1;
		continue;
	}else{
		$open_ary[]=$v;
	}
}

if($out) js::location('?m=user&a=inbox&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面

$permit_ary=array(
	'orders_edit'		=>	manage::check_permit('user', 0, array('a'=>'inbox', 'd'=>'orders',	'p'=>'edit')),
	'orders_del'		=>	manage::check_permit('user', 0, array('a'=>'inbox', 'd'=>'orders',	'p'=>'del')),
	'orders_export'		=>	manage::check_permit('user', 0, array('a'=>'inbox', 'd'=>'orders',	'p'=>'export')),
	'products_edit'		=>	manage::check_permit('user', 0, array('a'=>'inbox', 'd'=>'products','p'=>'edit')),
	'products_del'		=>	manage::check_permit('user', 0, array('a'=>'inbox',	'd'=>'products','p'=>'del')),
	'products_export'	=>	manage::check_permit('user', 0, array('a'=>'inbox', 'd'=>'products','p'=>'export')),
	'others_add'		=>	manage::check_permit('user', 0, array('a'=>'inbox', 'd'=>'others',	'p'=>'add')),
	'others_edit'		=>	manage::check_permit('user', 0, array('a'=>'inbox', 'd'=>'others',	'p'=>'edit')),
	'others_del'		=>	manage::check_permit('user', 0, array('a'=>'inbox', 'd'=>'others',	'p'=>'del')),
	'others_export'		=>	manage::check_permit('user', 0, array('a'=>'inbox', 'd'=>'others',	'p'=>'export'))
);
?>
<div class="r_nav">
	<h1>{/module.user.inbox.module_name/}</h1>
	<div class="turn_page"></div>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<input type="hidden" name="m" value="user" />
				<input type="hidden" name="a" value="inbox" />
				<?php if($c['manage']['do']=='orders'){?>
					<input type="hidden" name="d" value="orders" />
				<?php }elseif($c['manage']['do']=='products'){?>
					<input type="hidden" name="d" value="products" />
				<?php }else{?>
					<input type="hidden" name="d" value="others" />
				<?php }?>
				<?php if($_GET['Type']){ ?>
					<input type="hidden" name="Type" value="1" />
				<?php } ?>
			</form>
		</div>
		<ul class="ico">
			<?php if($c['manage']['do']=='others' && $type){?>
				<?php if($permit_ary['others_add']){?><li><a class="tip_ico_down add" href="./?m=user&a=inbox&d=others_edit&Type=1" label="{/inbox.send/}"></a></li><?php }?>
			<?php }?>
			<?php if($permit_ary[$c['manage']['do'].'_export']){?><li><a class="tip_ico_down explode" href="./?do_action=user.inbox_explode&Status=<?=$c['manage']['do'];?><?=$type?'&Type=1':''?>" label="{/global.explode/}"></a></li><?php }?>
			<?php if($permit_ary[$c['manage']['do'].'_del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<dl class="edit_form_part">
		<?php if(manage::check_permit('user', 0, array('a'=>'inbox', 'd'=>'products'))){?>
			<dt></dt>
			<dd><a href="./?m=user&a=inbox&d=products"<?=substr_count($c['manage']['do'],'products')?' class="current"':'';?>>{/module.user.inbox.products/}</a></dd>
		<?php }?>
		<?php if(manage::check_permit('user', 0, array('a'=>'inbox', 'd'=>'orders'))){?>
			<dt></dt>
			<dd><a href="./?m=user&a=inbox&d=orders"<?=substr_count($c['manage']['do'],'orders')?' class="current"':'';?>>{/module.user.inbox.orders/}</a></dd>
		<?php }?>
		<?php if(manage::check_permit('user', 0, array('a'=>'inbox', 'd'=>'others'))){?>
			<dt></dt>
			<dd><a href="./?m=user&a=inbox&d=others"<?=substr_count($c['manage']['do'],'others')?' class="current"':'';?>>{/module.user.inbox.others/}</a></dd>
		<?php }?>
	</dl>
</div>
<div id="inbox" class="r_con_wrap">
	<?php if($c['manage']['do']=='orders' || $c['manage']['do']=='products' || $c['manage']['do']=='others'){//订单,产品,其他?>
		<script type="text/javascript">$(document).ready(function(){user_obj.inbox_inbox_init()});</script>
        <?php if($c['manage']['do']=='others'){?>
		<div class="r_con_column">
			<dl class="inbox_type_list">
				<dd><a href="./?m=user&a=inbox&d=others"<?=$type==0?' class="current"':'';?>>{/module.user.inbox.inbox/}</a></dd>
                <dt></dt>
                <dd><a href="./?m=user&a=inbox&d=others&Type=1"<?=$type==1?' class="current"':'';?>>{/module.user.inbox.outbox/}</a></dd>
			</dl>
		</div>
        <?php }?>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary[$c['manage']['do'].'_del']){?>
						<td width="5%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td>
					<?php }?>
                    <?php if($c['manage']['do']=='orders'){?>
                    	<td width="8%" nowrap="nowrap">{/orders.oid/}</td>
                    <?php }elseif($c['manage']['do']=='products'){?>
                    	<td width="8%" nowrap="nowrap">{/products.picture/}</td>
                        <td width="30%" nowrap="nowrap">{/products.name/}</td>
                    <?php }else{?>
                    	<td width="45%" nowrap="nowrap">{/inbox.inbox.subject/}</td>
                    <?php }?>
					<td width="20%" nowrap="nowrap">{/inbox.inbox.sender/}</td>
                    <?php if($type){?>
						<td width="15%" nowrap="nowrap">{/inbox.inbox.receiver/}</td>
					<?php }?>
					<td width="20%" nowrap="nowrap">{/global.time/}</td>
					<?php if($permit_ary[$c['manage']['do'].'_edit']){?><td width="10%" class="last" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$Keyword=$_GET['Keyword'];
				if($type){	//发件箱
					$page_count=20;
					$where="Module='others' and Type=1";
					if($Keyword){
						$user_row=str::str_code(db::get_one('user', "FirstName like '%{$Keyword}%' or LastName like '%{$Keyword}%' or concat(FirstName,' ',LastName) like '%{$Keyword}%' or Email like '%{$Keyword}%'"));
						if($user_row){
							$where.=" and (Subject like '%{$Keyword}%' or UserId like '%|{$user_row['UserId']}|%')";
						}else $where.=" and Subject like '%{$Keyword}%'";
					}
					$msg_row=str::str_code(db::get_limit_page('user_message', $where, '*', 'MId desc', (int)$_GET['page'], $page_count));
					$userAry=array();
					foreach((array)$msg_row[0] as $v){
						if($v['UserId']=='|' || $v['UserId']=='||') continue;
						$userAry[]=(int)str_replace('|', ',', substr($v['UserId'], 1, -1));
					}
					$user_where='UserId in('.(count($userAry)?implode(',', $userAry):'0').')';
					$uesr_row=str::str_code(db::get_all('user', $user_where));
					$user_ary=array();
					foreach((array)$uesr_row as $k=>$v){
						$user_ary[$v['UserId']]=$v;
					}
				}else{
					$page_count=20;
					$where="m.Module='{$c['manage']['do']}'";
					$c['manage']['do']=='others' && $where.=' and m.Type=0';
					if($Keyword){
						$user_row=str::str_code(db::get_one('user', "FirstName like '%{$Keyword}%' or LastName like '%{$Keyword}%' or concat(FirstName,' ',LastName) like '%{$Keyword}%' or Email like '%{$Keyword}%'"));
						if($user_row){
							$where.=" and (m.Subject like '%{$Keyword}%' or m.UserId='{$user_row['UserId']}' or u.Email like '%$Keyword%'";
						}else{
							$where.=" and (m.Subject like '%{$Keyword}%' or u.Email like '%$Keyword%'";
						}
						if($c['manage']['do']=='products'){
							$pro_id_str = '(0';
							$pro_id_row=db::get_all('products', "Name{$c['manage']['web_lang']} like '%$Keyword%'", 'ProId');
							foreach((array)$pro_id_row as $k => $v){
								$pro_id_str.=','.$v['ProId'];
							}
							$pro_id_str.=')';
							$where.=" or m.Subject in $pro_id_str)";
						}else{
							$where.=')';
						}
					}
					(int)$_SESSION['Manage']['GroupId']==3 && $where.=" and u.SalesId='{$_SESSION['Manage']['UserId']}'";//业务员账号过滤
					$msg_row=str::str_code(db::get_limit_page('user_message m left join user u on m.UserId=u.UserId', $where, 'm.*, u.FirstName, u.LastName, u.Email, u.SalesId', 'm.IsRead asc,m.MId desc', (int)$_GET['page'], $page_count));
					if($c['manage']['do']=='products'){
						$pro_ary=array();
						foreach($msg_row[0] as $v){
							$pro_ary[]=(int)$v['Subject'];
						}
						$pro_ary=implode(',',$pro_ary);
						if($pro_ary){
							$pro_ary=db::get_all('products',"ProId in ($pro_ary)");
							$proId_ary=array();
							foreach($pro_ary as $v){
								$proId_ary[$v['ProId']]=$v;
							}
						}
					}
				}
				$i=1;
				foreach($msg_row[0] as $v){
				?>
				<tr>
					<?php if($permit_ary[$c['manage']['do'].'_del']){?>
                    	<td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['MId'];?>" class="va_m" /></td>
					<?php }?>
                    <?php if($c['manage']['do']=='products'){?>
                    	<td nowrap="nowrap" class="img"><img src="<?=ly200::get_size_img($proId_ary[$v['Subject']]['PicPath_0'], end($c['manage']['resize_ary']['products']))?>" /></td>
                    	<td nowrap="nowrap"<?=$v['IsRead']==0?' class="fc_red"':'';?>><?=$proId_ary[$v['Subject']]['Name'.$c['manage']['web_lang']]?></td>
                    <?php }else{?>
                    	<td nowrap="nowrap"<?=$v['IsRead']==0 && !$type?' class="fc_red"':'';?>><?=$v['Subject']?></td>
                    <?php }?>
                    <?php if($type){?>
						<td nowrap="nowrap">{/manage.manage.manager/}</td>
						<td nowrap="nowrap">
							<?php
							$userid=explode('|', $v['UserId']);
							foreach((array)$userid as $k2=>$v2){
								if(!$v2) continue;
								if($k2>8){
									echo '...';
									break;
								}
								echo "<div title='{$user_ary[$v2]['Email']}'>{$user_ary[$v2]['FirstName']} {$user_ary[$v2]['LastName']}</div>";
							}
							?>
						</td>
                    <?php }else{?>
						<td nowrap="nowrap"><?=(int)$v['UserId']?$v['FirstName'].' '.$v['LastName'].' ('.$v['Email'].')':'{/inbox.system_error/}';?></td>
                    <?php }?>
					<td nowrap="nowrap"><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
					<?php if($permit_ary[$c['manage']['do'].'_edit'] || $permit_ary[$c['manage']['do'].'_del']){?>
						<td nowrap="nowrap">
							<?php if($permit_ary[$c['manage']['do'].'_edit']){?><a class="tip_ico tip_min_ico" href="./?m=user&a=inbox&d=<?=$c['manage']['do']?>_edit&MId=<?=$v['MId'];?><?=$type?'&Type=1':''?>" label="{/<?=$type?'global.edit':'global.view'?>/}"><img src="/static/ico/<?=$type?'edit':'search'?>.png" align="absmiddle" /></a><?php }?>
							<?php if($permit_ary[$c['manage']['do'].'_del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=user.inbox_del&MId=<?=$v['MId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
						</td>
					<?php }?>
				</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($msg_row[1], $msg_row[2], $msg_row[3], '?'.ly200::query_string('page').'&page=');?></div>
    <?php
	}elseif($c['manage']['do']=='others_edit' && (int)$_GET['Type']){	//发件箱	
		$MId=(int)$_GET['MId'];
		$UserId=(int)$_GET['UserId'];
		$Name=$_GET['Name'];
		$level_select_ary=$level_ary=array();
		$level_row=str::str_code(db::get_all('user_level', 'IsUsed=1'));
		foreach((array)$level_row as $k=>$v){
			$level_ary[$v['LId']]=$v;
			$level_select_ary[$v['LId']]=$v['Name'.$c['manage']['web_lang']];
		}
		if($UserId){
			$user_row=str::str_code(db::get_one('user', "UserId='$UserId'", '*'));
			$MId && $msg_row=str::str_code(db::get_one('user_message', "MId='$MId'"));
			$userid_ary=array($UserId);
		}else{
			$userid_ary=array();
			$where='1';//条件
			$page_count=100;//显示数量
			$Name && $where.=" and (FirstName like '%$Name%' or LastName like '%$Name%' or concat(FirstName, LastName) like '%$Name%' or Email like '%$Name%')";
			$user_row=str::str_code(db::get_limit_page('user', $where, '*', 'UserId desc', (int)$_GET['page'], $page_count));
			if($MId){
				$msg_row=str::str_code(db::get_one('user_message', "MId='$MId' and Type=1"));
				$userid_ary=explode('|', $msg_row['UserId']);
			}
		}
	?>    
		<script type="text/javascript">$(document).ready(function(){user_obj.inbox_edit_init()});</script>
		<form id="edit_form" class="r_con_form">
			<h3 class="rows_hd"><?=$MId?'{/global.edit/}':'{/global.add/}';?>{/inbox.info.outbox/}</h3>
            <?php if($UserId && $msg_row && $msg_row['Type']==0){?>
                <div class="rows">
                    <label>{/inbox.inbox.subject/}</label>
                    <span class="input"><?=$msg_row['Subject'];?></span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label>{/inbox.content/}</label>
                    <span class="input"><?=$msg_row['Content'];?></span>
                    <div class="clear"></div>
                </div>
            <?php }?>
			<div class="rows">
				<label>{/inbox.inbox.sender/}</label>
				<span class="input">{/manage.manage.manager/}</span>
				<div class="clear"></div>
			</div>
			<?php if(!$UserId){?>
				<div class="rows">
					<label>{/global.search/}</label>
					<span class="input">
						<input name="Name" value="<?=$Name;?>" type="text" class="form_input" size="22" />
						<input type="button" class="sub_btn" value="{/global.submit/}" />
					</span>
					<div class="clear"></div>
				</div>
			<?php }?>
			<div class="rows">
				<label>{/inbox.inbox.receiver/}</label>
				<span class="input user_choice">
					<?php if($UserId){?>
						<div id="inbox_box">
							<ul class="user_list">
								<li <?php /*?>class="current"<?php */?>><input type="checkbox" name="UserId[]" class="fl" value="<?=$user_row['UserId'];?>" <?php /*?>checked<?php */?> /><a href="javascript:;"><span data="./?m=user&a=user&d=base_info&UserId=<?=$user_row['UserId'];?>"><?=$user_row['Email'];?></span>, &nbsp;&nbsp;&nbsp;<?=$user_row['FirstName'].' '.$user_row['LastName'].', &nbsp;&nbsp;&nbsp;'.$level_ary[$user_row['Level']]['Name'.$c['manage']['web_lang']];?></a></li>
							</ul>
						</div>
					<?php }else{?>
						<div class="user_menu">
							<?php
							foreach((array)$level_row as $k=>$v){
							?>
								<span class="choice_btn"><b><?=$v['Name'.$c['manage']['web_lang']];?></b><input type="checkbox" name="Level[]" class="hide level_check"  value="<?=$v['LId'];?>" /></span>
							<?php }?>
						</div>
						<div id="list_box">
							<ul class="user_list">
								<?php
								foreach((array)$user_row[0] as $v){
								?>
									<li status="<?=(int)$v['Level'];?>"<?=in_array($v['UserId'], $userid_ary)?' class="current"':'';?>><input type="checkbox" name="UserId[]" class="fl" value="<?=$v['UserId'];?>"<?=in_array($v['UserId'], $userid_ary)?' checked':'';?> /><a href="javascript:;"><span data="./?m=user&a=user&d=base_info&UserId=<?=$v['UserId'];?>"><?=$v['Email'];?></span>, &nbsp;&nbsp;&nbsp;<?=$v['FirstName'].' '.$v['LastName'].', &nbsp;&nbsp;&nbsp;'.$level_ary[$v['Level']]['Name'.$c['manage']['web_lang']];?></a></li>
								<?php }?>
							</ul>
							<div class="blank9"></div>
							<span class="choice_btn all_btn"><b>{/global.select_all/}</b><input type="checkbox" name="AllUser" id="all_user" class="hide level_check" value="1" /></span>
							<div class="blank9"></div>
							<div class="turn_page"><?=ly200::turn_page($user_row[1], $user_row[2], $user_row[3], '?'.ly200::query_string('page').'&page=', '{/global.pre_page/}', '{/global.next_page/}');?> <input type="button" class="clear_check" value="{/user.clear_check/}" /></div>
						</div>
					<?php }?>
					<div class="blank12"></div>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/inbox.inbox.subject/}</label>
				<span class="input"><input type="text" name="Subject" value="<?=(int)$msg_row['Type']?$msg_row['Subject']:'';?>" class="form_input" size="50" maxlength="100" notnull="" /></span>
				<div class="clear"></div>
			</div>
            <div class="rows">
				<label>{/inbox.content/}</label>
				<span class="input"><textarea name="Content" class="large" notnull=""><?=(int)$msg_row['Type']?$msg_row['Content']:'';?></textarea></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.picture/}</label>
				<span class="input upload_file upload_pic">
					<div class="img">
						<div id="PicDetail" class="upload_box preview_pic"><input type="button" id="PicUpload" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="" /></div>
					</div>
					<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
					<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/inbox.info.send/}" />
					<a href="./?m=user&a=inbox&d=others&Type=1" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="do_action" value="user.inbox_edit" />
            <input type="hidden" name="back" value="others&Type=1" />
            <input type="hidden" name="PicPath" value="<?=$msg_row['PicPath']?>" save="<?=is_file($c['root_path'].$msg_row['PicPath']) && !$UserId?1:0;?>" />
			<input type="hidden" name="UserIdStr" value="<?=$UserId?"|{$UserId}|":($msg_row['UserId']?$msg_row['UserId']:'|');?>" data="<?=$UserId?>" />
		</form>
	<?php
	}elseif($c['manage']['do']=='orders_edit' || $c['manage']['do']=='products_edit' || $c['manage']['do']=='others_edit'){
		echo ly200::load_static('/static/js/plugin/lightbox/js/lightbox.min.js','/static/js/plugin/lightbox/css/lightbox.min.css');
		$MId=(int)$_GET['MId'];
		$msg_row=str::str_code(db::get_one('user_message', "MId='$MId'"));
		$UserId=(int)$msg_row['UserId'];
		$uesr_row=str::str_code(db::get_one('user', "UserId='$UserId'"));
		$reply_row=str::str_code(db::get_all('user_message_reply', "MId='$MId'", '*', 'RId asc'));
		$action=explode('_',$c['manage']['do']);
		$action=$action[0];
		if(!$msg_row['IsRead']) db::update('user_message', "MId='$MId'", array('IsRead'=>1));
	?>
		<script type="text/javascript">$(document).ready(function(){user_obj.inbox_edit_init()});</script>
		<form id="edit_form" class="r_con_form">
			<h3 class="rows_hd">{/global.view/}{/inbox.info.inbox/}</h3>
			<div class="rows bg">
				<label>{/inbox.inbox.sender/}</label>
				<span class="input"><?=$UserId?"{$uesr_row['FirstName']} {$uesr_row['LastName']} ({$uesr_row['Email']})":'{/inbox.system_error/}';?></span>
				<div class="clear"></div>
			</div>
            <?php
            	if($action=='orders'){
					$OId=(int)$msg_row['Subject'];
					$orders_row=db::get_one('orders',"OId='$OId'");
			?>
			<div class="rows bg">
				<label>{/orders.oid/}</label>
				<span class="input"><a target="_blank" href="./?m=orders&a=orders&d=view&OrderId=<?=$orders_row['OrderId']?>"><?=$msg_row['Subject']?></a></span>
				<div class="clear"></div>
			</div>
            <?php
            	}elseif($action=='products'){
					$ProId=(int)$msg_row['Subject'];
					$prod_row=db::get_one('products',"ProId='$ProId'");
					$url=ly200::get_url($prod_row,'products');
			?>
            <div class="rows bg">
                <label>{/products.name/}</label>
                <span class="input"><a target="_blank" href="<?=$url?>"><?=$prod_row['Name'.$c['manage']['web_lang']]?></a></span>
                <div class="clear"></div>
            </div>
			<div class="rows bg">
				<label>{/products.picture/}</label>
				<span class="input"><a target="_blank" href="<?=$url?>"><img class="pic" src="<?=ly200::get_size_img($prod_row['PicPath_0'], end($c['manage']['resize_ary']['products']))?>" /></a></span>
				<div class="clear"></div>
			</div>
            <?php }else{?>
            <div class="rows">
                <label>{/inbox.inbox.subject/}</label>
                <span class="input"><?=$msg_row['Subject'];?></span>
                <div class="clear"></div>
            </div>
            <?php }?>
            <div class="rows">
				<label>{/inbox.content/}</label>
				<span class="input">
                	<?=str::format($msg_row['Content']);?>
                    <?php if($msg_row['PicPath']){?>
                    <div><a class="light_box_pic" target="_blank" href="<?=$msg_row['PicPath']?>"><img class="pic" src="<?=$msg_row['PicPath']?>" /></a></div>
                    <?php }?>
                </span>
				<div class="clear"></div>
			</div>
            <?php if($action!='others'){?>
				<?php foreach((array)$reply_row as $k => $v){?>
                <div class="rows dotted <?=!$k?'dotted_top':''?> <?=(int)$v['UserId']?'':'reply'?>">
                    <label>{/inbox.<?=(int)$v['UserId']?'ask':'reply'?>/}</label>
                    <span class="input">
                        <?=str::format($v['Content']);?>
                        <?php if($v['PicPath']){?>
                        <div><a class="light_box_pic" target="_blank" href="<?=$v['PicPath']?>"><img class="pic" src="<?=$v['PicPath']?>" /></a></div>
                        <?php }?>
                    </span>
                    <div class="clear"></div>
                </div>
                <?php }?>
                <div class="rows">
                    <label>{/products.picture/}</label>
                    <span class="input upload_file upload_pic">
                        <div class="img">
                            <div id="PicDetail" class="upload_box preview_pic"><input type="button" id="PicUpload" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="" /></div>
                        </div>
                        <a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
                        <a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
                    </span>
                    <div class="clear"></div>
                </div>
                <div class="rows">
                    <label></label>
                    <span class="input"><textarea name="Content" notnull></textarea></span>
                    <div class="clear"></div>
                </div>
                <div id="View"></div>
            <?php }?>
			<div class="rows">
				<label></label>
				<span class="input">
                	<?php if($action=='others'){?>
                    <a href="./?m=user&a=inbox&d=others_edit&UserId=<?=$msg_row['UserId'];?>&MId=<?=$MId;?>&Type=1" class="btn_ok submit_btn">{/inbox.reply/}</a>
                    <?php }else{?>
                    <input type="submit" class="btn_ok" name="submit_button" value="{/inbox.reply/}" />
                    <?php }?>
					<a href="./?m=user&a=inbox&d=<?=$action?>" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
            <input type="hidden" name="PicPath" value="" />
            <input type="hidden" name="MId" value="<?=$MId?>" />
            <input type="hidden" name="back" value="<?=$action?>" />
            <input type="hidden" name="do_action" value="user.inbox_reply" />
		</form>
    <?php }?>
</div>