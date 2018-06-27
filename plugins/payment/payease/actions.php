<?
/**
 * 这是一个很牛逼的插件实现
 * 
 * @package     payment
 * @subpackage  payease 首信易(PayEase)
 * @category    payment
 * @author      鄙人
 * @link        http://www.ueeshop.com/
 */
/**
 * 需要注意的几个默认规则：
 * 1. 本插件类的文件名必须是action
 * 2. 插件类的名称必须是{插件名_actions}
 */
class payease_actions 
{ 
    //解析函数的参数是pluginManager的引用 
    function __construct(&$pluginManager){
        //注册这个插件 
        //第一个参数是钩子的名称 
        //第二个参数是pluginManager的引用 
        //第三个是插件所执行的方法 
        $pluginManager->register('payease', $this, '__config');
        $pluginManager->register('payease', $this, 'do_payment');
        $pluginManager->register('payease', $this, 'returnUrl');
    }
	
	function __config($data){
		return @in_array($data, array('do_payment', 'returnUrl'))?'enable':'';
	}
     
    function do_payment($data){
		global $c;
		/**************************************************************************
			'https://pay.yizhifubj.com/prs/user_payment.checkit',	//标准提交接口
			'https://pay.yizhifubj.com/customer/gb/pay_bank.jsp',	//直连银行快捷通道
			'https://pay.yizhifubj.com/customer/gb/pay_member.jsp',	//会员支付快捷通道
			
			https://www.5upay.com/customer/i18n/i18n_raw_order3_0.jsp	//多语言支付通道
			https://www.5upay.com/prs/e_user_payment.checkit			//英文支付通道
		 **************************************************************************/
		
		$_PayEase_currency=array(0=>'CNY', 1=>'USD', 2=>'EUR', 3=>'GBP', 4=>'JPY', 5=>'KER', 6=>'AUD', 7=>'RUB', 8=>'CHF', 9=>'HKD', 10=>'SGD', 11=>'MOP');
		@!in_array($data['order_row']['Currency'], $_PayEase_currency) && js::localtion('/', $c['lang_pack']['cart']['not_accept'], '.top');//不支持的货币
		!in_array($data['order_row']['OrderStatus'], array(1, 3)) && js::location("/account/orders/view{$data['order_row']['OId']}.html");
		
		foreach((array)$_PayEase_currency as $key=>$value){
			if($data['order_row']['Currency']==$value){
				$currency_id=$key;
				break;
			}
		}
		
		$form_data = array( 
			'v_mid'			=>	$data['account']['MerNo'],	//商户编号
			'v_rcvname'		=>	$data['account']['MerNo'],	//收货人姓名
			'v_rcvaddr'		=>	$data['account']['MerNo'],	//收货人地址
			'v_rcvtel'		=>	$data['account']['MerNo'],	//收货人电话
			'v_rcvpost'		=>	$data['account']['MerNo'],	//收货人邮政编码
			'v_amount'		=>	$data['total_price'],		//订单总金额
			'v_ymd'			=>	@date('Ymd', $data['order_row']['OrderTime']),	//订单产生日期
			'v_orderstatus'	=>	1,	//商户配货状态，0 为未配齐，1 为已配齐；一般商户该参数无实际意义，建议统一配置为 1（已配齐）
			'v_ordername'	=>	$data['account']['MerNo'],	//订货人姓名，建议统一用商户编号的值代替。
			'v_moneytype'	=>	$currency_id,		//支付币种
			'v_url'			=>	"{$data['domain']}/payment/payease/returnUrl/{$data['order_row']['OId']}.html?utm_nooverride=1",
			/*
			'v_shipstreet'	=>	$data['order_row']['ShippingAddressLine1'],	//送货街道地址
			'v_shipcity'	=>	$data['order_row']['ShippingCity'],	//送货城市
			'v_shipstate'	=>	$data['order_row']['ShippingState'],	//送货省/州
			'v_shippost'	=>	$data['order_row']['ShippingZipCode'],	//送货邮编
			'v_shipcountry'	=>	$data['order_row']['MerNo'],	//送货国家，为送货国家三位数字代
			'v_shipphone'	=>	$data['order_row']['ShippingCountryCode'].$data['order_row']['ShippingPhoneNumber'],	//送货电话
			'v_shipemail'	=>	$data['order_row']['Email'],	//送货邮箱
			*/
		);
		$form_data['v_oid']=$form_data['v_ymd'].'-'.$form_data['v_mid'].'-'.$data['order_row']['OId'];
		
		$MD5key=$data['account']['MD5key'];//商户的密钥
		$md5src=$form_data['v_moneytype'].$form_data['v_ymd'].$form_data['v_amount'].$form_data['v_rcvname'].$form_data['v_oid'].$form_data['v_mid'].$form_data['v_url'];
		$form_data['v_md5info']=@self::hmac($MD5key, $md5src);
		
		echo '<form name="payease_form" id="payease_form" method="post" action="https://www.5upay.com/prs/e_user_payment.checkit">';
			foreach((array)$form_data as $key=>$value){
				echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}
			echo '<input type="submit" style="display:none;" />';
		echo '</form>';
		echo '<script language="javascript">document.getElementById("payease_form").submit();</script>';
    } 
	
	function returnUrl($data){
		global $c;
		
		$account=str::json_data(db::get_value('payment', "Method='PayEase'", 'Attribute'), 'decode');
		$MD5key=$account['MD5key'];//商户的密钥
		
		$v_oid=$_GET['v_oid'];             //支付提交时的订单编号，此时返回
		$v_pstatus=$_GET['v_pstatus'];     //1 待处理,20 支付成功,30 支付失败
		$v_pstring=urldecode($_GET['v_pstring']);   //支付结果信息返回。当v_pstatus=1时-已提交。20-支付完成。30-支付失败
		$v_pmode=urldecode($_GET['v_pmode']);       //支付方式。
		$v_amount=$_GET['v_amount'];                //订单金额
		$v_moneytype=$_GET['v_moneytype'];          //币种
		$v_md5info=$_GET['v_md5info'];
		$v_md5money=$_GET['v_md5money'];
		$v_sign=$_GET['v_sign'];
		
		$md5src=array(
			0	=>	$v_oid.$v_pstatus.$v_pstring.$v_pmode,
			1	=>	$v_amount.$v_moneytype,
			2	=>	$v_oid.$v_pstatus.$v_amount.$v_moneytype,
		);
		$md5info=self::hmac($MD5key, $md5src[0]);
		$md5money=self::hmac($MD5key, $md5src[1]);
		$md5sign=self::hmac($MD5key, $md5src[2]);
		
		$ary=@explode('-', $v_oid);
		$OId=$ary[2];
		!$OId && $OId=$_GET['OId'];
		
		$jumpUrl="/cart/success/{$OId}.html";
		if($md5info==$v_md5info && $md5money==$v_md5money){ //$md5sign==$v_sign && 
			$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1, 2, 3)");
			!$order_row && js::location('/');
			$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
			if($v_pstatus=='20'){ //支付成功
				$error=orders::orders_payment_result(1, $UserName, $order_row, $v_pstring);
			}elseif($v_pstatus=='1'){ //支付处理中
				$error=orders::orders_payment_result(2, $UserName, $order_row, $v_pstring);	
			}elseif($v_pstatus=='30'){ //支付失败
				$error=orders::orders_payment_result(0, $UserName, $order_row, $v_pstring);	
			}
		}else{ //验证失败
			$error="Verification Failed";
		}
		
		ob_start();
		print_r($_GET);
		print_r($_POST);
		echo "\r\n\r\n md5info: $md5info";
		echo "\r\n\r\n md5money: $md5money";
		echo "\r\n\r\n md5sign: $md5sign";
		echo "\r\n\r\n$error";
		$log=ob_get_contents();
		ob_end_clean();
		file::write_file('/_pay_log_/payease/'.date('Y_m/', $c['time']), "{$OId}.txt", $log);	//把返回数据写入文件
		js::location($jumpUrl, $error, '.top');
	}
	
	function hmac($key, $data){//创建md5的HMAC
		$b=64;//md5加密字节长度
		
		if(strlen($key)>$b){
		  $key=pack("H*", md5($key));
		}
		$key=str_pad($key, $b, chr(0x00));
		$ipad=str_pad('', $b, chr(0x36));
		$opad=str_pad('', $b, chr(0x5c));
		$k_ipad=$key ^ $ipad;
		$k_opad=$key ^ $opad;
		
		return md5($k_opad.pack("H*", md5($k_ipad.$data)));
	}
}
?>