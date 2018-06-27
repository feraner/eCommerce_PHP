<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class manage{
	public static function language($html){
		global $c;
		$replace=array();
		preg_match_all("/{\/(.*)\/}/isU", $html, $lang_ary);
		foreach($lang_ary[1] as $v){
			$replace[0][]="{/$v/}";
			$replace[1][]=manage::get_language($v);
		}
		return str_replace($replace[0], $replace[1], $html);
	}
	
	public static function get_language($language){
		global $c;
		return @eval('return $c[\'manage\'][\'lang_pack\'][\''.str_replace('.', '\'][\'', $language).'\'];');
	}
	
	public static function check_upfile($file){
		!substr_count($file, '/u_file') && $file='';
		return $file;
	}
	
	public static function check_tmp($file){
		!substr_count($file, '/tmp') && $file='';
		return $file;
	}
	
	public static function config_operaction($cfg, $global){
		global $c;
		foreach($cfg as $key=>$value){
			$where="GroupId='$global' and Variable='$key'";
			if(db::get_row_count('config', $where)){
				db::update('config', $where, array('Value'=>$value));
			}else{
				db::insert('config', array(
						'GroupId'	=>	$global,
						'Variable'	=>	$key,
						'Value'		=>	$value
					)
				);
			}
		}
	}
	
	public static function operation_log($Logs){
		global $c;
		if($_SESSION['Manage']['UserId']==-1){return;}
		$data='';
		if($_GET){
			$get_data=@array_filter($_GET);
			foreach($get_data as $k=>$v){
				substr_count(strtolower($k), 'password') && $get_data[$k]='<font color=red>removed</font>';
			}
			$data.='GET='.addslashes(str::json_data(str::str_code($get_data, 'stripslashes')));
		}
		if($_POST){
			$post_data=@array_filter($_POST);
			foreach($post_data as $k=>$v){
				substr_count(strtolower($k), 'password') && $post_data[$k]='<font color=red>removed</font>';
			}
			$data.=($data?"\n":'').'POST='.addslashes(str::json_data(str::str_code($post_data, 'stripslashes')));
		}
		$do_action_ary=@explode('.', isset($_POST['do_action'])?$_POST['do_action']:$_GET['do_action']);
		db::insert('manage_operation_log', array(
				'UserId'	=>	$_SESSION['Manage']['UserId'],
				'UserName'	=>	addslashes($_SESSION['Manage']['UserName']),
				'Module'	=>	array_shift($do_action_ary),
				'Ip'		=>	ly200::get_ip(),
				'Log'		=>	addslashes($Logs),
				'Data'		=>	$data,
				'AccTime'	=>	$c['time']
			)
		);
	}
	
	public static function email_log($Email, $Subject, $Body){
		global $c;
		if($_SESSION['Manage']['UserId']==-1){return;}
		$time=$c['time'];
		foreach($Email as $k=>$v){
			db::insert('email_log', array(
					'Email'		=>	$v,
					'Subject'	=>	addslashes($Subject),
					'Body'		=>	addslashes($Body),
					'AccTime'	=>	$time
				)
			);
		}
	}
	
	public static function time_between($StartTime, $EndTime){
		if(date('H:i:s', $StartTime)=='00:00:00' && date('H:i:s', $EndTime)=='00:00:00'){
			$format='Y-m-d';
			$separator=' ~ ';
		}elseif(date('s', $StartTime)=='00' && date('s', $EndTime)=='00'){
			$format='Y-m-d H:i';
			$separator='<br>~<br>';
		}else{
			$format='Y-m-d H:i:s';
			$separator='<br>~<br>';
		}
		return date($format, $StartTime).$separator.date($format, $EndTime);
	}
	
	public static function iconv_price($price, $method=0, $currency=''){
		global $c;
		$currency_row=array();
		$currency!='' && $currency_row=db::get_one('currency', "Currency='{$currency}'");
		!$currency_row && $currency_row=$_SESSION['Manage']['Currency'];
		$rate=(float)$currency_row['Rate'];
		$Symbol=$currency_row['Symbol'];
		if($method==0){
			return $Symbol.sprintf('%01.2f', $price*$rate);
		}elseif($method==1){
			return $Symbol;
		}else{
			return sprintf('%01.2f', $price*$rate);
		}
	}
	
	public static function range_price($row, $method=0){
		global $c;
		$CurPrice=$row['Price_1'];
		$is_wholesale=($row['Wholesale'] && $row['Wholesale']!='[]');
		if($is_wholesale){
			$wholesale_price=str::json_data(htmlspecialchars_decode($row['Wholesale']), 'decode');
			foreach($wholesale_price as $k=>$v){
				if($row['MOQ']<$k) break;
				$CurPrice=(float)$v;
			}
			$maxPrice=reset($wholesale_price);
			$minPrice=end($wholesale_price);
		}
		if($row['IsPromotion'] && $row['StartTime']<$c['time'] && $c['time']<$row['EndTime']){
			if($row['PromotionType']){
				$CurPrice=$row['Price_1']*($row['PromotionDiscount']/100);
			}else $CurPrice=$row['PromotionPrice'];
		}
		if($is_wholesale){
			$CurPrice>$maxPrice && $maxPrice=$CurPrice;
			$CurPrice<$minPrice && $minPrice=$CurPrice;
		}
		if($is_wholesale && !$method){
			return $c['manage']['currency_symbol'].cart::iconv_price($minPrice, 2).' - '.cart::iconv_price($maxPrice, 2);
		}elseif($is_wholesale && $method){
			return manage::iconv_price($minPrice);
		}elseif(!$is_wholesale || $method){
			return manage::iconv_price($CurPrice);
		}
	}
	
	public static function form_edit($row, $type='text', $name, $size=0, $max=0, $attr=''){
		global $c;
		$result='';
		foreach($c['manage']['config']['Language'] as $k=>$v){
			if(substr($name, -2, 2)=='[]'){
				$field_name=substr($name, 0, -2).'_'.$v;
				$value=isset($row[$field_name])?$row[$field_name]:'';
				$field_name.='[]';
			}else{
				$field_name=$name.'_'.$v;
				$value=isset($row[$field_name])?$row[$field_name]:'';
			}
			$k!=0 && $result.='<div class="blank6"></div>';
			if($type=='text'){
				$value=htmlspecialchars(htmlspecialchars_decode($value), ENT_QUOTES);
				$result.="<span class='price_input lang_input'><b>{/language.$v/}<div class='arrow'><em></em><i></i></div></b><input type='text' name='$field_name' value='$value' class='form_input' size='$size' maxlength='$max' $attr></span>";
				$attr=='notnull' && $result.=' <font class="fc_red">*</font>';
			}elseif($type=='textarea'){
				$result.="<span class='price_input lang_input price_textarea'><b>{/language.$v/}<div class='arrow'><em></em><i></i></div></b><textarea name='$field_name' $attr>$value</textarea></span>";
			}else{
				$result.="<div class='fl'>{/language.$v/}</div>".manage::Editor($field_name, $value);
			}
		}
		return $result;
	}
	
	public static function database_language_operation($table, $where, $input_field){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$column_ary=db::get_table_fields($table, 1);
		$data=array();
		foreach($c['manage']['config']['Language'] as $k=>$v){
			foreach($input_field as $k2=>$v2){
				$field_name=$k2.'_'.$v;
				$data[$field_name]=${'p_'.$field_name};
				if(!in_array($field_name, $column_ary)){
					$f=$c['manage']['field_ext'][$v2];
					db::query("alter table {$table} add {$field_name} {$f} after {$k2}_en");
				}
			}
		}
		db::update($table, $where, $data);
	}
	
	public static function turn_on_language_database_operation($p_LanguageDefault, $p_Language){
		global $c;
		$langs=$c['manage']['config']['Language'];
		$diff=@array_diff($p_Language, $langs);
		$default=@in_array($p_LanguageDefault, $diff)?$c['manage']['config']['LanguageDefault']:$p_LanguageDefault;
		if(!count($diff)) return;
		$tables=$c['manage']['table_lang_field'];
		foreach($tables as $tb=>$field){
			$column_ary=db::get_table_fields($tb, 1);
			$update_sql='';
			foreach($diff as $k=>$v){
				foreach($field as $k2=>$v2){
					$field_name=$k2.'_'.$v;
					if(!in_array($field_name, $column_ary)){
						$update_sql.=($update_sql!=''?',':'')."`$field_name`=`{$k2}_{$default}`";
						$f=$c['manage']['field_ext'][$v2];
						db::query("alter table {$tb} add {$field_name} {$f} after {$k2}_en");
					}elseif($tb=='products_attribute' &&  $k2=='Value'){
						$update_sql.=($update_sql!=''?',':'')."`$field_name`=`{$k2}_{$default}`";
					}
				}
			}
			$update_sql!='' && db::query("UPDATE $tb SET $update_sql");
		}
	}
	
	public static function Editor($name, $content='', $imgbank=true){
		global $c;
		$html='';
		if($name){
			$html .= "<textarea id='{$name}' name='{$name}'>".htmlspecialchars_decode($content)."</textarea>";
			$html .= '<script type="text/javascript">';
			$html .= "CKEDITOR.replace('{$name}', {'language':'".$c['manage']['config']['ManageLanguage']."'});";
			$html .= '</script>';
		}
		return $html;
	}
	
	public static function Editor_Simple($name, $content='', $imgbank=true){
		global $c;
		$html='';
		if($name){
			$html .= "<textarea id='{$name}' name='{$name}'>".htmlspecialchars_decode($content)."</textarea>";
			$html .= '<script type="text/javascript">';
			$html .= "CKEDITOR.replace('{$name}', {'toolbar':'simple', 'height':300, 'language':'".$c['manage']['config']['ManageLanguage']."'});";
			$html .= '</script>';
		}
		return $html;
	}
	
	public static function turn_page($row_count, $page, $total_pages, $query_string){
		if(!$row_count){return;}
		$str="<div class='page'><div class='cur'>$page/$total_pages</div>";
		if($total_pages>1){
			$step=array(1, $total_pages>=200?2:0, $total_pages>=500?5:0, $total_pages>=1000?10:0, $total_pages>=2000?20:0, $total_pages>=5000?50:0);
			$str.='<ul>';
			for($i=max($step); $i<=$total_pages; $i+=max($step)){
				$str.="<li><a href='$query_string$i'>$i/$total_pages</a></li>";
			}
			$str.='</ul>';
		}
		$str.='</div>';
		$pre=$page>1?$page-1:1;
		$next=$page+1>$total_pages?$total_pages:$page+1;
		$str.="<a href='$query_string$pre' class='page_item pre'></a>";
		$str.="<a href='$query_string$next' class='page_item next'></a>";
		return $str;
	}
	
	public static function update_permit($UserName){
		global $c;
		if(!$UserName){return;}
		$details_permit=array();
		foreach($c['manage']['permit'] as $v){
			foreach($v as $k1=>$v1){
				$DetailsPermit='';
				if(@in_array($k1, $c['manage']['permit_base'])) continue;
				$permit=(int)$_POST['Permit_'.$k1];
				if($v1){
					foreach((array)$v1 as $k2=>$v2){
						$details_permit[$k1][$k2][0]=(int)$_POST['Permit_'.$k1.'_'.$k2];
						if(isset($v2['menu'])){
							foreach((array)$v2['menu'] as $v3){
								$details_permit[$k1][$k2][1][$v3][0]=(int)$_POST['Permit_'.$k1.'_'.$k2.'_'.$v3];
								if($v2['permit'][$v3]){
									foreach((array)$v2['permit'][$v3] as $v4){
										$details_permit[$k1][$k2][1][$v3][1][$v4][0]=(int)$_POST['Permit_'.$k1.'_'.$k2.'_'.$v3.'_'.$v4];
									}
								}
							}
						}elseif(!isset($v2['menu']) && isset($v2['permit'])){
							foreach((array)$v2['permit'] as $v3){
								$details_permit[$k1][$k2][1][$v3][0]=(int)$_POST['Permit_'.$k1.'_'.$k2.'_'.$v3];
							}
						}
					}
				}
				$details_permit[$k1] && $DetailsPermit=addslashes(str::json_data(str::str_code($details_permit[$k1], 'stripslashes')));
				if((int)db::get_row_count('manage_permit', "UserName='$UserName' and Module='$k1'")){
					db::update('manage_permit', "UserName='$UserName' and Module='$k1'", array('Permit'=>$permit, 'DetailsPermit'=>$DetailsPermit));
				}else{
					db::insert('manage_permit', array(
							'UserName'		=>	$UserName,
							'Module'		=>	$k1,
							'Permit'		=>	$permit,
							'DetailsPermit'	=>	$DetailsPermit
						)
					);
				}
			}
		}
	}
	
	public static function check_permit($module, $type=0, $details=array()){
		global $c;
		$data=$_SESSION['Manage']['Permit'][$module];
		$len=count($details);
		if((int)$_SESSION['Manage']['GroupId']!=1 && !in_array($module, $c['manage']['permit_base'])){
			if(!$len && !(int)$data[0]){
				return manage::no_permit($type);
			}elseif($details['a'] && $len==1 && !(int)$data[1][$details['a']][0]){
				return manage::no_permit($type);
			}elseif($details['d'] && $len==2 && !(int)$data[1][$details['a']][1][$details['d']][0]){
				return manage::no_permit($type);
			}elseif($details['p'] && $len==3 && !(int)$data[1][$details['a']][1][$details['d']][1][$details['p']][0]){
				return manage::no_permit($type);
			}
		}
		
		unset($data);
		return true;
	}
	
	public static function no_permit($type){
		global $c;
		if($type==0){
			return false;
		}else{
			exit($c['manage']['lang_pack']['manage']['manage']['no_permit']);
		}
	}
	
	public static function html_tab_button($class=''){
		global $c;
		$html='';
		$html.='<div class="tab_box_row'.(count($c['manage']['config']['Language'])==1?' hide':'').($class?" {$class}":'').'">';
		foreach($c['manage']['config']['Language'] as $k=>$v){
			$html.='<a class="tab_box_btn fl" data-lang="'.$v.'">'.$c['manage']['lang_pack']['language'][$v].'</a>';
		}
		$html.='</div>';
		return $html;
	}
	
	public static function config_edit($config){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$data=array();
		if((int)$config['Effects']){
			$data['Effects']=$p_Effects;
		}
		if((int)$config['HeaderContent']){
			$HeaderContentAry=array();
			foreach($c['manage']['config']['Language'] as $k=>$v){
				for($i=0;$i<$config['HeaderContent'];++$i){
					$num = (int)$config['HeaderContent']>1 ? '_'.$i : '';
					$HeaderContentAry['HeaderContent'.$num.'_'.$v]=${'p_HeaderContent'.$num.'_'.$v};
				}
			}
			$HeaderContentData=addslashes(str::json_data(str::str_code($HeaderContentAry, 'stripslashes')));
			$data['HeaderContent']=$HeaderContentData;
		}
		if((int)$config['IndexContent']){
			$IndexContentAry=array();
			foreach($c['manage']['config']['Language'] as $k=>$v){
				for($i=0;$i<$config['IndexContent'];++$i){
					$num = (int)$config['IndexContent']>1 ? '_'.$i : '';
					$IndexContentAry['IndexContent'.$num.'_'.$v]=${'p_IndexContent'.$num.'_'.$v};
				}
			}
			$IndexContentData=addslashes(str::json_data(str::str_code($IndexContentAry, 'stripslashes')));
			$data['IndexContent']=$IndexContentData;
		}
		if((int)$config['TopMenu']){
			$TopMenuAry=array();
			foreach((array)$p_TopUrl as $k=>$v){
				foreach($c['manage']['config']['Language'] as $k2=>$v2){
					$TopMenuAry[$k]["TopName_{$v2}"]=${'p_TopName_'.$v2}[$k];
				}
				$TopMenuAry[$k]['TopUrl']=$v;
				$TopMenuAry[$k]['TopNewTarget']=$p_TopNewTarget[$k];
			}
			$TopMenuData=addslashes(str::json_data(str::str_code($TopMenuAry, 'stripslashes')));
			$data['TopMenu']=$TopMenuData;
		}
		if((int)$config['ContactMenu']){
			$ContactMenuData=addslashes(str::json_data(str::str_code($p_ContactMenu, 'stripslashes')));
			$data['ContactMenu']=$ContactMenuData;
		}
		if((int)$config['ShareMenu']){
			$ShareMenu=array();
			foreach($c['share'] as $v){
				$ShareMenu[$v]=${'p_Share'.$v};
			}
			$ShareMenuData=addslashes(str::json_data(str::str_code($ShareMenu, 'stripslashes')));
			$data['ShareMenu']=$ShareMenuData;
		}
		if((int)$config['IndexTitle']){
			$data['IndexTitle']=$p_IndexTitle;
		}
		return $data;
	}
}
?>