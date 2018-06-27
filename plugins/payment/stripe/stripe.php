<?php $pay_data=$data; //转换一下，防止其他地方已经调用这一变量?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Stripe</title>
<style type="text/css">
.StripeElement {
  background-color: white;
  padding: 10px 12px;
  border-radius: 4px;
  border: 1px solid transparent;
  box-shadow: 0 1px 3px 0 #e6ebf1;
  -webkit-transition: box-shadow 150ms ease;
  transition: box-shadow 150ms ease;
}
.StripeElement--focus {
  box-shadow: 0 1px 3px 0 #cfd7df;
}
.StripeElement--invalid {
  border-color: #fa755a;
}
.StripeElement--webkit-autofill {
  background-color: #fefde5 !important;
}
<?php if($is_mobile){?>
#creditcart{width:90%; margin:0 auto; overflow:hidden;}
#creditcart .orderinfo{width:100%; margin:.625rem 0; overflow:hidden;}
#creditcart .orderinfo dt{color:#C88039; font-size:.75rem; font-weight:bold; padding-left:1rem; margin:1.25rem 0;}
#creditcart .orderinfo dd{border-radius:.5rem; border:.0625rem #bbb solid; box-shadow:.0625rem .125rem .1875rem rgba(0, 0, 0, 0.5);}
#creditcart .orderinfo dd>div{width:90%; margin:0 auto;}
#creditcart .orderinfo p{font-size:.75rem; line-height:1rem;}
#creditcart .payinfo{width:99%; height:auto; overflow:hidden; border-radius:8px; border:.0625rem solid #bbbbbb; box-shadow:.0625rem .125rem .1875rem rgba(0, 0, 0, 0.5); background:url(/static/themes/default/images/cart/sp_checkout/bg_x.gif.jpg); background-repeat:repeat-x; margin:.625rem 0 0 0;}
#creditcart .payinfo table, #creditcart .payinfo table td, #creditcart .payinfo table strong{font-size:.75rem; line-height:1.25rem;}
#creditcart .payinfo_bd{width:90%; height:auto; border:.0625rem solid #dedede; border-radius:8px; background:#fff; margin:0 auto; margin-top:1.25rem; margin-bottom:1.25rem;}
#creditcart .payinfo_bd_oth{width:90%; height:auto; margin:.625rem; font-size:.75rem;}
#creditcart table{width:100%; margin:0 auto; padding:0.6em; text-decoration:none; display:inline-table;}

#contexttable td{height:1.125rem;}
#contexttable td>span{color:#c93; padding-left:1.25rem;}
#contexttable td:first-child{width:40%; text-align:right;}
#contexttable td, #contexttable td>strong, #contexttable td>span{font-size:.75rem;}

.content{width:350px; overflow:hidden; border:.0625rem #ddd solid;}
.info_left{font:12px/20px Verdana; color:#666; width:40%; vertical-align:top; line-height:1.25rem;}
.info_right{width:155px; border:.0625rem #6CF solid; float:left; margin:.3125rem .625rem .3125rem 0; height:1.25rem; font:.75rem/1.25rem Verdana; color:#666;}
#CardSecurityCode{width:80px; border:.0625rem #6CF solid; float:left; margin:.3125rem .625rem .3125rem 0; height:1.25rem; font:.75rem/1.25rem Verdana; color:#666;}
#bigGlass{height:2.1875rem; position:absolute; background-color:#FFFBE5; border:.0625rem #2D8DCF solid; display:none; line-height:35px; font-size:1.125rem; color:#F79209; padding:0 1rem 0 1rem;}
#bigGlass span{margin-left:8px;}
#bigGlass span:first-child{margin-left:0;}
select{width:80px; height:1.25rem; border:.0625rem #bbb solid; float:left; margin:.3125rem .625rem .3125rem 0; font:.75rem/1.25rem Verdana; color:#666;}
<?php }else{?>
#creditcart{width:572px; margin:0 auto; overflow:hidden;}
#creditcart .orderinfo{width:560px; margin:10px 0; overflow:hidden;}
#creditcart .orderinfo dt{color:#C88039; font-size:16px; font-weight:bold; padding-left:15px; margin:20px 0;}
#creditcart .orderinfo dd{border-radius:8px; border:1px #bbb solid; box-shadow:1px 2px 3px rgba(0, 0, 0, 0.5);}
#creditcart .orderinfo dd>div{width:522px; margin:0 auto;}
#creditcart .payinfo{width:560px; height:auto; overflow:hidden; border-radius:8px; border:1px solid #bbbbbb; box-shadow:1px 2px 3px rgba(0, 0, 0, 0.5); background:url(/static/themes/default/images/cart/sp_checkout/bg_x.gif.jpg); background-repeat:repeat-x; margin:10px 0 0 0;}
#creditcart .payinfo_bd{width:520px; height:auto; border:1px solid #dedede; border-radius:8px; background:#fff; margin:0 auto; margin-top:20px; margin-bottom:20px;}
#creditcart .payinfo_bd_oth{width:295px; height:auto; margin:10px; font-size:12px;}
#creditcart .payinfo_bd_oth table td{line-height:20px;}
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
<?php }?>
</style>
<?php if(!$is_mobile) include("{$c['static_path']}/inc/static.php");?>
<script src="https://js.stripe.com/v3/"></script>
<script>
$(function(){
	var stripe = Stripe('<?=$pay_data['account']['Publishable_key']?>');
	var elements = stripe.elements();
	var card = elements.create('card');
	card.mount('#card-element');
	var form = document.getElementById('payForm');
	form.addEventListener('submit', function(event) {
	  event.preventDefault();
	  stripe.createToken(card).then(function(result) {
		if (result.error) {
			
		} else {
		  stripeTokenHandler(result.token);
		}
	  });
	});
	function stripeTokenHandler(token) {
	  var form = document.getElementById('payForm');
	  var hiddenInput = document.createElement('input');
	  hiddenInput.setAttribute('type', 'hidden');
	  hiddenInput.setAttribute('name', 'stripeToken');
	  hiddenInput.setAttribute('value', token.id);
	  form.appendChild(hiddenInput);
	  document.getElementById('form_submit').disabled=true;
	  // Submit the form
	  form.submit();
	}	
	
})
</script>
</head>
<body>
	<?php if(!$is_mobile){?>
		<?php include("{$c['theme_path']}/inc/header.php");?>
        <script>$('#payment_loading').remove();</script>
        <div class="blank25"></div>
    <?php }?>
    <!--main start-->
    <div id="main" class="w">
        <div id="creditcart">
            <div class="orderinfo">
                <dl>
                    <dt>Order Information:</dt>
                    <dd>
                        <div style="padding-top:10px;">
                            <table id="contexttable" width="295" cellspacing="0" cellpadding="0" border="0" style="font-size:12px;">
                                <tr>
                                    <td><strong>Order Number:</strong></td> 
                                    <td><span><?=$pay_data['order_row']['OId'];?></span></td>                 
                                </tr>
                                <tr height="25">
                                    <td><strong>Amount:</strong></td>
                                    <td><span><?=$pay_data['order_row']['Currency'].' '.$pay_data['total_price'];?></span></td>
                                </tr>
                            </table>
                        </div>
                        <div style="margin-bottom:20px; border-top:1px #000 solid;">
                            <p style="margin:10px 0;">Once your order has been completed successfully you will receive a confirmation email. Your order details will be securely transmitted. Due to exchange rates, the amount billed can vary slightly. Thank you very much for shopping from our shop</p>
                        </div>
                    </dd>
                </dl>
            </div>
            <div class="payinfo">
                <div class="payinfo_bd">
                    <div class="payinfo_bd_oth">
                        <div style="font-size:12px; padding-top:10px;">
                            <table>
                                <form id="payForm" name="payForm" action="" method="post" <?php /*?>onSubmit="return checkPay();"<?php */?>>
                                    <tr>
                                        <td colspan="2"><img src="/static/themes/default/images/cart/sp_checkout/vsia_ico.jpg" /><img style="margin-left:5px;" src="/static/themes/default/images/cart/sp_checkout/mastercard.jpg" /><img src="/static/themes/default/images/cart/sp_checkout/jcb.gif" /></td>
                                    </td>
                                    <tr>
                                        <td height="20" colspan="2">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td id="card-element" colspan="2"></td>
                                    </tr>
                                    <tr>
                                        <td height="20" colspan="2">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td class="info_left" colspan="2"><strong style="padding:12px 0;"><img src="/static/themes/default/images/cart/sp_checkout/small_menu.gif">&nbsp;&nbsp;Your Billing Address .</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="info_left" valign="top">Name: </td>
                                        <td><?=$pay_data['order_row']['BillFirstName'].' '.$pay_data['order_row']['BillLastName'];?></td>
                                    </tr>
                                    <tr>
                                        <td class="info_left" valign="top">Address: </td>
                                        <td><?=$pay_data['order_row']['BillAddressLine1'].' '.$pay_data['order_row']['BillAddressLine2'].', '.$pay_data['order_row']['BillCity'].', '.$pay_data['order_row']['BillState'].' ( '.$pay_data['order_row']['BillCountry'].' )';?><br /><?=$pay_data['order_row']['BillZipCode'];?></td>
                                    </tr>
                                    <tr>
                                        <td class="info_left" valign="top">Phone Number: </td>
                                        <td><?=$pay_data['order_row']['BillCountryCode'].' '.$pay_data['order_row']['BillPhoneNumber'];?></td>
                                    </tr>
                                    <tr>
                                        <td height="20" colspan="2">&nbsp;</td>
                                    </tr>
                                    <tr>
                                        <td class="info_left" colspan="2"><strong style="padding:8px 0;"><img src="/static/themes/default/images/cart/sp_checkout/small_menu.gif">&nbsp;&nbsp;Your Shipping Address .</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="info_left" valign="top">Name: </td>
                                        <td><?=$pay_data['order_row']['ShippingFirstName'].' '.$pay_data['order_row']['ShippingLastName'];?></td>
                                    </tr>
                                    <tr>
                                        <td class="info_left" valign="top">Address: </td>
                                        <td><?=$pay_data['order_row']['ShippingAddressLine1'].' '.$pay_data['order_row']['ShippingAddressLine2'].', '.$pay_data['order_row']['ShippingCity'].', '.$pay_data['order_row']['ShippingState'].' ( '.$pay_data['order_row']['ShippingCountry'].' )';?><br /><?=$pay_data['order_row']['ShippingZipCode'];?></td>
                                    </tr>
                                    <tr>
                                        <td class="info_left" valign="top">Phone Number: </td>
                                        <td><?=$pay_data['order_row']['ShippingCountryCode'].' '.$pay_data['order_row']['ShippingPhoneNumber'];?></td>
                                    </tr>
                                    <tr>
                                        <td></td>
                                        <td><br /><br /><input id="form_submit" type="image" name="btn_submit" src="/static/themes/default/images/cart/sp_checkout/btn.png"></td>
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
    <?php if(!$is_mobile) include("{$c['theme_path']}/inc/footer.php");?>
</body>
</html>