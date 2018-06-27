<?
/**
 * 这是一个很牛逼的插件实现
 * 
 * @package     payment
 * @subpackage  paypal
 * @category    payment
 * @author      鄙人
 * @link        http://www.ueeshop.com/
 */
/**
 * 需要注意的几个默认规则：
 * 1. 本插件类的文件名必须是action
 * 2. 插件类的名称必须是{插件名_actions}
 */
class paypal_actions 
{ 
    //解析函数的参数是pluginManager的引用 
    function __construct(&$pluginManager){
        //注册这个插件 
        //第一个参数是钩子的名称 
        //第二个参数是pluginManager的引用 
        //第三个是插件所执行的方法 
        $pluginManager->register('paypal', $this, '__config');
        $pluginManager->register('paypal', $this, 'do_payment');
        $pluginManager->register('paypal', $this, 'notify');
        $pluginManager->register('paypal', $this, 'ipn_handler');
		$pluginManager->register('paypal', $this, 'excheckout');
		$pluginManager->register('paypal', $this, 'cancel');
    }
	
	function __config($data){
		return @in_array($data, array('do_payment', 'notify', 'ipn_handler', 'excheckout', 'cancel'))?'enable':'';
	}
     
    function do_payment($data){
		global $c;
		
		$ceil_ary=array('TWD', 'JPY');//不需要金额小数点后两位
		$form_data = array(
			'cmd'				=>	'_xclick',
			'business'			=>	$data['account']['Account'],
			'item_name'			=>	$data['order_row']['OId'],
			'amount'			=>	@in_array($data['order_row']['Currency'], $ceil_ary)?ceil($data['total_price']):$data['total_price'],
			'currency_code'		=>	$data['order_row']['Currency'],
			'return'			=>	"{$data['domain']}/cart/success/{$data['order_row']['OId']}.html?utm_nooverride=1",//"{$data['domain']}/account/orders/",
			'invoice'			=>	$data['order_row']['OId'],
			'charset'			=>	'utf-8',
			'cancel_return'		=>	"{$data['domain']}/account/orders/view{$data['order_row']['OId']}.html?utm_nooverride=1",
			'notify_url'		=>	"{$data['domain']}/payment/paypal/notify/{$data['order_row']['OId']}.html",
			'cpp_logo_image'	=>	$data['domain'].$c['config']['global']['LogoPath'],
			'bn'				=>	'ueeshop_Cart',
			
			//覆盖Paypal地址
//			'address_override'	=>	1,
//			'address1'			=>	$data['order_row']['ShippingAddressLine1'],
//			'address2'			=>	$data['order_row']['ShippingAddressLine2'],
//			'city'				=>	$data['order_row']['ShippingCity'],
//			'country'			=>	db::get_value('country', "CId='{$data['order_row']['ShippingCId']}'", 'Acronym'),
//			'email'				=>	$data['order_row']['Email'],
//			'first_name'		=>	$data['order_row']['ShippingFirstName'],
//			'last_name'			=>	$data['order_row']['ShippingLastName'],
//			'state'				=>	$data['order_row']['ShippingState'],
//			'zip'				=>	$data['order_row']['ShippingZipCode']
		);
//		($data['order_row']['ShippingCId']==226 && $data['order_row']['ShippingSId']) && $form_data['state']=db::get_value('country_states', "SId='{$data['order_row']['ShippingSId']}'", 'AcronymCode');
		(int)$data['IsCreditCard']==1 && $form_data['landing_page']='billing';//信用卡支付
		
		echo '<form id="paypal_form" action="https://www.paypal.com/cgi-bin/webscr" method="post">';
		
		foreach((array)$form_data as $key=>$value){
			echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
		}
		
		echo '<input type="submit" value="Submit" style="width:1px; height:1px; display:none;" /></form><script language="javascript">document.getElementById("paypal_form").submit();document.getElementById("paypal_form").innerHTML="";</script>';
		exit;
    } 
     
    function ipn_handler($data){
		global $c;
		if(!$_POST) exit('IPN Processing Center');
		
		$data_ary=str::str_code($_POST, 'stripslashes');
		$data_ary['cmd']='_notify-validate';
		for($i=0;$i<5;$i++){
			$contents=ly200::curl('https://www.paypal.com/cgi-bin/webscr', $data_ary);
			if($contents) break;//返回结果不为空则退出
		}
		
		$OId=$_GET['OId'];
		!$OId && $OId=trim($_POST['invoice'])?trim($_POST['invoice']):trim($_GET['invoice']);
		if($contents){
			if(substr_count($contents, 'VERIFIED')){
				$account=str::json_data(db::get_value('payment', "Method='Paypal'", 'Attribute'), 'decode');
				
				if($_POST['payment_status']!='Completed' && $_POST['payment_status']!='Pending'){	//检查状态
					$payment_result='payment status not in Completed or Pending';
				}elseif(@strtolower(trim($_POST['receiver_id']))!=@strtolower(trim($account['Account'])) && @strtolower(trim($_POST['business']))!=@strtolower(trim($account['Account']))){	//检查收款帐号
					$payment_result='business account error';
				}else{
					$payment_result='payment success';
					echo 'success';
				}
			}else{
				$payment_result='paypal not return VERIFIED';
				echo 'fail';
			}
		}else{
			$payment_result='can not get paypal return info';
			echo 'fail';
		}
		
		ob_start();
		print_r($_GET);
		print_r($_POST);
		echo "\r\n\r\n$contents";
		echo "\r\n\r\n$payment_result";
		$log=ob_get_contents();
		ob_end_clean();
		file::write_file('/_pay_log_/paypal/ipn/'.date('Y_m/', $c['time']), "ipn-{$OId}.txt", $log);	//把返回数据写入文件
    }
	
	function notify($data){
		global $c;
		
		$data_ary=str::str_code($_POST, 'stripslashes');
		$data_ary['cmd']='_notify-validate';
		for($i=0;$i<5;$i++){
			$contents=ly200::curl('https://www.paypal.com/cgi-bin/webscr', $data_ary);
			if($contents) break;//返回结果不为空则退出
		}
		
		$OId=$_GET['OId'];
		if($contents){
			if(substr_count($contents, 'VERIFIED')){
				$business=$_POST['business']?$_POST['business']:$_POST['receiver_email'];
				$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1,2,3)");
				!$order_row && exit();
				
				$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
				
				$total_price=orders::orders_price($order_row, 1);//网站付款时的币种的订单总金额
				$account=str::json_data(db::get_value('payment', "Method='Paypal'", 'Attribute'), 'decode');
				$mc_gross=(float)$_POST['mc_gross'];
				
				if($_POST['mc_currency']!=$order_row['Currency']){	//对比币种（不相同）
					$mc_gross=cart::iconv_price($mc_gross, 2, $order_row['Currency'], 0);
				}
				
				if($_POST['payment_status']!='Completed' && $_POST['payment_status']!='Pending'){	//检查状态
					$payment_result='payment status not in Completed or Pending';
				}elseif(@strtolower(trim($_POST['receiver_id']))!=@strtolower(trim($account['Account'])) && @strtolower(trim($business))!=@strtolower(trim($account['Account']))){	//检查收款帐号
					$payment_result='business account error';
				}elseif(abs($mc_gross-$total_price)>1 && abs($mc_gross-$total_price)>($total_price/100)){	//检查金额(相差小于1 / 相差小于总金额的1%)
					$payment_result='grand total error';
				}else{
					$status=1;
					$_POST['payment_status']=='Pending' && $status=2;
					$payment_result=orders::orders_payment_result($status, $UserName, $order_row, '');
					echo 'success';
					/******** 记录PayPal买家账号的收货地址信息 Start ********/
					$shipto_ary=array(
						'Account'		=>	addslashes($_POST['payer_email']),
						'FirstName'		=>	addslashes($_POST['address_name']),
						'LastName'		=>	'',
						'AddressLine1'	=>	addslashes($_POST['address_street']),
						'CountryCode'	=>	'+'.$_POST['address_country_code'],
						'PhoneNumber'	=>	$_POST['contact_phone'],
						'City'			=>	addslashes($_POST['address_city']),
						'State'			=>	addslashes($_POST['address_state']),
						'Country'		=>	addslashes($_POST['address_country']),
						'ZipCode'		=>	addslashes($_POST['address_zip'])
					);
					if(db::get_row_count('orders_paypal_address_book', "OrderId='{$order_row['OrderId']}'")){
						db::update('orders_paypal_address_book', "OrderId='{$order_row['OrderId']}'", $shipto_ary);
					}else{
						$shipto_ary['OrderId']=$order_row['OrderId'];
						db::insert('orders_paypal_address_book', $shipto_ary);
					}
					/******** 记录PayPal买家账号的收货地址信息 End ********/
				}
			}else{
				$payment_result='paypal not return VERIFIED';
				echo 'fail';
			}
		}else{
			$payment_result='can not get paypal return info';
			echo 'fail';
		}
		
		ob_start();
		print_r($_GET);
		print_r($_POST);
		echo "\r\n\r\n$contents";
		echo "\r\n\r\n$payment_result";
		$log=ob_get_contents();
		ob_end_clean();
		file::write_file('/_pay_log_/paypal/'.date('Y_m/', $c['time']), "$OId.txt", $log);	//把返回数据写入文件
	}
}
?>