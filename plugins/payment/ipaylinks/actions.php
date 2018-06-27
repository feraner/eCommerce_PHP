<?php
/**
 * 这是一个很牛逼的插件实现
 * 
 * @package     payment
 * @subpackage  ipaylinks iPayLinks(iPayLinks)
 * @category    payment
 * @author      鄙人
 * @link        http://www.ueeshop.com/
 */
/**
 * 需要注意的几个默认规则：
 * 1. 本插件类的文件名必须是action
 * 2. 插件类的名称必须是{插件名_actions}
 */
class ipaylinks_actions 
{ 
    //解析函数的参数是pluginManager的引用 
    function __construct(&$pluginManager){
        //注册这个插件 
        //第一个参数是钩子的名称 
        //第二个参数是pluginManager的引用 
        //第三个是插件所执行的方法 
        $pluginManager->register('ipaylinks', $this, '__config');
        $pluginManager->register('ipaylinks', $this, 'do_payment');
		$pluginManager->register('ipaylinks', $this, 'returnUrl');
    }
	
	function __config($data){
		return @in_array($data, array('do_payment', 'returnUrl'))?'enable':'';
	}
     
    function do_payment($data){
		global $c;
		$is_mobile=ly200::is_mobile_client(1);
		
		!in_array($data['order_row']['OrderStatus'], array(1, 3)) && js::location("/account/orders/view{$data['order_row']['OId']}.html");
		
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
				'ID'	=>	'INA',
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
			for($i=0;$i<10;$i++){ $yearArr[]=$year+$i; }
			
			(!$p_CardNo || !is_numeric($p_CardNo) || @strlen($p_CardNo)!=16 || !@in_array($p_CardExpireMonth, $monthArr) || !@in_array($p_CardExpireYear, $yearArr) || !$p_CardSecurityCode || !is_numeric($p_CardSecurityCode) || @strlen($p_CardSecurityCode)!=3 ) && js::back();
			
			//产品信息
			$goodsAry=array();
			$order_products_list_row=db::get_all('orders_products_list', "OrderId='{$data['order_row']['OrderId']}'", '*', 'LId asc');
			foreach($order_products_list_row as $k=>$v){
				$goodsAry['goodsInfo'][$k]=array('goodsName'=>$v['Name'], 'quantity'=>$v['Qty'], 'goodsPrice'=>sprintf('%01.2f', cart::iconv_price($v['Price'], 2, $orders_row['Currency'], 0)));
			}
			//$goodsString=str::json_data(str::str_code($goodsAry, 'stripslashes'));
			
			$BillAcronym=db::get_value('country', "CId='{$data['order_row']['BillCId']}'", 'Acronym');
			$ShippingAcronym=db::get_value('country', "CId='{$data['order_row']['ShippingCId']}'", 'Acronym');
			
			$form_data=array( 
				//订单信息
				'version'			=>	'1.1', //版本号
				'orderId'			=>	'UEESHOP-'.$data['order_row']['OId'], //订单编号
				'goodsName'			=>	$goodsAry['goodsInfo'][0], //商品名称
				'goodsDesc'			=>	$goodsAry['goodsInfo'][0], //商品描述
				'submitTime'		=>	date('YmdHis', $data['order_row']['OrderTime']), //订单提交时间
				'customerIP'		=>	ly200::get_ip()?ly200::get_ip():ly200::get_server_ip(), //客户下单IP
				'siteId'			=>	$data['account']['siteId'], //商户网站域名
				'orderAmount'		=>	(float)$data['total_price']*100, //订单总金额
				'tradeType'			=>	'1001', //交易类型
				'payType'			=>	'EDC', //EDC DCC
				'currencyCode'		=>	$data['order_row']['Currency'], //币种
				'borrowingMarked'	=>	'0', //资金来源借贷标识
				'noticeUrl'			=>	"{$data['domain']}/cart/success/{$data['order_row']['OId']}.html", //异步通知地址
				'partnerId'			=>	$data['account']['partnerId'], //会员号
				'mcc'				=>	'4000', //行业
				//账单信息
				'billFirstName'		=>	$data['order_row']['BillFirstName'],
                'billLastName'		=>	$data['order_row']['BillLastName'],
                'billPhoneNumber'	=>	$data['order_row']['BillCountryCode'].$data['order_row']['BillPhoneNumber'], //电话
                'billEmail'			=>	$data['order_row']['Email'], //邮箱
                'billAddress'		=>	$data['order_row']['BillAddressLine1'], //联系地址
                'billCity'			=>	$data['order_row']['BillCity'], //城市
                'billState'			=>	$data['order_row']['BillState'], //省份/州
                'billPostalCode'	=>	$data['order_row']['BillZipCode'], //邮政编码
                'billCountryCode'	=>	$CountryArray[$BillAcronym], //国家
				//收货信息
				'shippingFirstName'	=>	$data['order_row']['ShippingFirstName'], //收货人名
                'shippingLastName'	=>	$data['order_row']['ShippingLastName'], //收货人姓
                'shippingAddress'	=>	$data['order_row']['ShippingAddressLine1'], //详细地址
                'shippingCity'		=>	$data['order_row']['ShippingCity'], //收货城市
                'shippingState'		=>	$data['order_row']['ShippingState'], //收货省份/州
                'shippingCountryCode'=>	$CountryArray[$ShippingAcronym], //收货国家
                'shippingPostalCode'=>	$data['order_row']['ShippingZipCode'], //收货邮编
                'shippingMail'		=>	$data['order_row']['Email'], //收货人邮箱
                'shippingPhoneNumber'=>	$data['order_row']['ShippingCountryCode'].$data['order_row']['ShippingPhoneNumber'], //收货人电话
				//信用卡信息
				'payMode'			=>	'10', //国际信用卡
				'cardHolderNumber'	=>	$p_CardNo, //卡号	4414444444444444
				'cardHolderFirstName'=>	$data['order_row']['BillFirstName'], //持卡人名
				'cardHolderLastName'=>	$data['order_row']['BillLastName'], //持卡人姓
				'cardHolderEmail'	=>	$data['order_row']['Email'], //持卡人联系邮箱
				'cardHolderPhoneNumber'=>	$data['order_row']['BillCountryCode'].$data['order_row']['BillPhoneNumber'], //持卡人手机
				'cardExpirationYear'=>	substr($p_CardExpireYear, 2, 2), //卡有效期年 2015年的15
				'cardExpirationMonth'=>	$p_CardExpireMonth, //卡有效期月 1月的01
				'securityCode'		=>	$p_CardSecurityCode, //CVV2
				//安全信息
				'deviceFingerprintId'=>	$data['order_row']['OId'], //设备指纹ID
				'charset'			=>	'1', //编码方式，1表示UTF-8
				'signType'			=>	'2', //签名类型，2表示MD5方式
				'remark'			=>	'UEESHOP'
			);
				
			//MD5加密
			$pkey=$data['account']['publicKey']; //商户公钥
			ksort($form_data);
			$sign_src='';
			$i=0;
			foreach($form_data as $k=>$v){
				$sign_src.=($i?'&':'').$k.'='.trim($v);
				++$i;
			}
			$SignInfo=md5($sign_src.'&pkey='.$pkey);
			$form_data['signMsg']=$SignInfo;
			
			//$url='http://api.test.ipaylinks.com/webgate/crosspay.htm'; //测试支付网关
			$url='http://api.ipaylinks.com/webgate/crosspay.htm'; //支付网关
			$curl=@curl_init(); 
			@curl_setopt($curl, CURLOPT_URL, $url);
			@curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			@curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			@curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
			@curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
			@curl_setopt($curl, CURLOPT_REFERER, ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on')?'https://':'http://').$_SERVER['HTTP_HOST']);
			@curl_setopt($curl, CURLOPT_POST, 1);
			@curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($form_data));
			@curl_setopt($curl, CURLOPT_TIMEOUT, 300);
			@curl_setopt($curl, CURLOPT_HEADER, 0);
			@curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
			$result=@curl_exec($curl);
			@curl_close($curl);
			
			//解析返回的xml参数
			$payXml=simplexml_load_string($result);
			
			$return=array(
				'orderId'			=>	(String)$payXml->orderId,		//返回的商户订单号
				'resultCode'		=>	(String)$payXml->resultCode,	//返回的处理结果码
				'resultMsg'			=>	(String)$payXml->resultMsg,		//返回的处理结果描述
				'orderAmount'		=>	(String)$payXml->orderAmount,	//返回的订单金额
				'currencyCode'		=>	(String)$payXml->currencyCode,	//返回的交易币种
				'merchantBillName'	=>	(String)$payXml->merchantBillName, //返回的商户账单名
				'settlementCurrencyCode'=>	(String)$payXml->settlementCurrencyCode, //返回的结算币种
				'acquiringTime'		=>	(String)$payXml->acquiringTime,	//返回的收单时间
				'completeTime'		=>	(String)$payXml->completeTime,	//返回的处理完成时间
				'dealId'			=>	(String)$payXml->dealId,		//返回的支付流水号
				'partnerId'			=>	(String)$payXml->partnerId,		//返回的会员号
				'remark'			=>	(String)$payXml->remark,		//返回的扩展字段
				'language'			=>	(String)$payXml->language,		//返回的显示语言
				'settlementRates'	=>	(String)$payXml->settlementRates,//返回的结算汇率
				'rates'				=>	(String)$payXml->rates,			//返回的交易汇率
				'charset'			=>	(String)$payXml->charset,		//返回的编码方式
				'signType'			=>	(String)$payXml->signType,		//返回的签名类型
				'signMsg'			=>	(String)$payXml->signMsg,		//返回的签名字符串
			);
			
			$OIdAry=@explode('-', $return['orderId']);
			$OIdAry && $OId=$OIdAry[1];
			!$OId && $OId=$data['order_row']['OId'];
			$jumpUrl="/cart/success/{$OId}.html";
			$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
			
			//if($return['signMsg'] && $return['signMsg']==$SignInfo){
				if($return['resultCode']=='0000'){ //更新订单状态为支付成功
					$error=orders::orders_payment_result(1, $UserName, $data['order_row'], $return['resultMsg']);
				}elseif($return['resultCode']=='0300' || $return['resultCode']=='0330'){ //更新订单状态为待处理（请求接受成功）（原交易未完成，操作失败）
					$error=orders::orders_payment_result(2, $UserName, $data['order_row'], $return['resultMsg']);
				}else{	//更新订单状态为其他状态
					$error=orders::orders_payment_result(0, $UserName, $data['order_row'], $return['resultMsg']);
				}
			//}else{
				//$error=orders::orders_payment_result(0, $UserName, $data['order_row'], $return['resultMsg']);
				//$error.=' VERIFIED Error!';
			//}
				
			ob_start();
			print_r($_GET);
			print_r($_POST);
			print_r($form_data);
			print_r($result);
			print_r($return);
			echo "\r\n\r\nMD5sign: $SignInfo";
			echo "\r\n\r\nMD5info: {$return['signMsg']}";
			echo "\r\n\r\n$error";
			$log=ob_get_contents();
			ob_end_clean();
			file::write_file('/_pay_log_/ipaylinks/'.date('Y_m/', $c['time']), $OId.'-'.mt_rand(10,99).".txt", $log);	//把返回数据写入文件
			js::location($jumpUrl, $error, '.top');
		}else{
			$title='Credit Card Payment';
			include($c['root_path'].'/static/js/plugin/payment/CreditCard.php');
		}
    }
	
	function returnUrl($data){
		global $c;
		return false;
	}
}
?>