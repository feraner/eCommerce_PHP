<?php !isset($c) && exit();?>
<?php
manage::check_permit('set', 1, array('a'=>'shipping'));//检查权限

if(!$c['manage']['do'] || $c['manage']['do']=='index'){//重新指向“快递”页面
	$c['manage']['do']='express';
}
$OvId=(int)$_GET['OvId'];//海外仓ID
$SId=(int)$_GET['SId'];//快递公司ID
$AId=(int)$_GET['AId'];//快递分区ID
$set_row=str::str_code(db::get_one('shipping_config'));

$permit_ary=array(
	'add'			=>	manage::check_permit('set', 0, array('a'=>'shipping', 'd'=>'express', 'p'=>'add')),
	'edit'			=>	manage::check_permit('set', 0, array('a'=>'shipping', 'd'=>'express', 'p'=>'edit')),
	'del'			=>	manage::check_permit('set', 0, array('a'=>'shipping', 'd'=>'express', 'p'=>'del')),
	'insurance_add'	=>	manage::check_permit('set', 0, array('a'=>'shipping', 'd'=>'insurance', 'p'=>'add')),
	'insurance_edit'=>	manage::check_permit('set', 0, array('a'=>'shipping', 'd'=>'insurance', 'p'=>'edit')),
	'insurance_del'	=>	manage::check_permit('set', 0, array('a'=>'shipping', 'd'=>'insurance', 'p'=>'del')),
	'overseas_add'	=>	manage::check_permit('set', 0, array('a'=>'shipping', 'd'=>'overseas', 'p'=>'add')),
	'overseas_edit'	=>	manage::check_permit('set', 0, array('a'=>'shipping', 'd'=>'overseas', 'p'=>'edit')),
	'overseas_del'	=>	manage::check_permit('set', 0, array('a'=>'shipping', 'd'=>'overseas', 'p'=>'del'))
);
?>
<div class="r_nav<?=$c['manage']['iframe']?' hide':'';?>">
	<h1>{/module.set.shipping.module_name/}</h1>
	<?php if($c['manage']['do']=='overseas'){?>
		<ul class="ico">
			<?php if($c['manage']['page']=='index'){?>
				<?php if($permit_ary['overseas_add']){?><li><a class="tip_ico_down add" href="./?m=set&a=shipping&d=overseas&p=edit" label="{/global.add/}{/module.set.shipping.overseas/}"></a></li><?php }?>
			<?php }?>
		</ul>
	<?php }elseif($c['manage']['do']=='express'){?>
		<ul class="ico">
			<?php if($c['manage']['page']=='index'){?>
				<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=set&a=shipping&d=express&p=edit" label="{/global.add/}{/shipping.shipping.express/}"></a></li><?php }?>
			<?php }?>
		</ul>
	<?php }?>
	<dl class="edit_form_part">
		<?php
		$out=0;
		$open_ary=array();
		foreach($c['manage']['permit']['pc']['set']['shipping']['menu'] as $k=>$v){
			if(!manage::check_permit('set', 0, array('a'=>'shipping', 'd'=>$v))){
				if($v=='express' && $c['manage']['do']=='express') $out=1;
				continue;
			}else{
				$open_ary[]=$v;
			}
			if($v=='overseas' && $c['manage']['config']['Overseas']==0) continue;
		?>
		<dt></dt>
		<dd><a href="./?m=set&a=shipping&d=<?=$v;?>"<?=$c['manage']['do']==$v?' class="current"':'';?>>{/module.set.shipping.<?=$v;?>/}</a></dd>
		<?php
		}
		if($out) js::location('?m=set&a=shipping&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面
		?>
	</dl>
</div>
<div id="shipping" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='express'){
		//快递管理
	?>
		<?php
		if($c['manage']['page']=='index'){
			//快递列表
			$api_ary=array();
			$api_row=db::get_all('shipping_api', '1');
			foreach($api_row as $v){ $api_ary[$v['AId']]=$v['Name']; }
		?>
			<script type="text/javascript">$(function(){set_obj.shipping_express_init();});</script>
			<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
				<thead>
					<tr>
						<td width="6%" nowrap="nowrap">{/global.serial/}</td>
						<td width="20%" nowrap="nowrap">{/shipping.shipping.express/}</td>
						<td width="20%" nowrap="nowrap">{/shipping.shipping.logo/}</td>
						<td width="8%" nowrap="nowrap">{/shipping.shipping.used/}</td>
						<td width="12%" nowrap="nowrap">{/shipping.shipping.weight/}{/shipping.shipping.calculation/}</td>
						<td width="12%" nowrap="nowrap">{/shipping.shipping.set/}</td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td width="5%" nowrap="nowrap">{/global.operation/}</td>
						<?php }?>
					</tr>
				</thead>
				<tbody>
					<?php
					$i=1;
					$weight_area_ary=array('{/shipping.shipping.first_weight/}/{/shipping.shipping.ext_weight/}', '{/shipping.shipping.weightarea/}', '{/shipping.shipping.weightmix/}', '{/shipping.shipping.qty/}', '{/shipping.shipping.special/}');
					$shipping_express_row=str::str_code(db::get_all('shipping', '1', '*', $c['my_order'].'SId asc'));
					foreach($shipping_express_row as $k=>$v){
					?>
						<tr>
							<td nowrap="nowrap"><?=$i++;?></td>
							<td nowrap="nowrap"><?=$v['Express'];?></td>
							<td nowrap="nowrap" class="img"><img src="<?=$v['Logo'];?>" alt="<?=$v['Express'];?>" /></td>
							<td nowrap="nowrap">{/global.n_y.<?=$v['IsUsed'];?>/}</td>
							<td nowrap="nowrap"><?=$v['IsAPI']>0?str_replace('%API%', $api_ary[$v['IsAPI']], $c['manage']['lang_pack']['shipping']['info']['api']):$weight_area_ary[$v['IsWeightArea']];?></td>
							<td nowrap="nowrap"><a href="javascript:;" class="set open_area" data-url="./?m=set&a=shipping&d=express&p=area&SId=<?=$v['SId'];?>" data-name="<?=$v['Express'];?>"><img src="/static/ico/set.png" title="{/global.set/}" align="absmiddle" /></a></a></td>
							<?php if($permit_ary['edit'] || $permit_ary['del']){?>
								<td nowrap="nowrap">
									<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico edit" href="./?m=set&a=shipping&d=express&p=edit&SId=<?=$v['SId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" title="{/global.edit/}{/shipping.shipping.express/}" /></a><?php }?>
									<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=set.shipping_express_del&SId=<?=$v['SId'];?>" label="{/global.del/}"><img src="/static/ico/del.png" alt="{/global.del/}" title="{/global.del/}{/shipping.shipping.express/}" /></a><?php }?>
								</td>
							<?php }?>
						</tr>
					<?php }?>
				</tbody>
			</table>
		<?php 
		}elseif($c['manage']['page']=='edit'){
			//快递编辑
			$SId && $shipping_row=str::str_code(db::get_one('shipping', "SId='{$SId}'"));
			$used_checked=' checked';
			$hot_checked=$state_checked='';
			if($shipping_row){
				$used_checked=$shipping_row['IsUsed']==1?' checked':'';
			}
		?>
			<?=ly200::load_static('/static/js/plugin/operamasks/operamasks-ui.css', '/static/js/plugin/operamasks/operamasks-ui.min.js');?>
			<script type="text/javascript">$(function(){set_obj.shipping_express_edit_init();});</script>
			<form id="edit_form" class="r_con_form">
				<h3 class="rows_hd"><?=$SId?'{/global.edit/}':'{/global.add/}';?>{/shipping.shipping.express/}</h3>
				<div class="rows">
					<label>{/shipping.shipping.express/}</label>
					<span class="input"><input type="text" name="Express" value="<?=$shipping_row['Express'];?>" class="form_input" size="25" maxlength="100" notnull=""></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/shipping.shipping.logo/}</label>
					<span class="input upload_file upload_logo">
						<div class="img">
							<div id="LogoDetail" class="upload_box preview_pic"><input type="button" id="LogoUpload" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '160*160');?>" /></div>
							<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '160*160');?>
						</div>
						<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
						<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/shipping.shipping.used/}</label>
					<span class="input">
						<div class="switchery<?=$used_checked;?>">
							<input type="checkbox" name="IsUsed" value="1"<?=$used_checked;?>>
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
						<span class="tool_tips_ico" content="{/shipping.shipping.used_notes/}"></span>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows hide">
					<label>{/shipping.shipping.brief/}</label>
					<span class="input"><input name="Brief" value="<?=$shipping_row['Brief'];?>" type="text" class="form_input" size="40" maxlength="100" /><span class="tool_tips_ico" content="{/shipping.shipping.brief_notes/}"></span></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/shipping.shipping.query/}</label>
					<span class="input"><input name="Query" value="<?=$shipping_row['Query'];?>" type="text" class="form_input" size="40" maxlength="150" /></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/products.myorder/}</label>
					<span class="input"><?=ly200::form_select($c['manage']['my_order'], 'MyOrder', $shipping_row['MyOrder']);?><span class="tool_tips_ico" content="{/page.page.myorder_notes/}"></span></span>
					<div class="clear"></div>
				</div>
				<?php
				//物流接口
				$api_row=db::get_all('shipping_api', '1', '*', 'AId asc');
				if(count($api_row)){
					//已经使用的接口数据
					$used_api_ary=array();
					$used_api_row=db::get_all('shipping', 'IsAPI>0', 'SId, IsAPI');
					foreach($used_api_row as $v){ $used_api_ary[$v['IsAPI']]=$v['SId']; }
				?>
					<div class="rows api_box">
						<label>{/shipping.shipping.is_api/}</label>
						<span class="input">
							<span class="choice_btn<?=$shipping_row['IsAPI']==0?' current':'';?>"><b>{/global.no_use/}</b><input type="radio" name="IsAPI" class="hide"<?=$shipping_row['IsAPI']==0?' checked':'';?> value="0" /></span>
							<?php
							foreach($api_row as $k=>$v){
								if($used_api_ary[$v['AId']]==$shipping_row['SId'] || !$used_api_ary[$v['AId']]){//正在使用的接口 或者 还没使用的接口
									$ApiName=$v['Name'];
									$ApiName=='4PX' && $ApiName='_4px';
							?>
									<span class="choice_btn<?=$shipping_row['IsAPI']==$v['AId']?' current':'';?>" data-name="<?=$v['Name'];?>" data-api-name="<?=$ApiName;?>" data-attribute="<?=htmlspecialchars(str::json_data($v['Attribute']));?>"><b><?=$v['Name'];?></b><input type="radio" name="IsAPI" class="hide"<?=$shipping_row['IsAPI']==$v['AId']?' checked':'';?> value="<?=$v['AId'];?>" /></span>
							<?php
								}
							}?>
						</span>
						<div class="clear"></div>
					</div>
				<?php }?>
				<div id="method_shipping_box">
					<div class="rows">
						<label>{/shipping.shipping.weight/}{/shipping.shipping.calculation/}</label>
						<span class="input">
							<label><input type="radio" name="IsWeightArea" value="0"<?=$shipping_row['IsWeightArea']==0?' checked':'';?> />{/shipping.shipping.first_weight/}/{/shipping.shipping.ext_weight/}</label><span class="tool_tips_ico" content="{/shipping.shipping.weight_area_0/}"></span>&nbsp;&nbsp;
							<label><input type="radio" name="IsWeightArea" value="1"<?=$shipping_row['IsWeightArea']==1?' checked':'';?> />{/shipping.shipping.weightarea/}</label><span class="tool_tips_ico" content="{/shipping.shipping.weight_area_1/}"></span>&nbsp;&nbsp;
							<label><input type="radio" name="IsWeightArea" value="2"<?=$shipping_row['IsWeightArea']==2?' checked':'';?> />{/shipping.shipping.weightmix/}</label><span class="tool_tips_ico" content="{/shipping.shipping.weight_area_2/}"></span>&nbsp;&nbsp;
							<label><input type="radio" name="IsWeightArea" value="3"<?=$shipping_row['IsWeightArea']==3?' checked':'';?> />{/shipping.shipping.qty/}</label><span class="tool_tips_ico" content="{/shipping.shipping.weight_area_3/}"></span>
							<label><input type="radio" name="IsWeightArea" value="4"<?=$shipping_row['IsWeightArea']==4?' checked':'';?> />{/shipping.shipping.special/}</label><span class="tool_tips_ico" content="{/shipping.shipping.weight_area_4/}"></span>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows" id="WeightBetween">
						<label>{/shipping.shipping.limit/}</label>
						<span class="input">
							<span class="price_input"><input type="text" name="MinWeight" id="MinWeight" value="<?=(float)$shipping_row['MinWeight'];?>" class="form_input" size="4" maxlength="10" rel="amount" /><b class="last">{/shipping.shipping.unit/}</b></span>&nbsp;&nbsp;~&nbsp;&nbsp;
							<span class="box_unlimited">∞</span>
							<span class="box_max">
								<span class="price_input"><input type="text" name="MaxWeight" id="MaxWeight" value="<?=(float)$shipping_row['MaxWeight'];?>" class="form_input" size="4" maxlength="10" rel="amount" /><b class="last">{/shipping.shipping.unit/}</b></span>
							</span>
							<span class="tool_tips_ico" content="{/shipping.shipping.limit_notes/}"></span>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows" id="VolumeBetween">
						<label>{/shipping.shipping.volume_limit/}</label>
						<span class="input">
							<span class="price_input"><input type="text" name="MinVolume" id="MinVolume" value="<?=(float)$shipping_row['MinVolume'];?>" class="form_input" size="4" maxlength="10" rel="amount" /><b class="last">{/shipping.shipping.volume_unit/}</b></span>&nbsp;&nbsp;~&nbsp;&nbsp;
							<span class="box_unlimited">∞</span>
							<span class="tool_tips_ico" content="{/shipping.shipping.volume_limit_notes/}"></span>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows" id="ExtWeight">
						<label>{/shipping.shipping.first_weight/}/{/shipping.shipping.ext_weight/}</label>
						<span class="input">
							<span class="price_input"><b>{/shipping.shipping.first_weight/}<div class="arrow"><em></em><i></i></div></b><input type="text" name="FirstWeight" id="FirstWeight" value="<?=$shipping_row['FirstWeight'];?>" class="form_input" size="4" maxlength="10" rel="amount" /><b class="last">{/shipping.shipping.unit/}</b></span>&nbsp;&nbsp;
							<span class="price_input"><b>{/shipping.shipping.ext_weight/}<div class="arrow"><em></em><i></i></div></b><input type="text" name="ExtWeight" value="<?=$shipping_row['ExtWeight']?>" class="form_input" size="4" maxlength="10" rel="amount" /><b class="last">{/shipping.shipping.unit/}</b></span>&nbsp;&nbsp;
							<span id="StartWeight_span" style="display:<?=$shipping_row['IsWeightArea']==2?'':'none';?>;">
								<span class="price_input"><b>{/shipping.shipping.startweight/}<div class="arrow"><em></em><i></i></div></b><input type="text" name="StartWeight" value="<?=$shipping_row['StartWeight']?>" class="form_input" size="4" maxlength="10" rel="amount" /><b class="last">{/shipping.shipping.unit/}</b></span>
							</span>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows" id="ExtWeightArea">
						<label>{/shipping.shipping.ext_weightarea/}</label>
						<span class="input">
							<input type="button" value="{/global.add/}{/shipping.shipping.node/}" id="addExtWeight" data-unit="{/shipping.shipping.unit/}"  class="btn_ok" />
							<div class="blank6"></div>
							<div id="ExtWeightrow">
								<?php
								$ExtWeightArea=str::json_data(htmlspecialchars_decode($shipping_row['ExtWeightArea']), 'decode');
								!$ExtWeightArea && $ExtWeightArea=array(0);//默认一个0
								foreach($ExtWeightArea as $k=>$v){
								?>
								<div class="row">
									<span class="price_input"><input type="text" name="ExtWeightArea[]" value="<?=$v?>" class="form_input" size="6" maxlength="10" rel="amount" /><b class="last">{/shipping.shipping.unit/}</b></span><?=$k?'<a href="javascript:;"><img hspace="5" src="/static/ico/del.png"></a>':'';?>
								</div>
								<?php }?>
							</div>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows" id="WeightArea">
						<label>{/shipping.shipping.weightarea/}</label>
						<span class="input">
							<span class="choice_btn<?=$shipping_row['WeightType']==1?' current':'';?>">{/shipping.shipping.weightarea_type.0/}<input type="checkbox" name="WeightType" value="1"<?=$shipping_row['WeightType']==1?' checked':'';?> /></span>
							<div class="blank6"></div>
							<input type="button" value="{/global.add/}{/shipping.shipping.node/}" id="addWeight" data-unit="{/shipping.shipping.unit/}"  class="btn_ok" />
							<div class="blank6"></div>
							<div id="Weightrow">
								<?php
								$WeightArea=str::json_data(htmlspecialchars_decode($shipping_row['WeightArea']), 'decode');
								!$WeightArea && $WeightArea=array(0);//默认一个0
								foreach($WeightArea as $k=>$v){
									$readonly=$k==0?true:false;
								?>
								<div class="row">
									<span class="price_input"><input type="text" name="WeightArea[]" value="<?=$v?>" <?=$readonly?'readonly="readonly"':'';?> class="form_input <?=$readonly?'readonly':'';?>" size="6" maxlength="10" rel="amount" /><b class="last">{/shipping.shipping.unit/}</b></span><?=!$readonly?'<a href="javascript:;"><img hspace="5" src="/static/ico/del.png"></a>':'';?>
								</div>
								<?php }?>
							</div>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows" id="Quantity">
						<label>{/shipping.shipping.qty/}</label>
						<span class="input">
							<span class="price_input"><b>{/shipping.shipping.first_qty_0/}<div class="arrow"><em></em><i></i></div></b><input type="text" name="FirstMinQty" id="FirstMinQty" value="<?=$shipping_row['FirstMinQty'];?>" class="form_input" size="4" maxlength="10" rel="amount" /></span>&nbsp;&nbsp;
							<span class="price_input"><b>{/shipping.shipping.first_qty_1/}<div class="arrow"><em></em><i></i></div></b><input type="text" name="FirstMaxQty" id="FirstMaxQty" value="<?=$shipping_row['FirstMaxQty'];?>" class="form_input" size="4" maxlength="10" rel="amount" /></span>&nbsp;&nbsp;
							<span class="price_input"><b>{/shipping.shipping.ext_qty/}<div class="arrow"><em></em><i></i></div></b><input type="text" name="ExtQty" id="ExtQty" value="<?=$shipping_row['ExtQty'];?>" class="form_input" size="4" maxlength="10" rel="amount" /></span>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows" id="VolumeArea">
						<label>{/shipping.shipping.volumearea/}</label>
						<span class="input">
							<input type="button" value="{/global.add/}{/shipping.shipping.node/}" id="addVolume" data-unit="{/shipping.shipping.volume_unit/}"  class="btn_ok" />
							<div class="blank6"></div>
							<div id="Volumerow">
								<?php
								$VolumeArea=str::json_data(htmlspecialchars_decode($shipping_row['VolumeArea']), 'decode');
								!$VolumeArea && $VolumeArea=array(0);//默认一个0
								foreach($VolumeArea as $k=>$v){
									$readonly=$k==0?true:false;
								?>
								<div class="row">
									<span class="price_input"><input type="text" name="VolumeArea[]" value="<?=$v?>" <?=$readonly?'readonly="readonly"':'';?> class="form_input <?=$readonly?'readonly':'';?>" size="6" maxlength="10" rel="amount" /><b class="last">{/shipping.shipping.volume_unit/}</b></span><?=!$readonly?'<a href="javascript:;"><img hspace="5" src="/static/ico/del.png"></a>':'';?>
								</div>
								<?php }?>
							</div>
						</span>
						<div class="clear"></div>
					</div>
				</div>
				<div id="method_api_box">
					<div class="rows">
						<label>{/orders.account_info/}</label><span class="input"></span><div class="clear"></div>
					</div>
				</div>
				<div class="rows">
					<label></label>
					<span class="input">
						<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
						<a href="./?m=set&a=shipping&d=express" class="btn_cancel">{/global.return/}</a>
					</span>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="SId" value="<?=$SId;?>" />
				<input type="hidden" name="Logo" value="<?=$shipping_row['Logo'];?>" save="<?=is_file($c['root_path'].$shipping_row['Logo'])?1:0;?>" />
				<input type="hidden" name="do_action" value="set.shipping_express_edit" />
			</form>
		<?php 
		}elseif($c['manage']['page']=='area'){
			//分区设置
			$shipping_row=str::str_code(db::get_one('shipping', "SId='{$SId}'", 'Express, Logo'));
			!$shipping_row && js::location('./?m=set&a=shipping.express');
			$area_row=str::str_code(db::get_all('shipping_area', "SId='{$SId}' and OvId='{$OvId}'", '*', 'AId asc'));//查询属于此快递的区域
			$shipping_overseas_row=str::str_code(db::get_all('shipping_overseas', '1', '*', $c['my_order'].'OvId asc'));//海外仓
		?>
		 <script type="text/javascript">$(function(){set_obj.shipping_express_area_init();});</script>
		 <div class="shipping_area" SId="<?=$SId;?>">
		 	<div class="nav_list"<?=(int)$c['manage']['config']['Overseas']==0?' style="display:none;"':'';?>>
				<div class="name">{/shipping.area.ships_from/}:</div>
				<div class="list">
					<div class="unit_box">
						<div class="button"><a href="javascript:;" class="add_unit">+</a></div>
						<div class="list">
							<div class="list_bd">
								<?php foreach($shipping_overseas_row as $k=>$v){?>
									<div class="item" data-id="<?=$v['OvId'];?>" data-url="./?m=set&a=shipping&d=express&p=area&iframe=1&SId=<?=$SId;?>&OvId=<?=$v['OvId'];?>" title="<?=$v['Name'.$c['manage']['web_lang']];?>"><span><?=$v['Name'.$c['manage']['web_lang']];?></span><?php /*if($v['OvId']>0){?><em class="btn_overseas_del" href="./?do_action=set.shipping_overseas_del&OvId=<?=$v['OvId'];?>">x</em><?php }*/?></div>
								<?php }?>
							</div>
							<div class="list_ft">
								<form name="Overseas">
									<?=manage::form_edit('', 'text', 'Name', 35, 150, 'notnull');?>
									<input type="hidden" name="OvId" value="-1" />
									<input type="button" id="expand_btn" class="btn_ok btn_overseas_add fl" value="+ {/global.add/}">
								</form>
							</div>
						</div>
					</div>
					<div class="list_btn">
						<?php foreach($shipping_overseas_row as $k=>$v){?>
							<a href="javascript:;" data-id="<?=$v['OvId'];?>" data-url="./?m=set&a=shipping&d=express&p=area&iframe=1&SId=<?=$SId;?>&OvId=<?=$v['OvId'];?>"><span><?=$v['Name'.$c['manage']['web_lang']];?></span><em></em><i></i></a>
						<?php }?>
					</div>
				</div>
			</div>
			<div class="clear"></div>
			<div class="menu_list">
				<div class="menu_hd">
					<h4>{/shipping.area.area_list/}</h4>
					<?php if($permit_ary['add']){?><a class="add" href="javascript:;" data-url="./?m=set&a=shipping&d=express&p=area_edit&SId=<?=$SId?>&OvId=<?=$OvId;?>">{/shipping.area.area_add/}</a><?php }?>
				</div>
				<ul class="menu_area_list">
					<?php
					foreach($area_row as $k=>$v){
					?>
						<li class="clean" aid="<?=$v['AId'];?>">
							<h5 class="fl" title="<?=$v['Name'];?>"><?=$v['Name'];?></h5>
							<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico menu_view fr del" href="./?do_action=set.shipping_area_del&AId=<?=$v['AId'];?>" label="{/global.del/}"><img src="/static/ico/del.png" /></a><?php }?>
							<?php if($permit_ary['edit']){?>
								<a class="tip_ico tip_min_ico menu_view fr edit" href="javascript:;" label="{/global.edit/}" data-url="./?m=set&a=shipping&d=express&p=area_edit&SId=<?=$SId;?>&OvId=<?=$OvId;?>&AId=<?=$v['AId'];?>"><img src="/static/ico/edit.png" /></a>
								<a class="tip_ico tip_min_ico menu_view fr set" href="javascript:;" label="{/shipping.area.setcountry/}" data-url="./?m=set&a=shipping&d=express&p=area_country&SId=<?=$SId;?>&OvId=<?=$OvId;?>&AId=<?=$v['AId'];?>"><img src="/static/ico/set.png" /></a>
							<?php }?>
						</li>
					<?php }?>
				</ul>
			</div>
			<div class="edit_form shipping_area_edit"></div>
		</div>
		<?php
		}elseif($c['manage']['page']=='area_edit'){
			//分区编辑
			$shipping_row=str::str_code(db::get_one('shipping', "SId='{$SId}'"));//所属快递公司
			!$shipping_row && js::location('./?m=set&a=shipping&d=express');
			$AId && $area_row=str::str_code(db::get_one('shipping_area', "AId='{$AId}'"));
			$overseas_row=str::str_code(db::get_one('shipping_overseas', "OvId='$OvId'"));//海外仓
		?>
			<div class="shipping_area_edit">
				<form id="shipping_area_edit_form" class="r_con_form">
					<h3 class="rows_hd"><?=$AId?'{/global.edit/}':'{/global.add/}';?>{/shipping.shipping.area/}</h3>
					<div class="rows">
						<label>{/shipping.shipping.express/}</label>
						<span class="input"><?=$shipping_row['Express'];?></span>
						<div class="clear"></div>
					</div>
					<div class="rows"<?=(int)$c['manage']['config']['Overseas']==0?' style="display:none;"':'';?>>
						<label>{/shipping.area.ships_from/}</label>
						<span class="input"><?=$overseas_row['Name'.$c['manage']['web_lang']];?></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/shipping.area.area/}</label>
						<span class="input"><input type="text" name="Name" value="<?=$area_row['Name'];?>" class="form_input" size="25" maxlength="100" notnull=""></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/shipping.shipping.brief/}</label>
						<span class="input"><input name="Brief" value="<?=$area_row['Brief'];?>" type="text" class="form_input" size="40" maxlength="100" /></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/shipping.area.freeshipping/}</label>
						<span class="input">
							<div class="switchery<?=$area_row['IsFreeShipping']?' checked':'';?>">
								<input type="checkbox" name="IsFreeShipping" value="1"<?=$area_row['IsFreeShipping']?' checked':'';?>>
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
							{/global.used/}&nbsp;
							<?php
							$open=0;
							if($area_row['FreeShippingWeight']>0) $open=1;
							?>
							<span class="price_input">
								<input type="text" name="FreeShippingPrice" value="<?=sprintf('%01.2f', $area_row['FreeShippingPrice']);?>" class="form_input" size="6" maxlength="10" notnull="" style="display:<?=$open==0?'inline-block':'none';?>;"<?=$open==0?'':' disabled';?> />
								<input type="text" name="FreeShippingWeight" value="<?=sprintf('%01.3f', $area_row['FreeShippingWeight']);?>" class="form_input" size="6" maxlength="10" notnull="" style="display:<?=$open==1?'inline-block':'none';?>;"<?=$open==1?'':' disabled';?> />
								<b class="last box_select">
									<span class="head"><span><?=$open==0?$c['manage']['currency_symbol']:'{/shipping.shipping.unit/}';?></span><em></em></span>
									<ul class="list">
										<li><?=$c['manage']['currency_symbol'];?></li>
										<li>{/shipping.shipping.unit/}</li>
									</ul>
								</b>
							</span>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/shipping.area.additional/}</label>
						<span class="input">
							<span class="price_input"><b><?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input type="text" name="AffixPrice" value="<?=sprintf('%01.2f', $area_row['AffixPrice']);?>" class="form_input" size="6" maxlength="10" notnull="" /></span>
						</span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/shipping.shipping.freight/}{/shipping.shipping.calculation/}</label>
						<span class="input">
							<?php
							switch($shipping_row['IsWeightArea']){
								case 0: echo '{/shipping.shipping.first_weight/}/{/shipping.shipping.ext_weight/}<br />'; break;
								case 1: echo '{/shipping.shipping.weightarea/}<br />'; break;
								case 2: echo '{/shipping.shipping.weightmix/}<br />'; break;
								case 3: echo '{/shipping.shipping.qty/}<br />'; break;
								case 4: echo '{/shipping.shipping.special/}<br />'; break;
							}
							if($shipping_row['IsWeightArea']==0 || $shipping_row['IsWeightArea']==2){ //首重或混合计算
							?>
								<span class="price_input"><b>{/shipping.shipping.first_weight/}(<?=$shipping_row['FirstWeight'];?> {/shipping.shipping.unit/}) <?=$c['manage']['currency_symbol'];?><div class="arrow"><em></em><i></i></div></b><input type="text" name="FirstPrice" value="<?=sprintf('%01.3f', $area_row['FirstPrice']);?>" class="form_input" size="6" maxlength="10" rel="amount" /></span>
							<?php
							}
							if($shipping_row['IsWeightArea']==3){ //按数量计算
							?>
								<span class="price_input"><b>{/shipping.shipping.first_weight/}(<?=$shipping_row['FirstMinQty'];?>-<?=$shipping_row['FirstMaxQty'];?>{/global.item/}) <?=$c['manage']['currency_symbol'];?><div class="arrow"><em></em><i></i></div></b><input type="text" name="FirstQtyPrice" value="<?=sprintf('%01.3f', $area_row['FirstQtyPrice']);?>" class="form_input" size="6" maxlength="10" rel="amount" /></span>
								<span class="price_input"><b>{/shipping.shipping.ext_weight/} <?=$c['manage']['currency_symbol'];?><div class="arrow"><em></em><i></i></div></b><input type="text" name="ExtQtyPrice" value="<?=sprintf('%01.3f', $area_row['ExtQtyPrice']);?>" class="form_input" size="6" maxlength="10" rel="amount" /><b class="last">/ <?=$shipping_row['ExtQty'];?>{/global.item/}</b></span>
							<?php }?>
						</span>
						<div class="clear"></div>
					</div>
					<?php 
					if($shipping_row['IsWeightArea']==0 || $shipping_row['IsWeightArea']==2){
						//续重区间
						$ExtWeightArea=str::json_data(htmlspecialchars_decode($shipping_row['ExtWeightArea']), 'decode');
						$ExtWeightAreaPrice=str::json_data(htmlspecialchars_decode($area_row['ExtWeightAreaPrice']), 'decode');
					?>
						<div class="rows">
							<label>{/shipping.shipping.freight/}{/shipping.shipping.ext_weightarea/}</label>
							<span class="input">
								<table cellpadding="2" cellspacing="0" border="0">
									<?php
									foreach($ExtWeightArea as $k=>$v){
										if((float)$ExtWeightArea[$k+1]<=0) break;
									?>
										<tr>
											<td><span class="price_input lang_input"><b><?=$v;?> - <?=(float)$ExtWeightArea[$k+1];?> {/shipping.shipping.unit/} <?=$c['manage']['currency_symbol'];?><div class="arrow"><em></em><i></i></div></b><input type="text" name="ExtWeightAreaPrice[]" value="<?=sprintf('%01.3f', $ExtWeightAreaPrice[$k]);?>" class="form_input" size="6" maxlength="10" rel="amount"><b class="last">/ <?=sprintf('%01.3f', $shipping_row['ExtWeight']);?> {/shipping.shipping.unit/}</b></span><div class=""></div></td>
										</tr>
									<?php }?>
									<tr>
										<td><span class="price_input lang_input"><b><?=@count($ExtWeightArea)?$v:$shipping_row['FirstWeight'];?> {/shipping.shipping.unit/} {/shipping.shipping.over/} <?=$c['manage']['currency_symbol'];?><div class="arrow"><em></em><i></i></div></b><input type="text" name="ExtWeightAreaPrice[]" value="<?=sprintf('%01.3f', $ExtWeightAreaPrice[(@count($ExtWeightArea)?$k:0)]);?>" class="form_input" size="6" maxlength="10" rel="amount"><b class="last">/ <?=sprintf('%01.3f', $shipping_row['ExtWeight']);?> {/shipping.shipping.unit/}</b></span></td>
									</tr>
								</table>
							</span>
							<div class="clear"></div>
						</div>
					<?php }?>
					<?php 
					if($shipping_row['IsWeightArea']==1 || $shipping_row['IsWeightArea']==2 || $shipping_row['IsWeightArea']==4){
						//重量区间
						$WeightArea=str::json_data(htmlspecialchars_decode($shipping_row['WeightArea']), 'decode');
						$WeightAreaPrice=str::json_data(htmlspecialchars_decode($area_row['WeightAreaPrice']), 'decode');
					?>
						<div class="rows">
							<label>{/shipping.shipping.freight/}{/shipping.shipping.weightarea/}</label>
							<span class="input">
								<table cellpadding="2" cellspacing="0" border="0">
									<?php
									foreach($WeightArea as $k=>$v){
										if((float)$WeightArea[$k+1]<=0) break;
									?>
										<tr>
											<td><span class="price_input lang_input"><b><?=$v;?> - <?=(float)$WeightArea[$k+1];?> {/shipping.shipping.unit/} <?=$c['manage']['currency_symbol'];?><div class="arrow"><em></em><i></i></div></b><input type="text" name="WeightAreaPrice[]" value="<?=sprintf('%01.3f', $WeightAreaPrice[$k]);?>" class="form_input" size="6" maxlength="10" rel="amount"><?=$shipping_row['WeightType']==1?'<b class="last">/ {/shipping.shipping.unit/}</b>':'';?></span></td>
										</tr>
									<?php }?>
									<tr>
										<td><span class="price_input lang_input"><b><?=$v;?> {/shipping.shipping.unit/} {/shipping.shipping.over/} <?=$c['manage']['currency_symbol'];?><div class="arrow"><em></em><i></i></div></b><input type="text" name="WeightAreaPrice[]" value="<?=sprintf('%01.3f', $WeightAreaPrice[$k]);?>" class="form_input" size="6" maxlength="10" rel="amount"><?=$shipping_row['WeightType']==1?'<b class="last">/ {/shipping.shipping.unit/}</b>':'';?></span></td>
									</tr>
								</table>
							</span>
							<div class="clear"></div>
						</div>
					<?php }?>
					<?php
					if($shipping_row['IsWeightArea']==4){
						//体积区间
						$VolumeArea=str::json_data(htmlspecialchars_decode($shipping_row['VolumeArea']), 'decode');
						$VolumeAreaPrice=str::json_data(htmlspecialchars_decode($area_row['VolumeAreaPrice']), 'decode');
					?>
						<div class="rows">
							<label>{/shipping.shipping.freight/}{/shipping.shipping.volumearea/}</label>
							<span class="input">
								<table cellpadding="2" cellspacing="0" border="0">
									<?php
									foreach((array)$VolumeArea as $k=>$v){
										if((float)$VolumeArea[$k+1]<=0) break;
									?>
										<tr>
											<td><span class="price_input lang_input"><b><?=$v?> - <?=(float)$VolumeArea[$k+1]?> {/shipping.shipping.volume_unit/} <?=$c['manage']['currency_symbol'];?><div class="arrow"><em></em><i></i></div></b><input type="text" name="VolumeAreaPrice[]" value="<?=sprintf('%01.2f', $VolumeAreaPrice[$k]);?>" class="form_input" size="6" maxlength="6" rel="amount"><b class="last">/ {/shipping.shipping.volume_unit/}</b></span></td>
										</tr>
									<?php }?>
									<tr>
										<td><span class="price_input lang_input"><b><?=$v?> {/shipping.shipping.volume_unit/} {/shipping.shipping.over/} <?=$c['manage']['currency_symbol'];?><div class="arrow"><em></em><i></i></div></b><input type="text" name="VolumeAreaPrice[]" value="<?=sprintf('%01.2f', $VolumeAreaPrice[$k]);?>" class="form_input" size="6" maxlength="6" rel="amount"><b class="last">/ {/shipping.shipping.volume_unit/}</b></span></td>
									</tr>
								</table>
							</span>
							<div class="clear"></div>
						</div>
					<?php }?>
					<div class="rows">
						<label></label>
						<span class="input"><input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" /></span>
						<div class="clear"></div>
					</div>
					<input type="hidden" name="AId" value="<?=$AId;?>" />
					<input type="hidden" name="SId" value="<?=$SId;?>" />
					<input type="hidden" name="OvId" value="<?=$OvId;?>" />
					<input type="hidden" name="do_action" value="set.shipping_express_area_edit" />
				</form>
			</div>
		<?php
		}elseif($c['manage']['page']=='area_country'){
			//添加删除分区的国家
			$shipping_row=str::str_code(db::get_one('shipping', "SId='{$SId}'", 'SId,Express,Logo')); //所属快递公司
			$area_row=str::str_code(db::get_one('shipping_area', "AId='{$AId}'", 'AId,SId,Name')); //所属分区
			!$area_row && js::location("./?m=set&a=shipping&d=express&p=area&SId=$SId");
			/******************** 分隔线 ********************/
			$country_row=str::str_code(db::get_all('country', 'IsUsed=1', 'CId, Country, Continent', 'Country asc')); //所有国家
			$express_country_row=str::str_code(db::get_all('shipping_country', "SId='$SId' and AId in(select AId from shipping_area where SId='{$SId}' and OvId='{$OvId}')", '*', 'CId asc')); //从快递公司的国家中筛选出当前分区的国家
			$range_ary=range('A', 'Z');
			$all_country_ary=$area_country_ary=$added_CId=$area_CId=array();
			foreach($country_row as $k=>$v){
				$initial=mb_strcut($v['Country'], 0, 1);//首字母
				$all_country_ary[$initial][]=array('CId'=>$v['CId'], 'Country'=>$v['Country'], 'Continent'=>$v['Continent']); //用一个新数组记录
			}
			foreach($express_country_row as $k=>$v){
				$added_CId[]=$v['CId'];
				if($v['AId']==$area_row['AId']){
					foreach($country_row as $k2=>$v2){
						if($v['CId']==$v2['CId']){ //找到对应的国家
							$area_CId[]=$v['CId'];
							$area_country_ary[]=array('CId'=>$v2['CId'], 'Country'=>$v2['Country'], 'Continent'=>$v['Continent']); //用一个新数组记录
							continue;
						}
					}
				}
			}
		?>
			<script type="text/javascript">$(function(){set_obj.shipping_express_area_country_edit_init();});</script>
			<div class="shipping_area_edit">
				<div class="shipping_area_title">{/shipping.area.setcountry_to/} [ <?=$area_row['Name'];?> ]</div>
				<table cellpadding="0" cellspacing="0" border="0" width="100%" class="country_list">
					<tr>
						<td width="45%" valign="top">
							<div class="country_title">{/shipping.area.choice/}</div>
							<div class="continent_list continent_left_list">
								<?php
								foreach($c['continent'] as $k=>$v){
								?>
									<a class="fl" href="javascript:;" continent="<?=$k;?>">{/continent.<?=$k;?>/}</a>
								<?php }?>
							</div>
							<div class="initial_list initial_left_list"> 
								<?php foreach($range_ary as $v){?>
									<a class="fl" href="javascript:;" initial="<?=$v;?>"><?=$v;?></a>
								<?php }?>
								<div class="clear"></div>
							</div>
							<div class="btn_anti left_anti"><input type="checkbox" class="input_anti" /> {/global.anti/}</div>
							<div class="country_box" id="left_country">
								<?php
								$temp=array();
								foreach($all_country_ary as $k=>$v){
									echo '<div class="initial initial_'.$k.'"></div>';
									foreach($v as $vv){
										if(in_array($vv['CId'], $added_CId)) continue;//如果已添加到快递公司，无论是否添加到此分区都跳过
								?>
									<div class="item" continent="<?=$vv['Continent'];?>"><input type="checkbox" class="select_cid" initial="<?=$k;?>" /> <?=$vv['Country'];?><input type="hidden" name="CId[]" value="<?=$vv['CId'];?>" /></div>
								<?php
									}
								}?>
							</div>
						</td>
						<td width="10%" align="center"><a class="btn_cut" href="javascript:;" id="left_arrow_img"></a>&nbsp;&nbsp;<br />&nbsp;&nbsp;<a class="btn_add" href="javascript:;" id="right_arrow_img"></a></td>
						<td width="45%" valign="top">
							<div class="country_title">{/shipping.area.added/}</div>
							<div class="initial_list initial_right_list">
								<?php foreach($range_ary as $v){?>
									<a class="fl" href="javascript:;" initial="<?=$v;?>"><?=$v;?></a>
								<?php }?>
							</div>
							<form id="edit_form">
								<div class="btn_anti right_anti"><input type="checkbox" class="input_anti" /> {/global.anti/}</div>
								<div class="country_box" id="right_country">
									<?php
									$temp=array();
									foreach($all_country_ary as $k=>$v){
										echo '<div class="initial initial_'.$k.'"></div>';
										foreach($v as $vv){
											if(!in_array($vv['CId'], $area_CId)) continue;
									?>
										<div class="item"><input type="checkbox" class="select_cid" initial="<?=$k;?>" /> <?=$vv['Country'];?><input type="hidden" name="CId[]" value="<?=$vv['CId'];?>" /></div>
									<?php
										}
									}?>
								</div>
								<input type="submit" class="btn_ok btn_submit" name="submit_button" value="{/global.submit/}" />
								<input type="hidden" name="AId" value="<?=$AId;?>" />
								<input type="hidden" name="SId" value="<?=$SId;?>" />
								<input type="hidden" name="do_action" value="set.shipping_express_area_country_edit" />
							</form>
						</td>
					</tr>
				</table>
			</div>
		<?php }?>
	<?php
	}elseif($c['manage']['do']=='insurance'){
		//保险管理
		$insurance_row=str::str_code(db::get_one('shipping_insurance'));
		$config_row=str::str_code(db::get_one('shipping_config', '1', 'IsInsurance'));
	?>
		<script type="text/javascript">$(function(){set_obj.shipping_insurance_edit_init()});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/global.used/}</label>
				<span class="input">
					<div class="switchery<?=$config_row['IsInsurance']?' checked':'';?>">
						<input type="checkbox" name="IsInsurance" value="1"<?=$config_row['IsInsurance']?' checked':'';?>>
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
				<label>{/shipping.shipping.insurance/}</label>
				<span class="input">
					<?php if($permit_ary['insurance_add']){?>
						<input type="button" value="{/global.add/}{/shipping.shipping.node/}" id="addArea"  class="btn_ok" />
						<div class="blank6"></div>
					<?php }?>
					<table cellpadding="2" cellspacing="0" border="0" id="InsArea" currency="<?=$c['manage']['currency_symbol']?>" tips="{/shipping.shipping.over/}">
						<tr>
							<td nowrap="nowrap">{/shipping.info.order_price/}</td>
							<td nowrap="nowrap">{/shipping.info.insurance_price/}</td>
						</tr>
						<?php
						$AreaPrice=str::json_data(htmlspecialchars_decode($insurance_row['AreaPrice']), 'decode');
						foreach((array)$AreaPrice as $k=>$v){?>
							<tr>
								<td nowrap="nowrap"><span class="price_input"><b><?=$c['manage']['currency_symbol'];?><div class="arrow"><em></em><i></i></div></b><input type="text" name="ProPrice[]" value="<?=$v[0];?>" <?=!$permit_ary['insurance_edit']?'readonly="readonly"':'';?> class="form_input <?=!$permit_ary['insurance_edit']?'readonly':'';?>" size="3" maxlength="5" rel="amount" notnull><b class="last">{/shipping.shipping.over/}</b></span></td>
								<td nowrap="nowrap"><span class="price_input"><b><?=$c['manage']['currency_symbol'];?><div class="arrow"><em></em><i></i></div></b><input type="text" name="AreaPrice[]" value="<?=$v[1];?>" <?=!$permit_ary['insurance_edit']?'readonly="readonly"':'';?> class="form_input <?=!$permit_ary['insurance_edit']?'readonly':'';?>" size="3" maxlength="5" rel="amount" notnull></span><?php if($permit_ary['insurance_del']){?><a href="javascript:;"><img hspace="5" src="/static/ico/del.png"></a><?php }?></td>
							</tr>
						<?php }?>
					</table>
				</span>
				<div class="clear"></div>
			</div>
			<?php if($permit_ary['insurance_edit']){?>
				<div class="rows">
					<label></label>
					<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
					<div class="clear"></div>
				</div>
			<?php }?>
			<input type="hidden" name="do_action" value="set.shipping_insurance_edit" />
		</form>
	<?php
	}elseif($c['manage']['do']=='overseas'){
		//海外仓管理
	?>
		<script type="text/javascript">$(function(){set_obj.shipping_overseas_init();});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<td width="6%" nowrap="nowrap">{/global.serial/}</td>
					<td width="84%" nowrap="nowrap">{/global.name/}</td>
					<?php if($permit_ary['overseas_edit'] || $permit_ary['overseas_del']){?><td width="10%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$shipping_overseas_row=str::str_code(db::get_all('shipping_overseas', '1', '*', $c['my_order'].'OvId asc'));
				foreach($shipping_overseas_row as $k=>$v){
					if((int)$c['manage']['config']['Overseas']==0 && $v['OvId']>1) continue;
				?>
					<tr cid="<?=$v['OvId'];?>" data="<?=htmlspecialchars(str::json_data($v));?>">
						<td nowrap="nowrap"><?=$k+1;?></td>
						<td nowrap="nowrap"><?=$v['Name'.$c['manage']['web_lang']];?></td>
						<?php if($permit_ary['overseas_edit'] || $permit_ary['overseas_del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico edit" href="javascript:;" label="{/global.edit/}" data-id="<?=$v['OvId'];?>"><img src="/static/ico/edit.png" alt="{/global.edit/}" /></a><?php }?>
								<?php if($v['OvId']>1 && $permit_ary['overseas_del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=set.shipping_overseas_del&OvId=<?=$v['OvId'];?>" label="{/global.del/}"><img src="/static/ico/del.png" alt="{/global.del/}" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<?php /***************************** 发货地编辑 Start *****************************/?>
		<div class="pop_form box_overseas_edit">
			<form id="edit_form">
				<div class="t"><h1><span></span>{/module.set.shipping.overseas/}</h1><h2>×</h2></div>
				<div class="r_con_form">
					<div class="rows">
						<label>{/global.name/}</label>
						<span class="input"><?=manage::form_edit('', 'text', 'Name', 53, 150, 'notnull');?></span>
						<div class="clear"></div>
					</div>
					<input type="hidden" name="OvId" value="0" />
					<input type="hidden" name="do_action" value="set.shipping_overseas_edit" />
				</div>
				<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
			</form>
		</div>
		<?php /***************************** 发货地编辑 End *****************************/?>
	<?php }?>
</div>