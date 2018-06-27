<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class user_module{
	/************************** 功能模块 Start **************************/
	public static function logout(){
		global $c;
		$_SESSION['User']='';
		unset($_SESSION['User'], $_SESSION['Ueeshop']['LoginReturnUrl'], $_SESSION['Cart']['ShippingAddress']);
		str::SetTheCookie('User');
		js::location('/');
	}
	
	public static function login(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if(empty($p_Email) || empty($p_Password) || !preg_match('/^\w+[a-zA-Z0-9-.+_]+@[a-zA-Z0-9-.+_]+\.\w*$/i', $p_Email)){
			ly200::e_json(array($c['lang_pack']['user']['error']['Email']), 0);
		}
		$p_Password=ly200::password($p_Password);
		$p_IsStay=(int)$p_IsStay;
		$time=$c['time'];
		$ip=ly200::get_ip();
		if($user_row=str::str_code(db::get_one('user', "Email='$p_Email' and Password='$p_Password'"))){
			if(($c['FunVersion']>=1 && $c['config']['global']['UserStatus'] && $user_row['Status']==1) || !$c['config']['global']['UserStatus']){//会员审核
				$_SESSION['User']=$user_row;
				$UserId=$user_row['UserId'];
				
				if($p_IsStay){//保持登录
					$_time=$c['time']+3600*24*7;//7天
					str::SetTheCookie('User', str::str_crypt(trim($UserId)."\t".str::PwdCode($p_Password)), $_time);//保存登录信息，下次自动登录
				}
				
				db::update('user', "UserId='{$UserId}'", array('LastLoginTime'=>$c['time'], 'LastLoginIp'=>$ip, 'LoginTimes'=>$user_row['LoginTimes']+1));
				cart::login_update_cart();
				user::operation_log($UserId, '会员登录', 1);
				$p_jumpUrl=$p_jumpUrl?stripslashes($p_jumpUrl):$_SESSION['Ueeshop']['LoginReturnUrl'];
				unset($_SESSION['Cart']['ShippingAddress']); //清空非会员收货地址信息
				ly200::e_json(array($p_jumpUrl ? urldecode($p_jumpUrl) : '/account/'), 1);
			}else{
				ly200::e_json(array($c['lang_pack']['user']['error']['LoginStatus']), 0);
			}
		}else{
			ly200::e_json(array($c['lang_pack']['user']['error']['Password']), 0);
		}
	}
	
	public static function register(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if(!isset($_SERVER["HTTP_X_REQUESTED_WITH"]) || strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])!="xmlhttprequest"){ //非 ajax 请求的处理方式
			ly200::e_json(array($c['lang_pack']['user']['error']['Error']), 0);
		};
		$reg_ary=str::json_data(db::get_value('config', "GroupId='user' and Variable='RegSet'", 'Value'), 'decode');
		if((int)$reg_ary['Code'][0]==1 && (!$p_Code || $_SESSION['Ueeshop']['VCode'][md5('register')]!=strtoupper($p_Code))){
			ly200::e_json(array($c['lang_pack']['user']['error']['Code']), 0);
		}
		unset($_SESSION['Ueeshop']['VCode'][md5('register')]);
		if(empty($p_Email) || !preg_match('/^\w+[a-zA-Z0-9-.+_]+@[a-zA-Z0-9-.+_]+\.\w*$/i', $p_Email) || strlen($p_Email)>100 || empty($p_Password)){
			ly200::e_json(array($c['lang_pack']['user']['error']['EmailEntered']), 0);
		}
		if($reg_ary['Name'][0] && $reg_ary['Name'][1] && (empty($p_FirstName) || empty($p_LastName))){
			ly200::e_json(array($c['lang_pack']['user']['error']['Name']), 0);
		}
		$p_Password=ly200::password($p_Password);
		$p_Other=str::json_data((array)$p_Other);
		if(!db::get_row_count('user', "Email='$p_Email'")){
			$time=$c['time'];
			$ip=ly200::get_ip();
			$data=array(
				'Language'		=>	'en',
				'Gender'		=>	(int)$p_Gender,
				'FirstName'		=>	$p_FirstName,
				'LastName'		=>	$p_LastName,
				'Email'			=>	$p_Email,
				'Password'		=>	$p_Password,
				'Age'			=>	(int)$p_Age,
				'NickName'		=>	$p_NickName,
				'Telephone'		=>	$p_Telephone,
				'Fax'			=>	$p_Fax,
				'Birthday'		=>	$p_Birthday,
				'Facebook'		=>	$p_Facebook,
				'Company'		=>	$p_Company,
				'Other'			=>	$p_Other,
				'RegTime'		=>	$time,
				'RegIp'			=>	$ip,
				'LastLoginTime'	=>	$time,
				'LastLoginIp'	=>	$ip,
				'LoginTimes'	=>	1,
				'Status'		=>	0
			);
			db::insert('user', $data);
			$UserId=db::get_insert_id();
			if($ParentId=db::get_value('sales_coupon',"CouponWay=2 and ({$c['time']} < EndTime and {$c['time']} > StartTime)",'CId')){
				user::get_user_coupons($UserId,$ParentId); //会员注册送优惠券
			}
			if($p_Address || (int)$p_country_id || $p_Phone){
				$data_oth=array(
					'UserId'		=>	$UserId,
					'FirstName'		=>	$p_FirstName,
					'LastName'		=>	$p_LastName,
					'AddressLine1'	=>	$p_Address,
					'City'			=>	$p_City,
					'State'			=>	$p_State?$p_State:'',
					'SId'			=>	(int)$p_Province,
					'CId'			=>	(int)$p_country_id,
					'CodeOption'	=>	(int)$p_tax_code_type,
					'TaxCode'		=>	$p_tax_code_value?$p_tax_code_value:'',
					'ZipCode'		=>	$p_ZipCode,
					'CountryCode'	=>	(int)$p_country_id?db::get_value('country', "CId='$p_country_id'", 'Code'):'',//$p_CountryCode,
					'PhoneNumber'	=>	$p_Phone,
					'AccTime'		=>	$time
				);
				db::insert('user_address_book', $data_oth);//Shipping Address
				$data_oth['IsBillingAddress']=1;
				db::insert('user_address_book', $data_oth);//Billing Address
			}
			if($c['FunVersion']>=1 && $c['config']['global']['UserStatus']){//开启会员审核
				$_SESSION['User']='';
				unset($_SESSION['User']);
				$tips=array($c['lang_pack']['user']['error']['UserStatus']);
				$status=0;
				
				if((int)$c['config']['global']['UserVerification']){
					$tips=array('/account/sign-up.html?userType=1&UserId='.$UserId);
					$status=1;
					include($c['static_path'].'/inc/mail/validate_mail.php');
					ly200::sendmail($data['Email'], $mail_title, $mail_contents);
				}
			}else{
				$_SESSION['User']=$data;
				$_SESSION['User']['UserId']=$UserId;
				$tips=array($p_jumpUrl ? urldecode(stripslashes($p_jumpUrl)) : '/account/');
				$status=1;
				
				cart::login_update_cart();
				//更新会员等级
				$LId=(int)db::get_value('user_level', 'IsUsed=1 and FullPrice<=0', 'LId');
				if($LId){
					db::update('user', "UserId='$UserId'", array('Level'=>$LId));
					$_SESSION['User']['Level']=$LId;
				}
				
				unset($_SESSION['Cart']['ShippingAddress']); //清空非会员收货地址信息
				user::operation_log($UserId, '会员注册', 1);
				if((int)$c['config']['email']['notice']['create_account']){ //邮件通知开关【会员注册】
					include($c['static_path'].'/inc/mail/create_account.php');
					ly200::sendmail($data['Email'], $mail_title, $mail_contents);
				}
			}
			ly200::e_json($tips, $status);
		}else{
			ly200::e_json(array($c['lang_pack']['user']['error']['Exists']), 0);
		}
	}
	
	public static function check_user(){//检查登录状态
		$data=user::check_login('', 1);
		ly200::e_json($data, 1);
	}
	
	public static function mod_profile(){
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$user=user::check_login();
		(empty($p_FirstName) || empty($p_LastName)) && js::location('/account/setting/');
		$p_Other=str::json_data((array)$p_Other);
		$data=array(
			'Language'		=>	'en',
			'Gender'		=>	(int)$p_Gender,
			'FirstName'		=>	$p_FirstName,
			'LastName'		=>	$p_LastName,
			'Age'			=>	(int)$p_Age,
			'NickName'		=>	$p_NickName,
			'Telephone'		=>	$p_Telephone,
			'Fax'			=>	$p_Fax,
			'Birthday'		=>	$p_Birthday,
			'Facebook'		=>	$p_Facebook,
			'Company'		=>	$p_Company,
			'Other'			=>	$p_Other
		);
		db::update('user', "UserId='{$user['UserId']}'", $data);
		foreach($data as $k=>$v) $_SESSION['User'][$k]=$v;
		$p_ajax_submit==1 && ly200::e_json('', 1);
		js::location('/account/setting/');
	}
	
	public static function mod_email(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$user=user::check_login();
		(empty($p_NewEmail) || empty($p_ExtPassword) || !preg_match('/^\w+[a-zA-Z0-9-.+_]+@[a-zA-Z0-9-.+_]+\.\w*$/i', $p_NewEmail)) && js::location('/account/setting/');
		if(db::get_row_count('user', "Email='$p_NewEmail'")){
			js::location('/account/setting/', $c['lang_pack']['user']['error']['EmailBeen']);
		}else{
			$p_ExtPassword=ly200::password($p_ExtPassword);
			if(db::get_row_count('user', "UserId='{$user['UserId']}' and Password='$p_ExtPassword'")){
				db::update('user', "UserId='{$user['UserId']}'", array('Email'=>$p_NewEmail));		
				$_SESSION['User']['Email']=$p_NewEmail;
				js::location('/account/setting/', $c['lang_pack']['user']['error']['EmailSuccess']);
			}else{
				js::location('/account/setting/', $c['lang_pack']['user']['error']['PWDWrong']);
			}
		}
	}
	
	public static function mod_password(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$user=user::check_login();
		(empty($p_ExtPassword) || empty($p_NewPassword) || empty($p_NewPassword2)) && js::location('/account/setting/');
		if($p_NewPassword!=$p_NewPassword2){
			$p_ajax_submit==1 && ly200::e_json($c['lang_pack']['user']['error']['PWDBeen'], 0);
			js::location('/account/setting/', $c['lang_pack']['user']['error']['PWDBeen']);
		}else{
			$p_ExtPassword=ly200::password($p_ExtPassword);
			if(db::get_row_count('user', "UserId='{$user['UserId']}' and Password='$p_ExtPassword'")){
				$p_NewPassword=ly200::password($p_NewPassword);
				db::update('user', "UserId='{$user['UserId']}'", array('Password'=>$p_NewPassword));
				$p_ajax_submit==1 && ly200::e_json($c['lang_pack']['user']['error']['PWDSuccess'], 1);
				js::location('/account/setting/', $c['lang_pack']['user']['error']['PWDSuccess']);
			}else{
				$p_ajax_submit==1 && ly200::e_json($c['lang_pack']['user']['error']['PWDWrong'], 0);
				js::location('/account/setting/', $c['lang_pack']['user']['error']['PWDWrong']);
			}
		}
	}
	
	public static function forgot(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$Email=$p_Email;
		$user_row=db::get_one('user', "Email='$Email'", 'UserId,Email,FirstName,LastName');
		if($user_row){
			$EmailEncode=base64_encode($user_row['Email']);
			$Expiry=base64_encode(str::rand_code(15));
			if(!db::get_row_count('user_forgot', "UserId='{$user_row['UserId']}' and IsReset=0")){
				db::insert('user_forgot', array(
						'UserId'		=>	$user_row['UserId'],
						'EmailEncode'	=>	$EmailEncode,
						'Expiry'		=>	$Expiry,
						'ResetTime'		=>	$c['time'],
						'IsReset'		=>	0
					)
				);
			}else{
				db::update('user_forgot', "UserId='{$user_row['UserId']}' and IsReset=0", array(
						'EmailEncode'	=>	$EmailEncode,
						'Expiry'		=>	$Expiry,
						'ResetTime'		=>	$c['time']
					)
				);
			}
			if((int)$c['config']['email']['notice']['forgot_password']){ //邮件通知开关【忘记密码】
				include($c['static_path'].'/inc/mail/forgot_password.php');
				ly200::sendmail($Email, $mail_title, $mail_contents);
			}
			ly200::e_json(array('/account/forgot.html?forgot_success=1'), 1);
		}else{
			ly200::e_json(array($c['lang_pack']['user']['error']['Forgot']), 0);
		}
	}
	
	public static function reset_password(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$Password=ly200::password($p_Password);
		$Password2=ly200::password($p_Password2);
		$email=$p_email;
		$expiry=$p_expiry;
		$user_row=db::get_one('user_forgot', "EmailEncode='$email' and Expiry='$expiry' and IsReset=0");
		if($Password==$Password2 && $user_row){
			db::update('user', "UserId='{$user_row['UserId']}'", array(
					'Password'	=>	$Password
				)
			);
			db::update('user_forgot', "FId='{$user_row['FId']}'", array(
					'IsReset'	=>	1
				)
			);
			ly200::e_json(array('/account/forgot.html?reset_success=1'), 1);
		}else{
			ly200::e_json(array($c['lang_pack']['user']['error']['Forgot']), 0);
		}
	}
	
	public static function cancel_newsletter(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$newsletter_row=db::get_one('newsletter', "Email='{$p_Email}'");
		if($newsletter_row){
			db::delete('newsletter', "Email='{$p_Email}'");
		}
		ly200::e_json('', 1);
	}
	
	/************************** 订单模块 Start **************************/
	public static function cancel_order(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$user=user::check_login(urlencode('/account/orders/'));
		$OId=$p_OId;
		$orders_row=str::str_code(db::get_one('orders', "OId='$OId'"));
		if($orders_row['OrderStatus']<4){
			db::update('orders', "OId={$OId}", array(
					'OrderStatus'	=>	7,
					'CancelReason'	=>	$p_CancelReason,
					'UpdateTime'	=>	$c['time']
				)
			);
			$log="Cancel order #{$orders_row['OId']}";
			orders::orders_log((int)$_SESSION['User']['UserId'], $_SESSION['User']['UserId']?($_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']):'Customer', $orders_row['OrderId'], 7, $log);
			orders::orders_products_update(7, $orders_row, 1);
			
			$notice_config=str::json_data(db::get_value('config', 'GroupId="email" and Variable="notice"', 'Value'), 'decode');
			if((int)$notice_config['order_cancel']){//取消订单
				$ToAry=array($orders_row['Email']);
				$c['config']['global']['AdminEmail'] && $ToAry[]=$c['config']['global']['AdminEmail'];
				include($c['static_path'].'/inc/mail/order_cancel.php');
				ly200::sendmail($ToAry, $mail_title, $mail_contents);
			}
			js::location('/account/orders/cancel'.$OId.'.html');
		}else{
			js::location('/account/orders/');
		}
	}
	
	public static function confirm_receiving(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$user=user::check_login(urlencode('/account/orders/'));
		$OId=$p_OId;
		$orders_row=str::str_code(db::get_one('orders', "OId='$OId'"));
		if($orders_row['OrderStatus']==5){
			db::update('orders', "OId='{$OId}'", array('OrderStatus'=>6, 'UpdateTime'=>$c['time']));
			$log="Received order #{$orders_row['OId']}";
			//orders::orders_log((int)$_SESSION['User']['UserId'], $_SESSION['User']['UserId']?($_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']):'Customer', $orders_row['OrderId'], 6, $log);
			orders::orders_log((int)$_SESSION['User']['UserId'], $_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName'], $orders_row['OrderId'], 6, $log);
			$orders_row['OrderStatus']=6;
			$ToAry=array($orders_row['Email']);
			$c['config']['global']['AdminEmail'] && $ToAry[]=$c['config']['global']['AdminEmail'];
			include($c['static_path'].'/inc/mail/order_change.php');
			ly200::sendmail($ToAry, $mail_title, $mail_contents);
			js::location('/account/orders/view'.$OId.'.html');
		}else{
			js::location('/account/orders/');
		}
	}
	/************************** 订单模块 End **************************/
	
	/************************** 收藏模块 Start **************************/
	public static function add_favorite(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$user=user::check_login(urlencode('/account/favorite/add'.sprintf('%04d', $g_ProId).'.html'), 1);
		!$user && ly200::e_json('', -1);
		$g_ProId=(int)$g_ProId;
		if(!db::get_row_count('user_favorite', "UserId='{$user['UserId']}' and ProId='{$g_ProId}'")){
			$data=array(
				'UserId'	=>	$user['UserId'],
				'ProId'		=>	$g_ProId,
				'AccTime'	=>	$c['time']
			);
			db::insert('user_favorite', $data);
			db::query("update products set FavoriteCount=FavoriteCount+1 where ProId='$g_ProId'");
			$products_row=db::get_one('products', "ProId='$g_ProId'");
			$result=array(
				'Name'		=>	$products_row['Name'.$c['lang']],
				'Num'		=>	$products_row['SKU']?$products_row['SKU']:$products_row['Prefix'].$products_row['Number'],
				'Currency'	=>	$_SESSION['Currency']['Currency']
			);
			ly200::e_json($result, 1);
		}else{
			ly200::e_json('', 0);
		}
	}
	
	public static function del_favorite(){
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$user=user::check_login();
		$g_ProId=(int)$g_ProId;
		$isjson = (int)$g_isjson;
		db::delete('user_favorite', "ProId='{$g_ProId}' and UserId='{$user['UserId']}'");
		db::query("update products set FavoriteCount=FavoriteCount-1 where ProId='$g_ProId'");
		if($isjson){
			ly200::e_json('', 1);
		}else{
			js::location('/account/favorite/');
		}
	}
	/************************** 收藏模块 End **************************/

	/************************** 地址模块 Start **************************/
	public static function select_country(){
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_CId=(int)$p_CId;
		
		$country_row=str::str_code(db::get_one('country', "CId='{$p_CId}'"));
		if($country_row['HasState']==1){
			$state_row=str::str_code(db::get_all('country_states', "CId='{$p_CId}'", '*', 'States asc'));
			if(count($state_row)){
				$data=$state_row;
			}else $data=-1;
		}else{
			$data=-1;
		}
		ly200::e_json(array('cid'=>$p_CId, 'code'=>$country_row['Code'], 'contents'=>$data), 1);
	}
		
	public static function get_addressbook(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_AId=(int)$p_AId;
		$p_NotUser=(int)$p_NotUser;
		if($p_NotUser && $_SESSION['Cart']['ShippingAddress']){ //非会员收货地址信息
			$address_row=$_SESSION['Cart']['ShippingAddress'];
			$country_row=str::str_code(db::get_one('country', "CId='{$address_row['CId']}'", 'Country, HasState, CountryData'));
			$CountryName = @json_decode(htmlspecialchars_decode($country_row['CountryData']), true);
			$country_row['Country']=$CountryName[substr($c['lang'], 1)]?$CountryName[substr($c['lang'], 1)]:$country_row['Country'];
			(int)$address_row['SId'] && $address_row['StateName']=str::str_code(db::get_value('country_states', "CId='{$address_row['CId']}' and SId='{$address_row['SId']}'", 'States'));
			$address_row['CountryCode']=str_replace('+', '', $address_row['CountryCode']); //去掉+
			ly200::e_json(array('address'=>$address_row, 'country'=>$country_row), 1);
		}else{
			if(db::get_row_count('user_address_book', "UserId='{$_SESSION['User']['UserId']}' and AId='$p_AId'")){// and IsBillingAddress=0
				$address_row=str::str_code(db::get_one('user_address_book', "UserId='{$_SESSION['User']['UserId']}' and AId='$p_AId'"));
				$country_row=str::str_code(db::get_one('country', "CId='{$address_row['CId']}'", 'Country,HasState,CountryData'));
				$CountryName = @json_decode(htmlspecialchars_decode($country_row['CountryData']), true);
				$country_row['Country']=$CountryName[substr($c['lang'], 1)]?$CountryName[substr($c['lang'], 1)]:$country_row['Country'];
				(int)$address_row['SId'] && $address_row['StateName']=str::str_code(db::get_value('country_states', "CId='{$address_row['CId']}' and SId='{$address_row['SId']}'", 'States'));
				
				ly200::e_json(array('address'=>$address_row, 'country'=>$country_row), 1);
			}else{
				$country_row=str::str_code(db::get_one('country', "IsDefault=1"));
				!$country_row['CId'] && $country_row=str::str_code(db::get_one('country', "CId=226"));
				$CountryName = @json_decode(htmlspecialchars_decode($country_row['CountryData']), true);
				$country_row['Country']=$CountryName[substr($c['lang'], 1)]?$CountryName[substr($c['lang'], 1)]:$country_row['Country'];
				ly200::e_json(array('country'=>$country_row), 2);
			}
		}
		ly200::e_json(array('error'=>$c['lang_pack']['user']['error']['Error']), -1);
	}
	
	public static function set_default_address(){
		global $c;
		$data=user::check_login();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_AId=(int)$p_AId;
		if(db::get_row_count('user_address_book', "{$data['fetch_where']} and AId='$p_AId'")){
			db::update('user_address_book', "{$data['fetch_where']} and AId='$p_AId'", array('AccTime'=>$c['time']));
			
			ly200::e_json('', 1);
		}else{
			ly200::e_json('', -1);
		}
	}
	
	public static function addressbook_selected(){
		$data=user::check_login();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_AId=(int)$p_AId;
		if(db::get_row_count('user_address_book', "{$data['fetch_where']} and AId='$p_AId'")){
			$_SESSION['Cart']['ShippingAddressAId']=$p_AId;
			ly200::e_json('', 1);
		}else{
			ly200::e_json('', -1);
		}
	}

	public static function addressbook_mod(){
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		!$p_typeAddr && $data=user::check_login();
		$p_AId=(int)$p_edit_address_id;
		$data_ary=array(
			'FirstName'		=>	$p_FirstName,
			'LastName'		=>	$p_LastName,
			'AddressLine1'	=>	$p_AddressLine1,
			'AddressLine2'	=>	$p_AddressLine2,
			'City'			=>	$p_City,
			'State'			=>	$p_State?$p_State:'',
			'SId'			=>	(int)$p_Province,
			'CId'			=>	(int)$p_country_id,
			'CodeOption'	=>	(int)$p_tax_code_type,
			'TaxCode'		=>	$p_tax_code_value?$p_tax_code_value:'',
			'ZipCode'		=>	$p_ZipCode,
			'CountryCode'	=>	$p_CountryCode,
			'PhoneNumber'	=>	$p_PhoneNumber,
			'AccTime'		=>	$c['time']
		);
		if(!$p_typeAddr){ //会员填写
			if($p_AId){
				db::update('user_address_book', "{$data['fetch_where']} and AId='$p_AId'", $data_ary);
				//账单地址不完整，则覆盖
				$bill_row=db::get_one('user_address_book', "{$data['fetch_where']} and IsBillingAddress=1");
				
				if($p_AId!=$bill_row['AId'] && (!$bill_row['FirstName'] || !$bill_row['LastName'] || !$bill_row['AddressLine1'] || !$bill_row['City'] || (!$bill_row['State'] && !$bill_row['SId']) || !$bill_row['CId'] || !$bill_row['ZipCode'] || !$bill_row['PhoneNumber'])){
					db::update('user_address_book', "{$data['fetch_where']} and IsBillingAddress=1", $data_ary);
				}
			}else{
				$data_ary['UserId']=$data['UserId'];
				db::insert('user_address_book', $data_ary);
				$AId=db::get_insert_id();
				if(!db::get_row_count('user_address_book', "{$data['fetch_where']} and IsBillingAddress=1")){
					$data_ary['IsBillingAddress']=1;
					db::insert('user_address_book', $data_ary);
				}
				$_SESSION['Cart']['ShippingAddressAId']=$AId;
			}
			user::operation_log($data['UserId'], '会员收货地址'.($p_AId?'更改':'添加'));
		}else{ //非会员填写
			$_SESSION['Cart']['ShippingAddress']=$data_ary;
			$_SESSION['Cart']['ShippingAddress']['Email']=$p_Email;
			$_SESSION['Cart']['ShippingAddress']['Province']=$data_ary['SId'];
			$_SESSION['Cart']['ShippingAddress']['country_id']=$data_ary['CId'];
			$_SESSION['Cart']['ShippingAddress']['tax_code_type']=$data_ary['CodeOption'];
			$_SESSION['Cart']['ShippingAddress']['tax_code_value']=$data_ary['TaxCode'];
			$_SESSION['Cart']['ShippingAddress']['typeAddr']=1;
		}
		ly200::e_json('', 1);
	}
	
	public static function addressbook_del(){
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$user=user::check_login();
		$g_AId=(int)$g_AId;
		db::delete('user_address_book', "AId={$g_AId} and UserId='{$user['UserId']}'");
		js::location('/account/address/');
	}
	/************************** 地址模块 End **************************/
	
	/************************** 评论模块 Start **************************/
	//会员评论提交
	public static function submit_review(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$filter_ary=array('http:', 'https:', '//');//过滤数组
		$p_ProId=(int)$p_ProId;
		$p_RId=(int)$p_RId;
		$p_OrderId=(int)$p_OrderId;
		$PicPath=array();
		$time=$c['time'];
		$CustomerName=$p_Name;
		$review_cfg=str::json_data(db::get_value('config', "GroupId='products_show' and Variable='review'", 'Value'), 'decode');
		$user=user::check_login('', 1);
		if(!$p_RId){
			if($review_cfg['range']==1){
				!$user && exit(str::json_data(array('ok'=>0)));
			}elseif(!$user && $review_cfg['range']==2){
				$user['UserId']=0;
			}
		}
		if($user['FirstName'] || $user['LastName']) $CustomerName=$user['FirstName'].' '.$user['LastName'];
		if($p_RId){
			$ReAudit=0;
			(int)$review_cfg['display']==2 && $ReAudit=1;//无需审核的情况下，默认为审核通过
			$data=array(
				'ProId'			=>	$p_ProId,
				'UserId'		=>	$user['UserId'],
				'ReId'			=>	$p_RId,
				'CustomerName'	=>	$CustomerName,
				'Content'		=>	$p_ReviewComment,
				'Ip'			=>	ly200::get_ip(),
				'AccTime'		=>	$time,
				'Audit'			=>	$ReAudit,
			);
			db::insert('products_review', $data);
			$result_ary=array(
				'ok'	=>	1,
				'info'	=>	$p_ReviewComment,
				'name'	=>	$CustomerName,
				'time'	=>	date('M d,Y H:i:s', $time)
			);
			ly200::e_json($result_ary, 1);
		}else{
			if($review_cfg['code']==1 && $_SESSION['Ueeshop']['VCode'][md5('review')]!=strtoupper($p_Code)){//验证码
				js::location($p_BackUrl, 'Verification code error.', '.top');
			}
			if(count($_SESSION['Ueeshop']['ReviewImg'])){//评论图片
				$PicPath=$_SESSION['Ueeshop']['ReviewImg'];
			}
			unset($_SESSION['Ueeshop']['ReviewImg']);
			(!$user['UserId'] && $review_cfg['range']==1) && js::location($p_BackUrl, '', '.top');
			$p_Rating=(int)$p_Rating;
			!$p_Rating && js::location($p_BackUrl, '', '.top');
			$Audit=0;
			(int)$review_cfg['display']==2 && $Audit=1;//无需审核的情况下，默认为审核通过
			$data=array(
				'ProId'			=>	$p_ProId,
				'UserId'		=>	$user['UserId'],
				'OrderId'		=>	$p_OrderId,
				'CustomerName'	=>	$CustomerName,
				'Content'		=>	str_replace($filter_ary, '', $p_Content),
				'PicPath_0'		=>	$PicPath[0],
				'PicPath_1'		=>	$PicPath[1],
				'PicPath_2'		=>	$PicPath[2],
				'Rating'		=>	$p_Rating,
				'Audit'			=>	$Audit,
				'Ip'			=>	ly200::get_ip(),
				'AccTime'		=>	$time
			);
			db::insert('products_review', $data);
			$RId=db::get_insert_id();
			if($Audit){
				$count=(int)db::get_row_count('products_review', "ProId='{$p_ProId}' and ReId=0");
				$rating=(float)db::get_sum('products_review', "ProId='{$p_ProId}' and ReId=0", 'Rating');
				db::update('products', "ProId='{$p_ProId}'", array('Rating'=>($count?ceil($rating/$count):0), 'TotalRating'=>$count));
				db::update('products_review', "ReId='{$RId}'", array('Audit'=>0));//回复审核清0
			}
			js::location($p_BackUrl, '', '.top');
		}
	}
	
	//会员评论图片提交
	public static function review_img(){
		global $c;
		$_SESSION['Ueeshop']['ReviewImg']=array();
		$ImgPath=array();
		$resize_ary=array('85x85');
		$save_dir='/u_file/'.date('ym/').'review/'.date('d/');
		for($i=0; $i<3; ++$i){
			if($_FILES['PicPath_'.$i]['name']){
				$picpath=file::file_upload($_FILES['PicPath_'.$i], $save_dir);
				$ext_name=file::get_ext_name($picpath);
				foreach($resize_ary as $v2){
					$size_w_h=explode('x', $v2);
					$resize_path=img::resize($picpath, $size_w_h[0], $size_w_h[1]);
				}
				$_SESSION['Ueeshop']['ReviewImg'][]=$picpath;
			}
		}
		echo '<script type="text/javascript" src="/static/js/jquery-1.7.2.min.js"></script>';
		echo "<script>";
		echo "$(window.parent.document).find('#reviews_img').parent().hide();";
		echo "$(window.parent.document).find('#review_form').submit();";
		echo "</script>";
		exit();
	}
	
	//会员评论点评
	public static function like_review(){
		global $c;
		$user=user::check_login('', 1);
		if(!$user['UserId']) ly200::e_json('', 0);
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_ProId=(int)$g_ProId;
		$g_RId=(int)$g_RId;
		$g_Like=(int)$g_Like;//1 & -1
		$where="RId='{$g_RId}'";
		$review_row=str::str_code(db::get_one('products_review', $where, 'Agree, Oppose'));
		if(!db::get_row_count('products_comment', "{$user['fetch_where']} and {$where}")){
			if($g_Like==1){//点赞
				db::insert('products_comment', array('RId'=>$g_RId, 'UserId'=>$_SESSION['User']['UserId'], 'Agree'=>1));
				db::query("update products_review set Agree=Agree+1 where $where");
				$result='('.($review_row['Agree']+1).')';
			}else{//点踩
				db::insert('products_comment', array('RId'=>$g_RId, 'UserId'=>$_SESSION['User']['UserId'], 'Oppose'=>1));
				db::query("update products_review set Oppose=Oppose+1 where $where");
				$result='('.($review_row['Oppose']+1).')';
			}
			ly200::e_json($result, 1);
		}
	}
	/************************** 评论模块 End **************************/
	
	/************************** 站内信模块 Start **************************/
	public static function write_inbox(){
		global $c;
		$user=user::check_login();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Subject=$p_Subject;
		$p_Content=$p_Content;
		$p_PicPath=$p_PicPath;
		!$user['UserId'] && exit();
		if($_FILES['PicPath']['name']){
			if(!in_array($_FILES['PicPath']['type'],array('image/png','image/jpg','image/jpeg','image/gif')) || $_FILES['PicPath']['size']>1024*1024*2) js::back($c['lang_pack']['picture_tips']);
			$resize_ary=array('85x85');
			$save_dir='/u_file/'.date('ym/').'inbox/'.date('d/');
			$picpath=file::file_upload($_FILES['PicPath'], $save_dir);
			$ext_name=file::get_ext_name($picpath);
			foreach($resize_ary as $v){
				$size_w_h=explode('x', $v);
				$resize_path=img::resize($picpath, $size_w_h[0], $size_w_h[1]);
			}
		}
		$data=array(
			'UserId'	=>	$user['UserId'],
			'Subject'	=>	$p_Subject,
			'Content'	=>	$p_Content,
			'PicPath'	=>	$picpath,
			'IsRead'	=>	0,
			'AccTime'	=>	$c['time']
		);
		db::insert('user_message', $data);
		js::location('/account/outbox/');
	}
	
	public static function reply_inbox(){
		global $c;
		$user=user::check_login();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		(!$user['UserId'] || !$p_Content) && exit();
		
		if($_FILES['PicPath']['name']){
			if(!in_array($_FILES['PicPath']['type'],array('image/png','image/jpg','image/jpeg','image/gif')) || $_FILES['PicPath']['size']>1024*1024*2) js::back($c['lang_pack']['picture_tips']);
			$resize_ary=array('85x85');
			$save_dir='/u_file/'.date('ym/').'inbox/'.date('d/');
			$picpath=file::file_upload($_FILES['PicPath'], $save_dir);
			$ext_name=file::get_ext_name($picpath);
			foreach($resize_ary as $v){
				$size_w_h=explode('x', $v);
				$resize_path=img::resize($picpath, $size_w_h[0], $size_w_h[1]);
			}
		}
		
		if((int)$p_MId){
			$data=array(
				'MId'		=>	(int)$p_MId,
				'UserId'	=>	$user['UserId'],
				'Content'	=>	$p_Content,
				'PicPath'	=>	$picpath,
				'AccTime'	=>	$c['time']
			);
			db::insert('user_message_reply', $data);
			db::update('user_message',"MId='$p_MId'",array('IsRead'=>0));
		}else{	//订单询盘
			$data=array(
				'UserId'	=>	$user['UserId'],
				'Module'	=>	'orders',
				'Subject'	=>	(int)$p_OId,
				'Content'	=>	$p_Content,
				'PicPath'	=>	$picpath,
				'IsRead'	=>	0,
				'AccTime'	=>	$c['time'],
			);
			db::insert('user_message', $data);
		}
		js::location($p_JumpUrl,$c['lang_pack']['mobile']['submit_success']);
	}
	
	public static function get_inbox_list(){
		global $c;
		$user=user::check_login();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Page=(int)$p_Page;
		$p_Page<1 && $p_Page=1;
		$row_count=10;
		if($p_Name=='inbox_list'){	//收件箱
			$row=str::str_code(db::get_limit_page('user_message', "(UserId like '%|{$user['UserId']}|%' or UserId='-1') and Module='others' and Type=1", '*', 'MId desc', $p_Page, $row_count));
		}elseif($p_Name=='outbox_list'){	//发件箱
			$row=str::str_code(db::get_limit_page('user_message', "{$user['fetch_where']} and Module='others' and Type=0", '*', 'MId desc', $p_Page, $row_count));
		}
		$result='';
		if($row[0]){
			$result.='<ul class="msg_list">';
			foreach($row[0] as $k=>$v){
				$url="/account/".str_replace('_list', '', $p_Name)."/view".sprintf('%04d', $v['MId']).".html";
				$subject=$v['Subject'];
				$is_read=0;
				if($v['IsRead']){
					$userid_ary=array_flip(explode('|', $v['UserId']));
					$isread_ary=explode('|', $v['IsRead']);
					$is_read=$isread_ary[$userid_ary[$user['UserId']]];
				}
				$mail=$is_read?' read':'';
				$result.='<li><a href="'.$url.'" title="'.$subject.'" class="sys_bg_button"><i class="fl'.$mail.'"></i><span class="time fr">'.date('M d,Y H:i:s', $v['AccTime']).'</span>'.$subject.'</a></li>';
			}
			$result.='</ul>';
			$result.='<div class="blank20"></div>';
			$result.='<div id="turn_page">'.str_replace(array('<a', "href='page="), array('<a data="'.$p_Name.'"', "href='javascript:;' page='"), ly200::turn_page_html($row[1], $row[2], $row[3], 'page=', $c['lang_pack']['user']['previous'], $c['lang_pack']['user']['next'], 3, '', 0)).'</div>';
			ly200::e_json($result, 1);
		}else{
			ly200::e_json('', 0);
		}
	}
	
	public static function get_inbox_list_mb(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$user=user::check_login('', 1);
		if(!$user['UserId']) ly200::e_json('', 0);
		$p_Page=(int)$p_Page;
		$row_count=20;
		$row=array();
		if($p_module=='inbox'){
			$row=str::str_code(db::get_limit_page('user_message', "(UserId like '%|{$user['UserId']}|%' or UserId='-1') and Module='others' and Type=1", '*', 'MId desc', $p_Page, $row_count));
		}elseif($p_module=='outbox'){
			$row=str::str_code(db::get_limit_page('user_message', "{$user['fetch_where']} and Module='others' and Type=0", '*', 'MId desc', $p_Page, $row_count));
		}
		if($row){
			$row_new=array();
			foreach($row[0] as $k => $v){
				$row_new[$k]['MId']=$v['MId'];
				$row_new[$k]['Subject']=$v['Subject'];
				$row_new[$k]['AccTime']=date('M d,Y', $v['AccTime']);
				$is_read=0;
				if($v['IsRead']){
					$userid_ary=array_flip(explode('|', $v['UserId']));
					$isread_ary=explode('|', $v['IsRead']);
					$is_read=$isread_ary[$userid_ary[$user['UserId']]];
				}
				$row_new[$k]['IsRead']=$is_read;
			}
			$row[0]=$row_new;
		}
		ly200::e_json($row, 1);
	}
	
	public static function get_inbox_detail_mb(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$user=user::check_login('', 1);
		if(!$user['UserId']) ly200::e_json('user_message', 0);
		$p_MId=(int)$p_MId;
		if($p_module=='inbox'){
			$row=str::str_code(db::get_one('user_message',"(UserId like '%|{$user['UserId']}|%' or UserId='-1') and Module='others' and Type=1 and MId='$p_MId'"));
		}elseif($p_module=='outbox'){
			$row=str::str_code(db::get_one('user_message',"{$user['fetch_where']} and Module='others' and Type=0 and MId='$p_MId'"));
		}
		if($row['IsRead'] && $row['Type']==1){
			$userid_ary=array_flip(explode('|', $row['UserId']));
			//print_r($userid_ary);exit;
			$isread_ary=explode('|', $row['IsRead']);
			$key=$userid_ary[$user['UserId']];
			$is_read=$isread_ary[$key];
			if($is_read!=1){
				$isread_ary[$key]=1;
				$IsRead=implode('|', $isread_ary);
				db::update('user_message', "MId='{$row['MId']}'", array('IsRead'=>$IsRead));
			}
		}
		$result=array(
			'Subject'	=>	$row['Subject'],
			'Content'	=>	$row['Content'],
			'PicPath'	=>	$row['PicPath'],
			'AccTime'	=>	date('M d,Y', $row['AccTime'])
		);
		ly200::e_json($result, 1);
	}
	/************************** 站内信模块 End **************************/
	
	/************************** 第三方登录 Start **************************/
	public static function user_oauth(){
		global $c;
		@set_time_limit(0);
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		unset($_SESSION['Oauth']['User']);
		if((int)$_SESSION['User']['UserId']) js::location("/account/sign-up.html?JumpUrl=".urlencode($_GET['JumpUrl']));
		if($g_Type=='Facebook'){
			$where="FaceBookId='$g_id'";
		}elseif($g_Type=='Twitter'){
			$oauth_verifier=$g_oauth_verifier;
			$oauth_token=$g_oauth_token;
			$request_token=array();
			$request_token['oauth_token']=$_SESSION['TwitterOauth']['oauth_token'];
			$request_token['oauth_token_secret']=$_SESSION['TwitterOauth']['oauth_token_secret'];
			if($oauth_verifier){
				$data=$c['config']['Platform']['Twitter']['SignIn']['Data'];
				$TwitterCallback=true;
				unset($_SESSION['TwitterOauth']);
				include($c['root_path'].'static/themes/default/user/oauth/twitter/api.php');
				exit;
			}
		}elseif($g_Type=='Google'){
			$where="GoogleId='$g_id'";
			$name_ary=explode(' ', $g_name);
			$g_last_name=$name_ary[0];
			$g_first_name=$name_ary[1];
		}elseif($g_Type=='Paypal'){
			if(!$_GET['scope'] || !$_GET['code']){
				$_GET['error_description'] && exit($_GET['error_description']);
				echo "<script>window.opener.location.href='/';window.close();</script>";
			}
			$paypal_sdk=$c['root_path'].'/static/themes/default/user/oauth/paypal_sdk_core/lib';
			include($paypal_sdk.'/common/PPApiContext.php');
			include($paypal_sdk.'/common/PPModel.php');
			include($paypal_sdk.'/common/PPUserAgent.php');
			include($paypal_sdk.'/common/PPReflectionUtil.php');
			include($paypal_sdk.'/common/PPArrayUtil.php');
			include($paypal_sdk.'/PPConfigManager.php');
			include($paypal_sdk.'/PPLoggingManager.php');
			include($paypal_sdk.'/PPHttpConfig.php');
			include($paypal_sdk.'/PPHttpConnection.php');
			include($paypal_sdk.'/PPLoggingLevel.php');
			include($paypal_sdk.'/PPConstants.php');
			include($paypal_sdk.'/transport/PPRestCall.php');
			include($paypal_sdk.'/exceptions/PPConnectionException.php');
			include($paypal_sdk.'/handlers/IPPHandler.php');
			include($paypal_sdk.'/handlers/PPOpenIdHandler.php');
			include($paypal_sdk.'/auth/openid/PPOpenIdTokeninfo.php');
			include($paypal_sdk.'/auth/openid/PPOpenIdUserinfo.php');
			include($paypal_sdk.'/auth/openid/PPOpenIdAddress.php');
			include($paypal_sdk.'/auth/openid/PPOpenIdError.php');
			include($paypal_sdk.'/auth/openid/PPOpenIdSession.php');
			$data=$c['config']['Platform']['Paypal']['SignIn']['Data'];
			$apicontext=new PPApiContext(array('mode'=>'live'));//
			$code=$_REQUEST['code'];
			$params=array(
				'client_id'		=>	$data['client_id'],
				'client_secret' =>	$data['client_secret'],
				'code' 			=>	$code
			);
			$token=PPOpenIdTokeninfo::createFromAuthorizationCode($params, $apicontext);
			$params=array('access_token'=>$token->getAccessToken());
			$user=PPOpenIdUserinfo::getUserinfo($params, $apicontext);
			//这步之后数据库连接出问题，重新跳转一次处理
			$g_id=$g_email=$user->getEmail();
			$where="PaypalId='$g_id'";
			$g_last_name=$user->getFamilyName();
			$g_first_name=$user->getGivenName();
			$_SESSION['Oauth']['Paypal']=array(
				'PaypalId'	=>	$g_id,
				'FirstName'	=>	$g_first_name,
				'LastName'	=>	$g_last_name
			);
			js::location("/?do_action=user.user_oauth_paypal");
		}elseif($g_Type=='VK'){
			$where="VKId='$g_id'";
		}
		if(($g_Type=='Twitter' && !$oauth_verifier) || ($g_Type!='Twitter' && !$g_id)){//缺失重要数据，自动跳出
			js::location('/');
		}
		if(db::get_row_count('user', $where)){
			$user_row=str::str_code(db::get_one('user', $where));
			$time=$c['time'];
			$ip=ly200::get_ip();
			$_SESSION['User']=$user_row;

			$UserId=$user_row['UserId'];
			db::update('user', "UserId='{$UserId}'", array('LastLoginTime'=>$time, 'LastLoginIp'=>$ip, 'LoginTimes'=>$user_row['LoginTimes']+1));
			cart::login_update_cart();
			user::operation_log($UserId, '会员登录', 1);
			if($g_Type=='Paypal'){
				js::location($_SESSION['Ueeshop']['LoginReturnUrl']?$_SESSION['Ueeshop']['LoginReturnUrl']:'/account/');
			}else{
				ly200::e_json(array($_SESSION['Ueeshop']['LoginReturnUrl']?$_SESSION['Ueeshop']['LoginReturnUrl']:'/account/'), 1);
			}
			unset($_SESSION['Ueeshop']['LoginReturnUrl']);
		}else{
			$_SESSION['Oauth']['User']=array(
				'Type'		=>	$g_Type,
				'Id'		=>	$g_id,
				'Email'		=>	$g_email,
				'FirstName'	=>	(($g_first_name && $g_first_name!='undefined')?$g_first_name:''),
				'LastName'	=>	(($g_last_name && $g_last_name!='undefined')?$g_last_name:''),
				'Gender'	=>	$g_gender
			);
			if(trim($g_email)!='' && trim($g_email)!='undefined'){//有邮箱
				self::user_oauth_binding();
			}else{//没有邮箱
				if(ly200::is_mobile_client(1)==1){//移动端
					ly200::e_json(array('/account/binding.html'), 1);
				}else{//PC端
					ly200::e_json(array('/account/binding.html'), 0);
				}
			}
		}
	}

	public static function user_oauth_paypal(){
		global $c;
		$where="PaypalId='{$_SESSION['Oauth']['Paypal']['PaypalId']}'";
		if(db::get_row_count('user', $where)){
			$user_row=str::str_code(db::get_one('user', $where));
			$time=$c['time'];
			$ip=ly200::get_ip();
			$_SESSION['User']=$user_row;
			$UserId=$user_row['UserId'];
			db::update('user', "UserId='{$UserId}'", array('LastLoginTime'=>$time, 'LastLoginIp'=>$ip, 'LoginTimes'=>$user_row['LoginTimes']+1));
			cart::login_update_cart();
			user::operation_log($UserId, '会员登录', 1);
			echo "<script>window.opener.location.href='".($_SESSION['Ueeshop']['LoginReturnUrl']?$_SESSION['Ueeshop']['LoginReturnUrl']:'/account/')."';window.close();</script>";
			unset($_SESSION['Oauth']['Paypal'], $_SESSION['Ueeshop']['LoginReturnUrl']);
		}else{
			$_SESSION['Oauth']['User']=array(
				'Type'		=>	'Paypal',
				'Id'		=>	$_SESSION['Oauth']['Paypal']['PaypalId'],
				'FirstName'	=>	$_SESSION['Oauth']['Paypal']['FirstName'],
				'LastName'	=>	$_SESSION['Oauth']['Paypal']['LastName'],
			);
			unset($_SESSION['Oauth']['Paypal']);
			echo "<script>window.opener.location.href='/account/binding.html';window.close();</script>";
			exit();
			js::location('/account/binding.html');
		}
	}
	
	public static function user_oauth_binding(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if($_POST){//表单提交
			if(empty($p_Email) || !preg_match('/^\w+[a-zA-Z0-9-.+_]+@[a-zA-Z0-9-.+_]+\.\w*$/i', $p_Email)){
				ly200::e_json(array($c['lang_pack']['user']['error']['Incomplete']), 0);
			}
			$Email=$p_Email;
			$Type=$p_Type;
		}else{//邮箱直接传递
			$Email=$_SESSION['Oauth']['User']['Email'];
			$Type=$_SESSION['Oauth']['User']['Type'];
		}
		
		$time=$c['time'];
		$ip=ly200::get_ip();
		if(db::get_row_count('user', "Email='$Email'")){//判断是否存在此邮箱会员
			$user_row=str::str_code(db::get_one('user', "Email='$Email'"));
			$_SESSION['User']=$user_row;
			$UserId=$user_row['UserId'];
			$data=array(
				'LastLoginTime'	=>	$time,
				'LastLoginIp'	=>	$ip,
				'LoginTimes'	=>	$user_row['LoginTimes']+1
			);
			!$user_row['FirstName'] && $data['FirstName']=$_SESSION['Oauth']['User']['FirstName'];
			!$user_row['LastName'] && $data['LastName']=$_SESSION['Oauth']['User']['LastName'];
			if($Type=='Facebook'){
				$data['FaceBookId']=$_SESSION['Oauth']['User']['Id'];
			}elseif($Type=='Twitter'){
				$data['TwitterId']=$_SESSION['Oauth']['User']['Id'];
			}elseif($Type=='Google'){
				$data['GoogleId']=$_SESSION['Oauth']['User']['Id'];
			}elseif($Type=='Paypal'){
				$data['PaypalId']=$_SESSION['Oauth']['User']['Id'];
			}elseif($Type=='VK'){
				$data['VKId']=$_SESSION['Oauth']['User']['Id'];
			}
			db::update('user', "UserId='{$UserId}'", $data);
			cart::login_update_cart();
			user::operation_log($UserId, '会员登录', 1);
			unset($_SESSION['Oauth']['User']);
			ly200::e_json($_SESSION['Ueeshop']['LoginReturnUrl']?$_SESSION['Ueeshop']['LoginReturnUrl']:'/account/', 1);
		}else{
			$data=array(
				'Language'		=>	'en',
				'Gender'		=>	$_SESSION['Oauth']['User']['Gender'],
				'FirstName'		=>	$_SESSION['Oauth']['User']['FirstName'],
				'LastName'		=>	$_SESSION['Oauth']['User']['LastName'],
				'NickName'		=>	$_SESSION['Oauth']['User']['NickName'],
				'Email'			=>	$Email,
				'RegTime'		=>	$time,
				'RegIp'			=>	$ip,
				'LastLoginTime'	=>	$time,
				'LastLoginIp'	=>	$ip,
				'LoginTimes'	=>	1
			);
			if($Type=='Facebook'){
				$data['FaceBookId']=$_SESSION['Oauth']['User']['Id'];
			}elseif($Type=='Twitter'){
				$data['TwitterId']=$_SESSION['Oauth']['User']['Id'];
			}elseif($Type=='Google'){
				$data['GoogleId']=$_SESSION['Oauth']['User']['Id'];
			}elseif($Type=='Paypal'){
				$data['PaypalId']=$_SESSION['Oauth']['User']['Id'];
			}elseif($Type=='VK'){
				$data['VKId']=$_SESSION['Oauth']['User']['Id'];
			}
			db::insert('user', $data);
			$UserId=db::get_insert_id();
			$_SESSION['User']=$data;
			$_SESSION['User']['UserId']=$UserId;
			user::operation_log($UserId, '会员注册', 1);
			unset($_SESSION['Oauth']['User']);
			ly200::e_json($_SESSION['Ueeshop']['LoginReturnUrl']?$_SESSION['Ueeshop']['LoginReturnUrl']:'/account/', 1);
		}
		unset($_SESSION['Ueeshop']['LoginReturnUrl']);
	}
	/************************** 第三方登录 End **************************/
	
	/************************** Twitter 登录链接 ************************/
	public static function twitter_oauth_url(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if ($_SESSION['User']['UserId']){//已登录
			echo str::json_data(array('status'=>1));
		}else{
			$apilogin = $p_apilogin;
			$key = ~base64_decode($p_key);
			$secret = ~base64_decode($p_secret);
			$callback = urldecode($p_callback);
			include($c['root_path'].'static/themes/default/user/oauth/twitter/api.php');
		}
	}
	
	/************************** Facebook分享返回 Start **************************/
	public static function facebook_callback(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		echo '<script type="text/javascript">window.opener=null; window.open("", "_self"); window.close();</script>';
		exit;
	}
	/************************** Facebook分享返回 End **************************/
	
	/************************** 产品询盘 Start **************************/
	public static function product_inquiry(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		(!$_SESSION['User']['UserId'] || !trim($p_Content) || !$p_ProId) && js::back($c['lang_pack']['user']['error']['Error'], 0);;
		if($_FILES['PicPath']['name']){
			if(!in_array($_FILES['PicPath']['type'],array('image/png','image/jpg','image/jpeg','image/gif')) || $_FILES['PicPath']['size']>1024*1024*2) js::back($c['lang_pack']['picture_tips']);
			$resize_ary=array('85x85');
			$save_dir='/u_file/'.date('ym/').'inbox/'.date('d/');
			$picpath=file::file_upload($_FILES['PicPath'], $save_dir);
			$ext_name=file::get_ext_name($picpath);
			foreach($resize_ary as $v){
				$size_w_h=explode('x', $v);
				$resize_path=img::resize($picpath, $size_w_h[0], $size_w_h[1]);
			}
		}
		$data=array(
			'UserId'	=>	(int)$_SESSION['User']['UserId'],
			'Module'	=>	'products',
			'Subject'	=>	(int)$p_ProId,
			'Content'	=>	$p_Content,
			'PicPath'	=>	$picpath,
			'IsRead'	=>	0,
			'AccTime'	=>	$c['time']
		);
		db::insert('user_message',$data);
		js::back($c['lang_pack']['mobile']['submit_success']);
	}
	
	/************************** 产品询盘 End **************************/

	public static function get_user_coupons(){ //会员领取优惠券
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$UserId = $_SESSION['User']['UserId'];
		$ParentId = (int)$p_CId;
		$CouponNumber = user::get_user_coupons($UserId,$ParentId);
		if($CouponNumber){
			ly200::e_json($c['lang_pack']['user']['new_coupon'], 1);
		}else{
			ly200::e_json('', 0);
		}
	}
}
?>