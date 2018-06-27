<?php !isset($c) && exit();?>
<?php
$OId=trim($_GET['OId']);
!$OId && js::location('/cart/');

$order_row=db::get_one('orders', "OId='$OId'");	
//订单不存在
(!$order_row) && js::location('/');
//会员订单，未登录不允许查看
((int)$order_row['UserId'] && !(int)$_SESSION['User']['UserId']) && js::location('/account/login.html?JumpUrl='.urlencode("/cart/success/{$OId}.html"));
//当前会员非订单会员
((int)$order_row['UserId'] && (int)$order_row['UserId']!=(int)$_SESSION['User']['UserId']) && js::location("/account/");
//支付方式为在线付款，并且状态为未付款
//((int)$_SESSION['User']['UserId'] && (int)$order_row['OrderStatus']<4) && js::location("/cart/complete/{$OId}.html");

$total_price=sprintf('%01.2f', orders::orders_price($order_row, 1));
$payment_row=db::get_one('payment', "PId='{$order_row['PId']}'");
if($total_price>0 && !$payment_row['IsOnline']){//线下支付
	js::location("/cart/complete/{$OId}.html", '', '.top');//返回线下支付页面
}
?>
<script type="text/javascript">
$(document).ready(function(){
	cart_obj.complete();
	<?php if($order_row['OrderStatus']<4){ //付款尚未成功，自动检测订单状态的更新?>
		var iTime=0;
		var checkSuccess=setInterval(function(){//每1秒钟之后，重新获取一次订单最新的状态，持续到3秒钟后自动终止
			$.get('/cart/success/<?=$OId;?>.html');
			++iTime;
			if(iTime>3){
				clearInterval(checkSuccess);
			}
		}, 1000);
	<?php }?>
});
</script>
<div id="cart">
	<?=html::mobile_cart_step(2);?>
    <div class="success">
        <div class="success_info">
        	<div class="hd<?=$order_row['OrderStatus']==3?' hd_error':'';?>"><h3><?=$c['lang_pack']['cart']['paySent'];?></h3></div>
			<div class="bd">
				<div class="rows title">
					<label>
						<strong>
							<?php
							switch($order_row['OrderStatus']){
								case 1: echo $c['lang_pack']['cart']['waitingfully']; break;
								case 2: echo $c['lang_pack']['cart']['waitingfully']; break;
								case 3: echo $c['lang_pack']['cart']['errorfully']; break;
								case 4: echo $c['lang_pack']['cart']['successfully']; break;
							}?>
						</strong>
					</label>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label><?=$c['lang_pack']['cart']['orderNo'];?></label>
					<span class="red"><?=$OId;?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label><?=$c['lang_pack']['cart']['totalamount'];?>:</label>
					<span class="red"><?=cart::iconv_price(0, 1, $order_row['Currency']).orders::orders_price($order_row);?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label><?=$c['lang_pack']['cart']['totalqty'];?>:</label>
					<span><?=(int)db::get_sum('orders_products_list', "OrderId='{$order_row['OrderId']}'", 'Qty');?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label><?=$c['lang_pack']['cart']['status'];?>:</label>
					<span><?=$c['lang_pack']['user']['OrderStatusAry'][($order_row['OrderStatus']<=2?2:$order_row['OrderStatus'])];?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label><?=$c['lang_pack']['cart']['paymethod'];?>:</label>
					<span><?=$order_row['PaymentMethod'];?></span>
					<div class="clear"></div>
				</div>
				<?php if($order_row['OrderStatus']==3){?>
					<div class="blank15"></div>
					<div class="rows">
						<label></label>
						<span><a href="/cart/complete/<?=$OId;?>.html" class="btn_global textbtn BuyNowBgColor"><?=$c['lang_pack']['cart']['nextPay'];?></a></span>
						<div class="clear"></div>
					</div>
				<?php }?>
			</div>
			<div class="foot">
				<div class="what">
					<a href="/"><?=$c['lang_pack']['cart']['returnHome'];?></a>
					<a href="/account/orders/view<?=$OId;?>.html"><?=$c['lang_pack']['cart']['returnTo'];?></a>
				</div>
			</div>
        </div>
    </div>
    <div class="blank12"></div>
</div>