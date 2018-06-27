<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class img{
	public static function resize($source_img, $dest_width=200, $dest_height=150, $dest_img=''){	//源图片必须相对于网站根目录
		global $c;
		if(!is_file($c['root_path'].$source_img)){return '';}
		$ext_name=file::get_ext_name($source_img);
		$dest_img=='' && $dest_img=$source_img.".{$dest_width}x{$dest_height}.{$ext_name}";
		if($ext_name=='jpg' || $ext_name=='jpeg'){
			$im=@imagecreatefromjpeg($c['root_path'].$source_img);
		}elseif($ext_name=='png'){
			$im=@imagecreatefrompng($c['root_path'].$source_img);
		}elseif($ext_name=='gif'){
			$im=@imagecreatefromgif($c['root_path'].$source_img);
		}else{
			@copy($c['root_path'].$source_img, $c['root_path'].$dest_img);
			@chmod($c['root_path'].$dest_img, 0775);
			return $return_img;	//返回调整后的文件
		}
		$source_width=@imagesx($im);	//源图片宽
		$source_height=@imagesy($im);	//源图片高
		if($source_width>$dest_width || $source_height>$dest_height){
			if($source_width>$dest_width){
				$width_ratio=$dest_width/$source_width;
				$resize_width=true;
			}
			if($source_height>$dest_height){
				$height_ratio=$dest_height/$source_height;
				$resize_height=true;
			}
			if($resize_width && $resize_height){
				$ratio=min($width_ratio, $height_ratio);
			}elseif($resize_width){
				$ratio=$width_ratio;
			}elseif($resize_height){
				$ratio=$height_ratio;
			}
			$new_width=@ceil($source_width*$ratio);	//优先保持所定义的宽度
			$new_height=@floor($source_height*$ratio);
			$new_im=@imagecreatetruecolor($new_width, $new_height);
			if($ext_name=='png' || $ext_name=='gif'){
				@imagealphablending($new_im, false);
				@imagesavealpha($new_im, true);
			}
			@imagecopyresampled($new_im, $im, 0, 0, 0, 0, $new_width, $new_height, $source_width, $source_height);
			if($ext_name=='jpg' || $ext_name=='jpeg'){
				@imagejpeg($new_im, $c['root_path'].$dest_img, 100);
			}elseif($ext_name=='png'){
				@imagepng($new_im, $c['root_path'].$dest_img);
			}else{
				$bgcolor=@imagecolorallocate($new_im, 0, 0, 0);
				$bgcolor=@imagecolortransparent($new_im, $bgcolor);
				$bgcolor=@imagecolorallocatealpha($new_im, 0, 0, 0, 127);
				@imagefill($new_im, 0, 0, $bgcolor);
				@imagegif($new_im, $c['root_path'].$dest_img);
			}
			@imagedestroy($new_im);
		}else{
			@copy($c['root_path'].$source_img, $c['root_path'].$dest_img);
		}
		@imagedestroy($im);
		@chmod($c['root_path'].$dest_img, 0775);
		return $dest_img;	//返回调整后的文件名
	}
	
	public static function img_width_height($show_img_width, $show_img_height, $picpath, $echo_width_height=1){   //按比例缩小图片
		global $c;
		if(is_file($c['root_path'].$picpath)){
			$img_info=getimagesize($c['root_path'].$picpath); 
			$width=$img_info[0];
			$height=$img_info[1];
			if($width>$show_img_width){
				$ratio=$width/$show_img_width;
				$width=floor($width/$ratio);
				$height=floor($height/$ratio);
			}
			if($height>$show_img_height){
				$ratio=$height/$show_img_height;
				$width=floor($width/$ratio);
				$height=floor($height/$ratio);
			}
			if($echo_width_height==1){
				return "width='$width' height='$height'";
			}else{
				return array($width, $height);
			}
		}
		return '';
	}
	
	public static function img_add_watermark($source_img){	//图片添加水印
		global $c;
		$config_row=str::str_code(db::get_all('config', "GroupId='global'"));
		$cfg=array();
		foreach($config_row as $v){
			$cfg[$v['Variable']]=$v['Value'];
		}
		$watermark=array(
			'allowed_width'		=>	100,	//源图片宽度小于此值不添加水印直接返回
			'allowed_height'	=>	100,	//源图片高度小于此值不添加水印直接返回
			'padding_border'	=>	10,		//水印离图片边缘的像素数
			'img'				=>	$cfg['WatermarkPath'],	//图片水印路径
			'img_alpha'			=>	$cfg['Alpha'],	//图片水印透明度（PNG水印时此参数无效）
			'position'			=>	$cfg['WaterPosition'],	//水印的位置印
		);
		($watermark['position']<1 || $watermark['position']>9) && $watermark['position']=0;
		if(!is_file($c['root_path'].$source_img) || !is_file($c['root_path'].$watermark['img'])){	//源文件不存在则直接返回
			return $source_img;	//返回源文件路径
		}
		$ext_name=file::get_ext_name($source_img);
		if($ext_name=='jpg' || $ext_name=='jpeg'){
			$source_im=@imagecreatefromjpeg($c['root_path'].$source_img);
		}elseif($ext_name=='png'){
			$source_im=@imagecreatefrompng($c['root_path'].$source_img);
		}elseif($ext_name=='gif'){
			$source_im=@imagecreatefromgif($c['root_path'].$source_img);
		}else{
			return $source_img;	//返回源文件路径
		}
		$source_width=@imagesx($source_im);	//源图片宽
		$source_height=@imagesy($source_im);	//源图片高
		if($source_width<$watermark['allowed_width'] || $source_height<$watermark['allowed_height']){
			return $source_img;	//返回源文件路径
		}
		$watermark_img_ext_name=file::get_ext_name($watermark['img']);
		if($watermark_img_ext_name=='jpg' || $watermark_img_ext_name=='jpeg'){
			$watermark_im=@imagecreatefromjpeg($c['root_path'].$watermark['img']);
		}elseif($watermark_img_ext_name=='png'){
			$watermark_im=@imagecreatefrompng($c['root_path'].$watermark['img']);
		}elseif($watermark_img_ext_name=='gif'){
			$watermark_im=@imagecreatefromgif($c['root_path'].$watermark['img']);
		}else{
			return $source_img;	//返回源文件路径
		}
		$watermark_width=@imagesx($watermark_im);	//水印图片宽
		$watermark_height=@imagesy($watermark_im);	//水印图片高
		switch($watermark['position']){	//水印位置
			case 1:	//1为顶端居左
				$posX=$watermark['padding_border'];
				$posY=$watermark['padding_border'];
				break;
			case 2:	//2为顶端居中
				$posX=($source_width-$watermark_width)/2;
				$posY=$watermark['padding_border'];
				break;
			case 3:	//3为顶端居右
				$posX=$source_width-$watermark_width-$watermark['padding_border'];
				$posY=$watermark['padding_border'];
				break;
			case 4:	//4为中部居左
				$posX=$watermark['padding_border'];
				$posY=($source_height-$watermark_height)/2;
				break;
			case 5:	//5为中部居中
				$posX=($source_width-$watermark_width)/2;
				$posY=($source_height-$watermark_height)/2;
				break;
			case 6:	//6为中部居右
				$posX=$source_width-$watermark_width-$watermark['padding_border'];
				$posY=($source_height-$watermark_height)/2;
				break;
			case 7:	//7为底端居左
				$posX=$watermark['padding_border'];
				$posY=$source_height-$watermark_height-$watermark['padding_border'];
				break;
			case 8:	//8为底端居中
				$posX=($source_width-$watermark_width)/2;
				$posY=$source_height-$watermark_height-$watermark['padding_border'];
				break;
			case 9:	//9为底端居右
				$posX=$source_width-$watermark_width-$watermark['padding_border'];
				$posY=$source_height-$watermark_height-$watermark['padding_border'];
				break;
			default:	//随机
				$posX=mt_rand($watermark['padding_border'], $source_width-$watermark_width-$watermark['padding_border']);
				$posY=mt_rand($watermark['padding_border'], $source_height-$watermark_height-$watermark['padding_border']);
		}
		if($ext_name=='png' || $ext_name=='gif'){
			@imagealphablending($source_im, false);
			@imagesavealpha($source_im, true);
		}
		if($watermark_img_ext_name=='png'){
			@imagecopyresampled($source_im, $watermark_im, $posX, $posY, 0, 0, $watermark_width, $watermark_height, $watermark_width, $watermark_height);
		}else{
			@imagecopymerge($source_im, $watermark_im, $posX, $posY, 0, 0, $watermark_width, $watermark_height, $watermark['img_alpha']);	//拷贝水印到目标文件
		}
		@imagedestroy($watermark_im);
		if($ext_name=='jpg' || $ext_name=='jpeg'){
			@imagejpeg($source_im, $c['root_path'].$source_img, 100);
		}elseif($ext_name=='png'){
			@imagepng($source_im, $c['root_path'].$source_img);
		}else{
			@imagegif($source_im, $c['root_path'].$source_img);
		}
		@imagedestroy($source_im);
		return $source_img;	//返回源文件路径
	}
}
?>