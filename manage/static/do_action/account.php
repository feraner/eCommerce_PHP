<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class account_module{
	/*********************************************************************************************************************************************************/
	public static function login(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		$data=array(
			'Action'	=>	'ueeshop_web_manage_login',
			'UserName'	=>	$p_UserName,
			'Password'	=>	ly200::password(trim($p_Password)),
			'Ip'		=>	ly200::get_ip()
		);
		$result=ly200::api($data, $c['ApiKey'], $c['api_url']);

		if($result['ret']==1 && $result['msg'] && @is_array($result['msg'])){
			$userinfo=$result['msg'];
			if($userinfo['Locked']==1){	//账号被锁定
				ly200::e_json(manage::language('{/account.locking/}'));
			}else{
				$_SESSION['Manage']=@is_array($_SESSION['Manage'])?@array_merge($_SESSION['Manage'], $userinfo):$userinfo;
				//---------------------------------非超级管理员加载权限(Start)--------------------------------------
				if($userinfo['GroupId']!=1){
					$permit_row=db::get_all('manage_permit', "UserName='{$userinfo['UserName']}'");
					foreach($permit_row as $v){
						$_SESSION['Manage']['Permit'][$v['Module']]=array(
							0=>$v['Permit'],
							1=>@json_decode(stripslashes($v['DetailsPermit']), true)
						);
					}
				}
				//---------------------------------非超级管理员加载权限(End)--------------------------------------
				$_SESSION['Manage']['GroupId']==3 && $_SESSION['Manage']['SalesId']=db::get_value('manage_sales', "UserName='{$_SESSION['Manage']['UserName']}'", 'SalesId');
				manage::operation_log("管理员登录【{$p_UserName}】");
				ly200::e_json('', 1);
			}
		}else{
			ly200::e_json(manage::language('{/account.error/}'));
		}
	}
	/*********************************************************************************************************************************************************/
	
	public static function logout(){
		global $c;
		$log="退出登录【{$_SESSION['Manage']['UserName']}】";
		manage::operation_log($log);
		unset($_SESSION['Manage']);
		js::location('./');
	}
	
	public static function ueeshop_web_get_data(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_key=(int)$p_key;
		$day_ary=array(30,1,2,7,15,30);
		$p_day=$day_ary[$p_key];
		!$p_day && $p_key=0 && $p_day=$day_ary[0];
		
		if(substr_count($_SERVER['HTTP_HOST'], '.vgcart.com') || substr_count($_SERVER['HTTP_HOST'], '.ly200.net')){//substr_count($_SERVER['HTTP_HOST'], '.myueeshop.com') || substr_count($_SERVER['HTTP_HOST'], '.ueeshopweb.com') || 
			$data_object=@file_get_contents($c['root_path'].'/inc/file/analytics.json');
			$analytics_row=str::json_data($data_object, 'decode');
		}else{
			$analytics_row=ly200::ueeshop_web_get_data();//加载统计json
		}
		
		$data=array(
			'key'	=>	$p_key,
			'ip'	=>	(int)$analytics_row['Analytics'][0][$p_day]['Ip'],
			'pv'	=>	(int)$analytics_row['Analytics'][0][$p_day]['Pv']
		);
		
		$area_ary=$source_ary=array();
		$other=0;
		if((int)$data['ip']){//国家地区分布
			foreach((array)$analytics_row['Analytics'][1][$p_day] as $k=>$v){
				if(sprintf('%01.4f', $v/$data['ip'])<0.001){
					$other+=$v;
				}else{
					$area_ary[]=array(
						'country'	=>	$k,
						'pv'		=>	$v,
						'rate'		=>	sprintf('%01.4f', $v/$data['ip'])
					);
				}
			}
			$area_ary[]=array(
				'country'	=>	'其他',
				'pv'		=>	$other,
				'rate'		=>	sprintf('%01.4f', $other/$data['ip'])
			);
		}
		if((int)$data['pv']){//搜索引擎分布
			foreach((array)$analytics_row['Analytics'][2][$p_day] as $k=>$v){
				$source_ary[]=array(
					'engine'	=>	$k,
					'pv'		=>	$v,
					'rate'		=>	sprintf('%01.4f', $v/$data['pv'])
				);
			}
		}
		$data['area']=$area_ary;
		$data['source']=$source_ary;
		
		//$StartTime=$c['time']-$p_day*86400;
		$StartTime=$c['time']-($p_day-1)*86400;
		$StartTime=@strtotime(@date('Y/m/d', $StartTime).' 00:00:00');
		$EndTime=$c['time'];
		$p_key==2 && $EndTime=@strtotime(@date('Y/m/d', $c['time']).' 00:00:00');//昨天
		$where=$p_key==5?1:"OrderTime>{$StartTime} and OrderTime<{$EndTime}";
		/* 总订单数 */
		$order_total_count=(int)db::get_row_count('orders', $where);
		/* 总销售额 */
		$order_total_row=db::get_all('orders', $where." and OrderStatus in(4,5,6)", 'sum((ProductPrice*((100-Discount)/100)*(if(UserDiscount>0, UserDiscount, 100)/100)*(1-CouponDiscount))+ShippingPrice+PayAdditionalFee+ShippingInsurancePrice) as TotalPrice');
		$order_total_price=0;
		foreach($order_total_row as $k=>$v){
			$order_total_price+=sprintf('%0d', $v['TotalPrice']);
		}
		$s_order_total_price=$order_total_price;
		$order_total_price>9999 && $s_order_total_price=substr($order_total_price, 0, -3).'k';
		
		$data['order_total_count']=$order_total_count;
		$data['order_total_price']=$c['manage']['currency_symbol'].$order_total_price;
		$data['s_order_total_price']=$c['manage']['currency_symbol'].$s_order_total_price;
		
		ly200::e_json($data, 1);
	}
	
	public static function ueeshop_web_get_service_data(){
		global $c;
		
		$analytics_row=ly200::ueeshop_web_get_data();//加载统计json
		$data=array(
			'trial'		=>	(int)$analytics_row['IsCustomer'],
			'spread'	=>	$analytics_row['Spread'],
			'server'	=>	$analytics_row['Server'],
			'expired'	=>	$analytics_row['ExpiredTime'],
			'backup'	=>	$analytics_row['BackupTime'],
			'service'	=>	$analytics_row['Service'],
		);
		
		ly200::e_json($data, 1);
	}
}
?>