<?php !isset($c) && exit();?>
<?php
$error_ary=array();
	
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
(!$CId || !count($cart_row)) && js::location("/cart/");
//产品总金额
$total_price=$iconv_total_price=0;
foreach((array)$cart_row as $v){
	$products_row=str::str_code(db::get_one('products', "ProId='{$v['ProId']}'"));
	//$price=$v['Price'];
	//$v['BuyType']==0 && $price=cart::products_add_to_cart_price($products_row, $v['Qty']);
	//$price+=$v['PropertyPrice'];
	$price=$v['Price']+$v['PropertyPrice'];
	$v['Discount']<100 && $price*=$v['Discount']/100;
	$total_price+=$price*$v['Qty'];
	$iconv_total_price+=cart::iconv_price($price, 2, '', 0)*$v['Qty'];
}
unset($products_row);

//检查产品资料是否完整
$oversea_id_ary=array();
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
		if(!$row || $IsError>0) $error_ary["{$v['ProId']}_{$v['CId']}"]=1;//检查此产品是否有选择购物车属性
	}
	!in_array($v['OvId'], $oversea_id_ary) && $oversea_id_ary[]=$v['OvId'];
}

if((int)$_SESSION['User']['UserId']){
	//收货地址
	$address_row=str::str_code(db::get_all('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', 'a.'.$c['where']['cart']." and a.IsBillingAddress=0", 'a.*, c.Country, s.States as StateName', 'a.AccTime desc, a.AId desc'));
}
//付款方式
$payment_row=db::get_all('payment', "IsUsed=1 and PId!=2", '*', $c['my_order'].'IsOnline desc,PId asc');

$IsInsurance=str::str_code(db::get_value('shipping_config', '1', 'IsInsurance'));

//会员优惠价 与 全场满减价 比较
$AfterPrice_0=$AfterPrice_1=0;
$user_discount=$_discount=100;
if((int)$_SESSION['User']['UserId'] && (int)$_SESSION['User']['Level']){
	$user_discount=(float)db::get_value('user_level', "LId='{$_SESSION['User']['Level']}' and IsUsed=1", 'Discount');
	$user_discount=($user_discount>0 && $user_discount<100)?$user_discount:100;
	$AfterPrice_0=$iconv_total_price-($iconv_total_price*($user_discount/100));
}
if($cutArr['IsUsed']==1 && $c['time']>=$cutArr['StartTime'] && $c['time']<=$cutArr['EndTime']){
	foreach((array)$cutArr['Data'] as $k=>$v){
		if($total_price<$k) break;
		$cutArr['Type']==0 && $_discount=100-$v[0];
		$AfterPrice_1=($cutArr['Type']==1?cart::iconv_price($v[1], 2, '', 0):($iconv_total_price*(100-$v[0])/100));
	}
}
if($AfterPrice_0==$AfterPrice_1){//当会员优惠价和全场满减价一致，默认只保留会员优惠价
	$AfterPrice_1=0;
}

if((int)$c['config']['global']['Overseas']==0 || count($c['config']['Overseas'])<2 || count($oversea_id_ary)<2){//关闭海外仓功能 或者 仅有一个海外仓选项
?>
	<style>#shippingObj .shipping ul:first-child li.title{display:none;}</style>
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
<script src="//www.paypalobjects.com/api/checkout.js" async></script>
<script type="text/javascript">
var address_count=<?=count($address_row);?>;
var address_perfect=<?=($address_row && (!$address_row[0]['FirstName'] || !$address_row[0]['LastName'] || !$address_row[0]['AddressLine1'] || !$address_row[0]['City'] || !$address_row[0]['CId'] || !$address_row[0]['ZipCode'] || !$address_row[0]['PhoneNumber']))?1:0;?>;
$(document).ready(function(){
	cart_obj.cart_list();
	cart_obj.checkout_init();
});
$('html').loginOrVisitors('<?=$_SERVER['REQUEST_URI'];?>', 0, function(){
	ueeshop_config['_login']=1;
	return false;
});
</script>
<div id="lib_cart" class="buynow_content">
    <div class="step"><div class="step_1"></div></div>
    <div class="cartFrom">
    	<table width="100%" align="center" cellpadding="12" cellspacing="0" border="0" class="itemFrom">
        	<thead>
            	<tr>
                	<td width="50%" class="first"><?=$c['lang_pack']['cart']['item'];?><input type="checkbox" name="select_all" value="" class="va_m" style="display:none;" /></td>
                	<td width="16%"><?=$c['lang_pack']['cart']['price'];?></td>
                	<td width="16%" class="quantity"><?=$c['lang_pack']['cart']['qty'];?></td>
                	<td width="18%"><?=$c['lang_pack']['cart']['amount'];?></td>
                </tr>
            </thead>
            <tbody>
            	<?php
				$total_weight=$total_quantity=0;
				$cart_attr=$cart_attr_data=array();
				$OvId_where='a.OvId in(-1';
				foreach((array)$cart_row as $v){
					$attr=array();
					$v['Property']!='' && $attr=str::json_data(str::attr_decode($v['Property']), 'decode');
					$attr_len=count($attr);
					$oversea_len=db::get_row_count('products_selected_attribute', "ProId='{$v['ProId']}' and AttrId=0 and VId=0 and OvId>1 and IsUsed=1");
					(int)$c['config']['global']['Overseas']==0 && $attr_len==1 && $attr['Overseas'] && $attr_len-=1;
					$products_row=str::str_code(db::get_one('products', "ProId='{$v['ProId']}'"));
					$price=$v['Price']+$v['PropertyPrice'];
					$v['Discount']<100 && $price*=$v['Discount']/100;
					$img=ly200::get_size_img($v['PicPath'], '240x240');
					$url=ly200::get_url($v, 'products');
					$total_quantity+=$v['Qty'];
					$total_weight+=($v['Weight']*$v['Qty']);
					$OvId_where.=",{$v['OvId']}";
				?>
				<tr<?=($error_ary["{$v['ProId']}_{$v['CId']}"]?' class="null"':'');?> cid="<?=$v['CId'];?>">
					<td class="prList">
						<dl>
							<dt><a href="<?=$url;?>"><img src="<?=$img;?>" alt="<?=$v['Name'.$c['lang']];?>" name="<?=$v['Name'.$c['lang']];?>" /></a></dt>
							<dd>
								<h4><a href="<?=$url;?>" title="<?=$v['Name'.$c['lang']];?>"><?=$v['Name'.$c['lang']];?></a></h4>
								<p><?=$v['Prefix'].$v['Number'];?></p>
								<?php if(count($attr)){?>
									<div<?=(($attr_len>1 && $v['BuyType']!=3) || ($attr_len==1 && $oversea_len>0))?' class="prAttr"':'';?>>
										<?php
										foreach((array)$attr as $k=>$z){
											if($k=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
											echo '<p class="attr_'.$k.'">'.($k=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k).': '.$z.'</p>';
										}
										if((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1){
											echo '<p class="attr_Overseas">'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</p>';
										}?>
										<span class="prAttr_mod"></span>
										<div class="attr_edit"><div class="attr_edit_content clean"></div><span class="arrow"></span><em class="arrow arrow_bg"></em></div>
									</div>
								<?php }?>
								<?=$error_ary["{$v['ProId']}_{$v['CId']}"]?('<p class="error">'.$c['lang_pack']['cart']['prod_error'].'</p>'):'';?>
							</dd>
						</dl>
						<dl>
							<dd>
								<p class="remark"><?=$c['lang_pack']['cart']['remark'];?>: <input type="text" name="Remark[]" value="<?=htmlspecialchars($v['Remark']);?>" maxlength="200" data="<?=($v['Remark']);?>" cid="<?=$v['CId'];?>" proid="<?=$v['ProId'];?>" /></p>
							</dd>
						</dl>
					</td>
					<td class="prPrice"><p price="<?=$v['Price'];?>" discount="<?=$v['Discount'];?>"><?=cart::iconv_price($price);?></p></td>
					<td class="prQuant" start="<?=$v['StartFrom'];?>">
                    	<input type="checkbox" name="select" value="<?=$v['CId'];?>" class="va_m" style="display:none;" checked />
                    	<img src="/static/themes/default/images/cart/reduce.png" name="reduce" />
                        <input type="text" name="Qty[]" value="<?=$v['Qty'];?>" maxlength="4" />
                        <img src="/static/themes/default/images/cart/add.png" name="add" />
                        <input type="hidden" name="S_Qty[]" value="<?=$v['Qty'];?>" />
                        <input type="hidden" name="CId[]" value="<?=$v['CId'];?>" />
                        <input type="hidden" name="ProId[]" value="<?=$v['ProId'];?>" />
                    </td>
					<td class="prAmount"><p price="<?=cart::iconv_price($price, 2, '', 0)*$v['Qty'];?>"><?=cart::iconv_price(0, 1).cart::currency_format(cart::iconv_price($price, 2, '', 0)*$v['Qty'], 0, $_SESSION['Currency']['Currency']);?></p></td>
				</tr>
                <?php
				}
				$OvId_where.=')';
				?>
            </tbody>
        </table>
		<?php /*if($a && (int)db::get_row_count('payment', "Method='Excheckout' and IsUsed=1")){?>
			<div class="edit_shopping_cart">
				<button class="paypal_checkout_button fl"></button>
				<div class="clear"></div>
			</div>
		<?php }*/?>
    </div>
    <div class="cartBox" id="addressObj">
    	<h2><?=$c['lang_pack']['cart']['address'];?></h2>
        <div class="contents address">
            <ul id="lib_address">
            	<?php 
				if((int)$_SESSION['User']['UserId']){
					foreach((array)$address_row as $v){
					?>
            		<li>
                        <input type="radio" name="shipping_address_id" id="address_<?=$v['AId'];?>" value="<?=$v['AId'];?>" CId="<?=$v['CId'];?>" />
                        <label for="address_<?=$v['AId'];?>">
                        	<strong><?=$v['FirstName'].' '.$v['LastName'];?></strong>
							(<?=$v['AddressLine1'].' '.($v['AddressLine2']?$v['AddressLine2'].' ':'').$v['City'].', '.($v['StateName']?$v['StateName']:$v['State']).' '.$v['ZipCode'].' '.$v['Country'];?>)
                        </label>
                        <a href="javascript:;" class="edit_address_info"><?=$c['lang_pack']['cart']['edit'];?></a>
                    </li>
                <?php 
					}
				}
				?>
				<li style="display:<?=!(int)$_SESSION['User']['UserId']?'none':'';?>;"><a id="addAddress" href="javascript:;" class="textbtn"><?=$c['lang_pack']['cart']['add'];?></a> </li>
                <li id="addressInfo" style="display:none;"></li>
				<li id="addressForm"><?php include("{$c['default_path']}user/module/shippingAddress.php");?></li>
            </ul> 
            <div class="clear"></div>
        </div>
    </div>
    <div class="cartBox" id="shippingObj">
    	<h2><?=$c['lang_pack']['cart']['delivery'];?></h2>
        <div class="contents shipping">
			<?php
			$oversea_count=count($oversea_id_ary);
			foreach($oversea_id_ary as $k=>$v){
			?>
        	<ul data-id="<?=$v;?>"<?=($NotDefualtOvId && (int)$v==1)?' class="hide"':'';?>>
				<li class="title"><?=$c['config']['Overseas'][$v]['Name'.$c['lang']];?></li>
            	<li class="list">
                    <dl>
                        <dt><?=$c['lang_pack']['cart']['shipmethod'];?>:</dt>
                        <dd><ul class="shipping_method_list"></ul></dd>
                    </dl>
                </li>
				<?php if($IsInsurance){?>
            	<li class="insurance">
                	<dl>
                    	<dt><?=$c['lang_pack']['cart']['insurance'];?>:</dt>
                        <dd>
                        	<input type="checkbox" name="_shipping_method_insurance" class="shipping_insurance" value="1" checked="checked" />
                            <label for="shipping_insurance"><?=$c['lang_pack']['cart']['add_insur'];?></label>
                            <a class="delivery_ins" href="javascript:;" content="<?=$c['lang_pack']['cart']['tips_insur']?>"><?=$c['lang_pack']['cart']['why_insur'];?></a>
                        </dd>
                        <dd class="price"><?=$_SESSION['Currency']['Symbol'];?><em>0.99</em></dd>
                    </dl>
                </li>
				<?php }?>
				<?php if($k==$oversea_count-1){?>
            	<li class="tips"><?=$c['lang_pack']['cart']['arrive'];?></li>
            	<li class="editor_txt"><?=str::str_code($c['config']['global']['ArrivalInfo']['ArrivalInfo'.$c['lang']], 'htmlspecialchars_decode');?></li>
				<?php }?>
            </ul>
			<?php }?>
        </div>
    </div>
    <div class="cartBox" id="paymentObj">
    	<h2><?=$c['lang_pack']['cart']['paymethod'];?></h2>
        <div class="contents payment">
        	<h3><?=$c['lang_pack']['cart']['choosepay'];?>:</h3>
			<div class="payment_list">
            	<?php foreach((array)$payment_row as $k=>$v){?>
                	<div class="payment_row" value="<?=$v['PId'];?>" min="<?=$v['MinPrice'];?>" max="<?=$v['MaxPrice'];?>">
                    	<div class="check">&nbsp;<input name="PId" type="radio"></div>
                        <div class="img"><img src="<?=$v['LogoPath'];?>" alt="<?=$v['Name'.$c['lang']];?>"><span></span></div>
                        <div class="name"><?=$v['Name'.$c['lang']];?></div>
                        <div class="clear"></div>
                        <div class="payment_contents" fee="<?=$v['AdditionalFee'];?>" affix="<?=cart::iconv_price($v['AffixPrice'], 2, '', 0);?>">
                        	<?php if($v['Description'.$c['lang']]){?><div class="ext_txt"><?=$v['Description'.$c['lang']]?></div><?php }?>
                        </div>
                    </div>
                <?php }?>
			</div>
			<?php if($c['FunVersion']>=1){?>
			<div class="new-coupon">
                <p id="new-coupon-valid">
					<span class="valid" style="display:none;"><?=$c['lang_pack']['cart']['codevalid'];?><br /></span>
                </p>
                <p><a href="javascript:;" class="u" id="removeCoupon"><?=$c['lang_pack']['cart']['remove'];?></a></p>
                <p id="new-cp"><a href="javascript:;" id="to-use-coupon"><?=$c['lang_pack']['cart']['applycode'];?><i> </i> </a></p>
                <p id="link-error"><span class="netError red" style="display: none;"></span></p>
            </div>
			<?php }?>
        </div>
    </div>
    <form id="PlaceOrderFrom" method="post" action="/cart/" amountPrice="<?=$iconv_total_price;?>" userPrice="<?=(($AfterPrice_0 && !$AfterPrice_1) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_0>$AfterPrice_1))?$AfterPrice_0:0;?>" userRatio="<?=$user_discount;?>">
		<div class="NoteBox">
    		<h2><?=$c['lang_pack']['cart']['tips']['note'];?></h2>
			<div class="notes"><textarea name="Note"></textarea></div>
		</div>
        <div class="CartAmountSum">
            <table id="subTotal" cellpadding="0" cellspacing="0" border="0" width="100%">
                <!-- Grand Total -->
                <tfoot>
                    <tr id="cartAmount" style="display: table-row;">
                        <th width="100%"><?=$c['lang_pack']['cart']['grand'];?>:<em><?=$_SESSION['Currency']['Symbol'];?></em></th>
                        <td><strong id="ot_total"></strong></td>
                    </tr>
                </tfoot>
                <tbody>
					<?php
					if($c['config']['global']['CartWeight']){
					?>
                    <tr style="display: table-row;">
                        <!-- subtotal -->
                        <th><?=$c['lang_pack']['cart']['weight'];?> (KG):<em>&nbsp;</em></th>
                        <td><strong id="total_weight"><?=sprintf('%01.3f', $total_weight);?></strong></td>
                    </tr>
					<?php }?>
                    
                    <tr style="display: table-row;">
                        <!-- subtotal -->
                        <th><?=$c['lang_pack']['cart']['subtotal'];?>( <?=str_replace('%num%', $total_quantity, $c['lang_pack']['cart'][($total_quantity>1?'itemsCount':'itemCount')]);?> ):<em><?=$_SESSION['Currency']['Symbol'];?></em></th>
                        <td><strong id="ot_subtotal"><?=cart::currency_format($iconv_total_price, 0, $_SESSION['Currency']['Currency']);?></strong></td>
                    </tr>
					
					<tr id="memberSavings" style="display:<?=($AfterPrice_0 && !$AfterPrice_1) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_0>$AfterPrice_1)?'table-row':'none';?>;">
						<!-- Member Savings -->
						<th>(-) <?=$c['lang_pack']['cart']['user_save'];?>:<em>- <?=$_SESSION['Currency']['Symbol'];?></em></th>
						<td><strong id="ot_user"><?=cart::currency_format($AfterPrice_0, 0, $_SESSION['Currency']['Currency']);?></strong></td>
					</tr>
					
                    <tr id="shippingCharges" style="display: none;">
                        <!-- Shipping Charges-->
                        <th>(+) <?=$c['lang_pack']['cart']['shipcharge'];?>:<em><?=$_SESSION['Currency']['Symbol'];?></em></th>
                        <td><strong id="ot_shipping">0.00</strong></td>
                    </tr>
                    
                    <tr id="shippingInsuranceCombine" style="display: table-row;">
                        <!-- Shipping Insurance combine-->
                        <th>(+) <?=$c['lang_pack']['cart']['ship_insur'];?>:<em><?=$_SESSION['Currency']['Symbol'];?></em></th>
                        <td><strong id="ot_combine_shippnig_insurance"></strong></td>
                    </tr>
                                   
                    <tr id="couponSavings" style="display: none;">
                        <!-- Coupon Savings -->
                        <th>(-) <?=$c['lang_pack']['cart']['code_save'];?>:<em>- <?=$_SESSION['Currency']['Symbol'];?></em></th>
                        <td><strong id="ot_coupon">0.00</strong></td>
                    </tr>
					
					<tr id="subtotalDiscount" style="display:<?=($AfterPrice_1 && !$AfterPrice_0) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_1>$AfterPrice_0)?'table-row':'none';?>;">
						<!-- subtotal discount-->
						<th>(-) <?=$c['lang_pack']['cart']['save'];?>:<em>- <?=$_SESSION['Currency']['Symbol'];?></em></th>
						<td id="ot_subtotal_discount"><strong><?=cart::currency_format($AfterPrice_1, 0, $_SESSION['Currency']['Currency']);?></strong></td>
					</tr>
					
					<tr id="serviceCharge" style="display: none;">
                        <!-- Service Charge -->
                        <th>(+) <?=$c['lang_pack']['cart']['fee'];?>:<em><?=$_SESSION['Currency']['Symbol'];?></em></th>
                        <td><strong id="ot_fee">0.00</strong></td>
                    </tr>
                </tbody>
            </table>
			<input type="hidden" name="CartProductPrice" value="<?=$total_price-cart::iconv_price($cutprice, 2, '', 0);?>" />
			<input type="hidden" name="DiscountPrice" value="<?=cart::iconv_price($cutprice, 2, '', 0);?>" />
            <fieldset id="submitCart" class="clearfix">
                <a href="javascript:;" class="fr"><input type="button" id="orderFormSubmit" class="litb-btn placeOrderBtn"></a>
                <p class="clearfix"><?=$c['lang_pack']['cart']['savetips'];?></p>
            </fieldset>
        </div>
        <input type="hidden" name="order_coupon_code" value="<?=$_SESSION['Cart']['Coupon'];?>" cutprice="0" />
        <input type="hidden" name="order_discount_price" value="<?=($AfterPrice_1 && !$AfterPrice_0) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_1>$AfterPrice_0)?$AfterPrice_1:0;?>" />
        <input type="hidden" name="order_shipping_address_aid" value="-1" />
        <input type="hidden" name="order_shipping_address_cid" value="-1" />
        <input type="hidden" name="order_shipping_method_sid" value="[]" />
        <input type="hidden" name="order_shipping_method_type" value="[]" />
        <input type="hidden" name="order_shipping_price" value="[]" />
        <input type="hidden" name="order_shipping_insurance" value="[]" price="[]" />
		<input type="hidden" name="order_shipping_oversea" value="<?=$oversea_id_hidden?implode(',', $oversea_id_hidden):'';?>" />
        <input type="hidden" name="order_payment_method_pid" value="-1" />
		<input type="hidden" name="order_buynow" value="1" />
		<input type="hidden" name="shipping_method_where" value="&ProId=<?=$cart_row[0]['ProId'];?>&Qty=<?=$cart_row[0]['Qty'];?>&Type=shipping_cost" attr="<?=str::str_code(str::json_data($Attr));?>" />
		<input type="hidden" name="order_cid" value="<?=$CId;?>" />
	</form>
</div>
<?php
$payment_row=db::get_one('payment', "PId=1 and IsUsed=1", 'IsOnline, Method, Attribute');
if($payment_row){
	$account=str::json_data($payment_row['Attribute'], 'decode');
?>
	<form name="paypal_checkout_form" method="POST" action="" class="hide">
		<input id="paypal_payment_option" value="paypal_express" type="radio" name="paymentMethod" title="PayPal Checkout" class="radio" style="display:none;" checked />
		<input type="submit" value="Submit" id="paypal_now_checkout_button" />
	</form>
	<script>
	window.paypalCheckoutReady=function(){
		paypal.checkout.setup("<?=$account['Account'];?>", {
			button:"paypal_now_checkout_button",
			environment:"production",
			condition:function(){
				return document.getElementById("paypal_payment_option").checked === true;
			}
		});
	};
	</script>
<?php }?>