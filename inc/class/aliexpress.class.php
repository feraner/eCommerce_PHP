<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class aliexpress{
	/******************************************************** access_token 设置(start) *****************************************************************/
	public static function set_default_authorization($AId=0){//设置默认速卖通账号
		global $c;
		
		$Account=$_SESSION['Manage']['Aliexpress']['Token']['Account'];
		$w="Platform='aliexpress'";
		if(!(int)$AId && db::get_row_count('authorization', "{$w} and Account='{$Account}'")) return $Account;
		
		(int)$AId && $w.=" and AId='$AId'";
		$row=db::get_one('authorization', $w, '*', 'AId asc');
		if(!$row['AId']) return;
		$_SESSION['Manage']['Aliexpress']['Token']['AuthorizationId']=$row['AId'];
		$_SESSION['Manage']['Aliexpress']['Token']['Account']=$row['Account'];
		return $row['Account'];
	}
	/******************************************************** access_token 设置(end) *****************************************************************/

	/******************************************************** api连接设置(start) *****************************************************************/
	public static function api($data){
		global $c;
		$data['ApiKey']='ueeshop_sync';
		$data['Number']=$c['Number'];
		$data['ApiName']='aliexpress';
		$data['Account']=aliexpress::set_default_authorization();
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
	
	
	public static function sync_category($categoryId, $Account='', $update=1){//同步产品分类及所有父级分类
		global $c;
		!$categoryId && ly200::e_json('');
		
		!$Account && $Account=aliexpress::set_default_authorization();
		$data=array(
			'ApiKey'	=>	'ueeshop_sync',
			'Action'	=>	'sync_products_category',
			'Number'	=>	$c['Number'],
			'ApiName'	=>	'aliexpress',
			'Account'	=>	$Account,
			'categoryId'=>	$categoryId,
			'timestamp'	=>	$c['time']
		);
		$data['sign']=ly200::sign($data, $c['ApiKey']);
		$result=str::json_data(ly200::curl($c['sync_url'], $data), 'decode');
		
		if($result['ret']==1 && count($result['msg']['categoryInfo'])){
			$tb='products_aliexpress_category';
			foreach($result['msg']['categoryInfo'] as $v){
				$w="AliexpressCateId='{$v['AliexpressCateId']}'";
				($update && db::get_row_count($tb, $w, 'CateId'))?db::update($tb, $w, $v):db::insert($tb, $v);
			}
		}
	}
	
	public static function save_ali_images($AliImage, $Account){//将速卖通图片保存到本地
		global $c;
		if(!$AliImage) return;//图片为空

		$resize_ary=array('default', '500x500', '240x240');//$c['manage']['resize_ary']['products'];
		$save_dir='/u_file/'.date('ym/').'products/'.date('d/');//$c['manage']['upload_dir'].$c['manage']['sub_save_dir']['products'].date('d/');
		file::mk_dir($save_dir);
		
		$AliName=file::get_base_name($AliImage);//图片名称
		$UeeshopImagesUrl=db::get_value('products_aliexpress_images', "AliName='{$AliName}'", 'UeeshopImagesUrl');//检查此图片是否曾保存到本地
		if(@is_file($c['root_path'].$UeeshopImagesUrl)){//此图片已下载过
			$ext_name=file::get_ext_name($UeeshopImagesUrl);
			$PicPath=$save_dir.str::rand_code().'.'.$ext_name;
			@copy($c['root_path'].$UeeshopImagesUrl.".default.{$ext_name}", $c['root_path'].$PicPath);//复制原图
		}else{//下载图片
			$PicPath=file::write_file($save_dir, str::rand_code().'.jpg', ly200::curl($AliImage));
			@copy($c['root_path'].$PicPath, $c['root_path'].$PicPath.".default.jpg");//保留原图
			db::insert('products_aliexpress_images', array(
					'Account'				=>	$Account,
					'AliName'				=>	$AliName,
					'AliexpressImageUrl'	=>	$AliImage,
					'UeeName'				=>	@str_replace('/', '-', trim($PicPath, '/')),
					'UeeshopImagesUrl'		=>	$PicPath
				)
			);
		}
		
		return $PicPath;
	}
	
	public static function remove_aliexpress_images($Images){//删除速卖通图片记录表
		global $c;
		$UeeName=@str_replace('/', '-', trim($Images, '/'));
		db::delete('products_aliexpress_images', "UeeName='{$UeeName}'");
	}
}
?>