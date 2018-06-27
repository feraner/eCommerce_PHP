<?php !isset($c) && exit();?>
<?php
manage::check_permit('sales', 1, array('a'=>'discount'));//检查权限

$discount=db::get_value('config', "GroupId='cart' and Variable='discount'", 'Value');
$value=str::json_data(htmlspecialchars_decode($discount), 'decode');
?>
<div class="r_nav">
	<h1>{/module.sales.discount/}</h1>
</div>
<div id="coupon" class="r_con_wrap">
    <?=ly200::load_static('/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');?>
	<script language="javascript">$(function(){sales_obj.discount_init()});</script>
	<form id="discount_form" class="r_con_form">
		<div class="rows">
			<label>{/global.turn_on/}</label>
			<span class="input">
				<div class="switchery<?=(int)$value['IsUsed']?' checked':'';?>">
					<input type="checkbox" name="IsUsed" value="1"<?=(int)$value['IsUsed']?' checked':'';?>>
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
			<label>{/global.type/}</label>
			<span class="input">
				<input type="radio" name="Type" value="0" <?=$value['Type']==0?'checked="checked"':'';?> /> {/sales.coupon.discount/}
				<input type="radio" name="Type" value="1" <?=$value['Type']==1?'checked="checked"':'';?> /> {/sales.coupon.over_money/}
			</span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>&nbsp;</label>
			<span class="input">
				<table border="0" cellspacing="0" cellpadding="3" id="discount_list" class="item_data_table">
					<tbody>
						<tr>
							<td><input type="button" id="add_discount" class="btn_ok" value="{/global.add/}"></td>
						</tr>
						<?php
						$i=0;
						foreach((array)$value['Data'] as $k=>$v){
						?>
						<tr>
							<td>
								{/sales.coupon.condition/}:<span class="price_input"><b><?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="UseCondition[]" value="<?=$k?$k:0;?>" type="text" class="form_input" maxlength="10" size="5"></span><?php if(!$i){?><span class="tool_tips_ico" content="{/sales.discount.condition_tips/}"></span><?php }?>
								<span class="discount <?=$value['Type']==0?'':'none';?>">{/sales.coupon.discount/}:<span class="price_input"><input name="Discount[]" value="<?=$v[0]?$v[0]:100;?>" type="text" class="form_input" maxlength="3" size="5"><b class="last">%</b></span><?php if(!$i){?><span class="tool_tips_ico" content="{/sales.coupon.discount_tips/}"></span><?php }?></span>
								<span class="money <?=$value['Type']==1?'':'none';?>">{/sales.coupon.money/}:<span class="price_input"><b><?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="Money[]" value="<?=$v[1]?$v[1]:0;?>" type="text" class="form_input" maxlength="5" size="5"></span><?php if(!$i){?><span class="tool_tips_ico" content="{/sales.coupon.money_tips/}"></span><?php }?></span>
								<?php if($i){?>&nbsp;<a class="d_del" href="javascript:;"><img src="/static/ico/del.png" hspace="5" /></a></td><?php }?>
						</tr>
						<?php
							++$i;
						}?>
					</tbody>
				</table>
			</span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/sales.coupon.deadline/}</label>
			<span class="input">
				<input name="DeadLine" value="<?=($value['StartTime'] && $value['EndTime'])?date('Y-m-d H:i:s', $value['StartTime']).'/'.date('Y-m-d H:i:s', $value['EndTime']):'';?>" type="text" class="form_input" size="42" readonly notnull> 
				<font class="fc_red">*</font>
			</span>
			<div class="clear"></div>
		</div>      
		<div class="rows">
			<label></label>
			<span class="input">
				<input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" />
			</span>
			<div class="clear"></div>
		</div>
		<input type="hidden" name="do_action" value="sales.discount">
	</form>
</div>