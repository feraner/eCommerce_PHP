<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class js{
	public static function contents_code($str){
		return str_replace('\'', '\\\'', str_replace(array("\r\n", "\r", "\n"), '', ereg_replace('/[\s].*/gi', '', $str)));
	}
	
	public static function location($url, $alert='', $top=''){
		if($alert=='' && $top=='' && !headers_sent()){
			header("Location: $url");
			exit;
		}
		
		echo '<script language="javascript">';
		if($alert){
			echo 'alert(\''.js::contents_code($alert).'\');';
		}
		echo "window{$top}.location='$url';";
		echo '</script>';
		exit;
	}
	
	public static function back($alert=''){
		echo '<script language="javascript">';
		if($alert){
			echo 'alert(\''.js::contents_code($alert).'\');';
		}
		echo 'history.back();';
		echo '</script>';
		exit;
	}

	public static function jump_301(){
		if(!preg_match("/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/", $_SERVER['HTTP_HOST'])){//非IP访问
			if(!(int)db::get_value('config', 'GroupId="http" and Variable="code_301"', 'Value')){return;}
			if(!in_array(str_replace('-', '_', reset(explode('.', $_SERVER['HTTP_HOST']))), ly200::subdomain_list())){
				$http=($_SERVER['SERVER_PORT']==443?'https://':'http://');
				
				@header( "HTTP/1.1 301 Moved Permanently");
				@header("Location: {$http}www.{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
				exit;
			}
		}
	}
	
	public static function jump_lang(){
		global $c;
		
		if(!(int)db::get_value('config', 'GroupId="global" and Variable="BrowserLanguage"', 'Value')){return;}
		$http_lang=substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		$http_lang=$http_lang=='ja'?'jp':$http_lang;//日文
		$http_lang=str_replace(array('zh','-'), array('zh-tw','_'), $http_lang);//繁体中文
		if($http_lang!=substr($c['lang'], 1) && !in_array(str_replace('-','_',reset(explode('.', $_SERVER['HTTP_HOST']))), array_keys($c['lang_name'])) && in_array($http_lang, $c['config']['global']['Language'])){
			$http=($_SERVER['SERVER_PORT']==443?'https://':'http://');
			$host=in_array(reset(explode('.', $_SERVER['HTTP_HOST'])), ly200::subdomain_list())?implode('.', array_slice(explode('.', $_SERVER['HTTP_HOST']), 1)):$_SERVER['HTTP_HOST'];
			$_SESSION['jump_lang'] = 1;
			@header( "HTTP/1.1 301 Moved Permanently");
			@header("Location: {$http}{$http_lang}.{$host}{$_SERVER['REQUEST_URI']}");
			exit;
		}
	}
}
?>