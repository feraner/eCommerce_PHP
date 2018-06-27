<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class amazon_api{
	/*************************************************************************************************************
	同步产品
	*************************************************************************************************************/
	public static function sync_products_goods(){//产品信息同步
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$GoodsInfo=str::str_code(str::str_code(str::json_data(@gzuncompress(base64_decode($p_Goods)), 'decode'), 'stripslashes'), 'addslashes');
		/*
		ob_start();
		print_r($_POST);
		echo "\r\n\r\n";
		print_r($GoodsInfo);
		$log=ob_get_contents();
		ob_end_clean();
		file::write_file('/logs/amazon/'.date('ym/d/', $c['time']), $p_TaskId.'.txt', $log);	//把返回数据写入文件
		*/

		if($GoodsInfo['asin1']){//亚马逊产品ID存在
			$data=array();
			$table='products_amazon';
			$where="`asin1`='{$GoodsInfo['asin1']}'";
			foreach($GoodsInfo as $k=>$v){$data["`{$k}`"]=str::str_code(str::str_code($v, 'stripslashes'), 'addslashes');}
			
			if($ProId=db::get_value($table, $where, 'ProId')){
				db::update($table, $where, $data);
			}else{
				//获取产品图片开始
				$ImgPath='';
				$p_Image && $ImgPath=amazon::save_amazon_images($p_Image, $GoodsInfo['MerchantId']);
				$data['`image-url`']=$ImgPath;
				//获取产品图片结束
				db::insert($table, $data);
			}
			
			//更新任务完成度
			db::update('products_sync_task', "Platform='amazon' and TaskId='{$p_TaskId}'", array('TaskStatus'=>1,'CompletionRate'=>(int)$p_CompletionRate));
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