<?
/**
 * 这是一个很牛逼的插件实现
 * 
 * @package     payment
 * @subpackage  glbpay 九盈支付(Glbpay)
 * @category    payment
 * @author      鄙人
 * @link        http://www.ueeshop.com/
 */
/**
 * 需要注意的几个默认规则：
 * 1. 本插件类的文件名必须是action
 * 2. 插件类的名称必须是{插件名_actions}
 */
class glbpay_actions 
{ 
    //解析函数的参数是pluginManager的引用 
    function __construct(&$pluginManager){
        //注册这个插件 
        //第一个参数是钩子的名称 
        //第二个参数是pluginManager的引用 
        //第三个是插件所执行的方法 
        $pluginManager->register('glbpay', $this, '__config');
        $pluginManager->register('glbpay', $this, 'do_payment');
        $pluginManager->register('glbpay', $this, 'notify');
    }
	
	function __config($data){
		return @in_array($data, array('do_payment', 'notify'))?'enable':'';
	}
     
    function do_payment($data){
		global $c;
		$is_mobile=ly200::is_mobile_client(1);
		
		//支持货币：CNY(人民币)、USD(美元)、GBP(英镑)、EUR(欧元)、HKD(港币)、AUD(澳元)、CAD(加元)、CHF(瑞士法郎)、DKK(丹麦克朗)、SEK(瑞典克郞)、NOK(挪威克朗)
		$glb_currency=array('CNY', 'USD', 'GBP', 'EUR', 'HKD', 'AUD', 'CAD', 'CHF', 'DKK', 'SEK', 'NOK');
		@!in_array($data['order_row']['Currency'], $glb_currency) && js::location('/', $c['lang_pack']['cart']['not_accept'], '.top');//不支持的货币
		!in_array($data['order_row']['OrderStatus'], array(1,3)) && js::location("/account/orders/view{$data['order_row']['OId']}.html");
		
		$year=@date('Y', $c['time']);
		
		if($_POST){
			@extract($_POST, EXTR_PREFIX_ALL, 'p');
			$CountryArray = array(
				'US'	=>	'USA',
				'IT'	=>	'ITA',
				'ES'	=>	'ESP',
				'PT'	=>	'PRT',
				'GB'	=>	'GBR',
				'FR'	=>	'FRA',
				'NL'	=>	'NLD',
				'DE'	=>	'DEU',
				'RU'	=>	'RUS',
				'CN'	=>	'CHN',
				'AF'	=>	'AFG',
				'AL'	=>	'ALB',
				'AD'	=>	'AND',
				'AI'	=>	'AGO',
				'AM'	=>	'ARM',
				'AW'	=>	'ABW',
				'AU'	=>	'AUS',
				'AE'	=>	'ARE',
				'AR'	=>	'ARG',
				'AG'	=>	'ATG',
				'AT'	=>	'AUT',
				'AZ'	=>	'AZE',
				'AN'	=>	'ANT',
				'BB'	=>	'BRB',
				'BD'	=>	'BGD',
				'BE'	=>	'BEL',
				'BZ'	=>	'BLZ',
				'BJ'	=>	'BEN',
				'BT'	=>	'BTN',
				'BO'	=>	'BOL',
				'BA'	=>	'BIH',
				'BW'	=>	'BWA',
				'BN'	=>	'BRN',
				'BG'	=>	'BGR',
				'BH'	=>	'BHR',
				'BM'	=>	'BMU',
				'BR'	=>	'BRA',
				'BS'	=>	'BHS',
				'BF'	=>	'BFA',
				'BI'	=>	'BDI',
				'CM'	=>	'CMR',
				'CA'	=>	'CAN',
				'CV'	=>	'CPV',
				'CF'	=>	'CAF',
				'KM'	=>	'COM',
				'CG'	=>	'COG',
				'CH'	=>	'CHE',
				'CL'	=>	'CHL',
				'CO'	=>	'COL',
				'CR'	=>	'CRI',
				'CY'	=>	'CYP',
				'CZ'	=>	'CZE',
				'DK'	=>	'DNK',
				'DJ'	=>	'DJI',
				'DZ'	=>	'DZA',
				'DO'	=>	'DOM',
				'EC'	=>	'ECU',
				'EG'	=>	'EGY',
				'ER'	=>	'ERI',
				'EE'	=>	'EST',
				'ET'	=>	'ETH',
				'EH'	=>	'ESH',
				'FJ'	=>	'FJI',
				'FI'	=>	'FIN',
				'GF'	=>	'GUF',
				'GA'	=>	'GAB',
				'GM'	=>	'GMB',
				'GE'	=>	'GEO',
				'GH'	=>	'GHA',
				'GI'	=>	'GIB',
				'GD'	=>	'GRD',
				'GR'	=>	'GRC',
				'GP'	=>	'GLP',
				'GT'	=>	'GTM',
				'GY'	=>	'GUY',
				'GW'	=>	'GNB',
				'HT'	=>	'HTI',
				'HN'	=>	'HND',
				'HK'	=>	'HKG',
				'HU'	=>	'HUN',
				'ID'	=>	'IDN',
				'IE'	=>	'IRL',
				'IL'	=>	'ISR',
				'IN'	=>	'IND',
				'IS'	=>	'ISL',
				'JM'	=>	'JAM',
				'JP'	=>	'JPN',
				'JO'	=>	'JOR',
				'KZ'	=>	'KAZ',
				'KE'	=>	'KEN',
				'KG'	=>	'KGZ',
				'KR'	=>	'KOR',
				'KW'	=>	'KWT',
				'KN'	=>	'KNA',
				'LB'	=>	'LBN',
				'LY'	=>	'LBY',
				'LI'	=>	'LIE',
				'LK'	=>	'LKA',
				'LT'	=>	'LTU',
				'LU'	=>	'LUX',
				'LV'	=>	'LVA',
				'LC'	=>	'LCA',
				'MC'	=>	'MCO',
				'MO'	=>	'MAC',
				'MK'	=>	'MKD',
				'MG'	=>	'MDG',
				'MW'	=>	'MWI',
				'MV'	=>	'MDV',
				'ML'	=>	'MLI',
				'MT'	=>	'MLT',
				'MQ'	=>	'MTQ',
				'MR'	=>	'MRT',
				'MU'	=>	'MUS',
				'ME'	=>	'MEX',
				'MY'	=>	'MYS',
				'MD'	=>	'MDA',
				'MN'	=>	'MNG',
				'MA'	=>	'MAR',
				'MZ'	=>	'MOZ',
				'NA'	=>	'NAM',
				'NP'	=>	'NPL',
				'NI'	=>	'NIC',
				'NE'	=>	'NER',
				'NG'	=>	'NGA',
				'NO'	=>	'NOR',
				'NZ'	=>	'NZL',
				'OM'	=>	'OMN',
				'PK'	=>	'PAK',
				'PA'	=>	'PAN',
				'PG'	=>	'PNG',
				'PY'	=>	'PRY',
				'PE'	=>	'PER',
				'PH'	=>	'PHL',
				'PL'	=>	'POL',
				'QA'	=>	'QAT',
				'RO'	=>	'ROU',
				'RW'	=>	'RWA',
				'SM'	=>	'SMR',
				'ST'	=>	'STP',
				'SA'	=>	'SAU',
				'SN'	=>	'SEN',
				'RS'	=>	'SRB',
				'SZ'	=>	'SWZ',
				'SC'	=>	'SYC',
				'SL'	=>	'SLE',
				'SO'	=>	'SOM',
				'SR'	=>	'SUR',
				'SW'	=>	'SWE',
				'SG'	=>	'SGP',
				'SK'	=>	'SVK',
				'SI'	=>	'SVN',
				'SV'	=>	'SLV',
				'SY'	=>	'SYR',
				'TJ'	=>	'TJK',
				'TZ'	=>	'TZA',
				'TH'	=>	'THA',
				'TG'	=>	'TGO',
				'TN'	=>	'TUN',
				'TR'	=>	'TUR',
				'TT'	=>	'TTO',
				'TW'	=>	'TWN',
				'TM'	=>	'TKM',
				'TC'	=>	'TCA',
				'UG'	=>	'UGA',
				'UA'	=>	'UKR',
				'UY'	=>	'URY',
				'UZ'	=>	'UZB',
				'VE'	=>	'VEN',
				'VC'	=>	'VCT',
				'VN'	=>	'VNM',
				'YE'	=>	'YEM',
				'ZM'	=>	'ZMB',
				'ZA'	=>	'ZAF'
			);
			
			$monthArr=array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
			$yearArr=array();
			for($i=0;$i<10;$i++){
				$yearArr[]=$year+$i;
			}
			
			(!$p_CardNo || !is_numeric($p_CardNo) || @strlen($p_CardNo)!=16 || !@in_array($p_CardExpireMonth, $monthArr) || !@in_array($p_CardExpireYear, $yearArr) || !$p_CardSecurityCode || !is_numeric($p_CardSecurityCode) || @strlen($p_CardSecurityCode)!=3 ) && js::back();
		
			$CId=(int)$data['order_row']['BillCId']?(int)$data['order_row']['BillCId']:(int)$data['order_row']['ShippingCId'];
			$Acronym=db::get_value('country', "CId='{$CId}'", 'Acronym');
			$ShippingAcronym=$Acronym;
			$CId!=(int)$data['order_row']['ShippingCId'] && $ShippingAcronym=db::get_value('country', "CId='{$data['order_row']['ShippingCId']}'", 'Acronym');
			
			$form_data=array( 
				//***************************支付基本信息部分***************************
				'version'			=>	'1.1.2',	//接口版本号
				'txntype'			=>	'sale',
				'merchantid'		=>	$data['account']['mechantid'],	//商户号		
				'orderid'			=>	$data['order_row']['OId'],	//订单号
				'orderdate'			=>	@date('YmdHis', $data['order_row']['OrderTime']),	//订单时间
				'ordercurrency'		=>	strtoupper($data['order_row']['Currency']),//$currency_id,,	//交易币种
				'orderamount'		=>	$data['total_price'],	//支付金额
				//信用卡信息
				'cardnumber'		=>	$p_CardNo,	//卡号	41111111111111111
				'expyear'			=>	$p_CardExpireYear,	//卡有效期年	2020
				'expmonth'			=>	$p_CardExpireMonth,	//卡有效期月	11
				'cvv'				=>	$p_CardSecurityCode,	//CVV2	123
				'issuebank'			=>	$p_IssuingBank,	//发卡行
				
				//***************************付款人信息部分***************************
				'deliveryfirstname'	=>	$data['order_row']['ShippingFirstName'],	//收货人姓
				'deliverylastname'	=>	$data['order_row']['ShippingLastName'],	//收货人名
				'deliveryaddress'	=>	$data['order_row']['ShippingAddressLine1'],	//收货人地址
				'deliverycountry'	=>	$CountryArray[$ShippingAcronym],//收货人国家
				'deliverystate'		=>	$data['order_row']['ShippingState'],	//收货人省份
				'deliverycity'		=>	$data['order_row']['ShippingCity'],	//收货人城市
				'deliveryemail'		=>	$data['order_row']['Email'],	//收货人电邮
				'deliveryphone'		=>	$data['order_row']['ShippingPhoneNumber'],	//收货人电话
				'deliverypost'		=>	$data['order_row']['ShippingZipCode'],	//收货人邮编
		
				'billingfirstname'	=>	$data['order_row']['BillFirstName']?$data['order_row']['BillFirstName']:$data['order_row']['ShippingFirstName'],	//收货人姓
				'billinglastname'	=>	$data['order_row']['BillLastName']?$data['order_row']['BillLastName']:$data['order_row']['ShippingLastName'],	//收货人名
				'billingaddress'	=>	$data['order_row']['BillAddressLine1']?$data['order_row']['BillAddressLine1']:$data['order_row']['ShippingAddressLine1'],	//收货人地址
				'billingcountry'	=>	$CountryArray[$Acronym],//收货人国家
				'billingstate'		=>	$data['order_row']['BillState']?$data['order_row']['BillState']:$data['order_row']['ShippingState'],	//收货人省份
				'billingcity'		=>	$data['order_row']['BillCity']?$data['order_row']['BillCity']:$data['order_row']['ShippingCity'],	//收货人城市
				'billingemail'		=>	$data['order_row']['Email'],	//收货人电邮
				'billingphone'		=>	$data['order_row']['BillPhoneNumber']?$data['order_row']['BillPhoneNumber']:$data['order_row']['ShippingPhoneNumber'],	//收货人电话
				'billingpost'		=>	$data['order_row']['BillZipCode']?$data['order_row']['BillZipCode']:$data['order_row']['ShippingZipCode'],	//收货人邮编
				//***************************付款人信息部分***************************
				
				'clientip'			=>	ly200::get_ip(),
				'storetype'			=>	'UEESHOP',
				'accessurl'			=>	"{$data['domain']}?utm_nooverride=1",	//回调地址		/payment/glbpay/notify/
			);
			
			//签名的表单字段名
			$signature=$data['account']['hashkey'];
			$signature_ary=array('accessurl', 'billingaddress', 'billingcity', 'billingcountry', 'billingemail', 'billingfirstname', 'billinglastname', 'billingphone', 'billingpost', 'billingstate', 'cardnumber', 'clientip', 'cvv', 'dcctxnid', 'deliveryaddress', 'deliverycity', 'deliverycountry', 'deliveryemail', 'deliveryfirstname', 'deliverylastname', 'deliveryphone', 'deliverypost', 'deliverystate', 'expmonth', 'expyear', 'issuebank', 'merchantid', 'orderamount', 'ordercurrency', 'orderdate', 'orderid', 'storetype', 'txntype', 'version');
			
			sort($signature_ary);
			foreach($signature_ary as $v){ $signature.=$form_data[$v]; }
			$form_data['signature']=@md5($signature);
			$json_data=str::json_data($form_data);
			
			$payUrl='https://pgw.glbpay.com/api/bgpay';
			$ch = curl_init($payUrl);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($json_data))
			);
			$result=str::json_data(@curl_exec($ch), 'decode');
			
			// 校验源字符串
			$signreturn_ary=array('version', 'merchantid', 'orderid', 'orderdate', 'ordercurrency', 'orderamount', 'cardnumber', 'remark1', 'remark2', 'remark3', 'txnid', 'txndate', 'status', 'respcode', 'respmsg');
			$md5src=$data['account']['hashkey'];
			sort($signreturn_ary);
			foreach($signreturn_ary as $v){ $md5src.=$result[$v]; }
			$md5sign=md5($md5src);
			
			$OId=$data['order_row']['OId'];
			//$responseLog="Your payment operation failed! Reasons for failure: {$result['respmsg']}";		
			$jumpUrl="/cart/success/{$OId}.html";
			
			if($result['signature']!='' && strtoupper($result['signature'])==strtoupper($md5sign)){	//检验成功
				$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
				if((int)$result['status']==1){ //更新订单状态为支付成功
					$error=orders::orders_payment_result(1, $UserName, $data['order_row'], $result['respmsg']);
				}else{ //更新订单状态为其他状态
					$error=orders::orders_payment_result(0, $UserName, $data['order_row'], $result['respmsg']);
				}
			}
			
			ob_start();
			echo "\r\n\r\n POST Data";
			print_r($form_data);
			echo "\r\n\r\n Return Data";
			print_r($result);
			echo "\r\n\r\n md5sign: $md5sign";
			echo "\r\n\r\n signature: ".$result['signature'];
			echo "\r\n\r\n response log: ".$result['respmsg'];
			echo "\r\n\r\n $error";
			$log=ob_get_contents();
			ob_end_clean();
			
			file::write_file('/_pay_log_/glbpay/'.date('Y_m/', $c['time']), $OId.'-'.mt_rand(10, 99).".txt", $log);	//把返回数据写入文件
			js::location($jumpUrl, $Log);	
		}else{
			$title='Credit Card Payment';
			$IssuingBank=1;
			include($c['root_path'].'/static/js/plugin/payment/CreditCard.php');
		}
    } 
	
	function notify($data){
		global $c;
		
		$account=str::json_data(db::get_value('payment', "Method='Glbpay'", 'Attribute'), 'decode');
		
		$data_ary=array(
			//***************************支付基本信息***************************
			'version'			=>	$_POST['version'],	//接口版本号
			'encoding'			=>	$_POST['encoding'],	//字符集
			'language'			=>	$_POST['language'],	//界面语言
			'merchantid'		=>	$_POST['merchantid'],	//商户号
			'transtype'			=>	$_POST['transtype'],	//交易类型
			'orderid'			=>	$_POST['orderid'],	//订单号
			'orderdate'			=>	$_POST['orderdate'],	//订单时间
			'currency'			=>	$_POST['currency'],	//交易币种
			'orderamount'		=>	$_POST['orderamount'],	//支付金额
			'callbackurl'		=>	$_POST['callbackurl'],
			'remark1'			=>	$_POST['remark1'],
			'remark2'			=>	$_POST['remark2'],
			'remark3'			=>	$_POST['remark3'],
			
			//***************************交易结果***************************
			'paycurrency'		=>	$_POST['paycurrency'],	//实际支付币种
			'payamount'			=>	$_POST['payamount'],	//实际支付金额
			'transid'			=>	$_POST['transid'],	//系统流水号
			'transdate'			=>	$_POST['transdate'],	//系统时间
			'status'			=>	$_POST['status'],	//支付结果(Y:支付成功 ,N:支付失败)
			'message'			=>	$_POST['message'],	//支付信息
			'signature'			=>	$_POST['signature']	//数字签名
		);
		
		$hashkey=$account['hashkey'];//商户的密钥
		
		//签名的表单字段名
		$signature='';
		$signature_ary=array('version','encoding','language','merchantid','transtype','orderid','orderdate','currency','orderamount','paycurrency','payamount','remark1','remark2','remark3','transid','transdate','status');
		foreach($signature_ary as $key){
			$signature.=$form_data[$key];
		}
		$signature=$hashkey.$signature;//密钥放最前面
		$signature_string=@md5($signature);//md5算法
		
		if(strtolower($signature_string)==strtolower($data_ary['signature'])){//验证数字签名
			$OId=$data_ary['orderid'];
			$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1, 2, 3)");
			!$order_row && js::location('/');
			$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
			
			if($data_ary['status']=='Y'){//支付成功
				$payment_result=orders::orders_payment_result(1, $UserName, $order_row, $_POST['respmsg']);
			}else{//支付失败
				$payment_result=orders::orders_payment_result(0, $UserName, $order_row, $_POST['respmsg']);
			}
		}else{//验证失败
			$payment_result='Validation failure!';
		}
		
		ob_start();
		print_r($_GET);
		print_r($_POST);
		echo "\r\n\r\n$signature_string";
		echo "\r\n\r\n$payment_result";
		$log=ob_get_contents();
		ob_end_clean();
		file::write_file('/_pay_log_/glbpay/'.date('Y_m/', $c['time']), "{$OId}-".rand(0, 1000).".txt", $log);	//把返回数据写入文件
	}
}
?>