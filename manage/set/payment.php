<?php !isset($c) && exit();?>
<?php
manage::check_permit('set', 1, array('a'=>'payment'));//检查权限

$permit_ary['edit']=manage::check_permit('set', 0, array('a'=>'payment', 'd'=>'edit'));
?>
<div class="r_nav">
	<h1>{/module.set.payment/}</h1>
</div>
<div id="payment" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
	?>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<td width="5%" nowrap="nowrap">{/global.serial/}</td>
					<td width="20%" nowrap="nowrap">{/set.payment.method/}</td>
					<td width="20%" nowrap="nowrap">{/global.name/}</td>
					<td width="15%" nowrap="nowrap">{/global.logo/}</td>
					<td width="10%" nowrap="nowrap">{/set.payment.addfee/}</td>
					<td width="10%" nowrap="nowrap">{/set.payment.online/}</td>
					<td width="10%" nowrap="nowrap">{/global.turn_on/}</td>
                    <td width="9%" nowrap="nowrap">{/global.my_order/}</td>
					<?php if($permit_ary['edit']){?>
						<td width="10%" nowrap="nowrap">{/global.operation/}</td>
					<?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$payment_plugins=array();
				$rows=db::get_all('plugins', "Category='payment' and IsUsed", 'ClassName');
				foreach($rows as $v){$payment_plugins[]=strtolower($v['ClassName']);}
				
				$payment_row=str::str_code(db::get_all('payment', 1, '*', $c['my_order'].'IsUsed desc, IsOnline desc, PId asc'));
				$i=1;
				foreach($payment_row as $k=>$v){
					$method_path=@strtolower($v['Method']);
					$method_path=='excheckout' && $method_path='paypal_excheckout';
					@substr_count($method_path, 'payssion') && $method_path='payssion_'.substr($method_path, 8);
					if($v['IsOnline'] && !in_array($method_path, $payment_plugins)) continue;

					$pic=@is_file($c['root_path'].$v['LogoPath'])?$v['LogoPath']:'';
					$method=strtolower($v['Method']);
					substr_count($method, 'globebill') && $method='globebill';
					substr_count($method, 'payssion') && $method='payssion';
					$url=$v['Url']?$v['Url']:'javascript:;';
				?>
					<tr>
						<td nowrap="nowrap"><?=$k+1;?></td>
						<td nowrap="nowrap">
							<?=($v['Method']=='Excheckout'?'Paypal ':'').$v['Method'];?>
							<?php if((int)$v['IsOnline']){?><span class="tool_tips_ico" content="{/set.payment.tips.<?=$method;?>/}"></span><?php }?>
						</td>
						<td nowrap="nowrap"><a href="<?=$url;?>" target="_blank" title="<?=$v['Url'];?>"><?=$v['Name'.$c['manage']['web_lang']];?></a></td>
						<td class="img"><?php if($pic){?><img src="<?=$pic;?>" <?=img::img_width_height($pic, 200, 100);?> /><?php }?></td>
						<td nowrap="nowrap"><?=$v['AdditionalFee'];?>%</td>
						<td nowrap="nowrap"><?=$c['manage']['lang_pack']['global']['n_y'][$v['IsOnline']];?></td>
						<td nowrap="nowrap"><?=$c['manage']['lang_pack']['global']['n_y'][$v['IsUsed']];?></td>
                        <td nowrap="nowrap"><?=$c['manage']['my_order'][$v['MyOrder']];?></td>
						<?php if($permit_ary['edit']){?>
							<td nowrap="nowrap"><a class="tip_ico tip_min_ico" href="./?m=set&a=payment&d=edit&PId=<?=$v['PId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a></td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
	<?php
	}else{
		$PId=(int)$_GET['PId'];
		$payment_row=str::str_code(db::get_one('payment', "PId='$PId'"));
		!$payment_row && js::location('./?m=set&a=payment');
	?>
		<?=ly200::load_static('/static/js/plugin/operamasks/operamasks-ui.css', '/static/js/plugin/operamasks/operamasks-ui.min.js', '/static/js/plugin/ckeditor/ckeditor.js');?>
		<script type="text/javascript">$(document).ready(function(){set_obj.payment_edit_init();});</script>
		<form id="edit_form" class="r_con_form">
			<h3 class="rows_hd">{/set.payment.basic_info/}</h3>
			<div class="rows">
				<label>{/global.name/}</label>
				<span class="input"><?=manage::form_edit($payment_row, 'text', 'Name', 30, 40, 'notnull');?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/global.pic/}</label>
				<span class="input upload_file upload_logo">
					<?php if($permit_ary['edit']){?>
						<div class="img">
							<div id="LogoDetail" class="upload_box preview_pic"><input type="button" id="LogoUpload" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="" /></div>
							<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '128*41');?>
						</div>
						<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
						<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
					<?php
					}else{
						if(is_file($c['root_path'].$payment_row['LogoPath'])) echo '<img src="'.$payment_row['LogoPath'].'" />';
					}
					?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/global.used/}</label>
				<span class="input">
					<div class="switchery<?=$payment_row['IsUsed']?' checked':'';?>">
						<input type="checkbox" name="IsUsed" value="1"<?=$payment_row['IsUsed']?' checked':'';?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
					<span class="tool_tips_ico" content="{/set.payment.used_notes/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<?php if(in_array($payment_row['Method'], array('Paypal', 'Excheckout'))){?>
				<div class="rows">
					<label>{/set.payment.credit_card_payment/}</label>
					<span class="input">
						<div class="switchery<?=$payment_row['IsCreditCard']?' checked':'';?>">
							<input type="checkbox" name="IsCreditCard" value="1"<?=$payment_row['IsCreditCard']?' checked':'';?>>
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
					</span>
					<div class="clear"></div>
				</div>
			<?php }?>
			<div class="rows">
				<label>{/set.payment.limit/}</label>
				<span class="input">
					<span class="price_input"><b><?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input type="text" name="MinPrice" id="MinPrice" value="<?=(float)$payment_row['MinPrice'];?>" class="form_input" size="4" maxlength="10" rel="amount"></span>&nbsp;&nbsp;~&nbsp;&nbsp;
					<span class="price_input"><b><?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input type="text" name="MaxPrice" id="MaxPrice" value="<?=(float)$payment_row['MaxPrice'];?>" class="form_input" size="4" maxlength="10" rel="amount"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/set.payment.addfee/}</label>
				<span class="input"><input name="AdditionalFee" value="<?=$payment_row['AdditionalFee'];?>" type="text" class="form_input" size="5" maxlength="5" /> %</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/shipping.area.additional/}</label>
				<span class="input">
					<span class="price_input"><b><?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input type="text" name="AffixPrice" value="<?=sprintf('%01.2f', $payment_row['AffixPrice']);?>" class="form_input" size="6" maxlength="10" notnull=""></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/set.payment.account_info/}</label>
				<span class="input">
					<?php 
					$attr_ary=str::json_data(htmlspecialchars_decode($payment_row['Attribute']), 'decode');
					foreach((array)$attr_ary as $k=>$v){
					?>
						<span class="price_input lang_input"><b><?=$k;?><div class='arrow'><em></em><i></i></div></b><input type="<?=strtolower($k)=='password' ? 'password' : 'text';?>" class="form_input" name="Value[]" value="<?=$v;?>" size="40" /><input type="hidden" name="Name[]" value="<?=$k;?>" /></span>
						<div class="blank9"></div>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.products.other/}</label>
				<span class="input">
					{/products.myorder/}:<?=ly200::form_select($c['manage']['my_order'], 'MyOrder', $payment_row['MyOrder']);?><span class="tool_tips_ico" content="{/set.payment.myorder_notes/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows_hd_blank"></div>
			<h3 class="rows_hd">{/set.payment.description/}</h3>
			<div class="rows tab_box">
				<label>{/set.payment.description/}</label>
				<span class="input">
					<?=manage::html_tab_button();?>
					<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
						<div class="tab_txt tab_txt_<?=$k;?>"><?=manage::Editor_Simple("Description_{$v}", $payment_row["Description_{$v}"]);?></div>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<?php if($permit_ary['edit']){?>
						<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<?php }?>
					<a href="./?m=set&a=payment" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
            <input type="hidden" name="LogoPath" value="<?=$payment_row['LogoPath'];?>" save="<?=is_file($c['root_path'].$payment_row['LogoPath'])?1:0;?>" />
			<input type="hidden" id="PId" name="PId" value="<?=$payment_row['PId'];?>" />
			<input type="hidden" name="do_action" value="set.payment_edit" />
		</form>
    <?php } ?>
</div>