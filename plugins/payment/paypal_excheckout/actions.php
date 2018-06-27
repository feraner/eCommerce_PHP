<?
/**
 * 这是一个很牛逼的插件实现
 * 
 * @package     payment
 * @subpackage  paypal_excheckout
 * @category    payment
 * @author      鄙人
 * @link        http://www.ueeshop.com/
 */
/**
 * 需要注意的几个默认规则：
 * 1. 本插件类的文件名必须是action
 * 2. 插件类的名称必须是{插件名_actions}
 */
class paypal_excheckout_actions 
{ 
    //解析函数的参数是pluginManager的引用 
    function __construct(&$pluginManager){
        //注册这个插件 
        //第一个参数是钩子的名称 
        //第二个参数是pluginManager的引用 
        //第三个是插件所执行的方法 
        $pluginManager->register('paypal_excheckout', $this, '__config');
        $pluginManager->register('paypal_excheckout', $this, 'do_payment');
        $pluginManager->register('paypal_excheckout', $this, 'ReviewOrder');
        $pluginManager->register('paypal_excheckout', $this, 'APIError');
		$pluginManager->register('paypal_excheckout', $this, 'checkout');
		$pluginManager->register('paypal_excheckout', $this, 'DoExpressCheckoutPayment');
		$pluginManager->register('paypal_excheckout', $this, 'cancel');
    }
	
	function __config($data){
		return @in_array($data, array('do_payment', 'ReviewOrder', 'APIError', 'checkout', 'DoExpressCheckoutPayment', 'cancel'))?'enable':'';
	}
     
    function do_payment($data){
		global $c;
		include('SetExpressCheckout.php');
		exit;
    } 
     
    function ReviewOrder($data){
		global $c;
		include('ReviewOrder.php');
		exit;
    }
	
	function APIError($data){
		global $c;
		include('APIError.php');
		exit;
	}
	
	function checkout($data){
		global $c;
		include('checkout.php');
		exit;
	}
	
	function DoExpressCheckoutPayment($data){
		global $c;
		include('DoExpressCheckoutPayment.php');
		exit;
	}
	
	function cancel($data){
		global $c;
		include('cancel.php');
		exit;
	}
}
?>