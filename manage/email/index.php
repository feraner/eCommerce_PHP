<?php !isset($c) && exit();?>
<?php
manage::check_permit('email', 1);//检查权限

if(!$c['manage']['do'] || $c['manage']['do']=='index'){//重新指向“邮件发送”页面
	$c['manage']['do']='send';
}
?>
<div class="r_nav">
	<h1><?=$c['manage']['do']=='user_level'?'{/email.member_group/}':'{/frame.email/}';?></h1>
	<?php if($c['manage']['do']!='user_level'){?>
		<div class="turn_page"></div>
		<?php if($c['manage']['do']=='newsletter' || $c['manage']['do']=='arrival'){?>
			<div class="search_form">
				<form method="get" action="?">
					<div class="k_input">
						<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
						<input type="button" value="" class="more" />
					</div>
					<input type="submit" class="search_btn" value="{/global.search/}" />
					<input type="hidden" name="m" value="email" />
					<input type="hidden" name="d" value="<?=$c['manage']['do'];?>" />
				</form>
			</div>
		<?php }?>
		<?php if($c['manage']['do']=='newsletter'){?>
			<ul class="ico">
				<li><a class="tip_ico_down explode" id="excel_format" href="javascript:;" label="{/global.explode/}"></a></li>
			</ul>
		<?php }?>
		<dl class="edit_form_part">
			<?php
			$out=0;
			$open_ary=array();
			foreach($c['manage']['permit']['email']['email'] as $k=>$v){
				if(!manage::check_permit('email', 0, array('a'=>$k)) || ($c['FunVersion']==0 && ($k=='send' || $k=='email_logs'))){//没权限，标准版
					if($k=='send' && $c['manage']['do']=='send') $out=1;
					continue;
				}else{
					$open_ary[]=$k;
				}
			?>
			<dt></dt>
			<dd><a href="./?m=email&d=<?=$k;?>"<?=$c['manage']['do']==$k?' class="current"':'';?>>{/module.email.<?=$k;?>/}</a></dd>
			<?php
			}
			if($out) js::location('?m=email&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面
			?>
		</dl>
	<?php }?>
</div>
<div id="email" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='send'){
		//邮件发送
		/***************************** 会员分组 *****************************/
		$level_row=str::str_code(db::get_all('user_level', 'IsUsed=1', "LId, Name{$c['manage']['web_lang']}", 'FullPrice desc'));//会员等级
		$level_row[count($level_row)]=array('LId'=>0, 'Name'.$c['manage']['web_lang']=>'No level');
		$member_ary=array();
		$member_row=str::str_code(db::get_all('user', '1', 'UserId, Email, Level, FirstName, LastName', 'UserId asc'));//会员列表
		foreach($member_row as $v){
			$member_ary[$v['Level']][]=$v;
		}
	?>
		<?=ly200::load_static('/static/js/plugin/operamasks/operamasks-ui.css', '/static/js/plugin/operamasks/operamasks-ui.min.js', '/static/js/plugin/ckeditor/ckeditor.js');?>
        <script type="text/javascript">$(document).ready(function(){email_obj.send_init()});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/email.subject/}</label>
				<span class="input"><input name="Subject" value="" type="text" class="form_input" size="40" maxlength="100" notnull /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/email.addressee/}</label>
				<span class="input nohidden">
					<input type="button" class="btn_ok user_group" name="" value="{/email.member_group/}" />
					<?php /*if($c['FunVersion']>=1){?>
						<span class="upload_file">
							<div>
								<input name="TxtUpload" id="TxtUpload" type="file">
								{/email.import_list/}: {/email.import/}
							</div>
						</span>
					<?php }?>
					<div class="blank9"></div>
					<?php if($c['FunVersion']>=1){?>
						<table cellpadding="5" cellspacing="0" border="0">
							<tr>
								<td valign="top"><textarea name="Email" class="member_textarea MemberToName" notnull><?=$_GET['Email'];?></textarea></td>
								<td valign="top" class="fc_gory">{/email.remark/}</td>
							</tr>
						</table>
					<?php }else{*/?>
                        <div class="blank9"></div>
						<input name="Email" value="<?=$_GET['Email'];?>" type="text" class="form_input MemberToName" size="50" maxlength="100" notnull /><br />{/email.remark_single/}
					<?php //}?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/email.templates/}</label>
				<span class="input">
					<?php if($c['FunVersion']>=1){?>
						<input type="button" class="btn_ok" id="mail_tpl_btn" value="{/email.email_tpl/}" />
						<a href="javascript:;" class="btn_ok btn_save">{/email.save_email_tpl/}</a>
						<div class="blank12"></div>
					<?php }?>
					<?=manage::Editor('Content', '', false);?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="do_action" value="email.send" />
			<input type="hidden" name="Arrival" value="<?=$_GET['Arrival'];?>" />
		</form>
		<?php /***************************** 选择会员分组 Start *****************************/?>
		<div class="pop_form box_user_edit">
			<form id="user_level_form">
				<div class="t"><h1>{/email.member_group/}</h1><h2>×</h2></div>
				<div class="r_con_form">
					<div id="user_group">
						<div class="user_list clean">
							<?php foreach($level_row as $k=>$v){?>
								<div class="level_title"><?=$v['Name'.$c['manage']['web_lang']];?></div>
								<div class="level_list">
									<?php
									foreach((array)$member_ary[$v['LId']] as $k2=>$v2){
									?>
										<span class="choice_btn" title="<?=$v2['Email'];?>"><b><?=($v2['FirstName'] || $v2['LastName'])?$v2['FirstName'].' '.$v2['LastName']:'member'?></b><input type="checkbox" name="User" class="hide" value="<?=$v2['UserId'];?>" /></span>
									<?php }?>
								</div>
							<?php }?>
						</div>
					</div>
				</div>
				<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.confirm/}" /><input type="button" class="btn_cancel" value="{/global.cancel/}" /></div>
				<input type="hidden" name="id" value="<?=$id;?>" />
				<input type="hidden" name="do_action" value="email.user_level" />
			</form>
		</div>
		<?php /***************************** 选择会员分组 End *****************************/?>
		<?php /***************************** 选择邮件模板 Start *****************************/?>
		<div class="pop_form box_tpl_edit">
			<?php
			$i=$j=0;
			$type=array(	//邮件模板分类，键名与目录分类文件夹同名，且必须为英文
				'promotions'	=>	$c['manage']['lang_pack']['email']['email_tpl_class']['promotions'],//促销模板
				'festival'		=>	$c['manage']['lang_pack']['email']['email_tpl_class']['festival'],//节日模板
				'invitation'	=>	$c['manage']['lang_pack']['email']['email_tpl_class']['invitation'],//邀请函模板
			);
			?>
			<form id="email_tpl_form">
				<div class="t"><h1>{/email.email_tpl/}</h1><h2>×</h2></div>
				<div class="r_con_form">
					<div class="email_tab clean">
						<?php foreach($type as $k=>$v){?>
							<div class="item fl<?=$i==0?' cur':'';?>" data-class="<?=$k;?>"><?=$v;?></div>
						<?php
							++$i;
						}?>  
						<div class="item fl" data-class="customize">{/email.email_tpl_class.customize/}</div>	
					</div>
					<div class="tpl_list">
						<?php foreach($type as $k=>$v){?>
							<div class="list clean" style="display:<?=$j==0?'block':'none';?>">
								<?php
								//读取模板目录
								$tpl_dir=$c['manage']['email_tpl_dir'].$k.'/';//模板目录
								$base_dir=$c['root_path'].$tpl_dir;//邮件模板目录绝对路径
								$handle=opendir($base_dir);
								while($tpl=readdir($handle)){
									if($tpl!='.' && $tpl!='..' && is_dir($base_dir.$tpl)){
										$tpl_img=$tpl_dir.$tpl.'/cover.jpg';//封面
										$tpl_name_file=$base_dir.$tpl.'/name.txt';//模板名
										if(!file_exists($base_dir.$tpl.'/template.html')){ continue; }//模板不存在，跳过
										if(file_exists($tpl_name_file)){ $tpl_name=iconv("gbk", "UTF-8", file_get_contents($tpl_name_file)); }
								?>
										<div class="item fl" template="<?=$tpl;?>">
											<div class="img"><img src="<?=$tpl_img;?>" <?=img::img_width_height(120, 160, $tpl_img);?> /><span></span><a href="<?=str_replace('cover', 'big', $tpl_img);?>" class="zoom" target="_blank"><img src="/static/ico/search_big.png" /></a><div class="img_mask"></div></div>
											<div class="name"><?=$tpl_name;?></div>
										</div>
								<?php
									}
								}
								closedir($handle);
								?>
							</div>
						<?php
							++$j;
						}?>
						<div class="list clean" style="display:none;">
							<?php
							$email_list_row=db::get_all('email_list', '1');
							foreach($email_list_row as $v){
							?>
								<div class="item fl" template="<?=$v['EId'];?>">
									<div class="img"><strong><?=$v['Title'];?></strong><span></span><div class="img_mask"></div></div>
									<div class="name"><?=$v['Title'];?></div>
								</div>
							<?php }?>
						</div>
					</div>
					<?php /*
					<div class="list_foot clean">
						<input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" />
						<a href="javascript:;" class="btn_ok btn_del" style="display:none;">{/global.del/}</a>
					</div>*/?>
				</div>
				<div class="button">
					<input type="submit" class="btn_ok" name="submit_button" value="{/global.confirm/}" />
					<a href="javascript:;" class="btn_ok btn_del" style="display:none;">{/global.del/}</a>
					<input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" />
				</div>
				<input type="hidden" value="" name="template" />
				<input type="hidden" value="" name="class" />
				<input type="hidden" value="email.send_get_tpl" name="do_action" />
			</form>
		</div>
		<?php /***************************** 选择邮件模板 End *****************************/?>
		<?php /***************************** 保存模块 Start *****************************/?>
		<div class="pop_form box_email_edit">
			<form id="customize_form">
				<div class="t"><h1>{/global.save/}{/email.email_tpl_class.customize/}</h1><h2>×</h2></div>
				<div class="r_con_form">
					<div class="rows">
						<label>{/global.title/}</label>
						<span class="input"><input name="Title" value="" type="text" class="form_input" maxlength="50" size="30" notnull></span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
				<textarea name="Content" class="hide"></textarea>
				<input type="hidden" name="do_action" value="email.customize_edit" />
			</form>
		</div>
		<?php /***************************** 保存模块 End *****************************/?>
	<?php
	}elseif($c['manage']['do']=='user_level'){
		//会员分组
		$level_row=str::str_code(db::get_all('user_level', 'IsUsed=1', "LId, Name{$c['manage']['web_lang']}", 'FullPrice desc'));//会员等级
		$level_row[count($level_row)]=array('LId'=>0, 'Name'.$c['manage']['web_lang']=>'No level');
		$member_ary=array();
		$member_row=str::str_code(db::get_all('user', '1', 'UserId, Email, Level, FirstName, LastName', 'UserId asc'));//会员列表
		foreach($member_row as $v){
			$member_ary[$v['Level']][]=$v;
		}
	?>
		<script type="text/javascript">$(document).ready(function(){email_obj.email_group_init()});</script>
		<div id="user_group">
			<div class="user_list clean">
				<form id="user_level_form">
					<?php
					foreach($level_row as $k=>$v){
					?>
						<div class="level_title"><?=$v['Name'.$c['manage']['web_lang']];?></div>
						<div class="level_list">
							<?php
							foreach((array)$member_ary[$v['LId']] as $k2=>$v2){
							?>
								<span class="choice_btn" title="<?=$v2['Email'];?>"><b><?=($v2['FirstName'] || $v2['LastName'])?$v2['FirstName'].' '.$v2['LastName']:'member'?></b><input type="checkbox" name="User" class="hide" value="<?=$v2['UserId'];?>" /></span>
							<?php }?>
						</div>
					<?php }?>
					<input type="hidden" name="id" value="<?=$id;?>" />
					<input type="hidden" name="do_action" value="email.user_level">
				</form>
				<div class="blank9"></div>
			</div>
			<div class="list_foot clean"><input type="button" id="button_add" value="{/global.confirm/}" class="btn_ok" /></div>
		</div>
	<?php
	}elseif($c['manage']['do']=='config'){
		//邮件设置
		echo ly200::load_static('/static/js/plugin/ckeditor/ckeditor.js');
		$config_row=str::json_data(db::get_value('config', 'GroupId="email" and Variable="config"', 'Value'), 'decode');
		$notice_row=str::json_data(db::get_value('config', 'GroupId="email" and Variable="notice"', 'Value'), 'decode');
		$BottomContent=str::json_data(db::get_value('config', 'GroupId="email" and Variable="bottom"', 'Value'), 'decode');
	?>
		<script type="text/javascript">$(document).ready(function(){email_obj.config_init()})</script>
		<form id="edit_form" class="r_con_form">
			<h3 class="rows_hd">{/email.mailbox_config/}</h3>
			<div class="rows">
				<label>{/email.from_email/}</label>
				<span class="input"><input name="FromEmail" value="<?=$config_row['FromEmail'];?>" type="text" class="form_input" size="30" maxlength="100" /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/email.from_name/}</label>
				<span class="input"><input name="FromName" value="<?=$config_row['FromName'];?>" type="text" class="form_input" size="30" maxlength="100" /></span>
				<div class="clear"></div>
			</div>
			<div class="rows module1">
				<label>{/email.smtp/}</label>
				<span class="input"><input name="SmtpHost" value="<?=$config_row['SmtpHost'];?>" type="text" class="form_input" size="30" maxlength="100" /></span>
				<div class="clear"></div>
			</div>
			<div class="rows module1">
				<label>{/email.port/}</label>
				<span class="input"><input name="SmtpPort" value="<?=$config_row['SmtpPort'];?>" type="text" class="form_input" size="5" maxlength="5" /></span>
				<div class="clear"></div>
			</div>
			<div class="rows module1">
				<label>{/email.email/}</label>
				<span class="input"><input name="SmtpUserName" value="<?=$config_row['SmtpUserName'];?>" type="text" class="form_input" size="30" maxlength="100" /></span>
				<div class="clear"></div>
			</div>
			<div class="rows module1">
				<label>{/email.password/}</label>
				<span class="input"><input name="SmtpPassword" value="<?=$config_row['SmtpPassword'];?>" type="password" class="form_input" size="30" maxlength="100" /></span>
				<div class="clear"></div>
			</div>
			
			<h3 class="rows_hd">{/email.customize/}</h3>
			<div class="rows">
				<label>{/email.notice.notice_config/}</label>
				<span class="input notice_menu">
					<?php
					$notice_row['order_create']=(int)$c['manage']['config']['CheckoutEmail'];
					foreach($c['manage']['email_notice'] as $k=>$v){
					?>
						<span class="choice_btn<?=$notice_row[$v]?' current':'';?>"><b>{/email.notice.notice_ary.<?=$v;?>/}</b><input type="checkbox" name="Notice[]" class="hide" value="<?=$v;?>"<?=$notice_row[$v]?' checked':'';?> /></span>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows tab_box">
				<label>{/email.bottom_content/}</label>
				<span class="input">
					<?=manage::html_tab_button();?>
					<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
						<div class="tab_txt tab_txt_<?=$k;?>"><?=manage::Editor("BottomContent_{$v}", $BottomContent["BottomContent_{$v}"]);?></div>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="do_action" value="email.config" />
		</form>
	<?php
	}elseif($c['manage']['do']=='email_logs'){
		//邮件发送日志
	?>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<td width="5%" nowrap="nowrap">{/global.serial/}</td>
					<td width="15%" nowrap="nowrap">{/email.email_logs.to_email/}</td>
					<td width="30%" nowrap="nowrap">{/email.email_logs.subject/}</td>
					<td width="10%" nowrap="nowrap">{/email.email_logs.status/}</td>
					<td width="10%" nowrap="nowrap">{/global.time/}</td>
					<td width="10%" class="last" nowrap="nowrap">{/global.operation/}</td>
				</tr>
			</thead>
			<tbody>
				<?php
				$w='1';
				$UserId=$_GET['UserId'];
				$Keyword=$_GET['Keyword'];
				$Module=$_GET['Module'];
				$UserId && $w.=" and UserId='$UserId'";
				$Module && $w.=" and Module='$Module'";
				$Keyword && $w.=" and Log like '%$Keyword%'";
				$manage_logs_row=db::get_limit_page('email_log', $w, '*', 'LId desc', (int)$_GET['page'], 20);
				$i=1;
				foreach($manage_logs_row[0] as $v){
				?>
					<tr>
						<td nowrap="nowrap"><?=$manage_logs_row[4]+$i++;?></td>
						<td nowrap="nowrap"><?=$v['Email'];?></td>
						<td nowrap="nowrap"><?=$v['Subject'];?></td>
						<td nowrap="nowrap">{/email.email_logs.status_ary.0/}</td>
						<td nowrap="nowrap"><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
						<td nowrap="nowrap">
							<a class="tip_ico tip_min_ico" href="./?m=email&d=email_logs_view&LId=<?=$v['LId'];?>" label="{/global.view/}"><img src="/static/ico/search.png" align="absmiddle" /></a>
						</td>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($manage_logs_row[1], $manage_logs_row[2], $manage_logs_row[3], '?'.ly200::query_string('page').'&page=');?></div>
	<?php
	}elseif($c['manage']['do']=='email_logs_view'){
		//邮件发送记录查看
		$LId=(int)$_GET['LId'];
		$log_row=db::get_one('email_log', "LId='$LId'");
	?>
		<div class="r_con_form">
			<h3 class="rows_hd">{/global.view/}{/module.email.email_logs/}</h3>
			<div class="rows">
				<label>{/email.email_logs.to_email/}</label>
				<span class="input"><?=$log_row['Email'];?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/email.email_logs.status/}</label>
				<span class="input">{/email.email_logs.status_ary.0/}</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/global.time/}</label>
				<span class="input"><?=date('Y-m-d H:i:s', $log_row['AccTime']);?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/email.email_logs.subject/}</label>
				<span class="input"><?=$log_row['Subject'];?></span>
				<div class="clear"></div>
			</div>
            <div class="rows">
				<label>{/email.email_logs.content/}</label>
				<span class="input email_content"><?=$log_row['Body'];?></span>
				<?php /*<span class="input"><?=manage::Editor("Body", $log_row['Body']);?></span>*/?>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<a href="./?m=email&d=email_logs" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
		</div>
	<?php
	}elseif($c['manage']['do']=='newsletter'){
		//邮件订阅
		$permit_ary=array(
			'edit'	=>	manage::check_permit('email', 0, array('a'=>'newsletter', 'd'=>'edit')),
			'del'	=>	manage::check_permit('email', 0, array('a'=>'newsletter', 'd'=>'del'))
		);
	?>
		<script type="text/javascript">$(document).ready(function(){email_obj.newsletter_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<td width="50%" nowrap="nowrap">{/global.email/}</td>
					<td width="20%" nowrap="nowrap">{/global.time/}</td>
					<td width="10%" nowrap="nowrap">{/email.newsletter.status/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="10%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$i=1;
				$Keyword=str::str_code($_GET['Keyword']);
				$where='1';
				$Keyword && $where.=" and Email like '%$Keyword%'";
				$newsletter_row=db::get_limit_page('newsletter', $where, '*', 'NId desc', (int)$_GET['page'], 20);
				foreach($newsletter_row[0] as $v){
				?>
					<tr>
						<td nowrap="nowrap"><a href="?m=email&d=send&Email=<?=$v['Email'];?>"><?=$v['Email'];?></a></td>
						<td nowrap="nowrap"><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
						<td nowrap="nowrap"><?=$v['IsUsed']?'{/global.n_y.1/}':'{/global.n_y.0/}';?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?>
									<?php if($v['IsUsed']==1){?>
										<a class="tip_ico tip_min_ico" href="./?do_action=email.newsletter_status&NId=<?=$v['NId'];?>&Type=0" label="{/email.newsletter.cancel/}"><img src="/static/ico/no.png" align="absmiddle" /></a>&nbsp;&nbsp;
									<?php }else{?>
										<a class="tip_ico tip_min_ico" href="./?do_action=email.newsletter_status&NId=<?=$v['NId'];?>&Type=1" label="{/email.newsletter.submit/}"><img src="/static/ico/yes.png" align="absmiddle" /></a>&nbsp;&nbsp;
									<?php }?>
								<?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=email.newsletter_del&NId=<?=$v['NId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($newsletter_row[1], $newsletter_row[2], $newsletter_row[3], '?'.ly200::query_string('page').'&page=');?></div>
	<?php
	}elseif($c['manage']['do']=='arrival'){
		//到货通知
		$permit_ary['del']=manage::check_permit('email', 0, array('a'=>'arrival', 'd'=>'del'));
	?>
		<script type="text/javascript">$(document).ready(function(){email_obj.arrival_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<td width="5%" nowrap="nowrap">{/global.serial/}</td>
					<td width="20%" nowrap="nowrap">{/products.product/}{/products.name/}</td>
					<td width="20%" nowrap="nowrap">{/global.email/}</td>
					<td width="15%" nowrap="nowrap">{/global.time/}</td>
					<?php /*<td width="10%" nowrap="nowrap">{/email.send_status/}</td>*/?>
					<td width="15%" nowrap="nowrap">{/email.send_time/}</td>
					<?php if($permit_ary['del']){?><td width="10%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$i=1;
				$Keyword=str::str_code($_GET['Keyword']);
				$where='1';
				$Keyword && $where.=" and (p.Name{$c['manage']['web_lang']} like '%$Keyword%' or concat(p.Prefix, p.Number) like '%$Keyword%' or p.SKU like '%$Keyword%' or u.Email like '%$Keyword%')";
				$arrival_row=str::str_code(db::get_limit_page('arrival_notice a left join products p on a.ProId=p.ProId left join user u on a.UserId=u.UserId', $where, "a.*, p.ProId, p.Name{$c['manage']['web_lang']}, u.Email", 'a.AId desc', (int)$_GET['page'], 20));
				foreach($arrival_row[0] as $v){
				?>
					<tr>
						<td nowrap="nowrap"><?=$newsletter_row[4]+$i++;?></td>
						<td><a href="<?=ly200::get_url($v, 'products');?>" target="_blank"><?=$v['Name'.$c['manage']['web_lang']];?></a></td>
						<td nowrap="nowrap"><a href="?m=email&d=send&Email=<?=$v['Email'];?>&Arrival=<?=$v['AId'];?>"><?=$v['Email'];?></a></td>
						<td nowrap="nowrap"><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
						<?php /*<td nowrap="nowrap"><?=$v['IsSend']?'<span class="fc_red">{/email.send_status_ary.1/}</span>':'{/email.send_status_ary.0/}';?></td>*/?>
						<td nowrap="nowrap"><?=$v['SendTime']?date('Y-m-d H:i:s', $v['SendTime']):'N/A';?></td>
						<?php if($permit_ary['del']){?><td nowrap="nowrap"><a class="tip_ico tip_min_ico del" href="./?do_action=email.arrival_del&AId=<?=$v['AId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a></td><?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($arrival_row[1], $arrival_row[2], $arrival_row[3], '?'.ly200::query_string('page').'&page=');?></div>
	<?php }elseif($c['manage']['do']=='system'){//系统邮件设置?>
    	<?php
        $sys_tpl_row=db::get_all('system_email_tpl', '1', 'SId, Template, IsUsed');
		$sys_tpl_ary = array();
		foreach ($sys_tpl_row as $k=>$v){
			$sys_tpl_ary[$v['Template']] = $v;
		}
		$sys_lang = '';
		foreach($c['manage']['config']['Language'] as $k=>$v){
			$sys_lang .= $v.',';
		}
		?>
    	<?=ly200::load_static('/static/js/plugin/operamasks/operamasks-ui.css', '/static/js/plugin/operamasks/operamasks-ui.min.js', '/static/js/plugin/ckeditor/ckeditor.js');?>
        <script type="text/javascript">$(function (){email_obj.system_init();})</script>
		<form id="edit_form" class="r_con_form">
        	<div class="rows">
                <label>{/email.subject/}</label>
                <span class="input"><?=manage::form_edit('', 'text', 'Title', 40, 150, 'notnull');?></span>
                <div class="clear"></div>
            </div>
            <div class="rows">
                <label>{/global.used/}</label>
                <span class="input">
                	<div class="switchery">
                        <input type="checkbox" name="IsUsed" value="1">
                        <div class="switchery_toggler"></div>
                        <div class="switchery_inner">
                            <div class="switchery_state_on"></div>
                            <div class="switchery_state_off"></div>
                        </div>
                    </div>
                    <span class="tool_tips_ico" content="{/email.isused_tips/}"></span>
                </span>
                <div class="clear"></div>
            </div>
        	<div class="rows tab_box">
				<label>{/email.templates/}</label>
				<span class="input">
					<?php /*?><input type="button" class="btn_ok" id="sys_tpl_btn" value="{/email.email_tpl/}" />//下次更新删掉8-10<?php */?>
                    <select id="template_select" lang="<?=$sys_lang;?>">
                    	<option value="">{/global.select_index/}</option>
                        <?php foreach ($c['sys_email_tpl'] as $k=>$v){?>
                        <option value="<?=$v;?>">{/email.sys_email_tpl.<?=$v;?>/} - <?=$sys_tpl_ary[$v]['IsUsed']?'{/global.turn_on/}':'{/global.close/}';?></option>
                        <?php }?>
                    </select>
                    <div class="blank9"></div>
                    {/email.sys_remark/}
                </span>
				<div class="clear"></div>
			</div>
			<div class="rows tab_box">
				<label>&nbsp;</label>
				<span class="input">
					<?=manage::html_tab_button();?>
                    <?php
                    foreach($c['manage']['config']['Language'] as $k=>$v){
					?>
                        <div class="tab_txt tab_txt_<?=$k;?>"><?=manage::Editor("Content_{$v}", '');?></div>
                    <?php }?>
                </span>
				<div class="clear"></div>
			</div>
            <div class="rows">
				<label></label>
				<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
				<div class="clear"></div>
			</div>
            <input type="hidden" name="template" value="">
            <input type="hidden" name="do_action" value="email.system_tpl_edit">
		</form>
        <?php /***************************** 选择系统模板 Start *****************************/?>
		<?php /*?><div class="pop_form sys_tpl_edit">////下次更新删掉8-10
			<form id="email_tpl_form" onsubmit="return false;">
				<div class="t"><h1>{/email.email_tpl/}</h1><h2>×</h2></div>
				<div class="r_con_form">
                	<div class="tpl_list">
                    	<div class="list clean" style="display:block">
                        	<?php foreach ($c['sys_email_tpl'] as $k=>$v){?>
                        	<div class="item fl" template="<?=$v;?>">
                                <div class="img"><strong>{/email.sys_email_tpl.<?=$v;?>/}</strong><span></span><div class="img_mask"></div></div>
                                <div class="name"><?=$sys_tpl_ary[$v]['IsUsed']?'{/global.turn_on/}':'<span class="fc_red">{/global.close/}</span>';?></div>
                            </div>
                            <?php }?>
                        </div>
                    </div>
				</div>
				<div class="button">
                	<input type="hidden" name="template" value="" />
					<input type="submit" class="btn_ok" name="submit_button" value="{/global.confirm/}" />
					<input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" />
                    <input type="hidden" name="do_action" value="email.sys_get_tpl" />
                    <input type="hidden" name="lang" value="<?=$sys_lang;?>" />
				</div>
			</form>
		</div><?php */?>
		<?php /***************************** 选择系统模板 End *****************************/?>
    <?php }?>
</div>