<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class email_module{
	public static function send(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$Subject=$p_Subject;
		$Email=$p_Email;
		$Content=stripslashes($p_Content);
		$same=(substr_count($Subject, '{Email}') || substr_count($Subject, '{FullName}') || substr_count($Content, '{Email}') || substr_count($Content, '{FullName}'))?0:1;
		$to_ary=$to_name_ary=$send_list=array();
		$Email_ary=explode("\r\n", $Email);
		$Content=preg_replace("~([\"|'|(|=])/u_file/~i", '$1'.ly200::get_domain(1).'/u_file/', $Content);
		$Content=preg_replace("~([\"|'|(|=])/search/~i", '$1'.ly200::get_domain(1).'/search/', $Content);
		//检查一次收件人格式
		$Email_ary=array_unique($Email_ary);//删除重复
		$len=0;
		$Email_len=count($Email_ary);
		foreach($Email_ary as $k=>$v){
			if(strrpos($v, '/')) $len+=1;
		}
		if($Email_len!=$len) ly200::e_json(manage::get_language('email.send_tips'));
		if($Email_len>1) ly200::e_json('多个邮件发送功能尚在开发中，敬请期待');
		foreach($Email_ary as $k=>$v){
			if($v==''){continue;}
			$list_ary=explode('/', str_replace(';', '', $v));
			if(in_array(trim($list_ary[0]), $send_list)){
				continue;
			}else{
				$send_list[]=trim($list_ary[0]);
			}
			$to = $list_ary[0];
			$to_name = $list_ary[1];
			if($same==0){	//邮件内容不相同
				$ToAry[]=trim($list_ary[0]);
				$NameAry[]=trim($list_ary[1]);
				$SubjectAry[]=str_replace(array('{Email}', '{FullName}'), array($to, $to_name), $Subject);
				$ContentsAry[]=str_replace(array('{Email}', '{FullName}'), array($to, $to_name), $Content);
			}else{	//邮件内容全部相同的
				$ToAry[]=trim($list_ary[0]);
				$NameAry[]=trim($list_ary[1]);
				$SubjectAry[]=$Subject;
				$ContentsAry[]=$Content;
			}
		}
		manage::operation_log('发送邮件');
		manage::email_log($ToAry, $SubjectAry[0], $ContentsAry[0]);//邮件发送记录
		echo str::json_data(array(
				'msg'	=>	'发送成功！',
				'ret'	=>	1
			)
		);
		if((int)$p_Arrival){//更新到货通知信息
			db::update('arrival_notice', "AId='{$p_Arrival}'", array('IsSend'=>1, 'SendTime'=>$c['time']));
		}
		function_exists('fastcgi_finish_request') && fastcgi_finish_request();
		//ly200::sendmail($ToAry, $SubjectAry, $ContentsAry);
		ly200::sendmail($ToAry, $SubjectAry[0], $ContentsAry[0]);
	}
	
	public static function send_import(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$file_path=$c['root_path'].$p_FilePath;//邮件模板目录绝对路径
		$str='';
		if(is_file($file_path)){
			$txt=trim(file_get_contents($file_path));
			file::del_file($p_FilePath);
		}
		ly200::e_json($txt, $txt!='');
	}
	
	public static function send_get_tpl(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		//$p_class 模板分类文件夹; $p_template 模板文件夹
		$domain=ly200::get_domain();//网站域名
		$tpl_dir=$c['manage']['email_tpl_dir'].$p_class.'/'.$p_template;
        $base_dir=$c['root_path'].$tpl_dir;//邮件模板目录绝对路径
		$txt='';
		if($p_class=='customize'){//自定义模板
			if((int)$p_template){
				$txt=db::get_value('email_list', "EId='{$p_template}'", 'Content');
				$txt=preg_replace('/{tpl_dir}/i', $tpl_dir.'/', preg_replace('/{domain}/i', ly200::get_domain(1), $txt));
			}
		}else{
			if(is_dir($base_dir)){
				$tpl_file=$base_dir.'/template.html';
				if(file_exists($tpl_file)){
					//$txt=preg_replace('/{tpl_dir}/i', $domain.$tpl_dir.'/', preg_replace('/{domain}/i', ly200::get_domain(1), file_get_contents($tpl_file)));
					$txt=preg_replace('/{tpl_dir}/i', $tpl_dir.'/', preg_replace('/{domain}/i', ly200::get_domain(1), file_get_contents($tpl_file)));
				}
			}
		}
		ly200::e_json($txt, $txt!='');
	}
	
	public static function customize_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if($p_Title && $p_Content){
			$data=array(
				'Title'		=>	$p_Title,
				'Content'	=>	trim(htmlspecialchars_decode(str::unescape($p_Content))),
				'AccTime'	=>	$c['time']
			);
			db::insert('email_list', $data);
			manage::operation_log('添加邮件自定义模板');
		}
		ly200::e_json('', 1);
	}
	
	public static function customize_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		if((int)$g_EId){
			db::delete('email_list', "EId='$g_EId'");
			manage::operation_log('删除邮件自定义模板');
		}
		ly200::e_json('', 1);
	}
	
	public static function config(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$data=array(
			'FromEmail'		=>	$p_FromEmail,
			'FromName'		=>	$p_FromName,
			'SmtpHost'		=>	$p_SmtpHost,
			'SmtpPort'		=>	(int)$p_SmtpPort?(int)$p_SmtpPort:25,
			'SmtpUserName'	=>	$p_SmtpUserName,
			'SmtpPassword'	=>	$p_SmtpPassword
		);
		$json_data=str::json_data($data);
		manage::config_operaction(array('config'=>$json_data), 'email');
		
		//邮件通知
		$data=array();
		foreach($c['manage']['email_notice'] as $k=>$v){
			if($v=='order_create') continue; //下单，另外储存
			$data[$v]=in_array($v, $p_Notice)?1:0;
		}
		$json_data=str::json_data($data);
		manage::config_operaction(array('notice'=>$json_data), 'email');
		manage::config_operaction(array('CheckoutEmail'=>in_array('order_create', $p_Notice)?1:0), 'global');//下单
		
		//底部签名内容
		$BottomContentAry=array();
		foreach($c['manage']['config']['Language'] as $k=>$v){
			$BottomContentAry['BottomContent_'.$v]=${'p_BottomContent_'.$v};
		}
		$BottomContentData=addslashes(str::json_data(str::str_code($BottomContentAry, 'stripslashes')));
		manage::config_operaction(array('bottom'=>$BottomContentData), 'email');
		ly200::e_json('', 1);
	}
	
	public static function newsletter_cancel(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_NId=(int)$g_NId;
		$newsletter_row=db::get_one('newsletter', "NId='{$g_NId}'");
		if($newsletter_row){
			db::delete('newsletter', "NId='{$g_NId}'");
			manage::operation_log('删除订阅: '.$newsletter_row['Email']);
		}
		ly200::e_json('', 1);
	}
	
	public static function newsletter_status(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_NId=(int)$g_NId;
		$g_Type=(int)$g_Type;
		$newsletter_row=db::get_one('newsletter', "NId='{$g_NId}'");
		if($newsletter_row){
			db::update('newsletter', "NId='{$g_NId}'", array('IsUsed'=>$g_Type));
			manage::operation_log(($g_Type?'开启':'取消').'订阅: '.$newsletter_row['Email']);
		}
		//ly200::e_json('', 1);
		js::location('?m=email&d=newsletter');
	}
	
	public static function newsletter_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_NId=(int)$g_NId;
		$newsletter_row=db::get_one('newsletter', "NId='{$g_NId}'");
		if($newsletter_row){
			db::delete('newsletter', "NId='{$g_NId}'");
			manage::operation_log('删除订阅: '.$newsletter_row['Email']);
		}
		ly200::e_json('', 1);
	}
	
	//邮件订阅导出
	public static function newsletter_explode(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');	
		include($c['root_path'].'/inc/class/excel.class/PHPExcel.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/Writer/Excel5.php');
		include($c['root_path'].'/inc/class/excel.class/PHPExcel/IOFactory.php');
		
		//Add some data
		$result=db::get_all('newsletter', '1', '*', 'AccTime desc');
		
		if($result){
			$objPHPExcel=new PHPExcel();
			 
			//Set properties 
			$objPHPExcel->getProperties()->setCreator("Maarten Balliauw");
			$objPHPExcel->getProperties()->setLastModifiedBy("Maarten Balliauw");
			$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Test Document");
			$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Test Document");
			$objPHPExcel->getProperties()->setKeywords("office 2007 openxml php");
			$objPHPExcel->getProperties()->setCategory("Test result file");
			
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A1', '邮箱');
			$objPHPExcel->getActiveSheet()->setCellValue('B1', '时间');
			$objPHPExcel->getActiveSheet()->setCellValue('C1', '已订阅');
			
			$i=2;
			foreach((array)$result as $k=>$v){
				
				$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $v['Email']);
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, date('Y-m-d H:i:s', $v['AccTime']));
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, (int)$v['IsUsed']?'是':'否');
				
				//设置行的高度
				$objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(30);
				
				++$i;
			}
			
			//设置列的宽度
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(50);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
			
			//Rename sheet
			$objPHPExcel->getActiveSheet()->setTitle('Simple');
			
			//Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objPHPExcel->setActiveSheetIndex(0);
			
			//Save Excel 2007 file
			$ExcelName='newsletter_'.str::rand_code();
			$objWriter=new PHPExcel_Writer_Excel5($objPHPExcel);
			$objWriter->save($c['root_path']."/tmp/{$ExcelName}.xls");
			unset($c, $objPHPExcel, $objWriter, $row, $prod_ary);
			
			file::down_file("/tmp/{$ExcelName}.xls");
			file::del_file("/tmp/{$ExcelName}.xls");
			
			ly200::e_json('', 1);
		}else{
			js::location('?m=email&d=newsletter');
		}
	}
	
	public static function arrival_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_AId=(int)$g_AId;
		$arrival_row=db::get_one('arrival_notice', "AId='{$g_AId}'");
		if($arrival_row){
			db::delete('arrival_notice', "AId='{$g_AId}'");
			manage::operation_log('删除到货通知');
		}
		ly200::e_json('', 1);
	}
	
	public static function sys_get_tpl(){//获取系统模板
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$template = $p_template;
		!in_array($template, $c['sys_email_tpl']) && ly200::e_json('', 0);
		$lang= trim($p_lang, ',');//语言版
		$lang = (array)@explode(',', $lang);
		$row = db::get_one('system_email_tpl', "Template='{$template}'");//查询模板是否存在
		$data = array('lang'=>array(), 'template'=>$template);//返回的数据
		$data['IsUsed'] = $row['IsUsed']?1:0;
		foreach ($lang as $k=>$v){
			$data['lang'][]=$v;
			if ($row['Content_'.$v]){//存在读取数据库
				$data['Title'][$v] = $row['Title_'.$v];
				$data['Content'][$v] = $row['Content_'.$v];
			}else{//不存在读取系统文件默认模板
				$tpl_lang = $v;//定义语言包
				include $c['root_path'].'/static/static/inc/mail/source/'.$template.'.php';
				$data['Title'][$v] = $c['sys_email_tpl_title'][$template];
				$data['Content'][$v] = $mail_contents;
			}
		}
		ly200::e_json($data, 1);
	}
	
	public static function system_tpl_edit(){//编辑系统模板
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$template = $p_template;
		$IsUsed = (int)$p_IsUsed?1:0;
		!in_array($template, $c['sys_email_tpl']) && ly200::e_json(manage::get_language('email.select_tpl'), 0);
		$SId = db::get_value('system_email_tpl', "Template='{$template}'", 'SId');
		if (!$SId){//判断数据库是否存在
			db::insert('system_email_tpl', array(
				'Template'	=>	$template,
				'IsUsed'	=>	$IsUsed,
			));
			$SId = db::get_insert_id();
		}else{
			db::update('system_email_tpl', "SId='{$SId}'", array('IsUsed'=>$IsUsed));
		}
		foreach($c['manage']['config']['Language'] as $k=>$v){//加上域名
			$_POST['Content_'.$v]=preg_replace("~([\"|'|(|=])/u_file/~i", '$1'.ly200::get_domain(1).'/u_file/', $_POST['Content_'.$v]);
		}
		manage::database_language_operation('system_email_tpl', "SId='$SId'", array('Title'=>1, 'Content'=>3));
		manage::operation_log('编辑系统模板：'.$template);
		ly200::e_json(manage::get_language('email.edit_success'), 1);
	}
}