<?php
manage::check_permit('manage', 1);//检查权限

if(!$c['manage']['do'] || $c['manage']['do']=='index'){//重新指向“管理员”页面
	$c['manage']['do']='manage';
}

$permit_ary=array(
	'add'	=>	manage::check_permit('manage', 0, array('a'=>'manage', 'd'=>'add')),
	'edit'	=>	manage::check_permit('manage', 0, array('a'=>'manage', 'd'=>'edit')),
	'del'	=>	manage::check_permit('manage', 0, array('a'=>'manage', 'd'=>'del'))
);
?>
<div class="r_nav">
	<h1>{/module.manage.module_name/}</h1>
	<div class="turn_page"></div>
	<?php if($c['manage']['do']=='manage'){?>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=manage&d=manage&p=edit" label="{/global.add/}"></a></li><?php }?>
		</ul>
	<?php }elseif($c['manage']['do']=='manage_logs'){?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<div class="ext">
					<div class="rows">
						<label>{/manage.manage_logs.module/}</label>
						<span class="input"><select name="Module">
							<option value="">{/global.select_index/}</option>
							<?php
							foreach($c['manage']['lang_pack']['set']['manage_logs'] as $k=>$v){
								echo "<option value='{$k}'>{/set.manage_logs.$k/}</option>";
							}?>
						</select></span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="manage" />
				<input type="hidden" name="d" value="manage_logs" />
			</form>
		</div>
	<?php }?>
	<dl class="edit_form_part">
		<?php
		$out=0;
		$open_ary=array();
		foreach($c['manage']['permit']['manage']['manage'] as $k=>$v){
			if(!manage::check_permit('manage', 0, array('a'=>$k))){
				if($k=='manage' && $c['manage']['do']=='manage') $out=1;
				continue;
			}else{
				$open_ary[]=$k;
			}
		?>
		<dt></dt>
		<dd><a href="./?m=manage&d=<?=$k;?>"<?=$c['manage']['do']==$k?' class="current"':'';?>>{/module.manage.<?=$k;?>/}</a></dd>
		<?php
		}
		if($out) js::location('?m=manage&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面
		?>
	</dl>
</div>
<div id="manage" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='manage'){
		//管理员
		if($c['manage']['page']=='index'){
			//管理员管理列表
	?>
			<script type="text/javascript">$(document).ready(function(){manage_obj.manage_list_init()});</script>
			<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
				<thead>
					<tr>
						<td width="8%" nowrap="nowrap">{/global.serial/}</td>
						<td width="15%" nowrap="nowrap">{/manage.manage.username/}</td>
						<td width="15%" nowrap="nowrap">{/manage.manage.group/}</td>
						<td width="15%" nowrap="nowrap">{/manage.manage.last_login_time/}</td>
						<td width="15%" nowrap="nowrap">{/manage.manage.last_login_ip/}</td>
						<td width="10%" nowrap="nowrap">{/manage.manage.locked/}</td>
						<td width="10%" nowrap="nowrap">{/manage.manage.create_time/}</td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="12%" nowrap="nowrap">{/global.operation/}</td><?php }?>
					</tr>
				</thead>
				<tbody>
					<?php
					$data=array('Action'=>'ueeshop_web_manage_list');
					$result=ly200::api($data, $c['ApiKey'], $c['api_url']);
					if($result['ret']==1){
						foreach($result['msg'] as $k=>$v){
							$u=$v['UserName'].'.'.$v['GroupId'].'.'.$v['Locked'];
					?>
						<tr>
							<td nowrap="nowrap"><?=$k+1;?></td>
							<td nowrap="nowrap"><?=$v['UserName'];?></td>
							<td nowrap="nowrap"><?=$c['manage']['lang_pack']['manage']['manage']['permit_name'][$v['GroupId']];?></td>
							<td nowrap="nowrap"><?=$v['LastLoginTime']?date('Y-m-d H:i:s', $v['LastLoginTime']):'';?></td>
							<td nowrap="nowrap"><?=$v['LastLoginIp'];?></td>
							<td nowrap="nowrap">{/global.n_y.<?=$v['Locked'];?>/}</td>
							<td nowrap="nowrap"><?=date('Y-m-d', $v['AccTime']);?></td>
							<?php if($permit_ary['edit'] || $permit_ary['del']){?>
                            <td nowrap="nowrap">
                                <?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=manage&d=manage&p=edit&u=<?=$u;?>" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" /></a><?php }?>
                                <?php if($_SESSION['Manage']['UserName']!=$v['UserName'] && $permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=manage.manage_del&u=<?=$v['UserName'];?>" label="{/global.del/}"><img src="/static/ico/del.png" alt="{/global.del/}" /></a><?php }?>
                            </td>
							<?php }?>
						</tr>
					<?php 
						}
					}
					?>
				</tbody>
			</table>
		<?php
		}else{
			//管理员编辑
			$name_ary=array(
				'category'	=>	'{/global.category/}',
				'photo'		=>	'{/set.photo.pic_list/}',
				'page'		=>	'{/page.page.page/}',
				'news'		=>	'{/news.news.news/}',
				'model'		=>	'{/products.attribute/}',
				'business'	=>	'{/module.products.business.module_name/}',
			);
			
			if($_GET['u']){
				$data=@explode('.', $_GET['u']);
				if($data[1]!=1){
					$manage_permit=db::get_all('manage_permit', "UserName='{$data[0]}'");
					$manage_permit_ary=array();
					foreach($manage_permit as $v){
						$manage_permit_ary[$v['Module']]=array(
							0=>(int)$v['Permit'],
							1=>@json_decode(stripslashes($v['DetailsPermit']), true)
						);
					}
				}
				//print_r($manage_permit_ary);
			}
		?>
			<script type="text/javascript">$(document).ready(function(){manage_obj.manage_edit_init()});</script>
			<form id="edit_form">
				<div class="m_lefter r_con_form">
					<div class="rows">
						<label>{/manage.manage.username/}</label>
						<span class="input">
							<input name="UserName" value="<?=$data[0];?>" type="text" class="form_input w_160" maxlength="30" size="30" <?=$data[0]?'readonly':'';?> notnull> <font class="fc_red">*</font>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/manage.manage.password/}</label>
						<span class="input"><input name="Password" value="" type="password" class="form_input w_160" maxlength="30" size="30" <?=$data[0]?'':'notnull';?>> <?=$data[0]?'<font class="tips">{/manage.manage.password_un_mod/}</font>':'<font class="fc_red">*</font>';?></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/manage.manage.confirm_password/}</label>
						<span class="input"><input name="ConfirmPassword" value="" type="password" class="form_input w_160" maxlength="30" size="30" <?=$data[0]?'':'notnull';?>> <?=$data[0]?'':'<font class="fc_red">*</font>';?></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/manage.manage.group/}</label>
						<span class="input"><select name="GroupId">
							<option value="1"<?=$data[1]==1?' selected="selected"':'';?>>{/manage.manage.permit_name.1/}</option>
							<option value="2"<?=$data[1]==2?' selected="selected"':'';?>>{/manage.manage.permit_name.2/}</option>
							<option value="3"<?=$data[1]==3?' selected="selected"':'';?>>{/manage.manage.permit_name.3/}</option>
						</select></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/manage.manage.locked/}</label>
						<span class="input"><input type="checkbox" name="Locked" value="1" <?=$data[2]?'checked="checked" ':'';?>/></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label></label>
						<span class="input">
							<?php if(($data && manage::check_permit('manage', 0, array('a'=>'manage', 'd'=>'edit'))) || (!$data && manage::check_permit('manage', 0, array('a'=>'manage', 'd'=>'add')))){?>
								<input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" />
							<?php }?>
							<a href="./?m=manage&a=manage" class="btn_cancel">{/global.return/}</a>
						</span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="m_righter">
					<div id="PermitBox" style="display:<?=$data[1]==1?'none':'';?>">
						<div class="PermitHead">{/manage.manage.permit/}&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" id="selected_all" hidefocus="true" />{/global.select_all/}</div>
						<?php
						foreach($c['manage']['permit'] as $v){
							foreach((array)$v as $k1=>$v1){
								//一级权限
								if(@in_array($k1, $c['manage']['permit_base'])) continue;
								$input="Permit_{$k1}";
								$name="{/module.{$k1}.module_name/}";
						?>
							<div class="module" style="display:<?=($data[1]==2 || ($data[1]==3 && in_array($k1, array('orders', 'user'))))?'block':'none';?>;">
								<input type="checkbox" name="<?=$input;?>" <?=$manage_permit_ary[$k1][0]?'checked':'';?> value="1" id="<?=$input;?>" /> <label for="<?=$input;?>"><?=$name;?></label>
								<?php
								if($v1){
									//二级权限
									foreach((array)$v1 as $k2=>$v2){
										$v2=(array)$v2;
										$input="Permit_{$k1}_{$k2}";
										$name=$v2['menu']?"{/module.{$k1}.{$k2}.module_name/}":"{/module.{$k1}.{$k2}/}";
								?>
									<dl>
										<dt><input type="checkbox" name="<?=$input;?>" <?=$manage_permit_ary[$k1][1][$k2][0]?'checked':'';?> value="1" id="<?=$input;?>" /> <label for="<?=$input;?>"><?=$name;?></label></dt>
										<?php
										if($v2['menu']){
											//三级权限
										?>
										<dd>
											<?php
											foreach((array)$v2['menu'] as $v3){
												$input="Permit_{$k1}_{$k2}_{$v3}";
												$name="{/module.{$k1}.{$k2}.{$v3}/}";
												$name_ary[$v3] && $name=$name_ary[$v3];
											?>
											<input type="checkbox" name="<?=$input;?>" <?=$manage_permit_ary[$k1][1][$k2][1][$v3][0]?'checked':'';?> value="1" id="<?=$input;?>" /> <label for="<?=$input;?>"><?=$name;?></label>
												<?php if($v2['permit'][$v3]){//三级操作权限?>
													<span>(
													<?php
													foreach((array)$v2['permit'][$v3] as $v4){
														$input="Permit_{$k1}_{$k2}_{$v3}_{$v4}";
													?>
														<input type="checkbox" name="<?=$input;?>" <?=$manage_permit_ary[$k1][1][$k2][1][$v3][1][$v4][0]?'checked':'';?> value="1" id="<?=$input;?>" /> <label for="<?=$input;?>">{/global.<?=$v4;?>/}</label>
													<?php }?>
													)</span>
												<?php }?>
											<?php }?>
										</dd>
										<?php
										}elseif(!$v2['menu'] && $v2['permit']){
											//三级操作权限
										?>
										<dd>
											<span>(
											<?php
											foreach((array)$v2['permit'] as $v3){
												$input="Permit_{$k1}_{$k2}_{$v3}";
											?>
											<input type="checkbox" name="<?=$input;?>" <?=$manage_permit_ary[$k1][1][$k2][1][$v3][0]?'checked':'';?> value="1" id="<?=$input;?>" /> <label for="<?=$input;?>">{/global.<?=$v3;?>/}</label>
											<?php }?>
											)</span>
										</dd>
										<?php }?>
									</dl>
									<div class="clear"></div>
								<?php
									}
								}?>
							</div>
						<?php
							}
						}?>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="Method" value="<?=$data[0]?1:0;?>" />
				<input type="hidden" name="do_action" value="manage.manage_edit">
			</form>
		<?php }?>
	<?php
	}elseif($c['manage']['do']=='manage_logs'){
		//系统日志
	?>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<td width="5%" nowrap="nowrap">{/global.serial/}</td>
					<td width="10%" nowrap="nowrap">{/manage.manage_logs.username/}</td>
					<td width="10%" nowrap="nowrap">{/manage.manage_logs.module/}</td>
					<td width="25%" nowrap="nowrap">{/manage.manage_logs.log_contents/}</td>
					<td width="10%" nowrap="nowrap">{/manage.manage_logs.ip/}</td>
					<td width="15%" nowrap="nowrap">{/manage.manage_logs.ip_from/}</td>
					<td width="10%" nowrap="nowrap">{/global.time/}</td>
				</tr>
			</thead>
			<tbody>
				<?php
				$w='1';
				$Keyword=$_GET['Keyword'];
				$Module=$_GET['Module'];
				$Module && $w.=" and Module='$Module'";
				$Keyword && $w.=" and (Log like '%$Keyword%' or UserName='$Keyword')";
				$manage_logs_row=db::get_limit_page('manage_operation_log', $w, '*', 'LId desc', (int)$_GET['page'], 20);
				$i=1;
				foreach($manage_logs_row[0] as $v){
				?>
					<tr>
						<td nowrap="nowrap"><?=$manage_logs_row[4]+$i++;?></td>
						<td nowrap="nowrap"><?=$v['UserName'];?></td>
						<td nowrap="nowrap">{/set.manage_logs.<?=$v['Module'];?>/}</td>
						<td><?=$v['Log'];?></td>
						<td nowrap="nowrap"><?=$v['Ip'];?></td>
						<td nowrap="nowrap"><?=ly200::ip($v['Ip']);?></td>
						<td nowrap="nowrap"><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($manage_logs_row[1], $manage_logs_row[2], $manage_logs_row[3], '?'.ly200::query_string('page').'&page=');?></div>
	<?php }?>
</div>