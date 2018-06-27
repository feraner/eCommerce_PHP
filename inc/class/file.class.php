<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class file{
	public static function mk_dir($dir){	//建立目录
		global $c;
		if($dir=='/' || is_dir($c['root_path'].$dir)){return $dir;}
		$arr_dir=@explode('/', $dir);
		for($i=0; $i<count($arr_dir); $i++){
			$base_dir=$c['root_path'];
			for($j=0; $j<=$i; $j++){
				$base_dir.=$arr_dir[$j].'/';
			}
			!is_dir($base_dir) && @mkdir($base_dir) && @chmod($base_dir, 0775);
		}
		return $dir;
	}
	
	public static function get_ext_name($file=''){   //返回文件后辍名（小写）
		return strtolower(pathinfo($file, PATHINFO_EXTENSION));
	}
	
	public static function get_base_name($file=''){   //返回文件名
		return pathinfo($file, PATHINFO_BASENAME);
	}
	
	public static function file_upload($up_file_name, $save_dir, $is_ckeditor=0){	//上传文件
		global $c;
		/*
		//【ueeshop】【2018.01.26】
		if(substr_count(strtolower($up_file_name['name']), 'php')){
			file::del_file($up_file_name['tmp_name']);
			return '';
		}else{
			$ext_name=file::get_ext_name($up_file_name['name']);
			$save_name=$save_dir.str::rand_code().'.'.$ext_name;
			$save_path=$c['root_path'].$save_name;
			move_uploaded_file($up_file_name['tmp_name'], $save_path);
			@chmod($save_path, 0775);
			return is_file($save_path)?$save_name:'';
		}
		*/
		$default_ary==array('php','htm','js','css','xml');
		$ckeditor_ary=array('flv','swf');//不允许上传php、htm、js、css、xml、flv、swf
		$filter_ary=$is_ckeditor?$default_ary:@array_merge($default_ary, $ckeditor_ary);
		
		$lower_file_name=strtolower($up_file_name['name']);
		foreach((array)$filter_ary as $val){
			if(substr_count($lower_file_name, $val)){
				file::del_file($up_file_name['tmp_name']);
				return '';
			}
		}

		file::mk_dir($save_dir);
		$ext_name=file::get_ext_name($up_file_name['name']);
		$save_name=$save_dir.str::rand_code().'.'.$ext_name;
		$save_path=$c['root_path'].$save_name;
		//move_uploaded_file($up_file_name['tmp_name'], $save_path);
		move_uploaded_file(str_replace('\\\\', '\\', $up_file_name['tmp_name']), $save_path);
		@chmod($save_path, 0775);
		return is_file($save_path)?$save_name:'';
	}
	
	public static function file_upload_swf($save_dir, $resize_ary='', $AddPhoto=true, $is_water=0){
		global $c;
		$status=array('status'=>-1);
		if($filepath=file::file_upload($_FILES['Filedata'], $save_dir)){
			
			$water_ary=array();
			if($is_water && $c['manage']['config']['IsWater']) $water_ary[]=$filepath;
			$size=$_GET['size']?$_GET['size']:$_POST['size'];
			if($resize_ary){
				if(array_key_exists($size, $resize_ary)){
					if(in_array('default', $resize_ary[$size])){//保存不加水印的原图
						$ext_name=file::get_ext_name($filepath);
						@copy($c['root_path'].$filepath, $c['root_path'].$filepath.".default.{$ext_name}");
						@chmod($c['root_path'].$filepath.".default.{$ext_name}", 0775);
					}
					foreach((array)$resize_ary[$size] as $v){
						if($v=='default') continue;
						$size_w_h=explode('x', $v);
						$resize_path=img::resize($filepath, $size_w_h[0], $size_w_h[1]);
						if($is_water && $c['manage']['config']['IsWater'] && $c['manage']['config']['IsThumbnail']){//缩略图加水印
							$water_ary[]=$resize_path;
						}
					}
				}
			}
			foreach((array)$water_ary as $v){
				img::img_add_watermark($v);
			}
			$name=substr($_FILES['Filedata']['name'], 0, strrpos($_FILES['Filedata']['name'], '.'));
			$status=array(
				'status'	=>	1,
				'filepath'	=>	$filepath,
				'name'      =>  $name,
			);
			if($AddPhoto){//是否添加到图片银行
				$_img=file::photo_add_item($filepath, $name, $size);
				$size!='products' && $status['filepath']=$_img;//除产品外，全站图片使用图片银行的
			}
			if($_FILES['Filedata']){//新版
				$_ary=array();
				$_ary['files'][0]=array(
					'name'			=>	$name,
					'size'			=>	$_FILES['Filedata']['size'],
					'type'			=>	$_FILES['Filedata']['type'],
					'url'			=>	$status['filepath'],
					'thumbnailUrl'	=>	$status['filepath'],
					'deleteUrl'		=>	'/static/js/plugin/file_upload/server/php/index.php?file='.$status['filepath'],
					'deleteType'	=>	'DELETE'
				);
				$status=$_ary;//覆盖
			}
		}
		return str::json_data($status);
	}
	
	public static function file_upload_ckeditor($save_dir){
		global $c;
		$config=array(
			'file_type'			=>	array('attach', 'img', 'flash'), //允许上传的文件类型
			'img_allow_type'	=>	array('jpg', 'jpeg', 'bmp', 'gif', 'png'),	//图片允许上传的格式
			'flash_allow_type'	=>	array('swf', 'flv')	//flash允许上传的格式
		);
		$file_type=$_GET['file_type'];
		!in_array($file_type, $config['file_type']) && $file_type=$config['file_type'][0];
		$fn=(int)$_GET['CKEditorFuncNum'];
		if(in_array($file_type, array('img', 'flash')) && !in_array(file::get_ext_name($_FILES['upload']['name']), $config[$file_type.'_allow_type'])){
			exit("<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction('$fn', '', '".manage::get_language('ckeditor.file_type_err')."');</script>");
		}elseif($filepath=file::file_upload($_FILES['upload'], $save_dir, 1)){
			exit("<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction('$fn', '$filepath', '".manage::get_language('ckeditor.upload_success')."');</script>");
		}
		exit("<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction('$fn', '', '".manage::get_language('ckeditor.upload_fail')."');</script>");
	}
	
	public static function photo_tmp_upload($img, $save_dir, $resize_ary=array()){//图片上传文件处理
		global $c;
		if(manage::check_tmp($c['root_path'].$img)){//检查图片是否在临时文件夹
			$base_name=file::get_base_name($img);
			if($resize_ary){
				$ext_name=file::get_ext_name($img);
				foreach($resize_ary as $v){
					@rename($c['root_path'].$img.".{$v}.{$ext_name}", $c['root_path'].$save_dir."{$base_name}.{$v}.{$ext_name}");//移动文件
				}
			}
			@rename($c['root_path'].$img, $c['root_path'].$save_dir.$base_name);//移动文件
			$ImgPath=$save_dir.$base_name;
		}elseif(is_file($c['root_path'].$img)){//检查图片是否保存成功
			$ImgPath=$img;
		}else{
			$ImgPath='';
		}
		return $ImgPath;
	}
	
	/*
	** $Img:图片路径
	** $Name:图片名称
	** $IsSystem: 系统图片类型，等于 0 即该图片又图片银行管理上传，非系统图片
	** $CateId: 图片银行分类ID号
	*/
	public static function photo_add_item($Img, $Name='', $IsSystem=0, $CateId=0){//图片银行图片添加
		global $c;
		$PicPath=$c['root_path'].ltrim($Img, '/');
		if(@is_file($PicPath)){
			$ext_name=file::get_ext_name($PicPath);
			$save_dir=$c['manage']['upload_dir'].'photo/';
			file::mk_dir($save_dir);
			$Img=$save_dir.str::rand_code().'.'.$ext_name;
			$Path=$c['root_path'].ltrim($Img, '/');
			@copy($PicPath, $Path);//复制文件
			if(!$CateId && !in_array($IsSystem, (array)$c['manage']['photo_type'])){
				//不属于分类,也非基本系统类型的图片，全部定义为其他
				$IsSystem='other';
			}
			$data=array(
				'CateId'	=>	$IsSystem?0:$CateId,//图片银行分类ID
				'Name'		=>	@addslashes(stripslashes($Name)),
				'PicPath'	=>	$Img,
				'IsSystem'	=>	$IsSystem,
			);
			db::insert('photo', $data);
			return $Img;
		}
	}
	
	public static function write_file($save_dir, $save_name, $contents, $efbbbf=0){	//写文件
		global $c;
		if(substr_count(strtolower($save_name), 'php')){
			return '';
		}else{
			file::mk_dir($save_dir);
			$fp=@fopen($c['root_path'].$save_dir.$save_name, 'w');
			@flock($fp, LOCK_EX);
			@fwrite($fp, ($efbbbf==1?pack('H*', 'EFBBBF'):'').$contents);
			@flock($fp, LOCK_UN);
			@fclose($fp);
			@chmod($c['root_path'].$save_dir.$save_name, 0775);
			return $save_dir.$save_name;
		}
	}

	public static function del_file($file){	//删除文件
		global $c;
		$file=$c['root_path'].$file;
		if(!$file || !@is_file($file)){return false;}
		@unlink($file);
	}

	public static function del_dir($dir){	//删除文件夹
		global $c;
		realpath($c['root_path'])==realpath($c['root_path'].$dir) && exit;
		@substr_count($dir,$c['root_path']) && $dir=@str_replace($c['root_path'], '', $dir);//如果文件夹已带绝对路径，则不再重复添加绝对路径
		$handle=@opendir($c['root_path'].$dir);
		while($file=@readdir($handle)){
			if($file!='.' && $file!='..'){
				$fullpath=$dir.'/'.$file;
				if(!@is_dir($c['root_path'].$fullpath)){
					@unlink($c['root_path'].$fullpath);
				}else{
					file::del_dir($fullpath);
				}
			}
		}
		@closedir($handle);
		return @rmdir($c['root_path'].$dir);
	}

	public static function down_file($filepath, $save_name=''){    //下载文件
		global $c;
		$filepath=$c['root_path'].$filepath;
		!is_file($filepath) && exit();
		$save_name=='' && $save_name=basename($filepath);
		$file_size=filesize($filepath);
		$file_handle=fopen($filepath, 'r');
		ob_clean();//清理缓存，可避免下载文件后，出现乱码的情况
		header("Content-type: application/octet-stream; name=\"$save_name\"\n");
		header("Accept-Ranges: bytes\n");
		header("Content-Length: $file_size\n");
		header("Content-Disposition: attachment; filename=\"$save_name\"\n\n");
		while(!feof($file_handle)){
			echo fread($file_handle, 1024*100);
		}
		fclose($file_handle);
	}
	
	public static function check_cache($filepath, $isThemes=1){	//生成缓存文件
		global $c;
		$file=($isThemes==1?ly200::get_cache_path($c['theme']):'').$filepath;
		$time=@filemtime($file);//文件生成时间
		!$time && $time=0;
		$set_cache=$c['time']-$time-$c['cache_timeout'];	//当前时间 - 文件生成时间 - 自动生成静态文件时间间
		if($set_cache>0 || !@is_file($file)){
			return false;
		}else{
			return true;
		}
	}
}
?>