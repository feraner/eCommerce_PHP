<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

$c=array(
	'root_path'		=>	substr(dirname(__FILE__), 0, -4).DIRECTORY_SEPARATOR,
	'time'			=>	time(),
	'tmp_dir'		=>	'/tmp/',
	'api_url'		=>	'https://api.ly200.com/gateway/',
	'sync_url'		=>	'https://sync.ly200.com/gateway/',
	'analytics'		=>	'//analytics.ly200.com/js/analytics.js',
	'cdn'			=>	'//ueeshop.ly200-cdn.com/',
	'my_order'		=>	'if(MyOrder>0, if(MyOrder=999, 1000001, MyOrder), 1000000) asc,',
	'gender'		=>	array('Unknown', 'Ms', 'Mr'),
	'chat'			=>	array(
							'type'		=>	array('QQ', 'Skype', 'Email', 'trademanager', 'WeChat', 'WhatsApp'),
							'link'		=>	array('//wpa.qq.com/msgrd?v=3&uin=%s&site=qq&menu=yes', 'skype:%s?chat', 'mailto:%s', '//amos.alicdn.com/msg.aw?v=2&uid=%s&site=enaliint&s=24&charset=utf-8', '', 'https://api.whatsapp.com/send?phone=%s')
						),
	'share'			=>	array('Facebook', 'Twitter', 'Pinterest', 'YouTube', 'Google', 'VK', 'LinkedIn', 'Instagram'), //第三方分享项目
	'follow'		=>	array('Facebook', 'Instagram', 'Twitter', 'Pinterest', 'LinkedIn', 'YouTube', 'Google', 'VK'), //第三方关注项目
	'mobile'		=>	array(
							'tpl_dir'	=>	'/static/themes/default/mobile/'	//手机模板目录
						),
	'orders'		=>	array(
							'mode'		=>	1,	//购物模式[非会员购物]，【0,必须登录才可下单; 1,非会员下单】
							'path'		=>	'/tmp/orders/',
							'review'	=>	86400*30,	//订单产品评论时间上限(天) 目前是30天
							'status'	=>	array(
												1	=>	'Awaiting Payment',
												2	=>	'Awaiting Confirm Payment',
												3	=>	'Payment Wrong',
												4	=>	'Awaiting Shipping',
												5	=>	'Shipment Shipped',
												6	=>	'Received',
												7	=>	'Cancelled'
											)
						),
	'lang_name'		=>	array(//语言版本
							'en'	=>	'English',
							'jp'	=>	'日本語',
							'de'	=>	'Deutsch',
							'fr'	=>	'Français',
							'es'	=>	'Español',
							'ru'	=>	'Русский',
							'pt'	=>	'Português',
							'zh_tw'	=>	'繁體中文'
						),
	'continent'		=>	array(//洲
							1	=>	'亚洲',
							2	=>	'欧洲',
							3	=>	'非洲',
							4	=>	'北美洲',
							5	=>	'南美洲',
							6	=>	'大洋洲',
							7	=>	'南极洲'
						),
	'sys_email_tpl'	=>	array('create_account', 'forgot_password', 'validate_mail', 'order_create', 'order_payment', 'order_shipped', 'order_change', 'order_cancel',),//系统邮件模板
	'sys_email_tpl_title'	=>	array(//系统邮件模板默认标题
								'create_account'	=>	'Welcome to {Domain}.',
								'forgot_password'	=>	'{Domain} Password Recovery.',
								'validate_mail'		=>	'Dear {Email}, Please verify your email address.',
								'order_create'		=>	'Place an Order: {OrderNum}.',
								'order_payment'		=>	'We have received from your payment for order#{OrderNum}.',
								'order_shipped'		=>	'Your order#{OrderNum} has shipped.',
								'order_change'		=>	'Your order#{OrderNum} has changed to {OrderStatus}.',
								'order_cancel'		=>	'Cancel an Order: {OrderNum}',
							),
);
@include('config.php');
@include('nav_config.php');
ly200_web_init::init();
//$c['session_id']=(int)$_SESSION['User']['UserId']?'':substr(md5(md5(session_id())), 0, 10);
$c['session_id']=ly200::set_session_id(); //非会员ID
//$c['plugin']=new plugin();//插件类

//系统设置类
class ly200_web_init{
	public static function init(){
		header('Content-Type: text/html; charset=utf-8');
		@error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_STRICT);
		self::slashes_gpcf($_GET);
		self::slashes_gpcf($_POST);
		self::slashes_gpcf($_COOKIE);
		self::slashes_gpcf($_FILES);
		self::slashes_gpcf($_REQUEST);
		phpversion()<'5.3.0' && set_magic_quotes_runtime(0);
		date_default_timezone_set('PRC');	//5.1.0
		spl_autoload_register('self::class_auto_load');	//5.1.2
		$host_ary=explode('.', $_SERVER['HTTP_HOST']);
		@ini_set('session.cookie_domain', in_array(reset($host_ary), ly200::subdomain_list())?implode('.', array_slice($host_ary, 1)):$_SERVER['HTTP_HOST']);
		$_GET['session_id'] && @session_id($_GET['session_id']);
		@session_start();
	}
	
	private static function class_auto_load($class_name){
		global $c;
		$file=$c['root_path'].'inc/class/'.$class_name.'.class.php';
		@is_file($file) && include($file);
	}
	
	private static function slashes_gpcf(&$ary){
		foreach($ary as $k=>$v){
			if(is_array($v)){
				self::slashes_gpcf($ary[$k]);
			}else{
				$ary[$k]=trim($ary[$k]);
				!get_magic_quotes_gpc() && $ary[$k]=addslashes($ary[$k]);
			}
		}
	}
}
?>