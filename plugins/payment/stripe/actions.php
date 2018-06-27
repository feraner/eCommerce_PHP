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
class stripe_actions 
{ 
    //解析函数的参数是pluginManager的引用 
    function __construct(&$pluginManager){
        //注册这个插件 
        //第一个参数是钩子的名称 
        //第二个参数是pluginManager的引用 
        //第三个是插件所执行的方法 
        $pluginManager->register('stripe', $this, '__config');
        $pluginManager->register('stripe', $this, 'do_payment');
    }
	
	function __config($data){
		return @in_array($data, array('do_payment'))?'enable':'';
	}
     
    function do_payment($data){
		global $c;
		$is_mobile=ly200::is_mobile_client(1);
		if($_POST){		
			$json_data=array(
				'amount'		=>	$data['total_price']*100,
				'currency'		=>	strtolower($data['order_row']['Currency']),
				'source'		=>	$_POST['stripeToken'],
				'description'	=>	$data['order_row']['OId'],
			);
			
			$ch=curl_init();
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: Bearer {$data['account']['Secret_key']}"));
			curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/charges');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($json_data));
			$output = curl_exec($ch);
			curl_close($ch);
			
			$output=str::json_data($output,'decode');
			$log='Payment failed';
			if($output['paid'] && $output['status']=='succeeded'){
				$log='payment successful';
				$UserName=(int)$_SESSION['User']['UserId']?$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']:'Tourist';
				$order_row=db::get_one('orders', "OId='{$data['order_row']['OId']}' and OrderStatus in(1,2,3)");
				orders::orders_payment_result(1, $UserName, $order_row, '');
			}else{
				$log.=':'.$output['error']['message'];
			}
			file::write_file('/_pay_log_/stripe/'.date('Y_m/', $c['time']), $data['order_row']['OId'].".txt", print_r($json_data,true).print_r($output,true));	//把返回数据写入文件
			js::location("/cart/success/{$data['order_row']['OId']}.html", $log, '.top');
		}else{
			include('stripe.php');
		}
    } 
}
?>