<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

$config_row=db::get_all('config', "GroupId='global'");	//网站基本设置
foreach($config_row as $v){
	if(in_array($v['Variable'], array('OrdersSmsStatus', 'SearchTips', 'CopyRight', 'CloseWeb', 'Notice', 'ArrivalInfo', 'IndexContent', 'HeaderContent', 'TopMenu', 'ContactMenu', 'ShareMenu'))){
		$c['manage']['config'][$v['Variable']]=str::json_data(htmlspecialchars_decode($v['Value']), 'decode');
	}elseif($v['Variable']=='Language'){
		$c['manage']['config'][$v['Variable']]=explode(',', $v['Value']);
	}else{
		$c['manage']['config'][$v['Variable']]=$v['Value'];
	}
}
$c['manage']['lang_pack']=@include("static/lang/{$c['manage']['config']['ManageLanguage']}.php");	//后台语言包

if(!is_array($_SESSION['Manage']) || !$_SESSION['Manage']['UserName']){	//未登录
	if($_POST['do_action']){
		include('static/do_action/account.php');
		account_module::login();
		exit;
	}else{
		$c['manage']['module']='account';
		$c['manage']['action']='login';
	}
}else{
	$c['cache_timeout']=3600;//更新接口缓存文件间隔(s)
	$c['manage']=array_merge($c['manage'], array(
			'module'			=>	isset($_POST['m'])?$_POST['m']:$_GET['m'],
			'action'			=>	isset($_POST['a'])?$_POST['a']:$_GET['a'],
			'do'				=>	isset($_POST['d'])?$_POST['d']:$_GET['d'],
			'page'				=>	isset($_POST['p'])?$_POST['p']:$_GET['p'],
			'iframe'			=>	(int)$_GET['iframe'],
			'upload_dir'		=>	'/u_file/'.date('ym/'),   //网站所有上传的文件保存的基本目录
			'web_lang'			=>	'_'.$c['manage']['config']['LanguageDefault'],	//网站的默认语言版本
			'web_lang_list'		=>	array_keys($c['lang_name']),	//网站可用的语言列表
			'email_tpl_dir'		=>	'/static/email_tpl/',	//邮件模板存放目录
			'web_themes'		=>	db::get_value('config_module', 'IsDefault=1', 'Themes'),	//网站当前风格
			'currency_symbol'	=>	db::get_value('currency', 'IsUsed=1 and ManageDefault=1', 'Symbol'),	//后台默认的币种符号
			'manage_lang_list'	=>	array('zh-cn', 'en'),	//后台可用的语言列表
			'field_ext'			=>	array('VARCHAR(50)', 'VARCHAR(150)', 'VARCHAR(255)', 'TEXT'), //数据库添加字段参数
			'my_order'			=>	array('{/global.default/}', '999'=>'{/global.last/}'),
			'permit'			=>	include('static/inc/permit.php'),
			'permit_base'		=>	array('account'),	//基本权限
			'shipping_method'	=>	array('DHL', 'EMS', 'UPS', 'TNT', 'FedEx', 'DPEX', 1000=>'其他'),
			'blog_set'			=>	array('Title', 'Brief', 'nav'),
			'sync_ary'			=>	array(//数据同步状态
										'aliexpress'	=>	array('onSelling', 'offline', 'auditing', 'editingRequired'),
										'amazon'		=>	array(//设置链接	MarketplaceId
																'US'	=>	array('https://developer.amazonservices.com/', 'https://www.amazon.com/', 'ATVPDKIKX0DER'),
																'CA'	=>	array('https://developer.amazonservices.ca/', 'https://www.amazon.ca/', 'A2EUQ1WTGCTBG2'),
																'MX'	=>	array('https://developer.amazonservices.com.mx/', 'https://www.amazon.com.mx/', 'A1AM78C64UM0Y8'),
																
																'DE'	=>	array('https://developer.amazonservices.de/', 'https://www.amazon.de/', 'A1PA6795UKMFR9'),
																'ES'	=>	array('https://developer.amazonservices.es/', 'https://www.amazon.es/', 'A1RKKUPIHCS9HS'),
																'FR'	=>	array('https://developer.amazonservices.fr/', 'https://www.amazon.fr/', 'A13V1IB3VIYZZH'),
																'IT'	=>	array('https://developer.amazonservices.it/', 'https://www.amazon.it/', 'APJ6JRA9NG5V4'),
																'UK'	=>	array('https://developer.amazonservices.co.uk/', 'https://www.amazon.co.uk/', 'A1F83G8C2ARO7P'),
																
																'IN'	=>	array('https://developer.amazonservices.in/', 'https://www.amazon.in/', 'A21TJRUUN4KGV'),
																'JP'	=>	array('https://developer.amazonservices.jp/', 'https://www.amazon.co.jp/', 'A1VC38T7YXB528'),
																'CN'	=>	array('https://developer.amazonservices.com.cn/', 'https://www.amazon.cn/', 'AAHKV2X7AFYLW')
															)
									),
			'resize_ary'		=>	array(	//各系统的缩略图尺寸
										'products'	=>	array('default', '500x500', '240x240'),
									),
			'sub_save_dir'		=>	array(	//各系统的缩略图存放位置
										'products'	=>	'products/'
									),
			'photo_type'		=>	array(	//图片银行基本系统图片类型
										1	=>	'products',
										2	=>	'editor',
										0	=>	'other',
									),
			'user_reg_field'	=>	array(	//会员注册事项，请勿改动 1:可改 0:固定
										'Email'		=>	0,//邮箱
										'Name'		=>	1,//姓名
										'Gender'	=>	1,//性别
										'Age'		=>	1,//年龄
										'NickName'	=>	1,//昵称
										'Country'	=>	1,//国家
										'Telephone'	=>	1,//电话
										'Fax'		=>	1,//传真
										'Birthday'	=>	1,//生日
										'Facebook'	=>	1,//Facebook
										'Company'	=>	1,//公司
										'Code'		=>	1//验证码
									),
			'mod_order_status'	=>	array(	//订单状态可变更状态，如：Awaiting Payment=>(Awaiting Confirm Payment, Payment Wrong, Awaiting Shipping)
										1	=>	array(2,3,4,7),
										2	=>	array(3,4,7),
										3	=>	array(1,2,7),
										4	=>	array(5,7),
										5	=>	array(6,7),
										6	=>	array(7),
									),
			'email_notice'		=>	array('order_payment', 'order_shipped', 'order_change', 'order_cancel', 'create_account', 'forgot_password', 'order_create'), //邮件通知
			'table_lang_field'	=>	array(
										'article'						=>	array('Title'=>1, 'SeoTitle'=>1, 'SeoKeyword'=>1, 'SeoDescription'=>2),
										'article_category'				=>	array('Category'=>1),
										'article_content'				=>	array('Content'=>3),
										'info'							=>	array('Title'=>1, 'BriefDescription'=>2, 'SeoTitle'=>1, 'SeoKeyword'=>1, 'SeoDescription'=>2),
										'info_category'					=>	array('Category'=>1),
										'info_content'					=>	array('Content'=>3),
										'link'							=>	array('Keyword'=>1),
										'meta'							=>	array('SeoTitle'=>1, 'SeoKeyword'=>1, 'SeoDescription'=>2),
										'partners'						=>	array('Name'=>1),
										'payment'						=>	array('Name'=>0, 'Description'=>3),
										'products'						=>	array('Name'=>1, 'BriefDescription'=>2),
										'products_attribute'			=>	array('Name'=>0, 'Value'=>3),
										'products_attribute_value'		=>	array('Value'=>2),
										'products_category'				=>	array('Category'=>1, 'BriefDescription'=>2, 'SeoTitle'=>1, 'SeoKeyword'=>1, 'SeoDescription'=>2),
										'products_category_description'	=>	array('Description'=>3),
										'products_description'			=>	array('Description'=>3),
										'products_seo'					=>	array('SeoTitle'=>1, 'SeoKeyword'=>1, 'SeoDescription'=>2),
										'products_tags'					=>	array('Name'=>1),
										'user_level'					=>	array('Name'=>0),
										'user_reg_set'					=>	array('Name'=>0, 'Option'=>3)
									)
		)
	);
	for($i=1; $i<101; ++$i){//排序数量增加到100
		$c['manage']['my_order'][$i]=$i;
	}
	$c['manage']['web_themes']='t057';
	//后台汇率
	$_SESSION['Manage']['Currency']=db::get_one('currency', "IsUsed=1 and ManageDefault=1");
	//自动取消订单
	if((int)$c['manage']['config']['AutoCanceled']){
		$time=86400*$c['manage']['config']['AutoCanceledDay'];//一天*日数
		db::update('orders', "({$c['time']}-OrderTime)>{$time} and OrderStatus<4", array('OrderStatus'=>7));
	}
	
	//干活....
	$do_action=isset($_POST['do_action'])?$_POST['do_action']:$_GET['do_action'];
	$_GET['do_action'] == 'action.file_upload_plugin' && $do_action=$_GET['do_action'];// 通过文件上传进来的直接用get
	if($do_action){
		$_=explode('.', $do_action);
		$do_action_file="static/do_action/{$_[0]}.php";
		if(@is_file($do_action_file)){
			include($do_action_file);
			if(method_exists($_[0].'_module', $_[1])){
				eval("{$_[0]}_module::{$_[1]}();");
				exit;
			}
		}
	}
	
	!$c['manage']['module'] && $c['manage']['module']='account';
	!@is_dir($c['manage']['module']) && js::location('./');
	$c['manage']['module']=='account' && !@is_file("{$c['manage']['module']}/{$c['manage']['action']}.php") && $c['manage']['action']='index';
	!@is_file("{$c['manage']['module']}/{$c['manage']['action']}.php") && $c['manage']['action']='index';
	!$c['manage']['do'] && $c['manage']['do']='index';
	!$c['manage']['page'] && $c['manage']['page']='index';
	($c['manage']['module']!='set' && $c['manage']['action']!='photo') && manage::check_permit($c['manage']['module'], 1);	//权限检测
}
?>