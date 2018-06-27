<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class user_module{
	public static function user_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$UserId=(int)$g_UserId;
		$row=str::str_code(db::get_one('user', "UserId='$UserId'"));
		if($row){
			db::delete('user', "UserId='$UserId'");
			db::delete('user_address_book', "UserId='$UserId'");
			db::delete('user_favorite', "UserId='$UserId'");
			db::delete('user_operation_log', "UserId='$UserId'");
			$msg_row=str::str_code(db::get_all('user_message', "UserId like '%|{$UserId}|%'"));
			if($msg_row){
				foreach($msg_row as $v){
					$userid=str_replace("|{$UserId}|", '|', $v['UserId']);
					db::update('user_message', "MId={$v['MId']}", array('UserId'=>$userid));
				}
			}
			db::delete('user_message', "UserId='$UserId'");
			db::delete('products_review', "UserId='$UserId'");//删除会员评论
			$orders_row=str::str_code(db::get_all('orders', "UserId='$UserId'", 'OId, OrderId, OrderTime'));//删除会员订单
			if($orders_row){
				$del_where='OrderId in(0';
				foreach($orders_row as $v){
					$month_dir=$c['orders']['path'].date('ym', $v['OrderTime']).'/';
					file::del_dir($month_dir.$v['OId'].'/');
					$del_where.=','.$v['OrderId'];
				}
				$del_where.=')';
				db::delete('orders_products_list', $del_where);
				db::delete('orders', "UserId='{$UserId}'");
			}
			manage::operation_log('删除会员');
		}
		ly200::e_json('', 1);
	}
	
	public static function user_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_userid && js::location('./?m=user&a=user');
		$del_ary=explode('-', $g_group_userid);
		$del_where='UserId in('.str_replace('-', ',', $g_group_userid).')';
		if(db::get_row_count('user', $del_where)){
			db::delete('user', $del_where);
			db::delete('user_address_book', $del_where);
			db::delete('user_favorite', $del_where);
			db::delete('user_operation_log', $del_where);
			db::delete('user_message', $del_where);
			db::delete('products_review', $del_where);
			foreach($del_ary as $v){
				$msg_row=str::str_code(db::get_all('user_message', "UserId like '%|{$v}|%'"));
				if($msg_row){
					foreach($msg_row as $v2){
						$userid=str_replace("|{$v}|", '|', $v2['UserId']);
						db::update('user_message', "MId={$v2['MId']}", array('UserId'=>$userid));
					}
				}
			}
			unset($msg_row);
			$orders_row=str::str_code(db::get_all('orders', $del_where, 'OId, OrderId, OrderTime'));
			if($orders_row){
				$del_where_oth='OrderId in(0';
				foreach($orders_row as $v){
					$month_dir=$c['orders']['path'].date('ym', $v['OrderTime']).'/';
					file::del_dir($month_dir.$v['OId'].'/');
					$del_where_oth.=','.$v['OrderId'];
				}
				$del_where_oth.=')';
				db::delete('orders_products_list', $del_where_oth);
				db::delete('orders', $del_where);
			}
			manage::operation_log('批量删除会员');
		}
		ly200::e_json('', 1);
	}
	
	public static function user_add(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if(empty($p_Email) || !preg_match('/^[a-z0-9]+[a-z0-9_\.\'\-]*@[a-z0-9]+[a-z0-9\.\-]*\.(([a-z]{2,6})|([0-9]{1,3}))$/i', $p_Email)){
			ly200::e_json('The Email address you entered is incorrect.');
		}
		if(empty($p_Password) || $p_Password!=$p_Password2) ly200::e_json('两次密码不一致！');
		$p_Password=ly200::password($p_Password);
		$p_Other=addslashes(str::json_data(str::str_code($p_Other, 'stripslashes')));
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
				'SalesId'		=>	(int)$p_SalesId,
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
				'LoginTimes'	=>	1
			);
			db::insert('user', $data);
			$UserId=db::get_insert_id();
			if($ParentId=db::get_value('sales_coupon',"CouponWay=2 and ({$c['time']} < EndTime and {$c['time']} > StartTime)",'CId')){
				user::get_user_coupons($UserId,$ParentId); //会员注册送优惠券
			}
			if($p_Address || $p_country_id || $p_Phone){
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
					'CountryCode'	=>	$p_CountryCode,
					'PhoneNumber'	=>	$p_Phone,
					'AccTime'		=>	$time
				);
				db::insert('user_address_book', $data_oth);//Shipping Address
				$data_oth['IsBillingAddress']=1;
				db::insert('user_address_book', $data_oth);//Billing Address
			}
			user::operation_log($UserId, '会员注册');
			manage::operation_log('会员注册');
			ly200::e_json('', 1);
		}else{
			ly200::e_json('The email address already exists, please change it or sign in to checkout.');
		}
	}
	
	public static function user_edit_sales(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_SalesId=(int)$p_SalesId;
		$result='';
		if($p_SalesId>0 && (float)db::get_value('user', "UserId='{$p_UserId}'", 'SalesId')!=$p_SalesId){
			db::update('user', "UserId='{$p_UserId}'", array('SalesId'=>$p_SalesId));
			manage::operation_log('会员修改业务员');
		}
		$result=db::get_value('manage_sales', "SalesId='{$p_SalesId}'", 'UserName');
		ly200::e_json($result, 1);
	}
	
	public static function user_explode(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Number=(int)$p_Number;
		if(!$p_Number) unset($_SESSION['UserZip']);
		if(!(int)$p_explodeAll && $p_UserIdStr){
			$p_UserIdStr=='|' && ly200::e_json(array('没有选择会员'), 0);
			$p_UserId=str_replace('|', ',', substr($p_UserIdStr, 1, -1));
		}
		include($c['root_path'].'/inc/class/excel.class/PHPExcel.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/Writer/Excel5.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/IOFactory.php');
		$level_ary=array();
		$level_row=str::str_code(db::get_all('user_level', 'IsUsed=1'));
		foreach((array)$level_row as $k=>$v){
			$level_ary[$v['LId']]=$v['Name'.$c['manage']['web_lang']];
		}
		unset($level_row);
		$page_count=1000;//每次分开导出的数量
		$where='1';
		if(!(int)$p_explodeAll && $p_UserId) $where.=" and UserId in($p_UserId)";
		(int)$_SESSION['Manage']['GroupId']==3 && $where.=" and SalesId='{$_SESSION['Manage']['SalesId']}'";//业务员账号过滤
		$row_count=db::get_row_count('user', $where, 'UserId');
		$total_pages=ceil($row_count/$page_count);
		$zipAry=array();//储存需要压缩的文件
		$save_dir='/tmp/';//临时储存目录
		file::mk_dir($save_dir);
		if($p_Number<$total_pages){
			$page=$page_count*$p_Number;
			$user_row=str::str_code(db::get_limit('user', $where, '*', 'UserId desc', $page, $page_count));
			$objPHPExcel=new PHPExcel();
			//Set properties 
			$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
			$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
			$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
			$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
			$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
			$objPHPExcel->getProperties()->setCategory("Test result file");
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A1', $c['manage']['lang_pack']['user']['name']);
			$objPHPExcel->getActiveSheet()->setCellValue('B1', $c['manage']['lang_pack']['user']['email']);
			$objPHPExcel->getActiveSheet()->setCellValue('C1', $c['manage']['lang_pack']['user']['level']['level']);
			$objPHPExcel->getActiveSheet()->setCellValue('D1', $c['manage']['lang_pack']['user']['consumption_price']);
			$objPHPExcel->getActiveSheet()->setCellValue('E1', $c['manage']['lang_pack']['user']['reg_time']);
			$objPHPExcel->getActiveSheet()->setCellValue('F1', $c['manage']['lang_pack']['user']['reg_ip']);
			$objPHPExcel->getActiveSheet()->setCellValue('G1', $c['manage']['lang_pack']['orders']['export']['shipname']);
			$objPHPExcel->getActiveSheet()->setCellValue('H1', $c['manage']['lang_pack']['orders']['export']['shipaddress']);
			$objPHPExcel->getActiveSheet()->setCellValue('I1', $c['manage']['lang_pack']['orders']['export']['shipphone']);
			$objPHPExcel->getActiveSheet()->setCellValue('J1', $c['manage']['lang_pack']['orders']['export']['billname']);
			$objPHPExcel->getActiveSheet()->setCellValue('K1', $c['manage']['lang_pack']['orders']['export']['billaddress']);
			$objPHPExcel->getActiveSheet()->setCellValue('L1', $c['manage']['lang_pack']['orders']['export']['billphone']);
			$i=2;
			foreach($user_row as $v){
				$UserId=$v['UserId'];
				$ship_row=str::str_code(db::get_one('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', "a.UserId={$UserId} and a.IsBillingAddress=0", 'a.*, c.Country, c.Code, s.States as StateName', 'a.AccTime desc, a.AId desc'));
				$bill_row=str::str_code(db::get_one('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', "a.UserId={$UserId} and a.IsBillingAddress=1", 'a.*, c.Country, c.Code, s.States as StateName'));
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $v['FirstName'].' '.$v['LastName']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $v['Email']);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $level_ary[$v['Level']]);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $v['Consumption']);
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, date('Y-m-d H:i:s', $v['RegTime']));
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $v['RegIp'].'【'.ly200::ip($v['RegIp']).'】');
				$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $ship_row['FirstName'].' '.$ship_row['LastName']);
				$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $ship_row['AddressLine1'].' '.$ship_row['City'].', '.($ship_row['StateName']?$ship_row['StateName']:$ship_row['State']).', '.$ship_row['ZipCode'].' '.$ship_row['Country']);
				$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, '+'.$ship_row['Code'].' '.$ship_row['PhoneNumber']);
				$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $bill_row['FirstName'].' '.$bill_row['LastName']);
				$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $bill_row['AddressLine1'].' '.$bill_row['City'].', '.($bill_row['StateName']?$bill_row['StateName']:$bill_row['State']).', '.$bill_row['ZipCode'].' '.$bill_row['Country']);
				$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, '+'.$bill_row['Code'].' '.$bill_row['PhoneNumber']);
				++$i;
			}
			//设置列的宽度   
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(15);
			$objPHPExcel->getActiveSheet()->setTitle('Simple');//Rename sheet
			$objPHPExcel->setActiveSheetIndex(0);
			$ExcelName='user_'.str::rand_code();
			$objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);
			$objWriter->save($c['root_path']."{$save_dir}{$ExcelName}.xls");
			$_SESSION['UserZip'][]="{$save_dir}{$ExcelName}.xls";
			unset($c, $objPHPExcel, $objWriter, $user_row, $ship_row, $bill_row);
			ly200::e_json(array(($p_Number+1), "已导出{$save_dir}{$ExcelName}.xls<br />"), 2);
		}else{
			if(count($_SESSION['UserZip'])){
				ly200::e_json('', 1);
			}else{
				ly200::e_json('');
			}
		}
	}
	
	public static function user_explode_down(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		if($g_Status=='ok' && count($_SESSION['UserZip'])){	//开始打包
			$zip=new ZipArchive();
			$zipname='/tmp/user_'.str::rand_code().'.zip';
			if($zip->open($c['root_path'].$zipname, ZIPARCHIVE::CREATE)===TRUE){
				foreach($_SESSION['UserZip'] as $path){
					if(is_file($c['root_path'].$path)) $zip->addFile($c['root_path'].$path, $path);
				}
				$zip->close();
				file::down_file($zipname);
				file::del_file($zipname);
				foreach($_SESSION['UserZip'] as $path){
					if(is_file($c['root_path'].$path)) file::del_file($path);
				}
			}
		}
		unset($_SESSION['UserZip']);
		exit();
	}
	
	public static function user_custom_column(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Custom=addslashes(str::json_data(str::str_code($p_Custom, 'stripslashes')));
		$data=array(
			'User'	=>	$p_Custom
		);
		manage::config_operaction($data, 'custom_column');
		manage::operation_log('订单自定义列');
		ly200::e_json('', 1);
	}
	
	public static function user_base_info_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_UserId=(int)$p_UserId;
		$data=array(
			'Level'			=>	(int)$p_Level,
			'IsLocked'		=>	(int)$p_IsLocked,
			'Status'		=>	($c['FunVersion']>=1 && $c['manage']['config']['UserStatus'])?(int)$p_Status:0,
			'Gender'		=>	(int)$p_Gender?1:0,
			'Age'			=>	(int)$p_Age,
			'NickName'		=>	$p_NickName,
			'Telephone'		=>	$p_Telephone,
			'Fax'			=>	$p_Fax,
			'Birthday'		=>	$p_Birthday,
			'Facebook'		=>	$p_Facebook,
			'Company'		=>	$p_Company,
			'Remark'		=>	$p_Remark,
		);
		if(($c['FunVersion']>1 || ($c['FunVersion']==1 && $c['NewFunVersion']<=1)) && (int)$_SESSION['Manage']['GroupId']<3){
			$data['SalesId']=(int)$p_SalesId;
		}
		db::update('user', "UserId={$p_UserId}", $data);
		user::operation_log($p_UserId, '修改基本信息');
		manage::operation_log('修改会员基本信息');
		ly200::e_json('', 1);
	}
	
	public static function user_password_edit(){
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if($p_NewPassword==$p_ReNewPassword){
			$p_UserId=(int)$p_UserId;
			$p_NewPassword=ly200::password($p_NewPassword);
			db::update('user', "UserId={$p_UserId}", array('password'=>$p_NewPassword));
			user::operation_log($p_UserId, '修改密码');
			manage::operation_log('修改会员密码');
			ly200::e_json('', 1);
		}else{
			ly200::e_json('两次密码不一致!');
		}
	}
	
	public static function level_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$LId=(int)$p_LId;
		$p_PicPath=$p_PicPath;
		if(!(int)$p_IsUsed && db::get_row_count('user', "Level='$LId'")){
			ly200::e_json(manage::get_language('user.level.close_tips'), 0);
		}
		$p_Discount=(int)$p_Discount;
		$p_Discount>100 && $p_Discount=100;
		$data=array(
			'PicPath'	=>	$p_PicPath,
			'IsUsed'	=>	(int)$p_IsUsed,
			'Discount'	=>	$p_Discount,
			'FullPrice'	=>	(float)$p_FullPrice,
		);
		if($p_LId){
			db::update('user_level', "LId='$LId'", $data);
			manage::operation_log('修改会员等级');
		}else{
			db::insert('user_level', $data);
			$LId=db::get_insert_id();
			manage::operation_log('添加会员等级');
		}
		manage::database_language_operation('user_level', "LId='$LId'", array('Name'=>0));
		ly200::e_json('', 1);
	}
	
	public static function user_level_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$LId=(int)$g_LId;
		$row=str::str_code(db::get_one('user_level', "LId='$LId'"));
		if($row){
			if(db::get_row_count('user', "Level='$LId'")){
				ly200::e_json(manage::get_language('user.level.close_tips'), 0);
			}
			if(is_file($c['root_path'].$row['PicPath'])) file::del_file($row['PicPath']);
			db::delete('user_level', "LId='$LId'");
			manage::operation_log('删除会员等级');
		}
		ly200::e_json('', 1);
	}
	
	public static function reg_set(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_field=$g_field;
		$g_status=(int)$g_status;
		if(strpos($g_field, 'NotNull')){
			$field=str_replace('NotNull', '', $g_field);
			$key=1;
		}else{
			$field=$g_field;
			$key=0;
		}
		$RegSet=db::get_value('config', 'GroupId="user" and Variable="RegSet"', 'Value');
		if($RegSet){
			$reg_ary=str::json_data($RegSet, 'decode');
		}else{
			$reg_ary=array();
			foreach($c['manage']['user_reg_field'] as $k=>$v){
				$reg_ary[$k]=$v?array(0, 0):array(1, 1);
			}
		}
		$reg_ary[$field][$key]=$g_status?0:1;
		if(!$reg_ary[$field][0]) $reg_ary[$field][1]=0;
		$RegSet=addslashes(str::json_data(str::str_code($reg_ary, 'stripslashes')));
		db::update('config', 'GroupId="user" and Variable="RegSet"', array('Value'=>$RegSet));
		manage::operation_log('修改固定注册事项');
		ly200::e_json('', 1);
	}
	
	public static function reg_set_edit(){
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_SetId=(int)$p_SetId;
		$p_TypeId=(int)$p_TypeId;
		$data=array('TypeId'=>$p_TypeId);
		if($p_SetId){
			db::update('user_reg_set', "SetId={$p_SetId}", $data);
			manage::operation_log('修改注册事项');
		}else{
			db::insert('user_reg_set', $data);
			$p_SetId=db::get_insert_id();
			manage::operation_log('添加注册事项');
		}
		manage::database_language_operation('user_reg_set', "SetId={$p_SetId}", array('Name'=>0, 'Option'=>3));
		ly200::e_json('', 1);
	}
	
	public static function reg_set_del(){
		$SetId=(int)$_GET['SetId'];
		db::delete('user_reg_set', "SetId={$SetId}");
		manage::operation_log('删除注册事项');
		ly200::e_json('', 1);
	}
	
	public static function inbox_reply(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$data=array(
			'MId'		=>	(int)$p_MId,
			'Content'	=>	$p_Content,
			'PicPath'	=>	$p_PicPath,
			'AccTime'	=>	$c['time']
		);
		db::insert('user_message_reply',$data);
		db::update('user_message',"MId='$p_MId'",array('IsReply'=>1));
		manage::operation_log('回复站内信');
		ly200::e_json('', 1);
	}
	
	public static function inbox_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$count=count(explode('|', substr($p_UserIdStr, 1, -1)));
		!$p_Subject && ly200::e_json(manage::get_language('inbox.title_tips'));
		!$p_Content && ly200::e_json(manage::get_language('inbox.content_tips'));
		!$p_UserIdStr && ly200::e_json(manage::get_language('inbox.inbox.receiver_tips'));
		$IsRead='|'.implode('|', array_fill(0, $count, 0)).'|';
		$data=array(
			'UserId'	=>	$p_UserIdStr,
			'Type'		=>	1,
			'Subject'	=>	$p_Subject,
			'Content'	=>	$p_Content,
			'PicPath'	=>	$p_PicPath,
			'IsRead'	=>	$IsRead,
			'AccTime'	=>	$c['time']
		);
		db::insert('user_message', $data);
		manage::operation_log('发送站内信');
		ly200::e_json('', 1);
	}
	
	public static function inbox_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_MId=(int)$g_MId;
		db::delete('user_message', "MId='$g_MId'");
		db::delete('user_message_reply', "MId='$g_MId'");
		manage::operation_log('删除站内信');
		ly200::e_json('', 1);
	}
	
	public static function inbox_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="MId in(".str_replace('-',',',$g_group_id).")";
		db::delete('user_message', $del_where);
		db::delete('user_message_reply', $del_where);
		manage::operation_log('批量删除站内信');
		ly200::e_json('', 1);
	}

	public static function inbox_explode(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/Writer/Excel5.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/IOFactory.php');
		
		$w="Module='$g_Status'";
		if($g_Status=='others'){
			$w.=(int)$g_Type?' and Type=1':' and Type=0';	//0.收件箱 1.发件箱
		}
		$explode_row=str::str_code(db::get_all('user_message', $w, '*', 'MId desc'));
		if($explode_row){
			$reply_row=str::str_code(db::get_all('user_message_reply', 1, '*', 'RId asc'));
			$explode_reply=array();
			foreach((array)$reply_row as $v){
				$explode_reply[$v['MId']][]=$v;
			}
			
			if($g_Status=='products'){	//产品
				$pro_ary=array();
				foreach($explode_row as $v){
					$pro_ary[]=(int)$v['Subject'];
				}
				$pro_ary=implode(',',$pro_ary);
				if($pro_ary){
					$pro_ary=db::get_all('products',"ProId in ($pro_ary)");
					$proId_ary=array();
					foreach($pro_ary as $v){
						$proId_ary[$v['ProId']]=$v;
					}
				}
			}
			
			$userid_ary=array();
			foreach($explode_row as $v){
				if((int)$g_Type){	//发件箱
					if($v['UserId']=='-1') continue;
					$ex=explode('|',$v['UserId']);
					foreach((array)$ex as $v1){
						if(!$v1) continue;
						$userid_ary[]=$v1;
					}
				}else{
					$userid_ary[]=$v['UserId'];
				}
			}
			$userid_ary=implode(',',$userid_ary);
			if($userid_ary){
				$user_row=db::get_all('user',"UserId in ($userid_ary)");
				$user_ary=array();
				foreach($user_row as $v){
					$user_ary[$v['UserId']]=$v;
				}
			}
			//Set properties 
			$objPHPExcel=new PHPExcel();
			$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
			$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
			$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
			$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
			$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
			$objPHPExcel->getProperties()->setCategory("Test result file");
			$objPHPExcel->setActiveSheetIndex(0);
			$i=2;
			
			if($g_Status=='orders'){	//订单
					$objPHPExcel->getActiveSheet()->setCellValue('A1', '订单号');
					$objPHPExcel->getActiveSheet()->setCellValue('B1', '发件人');
					$objPHPExcel->getActiveSheet()->setCellValue('C1', '时间');
					$objPHPExcel->getActiveSheet()->setCellValue('D1', '图片');
					$objPHPExcel->getActiveSheet()->setCellValue('E1', '内容');
					$img_row='D';
			}elseif($g_Status=='products'){	//产品
					$objPHPExcel->getActiveSheet()->setCellValue('A1', '产品图片');
					$objPHPExcel->getActiveSheet()->setCellValue('B1', '名称');
					$objPHPExcel->getActiveSheet()->setCellValue('C1', '发件人');
					$objPHPExcel->getActiveSheet()->setCellValue('D1', '时间');
					$objPHPExcel->getActiveSheet()->setCellValue('E1', '图片');
					$objPHPExcel->getActiveSheet()->setCellValue('F1', '内容');
					$img_row='E';
			}else{	//其他
				if((int)$g_Type){	//发件箱
					$objPHPExcel->getActiveSheet()->setCellValue('A1', '主题');
					$objPHPExcel->getActiveSheet()->setCellValue('B1', '发件人');
					$objPHPExcel->getActiveSheet()->setCellValue('C1', '收件人');
					$objPHPExcel->getActiveSheet()->setCellValue('D1', '时间');
					$objPHPExcel->getActiveSheet()->setCellValue('E1', '图片');
					$objPHPExcel->getActiveSheet()->setCellValue('F1', '内容');
					$img_row='E';
				}else{	//收件箱
					$objPHPExcel->getActiveSheet()->setCellValue('A1', '主题');
					$objPHPExcel->getActiveSheet()->setCellValue('B1', '发件人');
					$objPHPExcel->getActiveSheet()->setCellValue('C1', '时间');
					$objPHPExcel->getActiveSheet()->setCellValue('D1', '图片');
					$objPHPExcel->getActiveSheet()->setCellValue('E1', '内容');
					$img_row='D';
				}
			}
			foreach((array)$explode_row as $k=>$v){
				if($g_Status=='orders'){	//订单
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $v['Subject']);
					$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $user_ary[(int)$v['UserId']]['FirstName'].' '.$user_ary[(int)$v['UserId']]['LastName'].' ('.$user_ary[(int)$v['UserId']]['Email'].')');
					$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, date('Y-m-d H:i:s', $v['AccTime']));
					$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, '');
					$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, str::format($v['Content']));
				}elseif($g_Status=='products'){	//产品
					$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, '');
					$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $proId_ary[(int)$v['Subject']]['Name'.$c['manage']['web_lang']]);
					$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $user_ary[(int)$v['UserId']]['FirstName'].' '.$user_ary[(int)$v['UserId']]['LastName'].' ('.$user_ary[(int)$v['UserId']]['Email'].')');
					$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, date('Y-m-d H:i:s', $v['AccTime']));
					$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, '');
					$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, str::format($v['Content']));
					//产品图片
					if(is_file($c['root_path'].$proId_ary[(int)$v['Subject']]['PicPath_0'])){
						$objDrawing=new PHPExcel_Worksheet_Drawing();
						$objDrawing->setName('ZealImg');
						$objDrawing->setDescription('Image inserted by Zeal');
						$objDrawing->setPath($c['root_path'].$proId_ary[(int)$v['Subject']]['PicPath_0']);
						$objDrawing->setWidth(80);
						$objDrawing->setHeight(80);
						$objDrawing->setCoordinates('A'.$i);
						$objDrawing->setOffsetX(15);
						$objDrawing->setOffsetY(15);
						$objDrawing->getShadow()->setVisible(true);
						$objDrawing->getShadow()->setDirection(36);
						$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
					}
				}else{	//其他
					if((int)$g_Type){	//发件箱
						$userStr='';
						if($v['UserId']=='-1'){
							$userStr='所有会员';
						}else{
							$ex=explode('|', $v['UserId']);
							foreach((array)$ex as $v1){
								if(!$v1) continue;
								$userStr.="{$user_ary[$v1]['FirstName']} {$user_ary[$v1]['LastName']}|";
							}
						}
						$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $v['Subject']);
						$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '管理员');
						$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $userStr);
						$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, date('Y-m-d H:i:s', $v['AccTime']));
						$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, '');
						$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, str::format($v['Content']));
					}else{	//收件箱
						$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $v['Subject']);
						$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $user_ary[(int)$v['UserId']]['FirstName'].' '.$user_ary[(int)$v['UserId']]['LastName'].' ('.$user_ary[(int)$v['UserId']]['Email'].')');
						$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, date('Y-m-d H:i:s', $v['AccTime']));
						$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, '');
						$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, str::format($v['Content']));
					}
				}
				//图片
				if(is_file($c['root_path'].$v['PicPath'])){
					$objDrawing=new PHPExcel_Worksheet_Drawing();
					$objDrawing->setName('ZealImg');
					$objDrawing->setDescription('Image inserted by Zeal');
					$objDrawing->setPath($c['root_path'].$v['PicPath']);
					$objDrawing->setWidth(80);
					$objDrawing->setHeight(80);
					$objDrawing->setCoordinates($img_row.$i);
					$objDrawing->setOffsetX(15);
					$objDrawing->setOffsetY(15);
					$objDrawing->getShadow()->setVisible(true);
					$objDrawing->getShadow()->setDirection(36);
					$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
				}
				if($explode_reply[$v['MId']]){	//回复
					foreach((array)$explode_reply[$v['MId']] as $v1){
						$objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(80);//设置行的宽度
						++$i;
						if($g_Status=='orders'){	//订单
							$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, '');
							$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, str::format($v1['Content']));
						}else{	//产品
							$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, '');
							$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, str::format($v1['Content']));
						}
						if(is_file($c['root_path'].$v1['PicPath'])){
							$objDrawing=new PHPExcel_Worksheet_Drawing();
							$objDrawing->setName('ZealImg');
							$objDrawing->setDescription('Image inserted by Zeal');
							$objDrawing->setPath($c['root_path'].$v1['PicPath']);
							$objDrawing->setWidth(80);
							$objDrawing->setHeight(80);
							$objDrawing->setCoordinates($img_row.$i);
							$objDrawing->setOffsetX(15);
							$objDrawing->setOffsetY(15);
							$objDrawing->getShadow()->setVisible(true);
							$objDrawing->getShadow()->setDirection(36);
							$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
						}
					}
				}
				$objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(80);//设置行的宽度
				++$i;
			}
			//设置列的宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);  
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
			$objPHPExcel->getActiveSheet()->setTitle('Simple');//Rename sheet
			//Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);
			//Save Excel 2007 file
			$ExcelName='inbox_'.str::rand_code();
			$objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);
			$objWriter->save($c['root_path']."/tmp/{$ExcelName}.xls");
			unset($c, $objPHPExcel, $objWriter, $explode_row);
			file::down_file("/tmp/{$ExcelName}.xls");
			file::del_file("/tmp/{$ExcelName}.xls");
		}else{
			js::location("./?m=user&a=inbox");
		}
	}
	
	public static function message_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_MId=(int)$p_MId;
		$p_UserId=(int)$p_UserId;
		!$p_Title && ly200::e_json(manage::get_language('inbox.title_tips'));
		!$p_Content && ly200::e_json(manage::get_language('inbox.content_tips'));
		$data=array(
			'UserId'	=>	$p_UserId,
			'Title'		=>	$p_Title,
			'Content'	=>	$p_Content
		);
		if($p_MId){
			$data['EditTime']=$c['time'];
			db::update('message', "MId='$p_MId'", $data);
			manage::operation_log('修改系统消息');
		}else{
			$data['AccTime']=$c['time'];
			db::insert('message', $data);
			manage::operation_log('添加系统消息');
		}
		ly200::e_json('', 1);
	}
	
	public static function message_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_MId=(int)$g_MId;
		db::delete('message', "MId='$g_MId'");
		manage::operation_log('删除系统消息');
		ly200::e_json('', 1);
	}
	
	public static function message_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="MId in(".str_replace('-',',',$g_group_id).")";
		db::delete('message', $del_where);
		manage::operation_log('批量删除系统消息');
		ly200::e_json('', 1);
	}
	
	public static function message_explode(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/Writer/Excel5.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/IOFactory.php');
		
		$explode_row=str::str_code(db::get_all('message', '1', '*', 'MId desc'));
		
		$objPHPExcel=new PHPExcel();
		//Set properties 
		$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
		$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
		$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
		$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
		$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
		$objPHPExcel->getProperties()->setCategory("Test result file");
		$objPHPExcel->setActiveSheetIndex(0);
		$i=2;
		$objPHPExcel->getActiveSheet()->setCellValue('A1', '标题');
		$objPHPExcel->getActiveSheet()->setCellValue('B1', '管理员');
		$objPHPExcel->getActiveSheet()->setCellValue('C1', '时间');
		$objPHPExcel->getActiveSheet()->setCellValue('D1', '内容');
		foreach((array)$explode_row as $k=>$v){
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $v['Title']);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $_SESSION['Manage']['UserName']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, date('Y-m-d H:i:s', $v['AccTime']));
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, str::format($v['Content']));
			++$i;
		}
		//设置列的宽度
		$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);  
		$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
		$objPHPExcel->getActiveSheet()->setTitle('Simple');//Rename sheet
		//Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		//Save Excel 2007 file
		$ExcelName='message_'.str::rand_code();
		$objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);
		$objWriter->save($c['root_path']."/tmp/{$ExcelName}.xls");
		unset($c, $objPHPExcel, $objWriter, $explode_row);
		file::down_file("/tmp/{$ExcelName}.xls");
		file::del_file("/tmp/{$ExcelName}.xls");
		exit;
	}
	
	public static function user_batch_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		$p_UserId=(array)$p_UserId;
		$p_type=(int)$p_type;
		foreach((array)$p_UserId as $k=>$v){
			$UserId=(int)$v;
			if(!$UserId) continue;
			if($p_type){	//业务员
				$data=array('SalesId'=>(int)$p_SalesId);
			}else{
				$data=array('Level'=>(int)$p_Level);
			}
			db::update('user', "UserId='$UserId'", $data);
		}
		ly200::e_json('批量修改成功<br />', 1);
	}
}
?>