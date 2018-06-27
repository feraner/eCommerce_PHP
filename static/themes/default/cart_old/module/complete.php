<?php !isset($c) && exit();?>
<?php	
	$OId=trim($_GET['OId']);
	!$OId && js::location('/cart/');
	
	$order_row=db::get_one('orders', "OId='$OId'");	
	//订单不存在
	(!$order_row) && js::location('/');
	//订单无产品
	if(!(int)db::get_row_count('orders_products_list', "OrderId='{$order_row['OrderId']}'")){
		db::delete('orders', "OrderId='{$order_row['OrderId']}'");
		js::location('/');
	}
	//会员订单，未登录不允许查看
	((int)$order_row['UserId'] && !(int)$_SESSION['User']['UserId']) && js::location('/account/login.html?JumpUrl='.urlencode("/cart/complete/{$OId}.html"));
	//当前会员非订单会员
	((int)$order_row['UserId'] && (int)$order_row['UserId']!=(int)$_SESSION['User']['UserId']) && js::location("/account/");
	//会员订单发货后状态在会员中心查询
	((int)$_SESSION['User']['UserId'] && (int)$order_row['OrderStatus']>4) && js::location("/account/orders/view{$OId}.html");
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
		echo '<div id="payment_loading" style="width:150px; height:32px; margin:30% auto 0; padding-top:40px; text-align:center; font-size:24px; color:#333; background:url(/static/themes/default/images/global/loading.gif) no-repeat center top;">Loading</div>';
		include('payment.php');
		exit();
	}
	
	$payOffline=($payment_row['IsOnline']!=1 && $order_row['OrderStatus']!=1 && $order_row['OrderStatus']!=3)?0:1;
?>
<script type="text/javascript">$(document).ready(function(){cart_obj.complete_init()});</script>
<div id="lib_cart">
	<div class="position"><strong><?=$c['lang_pack']['cart']['position'];?>: </strong> <a href="/"><?=$c['lang_pack']['cart']['home'];?></a> &gt; <a href="/cart/"><?=$c['lang_pack']['cart']['cart'];?></a> &gt; <strong><?=((int)$order_row['OrderStatus']>=4&&(int)$order_row['OrderStatus']<7)?$c['lang_pack']['cart']['complete']:$c['lang_pack']['cart']['payment'];?></strong></div>
    <div class="blank12"></div>
    <div class="complete">
    	<div class="tips fl">
        	<h3><?=$c['lang_pack']['cart']['thanks'];?></h3>
            <div class="payment_info"><?=$payment_row['Description'.$c['lang']];?></div>
            <?php 
			if((int)$payOffline && $payment_row['PId']!=9){	//线下支付，并且非货到付款
				$currency_row=db::get_all('currency', "IsUsed='1'");
				echo ly200::load_static('/static/themes/default/css/address.css');
			?>
                <div class="editAddr pay_form">
                <form method="post" id="PaymentForm">
                    <p><span class="required">*</span>&nbsp;<span class="indicates"><?=$c['lang_pack']['cart']['required'];?></span></p>
                    <?php if($payment_row['Method']=='WesternUnion'){?>
                        <table class="tb-shippingAddr">
                            <tbody>
                                <tr>
                                    <th></th>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th><span class="required">*</span><label><?=$c['lang_pack']['cart']['name'];?>:</label></th>
                                    <td class="recipient">
                                        <div><input type="text" name="FirstName" maxlength="32" notnull="" /><p><span class="required">*</span>&nbsp;<?=$c['lang_pack']['cart']['firstname'];?></p></div>
                                        <div><input type="text" name="LastName" maxlength="32" notnull="" /><p><span class="required">*</span>&nbsp;<?=$c['lang_pack']['cart']['lastname'];?></p></div>
                                    </td>
                                </tr>
                                <tr>
                                    <th><span class="required">*</span><label><?=$c['lang_pack']['cart']['currency'];?>:</label></th>
                                    <td>
                                        <select name="Currency" notnull="">
                                            <?php foreach((array)$currency_row as $v){?>
                                            <option value="<?=$v['Currency'];?>" <?=$_SESSION['Currency']['Currency']==$v['Currency']?'selected':'';?>><?=$v['Currency'];?></option>
                                            <?php }?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><span class="required">*</span><label><?=$c['lang_pack']['cart']['sentmoney'];?>:</label></th>
                                    <td><input type="text" name="SentMoney" maxlength="8" value="" notnull="" /></td>
                                </tr>
                                <tr>
                                    <th><span class="required">*</span><label><?=$c['lang_pack']['cart']['mtcn'];?></label></th>
                                    <td><input type="text" name="MTCNNumber" maxlength="10" value="" placeholder="10 Digits" title="10 Digits" notnull="" format="Length|10" /></td>
                                </tr>
                                <tr>
                                    <th><span class="required">*</span><label><?=$c['lang_pack']['cart']['country'];?>:</label></th>
                                    <td>
                                        <select name="Country" placeholder="<?=$c['lang_pack']['cart']['choosecty'];?>" notnull="">
                                            <option value=""><?=$c['lang_pack']['cart']['selectcty'];?></option>
                                            <?php 
                                                $country_row=str::str_code(db::get_all('country', "IsUsed=1", '*', 'Country asc'));
                                                foreach((array)$country_row as $v){
                                            ?>
                                                <option value="<?=$v['CId'];?>"><?=$v['Country'];?></option>
                                            <?php }?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><label><?=$c['lang_pack']['cart']['contents'];?>:</label></th>
                                    <td><textarea name="Contents" rows="4" cols="49"></textarea></td>
                                </tr>
                                <tr>
                                    <th></th>
                                    <td>
                                        <button id="paySubmit" class="textbtn"><?=$c['lang_pack']['cart']['submit'];?></button>
                                        <a href="javascript:void(0);" id="Cancel" class="textbtn"><?=$c['lang_pack']['cart']['cancel'];?></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    <?php }elseif($payment_row['Method']=='MoneyGram' || $payment_row['Method']=='TT' || $payment_row['Method']=='BankTransfer'){?>
                        <table class="tb-shippingAddr">
                            <tbody>
                                <tr>
                                    <th></th>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th><span class="required">*</span><label><?=$c['lang_pack']['cart']['name'];?>:</label></th>
                                    <td class="recipient">
                                        <div><input type="text" name="FirstName" maxlength="32" notnull="" /><p><span class="required">*</span>&nbsp;<?=$c['lang_pack']['cart']['firstname'];?></p></div>
                                        <div><input type="text" name="LastName" maxlength="32" notnull="" /><p><span class="required">*</span>&nbsp;<?=$c['lang_pack']['cart']['surname'];?></p></div>
                                    </td>
                                </tr>
                                <tr>
                                    <th><span class="required">*</span><label><?=$c['lang_pack']['cart']['currency'];?>:</label></th>
                                    <td>
                                        <select name="Currency">
                                            <?php foreach((array)$currency_row as $v){?>
                                                <option value="<?=$v['Currency'];?>" <?=$_SESSION['Currency']['Currency']==$v['Currency']?'selected':'';?>><?=$v['Currency'];?></option>
                                            <?php }?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th><span class="required">*</span><label><?=$c['lang_pack']['cart']['sentmoney'];?>:</label></th>
                                    <td><input type="text" name="SentMoney" maxlength="8" value="" notnull="" /></td>
                                </tr>
                                <tr>
                                    <th><span class="required">*</span><label><?=$c['lang_pack']['cart']['mtcn_num'];?>:</label></th>
                                    <td><input type="text" name="MTCNNumber" maxlength="8" value="" placeholder="8 Digits" title="8 Digits" notnull="" format="Length|8" /></td>
                                </tr>
                                <tr>
                                    <th><label><?=$c['lang_pack']['cart']['contents'];?>:</label></th>
                                    <td><textarea name="Contents" rows="4" cols="49"></textarea></td>
                                </tr>
                                <tr>
                                    <th></th>
                                    <td>
                                        <button id="paySubmit" class="textbtn"><?=$c['lang_pack']['cart']['submit'];?></button>
                                        <a href="javascript:void(0);" id="Cancel" class="textbtn"><?=$c['lang_pack']['cart']['cancel'];?></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    <?php }?>
                    <input type="hidden" name="PaymentMethod" value="<?=$payment_row['Method'];?>" />
                    <input type="hidden" name="OId" value="<?=$order_row['OId'];?>" />
                </form>
                </div>
            <?php }?>
        </div>
        <div class="orders_info fl">
        	<h3><?=$c['lang_pack']['cart']['summary'];?></h3>
            <div class="rows">
            	<label><?=$c['lang_pack']['cart']['orderNo'];?></label>
                <span class="red"><?=$OId;?></span>
                <div class="clear"></div>
            </div>
            <div class="rows">
            	<label><?=$c['lang_pack']['cart']['totalamount'];?>:</label>
                <span class="red"><?=$_SESSION['Currency']['Symbol'].' '.cart::iconv_price(orders::orders_price($order_row, 1, 1), 2);?></span>
                <div class="clear"></div>
            </div>
            <div class="rows">
            	<label><?=$c['lang_pack']['cart']['totalqty'];?>:</label>
                <span><?=(int)db::get_sum('orders_products_list', "OrderId='{$order_row['OrderId']}'", 'Qty');?></span>
                <div class="clear"></div>
            </div>
            <div class="rows">
            	<label><?=$c['lang_pack']['cart']['status'];?>:</label>
                <span><?=$c['lang_pack']['user']['OrderStatusAry'][$order_row['OrderStatus']];?></span>
                <div class="clear"></div>
            </div>
            <div class="rows">
            	<label><?=$c['lang_pack']['cart']['paymethod'];?>:</label>
                <span><?=$order_row['PaymentMethod'];?></span>
                <div class="clear"></div>
            </div>
            <?php if((int)$payOffline && $payment_row['PId']!=9 && (int)$order_row['OrderStatus']<4){?><a href="javascript:void();" class="textbtn payButton"><?=$c['lang_pack']['cart']['paynow'];?></a><?php }?>
        </div>
        <div class="blank12"></div>
        <div class="product_list">
        
        </div>
    </div>
    <div class="blank12"></div>
    <?php /*?><div class="cartBox">
    	<h2>Your Recent History</h2>
        <div class="contents products">

        </div>
    </div><?php */?>
</div>
