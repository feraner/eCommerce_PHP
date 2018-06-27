<?
/**
 * 这是一个很牛逼的插件实现
 * 
 * @package     payment
 * @subpackage  paydollar 传款易(PayDollar)
 * @category    payment
 * @author      鄙人
 * @link        http://www.ueeshop.com/
 */
/**
 * 需要注意的几个默认规则：
 * 1. 本插件类的文件名必须是action
 * 2. 插件类的名称必须是{插件名_actions}
 */
class paydollar_actions 
{ 
    //解析函数的参数是pluginManager的引用 
    function __construct(&$pluginManager){
        //注册这个插件 
        //第一个参数是钩子的名称 
        //第二个参数是pluginManager的引用 
        //第三个是插件所执行的方法 
        $pluginManager->register('paydollar', $this, '__config');
        $pluginManager->register('paydollar', $this, 'do_payment');
        $pluginManager->register('paydollar', $this, 'successUrl');
		$pluginManager->register('paydollar', $this, 'cancelUrl');
		$pluginManager->register('paydollar', $this, 'failUrl');
    }
	
	function __config($data){
		return @in_array($data, array('do_payment', 'successUrl', 'cancelUrl', 'failUrl'))?'enable':'';
	}
     
    function do_payment($data){
		global $c;
		
		$_PayDollar_currency=array(
			'CNY'	=>	'156',
			'HKD'	=>	'344',
			'USD'	=>	'840',
			'SGD'	=>	'702',
			'JPY'	=>	'392',
			'TWD'	=>	'901',
			'AUD'	=>	'036',
			'EUR'	=>	'978',
			'GBP'	=>	'826',
			'CAD'	=>	'124',
			'MOP'	=>	'446',
			'PHP'	=>	'608',
			'THB'	=>	'764',
			'MYR'	=>	'458',
			'IDR'	=>	'360',
			'KRW'	=>	'410',
			'SAR'	=>	'682',
			'NZD'	=>	'554',
			'AED'	=>	'784',
			'BND'	=>	'096',
			'VND'	=>	'704',
			'INR'	=>	'356'
		);
		!$_PayDollar_currency[$data['order_row']['Currency']] && js::localtion('/', $c['lang_pack']['cart']['not_accept'], '.top');//不支持的货币
		!in_array($data['order_row']['OrderStatus'], array(1, 3)) && js::location("/account/orders/view{$data['order_row']['OId']}.html");
		
		$_lang_ary=array('en'=>'E', 'de'=>'G', 'es'=>'S', 'fr'=>'F', 'jp'=>'J', 'ru'=>'R');
		
		$form_data=array(
			'merchantId'	=>	$data['account']['merchantId'],//商户ID
			'orderRef'		=>	$data['order_row']['OId'],	//订单号
			'mpsMode'		=>	'SCP',	//多种货币处理服务（MPS）模式，NIL或不填则关闭MPS，SCP开启MPS与简单的货币切换，DCC开启MPS与动态货币转换，MCP开启MPS与多货币定价
			'currCode'		=>	$_PayDollar_currency[$data['order_row']['Currency']],	//支付币种
			'amount'		=>	$data['total_price'],	//订单金额
			'lang'			=>	$_lang_ary[substr($c['lang'], 1)],	//语言版 “E”–English, “J”–Japanese, “F”–French, “G”–German, “R”–Russian, “S”–Spanish
			'payType'		=>	'N',	//付款类型 N 正常付款   H 授权付款
			'payMethod'		=>	'ALL',	//付款方式  ALL 所有可用的支付方式
			'cancelUrl'		=>	"{$data['domain']}/payment/paydollar/cancelUrl/{$data['order_row']['OId']}.html?utm_nooverride=1",	//取消交易返回链接
			'failUrl'		=>	"{$data['domain']}/payment/paydollar/failUrl/{$data['order_row']['OId']}.html?utm_nooverride=1",	//支付出错返回链接
			'successUrl'	=>	"{$data['domain']}/payment/paydollar/successUrl/{$data['order_row']['OId']}.html?utm_nooverride=1",//支付成功返回链接
			'remark'		=>	""
		);
		
		$md5src=$form_data['merchantId'].'|'.$form_data['orderRef'].'|'.$form_data['currCode'].'|'.$form_data['amount'].'|'.$form_data['payType'].'|'.$data['account']['secure_key'];
		$form_data['secureHash']=@sha1($md5src);
		
		//$postUrl='https://test.paydollar.com/b2cDemo/eng/payment/payForm.jsp';	//测试网关
		$postUrl='https://www.paydollar.com/b2c2/eng/payment/payForm.jsp';	//正式网关
		echo '<form name="paydollar_form" id="paydollar_form" method="post" action="'.$postUrl.'">';
			foreach((array)$form_data as $key=>$value){
				echo '<input type="hidden" name="'.$key.'" value="'.$value.'" />';
			}
			echo '<input type="submit" style="display:none;" />';
		echo '</form>';
		echo '<script language="javascript">document.getElementById("paydollar_form").submit();</script>';
    } 
     
    function successUrl($data){
		global $c;
		
		$account=str::json_data(db::get_value('payment', "Method='PayDollar'", 'Attribute'), 'decode');
		
		$src=$_POST['src'];	//Return bank host status code (secondary).
		$prc=$_POST['prc'];	//Return bank host status code (primary).
		
		$successcode=$_POST['successcode'];	//0- succeeded, 1- failure, Others - error
		$Ref=$_POST['Ref'];	//Merchant's Order Reference Number
		$PayRef=$_POST['PayRef'];	//PayDollar Payment Reference Number
		
		$Cur=$_POST['Cur'];	//Transaction Currency
		$Amt=$_POST['Amt'];	//Transaction Amount
		$payerAuth=$_POST['payerAuth'];	//Payer Authentication Status
		$errMsg=$_POST['errMsg'];
		
		$secureData=$src.'|'.$prc.'|'.$successcode.'|'.$Ref.'|'.$PayRef.'|'.$Cur.'|'.$Amt.'|'.$payerAuth.'|'.$account['secure_key'];
		$_secureHash= sha1($secureData);
		
		$secureHash=trim($_POST['secureHash']);	//Secure hash is used to authenticate the integrity of the response information and the identity of PayDollar. 
		
		$OId=$Ref;
		$jumpUrl="/cart/success/{$OId}.html";
		if($secureHash==$_secureHash){
			$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1, 2, 3)");
			!$order_row && js::location('/');
			$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
			if($successcode=='0'){	//支付成功
				$error=orders::orders_payment_result(1, $UserName, $order_row, $errMsg);
			}else{	//支付失败、支付错误
				$error=orders::orders_payment_result(0, $UserName, $order_row, $errMsg);
				$successcode==1 && $error='Payment failure';
			}
		}else{	//验证失败
			$error="Verification Failed";
		}
		
		ob_start();
		print_r($_GET);
		print_r($_POST);
		echo "\r\n\r\n$secureHash";
		echo "\r\n\r\n$_secureHash";
		echo "\r\n\r\n$error";
		$log=ob_get_contents();
		ob_end_clean();
		file::write_file('/_pay_log_/paydollar/'.date('Y_m/', $c['time']), "{$OId}.txt", $log);	//把返回数据写入文件
		
		js::location($jumpUrl, $error, '.top');
    }
	
	function cancelUrl($data){
		global $c;
		
		$OId=$_GET['OId'];
		js::location("/", 'Payment Error!');
	}
	
	function failUrl($data){
		global $c;
		
		$OId=$_GET['OId'];
		js::location("/", 'Payment Error!');
	}
}
?>