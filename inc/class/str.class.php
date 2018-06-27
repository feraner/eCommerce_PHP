<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class str{
	public static function str_code($data, $fun='htmlspecialchars'){	//文本编码
		if(!is_array($data)){
			return $fun($data);
		}
		$new_data=array();
		foreach((array)$data as $k=>$v){
			if(is_array($v)){
				$new_data[$k]=str::str_code($v, $fun);
			}else{
				$new_data[$k]=$fun($data[$k]);
			}
		}
		return $new_data;
	}
	
	public static function str_color($str='', $key=0, $return_type=0){
		$key>15 && $key=$key%15;
		if($return_type==0){
			return "<font class='fc_$key'>$str</font>";
		}else{
			return "fc_$key";
		}
	}
	
	public static function get_time($t, $return=0, $type=1){
		global $c;
		$format=array('Y-m-d', 'Y-m-01', 'Y-01-01');
		$type_ary=array('day', 'month', 'year');
		$st=strtotime(date($format[$type], $c['time']));
		$time=strtotime("$t {$type_ary[$type]}", $st);
		return $return==0?$time:date($format[$type], $time);
	}
	
	public static function iconver($data, $source='UTF-8', $target='GBK'){
		global $c;
		$chs=new iconver();
		if(!is_array($data)){
			return $chs->Convert($data, $source, $target);
		}
		$new_data=array();
		foreach((array)$data as $k=>$v){
			if(is_array($v)){
				$new_data[$k]=str::iconver($v, $source, $target);
			}else{
				$new_data[$k]=$chs->Convert($data[$k], $source, $target);
			}
		}
		return $new_data;
	}
	
	public static function str_sprintf($str, $vars, $char='%'){	//替换多个参数的sprintf
    	if(is_array($vars)){
			foreach($vars as $k=>$v){
				$str=str_replace($char.$k, $v, $str);
			}
		}
		return $str;
	}
	
	public static function rand_code($length=10){	//随机命名
		global $c;
		return substr(md5($c['time']+mt_rand(100000, 999999)), mt_rand(0, 32-$length), $length);	
	}
	
	public static function json_data($data, $action='encode'){	//json数据编码
		if($action=='encode'){
			if(!function_exists('unidecode')){
				function unidecode($match){
					return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
				}
			}
			return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'unidecode', json_encode($data));
		}else{
			return (array)json_decode($data, true);
		}
	}
	
	public static function format($str){	//格式化文本
		$str=htmlspecialchars($str);
		$str=str_replace('  ', '&nbsp;&nbsp;', $str);
		$str=nl2br($str);	
		return $str;
	}
	
	public static function cut_str($str, $length, $start=0){	//剪切字符串
		$str_0=array('&amp;', '&quot;', '&lt;', '&gt;', '&ldquo;', '&rdquo;');
		$str_1=array('&', '"', '<', '>', '“', '”');
		$str=str_replace($str_0, $str_1, $str);
		$len=strlen($str);
		if($len<=$length){return str_replace($str_1, $str_0, $str);}
		$substr='';
		$n=$m=0;
		for($i=0; $i<$len; $i++){
			$x=substr($str, $i, 1);
			$a=base_convert(ord($x), 10, 2);
			$a=substr('00000000'.$a, -8);
			if($n<$start){
				if(substr($a, 0, 3)==110){
					$i+=1;
				}elseif(substr($a, 0, 4)==1110){
					$i+=2;
				}
				$n++;
			}else{
				if(substr($a, 0, 1)==0){
					$substr.=substr($str, $i, 1);
				}elseif(substr($a, 0, 3)==110){
					$substr.=substr($str, $i, 2);
					$i+=1;
				}elseif(substr($a, 0, 4)==1110){
					$substr.=substr($str, $i, 3);
					$i+=2;
				}else{
					$substr.='';
				}
				if(++$m>=$length){break;}
			}
		}
		return str_replace($str_1, $str_0, $substr);
	}

	public static function str_echo($str, $length, $start=0, $replace=''){	//输出固定长度内容
		$result=@mb_substr(trim($str), $start, $length, "UTF-8");
		strlen($str)>$length && $result.=$replace;
		return $result;
	}
	
	public static function dump($str, $type=''){//原样输出
		echo '<pre>';
		if ($type){
			var_dump($str);
		}else{
			print_r($str);
		}
		echo '</pre>';
	}
	
	public static function keywords_filter($ary=array()){//过滤敏感词
		global $c;
		if((int)$c['Replica']) return;//Replica website do not filter
		
		@include_once($c['root_path'].'/manage/static/inc/filter.library.php');
		@include_once($c['root_path'].'/inc/un_filter_keywords.php');
		$filter_keywords_ary=$FilterKeyArr['Keyword'];
		$un_filter_keywords_ary=(array)@str::str_code($un_filter_keywords, 'strtolower');
		($ary && !@is_array($ary)) && $ary=(array)$ary;
		if((int)count($ary)){
			$filter_ary=$ary;
		}else{
			$filter_ary=$_POST;
			unset($filter_ary['do_action'], $filter_ary['PicPath'], $filter_ary['FilePath'], $filter_ary['UId'], $filter_ary['ColorPath'], $filter_ary['Number']);
		}
		$str=' '.@implode(' -- ', $filter_ary).' ';
		$key='';
		$in=0;
		foreach($filter_keywords_ary as $v){
			if(@count($un_filter_keywords_ary) && @in_array(strtolower(trim($v)), $un_filter_keywords_ary)){continue;}
			if(@substr_count(strtolower(stripslashes($str)), strtolower($v))){
				/*
				$in=1;
				$key=$v;
				break;
				*/
				$key.=($in?' ,':'').$v;
				$in++;
			}
		}
		unset($filter_ary);
		$in && ly200::e_json('带有敏感词：'.$key);
	}
	
	public static function clear_html($content) {//手机版清除格式
		$content = preg_replace("/<!--[^>]*-->/i", "", $content);//注释内容  
		$content = preg_replace("/style=.+?['|\"]/i",'',$content);//去除样式  
		$content = preg_replace("/class=.+?['|\"]/i",'',$content);//去除样式  
		$content = preg_replace("/id=.+?['|\"]/i",'',$content);//去除样式     
		$content = preg_replace("/lang=.+?['|\"]/i",'',$content);//去除样式      
		$content = preg_replace("/width=.+?['|\"]/i",'',$content);//去除样式   
		$content = preg_replace("/height=.+?['|\"]/i",'',$content);//去除样式   
		//$content = preg_replace("/border=.+?['|\"]/i",'',$content);//去除样式   
		$content = preg_replace("/face=.+?['|\"]/i",'',$content);//去除样式
		$content = preg_replace("/&nbsp;/i", " ", $content);
		return $content;
	}
	
	public static function ary_unique($ary){//去除二维数组的重复项 
		foreach($ary as $v){
			$temp[$v[0]]=$v[1];
		}
		$temp=array_unique($temp);//去掉重复的字符串,也就是重复的一维数组
		foreach($temp as $k=>$v){
			$temp_new[]=array($k, $v);
		}
		return $temp_new;
	}
	
	public static function ary_del_min($ary, $min=0, $sort=0){	//把数据中小于$min的数据删除
		if(!is_array($ary)) return false;
		foreach($ary as $k=>$v){
			if((float)$v<$min){
				unset($ary[$k]);
			}
		}
		$ary=array_unique($ary);//删除重复，空值，0
		$sort?rsort($ary):sort($ary);//(从大到小)(从小到大)排序，防止故意乱填
		return $ary;
	}
	
	public static function ary_format($ary, $return=0, $unset='', $explode_char=',', $implode_char=','){	//$return，0：字符串，1：数组，2：in查询语句，3：or查询语句，4：返回第一个值
		!is_array($ary) && $ary=explode($explode_char, $ary);
		//$ary=array_filter($ary, self::ary_format_ext($v));
		$ary=array_filter($ary, array(self, 'ary_format_ext'));
		if($unset){	//从数组中删除这些值
			$unset=str::ary_format($unset, 1, '', $explode_char, $implode_char);
			foreach($ary as $k=>$v){
				if(in_array($v, $unset)){
					unset($ary[$k]);
				}
			}
		}
		if($return==0){	
			return $ary?($implode_char.implode($implode_char, $ary).$implode_char):'';
		}elseif($return==1){
			return $ary;
		}elseif($return==2 || $return==3){
			if(!$ary){return '0';}
			if($return==2){
				$is_numeric=true;
				foreach($ary as $v){
					if(!is_numeric($v)){
						$is_numeric=false;
						break;
					}
				}
				return ($is_numeric?'':"'").implode($is_numeric?',':"','", $ary).($is_numeric?'':"'");
			}else{
				return implode(' or ', $ary);
			}
		}elseif($return==4){
			return array_shift($ary);
		}
	}
	
	public static function ary_format_ext($v){
		return ($v!='' || $v===0)?true:false;
	}
	
	public static function attr_decode($str){	//格式化产品属性内容里面的双引号
		//$str=str_replace(array('{"', '":"', '","', '"}'), array('{\"', '\":\"', '\",\"', '\"}'), $str);
		//$str=str_replace('"', '\\"', $str);
		//$str=str_replace('\\\"', '"', $str);
		$str=str::str_code($str, 'stripslashes');
		return $str;
	}
	
	public static function unescape($str){ 	//escape转译解码
		$ret='';
		$len=strlen($str);
		for($i=0; $i<$len; $i++){
			if($str[$i]=='%' && $str[$i+1]=='u'){
				$val=hexdec(substr($str, $i+2, 4));
				if($val<0x7f){
					$ret.=chr($val);
				}elseif($val<0x800){
					$ret.=chr(0xc0|($val>>6)).chr(0x80|($val&0x3f));
				}else{
					$ret.=chr(0xe0|($val>>12)).chr(0x80|(($val>>6)&0x3f)).chr(0x80|($val&0x3f));
				}
				$i+=5;
			}elseif($str[$i]=='%'){
				$ret.=urldecode(substr($str, $i, 3));
				$i+=2;
			}else{
				$ret.=$str[$i];
			}
		}
		return $ret; 
	}
	
	public static function str_str($str, $array){	//查找字符串在另一字符串中的第一次出现
		foreach((array)$array as $v){
			if(strstr($str, $v)!==false){
				return true;
			}
		}
		return false;
	}

	public static function referrer_filter($referrer){
		$share_platform=array('facebook', 'twitter', 'plus.google.com', 'pinterest', 'linkedin', 'digg', 'reddit', 'blogger', 'vk.com', 'youtube', 'instagram');
		$search_engine=array('google', 'bing', 'yahoo', 'yandex', 'baidu', 'naver', 'ask', 'haosou', 'sogou', 'youdao');
		
		//0:搜索引擎	1:分享平台	 99:直接输入		100:其他
		if(!$referrer) return 99;
		foreach($share_platform as $v){if(substr_count($referrer, $v)) return 1;}
		foreach($search_engine as $v){if(substr_count($referrer, $v)) return 0;}
		return 100;
	}
	
	/********************************设置Cookie登录(start)********************************/
	public static function GetCookie($type=''){//获取Cookie
		global $c;
		$name=ly200::password($c['ApiKey']).'_'.$c['db_cfg']['database'];
		if($type){
			return $_COOKIE[$type][$name];
		}else{
			return $_COOKIE[$name];
		}
	}

	public static function SetTheCookie($type='', $value='', $expire=31536000, $path='/', $domain=''){//设置Cookie
		global $c;
		$name=ly200::password($c['ApiKey']).'_'.$c['db_cfg']['database'];//Cookie名称
		$type && $name="{$type}[{$name}]";
		$expire=$expire!=0?$expire+=$c['time']:$expire;//Cookie过期时间
		@setcookie($name, $value, $expire, $path, $domain);
	}

	public static function PwdCode($pwd){
		global $c;
		return ly200::password($_SERVER["HTTP_USER_AGENT"].$pwd.$c['ApiKey']);//将密码加密保存到Cookie
	}
	
	public static function str_crypt($str, $action='encrypt', $key='www-ly200-com'){//字符串加密
		if(!$str){return;}
		$action!='encrypt' && $str=base64_decode($str);
		$keylen=strlen($key);
		$strlen=strlen($str);
		$new_str='';
		for($i=0; $i<$strlen; $i++){
			$k=$i%$keylen;
			$new_str.=$str[$i]^$key[$k];
		}
		return $action!='decrypt'?base64_encode($new_str):$new_str;
	}
	/********************************设置Cookie登录(end)********************************/
}
?>