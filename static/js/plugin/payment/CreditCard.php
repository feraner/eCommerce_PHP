<?php
$pay_data=$data; //转换一下，防止其他地方已经调用这一变量

if(!$is_mobile){
	//PC端
?>
	<!doctype html>
	<html>
	<head>
	<meta charset="utf-8">
	<title><?=$title;?></title>
	<?php include("{$c['static_path']}/inc/static.php");?>
	<?=ly200::load_static('/static/js/plugin/payment/CreditCard.js');?>
	</head>
	
	<body>
	<?php include("{$c['theme_path']}/inc/header.php");?>
	<div class="blank25"></div>
	<style type="text/css">
	#creditcart{width:572px; margin:0 auto; overflow:hidden;}
	#creditcart .orderinfo{width:560px; margin:10px 0; overflow:hidden;}
	#creditcart .orderinfo dt{color:#C88039; font-size:16px; font-weight:bold; padding-left:15px; margin:20px 0;}
	#creditcart .orderinfo dd{border-radius:8px; border:1px #bbb solid; box-shadow:1px 2px 3px rgba(0, 0, 0, 0.5);}
	#creditcart .orderinfo dd>div{width:522px; margin:0 auto;}
	#creditcart .payinfo{width:560px; height:auto; overflow:hidden; border-radius:8px; border:1px solid #bbbbbb; box-shadow:1px 2px 3px rgba(0, 0, 0, 0.5); background:url(/static/themes/default/images/cart/sp_checkout/bg_x.gif.jpg); background-repeat:repeat-x; margin:10px 0 0 0;}
	#creditcart .payinfo_bd{width:520px; height:auto; border:1px solid #dedede; border-radius:8px; background:#fff; margin:0 auto; margin-top:20px; margin-bottom:20px;}
	#creditcart .payinfo_bd_oth{width:295px; height:auto; margin:10px; font-size:12px;}
	#creditcart .payinfo_bd_oth table td{line-height:20px;}
	#creditcart .payinfo_bd_oth table td.info_left{width:120px;}
	#creditcart table{width:500px; margin:0 auto; padding:0.6em; text-decoration:none; display:inline-table;}
	
	#contexttable td>span{color:#c93; padding-left:20px;}
	#contexttable td:first-child{width:40%; text-align:right;}
	#contexttable td, #contexttable td>strong, #contexttable td>span{font-size:20px;}
	
	.content{width:350px; overflow:hidden; border:1px #ddd solid;}
	.info_left{font:12px/20px Verdana; color:#666;}
	.info_right{width:200px; border:1px solid #6CF; float:left; margin:5px 10px 5px 0; height:20px; font:12px/20px Verdana; color:#666;}
	#CardSecurityCode{width:80px; float:left; margin:5px 10px 5px 0; height:20px; font:12px/20px Verdana; color:#666;}
	#bigGlass{height:35px; position:absolute; background-color:#FFFBE5; border:1px #2D8DCF solid; display:none; line-height:35px; font-size:20px; color:#F79209; padding:0 15px 0 15px;}
	#bigGlass span{margin-left:8px;}
	#bigGlass span:first-child{margin-left:0;}
	select{width:80px; height:20px; border:1px #bbb solid; float:left; margin:5px 10px 5px 0; font:12px/20px Verdana; color:#666;}
	
	.btn_submit{height:22px; line-height:22px; background:url(/static/themes/default/images/user/buttons_bg.jpg) repeat-x; padding:0 10px; text-shadow:0 1px 0 #fff9a0; border:1px #d0af76 solid; border-radius:2px; -webkit-border-radius:2px; -moz-border-radius:2px; display:inline-block; text-decoration:none; color:#963; font-family:Verdana; margin-right:6px;}
	</style>
	<div id="main" class="w">
		<div id="creditcart">
			<div class="orderinfo">
				<dl>
					<dt><?=$c['lang_pack']['cart']['orderInfo'];?></dt>
					<dd>
						<div style="padding-top:10px;">
							<table id="contexttable" width="295" cellspacing="0" cellpadding="0" border="0" style="font-size:12px;">
								<tr>
									<td><strong><?=$c['lang_pack']['cart']['orderNo'];?>:</strong></td> 
									<td><span><?=$pay_data['order_row']['OId'];?></span></td>                 
								</tr>
								<tr height="25">
									<td><strong><?=$c['lang_pack']['cart']['amount'];?>:</strong></td>
									<td><span><?=$pay_data['order_row']['Currency'].' '.cart::iconv_price(0, 1, $pay_data['order_row']['Currency']).cart::currency_format($pay_data['total_price'], 0, $pay_data['order_row']['Currency']);?></span></td>
								</tr>
							</table>
						</div>
						<div style="margin-bottom:20px; border-top:1px #000 solid;">
							<p style="margin:10px 0;"><?=$c['lang_pack']['cart']['paymentInfo'];?></p>
						</div>
					</dd>
				</dl>
			</div>
			<div class="payinfo">
				<div class="payinfo_bd">
					<div class="payinfo_bd_oth">
						<div style="font-size:12px; padding-top:10px;">
							<table>
								<form id="payForm" name="payForm" action="" method="post" onSubmit="return checkPay();">
									<tr>
										<td class="info_left"><?=$c['lang_pack']['cart']['CardNo'];?>:</td>
										<td><input type="text" name="CardNo" id="CardNo"  maxlength="16" class="info_right" value="" /><img src="/static/themes/default/images/cart/sp_checkout/vsia_ico.jpg" /><img style="margin-left:5px;" src="/static/themes/default/images/cart/sp_checkout/mastercard.jpg" /><img src="/static/themes/default/images/cart/sp_checkout/jcb.gif" /></td>
									</tr>
									<tr>
										<td class="info_left"><?=$c['lang_pack']['cart']['expirationDate'];?>:</td>
										<td>
											<select name="CardExpireMonth" id="CardExpireMonth" notnull>
												<option value=""><?=$c['lang_pack']['cart']['month'];?></option>
												<option value="01">1-January</option>
												<option value="02">2-February</option>
												<option value="03">3-March</option>
												<option value="04">4-April</option>
												<option value="05">5-May</option>
												<option value="06">6-June</option>
												<option value="07">7-July</option>
												<option value="08">8-August</option>
												<option value="09">9-September</option>
												<option value="10">10-October</option>
												<option value="11">11-November</option>
												<option value="12">12-December</option>
												</select>
												<select name="CardExpireYear" id="CardExpireYear" notnull>
												<option value=""><?=$c['lang_pack']['cart']['year'];?></option>
												<?php 
												for($i=0; $i<10; ++$i){
													$y=$year+$i;
												?>
													<option value="<?=$y;?>"><?=$y;?></option>
												<?php }?>
											</select>
										</td>
									</tr>
									<tr>
										<td class="info_left" ><?=$c['lang_pack']['cart']['CVV2'];?>:</td>
										<td><input type="password" name="CardSecurityCode" id="CardSecurityCode" size="8" maxlength="3" />
										<img src="/static/themes/default/images/cart/sp_checkout/cvv_ico.jpg" id="showCard" style="position:absolute; z-index:1;margin-top:5px;" /></td>
									</tr>
									<?php if($IssuingBank){?>
									<tr>
										<td class="info_left" ><?=$c['lang_pack']['cart']['issuingBank'];?>:</td>
										<td><input type="text" name="IssuingBank" id="IssuingBank" maxlength="100" class="info_right" value="" /></td>
									</tr>
									<?php }?>
									<tr>
										<td height="20" colspan="2">&nbsp;</td>
									</tr>
									<tr>
										<td class="info_left" colspan="2"><strong style="padding:12px 0;"><img src="/static/themes/default/images/cart/sp_checkout/small_menu.gif">&nbsp;&nbsp;<?=$c['lang_pack']['cart']['yBillAddress'];?> .</strong></td>
									</tr>
									<tr>
										<td class="info_left" valign="top"><?=$c['lang_pack']['cart']['pName'];?>: </td>
										<td><?=$pay_data['order_row']['BillFirstName'].' '.$pay_data['order_row']['BillLastName'];?></td>
									</tr>
									<tr>
										<td class="info_left" valign="top"><?=$c['lang_pack']['cart']['pAddress'];?>: </td>
										<td><?=$pay_data['order_row']['BillAddressLine1'].' '.$pay_data['order_row']['BillAddressLine2'].', '.$pay_data['order_row']['BillCity'].', '.$pay_data['order_row']['BillState'].' ( '.$pay_data['order_row']['BillCountry'].' )';?><br /><?=$pay_data['order_row']['BillZipCode'];?></td>
									</tr>
									<tr>
										<td class="info_left" valign="top"><?=$c['lang_pack']['cart']['pPhoneNo'];?>: </td>
										<td><?=$pay_data['order_row']['BillCountryCode'].' '.$pay_data['order_row']['BillPhoneNumber'];?></td>
									</tr>
									<tr>
										<td height="20" colspan="2">&nbsp;</td>
									</tr>
									<tr>
										<td class="info_left" colspan="2"><strong style="padding:8px 0;"><img src="/static/themes/default/images/cart/sp_checkout/small_menu.gif">&nbsp;&nbsp;<?=$c['lang_pack']['cart']['yShipAddress'];?> .</strong></td>
									</tr>
									<tr>
										<td class="info_left" valign="top"><?=$c['lang_pack']['cart']['pName'];?>: </td>
										<td><?=$pay_data['order_row']['ShippingFirstName'].' '.$pay_data['order_row']['ShippingLastName'];?></td>
									</tr>
									<tr>
										<td class="info_left" valign="top"><?=$c['lang_pack']['cart']['pAddress'];?>: </td>
										<td><?=$pay_data['order_row']['ShippingAddressLine1'].' '.$pay_data['order_row']['ShippingAddressLine2'].', '.$pay_data['order_row']['ShippingCity'].', '.$pay_data['order_row']['ShippingState'].' ( '.$pay_data['order_row']['ShippingCountry'].' )';?><br /><?=$pay_data['order_row']['ShippingZipCode'];?></td>
									</tr>
									<tr>
										<td class="info_left" valign="top"><?=$c['lang_pack']['cart']['pPhoneNo'];?>: </td>
										<td><?=$pay_data['order_row']['ShippingCountryCode'].' '.$pay_data['order_row']['ShippingPhoneNumber'];?></td>
									</tr>
									<tr>
										<td></td>
										<td><br /><input type="submit" name="btn_submit" value="<?=$c['lang_pack']['cart']['makePayment'];?>" class="btn_submit" /><?php /*<input type="image" name="btn_submit" src="/static/themes/default/images/cart/sp_checkout/btn.png">*/?></td>
									</tr>
								</form>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="blank25"></div>
	<div class="blank25"></div>
	<script type="text/javascript">
	function checkPay(){
		var CardNo=$('input[name=CardNo]'),
			CardExpireMonth=$('select[name=CardExpireMonth]'),
			CardExpireYear=$('select[name=CardExpireYear]'),
			CardSecurityCode=$('input[name=CardSecurityCode]'),
			IssuingBank=$('input[name=IssuingBank]');
		
		$('input, select').css('border', '1px solid #ddd');
		
		if(CardNo.val()=='' || isNaN(CardNo.val()) || CardNo.val().length!=16 )
		{
			CardNo.css('border', '1px solid #f00').focus();
			alert('Card Number can not be null, it must be numeric, the length must be 16.');
			return false;
		}
		if(CardExpireMonth.val()=='' || CardExpireMonth.val().length==0){
			CardExpireMonth.css('border', '1px solid #f00').focus();
			alert('Card Expire Month can not be null.');
			return false;
		}
		if(CardExpireYear.val()=='' || CardExpireYear.val().length==0){
			CardExpireYear.css('border', '1px solid #f00').focus();
			alert('Card Expire Year can not be null.');
			return false;
		}
		if(CardSecurityCode.val()=='' || isNaN(CardSecurityCode.val()) || CardSecurityCode.val().length!=3){ 
			CardSecurityCode.css('border', '1px solid #f00').focus();
			alert('CVV2/CSC is incorrect, it must be numeric, the lenght must be 3!');
			return false;
		}
		if(IssuingBank.length && IssuingBank.val()==''){ 
			IssuingBank.css('border', '1px solid #f00').focus();
			alert('Issuing Bank can not be null!');
			return false;
		}
		return true;
	}
	//cvv图片提示
	$(function(){
		$('#main').delegate('img#showCard', 'mouseover', function(){
			$(this).attr('src', '/static/themes/default/images/cart/sp_checkout/cvv_help.gif');
		});
		$('#main').delegate('img#showCard', 'mouseleave', function(){
			$(this).attr('src', '/static/themes/default/images/cart/sp_checkout/cvv_ico.jpg');
		});
	});
	</script>
<?php
}else{
	//移动端
?>
	<style type="text/css">
	#creditcart{width:100%; margin:0 auto; overflow:hidden;}
	#creditcart .orderinfo dt{height:3rem; line-height:3rem; padding:0 5%; text-align:center; font-size:1.15rem; color:#333;}
	#creditcart .orderinfo dd{padding:.875rem 0; background-color:#f6f6f6;}
	#creditcart .orderinfo dd>div{width:90%; margin:0 auto;}
	#creditcart .orderinfo p{font-size:.75rem; line-height:1.25rem; color:#999;}
	
	#contexttable td{height:1.5rem; line-height:1.5rem;}
	#contexttable td>span{padding-left:.5rem; color:#005ab0;}
	#contexttable td:first-child{width:48%; text-align:right;}
	#contexttable td, #contexttable td>strong, #contexttable td>span{font-size:1rem;}
	
	.payinfo_bd{padding:.625rem 0;}
	.payinfo_bd .rows{margin:0 .9375rem .625rem;}
	.payinfo_bd .rows .field{font-size:.875rem; line-height:1.5rem;}
	.payinfo_bd .rows .input{font-size:.75rem; line-height:.75rem;}
	.payinfo_bd .rows .box_input{width:96%; padding:0 2%;}
	.payinfo_bd .rows .box_input.null{border-color:#f00;}
	.payinfo_bd .rows .box{width:47%; float:left; box-sizing:border-box; -webkit-box-sizing:border-box;}
	.payinfo_bd .rows .box .box_select{background-size:30%;}
	.payinfo_bd .rows .box .box_select>select{width:120%; margin:0;}
	.payinfo_bd .rows .box:first-child{margin-right:6%;}
	
	.inside_input{height:2.1875rem; line-height:2.1875rem; overflow:hidden; font-size:.875rem; background:none; border:.0625rem #ddd solid; border-radius:.3125rem;}
	.inside_input>input{width:60%; height:2.0625rem; line-height:2.0625rem;}
	.inside_input .img{width:38%; margin-right:2%; text-align:right; float:right;}
	.inside_input .img>img{max-width:30%; margin-top:.4rem;}
	
	.address_info{padding:0 .625rem;}
	.address_info .title{line-height:1.5rem; padding-top:.625rem; font-size:.95rem;}
	.address_info .txt{padding:.625rem 0 .4rem;}
	.address_info .txt .rows{margin:.625rem 0;}
	.address_info .txt .rows>strong, .address_info .txt .rows>span{line-height:1rem; font-size:.875rem; float:left; box-sizing:border-box; -webkit-box-sizing:border-box;}
	.address_info .txt .rows>strong{width:30%; text-align:left; color:#999;}
	.address_info .txt .rows>span{width:70%; line-height:1.25rem; text-align:left; color:#666; float:right;}
	
	#bigGlass{height:2.1875rem; position:absolute; background-color:#FFFBE5; border:.0625rem #2D8DCF solid; display:none; line-height:35px; font-size:1.125rem; color:#F79209; padding:0 1rem 0 1rem;}
	#bigGlass span{margin-left:8px;}
	#bigGlass span:first-child{margin-left:0;}
	
	.m_form_button{margin:1.25rem 0;}
	</style>
	<div id="main" class="w">
		<div id="creditcart">
			<div class="orderinfo">
				<dl>
					<dt class="ui_border_b"><?=$c['lang_pack']['cart']['orderInfo'];?></dt>
					<dd>
						<div>
							<table id="contexttable" width="295" cellspacing="0" cellpadding="0" border="0" style="font-size:12px;">
								<tr>
									<td><strong><?=$c['lang_pack']['cart']['orderNo'];?>:</strong></td> 
									<td><span><?=$pay_data['order_row']['OId'];?></span></td>                 
								</tr>
								<tr height="25">
									<td><strong><?=$c['lang_pack']['cart']['amount'];?>:</strong></td>
									<td><span><?=$pay_data['order_row']['Currency'].' '.cart::iconv_price(0, 1, $pay_data['order_row']['Currency']).$pay_data['total_price'];?></span></td>
								</tr>
							</table>
						</div>
						<div>
							<p style="margin:10px 0;"><?=$c['lang_pack']['cart']['paymentInfo'];?></p>
						</div>
					</dd>
				</dl>
			</div>
			<div class="payinfo ui_border_t">
				<form id="payForm" name="payForm" action="" method="post" onSubmit="return checkPay();">
					<div class="payinfo_bd">
						<div class="rows">
							<label class="field"><?=$c['lang_pack']['cart']['CardNo'];?> :</label>
							<div class="input clean">
								<div class="inside_input"><input type="text" name="CardNo" id="CardNo" maxlength="16" class="info_right" value="" /><div class="img"><img src="/static/themes/default/images/cart/sp_checkout/vsia_ico.jpg" /><img style="margin-left:5px;" src="/static/themes/default/images/cart/sp_checkout/mastercard.jpg" /><img src="/static/themes/default/images/cart/sp_checkout/jcb.gif" /></div></div>
							</div>
						</div>
						<div class="rows">
							<label class="field"><?=$c['lang_pack']['cart']['expirationDate'];?> :</label>
							<div class="input clean">
								<div class="box">
									<div class="box_select">
										<select name="CardExpireMonth" id="CardExpireMonth" notnull>
											<option value=""><?=$c['lang_pack']['cart']['month'];?></option>
											<option value="01">1-January</option>
											<option value="02">2-February</option>
											<option value="03">3-March</option>
											<option value="04">4-April</option>
											<option value="05">5-May</option>
											<option value="06">6-June</option>
											<option value="07">7-July</option>
											<option value="08">8-August</option>
											<option value="09">9-September</option>
											<option value="10">10-October</option>
											<option value="11">11-November</option>
											<option value="12">12-December</option>
										</select>
									</div>
								</div>
								<div class="box">
									<div class="box_select">
										<select name="CardExpireYear" id="CardExpireYear" notnull>
											<option value=""><?=$c['lang_pack']['cart']['year'];?></option>
											<?php 
											for($i=0; $i<10; ++$i){
												$y=$year+$i;
											?>
												<option value="<?=$y;?>"><?=$y;?></option>
											<?php }?>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="rows">
							<label class="field"><?=$c['lang_pack']['cart']['CVV2'];?> :</label>
							<div class="input clean">
								<div class="inside_input"><input type="password" name="CardSecurityCode" id="CardSecurityCode" size="8" maxlength="3" /><div class="img"><img src="/static/themes/default/images/cart/sp_checkout/cvv_ico.jpg" id="showCard" /></div></div>
							</div>
						</div>
						<?php if($IssuingBank){?>
							<div class="rows">
								<label class="field"><?=$c['lang_pack']['cart']['issuingBank'];?> :</label>
								<div class="input clean">
									<input type="text" name="IssuingBank" class="box_input info_right" id="IssuingBank" maxlength="100" value="" />
								</div>
							</div>
						<?php }?>
					</div>
					<div class="address_info ui_border_tb">
						<div class="title clean"><?=$c['lang_pack']['cart']['yBillAddress'];?></div>
						<div class="txt ui_border_b">
							<div class="rows clean">
								<strong><?=$c['lang_pack']['cart']['pName'];?> :</strong>
								<span><?=$pay_data['order_row']['BillFirstName'].' '.$pay_data['order_row']['BillLastName'];?></span>
							</div>
							<div class="rows clean">
								<strong><?=$c['lang_pack']['cart']['pAddress'];?> :</strong>
								<span><?=$pay_data['order_row']['BillAddressLine1'].' '.$pay_data['order_row']['BillAddressLine2'].', '.$pay_data['order_row']['BillCity'].', '.$pay_data['order_row']['BillState'].' ( '.$pay_data['order_row']['BillCountry'].' )';?><br /><?=$pay_data['order_row']['BillZipCode'];?></span>
							</div>
							<div class="rows clean">
								<strong><?=$c['lang_pack']['cart']['pPhoneNo'];?> :</strong>
								<span><?=$pay_data['order_row']['BillCountryCode'].' '.$pay_data['order_row']['BillPhoneNumber'];?></span>
							</div>
						</div>
						<div class="title clean"><?=$c['lang_pack']['cart']['yShipAddress'];?></div>
						<div class="txt ui_border_b">
							<div class="rows clean">
								<strong><?=$c['lang_pack']['cart']['pName'];?> :</strong>
								<span><?=$pay_data['order_row']['ShippingFirstName'].' '.$pay_data['order_row']['ShippingLastName'];?></span>
							</div>
							<div class="rows clean">
								<strong><?=$c['lang_pack']['cart']['pAddress'];?> :</strong>
								<span><?=$pay_data['order_row']['ShippingAddressLine1'].' '.$pay_data['order_row']['ShippingAddressLine2'].', '.$pay_data['order_row']['ShippingCity'].', '.$pay_data['order_row']['ShippingState'].' ( '.$pay_data['order_row']['ShippingCountry'].' )';?><br /><?=$pay_data['order_row']['ShippingZipCode'];?></span>
							</div>
							<div class="rows clean">
								<strong><?=$c['lang_pack']['cart']['pPhoneNo'];?> :</strong>
								<span><?=$pay_data['order_row']['ShippingCountryCode'].' '.$pay_data['order_row']['ShippingPhoneNumber'];?></span>
							</div>
						</div>
						<input type="submit" name="btn_submit" value="<?=$c['lang_pack']['cart']['makePayment'];?>" class="btn_global m_form_button BuyNowBgColor" />
					</div>
				</form>
			</div>
		</div>
	</div>
	<script type="text/javascript">
	function checkPay(){
		var CardNo=$('input[name=CardNo]'),
			CardExpireMonth=$('select[name=CardExpireMonth]'),
			CardExpireYear=$('select[name=CardExpireYear]'),
			CardSecurityCode=$('input[name=CardSecurityCode]'),
			IssuingBank=$('input[name=IssuingBank]');
			
		$('input:text').removeClass('null').next('p.error').hide();
		
		if(CardNo.val()=='' || isNaN(CardNo.val()) || CardNo.val().length!=16 ){
			CardNo.addClass('null');
			$('body,html').animate({scrollTop:CardNo.offset().top-80}, 500);
			$('html').tips_box('Card Number can not be null, it must be numeric, the length must be 16.', 'error');
			return false;
		}
		if(CardExpireMonth.val()=='' || CardExpireMonth.val().length==0){
			$('body,html').animate({scrollTop:CardExpireMonth.offset().top-80}, 500);
			$('html').tips_box('Card Expire Month can not be null.', 'error');
			return false;
		}
		if(CardExpireYear.val()=='' || CardExpireYear.val().length==0){
			$('body,html').animate({scrollTop:CardExpireYear.offset().top-80}, 500);
			$('html').tips_box('Card Expire Year can not be null.', 'error');
			return false;
		}
		if(CardSecurityCode.val()=='' || isNaN(CardSecurityCode.val()) || CardSecurityCode.val().length!=3){ 
			CardSecurityCode.addClass('null');
			$('body,html').animate({scrollTop:CardSecurityCode.offset().top-80}, 500);
			$('html').tips_box('CVV2/CSC is incorrect, it must be numeric, the lenght must be 3!', 'error');
			return false;
		}
		if(IssuingBank.length && IssuingBank.val()==''){ 
			IssuingBank.addClass('null');
			$('body,html').animate({scrollTop:IssuingBank.offset().top-80}, 500);
			$('html').tips_box('Issuing Bank can not be null!', 'error');
			return false;
		}
		return true;
	}
	</script>
<?php }?>
<?php include("{$c['theme_path']}/inc/footer.php");?>
</body>
</html>
