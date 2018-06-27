<?php !isset($c) && exit();?>
<?php if($c['manage']['do']=='index'){?>
	<div class="r_nav">
		<h1>{/module.email.send/}</h1>
	</div>
<?php }?>
<div id="send" class="r_con_wrap">
	<?php if($c['manage']['do']=='index'){?>
		<?=ly200::load_static('/static/js/plugin/operamasks/operamasks-ui.css', '/static/js/plugin/operamasks/operamasks-ui.min.js', '/static/js/plugin/ueditor/ueditor.config.js');?>
        <script type="text/javascript">$(function(){email_obj.send_init();});</script>
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
                    <div class="blank9"></div>
                    <input name="Email" value="<?=$_GET['Email'];?>" type="text" class="form_input MemberToName" size="50" maxlength="100" notnull /><br />{/email.remark_single/}
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/email.templates/}</label>
				<span class="input">
					<?php if($c['FunVersion']>=1){?>
						<input type="button" class="btn_ok" id="mail_tpl_btn" value="{/email.mail_tpl/}" />
						<div class="blank12"></div>
					<?php }?>
					<?=manage::ueditor('Content', '', false);?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="do_action" value="email.send" />
		</form>
		<form id="email_tpl_form" class="pop_contents">
			<div class="email_tab clean">
				<?php
				$i=0;
				$type=array(	//邮件模板分类，键名与目录分类文件夹同名，且必须为英文
					'promotions'	=>	$c['manage']['lang_pack']['email']['email_tpl_class']['promotions'],//促销模板
					'festival'		=>	$c['manage']['lang_pack']['email']['email_tpl_class']['festival'],//节日模板
					'invitation'	=>	$c['manage']['lang_pack']['email']['email_tpl_class']['invitation'],//邀请函模板
				);
				foreach($type as $k=>$v){
				?>
					<div class="item fl<?=$i++==0?' cur':'';?>" data-class="<?=$k;?>">{/email.mail_tpl_class.<?=$k;?>/}</div>
				<?php }?>  	
			</div>
			<div class="tpl_list">
				<?php
				$i=0;
				foreach($type as $k=>$v){?>
					<div class="list clean" style="display:<?=$i++==0?'block':'none';?>">
						<?php
						//读取模板目录
						$tpl_dir=$c['manage']['email_tpl_dir'].$k.'/';//模板目录
						$base_dir=$c['root_path'].$tpl_dir;//邮件模板目录绝对路径
						$handle=opendir($base_dir);
						while($tpl=readdir($handle)){
							if($tpl!='.' && $tpl!='..' && is_dir($base_dir.$tpl)){
								$tpl_img=$tpl_dir.$tpl.'/cover.jpg';//封面
								$tpl_name_file=$base_dir.$tpl.'/name.txt';//模板名
								if(!file_exists($base_dir.$tpl.'/template.html')) continue;//模板不存在，跳过
								if(file_exists($tpl_name_file)) $tpl_name=iconv("gbk", "UTF-8", file_get_contents($tpl_name_file));
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
				<?php }?>
			</div>
			<div class="list_foot clean"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></div>
			<input type="hidden" value="" name="template" />
			<input type="hidden" value="" name="class" />
			<input type="hidden" value="email.send_get_tpl" name="do_action" />
		</form>
	<?php
	}else{
		$level_row=str::str_code(db::get_all('user_level', 'IsUsed=1', "LId, Name{$c['manage']['web_lang']}", 'FullPrice desc'));//会员等级
		$level_row[count($level_row)]=array('LId'=>0, 'Name'.$c['manage']['web_lang']=>'No level');
		$member_ary=array();
		$member_row=str::str_code(db::get_all('user', '1', 'UserId, Email, Level, FirstName, LastName', 'UserId asc'));//会员列表
		foreach($member_row as $v){
			$member_ary[$v['Level']][]=$v;
		}
	?>
		<script type="text/javascript">$(function(){email_obj.email_group_init();});</script>
		<div id="user_group">
			<div class="list_hd"><div class="list_title">{/email.member_group/}</div></div>
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
	<?php }?>
</div>