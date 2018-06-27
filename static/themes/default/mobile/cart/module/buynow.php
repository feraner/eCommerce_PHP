<?php !isset($c) && exit();?>
<?php
$Data=array();
$data_ary=explode('&', rawurldecode(base64_decode($_GET['Data'])));//Buy Now参数，已通过加密
foreach($data_ary as $v){
	$arr=explode('=', $v);
	$Data[$arr[0]]=$arr[1];
}	

$CId=(int)$Data['CId'];

//购物车产品
$where=$c['where']['cart'];
$ext_where='c.'.$c['where']['cart'];
if($CId){
	$where.=" and CId='{$CId}'";
	$ext_where.=" and c.CId='{$CId}'";
}
$cart_row=db::get_all('shopping_cart c left join products p on c.ProId=p.ProId', $ext_where, "c.*, c.Attr as CartAttr, p.Name{$c['lang']}, p.Prefix, p.Number, p.AttrId, p.Attr, p.IsCombination", 'c.CId desc');
(!$CId || !count($cart_row)) && js::location('/cart/', '', '.top');
//产品总金额
$total_price=$iconv_total_price=0;
$total_price=db::get_sum('shopping_cart', $c['where']['cart'].($CId?' and CId in('.str_replace('.', ',', $CId).')':''), '(Price+PropertyPrice)*Discount/100*Qty');
$iconv_total_price=cart::cart_total_price(($CId?' and CId in('.str_replace('.', ',', $CId).')':''), 1);

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
$payment_row=db::get_all('payment', "IsUsed=1 and PId!=2", '*', $c['my_order'].'IsOnline desc,PId asc');

$IsInsurance=str::str_code(db::get_value('shipping_config', '1', 'IsInsurance'));

$total_weight=$total_quantity=0;
$total_quantity=db::get_sum('shopping_cart', $c['where']['cart'].($CId?' and CId in('.str_replace('.', ',', $CId).')':''), 'Qty');

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

if((int)$c['config']['global']['Overseas']==0 || count($c['config']['Overseas'])<2 || count($oversea_id_ary)<2){//关闭海外仓功能 或者 仅有一个海外仓选项
?>
	<style>#shippingObj .txt .oversea:first-child .shipping_title{display:none;}</style>
<?php 
}
$oversea_id_hidden=$oversea_id_ary;
$NotDefualtOvId=0;
if(!in_array(1, $oversea_id_ary)){//购物车没有默认海外仓追加隐藏选项
	$oversea_id_ary[]=1;
	$NotDefualtOvId=1;
}
sort($oversea_id_ary); //排列正序
?>
<script type="text/javascript">
if(!$('html').loginOrVisitors()){ //跳转到登录页面
	window.top.location.href='/account/login.html?&jumpUrl='+decodeURIComponent('<?=$_SERVER['REQUEST_URI'];?>');
}
$(function(){
	cart_obj.cart_checkout();
	<?php if($_SESSION['Cart']['ShippingAddress']){?>
		cart_obj.checkout_no_login(<?=str::json_data($_SESSION['Cart']['ShippingAddress']);?>);
	<?php }?>
});
</script>
<div id="cart">
	<?=html::mobile_cart_step(0);?>
    <div class="cart_checkout">
        <div class="checkout_box">
            <div class="title ui_border_b"><?=$c['lang_pack']['mobile']['ship_addr'];?></div>
            <div class="txt" id="address_list">
            	<?php
				if($address_row){
				?>
					<div class="address_row" data-aid="<?=$address_row['AId'];?>" data-cid="<?=$address_row['CId'];?>">
						<strong><?=$address_row['FirstName'].' '.$address_row['LastName'];?> (<?=($address_row['Email']?$address_row['Email'].', ':'').$address_row['Country'];?>)</strong>
						<p><?=($address_row['StateName']?$address_row['StateName']:$address_row['State']).', '.$address_row['City'].', '.$address_row['AddressLine1'].' '.($address_row['AddressLine2']?$address_row['AddressLine2'].' ':'').($address_row['ZipCode']?', '.$address_row['ZipCode']:'');?></p>
						<p><?=$address_row['CountryCode'].' '.$address_row['PhoneNumber'];?></p>
					</div>
					<div class="address_btn">
						<a href="/<?=(int)$_SESSION['User']['UserId']?'account':'cart';?>/address/?Shipping=1" class="btn_global btn change"><?=$c['lang_pack']['mobile']['change_ship'];?></a>
						<a href="/<?=(int)$_SESSION['User']['UserId']?'account':'cart';?>/address/?Form=1" class="btn_global btn add_address FontBgColor FontBorderColor"><em>+</em><?=$c['lang_pack']['mobile']['add_new_addr'];?></a>
					</div>
				<?php }else{?>
					<div class="address_btn">
						<a href="/cart/address/?Form=1&Shipping=1" class="btn_global btn add_address FontBgColor FontBorderColor"><em>+</em><?=$c['lang_pack']['mobile']['add_new_addr'];?></a>
					</div>
				<?php }?>
            </div>
        </div>
		<div class="checkout_divide"></div>
        <div class="checkout_box" id="shippingObj">
            <div class="title ui_border_b"><?=$c['lang_pack']['mobile']['ship_and_deli'];?></div>
            <div class="txt">
				<?php
				$oversea_count=count($oversea_id_ary);
				foreach($oversea_id_ary as $k=>$v){
				?>
					<div class="oversea<?=($NotDefualtOvId && (int)$v==1)?' hide':'';?>" data-id="<?=$v;?>">
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
		<div class="checkout_divide"></div>
        <div class="checkout_box" id="paymentObj">
            <div class="title ui_border_b"><?=$c['lang_pack']['mobile']['pay_method'];?></div>
            <div class="txt">
            	<?php
                	foreach((array)$payment_row as $v){
						$name=$v['Name'.$c['lang']]?$v['Name'.$c['lang']]:$v['Name_en'];
				?>
                <div class="payment_row ui_border_b" min="<?=cart::iconv_price($v['MinPrice'], 2, '', 0);?>" max="<?=cart::iconv_price($v['MaxPrice'], 2, '', 0);?>" pid="<?=$v['PId'];?>">
                	<div class="icon"><i class="FontBgColor"></i></div>
                	<div class="img"><img src="<?=$v['LogoPath'];?>" alt="<?=$v['Name'.$c['lang']];?>" /><span></span></div>
                    <div class="name"><?=$name;?></div>
                    <div class="clear"></div>
                    <div fee="<?=$v['AdditionalFee'];?>" affix="<?=cart::iconv_price($v['AffixPrice'], 2, '', 0);?>" class="payment_contents" style="display:none;">
                    	<?php if($v['Description'.$c['lang']]){?><div class="desc"><?=$v['Description'.$c['lang']]?></div><?php }?>
                    </div>
                </div>
                <?php }?>
            </div>
        </div>
		<div class="checkout_divide"></div>
        <?php if($c['FunVersion']>=1){?>
			<div class="checkout_box new-coupon">
				<div class="title ui_border_b"><?=$c['lang_pack']['mobile']['coupon_code'];?></div>
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
			<div class="checkout_divide"></div>
		<?php }?>
        <div class="checkout_box">
            <div class="title ui_border_b"><?=$c['lang_pack']['mobile']['order_summary'];?></div>
            <div class="txt">
				<div class="cart_item_list">
					<?php
					$ptotal=0;
					$cart_attr=$cart_attr_data=array();
					foreach($cart_row as $v){
						$attr=array();
						$v['Property']!='' && $attr=str::json_data(str::attr_decode($v['Property']), 'decode');
						!$attr && $attr=str::json_data(htmlspecialchars_decode($v['Property']), 'decode');
						$price=$v['Price']+$v['PropertyPrice'];
						$v['Discount']<100 && $price*=$v['Discount']/100;
						$img=ly200::get_size_img($v['PicPath'], '240x240');
						$url=ly200::get_url($v, 'products');
						//$total_quantity+=$v['Qty'];
						$total_weight+=($v['Weight']*$v['Qty']);
					?>
					<div class="item clean ui_border_b">
						<div class="img fl"><a href="<?=$url;?>"><img src="<?=$img;?>" alt="<?=$v['Name'.$c['lang']];?>"></a></div>
						<div class="info">
							<div class="name"><a href="<?=$url;?>"><?=$v['Name'.$c['lang']];?></a></div>
							<?php
							if(count($attr)){
								foreach($attr as $k=>$z){
									if($k=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
									echo '<div class="rows clean">'.($k=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k).': &nbsp;'.$z.'</div>';
								}
							}
							if((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1){
								echo '<div class="rows clean">'.$c['lang_pack']['products']['shipsFrom'].': &nbsp;'.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</div>';
							}?>
							<div class="rows clean"><?=$c['lang_pack']['mobile']['quantity'].': &nbsp;'.$v['Qty'];?></div>
							<?php if($v['Remark']){?><div class="rows clean"><?=$c['lang_pack']['mobile']['notice'].': &nbsp;'.$v['Remark'];?></div><?php }?>
							<div class="price"><?=cart::iconv_price($price);?></div>
						</div>
					</div>
					<?php }?>
				</div>
            </div>
        </div>
		<form id="PlaceOrderFrom" method="post" action="?" amountPrice="<?=$iconv_total_price;?>" userPrice="<?=(($AfterPrice_0 && !$AfterPrice_1) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_0>$AfterPrice_1))?$AfterPrice_0:0;?>">
			<div class="checkout_summary ui_border_tb">
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
				<div class="btn_global btn BuyNowBgColor" id="cart_checkout"><?=$c['lang_pack']['mobile']['place_order'];?></div>
			</div>
            <input type="hidden" name="order_coupon_code" value="<?=$_SESSION['Cart']['Coupon'];?>" cutprice="0" />
			<input type="hidden" name="order_discount_price" value="<?=($AfterPrice_1 && !$AfterPrice_0) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_1>$AfterPrice_0)?$AfterPrice_1:0;?>" />
            <input type="hidden" name="order_shipping_address_aid" value="<?=$address_row['AId']?$address_row['AId']:-1;?>" />
            <input type="hidden" name="order_shipping_address_cid" value="<?=$address_row['CId']?$address_row['CId']:-1;?>" />
            <input type="hidden" name="order_shipping_method_sid" value="[]" />
            <input type="hidden" name="order_shipping_method_type" value="[]" />
            <input type="hidden" name="order_shipping_price" value="[]" />
            <input type="hidden" name="order_shipping_insurance" value="[]" price="[]" />
			<input type="hidden" name="order_shipping_oversea" value="<?=$oversea_id_hidden?implode(',', $oversea_id_hidden):'';?>" />
            <input type="hidden" name="order_payment_method_pid" value="-1" />
            <input type="hidden" name="order_products_attribute_error" value="<?=(int)$products_attribute_error;?>" />
			<input type="hidden" name="shipping_method_where" value="&ProId=<?=$cart_row[0]['ProId'];?>&Qty=<?=$cart_row[0]['Qty'];?>&Type=shipping_cost" attr="<?=str::str_code(str::json_data($Attr));?>" />
			<input type="hidden" name="order_cid" value="<?=$CId;?>" />
        </form> 
    </div>
</div>
<script type="text/javascript">
var address_count=<?=count($address_row);?>;
var address_perfect=<?=(!$address_row['FirstName'] || !$address_row['LastName'] || !$address_row['AddressLine1'] || !$address_row['City'] || !$address_row['CId'] || !$address_row['ZipCode'] || !$address_row['PhoneNumber'] || (!$address_row['State'] && !$address_row['SId']))?1:0;?>;
</script>
