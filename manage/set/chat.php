<?php !isset($c) && exit();?>
<?php
manage::check_permit('set', 1, array('a'=>'chat'));//检查权限

if(!$c['manage']['do'] || $c['manage']['do']=='index'){//重新指向“风格”页面
	$c['manage']['do']='chat';
}

$row=db::get_all('chat', '1', '*', $c['my_order'].'CId asc');
$bgColor=db::get_value('config', "GroupId='chat' and Variable='chat_bg'", 'Value');
$IsFloatChat=(int)db::get_value('config', "GroupId='chat' and Variable='IsFloatChat'", 'Value');
$chatType=(int)db::get_value('config', "GroupId='chat' and Variable='Type'",'Value');
$chat_data=json_decode($bgColor, !0);
$chat_data['Bg3_0']=$chat_data['Bg3_0']?$chat_data['Bg3_0']:'/static/ico/bg3_0.png';
$chat_data['Bg3_1']=$chat_data['Bg3_1']?$chat_data['Bg3_1']:'/static/ico/bg3_1.png';
$chat_data['Bg4_0']=$chat_data['Bg4_0']?$chat_data['Bg4_0']:'/static/ico/bg4_0.png';
$json_data=array();
foreach($row as $k=>$v){
	$json_data[$v['CId']]=$v;
}
$json_data['add']=manage::check_permit('set', 0, array('a'=>'chat', 'd'=>'chat', 'p'=>'add'));//添加权限
$json_data['edit']=manage::check_permit('set', 0, array('a'=>'chat', 'd'=>'chat', 'p'=>'edit'));//修改权限
$json_data=str::json_data($json_data);

$permit_ary=array(
	'add'	=>	manage::check_permit('set', 0, array('a'=>'chat', 'd'=>'chat', 'p'=>'add')),
	'edit'	=>	manage::check_permit('set', 0, array('a'=>'chat', 'd'=>'chat', 'p'=>'edit')),
	'del'	=>	manage::check_permit('set', 0, array('a'=>'chat', 'd'=>'chat', 'p'=>'del'))
);

echo ly200::load_static('/static/js/plugin/jscolor/jscolor.js', '/static/js/plugin/dragsort/dragsort-0.5.1.min.js');
?>
<div class="r_nav">
	<h1>{/module.set.chat.module_name/}</h1>
	<?php if($c['manage']['do']=='chat'){?>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="javascript:;" label="{/global.add/}"></a></li><?php }?>
		</ul>
	<?php }?>
	<dl class="edit_form_part">
		<?php
		$out=0;
		$open_ary=array();
		foreach($c['manage']['permit']['pc']['set']['chat']['menu'] as $k=>$v){
			if(!manage::check_permit('set', 0, array('a'=>'chat', 'd'=>$v))){
				if($v=='themes' && $c['manage']['do']=='chat') $out=1;
				continue;
			}else{
				$open_ary[]=$v;
			}
		?>
		<dt></dt>
		<dd><a href="./?m=set&a=chat&d=<?=$v;?>"<?=$c['manage']['do']==$v?' class="current"':'';?>>{/module.set.chat.<?=$v;?>/}</a></dd>
		<?php
		}
		if($out) js::location('?m=set&a=chat&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面
		?>
	</dl>
</div>
<div id="chat" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='set'){
		//在线客服设置
	?>
		<script type="text/javascript">$(document).ready(function(){set_obj.chat_set_init()});</script>
		<div class="fixed">
			<form id="edit_form" class="r_con_form">
				<div class="rows">
					<label>{/global.turn_on/}</label>
					<span class="input">
						<div class="switchery<?=$IsFloatChat?' checked':'';?>">
							<input type="checkbox" name="IsFloatChat" value="1"<?=$IsFloatChat?' checked':'';?>>
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/set.chat.pop/}</label>
					<span class="input">
						<div class="switchery<?=$chat_data['IsHide']?' checked':'';?>">
							<input type="checkbox" name="IsHide" value="1"<?=$chat_data['IsHide']?' checked':'';?>>
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
					</span>
					<div class="clear"></div>
				</div>
				<div id="bgcolor" style=" display:<?=$chatType!=1?'block':'none';?>;">
					<div class="rows">
						<label>{/set.chat.color/}</label>
						<span class="input"><div class="classify fl"><input type="text" class='form_input color' name="Color" size="6" value="<?=trim($chat_data['Color']);?>" /></div></span>
						<div class="clear"></div>
					</div>
				</div>
				<div id="mulcolor" style=" display:<?=in_array($chatType, array(1,3))?'block':'none';?>;">
					<?php foreach ((array)$c['chat']['type'] as $k=>$v){?>
					<div class="rows">
						<label><?=$v!='trademanager'?$v:'AliIM';?></label>
						<span class="input"><div class="classify fl"><input type="text" class='form_input color' name="Color<?=$k;?>" size="6" value="<?=$chat_data[$k];?>" /></div></span>
					</div>
					<?php }?>
					<div class="rows">
						<label>Top</label>
						<span class="input"><div class="classify fl"><input type="text" class='form_input color' name="ColorTop" size="6" value="<?=$chat_data['ColorTop'];?>" /></div></span>
					</div>
				</div>
				
				<div id="bg3pic" style="display:<?=$chatType==3?'block':'none';?>;">
					<div class="rows">
						<label>{/global.pic/}<span class="tool_tips_ico" content="{/notes.png_tips/}<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '74*79');?>"></span></label>
						<span class="input upload_file upload_Bg3_0">
							<div class="img">
								<div id="DetailBg3_0" class="upload_box preview_pic"><input type="button" id="Bg3_0" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.png_tips/}{/notes.pic_size_tips/}'), '74*79');?>" /></div>
							</div>
							<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
							<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/global.pic/}<span class="tool_tips_ico" content="{/notes.png_tips/}<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '74*79');?>"></span></label>
						<span class="input upload_file upload_Bg3_1">
							<div class="img">
								<div id="DetailBg3_1" class="upload_box preview_pic"><input type="button" id="Bg3_1" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.png_tips/}{/notes.pic_size_tips/}'), '74*79');?>" /></div>
							</div>
							<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
							<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
						</span>
						<div class="clear"></div>
					</div>
				</div>
				<div id="bg4pic" style="display:<?=$chatType==4?'block':'none';?>;">
					<div class="rows">
						<label>{/global.pic/}<span class="tool_tips_ico" content="{/notes.png_tips/}<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '94*60');?>"></span></label>
						<span class="input upload_file upload_Bg4_0">
							<div class="img">
								<div id="DetailBg4_0" class="upload_box preview_pic"><input type="button" id="Bg4_0" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.png_tips/}{/notes.pic_size_tips/}'), '94*60');?>" /></div>
							</div>
							<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
							<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
						</span>
						<div class="clear"></div>
					</div>
				</div>
				
				<?php if($permit_ary['edit']){?>
					<div class="rows">
						<label></label>
						<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
						<div class="clear"></div>
					</div>
				<?php }?>
				<input type="hidden" name="do_action" value="set.chat_set" />
				<input type="hidden" name="Bg3_0" value="<?=$chat_data['Bg3_0'];?>" save="<?=is_file($c['root_path'].$chat_data['Bg3_0'])?1:0;?>" />
				<input type="hidden" name="Bg3_1" value="<?=$chat_data['Bg3_1'];?>" save="<?=is_file($c['root_path'].$chat_data['Bg3_1'])?1:0;?>" />
				<input type="hidden" name="Bg4_0" value="<?=$chat_data['Bg4_0'];?>" save="<?=is_file($c['root_path'].$chat_data['Bg4_0'])?1:0;?>" />
			</form>
		</div>
		<div class="style_box">
			<?php
			$chat_row=str::str_code(db::get_all('chat', '1', '*', 'CId asc'));
			$disabled=$permit_ary['edit']?false:true;
			for($i=0; $i<5; ++$i){
			?>
			<div class="box fl">
				<div class="box_hd"><input type="radio" name="Type" value="<?=$i;?>"<?=($chatType==$i?' checked':'').($disabled?' disabled':'');?> class="style_select" /></div>
				<div class="box_bd">
					<div class="blank12"></div>
					<?php if ($i==0){?>
						<div id="float_window" class="Color" style="position:inherit; margin:0 auto; background-color:#<?=$chat_data['Color'];?>; top:0; left:0;">
							<div id="inner_window">
								<div id="demo_window" style=" background-color:#<?=$chat_data['Color'];?>;" class="Color">
									<?php 
									foreach($row as $v){
										$link=sprintf($c['chat']['link'][$v['Type']],$v['Account']);
									?>
										<a class="<?=$c['chat']['type'][$v['Type']];?>" href="javascript:;" title="<?=$v['Name'];?>"></a>
										<div class="blank6"></div>
									<?php }?>
								</div>
							</div>
							<a href="javascript:;" id="go_top">TOP</a>
						</div>
					<?php }elseif ($i==1){?>
						<div id="service_0" style=" margin:0 auto; position:inherit;">
							<?php 
								foreach($row as $v){
									$link = sprintf($c['chat']['link'][$v['Type']],$v['Account']);
							?>
								<div class="r r<?=$v['Type'];?> Color<?=$v['Type'];?>" style="background-color:#<?=$chat_data[$v['Type']];?>;"><a href="javascript:;" title="<?=$v['Name'];?>"><?=$v['Name'];?></a></div>
							<?php }?>
							<div class="r top ColorTop" style=" background-color:#<?=$chat_data['ColorTop'];?>;"><a href="javascript:;">TOP</a></div>
						</div>
					<?php }elseif ($i==2){?>
						<div id="service_1" style=" margin:0 auto; position:inherit;">
							<?php 
								foreach($row as $v){
									$link = sprintf($c['chat']['link'][$v['Type']],$v['Account']);
							?>
								<div class="r r<?=$v['Type'];?> Color" style=" background-color:#<?=$chat_data['Color'];?>;"><a href="javascript:;" title="<?=$v['Name'];?>"></a></div>
							<?php }?>
							<div class="r top Color" style=" background-color:#<?=$chat_data['Color'];?>;"><a href="javascript:;"></a></div>
						</div>
					<?php }elseif ($i==3){?>
						<div id="service_2" style=" margin:0 auto; position:inherit;">
							<div class="sert">
								<div class="img0"><img src="<?=$chat_data['Bg3_0'];?>" /></div>
								<div class="img1"><img src="<?=$chat_data['Bg3_1'];?>" /></div>
							</div>
							<?php 
								foreach($row as $v){
									$link = sprintf($c['chat']['link'][$v['Type']],$v['Account']);
							?>
								<div class="r r<?=$v['Type'];?> Color hoverColor<?=$v['Type'];?>" style=" background-color:#<?=$chat_data['Color'];?>;" color="#<?=$chat_data['Color'];?>" hover-color="#<?=$chat_data[$v['Type']];?>"><a href="javascript:;" title="<?=$v['Name'];?>"></a></div>
							<?php }?>
							<div class="r top Color hoverColorTop" style=" background-color:#<?=$chat_data['Color'];?>;" color="#<?=$chat_data['Color'];?>" hover-color="#<?=$chat_data['ColorTop'];?>"><a href="javascript:;"></a></div>
						</div>
					<?php }elseif ($i==4){?>
						<div id="service_3" style=" margin:0 auto; position:inherit;">
							<div class="sert"><img src="<?=$chat_data['Bg4_0'];?>" /></div>
							<?php 
								foreach($row as $v){
									$link = sprintf($c['chat']['link'][$v['Type']],$v['Account']);
							?>
								<div class="r r<?=$v['Type'];?> Color" style=" background-color:#<?=$chat_data['Color'];?>;"><a href="javascript:;" title="<?=$v['Name'];?>"><?=$v['Name'];?></a></div>
							<?php }?>
							<div class="r top Color" style=" background-color:#<?=$chat_data['Color'];?>;"><a href="javascript:;">TOP</a></div>
						</div>
					<?php }?>
				</div><!-- .box_bd -->
			</div>
			<?php }?>
			<div class="clear"></div>
		</div>
	<?php
	}else{
		//在线客户账号管理
	?>
		<script type="text/javascript">$(document).ready(function(){set_obj.chat_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['edit']){?>
						<td width="6%" nowrap="nowrap" class="center">{/global.my_order/}</td>
					<?php }?>
					<td width="22%" nowrap="nowrap">{/set.chat.name/}</td>
					<td width="22%" nowrap="nowrap">{/set.chat.type/}</td>
					<td width="40%" nowrap="nowrap">{/set.chat.account/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td width="8%" nowrap="nowrap">{/global.operation/}</td>
					<?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach($row as $v){
				?>
					<tr cid="<?=$v['CId'];?>">
						<?php if($permit_ary['edit']){?>
							<td nowrap="nowrap" class="center move_myorder" data="move_myorder"><img src="/static/manage/images/products/move.png" align="absmiddle" /></td>
						<?php }?>
						<td nowrap="nowrap"><?=$v['Name'];?></td>
						<td nowrap="nowrap"><?=$c['chat']['type'][$v['Type']];?></td>
						<td nowrap="nowrap"><?=$v['Account'];?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico mod" href="javascript:;" CId="<?=$v['CId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=set.chat_del&CId=<?=$v['CId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" alt="{/global.del/}" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<?php /***************************** 客服编辑 Start *****************************/?>
		<div class="pop_form box_chat_edit" data-chat="<?=htmlspecialchars($json_data);?>">
			<form>
				<div class="t"><h1>{/module.set.chat.module_name/}<em></em></h1><h2>×</h2></div>
				<div class="r_con_form">
					<div class="rows">
						<label>{/set.chat.name/}</label>
						<span class="input"><input type="text" class='form_input' value="" name="Name" maxlength="50" notnull /></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/set.chat.type/}</label>
						<span class="input">
							<select name='Type'>
								<?php foreach($c['chat']['type'] as $k => $v){?>
									<option value="<?=$k;?>"><?=$v;?></option>
								<?php }?>
							</select>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows" id="Picture" style="display:none;">
						<label>{/global.pic/}:</label>
						<span class="input upload_file upload_pic">
							<div class="ubox">
								<div class="img">
									<div id="PicDetail" class="upload_box preview_pic"><input type="button" id="PicUpload" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="" /></div>
								</div>
								<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
								<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
							</div>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/set.chat.account/}</label>
						<span class="input">
                        	<input type="text" class='form_input' value="<?=$row['Account'];?>" name="Account" maxlength="50" notnull />
                            <span class="whatsapp_tips fc_grey">{/set.chat.whatsapp_tips/}</span>
                        </span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
				<input type="hidden" name="CId" value="1" />
				<input type="hidden" name="do_action" value="set.chat_edit" />
				<input type="hidden" name="PicPath" value="" />
			</form>
		</div>
		<?php /***************************** 客服编辑 End *****************************/?>
	<?php }?>
</div>
