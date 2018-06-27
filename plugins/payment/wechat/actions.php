<?
/**
 * 这是一个很牛逼的插件实现
 * 
 * @package     payment
 * @subpackage  wechat(微信支付)
 * @category    payment
 * @author      鄙人
 * @link        http://www.ueeshop.com/
 */
/**
 * 需要注意的几个默认规则：
 * 1. 本插件类的文件名必须是action
 * 2. 插件类的名称必须是{插件名_actions}
 */
class wechat_actions 
{ 
    //解析函数的参数是pluginManager的引用 
    function __construct(&$pluginManager){
        //注册这个插件 
        //第一个参数是钩子的名称 
        //第二个参数是pluginManager的引用 
        //第三个是插件所执行的方法 
        $pluginManager->register('wechat', $this, '__config'); 
        $pluginManager->register('wechat', $this, 'do_payment');
		$pluginManager->register('wechat', $this, 'notify');
		$pluginManager->register('wechat', $this, 'query');
    }
	
	function __config($data){
		return @in_array($data, array('do_payment','notify','query'))?'enable':'';
	}
     
    function do_payment($data){
		global $c;

		$success_url="{$data['domain']}/cart/success/{$data['order_row']['OId']}.html";
		$is_mobile=ly200::is_mobile_client(1);
		$order_rate=db::get_value('currency', "Currency='{$data['order_row']['Currency']}'", 'ExchangeRate');
		$cny_rate=db::get_value('currency', 'Currency="CNY"', 'ExchangeRate');
		$total_price=cart::currency_price($data['total_price'],$order_rate,$cny_rate);
		$out_trade_no=$data['order_row']['OId'].'-'.date('His');
		
		$config=array(
			'appid'				=>	$data['account']['appid'],	//AppId
			'mch_id'			=>	$data['account']['mch_id'],	//商户号
			'nonce_str'			=>	str::rand_code(32),
			'body'				=>	$data['order_row']['OId'],
			'out_trade_no'		=>	$out_trade_no,
			'total_fee'			=>	$total_price*100,
			'spbill_create_ip'	=>	ly200::get_ip(),
			'notify_url'		=>	$data['domain'].'/payment/wechat/notify/'.$data['order_row']['OId'].'.html',
			'product_id'		=>	$data['order_row']['OId'],
			'trade_type'		=>	$is_mobile?'MWEB':'NATIVE',
			'fee_type'			=>	'CNY'	//$data['order_row']['Currency']
			
		);
		$partner_key=$data['account']['partner_key'];	//秘钥
		
		$str='';
		ksort($config);
		foreach($config as $k=>$v){
			$str.="$k=$v&";
		}
		
		$config['sign']=strtoupper(md5($str.'key='.$partner_key));

		$tx_wechat=new tx_wechat();
		$result=$tx_wechat->api('', 'pay_unifiedorder', '', tx_wechat::array_to_xml($config), false, true);
		if($result['return_code']=='SUCCESS'){
			$wechat_qrcode=tx_wechat::qrcode($result['code_url'],$out_trade_no,6);
			include('wechat.php');
		}else{
			print_r($result['return_msg']);
		}
    } 
	function notify(){
		global $c;
		
		$postStr=@file_get_contents('php://input');
		$postStr=(array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
		
		$OId=explode('-',$postStr['out_trade_no']);
		$OId=$OId[0];
		$order_row=db::get_one('orders', "OId='$OId' and OrderStatus in(1,2,3)");
		$payment_row=db::get_one('payment',"PId='{$order_row['PId']}'");
		$account=str::json_data($payment_row['Attribute'], 'decode');
		
		$sign=$postStr['sign'];
		unset($postStr['sign']);
		$str='';
		ksort($postStr);
		foreach($postStr as $k=>$v){
			$str.="$k=$v&";
		}
		$partner_key=$account['partner_key'];	//秘钥
		
		$status='fail';
		if($sign==strtoupper(md5($str.'key='.$partner_key)) && $postStr['return_code']=='SUCCESS' && $postStr['result_code']=='SUCCESS' && $order_row){	//验证签名,通讯成功,支付成功
			$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
			//db::update('orders', "OId='$OId'", array('TranSactionId'=>$postStr['transaction_id']));
			orders::orders_payment_result(1, $UserName, $order_row, '');
			$status='success';
		}
		echo $status;
		
		ob_start();
		print_r($postStr);
		echo "\r\n\r\n$status";
		$log=ob_get_contents();
		ob_end_clean();
		file::write_file('/_pay_log_/wechat/'.date('Y_m/', $c['time']), "{$OId}.txt", $log);	//把返回数据写入文件
	}
	
	function query(){
		global $c;
		
		file::del_file($_POST['qrcode']);
		$OId=(int)$_GET['OId'];
		$order_row=db::get_one('orders', "OId='$OId'");
		$status=in_array($order_row['OrderStatus'],array(4,5,6))?1:0;
		ly200::e_json('',$status);
	}
}
?>