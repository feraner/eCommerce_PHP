<?
/**
 * 这是一个很牛逼的插件实现
 * 
 * @package     payment
 * @subpackage  leaderspay 万开付新版本(LeadersPay)
 * @category    payment
 * @author      鄙人
 * @link        http://www.ueeshop.com/
 */
/**
 * 需要注意的几个默认规则：
 * 1. 本插件类的文件名必须是action
 * 2. 插件类的名称必须是{插件名_actions}
 */
class leaderspay_actions 
{ 
    //解析函数的参数是pluginManager的引用 
    function __construct(&$pluginManager){
        //注册这个插件 
        //第一个参数是钩子的名称 
        //第二个参数是pluginManager的引用 
        //第三个是插件所执行的方法 
        $pluginManager->register('leaderspay', $this, '__config');
        $pluginManager->register('leaderspay', $this, 'do_payment');
        $pluginManager->register('leaderspay', $this, 'returnUrl');
    }
	
	function __config($data){
		return @in_array($data, array('do_payment', 'returnUrl'))?'enable':'';
	}
     
    function do_payment($data){
		global $c;
		
		//支持货币：
		$leaderspay_currency=array('SEK', 'PEN', 'JOD', 'LBP', 'PKR', 'HUF', 'UAH', 'QAR', 'RON', 'MOP', 'ARS', 'KWD', 'VEF', 'EGP', 'COP', 'NGN', 'CZK', 'KZT', 'CLP', 'VND', 'PLN', 'MXN', 'SAR', 'IDR', 'USD', 'BRL', 'AED', 'NOK', 'RUB', 'ZAR', 'ILS', 'CHF', 'PHP', 'INR', 'THB', 'MYR', 'TRY', 'DKK', 'KRW', 'TWD', 'NZD', 'SGD', 'AUD', 'EUR', 'GBP', 'CNY', 'HKD', 'CAD', 'JPY');
		@!in_array($data['order_row']['Currency'], $leaderspay_currency) && js::localtion('/', $c['lang_pack']['cart']['not_accept'], '.top');//不支持的货币
		!in_array($data['order_row']['OrderStatus'], array(1,3)) && js::location("/account/orders/view{$data['order_row']['OId']}.html");	//订单不是未支付状态
		
		$year=@date('Y', $c['time']);
		if($_POST){
			@extract($_POST, EXTR_PREFIX_ALL, 'p');
			$monthArr=array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
			$yearArr=array();
			for($i=0; $i<10; $i++){
				$yearArr[]=$year+$i;
			}
			
			(!$p_CardNo || !is_numeric($p_CardNo) || @strlen($p_CardNo)!=16 || !@in_array($p_CardExpireMonth, $monthArr) || !@in_array($p_CardExpireYear, $yearArr) || !$p_CardSecurityCode || !is_numeric($p_CardSecurityCode) || @strlen($p_CardSecurityCode)!=3 ) && js::back();
			
			//产品信息
			$goodsAry=array();
			$order_products_list_row=db::get_all('orders_products_list', "OrderId='{$data['order_row']['OrderId']}'", '*', 'LId asc');
			foreach($order_products_list_row as $k=>$v){
				$goodsAry['goodsInfo'][$k]=array('goodsName'=>$v['Name'], 'quantity'=>$v['Qty'], 'goodsPrice'=>sprintf('%01.2f', cart::iconv_price($v['Price'], 2, $orders_row['Currency'], 0)));
			}
			$goodsString=str::json_data(str::str_code($goodsAry, 'stripslashes'));
			
			$form_data = array( 
				//接口基本信息
				'transType'			=>	'sales', //交易类型
				'apiType'			=>	1, //接口类型 默认传递1 1.普通接口、2.app sdk、3.快捷支付、4.虚拟
				'transModel'		=>	'M', //交易模式
				'EncryptionMode'	=>	'SHA256', //加密方式类型
				'CharacterSet'		=>	'UTF8', //字符编码
				//订单基本信息
				'merNo'				=>	$data['account']['merNo'], //商户号
				'terNo'				=>	$data['account']['terNo'], //终端号
				'amount'			=>	$data['total_price'], //支付金额
				'currencyCode'		=>	$data['order_row']['Currency'], //交易币种
				'orderNo'			=>	$data['order_row']['OId'], //网店订单编号
				'goodsString'		=>	$goodsString, //货物信息
				//账单信息
				'cardCountry'		=>	db::get_value('country', "CId='{$data['order_row']['BillCId']}'", 'Acronym'), //账单国家
				'cardState'			=>	$data['order_row']['BillState'], //账单州(省)
				'cardCity'			=>	$data['order_row']['BillCity'],	//账单城市
				'cardAddress'		=>	$data['order_row']['BillAddressLine1'],	//账单详细地址
				'cardZipCode'		=>	$data['order_row']['BillZipCode'],	//账单邮编
				'cardFullName'		=>	$data['order_row']['BillFirstName'].' '.$data['order_row']['BillLastName'], //持卡人姓名
				'cardFullPhone'		=>	$data['order_row']['BillCountryCode'].$data['order_row']['BillPhoneNumber'], //持卡人电话
				'cardEmail'			=>	$data['order_row']['Email'], //持卡人邮箱
				//收货人信息
				'grCountry'			=>	db::get_value('country', "CId='{$data['order_row']['ShippingCId']}'", 'Acronym'), //收货国家
				'grState'			=>	$data['order_row']['ShippingState'], //收货州(省)
				'grCity'			=>	$data['order_row']['ShippingCity'], //收货城市
				'grAddress'			=>	$data['order_row']['ShippingAddressLine1'], //收货地址
				'grZipCode'			=>	$data['order_row']['ShippingZipCode'], //收货地址邮编
				'grphoneNumber'		=>	$data['order_row']['ShippingCountryCode'].$data['order_row']['ShippingPhoneNumber'], //收货人电话
				'grPerName'			=>	$data['order_row']['ShippingFirstName'].' '.$data['order_row']['ShippingLastName'], //收货人姓名
				'grEmail'			=>	$data['order_row']['Email'], //收货邮箱地址
				//信用卡信息
				'cardNO'			=>	$p_CardNo, //卡号 测试卡号：4111111111111111
				'expYear'			=>	$p_CardExpireYear, //卡有效期年
				'expMonth'			=>	$p_CardExpireMonth, //卡有效期月
				'cvv'				=>	$p_CardSecurityCode, //CVV
				//客户端信息
				'payIP'				=>	ly200::get_ip(), //客户的IP
				'merMgrURL'			=>	$data['domain'], //交易网址
				'returnURL'			=>	"{$data['domain']}/payment/leaderspay/returnUrl/{$data['order_row']['OId']}.html",	//返回支付结果到商户地址
				'notifyURL'			=>	"{$data['domain']}/payment/leaderspay/notifyUrl/{$data['order_row']['OId']}.html",	//异步/延时返回
				'bInfo'				=>	$_SERVER['HTTP_USER_AGENT'], //客户端浏览器信息
				'language'			=>	substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5),	//付页面的语言
				//其他信息
				'merremark'			=>	$data['order_row']['OId'],	//备注
			);
			
			//支付加密唯一签名，Hashcode
			$colmun_ary=array('EncryptionMode', 'CharacterSet', 'merNo', 'terNo', 'orderNo', 'currencyCode', 'amount', 'payIP', 'transType', 'transModel');
			$hashcode_ary='';
			foreach($colmun_ary as $v){
				$hashcode_ary[$v]=$form_data[$v];
			}
			$hashcode=self::array2String($hashcode_ary).$account['hash'];
			$form_data['hashcode']=@hash('sha256', $hashcode);
					
			$PayUrl='https://security.leaderspay.com/payment/api/payment';	//支付网关
			$curl=@curl_init(); 
			@curl_setopt($curl, CURLOPT_URL, $PayUrl);
			@curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			@curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			@curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			@curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			@curl_setopt($curl, CURLOPT_REFERER, $data['domain']);
			@curl_setopt($curl, CURLOPT_POST, 1);
			@curl_setopt($curl, CURLOPT_PORT, 443);
			@curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($form_data));
			@curl_setopt($curl, CURLOPT_TIMEOUT, 300);
			@curl_setopt($curl, CURLOPT_HEADER, 0);
			@curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$result=@curl_exec($curl);
			@curl_close($curl);
				
			//解析返回的JSON格式
			$return=str::json_data($result, 'decode');
			/*
			transType	交易类型
			orderNo		网店订单编号
			merNo		商户号
			terNo		终端号
			tradeNo		支付交易流水号
			currencyCode 订单币种
			amount		订单金额
			respCode	成功标志 00:成功，01:失败，02、03:待处理
			respMsg		返回交易成功失败信息
			hashcode	验证参数
			acquirer	英文账单名称
			*/
			
			//校验源字符串
			$s_return = array(
				'transType' 	    => $result->transType,
				'orderNo'		    => $result->orderNo,
				'merNo'				=> $result->merNo,
				'terNo'				=> $result->terNo,
				'currencyCode'	    => $result->currencyCode,
				'amount'		    => $result->amount,
				'tradeNo' 			=> $result->tradeNo,
				'respCode'			=> $result->respCode,
				'respMsg'		    => $result->respMsg
			);
			ksort($s_return);
			$return_hashcode=self::array2String($s_return).$account['hash'];
			$return_hashcode=@hash('sha256', $return_hashcode);
			
			$OId=$data['order_row']['OId'];
			$Log="Your payment operation failed! Reasons for failure: {$return['respMsg']}";		
			$jumpUrl="/cart/success/{$OId}.html";
			
			if($return_hashcode!='' && $return_hashcode==$form_data['hashcode']){ //检验成功
				$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
				if($return['respCode']=='00'){	//更新订单状态为支付成功
					$error=orders::orders_payment_result(1, $UserName, $data['order_row'], $return['respMsg']);
				}elseif($return['respCode']=='02' || $return['respCode']=='03'){	//更新订单状态为待处理
					$error=orders::orders_payment_result(2, $UserName, $data['order_row'], $return['respMsg']);
				}else{	//更新订单状态为其他状态
					$error=orders::orders_payment_result(0, $UserName, $data['order_row'], $return['respMsg']);
				}
			}
			
			ob_start();
			print_r($form_data);
			print_r($return);
			print_r($return_hashcode);
			echo "\r\n\r\nhashcode: $hashcode";
			echo "\r\n\r\n$Log";
			echo "\r\n\r\n$error";
			$log=ob_get_contents();
			ob_end_clean();
			
			file::write_file('/_pay_log_/leaderspay/'.date('Y_m/', $c['time']), $OId.'-'.mt_rand(10,99).".txt", $log);	//把返回数据写入文件
			js::location($jumpUrl, $Log, '.top');
		}else{
			$title='Credit Card Payment';
			include($c['root_path'].'/static/js/plugin/payment/CreditCard.php');
		}
    }
	
	function notifyUrl($data){
		global $c;
		$account=str::json_data(db::get_value('payment', "Method='LeadersPay'", 'Attribute'), 'decode');
		
		$transType		=	trim($_POST["transType"]); //交易类型
		$orderNo		=	trim($_POST["orderNo"]); //网店订单编号
		$merNo			=	trim($_POST["merNo"]); //商户号
		$terNo			=	trim($_POST["terNo"]); //终端号
		$tradeNo		=	trim($_POST["tradeNo"]); //支付交易流水号
		$currencyCode 	=	trim($_POST["currencyCode"]); //订单币种
		$amount			=	trim($_POST["amount"]); //订单金额
		$respCode		=	trim($_POST["respCode"]); //成功标志 00:成功，01:失败，02、03:待处理
		$respMsg		=	trim($_POST["respMsg"]); //返回交易成功失败信息
		$hashcode		=	trim($_POST["hashcode"]); //验证参数
		$acquirer		=	trim($_POST["acquirer"]); //英文账单名称
		
		$OId=$OrderNo;
		$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1, 3)");
		if($order_row){
			
			$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
			if($respCode=='00'){ //更新订单状态为支付成功
				$error=orders::orders_payment_result(1, $UserName, $order_row, $respMsg);
			}elseif($respCode=='02' || $respCode=='03'){ //更新订单状态为待处理
				$error=orders::orders_payment_result(2, $UserName, $order_row, $respMsg);
			}else{	//更新订单状态为其他状态
				$error=orders::orders_payment_result(0, $UserName, $order_row, $respMsg);
			}
			
			ob_start();
			print_r($_GET);
			print_r($_POST);
			echo "\r\n\r\n$error";
			$log=ob_get_contents();
			ob_end_clean();
			file::write_file('/_pay_log_/leaderspay/'.date('Y_m/', $c['time']), "{$OId}_notify.txt", $log);	//把返回数据写入文件
			
		}
	}
	
	function returnUrl($data){
		global $c;
		$account=str::json_data(db::get_value('payment', "Method='LeadersPay'", 'Attribute'), 'decode');
		
		$transType		=	trim($_GET["transType"]); //交易类型
		$orderNo		=	trim($_GET["orderNo"]); //网店订单编号
		$merNo			=	trim($_GET["merNo"]); //商户号
		$terNo			=	trim($_GET["terNo"]); //终端号
		$tradeNo		=	trim($_GET["tradeNo"]); //支付交易流水号
		$currencyCode 	=	trim($_GET["currencyCode"]); //订单币种
		$amount			=	trim($_GET["amount"]); //订单金额
		$respCode		=	trim($_GET["respCode"]); //成功标志 00:成功，01:失败，02、03:待处理
		$respMsg		=	trim($_GET["respMsg"]); //返回交易成功失败信息
		$hashcode		=	trim($_GET["hashcode"]); //验证参数
		$acquirer		=	trim($_GET["acquirer"]); //英文账单名称
		
		$OId=$OrderNo;
		$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1, 3)");
		!$order_row && js::location('/');
		
		$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
		if($respCode=='00'){ //更新订单状态为支付成功
			$error=orders::orders_payment_result(1, $UserName, $order_row, $respMsg);
		}elseif($respCode=='02' || $respCode=='03'){ //更新订单状态为待处理
			$error=orders::orders_payment_result(2, $UserName, $order_row, $respMsg);
		}else{	//更新订单状态为其他状态
			$error=orders::orders_payment_result(0, $UserName, $order_row, $respMsg);
		}
		
		ob_start();
		print_r($_GET);
		print_r($_POST);
		echo "\r\n\r\n$error";
		$log=ob_get_contents();
		ob_end_clean();
		file::write_file('/_pay_log_/leaderspay/'.date('Y_m/', $c['time']), "{$OId}_return.txt", $log);	//把返回数据写入文件
		
		js::location("/?m=user&a=order&d=view&OId=".$OId);
	}
	
	function array2String($arr){ //将数组转换成字符串
		if(is_null($arr) || !is_array($arr)) return false;
		$str='';
		$arr_length=count($arr)-1;
		foreach($arr as $key=>$value){
			$str.=$key.'='.$value.'&';				
		}
		return urldecode($str); //必须使用urldecode()方法处理明文字符串
	}
}
?>
