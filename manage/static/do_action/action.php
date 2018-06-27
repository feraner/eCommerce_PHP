<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class action_module{
	public static function file_upload(){
		global $c;
		/*
		$size=$_POST['size'];
		if($size=='file_upload'){//文件上传
			$status=array('status'=>-1);
			if($filepath=file::file_upload($_FILES['Filedata'], $c['tmp_dir'].'file/')){
				$name=substr($_FILES['Filedata']['name'], 0, strrpos($_FILES['Filedata']['name'], '.'));
				$status=array(
					'status'	=>	1,
					'filepath'	=>	$filepath,
					'name'      =>  $name
				);
			}
			exit(str::json_data($status));
		}else{//图片上传
			$resize_ary=$c['manage']['resize_ary'];
			$is_water=0;
			if(($c['manage']['config']['IsWaterPro'] && $size=='editor') || $size=='products') $is_water=1;
			exit(file::file_upload_swf($c['tmp_dir'].'photo/', $resize_ary, true, $is_water));//暂时把图片都保存到临时文件夹里
		}
		*/
		$size=$_GET['size']?$_GET['size']:$_POST['size'];
		if($size=='file_upload'){//文件上传
			$status=array('status'=>-1);
			if($filepath=file::file_upload($_FILES['Filedata'], $c['tmp_dir'].'file/')){
				$name=substr($_FILES['Filedata']['name'], 0, strrpos($_FILES['Filedata']['name'], '.'));
				$status['files'][0]=array(
					'name'			=>	$name,
					'size'			=>	$$_FILES['Filedata']['size'],
					'type'			=>	$$_FILES['Filedata']['type'],
					'url'			=>	$filepath,
					'thumbnailUrl'	=>	$filepath,
					'deleteUrl'		=>	'',
					'deleteType'	=>	'DELETE'
				);
			}
			exit(str::json_data($status));
		}else{//图片上传
			$resize_ary=$c['manage']['resize_ary'];
			$is_water=0;
			if(($c['manage']['config']['IsWaterPro'] && $size=='editor') || $size=='products') $is_water=1;
			exit(file::file_upload_swf($c['tmp_dir'].'photo/', $resize_ary, true, $is_water));//暂时把图片都保存到临时文件夹里
		}
	}
	
	public static function file_upload_plugin(){
		global $c;
		$type_ary=array('image/jpg', 'image/jpeg', 'image/gif', 'image/png', 'image/bmp', 'image/ico');
		$_type=$_FILES['Filedata']['type'];
		if(!in_array($_type, $type_ary)){
			$_GET['size']='file_upload';
		}
		
		if($_GET['size']=='photo'){ //图片银行
			exit(file::file_upload_swf($c['tmp_dir'].'photo/', '', false));
		}else{
			self::file_upload();
		}
	}
	
	public static function file_upload_ckeditor(){
		global $c;
		file::file_upload_ckeditor($c['manage']['upload_dir'].'file/');
	}
	
	public static function file_del(){	//删除单个图片
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		if(is_file($c['root_path'].$g_PicPath)){
			file::del_file($g_PicPath);
		}
		ly200::e_json('', 1);
	}
	
	public static function file_clear_cache(){//清空tmp/cache缓存
		global $c;
		file::del_dir($c['tmp_dir'].'cache/');
		file::del_dir($c['tmp_dir'].'manage/');
		ly200::e_json('', 1);
	}
}
?>