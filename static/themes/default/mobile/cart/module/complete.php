<?php !isset($c) && exit();?>
<?php
$OId=trim($_GET['OId']);
!$OId && js::location('/cart/', '', '.top');

$order_row=db::get_one('orders', "OId='$OId'");	
//订单不存在
(!$order_row) && js::location('/', '', '.top');
//会员订单，未登录不允许查看
((int)$order_row['UserId'] && !(int)$_SESSION['User']['UserId']) && js::location("/account/?JumpUrl=".urlencode("/cart/complete/{$OId}.html"), '', '.top');
//当前会员非订单会员
((int)$order_row['UserId'] && (int)$order_row['UserId']!=(int)$_SESSION['User']['UserId']) && js::location("/account/", '', '.top');
//会员订单发货后状态在会员中心查询
((int)$_SESSION['User']['UserId'] && (int)$order_row['OrderStatus']>4) && js::location("/account/orders/view{$OId}.html", '', '.top');
//订单总金额低于等于0
$total_price=sprintf('%01.2f', orders::orders_price($order_row, 1));
if($total_price<=0 && (int)$order_row['OrderStatus']<4){
	if((int)$order_row['OrderStatus']==1){//更改为“待确认”状态
		$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
		$Log='Update order status from '.$c['orders']['status'][1].' to '.$c['orders']['status'][2];
		db::update('orders', "OId='$OId'", array('OrderStatus'=>2));
		orders::orders_log($order_row['UserId'], $UserName, $order_row['OrderId'], 2, $Log);
	}
	js::location("/cart/success/{$OId}.html");
}

$payment_row=db::get_one('payment', "PId='{$order_row['PId']}'");
if($payment_row['IsOnline']==1 && (int)$order_row['OrderStatus']<4){//支付方式为在线付款，并且状态为未付款
	include("{$c['default_path']}cart/module/payment.php");
	exit();
}

$payOffline=($payment_row['IsOnline']!=1 && $order_row['OrderStatus']!=1 && $order_row['OrderStatus']!=3)?0:1;
?>
<script type="text/javascript">$(function(){cart_obj.complete()});</script>
<div id="cart">
	<?=html::mobile_cart_step(1);?>
    <div class="complete_box">
    	<div class="complete_tips"><?=$c['lang_pack']['mobile']['received_txt'];?></div>
        <?php
		if((int)$payOffline && $payment_row['PId']!=9){	//线下支付，并且非货到付款
			$currency_row=db::get_all('currency', "IsUsed='1'");
		?>
			<div class="pay_info">
				<div class="title"><?=$c['lang_pack']['mobile']['order_summary'];?></div>
				<div class="rows">
					<strong><?=$c['lang_pack']['mobile']['order_num'];?>:</strong>
					<span>#<?=$OId;?></span>
				</div>
				<div class="rows">
					<strong><?=$c['lang_pack']['mobile']['total_amonut'];?>:</strong>
					<span><?=$_SESSION['Currency']['Symbol'].' '.cart::iconv_price(orders::orders_price($order_row, 1, 1), 2);?></span>
				</div>
				<div class="rows">
					<strong><?=$c['lang_pack']['mobile']['num_of_item'];?>:</strong>
					<span><?=(int)db::get_sum('orders_products_list', "OrderId='{$order_row['OrderId']}'", 'Qty');?></span>
				</div>
				<div class="rows">
					<strong><?=$c['lang_pack']['mobile']['order_status'];?>:</strong>
					<span><?=$c['orders']['status'][$order_row['OrderStatus']];?></span>
				</div>
				<div class="rows">
					<strong><?=$c['lang_pack']['mobile']['pay_method'];?>:</strong>
					<span><?=$order_row['PaymentMethod'];?></span>
				</div>
			</div>
			<div class="payment_info"><?=$payment_row['Description'.$c['lang']];?></div>
			<form method="post" action="?" class="pay_form" id="pay_form">
				<?php if($payment_row['Method']=='WesternUnion'){?>
					<div class="rows">
						<div class="field"><?=$c['lang_pack']['mobile']['first_name'];?>:</div>
						<div class="input clean"><input type="text" class="box_input" name="FirstName" notnull /></div>
					</div>
					<div class="rows">
						<div class="field"><?=$c['lang_pack']['mobile']['last_name'];?>:</div>
						<div class="input clean"><input type="text" class="box_input" name="LastName" notnull /></div>
					</div>
					<div class="rows">
						<div class="field"><?=$c['lang_pack']['mobile']['currency'];?>:</div>
						<div class="input clean">
							<div class="box_select">
								<select class="addr_select" name="Currency" notnull>
									<?php
									$currency_row=db::get_all('currency', "IsUsed='1'");
									foreach((array)$currency_row as $v){
									?>
									<option value="<?=$v['Currency'];?>" <?=$_SESSION['Currency']['Currency']==$v['Currency']?'selected':'';?>><?=$v['Currency'];?></option>
									<?php }?>
								</select>
							</div>
						</div>
					</div>
					<div class="rows">
						<div class="field"><?=$c['lang_pack']['mobile']['sent_money'];?>:</div>
						<div class="input clean">
							<span class="input_span"><input type="text" class="box_input" name="SentMoney" maxlength="8" notnull /></span>
						</div>
					</div>
					<div class="rows">
						<div class="field"><?=$c['lang_pack']['mobile']['mtcn_no'];?>:</div>
						<div class="input clean">
							<span class="input_span"><input type="text" class="box_input" name="MTCNNumber" placeholder="<?=$c['lang_pack']['mobile']['10_digits'];?>" maxlength="10" format="Length|10" notnull /></span>
						</div>
					</div>
					<div class="rows">
						<div class="field"><?=$c['lang_pack']['mobile']['country'];?>:</div>
						<div class="input clean">
							<div class="box_select">
								<select class="addr_select" name="Country" notnull>
									<option value=""><?=$c['lang_pack']['mobile']['plz_country'];?>---</option>
									<?php 
										$country_row=str::str_code(db::get_all('country', "IsUsed=1", '*', 'Country asc'));
										foreach($country_row as $v){
									?>
										<option value="<?=$v['CId'];?>"><?=$v['Country'];?></option><?php /*?> <?=$v['IsDefault']==1?'selected':'';?><?php */?>
									<?php }?>
								</select>
							</div>
						</div>
					</div>
					<div class="rows">
						<div class="field"><?=$c['lang_pack']['mobile']['contents'];?>:</div>
						<div class="input clean">
							<span class="input_span"><textarea name="Contents" class="box_input box_textarea"></textarea></span>
						</div>
					</div>
				<?php }elseif($payment_row['Method']=='MoneyGram' || $payment_row['Method']=='TT' || $payment_row['Method']=='BankTransfer'){?>
					<div class="rows">
						<div class="field"><?=$c['lang_pack']['mobile']['send_name'];?>:</div>
						<div class="input clean">
							<span class="input_span fl whalf"><input type="text" class="box_input" placeholder="First Name" name="FirstName" notnull /></span>
							<span class="input_span fr whalf"><input type="text" class="box_input" placeholder="Last Name" name="LastName" notnull /></span>
						</div>
					</div>
					<div class="rows">
						<div class="field"><?=$c['lang_pack']['mobile']['currency'];?>:</div>
						<div class="input clean">
							<div class="box_select">
								<select class="addr_select" name="Currency" notnull>
									<?php
									$currency_row=db::get_all('currency', "IsUsed='1'");
									foreach((array)$currency_row as $v){
									?>
									<option value="<?=$v['Currency'];?>" <?=$_SESSION['Currency']['Currency']==$v['Currency']?'selected':'';?>><?=$v['Currency'];?></option>
									<?php }?>
								</select>
							</div>
						</div>
					</div>
					<div class="rows">
						<div class="field"><?=$c['lang_pack']['mobile']['sent_money'];?>:</div>
						<div class="input clean">
							<span class="input_span"><input type="text" class="box_input" name="SentMoney" maxlength="8" notnull /></span>
						</div>
					</div>
					<div class="rows">
						<div class="field"><?=$c['lang_pack']['mobile']['refer_num'];?>:</div>
						<div class="input clean">
							<span class="input_span"><input type="text" class="box_input" name="MTCNNumber" placeholder="<?=$c['lang_pack']['mobile']['8_digits'];?>" maxlength="8" format="Length|8" notnull /></span>
						</div>
					</div>
					<div class="rows">
						<div class="field"><?=$c['lang_pack']['mobile']['contents'];?>:</div>
						<div class="input clean">
							<span class="input_span"><textarea name="Contents" class="box_input box_textarea"></textarea></span>
						</div>
					</div>
				<?php }?>
				<div class="pay_button">
					<span class="btn_global btn btn_pay BuyNowBgColor" id="paybtn"><?=$c['lang_pack']['mobile']['submit'];?></span>
					<a class="btn_global btn btn_view_order" href="/account/orders/view<?=$OId?>.html"><?=$c['lang_pack']['mobile']['view_order'];?></a>
				</div>
				<input type="hidden" name="PaymentMethod" value="<?=$payment_row['Method'];?>" />
				<input type="hidden" name="OId" value="<?=$order_row['OId'];?>" />
			</form>
			<div class="blank15"></div>
        <?php }else{?>
       		<a href="/account/orders/view<?=$OId?>.html" class="btn_global btn_view_order"><?=$c['lang_pack']['mobile']['view_order'];?></a>
        <?php }?>
    </div>
</div>