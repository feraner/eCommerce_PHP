<?php !isset($c) && exit();?>
<?php
//购物车产品
$where='c.'.$c['where']['cart'];
if($_GET['CId']) $where.=' and c.CId in('.str_replace('.', ',', $_GET['CId']).')';
$cart_row=db::get_all('shopping_cart c left join products p on c.ProId=p.ProId', $where, "c.*, c.Attr as CartAttr, p.Name{$c['lang']}, p.Prefix, p.Number, p.AttrId, p.Attr, p.IsCombination", 'c.CId desc');
!count($cart_row) && js::location('/cart/', '', '.top');

//产品总价、总数量
$total_price=db::get_sum('shopping_cart', $c['where']['cart'].($_GET['CId']?' and CId in('.str_replace('.', ',', $_GET['CId']).')':''), '(Price+PropertyPrice)*Discount/100*Qty');
$iconv_total_price=cart::cart_total_price(($_GET['CId']?' and CId in('.str_replace('.', ',', $_GET['CId']).')':''), 1);

//检查产品资料是否完整
$oversea_id_ary=array();
$products_attribute_error=0;
foreach((array)$cart_row as $v){
	if($v['BuyType']!=4 && $v['CartAttr']!='[]' && $v['CartAttr']!='{}' && $v['CartAttr']!='{"Overseas":"Ov:1"}'){
		$IsError=0;
		$prod_selected_ary=$ext_ary=array();
		$AttrAry=@str::json_data(str::attr_decode($v['CartAttr']), 'decode');
		$prod_selected_row=db::get_all('products_selected_attribute', "ProId='{$v['ProId']}' and VId>0 and IsUsed=1");
		foreach((array)$prod_selected_row as $v2){
			$prod_selected_ary[]=$v2['VId'];
		}
		if((int)$v['IsCombination'] && db::get_row_count('products_selected_attribute_combination', "ProId='{$v['ProId']}'")){//开启规格组合
			$OvId=1;
			foreach($AttrAry as $k2=>$v2){
				if($k2=='Overseas'){//发货地
					$OvId=str_replace('Ov:', '', $v2);
					(int)$c['config']['global']['Overseas']==0 && $OvId!=1 && $OvId=0;//关闭海外仓功能，发货地不是China，不能购买
				}else{
					!in_array($v2, $prod_selected_ary) && $IsError=1;
					$ext_ary[]=$v2;
				}
			}
			sort($ext_ary); //从小到大排序
			$Combination='|'.implode('|', $ext_ary).'|';
			$row=str::str_code(db::get_one('products_selected_attribute_combination', "ProId='{$v['ProId']}' and Combination='{$Combination}' and OvId='{$OvId}'"));
		}else{
			foreach((array)$AttrAry as $k2=>$v2){
				if($k2=='Overseas') continue;
				$row=str::str_code(db::get_one('products_selected_attribute_combination', "ProId='{$v['ProId']}' and Combination='|{$v2}|' and OvId=1"));
				$row && $PropertyPrice+=(float)$row['Price'];//固定是加价
			}
			if($v['BuyType']==3 && !$AttrAry) $row=1;//放过组合购买
		}
		if(!$row || $IsError>0) $products_attribute_error=1;//检查此产品是否有选择购物车属性
	}
	!in_array($v['OvId'], $oversea_id_ary) && $oversea_id_ary[]=$v['OvId'];
}
sort($oversea_id_ary); //排列正序
$OvId_where='a.OvId in('.implode(',', $oversea_id_ary).')';

if((int)$_SESSION['User']['UserId']){ //会员收货地址信息
	//收货地址
	$address_row=str::str_code(db::get_one('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', 'a.'.$c['where']['cart']." and a.IsBillingAddress=0".($_SESSION['Cart']['ShippingAddressAId']?" and a.AId='{$_SESSION['Cart']['ShippingAddressAId']}'":''), 'a.*, c.Country, s.States as StateName', 'a.AccTime desc, a.AId desc'));
}elseif($_SESSION['Cart']['ShippingAddress']){ //非会员收货地址信息
	$address_ary=$_SESSION['Cart']['ShippingAddress'];
	$country_val=str::str_code(db::get_value('country', "CId='{$address_ary['CId']}'", 'Country'));
	$states_val=str::str_code(db::get_value('country_states', "SId='{$address_ary['SId']}'", 'States'));
	if($country_val || $states_val){
		$address_ary['Country']=$country_val;
		$address_ary['StateName']=$states_val;
	}
	$address_row=$address_ary;
	unset($address_ary);
}
//付款方式
$payment_row=db::get_all('payment', "IsUsed=1 and PId!=2");

$IsInsurance=str::str_code(db::get_value('shipping_config', '1', 'IsInsurance'));

$total_weight=db::get_sum('shopping_cart', $c['where']['cart'].($_GET['CId']?' and CId in('.str_replace('.', ',', $_GET['CId']).')':''), 'Weight*Qty');
$total_quantity=db::get_sum('shopping_cart', $c['where']['cart'].($_GET['CId']?' and CId in('.str_replace('.', ',', $_GET['CId']).')':''), 'Qty');

//会员优惠价 与 全场满减价 比较
$AfterPrice_0=$AfterPrice_1=0;
$user_discount=0;
if((int)$_SESSION['User']['UserId'] && (int)$_SESSION['User']['Level']){
	$user_discount=(float)db::get_value('user_level', "LId='{$_SESSION['User']['Level']}' and IsUsed=1", 'Discount');
	$user_discount=($user_discount>0 && $user_discount<100)?$user_discount:100;
	$AfterPrice_0=$iconv_total_price-($iconv_total_price*($user_discount/100));
}
if($cutArr['IsUsed']==1 && $c['time']>=$cutArr['StartTime'] && $c['time']<=$cutArr['EndTime']){
	foreach((array)$cutArr['Data'] as $k=>$v){
		if($total_price<$k) break;
		$AfterPrice_1=($cutArr['Type']==1?cart::iconv_price($v[1], 2, '', 0):($iconv_total_price*(100-$v[0])/100));
	}
}
if($AfterPrice_0==$AfterPrice_1){//当会员优惠价和全场满减价一致，默认只保留会员优惠价
	$AfterPrice_1=0;
}

$country_row=str::str_code(db::get_all('country', "IsUsed='1'", 'CId, Country, Acronym, FlagPath, IsDefault', 'Country asc'));
foreach($country_row as $v){
	if($v['IsDefault']){ $CId=$v['CId']; break; }
}
$shipto_country_ary=array();
$shipping_country_row=db::get_all('shipping_area a left join shipping_country c on a.AId=c.AId', $OvId_where.' Group By c.CId', 'c.CId');
foreach($shipping_country_row as $v){ $shipto_country_ary[]=$v['CId']; }

if((int)$c['config']['global']['Overseas']==0 || count($c['config']['Overseas'])<2 || count($oversea_id_ary)<2){//关闭海外仓功能 或者 仅有一个海外仓选项
?>
	<style>#shippingObj .txt .oversea:first-child .shipping_title{display:none;}</style>
<?php }?>
<script type="text/javascript">$(function(){cart_obj.cart_checkout()});</script>
<div id="cart">
	<?=html::mobile_cart_step(0);?>
    <div class="cart_checkout">
		<form id="PlaceOrderFrom" name="paypal_excheckout" method="post" action="/payment/paypal_excheckout/do_payment/?utm_nooverride=1" target="_blank" amountPrice="<?=$iconv_total_price;?>" userPrice="<?=(($AfterPrice_0 && !$AfterPrice_1) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_0>$AfterPrice_1))?$AfterPrice_0:0;?>"><?php /*?><?php */?>
        <div class="checkout_box">
			<div class="title"><?=$c['lang_pack']['mobile']['select_country'];?>: </div>
			<div class="txt">
				<div class="box_select" id="select_country">
					<select name="CId">
						<option value=""><?=$c['lang_pack']['mobile']['plz_country'];?></option>
						<?php
						foreach($country_row as $v){
							if(!in_array($v['CId'], $shipto_country_ary)) continue;//所有快递方式都没有的国家，给过滤掉
						?>
							<option value="<?=$v['CId'];?>"<?=$v['IsDefault']?' selected':'';?>><?=$v['Country'];?></option>
						<?php }?>
					</select>
				</div>
			</div>
		</div>
		<div class="checkout_divide"></div>
        <div class="checkout_box" id="shippingObj">
            <div class="title"><?=$c['lang_pack']['mobile']['ship_and_deli'];?></div>
			<div class="txt">
				<?php
				$oversea_count=count($oversea_id_ary);
				foreach($oversea_id_ary as $k=>$v){
				?>
					<div class="oversea" data-id="<?=$v;?>">
						<div class="shipping_title ui_border_b"><?=$c['config']['Overseas'][$v]['Name'.$c['lang']];?></div>
						<div class="shipping_list"></div>
						<?php if($IsInsurance){?>
							<div class="insurance_txt">
								<input type="checkbox" name="_shipping_insurance" class="_shipping_insurance" value="1" checked="checked" /><label for="_shipping_insurance"><?=$c['lang_pack']['mobile']['insur_tips'];?> (+ <?=$_SESSION['Currency']['Symbol'];?><span>0</span>)</label>
							</div>
						<?php }?>
					</div>
				<?php }?>
			</div>
        </div>
		<?php if($c['FunVersion']>=1){?>
			<div class="checkout_divide"></div>
			<div class="checkout_box new-coupon">
				<div class="title"><?=$c['lang_pack']['mobile']['coupon_code'];?></div>
				<div class="txt">
					<div class="clean code_input">
						<input type="text" name="couponCode" class="box_input fl" placeholder="<?=$c['lang_pack']['mobile']['apply'].' '.$c['lang_pack']['mobile']['coupon_code'];?>"><div class="btn_global btn_submit fl FontBgColor" id="coupon_apply"><?=$c['lang_pack']['submit'];?></div>
					</div>
					<div class="code_valid" id="code_valid" style="display:none;">
						<?=$c['lang_pack']['mobile']['coupon_txt'];?><br />
						<div class="valid_ex">
							<?=$c['lang_pack']['mobile']['discount_txt'];?> <strong></strong><br />
							(<?=$c['lang_pack']['mobile']['exp_date'];?>: <strong></strong>)
						</div>
						<a href="javascript:;" id="removeCoupon"><?=$c['lang_pack']['mobile']['remove'];?></a>
					</div>
				</div>
			</div>
		<?php }?>
			<div class="checkout_summary">
				<div class="clean"><!-- subtotal -->
					<div class="key"><?=$c['lang_pack']['mobile']['subtotal'].' ('.str_replace('%num%', $total_quantity, $c['lang_pack']['cart'][($total_quantity>1?'itemsCount':'itemCount')]).')';?>:</div>
					<div class="value"><?=$_SESSION['Currency']['Symbol'];?><span id="ot_subtotal"><?=cart::currency_format($iconv_total_price, 0, $_SESSION['Currency']['Currency']);?></span></div>
				</div>
				<?php if(($AfterPrice_0 && !$AfterPrice_1) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_0>$AfterPrice_1)){?>
					<div class="clean" id="memberSavings"><!-- Member Savings -->
						<div class="key">(-) <?=$c['lang_pack']['cart']['user_save'];?></div>
						<div class="value"><?=$_SESSION['Currency']['Symbol'];?><span id="ot_user"><?=cart::currency_format($AfterPrice_0, 0, $_SESSION['Currency']['Currency']);?></span></div>
					</div>
				<?php }?>
				<div class="clean" id="shipping_charges" style="display:none;"><!-- Shipping Charges -->
					<div class="key">(+) <?=$c['lang_pack']['mobile']['ship_charge'];?>:</div>
					<div class="value"><?=$_SESSION['Currency']['Symbol'];?><span id="ot_shipping">0</span></div>
				</div>
				<div class="clean" id="shipping_and_insurance"><!-- Shipping Insurance combine -->
					<div class="key">(+) <?=$c['lang_pack']['mobile']['ship_ins_txt'];?>:</div>
					<div class="value"><?=$_SESSION['Currency']['Symbol'];?><span id="ot_combine_shippnig_insurance">0</span></div>
				</div>
				<div class="clean" style="display:none;" id="couponSavings"><!-- Coupon Savings -->
					<div class="key">(-) <?=$c['lang_pack']['mobile']['coupon_save'];?>:</div>
					<div class="value"><?=$_SESSION['Currency']['Symbol'];?><span id="ot_coupon">0</span></div>
				</div>
				<?php 
				$cutprice=0;
				if(($AfterPrice_1 && !$AfterPrice_0) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_1>$AfterPrice_0)){
				?>
					<div class="clean" id="subtotalDiscount"><!-- subtotal discount -->
						<div class="key">(-) <?=$c['lang_pack']['cart']['save'];?>:</div>
						<div class="value"><?=$_SESSION['Currency']['Symbol'];?><span id="ot_subtotal_discount"><?=cart::currency_format($AfterPrice_1, 0, $_SESSION['Currency']['Currency']);?></span></div>
					</div>
				<?php }?>
				<div class="clean" style="display:none;" id="serviceCharge"><!-- Service Charge -->
					<div class="key">(+) <?=$c['lang_pack']['cart']['fee'];?>:</div>
					<div class="value"><?=$_SESSION['Currency']['Symbol'];?><span id="ot_fee">0</span></div>
				</div>
				<div class="clean" id="total"><!-- Total -->
					<div class="key"><?=$c['lang_pack']['mobile']['grand_total'];?>:</div>
					<div class="value"><?=$_SESSION['Currency']['Symbol'];?><span id="ot_total"></span></div>
				</div>
			</div>
			<div class="checkout_button">
				<div class="btn_global btn BuyNowBgColor" id="paypal_checkout"><?=$c['lang_pack']['mobile']['checkout'];?></div>
			</div>
            <input type="hidden" name="order_coupon_code" value="<?=$_SESSION['Cart']['Coupon'];?>" cutprice="0" />
			<input type="hidden" name="order_discount_price" value="<?=$AfterPrice_1;?>" />
			<input type="hidden" name="order_products_attribute_error" value="<?=(int)$products_attribute_error;?>" />
			<!-- 提交数据 Start -->
			<input type="hidden" name="ShippingMethodType" value="[]" />
			<input type="hidden" name="ShippingPrice" value="[]" />
			<input type="hidden" name="ShippingInsurancePrice" value="[]" />
			<input type="hidden" name="ShippingExpress" value="[]" />
			<input type="hidden" name="ShippingInsurance" value="[]" />
			<input type="hidden" name="SId" value="[]" />
			<!-- 提交数据 End -->
			<!-- 防止JSON格式报错 Start -->
			<input type="hidden" name="order_shipping_method_sid" value="[]" />
            <input type="hidden" name="order_shipping_method_type" value="[]" />
            <input type="hidden" name="order_shipping_price" value="[]" />
            <input type="hidden" name="order_shipping_insurance" value="[]" price="[]" />
			<input type="hidden" name="order_shipping_oversea" value="<?=$oversea_id_ary?implode(',', $oversea_id_ary):'';?>" />
			<!-- 防止JSON格式报错 End -->
			<input type="hidden" name="CartCId" value="<?=$_GET['CId']?str_replace('.', ',', $_GET['CId']):'';?>" />
        </form> 
    </div>
</div>
<script type="text/javascript">
var address_count=1;
var address_perfect=0;
</script>
