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
	
	$isFee=($order_row['OrderStatus']>=4 && $order_row['OrderStatus']!=7)?1:0;
	$total_price=sprintf('%01.2f', orders::orders_price($order_row, $isFee));
	$payment_row=db::get_one('payment', "PId='{$order_row['PId']}'");
	/*if($total_price>0 && !$payment_row['IsOnline']){//线下支付
		js::location("/cart/complete/{$OId}.html");//返回线下支付页面
	}*/
	
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
<style type="text/css">
.information_payment .icon_shipping_title{background-color:<?=$style_data['BuyNowBgColor'];?>;}
</style>
<script type="text/javascript">
$(document).ready(function(){
	cart_obj.success_init();
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
<div id="lib_cart" class="wide success_container">
	<div class="success_info">
		<?php
		if($order_row['OrderStatus']==1 || $order_row['OrderStatus']==2){
			//支付等待
		?>
			<div class="hd">
				<i class="icon_success_status await"></i>
				<h3><?=$c['lang_pack']['cart']['sStatusAwait'];?></h3>
				<div class="note"><?=str_replace('%OId%', $OId, $c['lang_pack']['cart']['sOrderNumber']);?><a href="/account/orders/view<?=$OId;?>.html" class="btn_detail"><?=$c['lang_pack']['detail'];?></a></div>
			</div>
			<div class="bd">
				<div class="await_info">
					<p><?=$c['lang_pack']['cart']['sAwaitTips1'];?></p>
					<p><?=$c['lang_pack']['cart']['sAwaitTips2'].': '.$c['config']['global']['AdminEmail'];?></p>
					<a href="/" class="btn_return_home"><?=$c['lang_pack']['cart']['sReturnHome'];?></a>
				</div>
			</div>
		<?php
		}elseif($order_row['OrderStatus']==3){
			//支付失败
			$log_row=db::get_one('orders_log', "OrderId='{$order_row['OrderId']}' and OrderStatus=3", '*', 'AccTime desc');
			$payment_row=db::get_all('payment', "IsUsed=1 and PId!=2", '*', 'IsOnline desc,'.$c['my_order'].'PId asc');
		?>
			<div class="hd">
				<i class="icon_success_status fail"></i>
				<h3><?=$c['lang_pack']['cart']['sStatusFail'];?></h3>
				<div class="note"><?=str_replace('%OId%', $OId, $c['lang_pack']['cart']['sOrderNumber']);?></div>
				<div class="message"><?=$c['lang_pack']['mobile']['message'];?>: <?=$log_row['Log'];?></div>
			</div>
			<div class="bd information_payment">
				<div class="title"><?=$c['lang_pack']['cart']['paymethod'];?></div>
				<form name="pay_edit_form" method="post" action="/account/">
					<?php
					//unset($payment_row[9], $payment_row[8], $payment_row[7], $payment_row[6]);
					$payment_count=count($payment_row);
					$pages=ceil($payment_count/7);
					for($i=0; $i<$pages; ++$i){
					?>
						<div class="payment_list clearfix" style="display:<?=$i==0?'block':'none';?>;">
							<?php
							for($j=$i*7; $j<($i+1)*7; ++$j){
								if($j>=$payment_count) break;
							?>
								<div class="payment_row" value="<?=$payment_row[$j]['PId'];?>" min="<?=$payment_row[$j]['MinPrice'];?>" max="<?=$payment_row[$j]['MaxPrice'];?>">
									<div class="check">&nbsp;<input name="PId" type="radio" /></div>
									<div class="img"><img src="<?=$payment_row[$j]['LogoPath'];?>" alt="<?=$payment_row[$j]['Name'.$c['lang']];?>"><span></span></div>
									<em class="icon_dot"></em>
									<div class="clear"></div>
								</div>
							<?php }?>
							<?php if($i==0 && $payment_count>7){?><i class="icon_shipping_title"></i><?php }?>
						</div>
						<div class="payment_contents clearfix" style="display:<?=$i==0?'block':'none';?>;">
							<?php
							for($j=$i*7; $j<($i+1)*7; ++$j){
								if($j>=$payment_count) break;
							?>
								<div class="payment_note" data-id="<?=$payment_row[$j]['PId'];?>" data-fee="<?=$payment_row[$j]['AdditionalFee'];?>" data-affix="<?=cart::iconv_price($payment_row[$j]['AffixPrice'], 2, '', 0);?>">
									<div class="name"><?=$payment_row[$j]['Name'.$c['lang']];?></div>
									<?php if($payment_row[$j]['Description'.$c['lang']]){?><div class="ext_txt"><?=$payment_row[$j]['Description'.$c['lang']]?></div><?php }?>
								</div>
							<?php }?>
						</div>
					<?php }?>
					<div class="payment_total">
						<div class="contact fl"><?=$c['lang_pack']['contactUs'];?>: <a href="mailto:<?=$c['config']['global']['AdminEmail'];?>"><?=$c['config']['global']['AdminEmail'];?></a></div>
						<div class="total fr"><span class="total_price"><?=$c['lang_pack']['user']['grandTotal'];?>: <span id="ot_total"><?=cart::iconv_price(0, 1, $order_row['Currency']).$total_price;?></span></span><a href="" class="btn_coutinue btn_global sys_shadow_button" id="pay_button"><?=$c['lang_pack']['cart']['sContinue'];?></a></div>
					</div>
					<input type="hidden" name="OId" value="<?=$OId;?>" />
					<input type="hidden" name="TotalPrice" value="<?=$total_price;?>" />
					<input type="hidden" name="BackLocation" value="/cart/complete/<?=$OId;?>.html" />
					<input type="hidden" name="Symbols" value="<?=cart::iconv_price($total_price, 1, $orders_row['Currency']);?>" currency="<?=$orders_row['Currency'];?>" />
				</form>
			</div>
		<?php
		}elseif($order_row['OrderStatus']==4){
			//支付成功
		?>
			<div class="hd">
				<i class="icon_success_status"></i>
				<h3><?=$c['lang_pack']['cart']['sStatusOk'];?></h3>
				<div class="note"><?=str_replace('%OId%', $OId, $c['lang_pack']['cart']['sOrderNumber']);?><a href="/account/orders/view<?=$OId;?>.html" class="btn_detail"><?=$c['lang_pack']['detail'];?></a></div>
			</div>
			<div class="bd">
				<?php
				if(!db::get_row_count('user', "Email='{$order_row['Email']}'")){
					//没有会员账号
				?>
					<div class="account_info">
						<form name="account_form" method="post">
							<?php 
								$coupon_row=db::get_one('sales_coupon',"CouponWay=2 and ({$c['time']} < EndTime and {$c['time']} > StartTime)");
								if($coupon_row['CouponType']){
									$str_replace = cart::iconv_price($coupon_row['Money'], 0, '', 0);
								}else{
									$str_replace = $coupon_row['Discount'].'% off';
								}
							?>
							<div class="title"><?=$coupon_row ? str_replace('%price%', $str_replace, $c['lang_pack']['cart']['win_coupon']) : $c['lang_pack']['cart']['sCreate'];?></div>
							<div class="account"><?=$c['lang_pack']['cart']['sUAccount'].': '.$order_row['Email'];?></div>
							<div class="password">
								<?php /*<input type="password" name="Password" value="" placeholder="<?=$c['lang_pack']['user']['password'];?>" class="pwd_input" />*/?>
								<label class="input_box">
									<span class="input_box_label"><?=$c['lang_pack']['user']['password'];?></span>
									<input type="password" class="input_box_txt pwd_input" name="Password" placeholder="<?=$c['lang_pack']['user']['password'];?>" />
								</label>
							</div>
							<input type="button" value="<?=$c['lang_pack']['cart']['sCreateMy'];?>" class="btn_create_account btn_global sys_shadow_button" />
							<input type="hidden" name="OId" value="<?=$OId;?>" />
						</form>
						<div class="back_list">
							<a href="/"><?=$c['lang_pack']['cart']['sReturnHome'];?></a>|<a href="/account/"><?=$c['lang_pack']['cart']['sReturnUser'];?></a>
						</div>
					</div>
				<?php
				}else{
					//推荐产品
					$products_list_row=orders::you_may_also_like(1, 12);
				?>
					<div class="like_prod_info">
						<div class="title"><?=$c['lang_pack']['cart']['sLikeProd'];?></div>
						<div class="content">
							<?php
							foreach((array)$products_list_row as $k=>$v){
									$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
									$url=ly200::get_url($v, 'products');
									$img=ly200::get_size_img($v['PicPath_0'], '240x240');
									$name=$v['Name'.$c['lang']];
									$price_ary=cart::range_price_ext($v);
									$price_0=$v["Price_{$is_promition}"];
							?>
									<dl class="pro_item fl<?=$k%4==0?' first':'';?>">
										<dt><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" /><span></span></a></dt>
										<dd class="name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
										<dd class="price">
											<em class="currency_data PriceColor"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0], 2);?></span>
											<?php if($c['config']['products_show']['Config']['price']){?><del><em class="currency_data"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data" data="<?=$price_0;?>"><?=cart::iconv_price($price_0, 2);?></span></del><?php }?>
										</dd>
									</dl>
							<?php }?>
						</div>
					</div>
				<?php }?>
			</div>
		<?php }?>
	</div>
    <div class="blank12"></div>
</div>