<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class tx_wechat{
	public function api($access_token, $method, $get='', $post='', $multi=false, $result_is_xml=false, $chunck=2,$data=''){
		$url=array(
			'pay_unifiedorder'			=>	'https://api.mch.weixin.qq.com/pay/unifiedorder',	//统一支付接口
			'order_query'				=>	'https://api.mch.weixin.qq.com/pay/orderquery',// 查询订单
			'refund'					=>	'https://api.mch.weixin.qq.com/secapi/pay/refund',//申请退款
			'refund_query'				=>	'https://api.mch.weixin.qq.com/pay/refundquery',//查询退款
		);
		$token_field='access_token';
		$access_token && $access_token="$token_field=$access_token&";	//Access_Token比较多api使用，所以放入函数参数
		$url_ary=$result=$return=array();
		if($multi){
			foreach($method as $k=>$v){
				$url_ary[]="{$url[$v]}?$access_token{$get[$k]}";
			}
			$result=ly200::curl_multi($url_ary, $post, array(CURLOPT_SSLVERSION=>CURL_SSLVERSION_TLSv1), $chunck);
		}else{
			$url_ary[]="{$url[$method]}?$access_token{$get}";
			$curl_par=array(CURLOPT_SSLVERSION=>CURL_SSLVERSION_TLSv1);
			if(in_array($method,array('refund'))){
				$curl_par[CURLOPT_SSL_VERIFYPEER]=false;
				$curl_par[CURLOPT_SSL_VERIFYHOST]=false;
				$curl_par[CURLOPT_SSLCERT]=$data['apiclient_cert'];
				$curl_par[CURLOPT_SSLKEY]=$data['apiclient_key'];
				$curl_par[CURLOPT_CAINFO]=$data['rootca'];
			}
			$result[]=ly200::curl($url_ary[0], $post, '', $curl_par);
		}
		foreach($url_ary as $k=>$v){	//不使用$result作为循环，因为有些可能并没有返回结果
			$info=!$result_is_xml?str::json_data($result[$k], 'decode'):json_decode(json_encode(simplexml_load_string($result[$k], 'SimpleXMLElement', LIBXML_NOCDATA)), true);
			if(!$info || (isset($info['errcode']) && (int)$info['errcode'])){
				$info=self::errcode($info['errcode'])?self::errcode($info['errcode']):$info['errmsg'];
				!$info && $info='操作过程中出现未知错误，请重试！';
			}
			$return[]=$info;
		}
		return $multi?$return:$return[0];
	}
	
	//---------------------------------------------------以下是一些通用的函数---------------------------------------------------------------------------
	private function errcode($errcode){
		$errcode_ary=array(
			'-1'	=>	'系统繁忙',
			'0'		=>	'请求成功',
			'40001'	=>	'获取access_token时AppSecret错误，或者access_token无效',
			'40002'	=>	'不合法的凭证类型',
			'40003'	=>	'不合法的OpenID',
			'40004'	=>	'不合法的媒体文件类型',
			'40005'	=>	'不合法的文件类型',
			'40006'	=>	'不合法的文件大小',
			'40007'	=>	'不合法的媒体文件id',
			'40008'	=>	'不合法的消息类型',
			'40009'	=>	'不合法的图片文件大小',
			'40010'	=>	'不合法的语音文件大小',
			'40011'	=>	'不合法的视频文件大小',
			'40012'	=>	'不合法的缩略图文件大小',
			'40013'	=>	'不合法的APPID',
			'40014'	=>	'不合法的access_token',
			'40015'	=>	'不合法的菜单类型',
			'40016'	=>	'不合法的按钮个数',
			'40017'	=>	'不合法的按钮个数',
			'40018'	=>	'不合法的按钮名字长度',
			'40019'	=>	'不合法的按钮KEY长度',
			'40020'	=>	'不合法的按钮URL长度',
			'40021'	=>	'不合法的菜单版本号',
			'40022'	=>	'不合法的子菜单级数',
			'40023'	=>	'不合法的子菜单按钮个数',
			'40024'	=>	'不合法的子菜单按钮类型',
			'40025'	=>	'不合法的子菜单按钮名字长度',
			'40026'	=>	'不合法的子菜单按钮KEY长度',
			'40027'	=>	'不合法的子菜单按钮URL长度',
			'40028'	=>	'不合法的自定义菜单使用用户',
			'40029'	=>	'不合法的oauth_code',
			'40030'	=>	'不合法的refresh_token',
			'40031'	=>	'不合法的openid列表',
			'40032'	=>	'不合法的openid列表长度',
			'40033'	=>	'不合法的请求字符，不能包含\uxxxx格式的字符',
			'40035'	=>	'不合法的参数',
			'40038'	=>	'不合法的请求格式',
			'40039'	=>	'不合法的URL长度',
			'40050'	=>	'不合法的分组id',
			'40051'	=>	'分组名字不合法',
			'41001'	=>	'缺少access_token参数',
			'41002'	=>	'缺少appid参数',
			'41003'	=>	'缺少refresh_token参数',
			'41004'	=>	'缺少secret参数',
			'41005'	=>	'缺少多媒体文件数据',
			'41006'	=>	'缺少media_id参数',
			'41007'	=>	'缺少子菜单数据',
			'41008'	=>	'缺少oauth code',
			'41009'	=>	'缺少openid',
			'42001'	=>	'access_token超时',
			'42002'	=>	'refresh_token超时',
			'42003'	=>	'oauth_code超时',
			'43001'	=>	'需要GET请求',
			'43002'	=>	'需要POST请求',
			'43003'	=>	'需要HTTPS请求',
			'43004'	=>	'需要接收者关注',
			'43005'	=>	'需要好友关系',
			'44001'	=>	'多媒体文件为空',
			'44002'	=>	'POST的数据包为空',
			'44003'	=>	'图文消息内容为空',
			'44004'	=>	'文本消息内容为空',
			'45001'	=>	'多媒体文件大小超过限制',
			'45002'	=>	'消息内容超过限制',
			'45003'	=>	'标题字段超过限制',
			'45004'	=>	'描述字段超过限制',
			'45005'	=>	'链接字段超过限制',
			'45006'	=>	'图片链接字段超过限制',
			'45007'	=>	'语音播放时间超过限制',
			'45008'	=>	'图文消息超过限制',
			'45009'	=>	'接口调用超过限制',
			'45010'	=>	'创建菜单个数超过限制',
			'45015'	=>	'回复时间超过限制',
			'45016'	=>	'系统分组，不允许修改',
			'45017'	=>	'分组名字过长',
			'45018'	=>	'分组数量超过上限',
			'46001'	=>	'不存在媒体数据',
			'46002'	=>	'不存在的菜单版本',
			'46003'	=>	'不存在的菜单数据',
			'46004'	=>	'不存在的用户',
			'47001'	=>	'解析JSON/XML内容错误',
			'48001'	=>	'api功能未授权',
			'50001'	=>	'用户未授权该api'
		);
		return $errcode_ary[$errcode];
	}
	
	public static function array_to_xml($data){	//XML编码
		$xml='<xml>';
		foreach($data as $k=>$v){
			is_numeric($k) && $k="item id=\"$k\"";
			$xml		.=	"<$k>";
			$xml		.=	'<![CDATA['.$v.']]>';
			list($k,)	=	explode(' ', $k);
			$xml		.=	"</$k>";
		}
		$xml.='</xml>';
		return $xml;
	}
	
	public static function qrcode($data,$prefix,$size=10){
		global $c;
		require_once $c['root_path'].'inc/class/qrcode/qrlib.php';
		$filename='/tmp/qrcode/'.$prefix.'.png';
		if(!is_dir($c['root_path'].dirname($filename))){
			file::mk_dir(dirname($filename));
		}
		QRcode::png($data,$c['root_path'].$filename,'M',$size, 2);
		return $filename;
	}
}
?>