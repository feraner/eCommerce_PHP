<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class aliexpress_api{
	/*************************************************************************************************************
	速卖通账号授权信息
	*************************************************************************************************************/
	public static function authorization(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		$where="Platform='aliexpress' and Account='{$p_Account}'";
		if(!db::get_row_count('authorization', $where)){
			db::insert('authorization', array(
					'Platform'	=>	'aliexpress',
					'Name'		=>	$p_Name,
					'Account'	=>	$p_Account,
					'ValidTime'	=>	(int)$p_ValidTime,
					'AccTime'	=>	$c['time']
				)
			);
		}else{
			db::update('authorization', $where, array('ValidTime'=>(int)$p_ValidTime));
		}
		ly200::e_json('', 1);
	}
	
	/*************************************************************************************************************
	同步用户产品分组信息
	*************************************************************************************************************/
	public static function sync_grouplist(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		if(count($p_target)){
			$table='products_aliexpress_grouplist';
			foreach((array)$p_target as $k=>$v){
				$where="Account='$p_Account' and AliexpressGroupId='{$v['groupId']}'";
				$row_count=(int)db::get_row_count($table, $where, 'GId');
				$data=array(
					'Account'				=>	$p_Account,
					'AliexpressGroupId'		=>	$v['groupId'],
					'AliexpressGroupName'	=>	$v['groupName']
				);
				$data=str::str_code(str::str_code($data, 'stripslashes'), 'addslashes');
				$row_count?db::update($table, $where, $data):db::insert($table, $data);
				
				if(@count($v['childGroup'])){//处理子分组
					foreach((array)$v['childGroup'] as $val){
						$w="AliexpressGroupId='{$val['groupId']}'";
						$count=(int)db::get_row_count($table, $w);
						$d=array(
							'Account'				=>	$p_Account,
							'AliexpressGroupId'		=>	$val['groupId'],
							'UpperAliexpressGroupId'=>	$v['groupId'],
							'AliexpressGroupName'	=>	$val['groupName']
						);
						$d=str::str_code(str::str_code($d, 'stripslashes'), 'addslashes');
						$count?db::update($table, $w, $d):db::insert($table, $d);
					}
				}
			}
		}
		ly200::e_json('', 1);
	}
	
	/*************************************************************************************************************
	同步用户运费模板列表信息
	*************************************************************************************************************/
	public static function sync_freight_template(){//用户运费模板列表信息
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		if(@count($p_FreightTemplate)){
			$table='products_aliexpress_freight_template';
			$p_FreightTemplate=str::str_code(str::str_code((array)$p_FreightTemplate, 'stripslashes'), 'addslashes');
			foreach((array)$p_FreightTemplate as $k=>$v){
				$w="Account='{$p_Account}' and templateId='{$v['templateId']}'";
				$row_count=db::get_row_count($table, $w);
				
				$d=array(
					'Account'		=>	$p_Account,
					'templateId'	=>	$v['templateId'],
					'templateName'	=>	$v['templateName'],
					'IsDefault'		=>	$v['default']?1:0
				);
				(int)$row_count?db::update($table, $w, $d):db::insert($table, $d);
			}
		}
		ly200::e_json('', 1);
	}

	/*************************************************************************************************************
	同步服务模板
	*************************************************************************************************************/
	public static function sync_service_template(){//服务模板查询
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		if(@count($p_templateList)){
			$table='products_aliexpress_service_template';
			$p_templateList=str::str_code(str::str_code((array)$p_templateList, 'stripslashes'), 'addslashes');
			foreach((array)$p_templateList as $k=>$v){
				$w="Account='{$p_Account}' and templateId='{$v['id']}'";
				$row_count=db::get_row_count($table, $w);
				
				$d=array(
					'Account'		=>	$p_Account,
					'templateId'	=>	$v['id'],
					'Name'			=>	$v['name']
				);
				(int)$row_count?db::update($table, $w, $d):db::insert($table, $d);
			}
		}
		ly200::e_json('', 1);
	}

	/*************************************************************************************************************
	同步尺码表
	*************************************************************************************************************/
	public static function sync_sizechart_template(){//服务模板查询
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		if(@count($p_SizeChart)){
			$table='products_aliexpress_sizechart_template';
			$p_SizeChart=str::str_code(str::str_code((array)$p_SizeChart, 'stripslashes'), 'addslashes');
			foreach((array)$p_SizeChart as $k=>$v){
				$w="Account='{$p_Account}' and sizechartId='{$v['sizechartId']}'";
				$row_count=db::get_row_count($table, $w);
				
				$d=array(
					'Account'		=>	$p_Account,
					'categoryId'	=>	$p_categoryId,
					'sizechartId'	=>	$v['sizechartId'],
					'IsDefault'		=>	$v['default']?1:0,
					'modelName'		=>	$v['modelName'],
					'name'			=>	$v['name']
				);
				(int)$row_count?db::update($table, $w, $d):db::insert($table, $d);
			}
		}
		ly200::e_json('', 1);
	}


	/*************************************************************************************************************
	同步产品
	*************************************************************************************************************/
	public static function sync_products_goods(){//产品信息同步
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		$table='products_aliexpress';
		$detail_table='products_aliexpress_description';
		$category_table='products_aliexpress_category';
		
		$GoodsInfo=str::str_code(str::str_code(str::json_data(@gzuncompress(base64_decode($p_Goods['info'])), 'decode'), 'stripslashes'), 'addslashes');
		$ImagePath=str::str_code(str::str_code(str::json_data(@gzuncompress(base64_decode($p_Goods['images'])), 'decode'), 'stripslashes'), 'addslashes');
		$GoodsDetail=str::str_code(str::str_code(str::json_data(@gzuncompress(base64_decode($p_Goods['detail'])), 'decode'), 'stripslashes'), 'addslashes');
		if($GoodsInfo['productId'] && $GoodsInfo['productId']==$GoodsDetail['productId']){//速卖通产品ID存在
			$where="productId='{$GoodsInfo['productId']}'";
			if($ProId=db::get_value($table, $where, 'ProId')){
				db::update($table, $where, $GoodsInfo);
			}else{
				//获取产品图片开始
				$resize_ary=array('default', '500x500', '240x240');//$c['manage']['resize_ary']['products'];
				$config_row=str::str_code(db::get_all('config', "GroupId='global' and (Variable='IsWater' or Variable='IsThumbnail')"));
				$cfg=$ImgPath=array();
				foreach($config_row as $v){$cfg[$v['Variable']]=$v['Value'];}
				
				foreach($ImagePath as $k=>$v){
					if($v){
						!substr_count($v,'http') && $v='http:'.$v;
						$ImgPath[]=aliexpress::save_ali_images($v, $GoodsInfo['Account']);
					}
				}
				if($cfg['IsWater']){//更新水印图片
					foreach((array)$ImgPath as $v1){
						$water_ary=array($v1);
						$ext_name=file::get_ext_name($v1);
						@copy($c['root_path'].$v1.".default.{$ext_name}", $c['root_path'].$v1);//覆盖大图
						if($cfg['IsThumbnail']){//缩略图加水印
							img::img_add_watermark($v1);
							$water_ary=array();
						}
						foreach($resize_ary as $v2){
							if($v1=='default') continue;
							$size_w_h=explode('x', $v2);
							$resize_path=img::resize($v, $size_w_h[0], $size_w_h[1]);
						}
						foreach((array)$water_ary as $v2){
							img::img_add_watermark($v2);
						}
					}
				}
				foreach((array)$ImgPath as $v1){
					$ext_name=file::get_ext_name($v1);
					foreach($resize_ary as $v2){
						if(!is_file($c['root_path'].$v1.".{$v2}.{$ext_name}")){
							$size_w_h=explode('x', $v2);
							$resize_path=img::resize($v1, $size_w_h[0], $size_w_h[1]);
						}
					}
					if(!is_file($c['root_path'].$v1.".default.{$ext_name}")){
						@copy($c['root_path'].$v1, $c['root_path'].$v1.".default.{$ext_name}");
					}
				}
				$GoodsInfo['PicPath_0']=$ImgPath[0];
				$GoodsInfo['PicPath_1']=$ImgPath[1];
				$GoodsInfo['PicPath_2']=$ImgPath[2];
				$GoodsInfo['PicPath_3']=$ImgPath[3];
				$GoodsInfo['PicPath_4']=$ImgPath[4];
				$GoodsInfo['PicPath_5']=$ImgPath[5];
				//获取产品图片结束
				
				db::insert($table, $GoodsInfo);
				$ProId=db::get_insert_id();
			}
			
			$GoodsDetail['ProId']=$ProId;
			db::get_row_count($detail_table, $where, 'DId')?db::update($detail_table, $where, $GoodsDetail):db::insert($detail_table, $GoodsDetail);
			//更新任务完成度
			db::update('products_sync_task', "Platform='aliexpress' and TaskId='{$p_TaskId}'", array('TaskStatus'=>1,'CompletionRate'=>(int)$p_CompletionRate));
			//分类不存在，同步产品分类
			!db::get_row_count($category_table, "AliexpressCateId='{$GoodsInfo['categoryId']}'") && aliexpress::sync_category($GoodsInfo['categoryId'], $GoodsInfo['Account']);
		}
		
		ly200::e_json('', 1);
	}

	/*************************************************************************************************************
	同步产品任务完成
	*************************************************************************************************************/
	public static function sync_products_complete(){//产品信息同步完成
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		db::update('products_sync_task', "Platform='{$p_ApiName}' and TaskId='{$p_TaskId}'", array(
				'TaskStatus'	=>	$p_TaskStatus,
				'CompletionRate'=>	$p_CompletionRate
			)
		);
	}
}
?>