<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class amazon{
	/******************************************************** access_token 设置(start) *****************************************************************/
	public static function set_default_authorization($AId=0){//设置默认速卖通账号
		global $c;
		
		$Account=$_SESSION['Manage']['Amazon']['Account'];
		$w="Platform='amazon'";
		if(!(int)$AId && db::get_row_count('authorization', "{$w} and Account='{$Account}'")) return $Account;
		
		(int)$AId && $w.=" and AId='$AId'";
		$row=db::get_one('authorization', $w, "AId,Account,Name", 'AId asc');
		if(!$row['AId']) return;
		$_SESSION['Manage']['Amazon']=$row;
		return $row['Account'];
	}
	/******************************************************** access_token 设置(end) *****************************************************************/

	/******************************************************** api连接设置(start) *****************************************************************/
	public static function api($data){
		global $c;
		$data['ApiKey']='ueeshop_sync';
		$data['Number']=$c['Number'];
		$data['ApiName']='amazon';
		$data['Account']=$_SESSION['Manage']['Amazon']['Account'];
		$data['timestamp']=$c['time'];
		$data=str::str_code($data, 'trim');
		$data['sign']=ly200::sign($data, $c['ApiKey']);

		$curl_opt=array(
			CURLOPT_CONNECTTIMEOUT	=>	60,
			CURLOPT_TIMEOUT			=>	60
		);
		$result=ly200::curl($c['sync_url'], $data, '', $curl_opt);//防止连接超时出现502
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
	/******************************************************** api连接设置(end) *****************************************************************/
	
	
	
	public static function save_amazon_images($AmazonImage, $MerchantId){//将速卖通图片保存到本地
		global $c;
		if(!$AmazonImage) return;//图片为空

		$resize_ary=array('default', '500x500', '240x240');			//$c['manage']['resize_ary']['products'];
		$save_dir='/u_file/'.date('ym/').'products/'.date('d/');	//$c['manage']['upload_dir'].$c['manage']['sub_save_dir']['products'].date('d/');
		file::mk_dir($save_dir);
		
		//原图链接
		$AmazonImage=preg_replace('/\._[0-9a-zA-Z]+_\./', '.', $AmazonImage);
		//图片名称
		$AmazonName=file::get_base_name($AmazonImage);
		if(substr_count($AmazonName, 'no-image')) return '';//亚马逊上没有上传图片
		
		//图片后缀
		$ext_name=file::get_ext_name($AmazonImage);
		//下载图片
		$PicPath=file::write_file($save_dir, str::rand_code().'.'.$ext_name, ly200::curl($AmazonImage));
		//检查是否需要生成缩略图
		$config_row=str::str_code(db::get_all('config', "GroupId='global' and (Variable='IsWater' or Variable='IsThumbnail')"));
		$cfg=array();
		foreach($config_row as $v){$cfg[$v['Variable']]=$v['Value'];}
			
		@copy($c['root_path'].$PicPath, $c['root_path'].$PicPath.".default.{$ext_name}");//保留原图
		if($cfg['IsWater']){//更新水印图片
			$water_ary=array($PicPath);
			if($cfg['IsThumbnail']){//缩略图加水印
				img::img_add_watermark($PicPath);
				$water_ary=array();
			}
			foreach($resize_ary as $v){
				if($PicPath=='default') continue;
				$size_w_h=explode('x', $v);
				$resize_path=img::resize($PicPath, $size_w_h[0], $size_w_h[1]);
			}
			foreach((array)$water_ary as $v){
				img::img_add_watermark($v);
			}
		}
		
		foreach($resize_ary as $v){
			if(!is_file($c['root_path'].$PicPath.".{$v}.{$ext_name}")){
				$size_w_h=explode('x', $v);
				$resize_path=img::resize($PicPath, $size_w_h[0], $size_w_h[1]);
			}
		}
		if(!is_file($c['root_path'].$PicPath.".default.{$ext_name}")){
			@copy($c['root_path'].$PicPath, $c['root_path'].$PicPath.".default.{$ext_name}");
		}
		
		return $PicPath;
	}
}
?>