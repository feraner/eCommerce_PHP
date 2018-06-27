<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class ly200{
	public static function ad($Id){
		global $c;
		$themes=$c['manage']?$c['manage']['web_themes']:$c['theme'];
		$lang=substr($c['lang'], 1);
		$ad_row=db::get_one('ad', "Themes='$themes' and Number=$Id");
		$width=$ad_row['Width']?$ad_row['Width'].'px':'auto';
		$height=$ad_row['Height']?$ad_row['Height'].'px':'auto';
		$ad_contents="<div style='overflow:hidden;'>";
		if($ad_row['AdType']==0){
			$ad_ary=array();
			for($i=0; $i<$ad_row['PicCount']; ++$i){
				$ad_ary['Name'][$i]=str::str_code(str::json_data($ad_row['Name_'.$i], 'decode'));
				$ad_ary['Brief'][$i]=str::str_code(str::json_data($ad_row['Brief_'.$i], 'decode'));
				$ad_ary['Url'][$i]=str::str_code(str::json_data($ad_row['Url_'.$i], 'decode'));
				$ad_ary['PicPath'][$i]=str::str_code(str::json_data($ad_row['PicPath_'.$i], 'decode'));
			}
			if($ad_row['PicCount']==1){
				$ad_contents="<div>";
				$ad_ary['Url'][0][$lang] && $ad_contents.="<a href='{$ad_ary['Url'][0][$lang]}' target='_blank'>";
				if(is_file($c['root_path'].$ad_ary['PicPath'][0][$lang])) $ad_contents.="<img src='{$ad_ary['PicPath'][0][$lang]}' alt='{$ad_ary['Name'][0][$lang]}'>";
				$ad_ary['Url'][0][$lang] && $ad_contents.='</a>';
			}else{
				if($ad_row['ShowType']==1 || $ad_row['ShowType']==2 || $ad_row['ShowType']==3){
					$effect_ary=array('', 'fade', 'top', 'left');
					$interTime=5000;
					$ad_contents.=ly200::load_static('/static/js/plugin/banner/jQuery.blockUI.js', '/static/js/plugin/banner/jquery.SuperSlide.js');
					$ad_contents.="<style type='text/css'>
										.slideBox_{$Id}{overflow:hidden; position:relative;} 
										.slideBox_{$Id} .hd{height:15px; overflow:hidden; position:absolute; bottom:15px; z-index:1;} 
										.slideBox_{$Id} .hd ul{overflow:hidden; zoom:1; float:left;} 
										.slideBox_{$Id} .hd ul li{float:left; margin-left:5px; width:10px; height:10px; -webkit-border-radius:5px; -moz-border-radius:5px; border-radius:5px; background:#f1f1f1; cursor:pointer;}
										.slideBox_{$Id} .hd ul li:first-child{margin-left:0;}
										.slideBox_{$Id} .hd ul li.on{ background:#f00; color:#fff;} 
										.slideBox_{$Id} .bd{position:relative; height:100%; z-index:0;}
										.slideBox_{$Id} .bd ul li a{display:block; background-position:center top; background-repeat:no-repeat;}
									</style>";
					$ad_contents.='<div id="slideBox_'.$Id.'" class="slideBox_'.$Id.'">';
					$hd='<div class="hd"><ul>';
					$bd='<div class="bd"><ul>';
					for($i=0; $i<$ad_row['PicCount']; $i++){
						$b='target="_blank"';
						$p=$ad_ary['PicPath'][$i][$lang];
						$u=$ad_ary['Url'][$i][$lang];
						$n=$ad_ary['Name'][$i][$lang];
						if(!is_file($c['root_path'].$p)){continue;}
						if(!$u){ $u='javascript:;'; $b=''; }
						$hd.="<li></li>";
						$bd.="<li><a href='$u' $b><img src='$p' alt='$n' /></a></li>";
					}
					$hd.='</ul></div>';
					$bd.='</ul></div>';
					$ad_contents.=$hd.$bd;
					$ad_contents.='</div><script type="text/javascript">jQuery(document).ready(function(){jQuery(".slideBox_'.$Id.'").slide({mainCell:".bd ul",effect:"'.$effect_ary[$ad_row['ShowType']].'",autoPlay:true,interTime:'.$interTime.($width=='auto'?', showType:"bg"':'').'});});</script>';
				}else{
					$ad_contents.=ly200::load_static('/static/js/plugin/banner/swf_obj.js');
					$xmlData='<list>';
					for($i=0; $i<$ad_row['PicCount']; $i++){
						$p=$ad_ary['PicPath'][$i][$lang];
						$u=$ad_ary['Url'][$i][$lang];
						is_file($c['root_path'].$p) && $xmlData.="<item><img>$p</img><url>$u</url></item>";
					}
					$xmlData.='</list>';
					$ad_contents.="<div id='swfContents_{$Id}'></div>
								<script type='text/javascript'>
									var xmlData='$xmlData';
									var flashvars={xmlData:xmlData};
									var params={menu:false, wmode:'transparent'};
									var attributes={};
									swfobject.embedSWF('/static/images/swf/ad.swf', 'swfContents_{$Id}', '{$ad_row['Width']}', '{$ad_row['Height']}', '9', 'expressInstall.swf', flashvars, params, attributes);
								</script>";
				}
			}
		}else{
			$ad_contents='<div>'.$ad_row['Contents'];
		}
		$ad_contents.='</div>';
		return $ad_contents;
	}
	
	public static function ad_custom($Id, $AId){
		global $c;
		$lang=substr($c['lang'], 1);
		$ad_row=db::get_one('ad', "Themes='{$c['theme']}' and Number='$Id'".($AId?" and AId='$AId'":''));
		$result=array('Lang'=>$lang, 'Count'=>$ad_row['PicCount']);
		for($i=0; $i<$ad_row['PicCount']; ++$i){
			$result['Name'][$i]=str::str_code(str::json_data($ad_row['Name_'.$i], 'decode'));
			$result['Brief'][$i]=str::str_code(str::json_data($ad_row['Brief_'.$i], 'decode'));
			$result['Url'][$i]=str::str_code(str::json_data($ad_row['Url_'.$i], 'decode'));
			$result['PicPath'][$i]=str::str_code(str::json_data($ad_row['PicPath_'.$i], 'decode'));
		}
		return $result;
	}

	public static function get_cache_path($theme='', $root=1){
		global $c;
		return ($root?$c['root_path']:'').$c['tmp_dir'].'cache/'.$theme.'/'.substr($c['lang'], 1).'/';
	}
	
	public static function get_url($row, $field='products_category', $lang=''){
		global $c;
		!$lang && $lang=$c['lang'];
		$ary=@explode('_', $field);
		$length=count($ary);
		if($ary[0]=='article' && $length==1){
			$path=ly200::str_to_url($row['Title'.$lang]);
			if($row['CateId']==99){
				$url='/help/'.$path.'_h'.sprintf('%04d', $row['AId']).'.html';
				$url=$row['PageUrl']?'/help/'.$row['PageUrl'].'.html':$url;
			}else{
				$url='/art/'.$path.'_a'.sprintf('%04d', $row['AId']).'.html';
				$url=$row['PageUrl']?'/art/'.$row['PageUrl'].'.html':$url;
			}
			$url=$row['Url']?$row['Url']:$url;
		}elseif($ary[0]=='info' && $length==1){
			$path=ly200::str_to_url($row['Title'.$lang]);
			$path=$row['PageUrl']?$row['PageUrl']:$path;
			$url='/info/'.$path.'_i'.sprintf('%04d', $row['InfoId']).'.html';
			$url=$row['Url']?$row['Url']:$url;
		}elseif($ary[0]=='info' && $ary[1]=='category'){
			$path=ly200::str_to_url($row['Category'.$lang]);
			$url='/info/'.$path.'_c'.sprintf('%04d', $row['CateId']);
		}elseif($ary[0]=='products' && $length==1){
			$path=ly200::str_to_url($row['Name'.$lang]);
			$path=$row['PageUrl']?$row['PageUrl']:$path;
			$url='/'.$path.'_p'.sprintf('%04d', $row['ProId']).'.html';
		}elseif($ary[0]=='products' && $ary[1]=='category'){
			$path=ly200::str_to_url($row['Category'.$lang]);
			$url='/c/'.$path.'_'.sprintf('%04d', $row['CateId']);
		}elseif($ary[0]=='seckill'){
			$path=ly200::str_to_url($row['Name'.$lang]);
			$url='/'.$path.'_p'.sprintf('%04d', $row['ProId']).'_s'.sprintf('%04d', $row['SId']).'.html';
		}elseif($ary[0]=='tuan'){
			$path=ly200::str_to_url($row['Name'.$lang]);
			$url='/'.$path.'_p'.sprintf('%04d', $row['ProId']).'_t'.sprintf('%04d', $row['TId']).'.html';
		}elseif($ary[0]=='review'){
			$url='/review_p'.sprintf('%04d', $row['ProId']).'/';
		}elseif($ary[0]=='write' && $ary[1]=='review'){
			$url='/review-write/'.sprintf('%04d', $row['ProId']).'.html';
		}elseif($ary[0]=='blog' && $length==1){
			$path=ly200::str_to_url($row['Title']);
			$url='/blog/'.$path.'-b'.sprintf('%04d', $row['AId']).'.html';
		}elseif($ary[0]=='blog' && $ary[1]=='category'){
			$path=ly200::str_to_url($row['Category_en']);
			$url='/blog/c/'.$path.'-'.sprintf('%04d', $row['CateId']);
		}elseif($ary[0]=='blog' && $ary[1]=='date'){
			$url='/blog/t/'.$row;
		}else{
			$url_ary=array(
				'page'				=>	"/?a=article&AId={$row['AId']}",
				'info'				=>	"/?a=info&InfoId={$row['InfoId']}",
				'products_category'	=>	"/?a=products&CateId={$row['CateId']}",
				'products'			=>	"/?a=goods&ProId={$row['ProId']}",
				'blog'				=>	"/?m=blog&p=detail&AId={$row['AId']}",
			);
			$url=$url_ary[$field];
		}
		return $url;
	}

	public static function str_to_url($str){
		$url=strtolower(trim($str));
		$url=str_replace(array(' ', '/'), '-', $url);
		$url=str_replace(array('`','~','!','@','#','$','%','^','&','*','(',')','_','=','+','[','{',']','}',';',':','\'','"','\\','|','<',',','.','>','?',"\r","\n","\t"), '', $url);
		$url=preg_replace('/[^\x00-\x7F]+/', '', $url);
		$url=preg_replace('/-{2,}/', '-', $url);
		!eregi('^[a-z0-9]', $url) && $url='';
		return $url;
	}
	
	public static function get_narrow_url($query_string, $narrow_ary=array(), $id){
		$page_string=ly200::get_url_dir($_SERVER['REQUEST_URI'], $ext_name='.html');
		$query_string='/'.trim($page_string, '/').'?'.ly200::get_query_string($query_string);
		if($narrow_ary){
			$key=array_search($id, $narrow_ary);
			if($key===false){
				$narrow_ary[]=$id;
			}else{
				unset($narrow_ary[$key]);
			}
			$result=$query_string.(count($narrow_ary)?'&Narrow='.implode('+', $narrow_ary):'');
		}else{
			$result=$query_string.'&Narrow='.$id;
		}
		return $result;
	}
	
	public static function get_narrow_pro_count($narrow_ary=array(), $id, $CateId=0){
		global $c;
		$arr=array();
		$v_ary=array();
		$key=array_search($id, $narrow_ary);
		if($key===false){ $narrow_ary[]=$id; }
		foreach((array)$narrow_ary as $k=>$v){ $arr[]=(int)$v; }
		sort($arr);
		$narrow_where='';
		if($CateId){
			$UId=category::get_UId_by_CateId($CateId);
			$narrow_where.=" and (CateId in(select CateId from products_category where UId like '{$UId}%') or CateId='{$CateId}' or ".category::get_search_where_by_ExtCateId($CateId, 'products_category').')';
		}
		if($arr){
			if(ly200::is_mobile_client(1)==1){
				$attr_ary=$value_ary=array();
				$attr_str=implode(',', $arr);
				$value_row=db::get_all('products_selected_attribute', 'IsUsed=1 and (VId in ('.$attr_str.'))', 'ProId, VId', 'VId asc');
				foreach((array)$value_row as $v){
					if(!in_array($v['VId'], $value_ary[$v['ProId']][$v['AttrId']])){
						$value_ary[$v['ProId']][$v['AttrId']][]=$v['VId'];
					}
					if(!in_array($v['VId'], $attr_ary[$v['AttrId']])){
						$attr_ary[$v['AttrId']][]=$v['VId'];
					}
				}
				$attr_len=count($attr_ary);
				$narrow_where.=' and ProId in (0';
				foreach((array)$value_ary as $k=>$v){
					$count=0;
					foreach($attr_ary as $k2=>$v2){
						foreach($v2 as $k3=>$v3){
							if(in_array($v3, $v[$k2])){
								$count+=1;
								break;
							}
						}
					}
					if($count>=$attr_len){
						$narrow_where.=",{$k}";
					}
				}
				$narrow_where.=')';
			}else{
				$attr_ary=$value_ary=array();
				$attr_str=implode(',', $arr);
				$value_row=db::get_all('products_selected_attribute', 'IsUsed=1 and (VId in ('.$attr_str.'))', 'ProId, VId', 'VId asc');
				foreach((array)$value_row as $v){
					if(!$value_ary[$v['ProId']]){
						$value_ary[$v['ProId']]=$v['VId'];
					}else{
						$value_ary[$v['ProId']].=','.$v['VId'];
					}
				}
				$attr_str='';
				foreach((array)$arr as $k=>$v){ $attr_str.=($k?',':'').$v; }
				$i=0;
				$narrow_where.=' and ProId in (0';
				foreach((array)$value_ary as $k=>$v){
					if($attr_str==$v){
						$narrow_where.=",{$k}";
						++$i;
					}
				}
				$narrow_where.=')';
			}
		}
		unset($v_ary, $arr);
		$result=(int)db::get_row_count('products', '1 '.$c['where']['products'].$narrow_where);
		return $result;
	}
	
	public static function get_web_position($row, $table, $lang='_en', $char=' &gt; ', $length=0){
		global $c;
		$str='';
		$lang=$c['lang']?$c['lang']:$lang;
		$UId=trim($row['UId'], ',');
		$name=$row['Category'.$lang];
		$length && $name=str::str_echo($name, $length, 0, '..');
		if($UId=='0'){
			$str.=$char."<a href='".ly200::get_url($row, $table)."'>{$name}</a>";  
		}
		if($UId){
			$all_row=str::str_code(db::get_all($table, "CateId in($UId)", '*', 'Dept asc'));	
			$i=0;
			foreach((array)$all_row as $v){
				$ext_name=$v['Category'.$lang];
				$length && $ext_name=str::str_echo($ext_name, $length, 0, '..');
				$str.=($i==0?$char:'')."<a href='".ly200::get_url($v, $table)."'>{$ext_name}</a>".$char;
				$i+=1;
			}
			$str.="<a href='".ly200::get_url($row, $table)."'>{$name}</a>";
		}
		return $str;
	}
	
	public static function seo_meta($row='', $spare_row=''){
		global $c;
		$lang=$c['lang'];
		$home_row=str::str_code(db::get_one('meta', "Type='home'"));
		$SeoTitle=htmlspecialchars_decode($row['SeoTitle'.$lang]?$row['SeoTitle'.$lang]:$spare_row['SeoTitle']);
		$SeoKeywords=htmlspecialchars_decode($row['SeoKeyword'.$lang]?$row['SeoKeyword'.$lang]:$spare_row['SeoKeyword']);
		$SeoDescription=htmlspecialchars_decode($row['SeoDescription'.$lang]?$row['SeoDescription'.$lang]:$spare_row['SeoDescription']);
		if(!$SeoTitle && !$SeoKeywords && !$SeoDescription){
			$SeoTitle=htmlspecialchars_decode($home_row['SeoTitle'.$lang]?$home_row['SeoTitle'.$lang]:$c['config']['global']['SiteName']);
			$SeoKeywords=htmlspecialchars_decode($home_row['SeoKeyword'.$lang]?$home_row['SeoKeyword'.$lang]:$c['config']['global']['SiteName']);
			$SeoDescription=htmlspecialchars_decode($home_row['SeoDescription'.$lang]?$home_row['SeoDescription'.$lang]:$c['config']['global']['SiteName']);
		}
		
		$str='';
		$where='IsUsed=1 and IsMeta=1';
		$where.=(ly200::is_mobile_client(1)?' and CodeType in(0,2)':' and CodeType in(0,1)');
		$third_row=db::get_all('third', $where, '*', $c['my_order'].'TId desc');
		foreach((array)$third_row as $v) $str.=$v['Code'];
		return "{$str}<link rel='shortcut icon' href='{$c['config']['global']['IcoPath']}' />\r\n<meta name=\"keywords\" content=\"$SeoKeywords\" />\r\n<meta name=\"description\" content=\"$SeoDescription\" />\r\n<title>$SeoTitle</title>\r\n{$copyCode}";
	}
	
	public static function get_size_img($filepath, $size){
		global $c;
		$result=$filepath;
		$ext_name=file::get_ext_name($filepath);
		if(is_file($c['root_path']."{$filepath}.{$size}.{$ext_name}")){
			$result="{$filepath}.{$size}.{$ext_name}";
		}
		return $result;
	}
	
	public static function set_products_history($row, $CurPrice, $OldPrice){
		global $c;
		$time=$c['time']+3600*24*365;
		$max=15;
		$history_num=count($_COOKIE['history']);
		if($history_num==0){
			setcookie('history[0][ProId]', $row['ProId'], $time);
			setcookie('history[0][Name]', $row['Name'.$c['lang']], $time);
			setcookie('history[0][PicPath]', $row['PicPath_0'], $time);
			setcookie('history[0][Price]', $CurPrice, $time);
			setcookie('history[0][OldPrice]', $OldPrice, $time);
		}else{
			$i=0;
			foreach($_COOKIE['history'] as $k=>$v){
				if($history_num==$max && $i==0){
					setcookie("history[$k][ProId]", '');
					setcookie("history[$k][Name]", '');
					setcookie("history[$k][PicPath]", '');
					setcookie("history[$k][Price]", '');
					setcookie("history[$k][OldPrice]", '');
				}
				if($v['ProId']==$row['ProId']){
					setcookie("history[$k][ProId]", '');
					setcookie("history[$k][Name]", '');
					setcookie("history[$k][PicPath]", '');
					setcookie("history[$k][Price]", '');
					setcookie("history[$k][OldPrice]", '');
				}
				if($i==($history_num-1)) $num=$k+1;
				++$i;	
			}
			setcookie("history[$num][ProId]", $row['ProId'], $time);
			setcookie("history[$num][Name]", $row['Name'.$c['lang']], $time);
			setcookie("history[$num][PicPath]", $row['PicPath_0'], $time);
			setcookie("history[$num][Price]", $CurPrice, $time);
			setcookie("history[$num][OldPrice]", $OldPrice, $time);
		}
	}
	
	public static function get_products_package($ProId, $Type=0){
		global $c;
		$id_where='0';
		$pid_ary=$prod_ary=array();
		$row=db::get_all('sales_package', "ProId='$ProId' and Type=$Type");
		$row=array_merge($row, (array)db::get_all('sales_package', "ReverseAssociate=1 and PackageProId like '%|$ProId|%' and Type=$Type", '*', 'PId desc'));
		if(!$row) return false;
		foreach((array)$row as $v){
			$v['ReverseAssociate']==1 && $v['PackageProId']=str_replace("|{$ProId}|", "|{$v['ProId']}|", $v['PackageProId']);
			$pid_ary=@array_filter(@explode('|', $v['PackageProId']));
			if(!count($pid_ary)){
				return false;
			}
			$id_where.=','.@implode(',', $pid_ary);
		}
		$pro_row=db::get_all('products', "ProId in($id_where) and ((SoldOut=0 and IsSoldOut=0) or (IsSoldOut=1 and SStartTime<{$c['time']} and {$c['time']}<SEndTime))", "ProId, Name{$c['lang']}, IsPromotion, PromotionType, PromotionPrice, PromotionDiscount, StartTime, EndTime, Price_0, Price_1, PicPath_0, Wholesale, AttrId, Attr, ExtAttr, MOQ, Stock, SoldOut, IsSoldOut, SStartTime, SEndTime, IsCombination", 'ProId asc');
		foreach((array)$pro_row as $v){
			$prod_ary[$v['ProId']]=$v;
		}
		return array('data'=>$row, 'pro'=>$prod_ary);
	}
	
	public static function get_table_data_to_ary($table, $w, $key, $return_field='', $query_field=''){
		global $c;
		$data=array();
		$row=str::str_code(db::get_all($table, $w, $query_field?$query_field:($return_field?"{$key},{$return_field}":'*')));
		foreach($row as $v){
			$data[$v[$key]]=$return_field?$v[$return_field]:$v;
		}
		return $data;
	}
	
	public static function get_ip(){
		if($_SERVER['HTTP_X_FORWARDED_FOR']){
			$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}elseif($_SERVER['HTTP_CLIENT_IP']){
			$ip=$_SERVER['HTTP_CLIENT_IP'];
		}else{
			$ip=$_SERVER['REMOTE_ADDR'];
		}
		return preg_match('/^[\d]([\d\.]){5,13}[\d]$/', $ip)?$ip:'';
	}
	
	public static function get_server_ip(){
		if($_SERVER['SERVER_ADDR']){
			$server_ip = $_SERVER['SERVER_ADDR'];
		}else if(function_exists('getenv') && getenv('SERVER_NAME')){
			$server_ip = getenv('SERVER_ADDR');
		}else if($_SERVER['SERVER_NAME']){
			$server_ip = gethostbyname($_SERVER['SERVER_NAME']);
		}else if($_SERVER['HTTP_HOST']){
			$host = preg_replace('/(\:{1}\d+)$/', '', $_SERVER['HTTP_HOST']);
			$server_ip = gethostbyname($host);
		}else{
			$server_ip = '';
		}
		return preg_match('/^[\d]([\d\.]){5,13}[\d]$/', $server_ip)?$server_ip:'';
	}
	
	public static function ip($ip, $group=''){
		if(!$ip){return;}
		$iploca=new ip;
		@$iploca->init();
		@$iploca->getiplocation($ip);
		$area=array();
		$area['country']=str_replace(array('未知', 'CZ88.NET'), '', $iploca->get('country'));
		$area['area']=str_replace(array('未知', 'CZ88.NET'), '', $iploca->get('area'));
		$area['country']=='' && $area['country']='未知';
		$area['area']=='' && $area['area']='未知';
		if($group=='country'){
			return str::iconver($area['country'], 'GBK', 'UTF-8');
		}elseif($group=='area'){
			return str::iconver($area['area'], 'GBK', 'UTF-8');
		}
		return str::iconver(implode($area), 'GBK', 'UTF-8');
	}
	
	public static function password($password){
		$password=md5($password);
		$password=substr($password, 0, 5).substr($password, 10, 20).substr($password, -5).'www.ly200.com';
		return md5($password.$password);
	}
	
	public static function check_upfile($file){
		global $c;
		$basepath=implode('/', array_slice(explode('/', $file), 0, 3)).'/';
		if(@is_file($c['root_path'].$file) && substr_count($file, $basepath)){
			return $file;
		}
		return '';
	}
	
	public static function form_select($data, $name, $selected_value='', $field='', $key='', $index=0, $attr=''){
		$select="<select name='$name' $attr>".($index?"<option value=''>$index</option>":'');
		foreach($data as $k=>$v){
			$value=$key!=''?$v[$key]:$k;
			$selected=($selected_value!='' && $value==$selected_value)?'selected':'';
			$text=$field!=''?$v[$field]:$v;
			$select.="<option value='{$value}' $selected>{$text}</option>";
		}
		$select.='</select>';
		return $select;
	}
	
	public static function e_json($msg='', $ret=0, $exit=1){
		is_bool($ret) && $ret=$ret?1:0;
		echo str::json_data(array(
				'msg'	=>	$msg,
				'ret'	=>	$ret
			)
		);
		$exit && exit;
	}
	
	public static function load_static(){
		global $c;
		$static='';
		$refresh='?v=1.327';
		$args=func_get_args();
		foreach($args as $v){
			if(is_array($v)){
				$attr=$v[1];
				$v=$v[0];
			}
			if(!$v || @!is_file($c['root_path'].$v)){continue;}
			$ext_name=file::get_ext_name($v);
			
			if($ext_name=='css'){
				$static.="<link href='{$v}$refresh' rel='stylesheet' type='text/css' $attr />\r\n";
			}elseif($ext_name=='js'){
				$static.="<script type='text/javascript' src='{$v}$refresh' $attr></script>\r\n";
			}
		}
		return $static;
	}
	
	public static function load_cdn_contents($html=''){
		global $c;
		
		echo $html;
		exit;
	}
	
	public static function query_string($un=''){
		!is_array($un) && $un=explode(',', str_replace(' ','',$un));
		if($_SERVER['QUERY_STRING']){
			$q=@explode('&', $_SERVER['QUERY_STRING']);
			$v='';
			for($i=0; $i<count($q); $i++){
				$t=@explode('=', $q[$i]);
				if(in_array($t[0], $un)){continue;}
				$v.=$t[0].'='.$t[1].'&';
			}
			$v=substr($v, 0, -1);
			$v=='=' && $v='';
			return $v;
		}else{
			return '';
		}
	}

	public static function get_domain($protocol=1){
		return ($protocol==1?((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on')?'https://':'http://'):'').$_SERVER['HTTP_HOST'].(($_SERVER['SERVER_PORT']!=80 && $_SERVER['SERVER_PORT']!=443)?':'.$_SERVER['SERVER_PORT']:'');
	}
	
	public static function turn_page_html($row_count, $page, $total_pages, $query_string, $pre_page='<<', $next_page='>>', $base_page=3, $link_ext_str='.html', $html=1){
		if(!$row_count){return;}
		if($html==1){
			$page_string=ly200::get_url_dir($_SERVER['REQUEST_URI'], $link_ext_str);
			$page_string=str_replace('//', '/', $page_string);
			if($query_string!='' && $query_string!='?'){
				$query_string='?'.ly200::get_query_string($query_string);
			}
		}else{
			$page_string=$query_string;
			$link_ext_str=$query_string='';
		}
		$i_start=$page-$base_page>0?$page-$base_page:1;
		$i_end=$page+$base_page>=$total_pages?$total_pages:$page+$base_page;
		($total_pages-$page)<$base_page && $i_start=$i_start-($base_page-($total_pages-$page));
		$page<=$base_page && $i_end=$i_end+($base_page-$page+1);
		$i_start<1 && $i_start=1;
		$i_end>=$total_pages && $i_end=$total_pages;
		$turn_page_str='<ul>';
		$pre=$page-1>0?$page-1:1;
		$turn_page_str.=($page<=1)?"<li><font class='page_noclick'><em class='icon_page_prev'></em>$pre_page</font></li>":"<li><a href='{$page_string}{$pre}{$link_ext_str}{$query_string}' class='page_button'><em class='icon_page_prev'></em>$pre_page</a></li>";
		$i_start>1 && $turn_page_str.="<li><a href='{$page_string}1{$link_ext_str}{$query_string}' class='page_item'>1</a></li><li><font class='page_item'>...</font></li>";
		for($i=$i_start; $i<=$i_end; $i++){
			$turn_page_str.=$page!=$i?"<li><a href='{$page_string}{$i}{$link_ext_str}{$query_string}' class='page_item'>$i</a></li>":"<li><font class='page_item_current'>$i</font></li>";
		}
		$i_end<$total_pages && $turn_page_str.="<li><font class='page_item'>...</font></li><li><a href='{$page_string}{$total_pages}{$link_ext_str}{$query_string}' class='page_item'>$total_pages</a></li>";
		$next=$page+1>$total_pages?$total_pages:$page+1;
		if($page+1>$total_pages){
			$turn_page_str.="<li class='page_last'><font class='page_noclick'>$next_page<em class='icon_page_next'></em></font></li>";
		}else{
			$page>=$total_pages && $page--;
			$turn_page_str.="<li class='page_last'><a href='{$page_string}{$next}{$link_ext_str}{$query_string}' class='page_button'>$next_page<em class='icon_page_next'></em></a></li>";
		}
		$turn_page_str.='</ul>';
		return $turn_page_str;
	}
	
	public static function turn_page_small_html($row_count, $page, $total_pages, $query_string, $link_ext_str='.html', $html=1){
		if(!$row_count){return;}
		if($html==1){
			$page_string=ly200::get_url_dir($_SERVER['REQUEST_URI'], $link_ext_str);
			$page_string=str_replace('//', '/', $page_string);
			if($query_string!='' && $query_string!='?'){
				$query_string='?'.ly200::get_query_string($query_string);
			}
		}else{
			$page_string=$query_string;
			$link_ext_str=$query_string='';
		}
		$str="<div class='page'><div class='cur'>$page/$total_pages</div>";
		if($total_pages>1){
			$step=array(1, $total_pages>=200?2:0, $total_pages>=500?5:0, $total_pages>=1000?10:0, $total_pages>=2000?20:0, $total_pages>=5000?50:0);
			$str.='<ul>';
			for($i=max($step); $i<=$total_pages; $i+=max($step)){
				$str.="<li><a href='{$page_string}{$i}{$link_ext_str}{$query_string}'>$i/$total_pages</a></li>";
			}
			$str.='</ul>';
		}
		$str.='</div>';
		$pre=$page>1?$page-1:1;
		$next=$page+1>$total_pages?$total_pages:$page+1;
		$str.="<a href='{$page_string}{$pre}{$link_ext_str}{$query_string}' class='page_item pre'></a>";
		$str.="<a href='{$page_string}{$next}{$link_ext_str}{$query_string}' class='page_item next'></a>";
		return $str;
	}
	
	public static function turn_page_mobile_html($row_count, $page, $total_pages, $query_string, $base_page=3, $link_ext_str='.html', $html=1){
		if(!$row_count){return;}
		if($html==1){
			$page_string=ly200::get_url_dir($_SERVER['REQUEST_URI'], $link_ext_str);
			$page_string=str_replace('//', '/', $page_string);
			if($query_string!='' && $query_string!='?'){
				$query_string='?'.ly200::get_query_string($query_string);
			}
		}else{
			$page_string=$query_string;
			$link_ext_str=$query_string='';
		}
		$i_start=$page-$base_page>0?$page-$base_page:1;
		$i_end=$page+$base_page>=$total_pages?$total_pages:$page+$base_page;
		($total_pages-$page)<$base_page && $i_start=$i_start-($base_page-($total_pages-$page));
		$page<=$base_page && $i_end=$i_end+($base_page-$page+1);
		$i_start<1 && $i_start=1;
		$i_end>=$total_pages && $i_end=$total_pages;
		$turn_page_str='';
		$pre=$page-1>0?$page-1:1;
		$turn_page_str.="<a href='{$page_string}{$pre}{$link_ext_str}{$query_string}' class='btn prev'>&nbsp;</a>";
		$i_start>1 && $turn_page_str.="<a href='{$page_string}1{$link_ext_str}{$query_string}'>1</a><font>...</font>";
		for($i=$i_start; $i<=$i_end; $i++){
			$turn_page_str.=$page!=$i?"<a href='{$page_string}{$i}{$link_ext_str}{$query_string}'>$i</a>":"<font class='cur'>$i</font>";
		}
		$i_end<$total_pages && $turn_page_str.="<font>...</font><a href='{$page_string}{$total_pages}{$link_ext_str}{$query_string}'>$total_pages</a>";
		$next=$page+1>$total_pages?$total_pages:$page+1;
		$turn_page_str.="<a href='{$page_string}{$next}{$link_ext_str}{$query_string}' class='btn next'>&nbsp;</a>";
		return $turn_page_str;
	}
	
	public static function get_url_dir($REQUEST_URI, $ext_name){
		$url_ary=@explode('/', trim($REQUEST_URI, '/'));
		$url='/';
		foreach($url_ary as $k=>$v){
			if($k==count($url_ary)-1){
				$p=@explode('?', $v);
				$v=$p[0];
			}
			if(substr_count($v, $ext_name)){break;}
			$url.=$v.'/';
		}
		return $url;
	}
	
	public static function get_query_string($str){
		$query_ary=@explode('&', trim($str, '?'));
		$query_string='';
		foreach($query_ary as $k=>$v){
			$v=trim($v);
			if($v=='' || $v=='='){continue;}
			$query_string.="&{$v}";
		}
		return $query_string;
	}
	
	public static function turn_page($row_count, $page, $total_pages, $query_string, $pre_page='<<', $next_page='>>', $base_page=3, $link_ext_str=''){
		if(!$row_count){return;}
		$i_start=$page-$base_page>0?$page-$base_page:1;
		$i_end=$page+$base_page>=$total_pages?$total_pages:$page+$base_page;
		($total_pages-$page)<$base_page && $i_start=$i_start-($base_page-($total_pages-$page));
		$page<=$base_page && $i_end=$i_end+($base_page-$page+1);
		$i_start<1 && $i_start=1;
		$i_end>=$total_pages && $i_end=$total_pages;
		$turn_page_str='';
		$pre=$page-1>0?$page-1:1;
		$turn_page_str.=($page<=1)?"<font class='page_noclick'>$pre_page</font>&nbsp;":"<a href='{$query_string}{$pre}{$link_ext_str}' class='page_button'>$pre_page</a>&nbsp;";
		for($i=$i_start; $i<=$i_end; $i++){
			$turn_page_str.=$page!=$i?"<a href='{$query_string}{$i}{$link_ext_str}' class='page_item'>$i</a>&nbsp;":"<font class='page_item_current'>$i</font>&nbsp;";
		}
		$i_end<$total_pages && $turn_page_str.="<font class='page_item'>...</font>&nbsp;<a href='{$query_string}{$total_pages}{$link_ext_str}' class='page_item'>$total_pages</a>&nbsp;";
		$next=$page+1>$total_pages?$total_pages:$page+1;
		if($page+1>$total_pages){
			$turn_page_str.="<font class='page_noclick'>$next_page</font>";
		}else{
			$page>=$total_pages && $page--;
			$turn_page_str.="<a href='{$query_string}{$next}{$link_ext_str}' class='page_button'>$next_page</a>";
		}
		return $turn_page_str;
	}
	
	public static function turn_page_mobile($row_count, $page, $total_pages, $query_string, $pre_page='<<', $next_page='>>', $base_page=3, $link_ext_str=''){
		if(!$row_count){return;}
		$i_start=$page-$base_page>0?$page-$base_page:1;
		$i_end=$page+$base_page>=$total_pages?$total_pages:$page+$base_page;
		($total_pages-$page)<$base_page && $i_start=$i_start-($base_page-($total_pages-$page));
		$page<=$base_page && $i_end=$i_end+($base_page-$page+1);
		$i_start<1 && $i_start=1;
		$i_end>=$total_pages && $i_end=$total_pages;
		$turn_page_str='';
		$pre=$page-1>0?$page-1:1;
		$turn_page_str.=$page<=1?"<font class='page_noclick'>$pre_page</font>":"<a href='$query_string$pre$link_ext_str' class='page_button'>$pre_page</a>";
		$turn_page_str.="&nbsp;&nbsp;<span class='fc_red'>{$page}</span> / {$total_pages}&nbsp;&nbsp;";
		$next=$page+1>$total_pages?$total_pages:$page+1;
		$turn_page_str.=$page>=$total_pages?"<font class='page_noclick'>$next_page</font>":"<a href='$query_string$next$link_ext_str' class='page_button'>$next_page</a>";
		return $turn_page_str;
	}
	
	public static function set_custom_style(){
		global $c;
		$StyleData=(int)db::get_row_count('config_module', 'IsDefault=1')?db::get_value('config_module', 'IsDefault=1', 'StyleData'):db::get_value('config_module', "Themes='{$c['theme']}'", 'StyleData');
		$style_data=str::json_data($StyleData, 'decode');
		$style_result="<style type=\"text/css\">\r\n";
		foreach($style_data as $k=>$v){
			$isHover=substr_count($k, 'Hover')?1:0;
			if(substr_count($k, 'Bg')){
				$style_result.=".{$k}".($isHover?':hover':'')."{background-color:{$v};}\r\n";
				if($k=='DiscountBgColor') $style_result.=".DiscountBorderColor{border-color:{$v};}\r\n";
				if($k=='AddtoCartBgColor') $style_result.=".".str_replace('Bg', 'Border', $k)."{border-color:{$v};}\r\n";
			}elseif(substr_count($k, 'Border')){
				$style_result.=".{$k}".($isHover?':hover':'')."{border-color:{$v};}\r\n";
				if($k=='GoodBorderHoverColor') $style_result.=".GoodBorderColor.selected{border-color:{$v};}\r\n.GoodBorderBottomHoverColor{border-bottom-color:{$v};}\r\n";
			}elseif(substr_count($k, 'Font')){
				$style_result.=".{$k},a.{$k},a.{$k}:hover,a:hover{color:{$v};}\r\n";
				$style_result.=".".str_replace('Font', 'FontBg', $k)."{background-color:{$v};}\r\n";
				$style_result.=".".str_replace('Font', 'FontBorder', $k)."{border-color:{$v};}\r\n";
				$style_result.=".".str_replace('Font', 'FontBorderHover', $k).":hover, a.".str_replace('Font', 'FontBorderHover', $k).":hover{border-color:{$v}!important;}\r\n";
				$style_result.=".".str_replace('Font', 'FontBgHover', $k).":hover{background-color:{$v}!important;}\r\n";
				$style_result.=".".str_replace('Font', 'FontHover', $k).":hover{color:{$v}!important;}\r\n";
			}else{
				$style_result.=".{$k}{color:{$v};}\r\n";
			}
		}
		$style_result.='</style>';
		return $style_result;
	}
	
	public static function nav_style($row, $down=0){
		global $c, $nav_ary;
		if($row['Custom']){
			$url=$row['Url'];
			$name=$row['Name'.$c['lang']];
		}else{
			$url=$c['nav_cfg'][$row['Nav']]['url'];
			$name=$c['nav_cfg'][$row['Nav']]['name'.$c['lang']];
			if($row['Nav']==1){
				$name=str::str_code(db::get_value('article_category', "CateId='{$row['Page']}'", "Category{$c['lang']}"));
				$page_row=str::str_code(db::get_one('article', "CateId='{$row['Page']}'", "AId, CateId, Title{$c['lang']}, PageUrl, Url", $c['my_order']."AId asc"));
				$page_row && $url=ly200::get_url($page_row, 'article');
			}elseif($row['Nav']==2){
				$info_row=str::str_code(db::get_one('info_category', "CateId='{$row['Info']}'", "CateId, Category{$c['lang']}"));
				if($info_row){
					$name=$info_row['Category'.$c['lang']];
					$url=ly200::get_url($info_row, 'info_category');
				}
			}elseif($row['Nav']==3){
				if((int)$row['Cate']){
					$prod_row=str::str_code(db::get_one('products_category', "CateId='{$row['Cate']}'", "CateId, UId, Dept, SubCateCount, Category{$c['lang']}"));
					if($prod_row){
						if($down && $row['Down'] && $prod_row['Dept']<3){
							$select=1;
							$uid="{$prod_row['UId']}{$prod_row['CateId']},";
						}
						$name=$prod_row['Category'.$c['lang']];
						$url=ly200::get_url($prod_row, 'products_category');
					}
				}else{
					if($down && $row['Down']){
						$select=1;
						$uid='0,';
					}
				}
			}
		}
		$target=$row['NewTarget']?' target="_blank"':'';
		return array('Name'=>$name, 'Url'=>$url, 'Target'=>$target, 'Select'=>$select, 'UId'=>$uid);
	}
	
	public static function out_put_third_code(){
		global $c;
		$str="<script type='text/javascript' src='{$c['analytics']}?Number={$c['Number']}'></script>";
		($_GET['m']=='user' && $_GET['a']=='register') && $str='';
		$where='IsUsed=1 and IsMeta=0';
		$where.=(ly200::is_mobile_client(1)?' and CodeType in(0,2)':' and CodeType in(0,1)');
		$third_row=db::get_all('third', $where, '*', $c['my_order'].'TId desc');
		foreach((array)$third_row as $v) $str.=$v['Code'];
		return $str!=''?'<div align="center">'.$str.'</div>':'';
	}
	
	public static function partners($type='',$vam=''){
		global $c;
		$result='<div class="partners_box">';
		$partner_row=db::get_all('partners', "IsUsed=1", '*', $c['my_order']." PId asc");
		foreach($partner_row as $v){
			$name=$v['Name'.$c['lang']];
			$type && $result.="<div class='partners_item'>";
			$v['Url']!='' && $result.="<a href=\"{$v['Url']}\" title=\"{$name}\" target=\"_blank\">";
			if(is_file($c['root_path'].$v['PicPath'])){
				$result.="<img src=\"{$v['PicPath']}\" alt=\"{$name}\" />";
			}else{
				$result.=$name;
			}
			$v['Url']!='' && $result.="</a>";
			$vam && $result.="<em></em>";
			$type && $result.="</div>";
		}
		$result.='</div>';
		return $result;
	}
	
	public static function powered_by($type=0){
		global $c;
		if($type==0){
			return '';
		}elseif($type==2){
			return 'POWERED BY UEESHOP';
		}else{
			return '<a href="http://www.ueeshop.com" target="_blank">POWERED BY UEESHOP</a>';
		}
	}
	
	public static function sendmail($toEmail, $Msubject, $Mbody){
		global $c;

		$config_row=str::json_data(db::get_value('config', 'GroupId="email" and Variable="config"', 'Value'), 'decode');
		$data=array(
			'Action'		=>	'send_mail',
			'From'			=>	$config_row['FromEmail']?$config_row['FromEmail']:'noreply@ueeshop.com',
			'FromName'		=>	$config_row['FromName']?$config_row['FromName']:'noreply',
			'To'			=>	$toEmail,
			'Subject'		=>	$Msubject,
			'Body'			=>	$Mbody
		);
		if($config_row['SmtpHost'] && $config_row['SmtpPort'] && $config_row['SmtpUserName'] && $config_row['SmtpPassword']){
			$data['SmtpHost']=$config_row['SmtpHost'];
			$data['SmtpPort']=$config_row['SmtpPort'];
			$data['SmtpUserName']=$config_row['SmtpUserName'];
			$data['SmtpPassword']=$config_row['SmtpPassword'];
		}
		return ly200::api($data, $c['ApiKey'], $c['api_url']);
	}
	
	public static function sendsms($mobilephone, $sms, $TemplateId=1){
		global $c;
		$data=array(
			'Action'		=>	'send_sms',
			'Application'	=>	0,
			'TemplateId'	=>	$TemplateId,
			'MobilePhone'	=>	$mobilephone,
			'SmsContents'	=>	$sms
		);
		return ly200::api($data, $c['ApiKey'], $c['api_url']);
	}
	
	public static function api($data, $key, $url){
		global $c;
		$data['ApiKey']='ueeshop_web';
		$data['Domain']=$_SERVER['HTTP_HOST'];
		(int)$c['UeeshopAgentId'] && $data['AgentId']=(int)$c['UeeshopAgentId'];
		(int)$c['UeeshopQcloudUserId'] && $data['QcloudUserId']=(int)$c['UeeshopQcloudUserId'];
		$c['manage']['config']['ManageLanguage'] && $data['lang']=$c['manage']['config']['ManageLanguage'];
		$data['Number']=$c['Number'];
		$data['timestamp']=$c['time'];
		$data=str::str_code($data, 'trim');
		$data['sign']=ly200::sign($data, $key);
		$curl_opt=array(
			CURLOPT_CONNECTTIMEOUT	=>	10,
			CURLOPT_TIMEOUT			=>	10
		);
		for($i=0;$i<5;$i++){
			$result=ly200::curl($url, $data, '', $curl_opt);
			if($result) break;
		}
		if(!$result){
			$return['msg']='connection error';
			return $return;
		}else{
			$json_data=str::json_data($result, 'decode');
			if($json_data['ret']==1){
				return $json_data;
			}else{
				$return['msg']=$json_data['msg']?$json_data['msg']:$result;
				return $return;
			}
		}
	}
	
	public static function sign($data, $key){
		$str='';
		$data=str::str_code($data, 'trim');
		ksort($data);
		foreach($data as $k=>$v){
			if($k=='sign' || $v===''){continue;}
			$str.="$k=$v&";
		}
		return md5($str.'key='.$key);
	}
	
	public static function appkey($ApiName=''){
		global $c;
		$open_api=array('dianxiaomi', 'spdcat');
		$appkey=$c['ApiKey'];
		@in_array($ApiName, $open_api) && $appkey=db::get_value('config', "GroupId='API' and Variable='AppKey'", 'Value');
		return $appkey;
	}
	
	public static function curl($url='', $post='', $referer='', $curl_opt=array(), $return_cookie=false){
		$options=array(
			CURLOPT_URL				=>	$url,
			CURLOPT_RETURNTRANSFER	=>	true,
			CURLOPT_CONNECTTIMEOUT	=>	60,
			CURLOPT_TIMEOUT			=>	60,
			CURLOPT_POST			=>	$post?true:false,
			CURLOPT_SSL_VERIFYPEER	=>	false,
			CURLOPT_REFERER			=>	$referer
		);
		$post && $options[CURLOPT_POSTFIELDS]=is_array($post)?http_build_query($post):$post;
		$return_cookie && $options[CURLOPT_HEADER]=true;
		foreach((array)$curl_opt as $k=>$v){
			$options[$k]=$v;
		}
		$ch=curl_init();
		curl_setopt_array($ch, $options);
		$result=curl_exec($ch);
		$handle=curl_getinfo($ch);
		if($handle['http_code']!=200){return;}
		if($return_cookie){
			$raw_header=substr($result, 0, $handle['header_size']);
			$cookies=array();
			if(preg_match_all('/Set-Cookie:(?:\s*)([^=]*?)=([^\;]*?);/i', $raw_header, $cookie_match)){
				for($i=0; $i<count($cookie_match[0]); $i++){
					$cookies[$cookie_match[1][$i]]=$cookie_match[2][$i];
				}
			}
			curl_close($ch);
			return array(substr($result, -$handle['download_content_length']), $cookies);
		}
		curl_close($ch);
		return $result;
	}
	
	public static function ueeshop_web_get_data(){
		global $c;		
		$save_dir=$c['tmp_dir'].'manage/';
		$filename='analytics.json';
		$data=array('Action'=>'ueeshop_web_get_data');
		
		if(!file::check_cache($c['root_path'].$save_dir.$filename, $isThemes=0)){
			$result=ly200::api($data, $c['ApiKey'], $c['api_url']);
			if($result['ret']==1){
				$contents=str::json_data(str::str_code($result['msg'], 'stripslashes'));
				file::write_file($save_dir, $filename, $contents);
			}
		}
		
		$data_object=@file_get_contents($c['root_path'].$save_dir.$filename);
		return str::json_data($data_object, 'decode');
	}
	
	public static function check_user_id(){
		global $c;
		if(!(int)$_SESSION['User']['UserId']){
			@list($autologin_UserId, $autologin_Password)=@explode("\t", str::str_crypt(str::GetCookie('User'), 'decrypt'));
			if($autologin_UserId!='' && @strlen($autologin_Password)==32){
				$userinfo=db::get_one('user', "UserId='$autologin_UserId'");
				if(str::PwdCode($userinfo['Password'])==$autologin_Password){
					if(($c['FunVersion']>=1 && $c['config']['global']['UserStatus'] && $userinfo['Status']==1) || !$c['config']['global']['UserStatus']){
						$_SESSION['User']=$userinfo;
						$UserId=$userinfo['UserId'];
						$ip=ly200::get_ip();
						db::update('user', "UserId='{$UserId}'", array('LastLoginTime'=>$c['time'], 'LastLoginIp'=>$ip, 'LoginTimes'=>$userinfo['LoginTimes']+1));
						cart::login_update_cart();
						unset($_SESSION['Cart']['ShippingAddress']);
						user::operation_log($UserId, '会员登录(Cookie登录)', 1);
					}else{
						unset($_SESSION['User']);
						str::SetTheCookie('User');
					}
				}else{
					unset($_SESSION['User']);
					str::SetTheCookie('User');
				}
			}
		}
	}
	
	public static function set_session_id(){
		global $c;
		$session_id='';
		if(!(int)$_SESSION['User']['UserId']){
			$session_id=substr(md5(md5(session_id())), 0, 10);
			$time=$c['time']+3600*24*31;
			if($_COOKIE['session_id']){
				$session_id=$_COOKIE['session_id'];
			}else{
				setcookie('session_id', $session_id, $time);
			}
		}
		return $session_id;
	}
	
	public static function is_mobile_client($type=0){
		global $c;
		$_SESSION['Ueeshop']['IsMobileClient']=0;
		if(((int)$c['FunVersion']!=100 && $c['config']['global']['IsMobile']==0) || (!(int)$c['FunVersion'] && $c['NewFunVersion']>=1)) return $_SESSION['Ueeshop']['IsMobileClient'];
		if($type && ((int)substr_count(ly200::get_domain(), '://m.') || preg_match('/^m\.(.*)/', $_SERVER['HTTP_HOST'], $host_match))){
			$_SESSION['Ueeshop']['IsMobileClient']=1;
		}else{
			if(@stripos($_SERVER['HTTP_USER_AGENT'], 'ipad')){
				$_SESSION['Ueeshop']['IsMobileClient']=0;
			}else{
				$phone_client_agent_array=array('240x320','acer','acoon','acs-','abacho','ahong','airness','alcatel','amoi','android','anywhereyougo.com','applewebkit/525','applewebkit/532','asus','audio','au-mic','avantogo','becker','benq','bilbo','bird','blackberry','blazer','bleu','cdm-','compal','coolpad','danger','dbtel','dopod','elaine','eric','etouch','fly ','fly_','fly-','go.web','goodaccess','gradiente','grundig','haier','hedy','hitachi','htc','huawei','hutchison','inno','ipaq','ipod','jbrowser','kddi','kgt','kwc','lenovo','lg ','lg2','lg3','lg4','lg5','lg7','lg8','lg9','lg-','lge-','lge9','longcos','maemo','mercator','meridian','micromax','midp','mini','mitsu','mmm','mmp','mobi','mot-','moto','nec-','netfront','newgen','nexian','nf-browser','nintendo','nitro','nokia','nook','novarra','obigo','palm','panasonic','pantech','philips','phone','pg-','playstation','pocket','pt-','qc-','qtek','rover','sagem','sama','samu','sanyo','samsung','sch-','scooter','sec-','sendo','sgh-','sharp','siemens','sie-','softbank','sony','spice','sprint','spv','symbian','tablet','talkabout','tcl-','teleca','telit','tianyu','tim-','toshiba','tsm','up.browser','utec','utstar','verykool','virgin','vk-','voda','voxtel','vx','wap','wellco','wig browser','wii','windows ce','wireless','xda','xde','zte','mobile');
				foreach($phone_client_agent_array as $v){
					if(@stripos($_SERVER['HTTP_USER_AGENT'], $v)){
						$_SESSION['Ueeshop']['IsMobileClient']=1;
						break;	
					}
				}
				unset($phone_client_agent_array);
			}
		}
		return $_SESSION['Ueeshop']['IsMobileClient'];
	}
		
	public static function lock_china_ip(){
		global $c;
		if($_SESSION['Manage']['UserName']){return false;}
		if((int)$c['config']['global']['IsIP'] && $_SESSION['Ueeshop']['Ip']!='' && $_SESSION['Ueeshop']['Ip']==ly200::get_ip() && (int)$_SESSION['Ueeshop']['LockChinaIp']){return true;}
		$_SESSION['Ueeshop']['Ip']=ly200::get_ip();
		$_SESSION['Ueeshop']['LockChinaIp']=0;
		if((int)$c['config']['global']['IsIP']){
			$ChinaProvince="中国/北京/浙江/天津/安徽/上海/福建/重庆/江西/山东/河南/内蒙古/湖北/新疆维吾尔/湖南/宁夏回族/广东/西藏/海南/广西壮族/四川/河北/贵州/山西/云南/辽宁/陕西/吉林/甘肃/黑龙江/青海/江苏";
			$IpArea=ly200::ip(ly200::get_ip());
			if(substr_count($_SERVER['PHP_SELF'], '/manage/')==0 && substr_count($ChinaProvince, substr($IpArea, 0, 6))>0){
				$_SESSION['Ueeshop']['LockChinaIp']=1;
				return true;
			}
		}
		return false;
	}

	public static function lock_china_browser(){
		global $c;
		if($_SESSION['Manage']['UserName']){return false;}
		
		if((int)$c['config']['global']['IsChineseBrowser'] && preg_match("/zh-cn/i", strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5)))){
			return true;
		}
		return false;
	}
	
	public static function lock_close_website(){
		global $c;
		if($_SESSION['Manage']['UserName']){return false;}
		
		if((int)$c['config']['global']['IsCloseWeb']){
			return true;
		}
		return false;
	}
	
	public static function subdomain_list(){
		global $c;
		$keys=array_keys($c['lang_name']);
		$pre_domain=array('www', 'm');
		return array_merge($pre_domain, $keys);
	}
	
	public static function get_http_browser(){
		$agent=$_SERVER['HTTP_USER_AGENT'];
		$browser_ary=array();
		$agent_ary=explode(' ', $agent);
		foreach($agent_ary as $k=>$v){
			if(strpos($v, '/')){
				$v=explode('/', $v);
				$browser_ary[$v[0]]=$v[1];
			}
		}
		unset($agent_ary);
		
		$return=array('browser'=>'unknown', 'version'=>'0');
		if(strpos($agent, 'MSIE')!==false){
			$return['browser']='ie';
			if(strpos($agent, 'rv:11.0')) $return['version']='11';
			elseif(strpos($agent, 'MSIE 10.0')) $return['version']='10';
			elseif(strpos($agent, 'MSIE 9.0')) $return['version']='9';
			elseif(strpos($agent, 'MSIE 8.0')) $return['version']='8';
			elseif(strpos($agent, 'MSIE 7.0')) $return['version']='7';
			elseif(strpos($agent, 'MSIE 6.0')) $return['version']='6';
		}elseif(strpos($agent, 'Firefox')!==false){
			$return=array('browser'=>'firefox', 'version'=>$browser_ary['Firefox']);
		}elseif(strpos($agent, 'Chrome')!==false){
			$return=array('browser'=>'chrome', 'version'=>$browser_ary['Chrome']);
		}elseif(strpos($agent, 'Opera')!==false){
			$return=array('browser'=>'opera', 'version'=>$browser_ary['Opera']);
		}elseif(strpos($agent, 'Chrome')==false && strpos($agent, 'Safari')!==false){
			$return=array('browser'=>'safari', 'version'=>$browser_ary['Safari']);
		}
		return $return;
	}
	
	public static function product_effects($effects, $num, $row, $content, $length=4, $notClear=0){
		global $c;
		$is_promition=($row['IsPromotion'] && $row['StartTime']<$c['time'] && $c['time']<$row['EndTime'])?1:0;
		$url=ly200::get_url($row, 'products');
		$img=ly200::get_size_img($row['PicPath_0'], '240x240');
		$imgTo=ly200::get_size_img($row['PicPath_1'], '240x240');
		$name=$row['Name'.$c['lang']];
		$price_ary=cart::range_price_ext($row);
		$share_data=htmlspecialchars(str::json_data(array('title'=>$name, 'url'=>ly200::get_domain().$url)));
		
		$html='<div class="prod_box prod_box_'.$effects.' fl'.($num%$length==0?' first':'').'">';
			$html.='
				<div class="prod_box_pic">
					<a class="pic_box" href="'.$url.'" title="'.$name.'">
						<img'.($effects==6?' class="thumb"':'').' src="'.$img.'" /><span></span>'.($effects==4?'<span class="icon_eyes"></span>':'').(($effects==6 && $imgTo)?'<em class="thumb_hover"><img src="'.$imgTo.'" /><span></span></em>':'').'
					</a>
					'.($is_promition?'<em class="icon_discount DiscountBgColor"><b>'.@intval(sprintf('%01.2f', ($row['Price_1']-$price_ary[0])/$row['Price_1']*100)).'</b>%<br />OFF</em><em class="icon_discount_foot DiscountBorderColor"></em>':'').'
					<em class="icon_seckill DiscountBgColor">'.$c['lang_pack']['products']['sale'].'</em>
				</div>
			';
			$html.='<div class="prod_box_info">';
				$html.='<div class="prod_box_inner">'.$content.'</div>';
				if($effects==1){
					$html.='<div class="prod_box_view">
						<div class="prod_box_button">
							<div class="addtocart fr"><a href="javascript:;" rel="nofollow" class="add_cart" data="'.$row['ProId'].'">'.$c['lang_pack']['products']['addToCart'].'</a></div>
							'.($c['config']['products_show']['Config']['favorite']?'<div class="wishlist fl"><a href="javascript:;" rel="nofollow" class="add_favorite" data="'.$row['ProId'].'"></a></div>':'').'
							<div class="compare fl"><a href="javascript:;" rel="nofollow" class="share_this" data="'.$share_data.'"></a></div>
						</div>
					</div>';
				}elseif($effects==2){
					$html.='<div class="add_cart_box"><div class="add_cart_bg ProListBgColor"></div><a href="javascript:;" rel="nofollow" class="add_cart" data="'.$row['ProId'].'">'.$c['lang_pack']['products']['addToCart'].'</a></div>';
				}elseif($effects==3){
					$html.='<div class="button_group">
						<div class="addtocart fl"><a href="javascript:;" rel="nofollow" class="add_cart ProListBgColor" data="'.$row['ProId'].'">'.$c['lang_pack']['products']['addToCart'].'</a></div>
						'.($c['config']['products_show']['Config']['favorite']?'<div class="wishlist fr"><a href="javascript:;" rel="nofollow" class="add_favorite" data="'.$row['ProId'].'"></a></div>':'').'
					</div>';
				}elseif($effects==4){
					$html.='<div class="prod_action">
						'.($c['config']['products_show']['Config']['favorite']?'<div class="wishlist fl"><a href="javascript:;" rel="nofollow" class="add_favorite" data="'.$row['ProId'].'"></a></div>':'').'
						<div class="addtocart fl"><a href="javascript:;" rel="nofollow" class="add_cart" data="'.$row['ProId'].'">'.$c['lang_pack']['products']['addToCart'].'</a></div>
						<div class="compare fr"><a href="javascript:;" rel="nofollow" class="share_this" data="'.$share_data.'"></a></div>
					</div>';
				}
			$html.='</div>';
		$html.='</div>';
		if(!$notClear && (($num+1)%$length)==0){ $html.='<div class="clear"></div>'; }
		return $html;
	}
	
	public static function system_email_tpl($txt, $data=array()){
		if ($txt==''){return '';}
		global $c;
		if($c['config']['global']['LogoPath']){
			$LogoPath=$c['config']['global']['LogoPath'];
		}else{
			$LogoPath=db::get_value('config', "GroupId='global' and Variable='LogoPath'", 'Value');
		}
		$EmailStr = $data['EmailStr'];
		$PasswordStr = $data['PasswordStr'];
		$orders_row = (array)$data['orders_row'];
		$LogoPath= '<img src="'.ly200::get_domain().$LogoPath.'" style="max-width:350px;" border="0" />';
		$Domain = ly200::get_domain(0);
		$FullDomain = ly200::get_domain();
		$time = date('m/d/Y H:i:s', $c['time']);
		$Email = $orders_row?$orders_row['Email']:($EmailStr?$EmailStr:$_SESSION['User']['Email']);
		$Password = $PasswordStr?$PasswordStr:'********';
		$UserName = $orders_row?($orders_row['ShippingFirstName'].' '.$orders_row['ShippingLastName']):($_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName']);
		$UserName = trim($UserName);
		$UserName = htmlspecialchars($UserName?$UserName:$Email);
		$Global_ary = array('{Logo}', '{Domain}', '{FullDomain}', '{Time}', '{UserName}', '{Email}', '{Password}');
		$Global_val = array($LogoPath, $Domain, $FullDomain, $time, $UserName, $Email, $Password);
		$txt = str_replace($Global_ary, $Global_val, $txt);
		
		$backUrl = $data['backUrl'];
		if ($backUrl){
			$txt = str_replace('{VerUrl}', $backUrl, $txt);
		}
		$ForgotUrl = $data['ForgotUrl'];
		if ($ForgotUrl){
			$txt = str_replace('{ForgotUrl}', $ForgotUrl, $txt);
		}
		$isFee = (int)$data['isFee'];
		if ($orders_row['OId']){
			$OrderNum = $OId = $orders_row['OId'];
			$total_price = orders::orders_price($orders_row, $isFee);
			if (strstr($txt, '{OrderDetail}')){
				ob_start();
				$default_lang=$c['manage']?$c['manage']['config']['LanguageDefault']:$c['config']['global']['LanguageDefault'];
				$c['lang_pack_email']=include($c['root_path'].'/static/static/inc/mail/lang/'.($c['lang']?substr($c['lang'], 1):$default_lang).'.php');
				include($c['root_path'].'/static/static/inc/mail/order_detail.php');
				$OrderDetail=ob_get_contents();
				ob_end_clean();
			}else{
				$OrderDetail='';
			}
			$OrderUrl = ly200::get_domain().'/account/orders/view'.$orders_row['OId'].'.html';
			$OrderStatus = $c['orders']['status'][$orders_row['OrderStatus']];
			$OrderPrice = $orders_row['Currency'].' '.cart::iconv_price(0, 1, $orders_row['Currency']).$total_price;
			$orders_ary = array('{OrderNum}', '{OrderDetail}', '{OrderUrl}', '{OrderStatus}', '{OrderPrice}');
			$orders_val = array($OrderNum, $OrderDetail, $OrderUrl, $OrderStatus, $OrderPrice);
			$txt = str_replace($orders_ary, $orders_val, $txt);
			unset($orders_ary, $orders_val, $OrderDetail);
			
			$OrderPaymentUrl = ly200::get_domain()."/cart/complete/{$orders_row['OId']}.html";
			$OrderPaymentName = $orders_row['PaymentMethod'];
			$orders_ary = array('{OrderPaymentUrl}', '{OrderPaymentName}');
			$orders_val = array($OrderPaymentUrl, $OrderPaymentName);
			$txt = str_replace($orders_ary, $orders_val, $txt);
			unset($orders_ary, $orders_val);
			
			$shipping_cfg = $data['shipping_cfg'];
			$shipping_row = $data['shipping_row'];
			$ShippingName = (int)$orders_row['ShippingMethodSId']?$shipping_cfg['Express']:($orders_row['ShippingMethodType']=='air'?$shipping_cfg['AirName']:$shipping_cfg['OceanName']);
			$ShippingBrief = $shipping_row['Brief'];;
			$TrackingNumber = ($data['trackingNumberStr']?$data['trackingNumberStr']:$orders_row['TrackingNumber']);
			$ShippingTime = @date('m/d-Y', $data['ShippingTimeStr']?$data['ShippingTimeStr']:$orders_row['ShippingTime']);
			$QueryUrl = $shipping_cfg['Query'];
			$orders_ary = array('{ShippingName}', '{ShippingBrief}', '{TrackingNumber}', '{ShippingTime}', '{QueryUrl}');
			$orders_val = array($ShippingName, $ShippingBrief, $TrackingNumber, $ShippingTime, $QueryUrl);
			$txt = str_replace($orders_ary, $orders_val, $txt);
			unset($orders_ary, $orders_val);
		}
		return $txt;
	}
	
	public static function search_logs($count){
		global $c;
		$keyword=$_GET['Keyword'];
		if($_SESSION['Search']['Keyword']==$keyword || !trim($keyword)) return;
		$keyword_row=db::get_one('search_logs',"Keyword='$keyword'");
		$selt_ip=ly200::get_ip();
		$selt_country=ly200::ip($selt_ip,'country');
		if($keyword_row){
			$country_ary=explode('|',$keyword_row['Country']);
			$country_ary=array_filter($country_ary);
			if(!in_array($selt_country,$country_ary)) $country_ary[]=$selt_country;
			$country_ary='|'.implode('|',$country_ary).'|';
			db::update('search_logs',"Keyword='$keyword'",array(
				'Result'	=>	(int)$count,
				'Number'	=>	$keyword_row['Number']+1,
				'Country'	=>	$country_ary,
				'AccTime'	=>	$c['time']
			));
		}else{
			db::insert('search_logs',array(
				'Keyword'	=>	$keyword,
				'Result'	=>	(int)$count,
				'Number'	=>	1,
				'Country'	=>	"|$selt_country|",
				'AccTime'	=>	$c['time']
			));
		}
		$_SESSION['Search']['Keyword']=$keyword;
	}
}
?>