<?
/**
 * 这是一个很牛逼的插件实现
 * 
 * @package     payment
 * @subpackage  alipay(微信支付)
 * @category    payment
 * @author      鄙人
 * @link        http://www.ueeshop.com/
 */
/**
 * 需要注意的几个默认规则：
 * 1. 本插件类的文件名必须是action
 * 2. 插件类的名称必须是{插件名_actions}
 */
class alipay_actions 
{ 
    //解析函数的参数是pluginManager的引用 
    function __construct(&$pluginManager){
        //注册这个插件 
        //第一个参数是钩子的名称 
        //第二个参数是pluginManager的引用 
        //第三个是插件所执行的方法 
        $pluginManager->register('alipay', $this, '__config'); 
        $pluginManager->register('alipay', $this, 'do_payment');
		$pluginManager->register('alipay', $this, 'notify');
		$pluginManager->register('alipay', $this, 'returnn');
    }
	
	function __config($data){
		return @in_array($data, array('do_payment','notify','returnn'))?'enable':'';
	}
     
    function do_payment($data){
		global $c;
		include('config/alipay_service.php');

		$is_mobile=ly200::is_mobile_client(1);
		$order_rate=db::get_value('currency', "Currency='{$data['order_row']['Currency']}'", 'ExchangeRate');
		$cny_rate=db::get_value('currency', 'Currency="CNY"', 'ExchangeRate');
		$total_price=cart::currency_price($data['total_price'],$order_rate,$cny_rate);

		$partner         = $data['account']['partner'];				//合作伙伴ID
		$security_code   = $data['account']['security_code'];		//安全检验码
		$seller_email    = $data['account']['seller_email'];		//卖家支付宝帐户
		$_input_charset  = 'utf-8';									//字符编码格式 目前支持 GBK 或 utf-8
		$sign_type       = 'MD5';									//加密方式 系统默认(不要修改)
		$transport       = 'https';									//访问模式,你可以根据自己的服务器是否支持ssl访问而选择http以及https访问模式(系统默认,不要修改)
		$notify_url      = $data['domain'].'/payment/alipay/notify/'.$data['order_row']['OId'].'.html';
		$return_url      = $data['domain'].'/payment/alipay/returnn/'.$data['order_row']['OId'].'.html';
		$show_url        = $data['domain'];										//你网站商品的展示地址
		$service		 = $is_mobile?'alipay.wap.create.direct.pay.by.user':'create_direct_pay_by_user';

		$parameter = array(
			"service"         => $service,  						//交易类型
			"partner"         => $partner,          				//合作商户号
			'seller_id'		  => $partner,							
			"return_url"      => $return_url,      					//同步返回
			"notify_url"      => $notify_url,      					//异步返回
			"_input_charset"  => $_input_charset,  					//字符集，默认为GBK
			"subject"         => $data['order_row']['OId'],        	//商品名称，必填
			"body"            => $data['order_row']['OId'],        	//商品描述，必填
			"out_trade_no"    => date('Ymdhms'),      				//商品外部交易号，必填（保证唯一性）
			"total_fee"       => $total_price,            			//商品单价，必填（价格不能为0）
			"payment_type"    => "1",               				//默认为1,不需要修改
			"show_url"        => $show_url,         				//商品相关网站
			"seller_email"    => $seller_email      				//卖家邮箱，必填
		);

		$alipay = new alipay_service($parameter,$security_code,$sign_type);
		$link=$alipay->create_url();
		echo "<script>window.location =\"$link\";</script>";
    } 
	
	function notify(){
		global $c;
		include('config/alipay_notify.php');
		
		$OId=$_GET['OId'];
		$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1,2,3)");
		$payment_row=db::get_one('payment',"PId='{$order_row['PId']}'");
		$account=str::json_data($payment_row['Attribute'], 'decode');

		$partner         = $account['partner'];						//合作伙伴ID
		$security_code   = $account['security_code'];				//安全检验码
		$seller_email    = $account['seller_email'];				//卖家支付宝帐户
		$_input_charset  = 'utf-8';									//字符编码格式 目前支持 GBK 或 utf-8
		$sign_type       = 'MD5';									//加密方式 系统默认(不要修改)
		
		$alipay = new alipay_notify($partner,$security_code,$sign_type,$_input_charset,$transport);
		$verify_result = $alipay->notify_verify();
		
		if($verify_result && $_POST['trade_status'] == 'TRADE_FINISHED' && $order_row) {
			$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
			orders::orders_payment_result(1, $UserName, $order_row, '');
		}
		
		ob_start();
		print_r($_GET);
		print_r($_POST);
		echo "\r\n\r\n$verify_result";
		$log=ob_get_contents();
		ob_end_clean();
		file::write_file('/_pay_log_/alipay/'.date('Y_m/', $c['time']), "{$OId}_notify.txt", $log);	//把返回数据写入文件
	}
	
	function returnn(){
		global $c;
		include('config/alipay_notify.php');
		
		$OId=$_GET['OId'];
		$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1,2,3)");
		$payment_row=db::get_one('payment',"PId='{$order_row['PId']}'");
		$account=str::json_data($payment_row['Attribute'], 'decode');
		
		$partner         = $account['partner'];						//合作伙伴ID
		$security_code   = $account['security_code'];				//安全检验码
		$seller_email    = $account['seller_email'];				//卖家支付宝帐户
		$_input_charset  = 'utf-8';									//字符编码格式 目前支持 GBK 或 utf-8
		$sign_type       = 'MD5';									//加密方式 系统默认(不要修改)
		
		unset($_GET['m'],$_GET['a'],$_GET['d'],$_GET['OId']);
		$alipay = new alipay_notify($partner,$security_code,$sign_type,$_input_charset,$transport);
		$verify_result = $alipay->return_verify();
		
		if($verify_result && $order_row){
			$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
			orders::orders_payment_result(1, $UserName, $order_row, '');
			$success_url="{$data['domain']}/cart/success/{$OId}.html";
			echo "<script>window.location =\"$success_url\";</script>";
		}
		
		ob_start();
		print_r($_GET);
		print_r($_POST);
		echo "\r\n\r\n$verify_result";
		$log=ob_get_contents();
		ob_end_clean();
		file::write_file('/_pay_log_/alipay/'.date('Y_m/', $c['time']), "{$OId}_returnn.txt", $log);	//把返回数据写入文件
	}
}
?>