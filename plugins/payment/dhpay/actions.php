<?
/**
 * 这是一个很牛逼的插件实现
 * 
 * @package     payment
 * @subpackage  dhpay 敦煌支付(DHpay)
 * @category    payment
 * @author      鄙人
 * @link        http://www.ueeshop.com/
 */
/**
 * 需要注意的几个默认规则：
 * 1. 本插件类的文件名必须是action
 * 2. 插件类的名称必须是{插件名_actions}
 */
class dhpay_actions 
{ 
    //解析函数的参数是pluginManager的引用 
    function __construct(&$pluginManager){
        //注册这个插件 
        //第一个参数是钩子的名称 
        //第二个参数是pluginManager的引用 
        //第三个是插件所执行的方法 
        $pluginManager->register('dhpay', $this, '__config');
        $pluginManager->register('dhpay', $this, 'do_payment');
        $pluginManager->register('dhpay', $this, 'returnUrl');
		$pluginManager->register('dhpay', $this, 'notifyUrl');
    }
	
	function __config($data){
		return @in_array($data, array('do_payment', 'returnUrl', 'notifyUrl'))?'enable':'';
	}
     
    function do_payment($data){
		global $c;
		
		//支持货币：CAD(加元)、AUD(澳元)、SGD(新加坡元)、EUR(欧元)、JPY(日元)、RUB(俄罗斯卢布)、CNY(人民币)、USD(美元)、GBP(英镑)、INR(印度卢比)、ILS(新锡克尔)、MXN(墨西哥比索)
		$dh_currency=array('CAD', 'AUD', 'SGD', 'EUR', 'JPY', 'RUB', 'CNY', 'USD', 'GBP', 'INR', 'ILS', 'MXN');
		@!in_array($data['order_row']['Currency'], $dh_currency) && js::location('/', $c['lang_pack']['cart']['not_accept'], '.top');//不支持的货币
		!in_array($data['order_row']['OrderStatus'], array(1, 3)) && js::location("/account/orders/view{$data['order_row']['OId']}.html");
		
		$row=db::get_all('country', "CId in({$data['order_row']['ShippingCId']}, {$data['order_row']['BillCId']})", 'CId, Acronym');
		$Acronym_ary=array();
		foreach((array)$row as $v){
			$Acronym_ary[$v['CId']]=$v['Acronym'];
		}
		
		/*
		//沙箱模式
		//测试卡号	4351300000000001	08/18	123
		$sandbox=1;
		$query_string='';
		if((int)$sandbox){
			$data['account']=array('mechant_id'=>'200000000109510','private_key'=>'asdfasdfa');
			$query_string='?&env=dhpaysandbox';
		}
		*/
		
		$form_data=array( 
			//***************************必填***************************
			'merchant_id'		=>	$data['account']['mechant_id'],	//商户号
			'invoice_id'		=>	$data['order_row']['OId'].mt_rand(100, 999),	//交易号
			'order_no'			=>	$data['order_row']['OId'],	//订单号
			'currency'			=>	strtoupper($data['order_row']['Currency']),//$currency_id,		//币种
			'amount'			=>	$data['total_price'],		//交易金额，必须包含 2 位小数
			'product_name'		=>	$data['order_row']['OId'],	//商品名称
			'product_price'		=>	$data['total_price'],	//商品单价，必须包含 2 位小数
			'product_quantity'	=>	1,	//商品数量
			
			//***************************选填***************************
			'return_url'		=>	"{$data['domain']}/payment/dhpay/returnUrl/{$data['order_row']['OId']}.html?utm_nooverride=1",
			'notify_url'		=>	"{$data['domain']}/payment/dhpay/notifyUrl/{$data['order_row']['OId']}.html?utm_nooverride=1", //异步通知地址，如果有回调失败的情况发生，会将支付结果发送到该地址，注意不要设置登陆限制，支付结果以 GET 方式发送
			'buyer_email'		=>	$data['order_row']['Email'],	//买家邮件地址
			'shipping_country'	=>	$Acronym_ary[$data['order_row']['ShippingCId']],	//订单货运国家二维代码
			'first_name'		=>	$data['order_row']['BillFirstName'],	//Billing 名
			'last_name'			=>	$data['order_row']['BillLastName'],	//Billing 姓
			'country'			=>	$Acronym_ary[$data['order_row']['BillCId']],	//Billing 国家，国家二维代码
			'state'				=>	$data['order_row']['BillState'],	//Billing 州省
			'city'				=>	$data['order_row']['BillCity'],	//Billing 城市
			'address_line'		=>	$data['order_row']['BillAddressLine1'],	//Billing 详细地址
			'zipcode'			=>	$data['order_row']['BillZipCode'],	//Billing 邮政编码
			'remark'			=>	$data['order_row']['Comments'],	//备注		
		);
		
		$private_key=$data['account']['private_key'];//商户的密钥
		
		//签名的表单字段名
		$hash_key=array('amount', 'currency', 'invoice_id', 'merchant_id');
		
		//按key名进行顺序排序
		sort($hash_key);
		foreach($hash_key as $key){
			$hash_src.=$form_data[$key];
		}
		
		//密钥放最前面
		$hash_src=$private_key.$hash_src;
		
		//sha256算法
		$form_data['hash']=@hash('sha256', $hash_src);
		
		echo '<form name="dhpay_form" id="dhpay_form" action="https://www.dhpay.com/merchant/web/cashier" method="post">';
			foreach((array)$form_data as $key=>$value){
				echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}
			echo '<input type="submit" style="width:1px; height:1px;" value="">';
		echo '</form>';
		echo '<script language="javascript">document.getElementById("dhpay_form").submit();</script>';
    } 
	
	function returnUrl($data){
		global $c;
		
		/**
		 * 回调方法会接受如下参数
		 *
		 * mechant_id: 商户号，对应商户提交的商户号
		 * invoice_id: 交易号，对应商户提交的交易号
		 * order_no: 订单号，对应商户提交的订单号
		 * currency: 交易币种
		 * amount: 交易金额
		 * status: 交易状态(00处理中, 01成功, 02失败)
		 * failure_reason: 如果交易状态为“失败”，则会有相应的失败原因
		 * trans_date: 交易日期
		 * trans_time: 交易时间
		 * hash: 返回参数的签名
		 */
		
		/*
		//	hash签名算法
		//参数签名校验支持对部分字段签名校验。
		//对部分字段签名校验：除标记了@HashIn 注解的字段外，对以下字段进行签名校验(商户编号、交易号、币种、金额)
		// 使用 map 键值对组织需要提交的参数。
		// 按键升序排列值。
		// 将值合成一串，排除空值。
		// 将商户密钥放在值串的最前面。
		// 签名算法使用 sha256（SHA-256）算法，对最终的值串进行签名。
		
		//沙箱测试
		$sandbox=1;//沙箱模式
		if((int)$sandbox){
			$account=array('merchant_id'=>'200000000109510','private_key'=>'asdfasdfa');
		}
		
		*/
		
		$OId=$_GET['order_no'];
		!$OId && $OId=$_GET['OId'];
		$account=str::json_data(db::get_value('payment', "Method='DHpay'", 'Attribute'), 'decode');
		
		$result['amount']=trim($_GET['amount']);
		$result['trans_time']=trim($_GET['trans_time']);
		$result['trans_date']=trim($_GET['trans_date']);
		$result['status']=trim($_GET['status']);
		$result['ref_no']=trim($_GET['ref_no']);
		$result['invoice_id']=trim($_GET['invoice_id']);
		$result['merchant_id']=trim($_GET['merchant_id']);
		$result['order_no']=trim($_GET['order_no']);
		$result['currency']=trim($_GET['currency']);
		$result['hash']=trim($_GET['hash']);
		
		//生成数字签名
		$hash_key=array('amount', 'trans_time', 'trans_date', 'status', 'ref_no', 'invoice_id', 'merchant_id', 'order_no', 'currency'); //签名的表单字段名
		sort($hash_key); //按key名进行顺序排序
		foreach($hash_key as $key){
			$hash_src.=$result[$key];
		}
		$hash_src=$account['private_key'].$hash_src; //密钥放最前面
		$hashString=@hash('sha256', $hash_src); //sha256算法
		
		$jumpUrl="/cart/success/{$OId}.html";
		if(strtoupper($hashString)==strtoupper($result['hash'])){ //验证数字签名
			$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1, 2, 3)");
			!$order_row && js::location('/');
			$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
			if($result['status']=='00'){ //支付处理中
				$error=orders::orders_payment_result(2, $UserName, $order_row, $_GET['failure_reason']);
			}else if($result['status']=='01'){ //支付成功
				$error=orders::orders_payment_result(1, $UserName, $order_row, '');
			}elseif($result['status']=='02'){ //支付失败
				$error=orders::orders_payment_result(0, $UserName, $order_row, $_GET['failure_reason']);
			}else{
				$error="Error! No Status!";
			}
		}else{ //验证失败
			$error="Verification Failed";
		}
		
		ob_start();
		print_r($_GET);
		print_r($_POST);
		echo "\r\n\r\n$hashString";
		echo "\r\n\r\n$error";
		$log=ob_get_contents();
		ob_end_clean();
		file::write_file('/_pay_log_/dhpay/'.date('Y_m/', $c['time']), "$OId.txt", $log);	//把返回数据写入文件
		js::location($jumpUrl, $error, '.top');
	}
	
	function notifyUrl($data){
		global $c;
		
		/**
		 * 回调方法会接受如下参数
		 *
		 * mechant_id: 商户号，对应商户提交的商户号
		 * invoice_id: 交易号，对应商户提交的交易号
		 * order_no: 订单号，对应商户提交的订单号
		 * currency: 交易币种
		 * amount: 交易金额
		 * status: 交易状态(00处理中, 01成功, 02失败)
		 * failure_reason: 如果交易状态为“失败”，则会有相应的失败原因
		 * trans_date: 交易日期
		 * trans_time: 交易时间
		 * hash: 返回参数的签名
		 */
		
		/*
		//	hash签名算法
		//参数签名校验支持对部分字段签名校验。
		//对部分字段签名校验：除标记了@HashIn 注解的字段外，对以下字段进行签名校验(商户编号、交易号、币种、金额)
		// 使用 map 键值对组织需要提交的参数。
		// 按键升序排列值。
		// 将值合成一串，排除空值。
		// 将商户密钥放在值串的最前面。
		// 签名算法使用 sha256（SHA-256）算法，对最终的值串进行签名。
		
		//沙箱测试
		$sandbox=1;//沙箱模式
		if((int)$sandbox){
			$account=array('merchant_id'=>'200000000109510','private_key'=>'asdfasdfa');
		}
		
		*/
		
		$OId=$_GET['order_no'];
		!$OId && $OId=$_GET['OId'];
		$account=str::json_data(db::get_value('payment', "Method='DHpay'", 'Attribute'), 'decode');
		
		$result['amount']=trim($_GET['amount']);
		$result['trans_time']=trim($_GET['trans_time']);
		$result['trans_date']=trim($_GET['trans_date']);
		$result['status']=trim($_GET['status']);
		$result['ref_no']=trim($_GET['ref_no']);
		$result['invoice_id']=trim($_GET['invoice_id']);
		$result['merchant_id']=trim($_GET['merchant_id']);
		$result['order_no']=trim($_GET['order_no']);
		$result['currency']=trim($_GET['currency']);
		$result['hash']=trim($_GET['hash']);
		
		//生成数字签名
		$hash_key=array('amount', 'trans_time', 'trans_date', 'status', 'ref_no', 'invoice_id', 'merchant_id', 'order_no', 'currency'); //签名的表单字段名
		sort($hash_key); //按key名进行顺序排序
		foreach($hash_key as $key){
			$hash_src.=$result[$key];
		}
		$hash_src=$account['private_key'].$hash_src; //密钥放最前面
		$hashString=@hash('sha256', $hash_src); //sha256算法
		
		if(strtoupper($hashString)==strtoupper($result['hash'])){ //验证数字签名
			$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1, 2, 3)");
			if($order_row){
				$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
				if($result['status']=='00'){ //支付处理中
					$error=orders::orders_payment_result(2, $UserName, $order_row, $_GET['failure_reason']);
				}else if($result['status']=='01'){ //支付成功
					$error=orders::orders_payment_result(1, $UserName, $order_row, '');
				}elseif($result['status']=='02'){ //支付失败
					$error=orders::orders_payment_result(0, $UserName, $order_row, $_GET['failure_reason']);
				}else{
					$error="Error! No Status!";
				}
			}else{
				$error="Error! No Orders!";
			}
		}else{ //验证失败
			$error="Verification Failed";
		}
		
		ob_start();
		print_r($_GET);
		print_r($_POST);
		echo "\r\n\r\n$hashString";
		echo "\r\n\r\n$error";
		$log=ob_get_contents();
		ob_end_clean();
		file::write_file('/_pay_log_/dhpay/'.date('Y_m/', $c['time']), "$OId_notify.txt", $log);	//把返回数据写入文件
	}
}
?>