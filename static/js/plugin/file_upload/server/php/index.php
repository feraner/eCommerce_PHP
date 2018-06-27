<?php
/*
 * jQuery File Upload Plugin PHP Example
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

//if($_GET['size']){
	include('../../../../../../inc/global.php');
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
	$c['manage']=array_merge($c['manage'],array(
		'upload_dir'	=>	'/u_file/'.date('ym/'),   //网站所有上传的文件保存的基本目录
		'resize_ary'	=>	array(	//各系统的缩略图尺寸
								'products'	=>	array('default', '500x500', '240x240'),
							),
		'sub_save_dir'	=>	array(	//各系统的缩略图存放位置
								'products'	=>	'products/'
							),
		'photo_type'	=>	array(	//图片银行基本系统图片类型
								1	=>	'products',
								2	=>	'editor',
								0	=>	'other',
							)
	));
	include($c['root_path'].'manage/static/do_action/action.php');
	
	$type_ary=array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/ico');
	$_type=$_FILES['Filedata']['type'];
	if(!in_array($_type, $type_ary)){
		$_GET['size']='file_upload';
	}
	
	if($_GET['size']=='photo'){ //图片银行
		include($c['root_path'].'manage/static/do_action/set.php');
		eval("set_module::photo_file_upload();");
	}else{
		eval("action_module::file_upload();");
	}
	exit;
/*}else{
	error_reporting(E_ALL | E_STRICT);
	require('UploadHandler.php');
	$upload_handler=new UploadHandler();
}*/

