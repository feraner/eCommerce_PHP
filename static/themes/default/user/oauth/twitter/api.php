<?php !isset($c) && exit();?>
<?php
//Twitter 接口
require('autoload.php');
use Abraham\TwitterOAuth\TwitterOAuth;

if ($TwitterCallback){//返回处理
	$connection = new TwitterOAuth($data['CONSUMER_KEY'], $data['CONSUMER_SECRET'], $request_token['oauth_token'], $request_token['oauth_token_secret']);
	$access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $oauth_verifier));
	if ($access_token){
		$connection2 = new TwitterOAuth($data['CONSUMER_KEY'], $data['CONSUMER_SECRET'], $access_token['oauth_token'], $access_token['oauth_token_secret']);
		$user = $connection2->get("account/verify_credentials");//获取用户信息
		$TwitterId = $user->id;
		if ($TwitterId){
			$where="TwitterId='{$TwitterId}'";
			if(db::get_row_count('user', $where)){
				$user_row=str::str_code(db::get_one('user', $where));
				$time=$c['time'];
				$ip=ly200::get_ip();
				$_SESSION['User']=$user_row;
				$UserId=$user_row['UserId'];
				db::update('user', "UserId='{$UserId}'", array('LastLoginTime'=>$time, 'LastLoginIp'=>$ip, 'LoginTimes'=>$user_row['LoginTimes']+1));
				cart::login_update_cart();
				user::operation_log($UserId, '会员登录');
				js::location($_SESSION['Ueeshop']['LoginReturnUrl']?$_SESSION['Ueeshop']['LoginReturnUrl']:'/account/');
			}else{
				$_SESSION['Oauth']['User']=array(
					'Type'		=>	$g_Type,
					'Id'		=>	$TwitterId,
					'Email'		=>	'',
					'FirstName'	=>	$user->name,
					'LastName'	=>	'',
					'NickName'	=>	$user->screen_name,
					'Gender'	=>	''
				);
				js::location('/account/binding.html');
			}
		}else{
			js::location('/');
		}
	}else{
		js::location('/');
	}
	exit;
}else if ($apilogin){//登录
	$connection = new TwitterOAuth($key, $secret);
	$request_token = $connection->oauth('oauth/request_token', array('oauth_callback'=>$callback));
	$_SESSION['TwitterOauth']['oauth_token'] = $request_token['oauth_token'].' ';
	$_SESSION['TwitterOauth']['oauth_token_secret'] = $request_token['oauth_token_secret'];
	$TwitterOAuthUrl = $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
	echo str::json_data(array('status'=>0, 'url'=>$TwitterOAuthUrl));
}


