<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

basename($_SERVER['PHP_SELF'])==basename(__FILE__) && v_code::create($_GET['name'], (int)$_GET['length'], $_GET['charset']);

class v_code{
	private static $v_code_cfg=array(
		'width' 		=>	30,	//每个字所占的宽度
		'height'		=>	30,	//图片高度
		'noise_count'	=>	30,	//杂点数量
		'line_count'	=>	3,	//干扰线数量
		'ttf'			=>	'../file/simfang.ttf',	//随机验证码使用的字体
		'length'		=>	4,	//随机验证码默认长度
		'charset'		=>	'en',	//随机验证码默认使用的字符集
		'char_table'	=>	array(
								'en'	=>	'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
								'num'	=>	'0123456789',
								'mix'	=>	'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'
							)
	);
	
	public static function create($name='ly200', $length=4, $charset='en'){
		if(basename($_SERVER['PHP_SELF'])==basename(__FILE__)){
			self::create_run($name, $length, $charset);
		}else{
			$img_url="/inc/class/v_code.class.php?name=$name&length=$length&charset=$charset";
			return "<img src='$img_url' onclick='this.src=\"$img_url&r=\"+Math.random();' style='cursor:pointer;'>";
		}
	}
	
	private static function create_run($name, $length, $charset){
		$name=='' && exit();
		($length<1 || $length>6) && $length=self::$v_code_cfg['length'];
		!array_key_exists($charset, self::$v_code_cfg['char_table']) && $charset=self::$v_code_cfg['charset'];
		$img_width=$length*self::$v_code_cfg['width'];	//图片宽度
		$img_height=self::$v_code_cfg['height'];	//图片高度
		$image=imagecreate($img_width+2, $img_height+2);
		imagecolorallocate($image, 0xff, 0xff, 0xff);		//设定背景颜色
		imagerectangle($image, 0, 0, $img_width+1, $img_height+1, imagecolorallocate($image, 0xcc, 0xcc, 0xcc));   //加个边框
		for($i=0; $i<self::$v_code_cfg['noise_count']; $i++){	//加入杂点
			imagesetpixel($image, mt_rand(1, $img_width), mt_rand(1, $img_height), self::rand_color($image));
		}
		for($i=0; $i<self::$v_code_cfg['line_count']; $i++){	//加入干扰线
			imageline($image, mt_rand(1, $img_width), mt_rand(1, $img_height), mt_rand(1, $img_width), mt_rand(1, $img_height), self::rand_color($image));
		}
		$posX=self::$v_code_cfg['width']/2-10;	//posX，posY代表了首字符的基本点，即第一个字符的左下角
		$posY=self::$v_code_cfg['width']/2+10;
		@session_start();
		$_SESSION['Ueeshop']['VCode'][md5($name)]='';
		for($i=0; $i<$length; $i++){
			$code=substr(self::$v_code_cfg['char_table'][$charset], mt_rand(0, strlen(self::$v_code_cfg['char_table'][$charset])-1), 1);
			imagettftext($image, mt_rand(16, 22), mt_rand(-30, 30), $posX, $posY, self::rand_color($image), self::$v_code_cfg['ttf'], $code);
			$posX+=self::$v_code_cfg['width'];
			$_SESSION['Ueeshop']['VCode'][md5($name)].=$code;
		}
		header('Content-type: image/png');
		imagepng($image);
		imagedestroy($image);
	}
	
	private static function rand_color($image){	
		$R=mt_rand(0, 200);
		$G=mt_rand(0, 200);
		$B=mt_rand(0, 200);
		//($R>200 && $G>200 && $B>200) && $R=$G=$B=0;
		return imagecolorallocate($image, $R, $G, $B);
	}
}
?>