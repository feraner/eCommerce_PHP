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
		js::location("/cart/complete/{$OId}.html");//返回线下支付页面
	}
	
	if($order_row['OrderStatus']==3 && !$_SESSION['Cart']['PaymentError']){//支付错误，发送站内信
		$log_row=db::get_one('orders_log', "OrderId='{$order_row['OrderId']}' and OrderStatus=3", '*', 'LId desc');
		$Content=$log_row['Log'];
		$Content=str_replace('<br />', "\r\n", $Content);
		$data=array(
			'UserId'	=>	0,
			'Subject'	=>	"{$c['orders']['status'][3]} ({$payment_row['Method']}) ({$OId})",
			'Content'	=>	addslashes(stripslashes($Content)),
			'IsRead'	=>	0,
			'AccTime'	=>	$c['time']
		);
		db::insert('user_message', $data);
		$_SESSION['Cart']['PaymentError']=1;//防止重复刷新提交
	}
?>
<script type="text/javascript">
$(document).ready(function(){
	cart_obj.complete_init();
	var iTime=0;
	var checkSuccess=setInterval(function(){//每1秒钟之后，重新获取一次订单最新的状态，持续到3秒钟后自动终止
		$.get('/cart/success/<?=$OId;?>.html');
		++iTime;
		if(iTime>3){
			clearInterval(checkSuccess);
		}
	}, 1000);
});
</script>
<div id="lib_cart">
	<div class="position"><strong><?=$c['lang_pack']['cart']['position'];?>: </strong> <a href="/"><?=$c['lang_pack']['cart']['home'];?></a> &gt; <a href="/cart/"><?=$c['lang_pack']['cart']['cart'];?></a> &gt; <strong><?=$c['lang_pack']['cart']['complete'];?></strong></div>
    <div class="blank12"></div>
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
					<span class="red"><?=cart::iconv_price(0, 1, $order_row['Currency']).' '.orders::orders_price($order_row);?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label><?=$c['lang_pack']['cart']['totalqty'];?>:</label>
					<span><?=(int)db::get_sum('orders_products_list', "OrderId='{$order_row['OrderId']}'", 'Qty');?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label><?=$c['lang_pack']['cart']['status'];?>:</label>
					<span><?=$c['orders']['status'][($order_row['OrderStatus']<=2?2:$order_row['OrderStatus'])];?></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label><?=$c['lang_pack']['cart']['paymethod'];?>:</label>
					<span><?=$order_row['PaymentMethod'];?></span>
					<div class="clear"></div>
				</div>
				<?php
				if($order_row['OrderStatus']==3){
					$log_row=db::get_one('orders_log', "OrderId='{$order_row['OrderId']}' and OrderStatus=3", '*', 'AccTime desc');
					$payment_row=db::get_all('payment', "IsUsed=1 and PId!=2", '*', 'IsOnline desc,'.$c['my_order'].'PId asc');
				?>
					<form name="pay_edit_form" method="post" action="/account/">
						<div class="rows">
							<label><?=$c['lang_pack']['mobile']['message'];?>:</label>
							<span><?=$log_row['Log'];?></span>
							<div class="clear"></div>
						</div>
						<div class="blank15"></div>
						<div class="rows">
							<select name="PId">
								<?php
								foreach($payment_row as $v){
									if($v['MaxPrice']>0?($total_price<$v['MinPrice'] || $total_price>$v['MaxPrice']):($total_price<$v['MinPrice'])) continue;
								?>
									<option value="<?=$v['PId'];?>" fee="<?=$v['AdditionalFee'];?>" affix="<?=$v['AffixPrice'];?>"<?=$order_row['PId']==$v['PId']?' selected':'';?>><?=$v['Name'.$c['lang']];?></option>
								<?php }?>
							</select>
						</div>
						<div class="blank15"></div>
						<div class="rows">
							<label></label>
							<div id="pay_button" class="textbtn"><?=$c['lang_pack']['cart']['nextPay'];?></div>
							<div class="clear"></div>
						</div>
						<input type="hidden" name="OId" value="<?=$OId;?>" />
						<input type="hidden" name="TotalPrice" value="<?=$total_price;?>" />
						<input type="hidden" name="BackLocation" value="/cart/complete/<?=$OId;?>.html" />
						<input type="hidden" name="Symbols" value="<?=cart::iconv_price($total_price, 1, $orders_row['Currency']);?>" currency="<?=$orders_row['Currency'];?>" />
					</form>
				<?php }?>
			</div>
			<div class="foot">
				<div class="what fl"><b><?=$c['lang_pack']['cart']['whatNext'];?>?</b><a href="/"><?=$c['lang_pack']['cart']['returnHome'];?></a>|<a href="/account/orders/view<?=$OId;?>.html"><?=$c['lang_pack']['cart']['returnTo'];?></a></div>
				<div class="contact fr"><?=$c['lang_pack']['contactUs'];?>: <a href="mailto:<?=$c['config']['global']['AdminEmail'];?>"><?=$c['config']['global']['AdminEmail'];?></a></div>
			</div>
        </div>
    </div>
    <div class="blank12"></div>
</div>