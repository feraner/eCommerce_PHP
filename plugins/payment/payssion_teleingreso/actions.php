<?
/**
 * 这是一个很牛逼的插件实现
 * 
 * @package     payment
 * @subpackage  payssion - teleingreso (Payssion本地支付)
 * @category    payment
 * @author      鄙人
 * @link        http://www.ueeshop.com/
 */
/**
 * 需要注意的几个默认规则：
 * 1. 本插件类的文件名必须是action
 * 2. 插件类的名称必须是{插件名_actions}
 */
class payssion_teleingreso_actions 
{ 
    //解析函数的参数是pluginManager的引用 
    function __construct(&$pluginManager){
        //注册这个插件 
        //第一个参数是钩子的名称 
        //第二个参数是pluginManager的引用 
        //第三个是插件所执行的方法 
        $pluginManager->register('payssion_teleingreso', $this, '__config'); 
        $pluginManager->register('payssion_teleingreso', $this, 'do_payment'); 
        $pluginManager->register('payssion_teleingreso', $this, 'notify'); 
        $pluginManager->register('payssion_teleingreso', $this, 'returnUrl'); 
    }
	
	function __config($data){
		return @in_array($data, array('do_payment', 'notify', 'returnUrl'))?'enable':'';
	}
     
    function do_payment($data){
		global $c;
		
		$pm_currency_ary=array('EUR', 'RUB', 'GBP', 'CHF', 'NOK', 'DKK', 'SEK', 'USD');//所支付的货币（欧洲）
		$currency=$data['order_row']['Currency'];
		if(!in_array($currency, $pm_currency_ary)){//不是相应的货币，自动切换成美元支付
			$currency='USD';
			$currency_row=db::get_one('currency', "Currency='{$data['order_row']['Currency']}'");//查询对美元的汇率
			$data['total_price']=sprintf('%01.2f', $data['total_price']/(float)$currency_row['ExchangeRate']);
		}
		
		$form_data=array( 
			'api_key'		=>	$data['account']['api_key'],
			'pm_id'			=>	'teleingreso_es',	//支付方式id: qiwi, yamoney, sofort, paysafecard, trustpay, molpay, boleto_br
			'payer_email'	=>	$data['order_row']['Email'],	//付款人邮箱
			'payer_ref'		=>	'',
			'amount'		=>	$data['total_price'],
			'currency'		=>	$currency,
			'track_id'		=>	$data['order_row']['OId'],	//订单跟踪id
			'sub_track_id'	=>	'',	//额外跟踪信息
			'description'	=>	"{$data['domain']}{$data['order_row']['OId']}",	//订单描述, 最长256 字符, 通过将网址和货物名称组合起来
			'redirect_url'	=>	"{$data['domain']}/payment/payssion_teleingreso/returnUrl/?utm_nooverride=1",	//支付未完成、失败等同步通知
			'success_url'	=>	"{$data['domain']}/payment/payssion_teleingreso/returnUrl/?utm_nooverride=1",	//支付成功同步通知
			'notify_url'	=>	"{$data['domain']}/payment/payssion_teleingreso/notify/"	//异步通知
		);
		
		$check_array = array($data['account']['api_key'], $form_data['pm_id'], $form_data['amount'], $form_data['currency'], $form_data['track_id'], $form_data['sub_track_id'], $data['account']['secret_key']);
		$check_msg=@implode('|', $check_array);
		$form_data['api_sig']=md5($check_msg);
		
		echo '<form id="payssion_form" action="'.$data['form_action'].'" method="post">';
			foreach((array)$form_data as $key=>$value){
				echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}
			echo '<input type="submit" value="Submit" style="width:1px; height:1px; display:none;" />';
		echo '</form>';
		echo '<script language="javascript">document.getElementById("payssion_form").submit();</script>';
    }
     
    function notify($data){
		global $c;
		
		$account=str::json_data(db::get_value('payment', "Method='PayssionTeleingreso'", 'Attribute'), 'decode');
		
		// Assign payment notification values to local variables
		$pm_id=trim($_POST['pm_id']);
		$amount=trim($_POST['amount']);
		$currency=trim($_POST['currency']);
		$track_id=trim($_POST['track_id']);
		$sub_track_id=trim($_POST['sub_track_id']);
		$state=trim($_POST['state']);
		$notify_sig=trim($_POST['notify_sig']);
		
		$check_array=array($account['api_key'], $pm_id, $amount, $currency, $track_id, $sub_track_id, $state, $account['secret_key']);
		$check_msg=implode('|', $check_array);
		$check_sig=md5($check_msg);
		$OId=$track_id;
		!$OId && $OId=$_GET['OId'];
		
		if(strtolower($notify_sig)==strtolower($check_sig)){ //验证数字签名
			$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1, 2, 3)");
			!$order_row && exit();
			$total_price=cart::iconv_price(orders::orders_price($order_row), 2);
			$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
			if($state=='completed'){ //支付完成
				$payment_result=orders::orders_payment_result(1, $UserName, $order_row, $state);
			}elseif(@in_array($state, array('pending','paid_partial','awaiting_confirm'))){ //未完成支付	$state=='pending'
				$payment_result=orders::orders_payment_result(2, $UserName, $order_row, $state);
			}else{ //支付错误
				$payment_result=orders::orders_payment_result(0, $UserName, $order_row, $state);
			}
		}else{ //验证失败
			$payment_result='Validation failure';
		}
		
		ob_start();
		print_r($_GET);
		print_r($_POST);
		echo "\r\n\r\n$check_sig";
		echo "\r\n\r\n$payment_result";
		$log=ob_get_contents();
		ob_end_clean();
		file::write_file('/_pay_log_/payssion/Teleingreso/'.date('Y_m/', $c['time']), "{$OId}-".rand(1,1000).".txt", $log);	//把返回数据写入文件
    }
	
	function returnUrl($data){
		global $c;
		
		$state=trim($_GET['state']);
		$track_id=trim($_GET['track_id']);
		if((int)db::get_row_count('orders', "OId='{$track_id}'")){
			$tips=$state=='completed'?'Payment successful':($state=='pending'?'Payment processed!':'Payment wrong!');
			js::location("/cart/success/{$track_id}.html", '', '.top');
		}else{
			js::location("/", 'Your order does not exist!', '.top');
		}
	}
}
?>