<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class mta_module{
	/*
	* 流量部分(products)
	* 1. 流量统计(api_get_data)、流量来源(api_get_data)、流量分布(api_get_data)、漏斗分析(api_get_data)
	* 2. 流量转化率(get_visits_conversion_data)
	*/
	public static function api_get_data(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		ksort($_POST);
		$save_dir=$c['tmp_dir'].'manage/';
		$filename=$p_Action.'-'.md5(implode('&', $_POST)).'.json';
		//if(!file::check_cache($c['root_path'].$save_dir.$filename, 0)){//文件是否存在、是否到更新时间
			unset($_POST['do_action']);
			$result=ly200::api($_POST, $c['ApiKey'], $c['api_url']);
			//if($result['ret']==1){
				@file::write_file($save_dir, $filename, str::json_data($result));
			//}else{
				//ly200::e_json('', 0);
			//}
		//}
		echo @file_get_contents($c['root_path'].$save_dir.$filename);
		exit;
	}

	public static function get_visits_conversion_data(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');

		$p_Compare=(int)$p_Compare;
		$p_Terminal=(int)$p_Terminal;
		$p_TimeS=$p_TimeS!=''?$p_TimeS:0;
		$p_Compare && $p_TimeE=$p_TimeE!=''?$p_TimeE:0;
		
		$reset_data=array(
			'uv'				=>	0,
			'addtocart'			=>	0,
			'addtocart_ratio'	=>	0,
			'placeorder'		=>	0,
			'placeorder_ratio'	=>	0,
			'complete'			=>	0,
			'complete_ratio'	=>	0
		);
		$return_data=array(
			'ratio'				=>	$reset_data,
			'enter_directly'	=>	$reset_data,
			'share_platform'	=>	$reset_data,
			'search_engine'		=>	$reset_data,
			'other'				=>	$reset_data,
		);
		$p_Compare && $return_data['compare']=$return_data;
		
		$result=ly200::api($_POST, $c['ApiKey'], $c['api_url']);
		
		$ary=array('ratio', 'enter_directly', 'share_platform', 'search_engine', 'other');
		if($result['msg']['total']['Uv']){//有流量才进入统计
			foreach($ary as $value){
				$key=($value=='ratio'?'total':$value);				
				$return_data[$value]['uv']=$result['msg'][$key]['Uv'];
				
				if($return_data[$value]['uv']){//有流量才进入统计
					$addtocart=(int)$result['msg'][$key]['1']['TotalVisitors']+(int)$result['msg'][$key]['2']['TotalVisitors']+(int)$result['msg'][$key]['3']['TotalVisitors'];
					$return_data[$value]['addtocart']=$addtocart;
					$return_data[$value]['addtocart_ratio']=$return_data[$value]['uv']?(int)($addtocart*100/$return_data[$value]['uv']):0;
					
					$return_data[$value]['placeorder']=(int)$result['msg'][$key]['5']['TotalVisitors'];
					$return_data[$value]['placeorder_ratio']=$return_data[$value]['uv']?(int)($return_data[$value]['placeorder']*100/$return_data[$value]['uv']):0;
	
					$where=1;
					$value=='enter_directly' && $where='RefererId=99';
					$value=='share_platform' && $where='RefererId=1';
					$value=='search_engine' && $where='RefererId=0';
					$value=='other' && $where='RefererId=100';
					$time_s=where::time($p_TimeS, '', !in_array($p_TimeS, array(0,-1)));
					$where="$where and OrderTime between {$time_s[0]} and {$time_s[1]}";
					$where.=" and OrderStatus in(4,5,6)";
					$return_data[$value]['complete']=(int)db::get_row_count('orders', $where);
					$return_data[$value]['complete_ratio']=$return_data[$value]['uv']?(int)($return_data[$value]['complete']*100/$return_data[$value]['uv']):0;
				}
			}
		}
		
		if($p_Compare && $result['msg']['compare']){
			foreach($ary as $value){
				$compare=array();
				$key=($value=='ratio'?'total':$value);				
				$compare['uv']=$result['msg']['compare'][$key]['Uv'];
				
				if($compare['uv']){//有流量才进入统计
					$addtocart=(int)$result['msg']['compare'][$key]['1']['TotalVisitors']+(int)$result['msg']['compare'][$key]['2']['TotalVisitors']+(int)$result['msg']['compare'][$key]['3']['TotalVisitors'];
					$compare['addtocart']=$addtocart;
					$compare['addtocart_ratio']=$compare['uv']?(int)($addtocart*100/$compare['uv']):0;
					
					$compare['placeorder']=(int)$result['msg']['compare'][$key]['5']['TotalVisitors'];
					$compare['placeorder_ratio']=$compare['uv']?(int)($compare['placeorder']*100/$compare['uv']):0;
	
					$where=1;
					$value=='enter_directly' && $where='RefererId=99';
					$value=='share_platform' && $where='RefererId=1';
					$value=='search_engine' && $where='RefererId=0';
					$value=='other' && $where='RefererId=100';
					$time_e=where::time($p_TimeE, '', !in_array($p_TimeS, array(0,-1)));
					$where="$where and OrderTime between {$time_e[0]} and {$time_e[1]}";
					$where.=" and OrderStatus in(4,5,6)";
					$compare['complete']=(int)db::get_row_count('orders', $where);
					$compare['complete_ratio']=$compare['uv']?(int)($compare['complete']*100/$compare['uv']):0;
				}
				$return_data['compare'][$value]=$compare;
			}
		}
		
		ly200::e_json($return_data, 1);
	}
	
	/*
	* 订单部分(products)
	* 1. 订单统计(get_orders_data)
	* 2. 复购率(get_orders_repurchase_data)
	*/
	public static function get_orders_data(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		$p_Compare=(int)$p_Compare;
		$p_Terminal=(int)$p_Terminal;
		$p_TimeS=$p_TimeS!=''?$p_TimeS:0;
		$IsCharts=@!in_array($p_TimeS, array(0,-1))?1:0;
		$result=ly200::api($_POST, $c['ApiKey'], $c['api_url']);
		
		$time_s=where::time($p_TimeS, '', !in_array($p_TimeS, array(0,-1)));
		$orders_data=array(
			'total'		=>	array(),
			'detail'	=>	array(
								'country'	=>	array(),
								'payment'	=>	array()
							),
			'compare'	=>	array(
								'total'		=>	array(),
								'detail'	=>	array(
													'country'	=>	array(),
													'payment'	=>	array()
												),
							),
			'time_s'	=>	date('Y-m-d', $time_s[0]).date('/Y-m-d', $time_s[1]),
			'time_e'	=>	''
		);
		$w="OrderStatus in(4,5,6) and (OrderTime between {$time_s[0]} and {$time_s[1]})";
		$p_Terminal>=1 && $w.=' and Source='.($p_Terminal-1);
		
		$total_field='sum(((ProductPrice*((100-Discount)/100)*(if(UserDiscount>0, UserDiscount, 100)/100)*(1-CouponDiscount))+ShippingPrice+ShippingInsurancePrice-CouponPrice-DiscountPrice)*(1+PayAdditionalFee/100)+PayAdditionalAffix) as total';
		$discount_price='sum((ProductPrice*((100-Discount)/100)*(if(UserDiscount>0, UserDiscount, 100)/100)*CouponDiscount)+CouponPrice) as discount_price';
		$order_count='count(OrderId) as order_count';
		$coupon_count='sum(if(CouponPrice>0||CouponDiscount>0, 1, 0)) as coupon_count';
		$order_row=db::get_one('orders', $w, "$total_field,$order_count,$coupon_count,$discount_price");
		$order_customer_count=db::get_row_count('orders', $w.' group by Email', 'OrderId');
		$orders_data['total']=array(
			'total_price'			=>	$c['manage']['currency_symbol'].sprintf('%01.2f', $order_row['total']),
			'order_count'			=>	(int)$order_row['order_count'],
			'order_unit_price'		=>	$c['manage']['currency_symbol'].((int)$order_row['order_count']?sprintf('%01.2f', $order_row['total']/$order_row['order_count']):0),
			'order_customer_price'	=>	$c['manage']['currency_symbol'].((int)$order_customer_count?sprintf('%01.2f', $order_row['total']/$order_customer_count):0),
			'order_customer_count'	=>	(int)$order_customer_count,
			'ratio'					=>	(int)$result['msg']['uv']?sprintf('%01.2f', $order_row['order_count']*100/$result['msg']['uv']).'%':0,
			'visit_customer'		=>	(int)$result['msg']['uv'],
			'discount_price'		=>	$c['manage']['currency_symbol'].sprintf('%01.2f', $order_row['discount_price']),
			'coupon_count'			=>	(int)$order_row['coupon_count']
		);
		//ShippingCountry
		$order_row=ly200::get_table_data_to_ary('orders', $w.' group by ShippingCId', 'ShippingCId', '', "ShippingCId,ShippingCountry,$total_field,count(OrderId) as order_count");
		foreach($order_row as $k=>$v){
			$orders_data['detail']['country'][$k]=array(
				'title'			=>	$v['ShippingCountry'],
				'price'			=>	$c['manage']['currency_symbol'].sprintf('%01.2f', $v['total']),
				'count'			=>	(int)$v['order_count'],
				'average_price'	=>	$c['manage']['currency_symbol'].((int)$v['order_count']?sprintf('%01.2f', $v['total']/$v['order_count']):0)
			);
			if($p_Compare){
				$orders_data['compare']['detail']['country'][$k]=array(
					'title'			=>	$v['ShippingCountry'],
					'price'			=>	0,
					'count'			=>	0,
					'average_price'	=>	0
				);
			}
		}
		//PaymentMethod
		$order_row=ly200::get_table_data_to_ary('orders', $w.' group by PId', 'PId', '', "PId,PaymentMethod,$total_field,count(OrderId) as order_count");
		foreach($order_row as $k=>$v){
			$orders_data['detail']['payment'][$k]=array(
				'title'			=>	$v['PaymentMethod'],
				'price'			=>	$c['manage']['currency_symbol'].sprintf('%01.2f', $v['total']),
				'count'			=>	(int)$v['order_count'],
				'average_price'	=>	$c['manage']['currency_symbol'].((int)$v['order_count']?sprintf('%01.2f', $v['total']/$v['order_count']):0)
			);
			if($p_Compare){
				$orders_data['compare']['detail']['payment'][$k]=array(
					'title'			=>	$v['PaymentMethod'],
					'price'			=>	0,
					'count'			=>	0,
					'average_price'	=>	0
				);
			}
		}
		
		if($p_Compare){
			$time_e=where::time($p_TimeE);
			//$time_e[1]=$time_e[0]+($time_s[1]-$time_s[0]);
			$orders_data['time_e']=date('Y-m-d', $time_e[0]).date('/Y-m-d', $time_e[1]);
			$where="OrderStatus in(4,5,6) and OrderTime between {$time_e[0]} and {$time_e[1]}";
			$p_Terminal>=1 && $where.=' and Source='.($p_Terminal-1);

			$compare_order_row=db::get_one('orders', $where, "$total_field,$order_count,$coupon_count,$discount_price");
			$compare_order_customer_count=db::get_row_count('orders', $where.' group by Email', 'OrderId');

			$orders_data['compare']['total']=array(
				'total_price'			=>	$c['manage']['currency_symbol'].sprintf('%01.2f', $compare_order_row['total']),
				'order_count'			=>	(int)$compare_order_row['order_count'],
				'order_unit_price'		=>	$c['manage']['currency_symbol'].((int)$compare_order_row['order_count']?sprintf('%01.2f', $compare_order_row['total']/$compare_order_row['order_count']):0),
				'order_customer_price'	=>	$c['manage']['currency_symbol'].((int)$compare_order_customer_count?sprintf('%01.2f', $compare_order_row['total']/$compare_order_customer_count):0),
				'order_customer_count'	=>	(int)$compare_order_customer_count,
				'discount_price'		=>	$c['manage']['currency_symbol'].sprintf('%01.2f', $compare_order_row['discount_price']),
				'coupon_count'			=>	(int)$compare_order_row['coupon_count'],
				'ratio'					=>	(int)$result['msg']['compare_uv']?sprintf('%01.2f', $compare_order_row['order_count']*100/$result['msg']['compare_uv']).'%':0,
				'visit_customer'		=>	(int)$result['msg']['compare_uv'],
			);
			//ShippingCountry
			$compare_order_row=ly200::get_table_data_to_ary('orders', $where.' group by ShippingCId', 'ShippingCId', '', "ShippingCId,ShippingCountry,$total_field,count(OrderId) as order_count");
			foreach($compare_order_row as $k=>$v){
				$orders_data['compare']['detail']['country'][$k]=array(
					'title'			=>	$v['ShippingCountry'],
					'price'			=>	$c['manage']['currency_symbol'].sprintf('%01.2f', $v['total']),
					'count'			=>	(int)$v['order_count'],
					'average_price'	=>	$c['manage']['currency_symbol'].((int)$v['order_count']?sprintf('%01.2f', $v['total']/$v['order_count']):0)
				);
				if(array_key_exists($k, $orders_data['detail']['country'])){continue;}
				$orders_data['detail']['country'][$k]=array(
					'title'			=>	$v['ShippingCountry'],
					'price'			=>	0,
					'count'			=>	0,
					'average_price'	=>	0
				);
			}
			//PaymentMethod
			$compare_order_row=ly200::get_table_data_to_ary('orders', $where.' group by PId', 'PId', '', "PId,PaymentMethod,$total_field,count(OrderId) as order_count");
			foreach($compare_order_row as $k=>$v){
				$orders_data['compare']['detail']['payment'][$k]=array(
					'title'			=>	$v['PaymentMethod'],
					'price'			=>	$c['manage']['currency_symbol'].sprintf('%01.2f', $v['total']),
					'count'			=>	(int)$v['order_count'],
					'average_price'	=>	$c['manage']['currency_symbol'].((int)$v['order_count']?sprintf('%01.2f', $v['total']/$v['order_count']):0)
				);
				if(array_key_exists($k, $orders_data['detail']['payment'])){continue;}
				$orders_data['detail']['payment'][$k]=array(
					'title'			=>	$v['PaymentMethod'],
					'price'			=>	0,
					'count'			=>	0,
					'average_price'	=>	0
				);
			}
		}
		
		if($IsCharts){//$p_Compare && 
			$date_field="FROM_UNIXTIME(OrderTime, '%Y%m%d') as order_time";
			$order_row=ly200::get_table_data_to_ary('orders', $w.' group by order_time', 'order_time', 'total', "$total_field,$date_field");


			//line charts
			$charts_data=array();
			$charts_data['chart']['height']=350;
			$charts_data['tooltip']['valuePrefix']=$c['manage']['lang_pack']['mta']['order']['order_price'].': '.$c['manage']['currency_symbol'];
			$charts_data['plotOptions']['spline']['dataLabels']['enabled']=false;
			$charts_data['series'][0]['type']='spline';
			$time_s_obj=array(new DateTime(date('Ymd', $time_s[0])), new DateTime(date('Ymd', $time_s[1])));

			$days=0;
			while($time_s_obj[0]<=$time_s_obj[1]){
				if($days>30) break;
				$date_key=$time_s_obj[0]->format('Ymd');
				$d_date_key=$time_s_obj[0]->format('m/d');
				$charts_data['xAxis']['categories'][]=$d_date_key;
				$charts_data['series'][0]['data'][]=(float)sprintf('%01.2f', $order_row[$date_key]);
				$time_s_obj[0]->modify('1 days');
				$days++;
			}
			$orders_data['orders_charts']=$charts_data;
		}
		ly200::e_json($orders_data, 1);
	}
	
	public static function get_orders_repurchase_data(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Terminal=(int)$p_Terminal;
		$p_Cycle=(int)$p_Cycle?(int)$p_Cycle:2;
		$data=$return_data=array();
		$where="OrderStatus in(4,5,6)";
		$p_Terminal>=1 && $where.=' and Source='.($p_Terminal-1);
		
		if($p_Cycle==3){//按年
			$year=date('Y', $c['time']);
			for($i=($year-3); $i<=$year; $i++){
				$s_time=@strtotime("{$i}-01-01");
				$e_time=@(int)strtotime(($i+1)."-01-01")-1;
				$w=$where." and OrderTime between {$s_time} and {$e_time}";
				
				$reorder_count=db::get_row_count('orders', $w." and IsNewCustom=0");
				$first_order_count=db::get_row_count('orders', $w." and IsNewCustom=1");
				$key=@sprintf($c['manage']['lang_pack']['mta']['cycle_year'], $i);
				$data[$key]=array(
					'first'			=>	$first_order_count,
					'reorder'		=>	$reorder_count,
					'total'			=>	$first_order_count+$reorder_count,
					'reorder_rate'	=>	@round($reorder_count/($first_order_count+$reorder_count), 4)
				);
			}
		}else if($p_Cycle==1){//按月
			$year=date('Y', $c['time']);
			$month=date('m', $c['time']);
			$k=$month+1;
			$k>12 && $k=1;
			for($i=0;$i<12;$i++){
				$y=$k>$month?($year-1):$year;
				$s_time=@strtotime("{$y}-{$k}-01");
				
				$e_year=$y;
				$e_month=$k+1;
				$e_month>12 && $e_year=$e_year+1;
				$e_month>12 && $e_month=1;
				$e_time=@(int)strtotime("{$e_year}-{$e_month}-01")-1;
				
				$w=$where." and OrderTime between {$s_time} and {$e_time}";
				
				$reorder_count=db::get_row_count('orders', $w." and IsNewCustom=0");
				$first_order_count=db::get_row_count('orders', $w." and IsNewCustom=1");
				$key=$c['manage']['config']['ManageLanguage']=='en'?@date('F', $s_time).','.$y:@sprintf($c['manage']['lang_pack']['mta']['cycle_month'], $y, $k);
				$data[$key]=array(
					'first'			=>	$first_order_count,
					'reorder'		=>	$reorder_count,
					'total'			=>	$first_order_count+$reorder_count,
					'reorder_rate'	=>	@round($reorder_count/($first_order_count+$reorder_count), 4)
				);
				
				if($k==12) $k=1; else $k++;
			}
		}else{//按季
			$year=date('Y', $c['time']);
			$month=date('m', $c['time']);
			$season_ary=array(
				1	=>	array(1,3),
				2	=>	array(4,6),
				3	=>	array(7,9),
				4	=>	array(10,12)
			);
			foreach($season_ary as $k=>$v){
				if($month>=$v[0] && $month<=$v[1]){
					$CurrentQuarter=$k;
					break;
				}
			}
			
			$k=$CurrentQuarter+1;
			$k>4 && $k=4;
			for($i=0;$i<4;$i++){
				$y=$k>$CurrentQuarter?($year-1):$year;
				$s_year=$e_year=$y;
				$s_month=$season_ary[$k][0];
				$s_time=@strtotime("{$s_year}-{$s_month}-01");
				
				$e_month=$season_ary[$k][1]+1;
				$e_month>12 && $e_year=$e_year+1;
				$e_month>12 && $e_month=1;
				$e_time=@(int)strtotime("{$e_year}-{$e_month}-01")-1;
				
				$w=$where." and OrderTime between {$s_time} and {$e_time}";
				
				$reorder_count=db::get_row_count('orders', $w." and IsNewCustom=0");
				$first_order_count=db::get_row_count('orders', $w." and IsNewCustom=1");
				$key=@sprintf($c['manage']['lang_pack']['mta']['cycle_season_ary'][$k-1], $y);
				$data[$key]=array(
					'first'			=>	$first_order_count,
					'reorder'		=>	$reorder_count,
					'total'			=>	$first_order_count+$reorder_count,
					'reorder_rate'	=>	@round($reorder_count/($first_order_count+$reorder_count), 4)
				);
				
				if($k==4) $k=1; else $k++;
			}
		}
		
		//line charts
		$charts_data=array();
		$charts_data['title']['text']=$c['manage']['lang_pack']['module']['mta']['orders_repurchase'];
		$charts_data['title']['style']['fontSize']='24px';
		$charts_data['title']['style']['fontWeight']='bold';
		$charts_data['chart']['height']=500;
		$charts_data['legend']['enabled']=true;
		$charts_data['plotOptions']['spline']['dataLabels']['enabled']=false;
		for($i=0;$i<4;$i++){
			$charts_data['series'][$i]['type']='column';
			$charts_data['series'][$i]['name']=$c['manage']['lang_pack']['mta']['order']['repurchase_ary'][$i];
			$charts_data['series'][$i]['tooltip']['valuePrefix']=$c['manage']['lang_pack']['mta']['order']['repurchase_ary'][$i].': ';
		}

		foreach((array)$data as $k=>$v){
			$charts_data['xAxis']['categories'][]=$k;
			$charts_data['series'][0]['data'][]=(int)$v['first'];//新客户订单
			$charts_data['series'][1]['data'][]=(int)$v['reorder'];//老客户订单
			$charts_data['series'][2]['data'][]=(int)$v['total'];//总订单量
			$charts_data['series'][3]['data'][]=$v['reorder_rate'];//复购率				
		}


		$return_data['repurchase_charts']=$charts_data;
		
		ly200::e_json($return_data, 1);
	}
	
	/*
	public static function get_orders_paid_data(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$time_par=array(
			array('Ymd', '%Y%m%d', str::get_time(-30, 1, 0), '1 days', 'Ymd', 'm/d'),
			array('Ym', '%Y%m', str::get_time(-13, 1), '1 month', 'Ym01', 'Y/m'),
			array('Y', '%Y', str::get_time(-4, 1, 2), '1 years', 'Y0101', 'Y')
		);
		$p_MtaMethod=(int)$p_MtaMethod?1:0;
		$p_MtaCycle=(int)$p_MtaCycle;
		!array_key_exists($p_MtaCycle, $time_par) && $p_MtaCycle=0;
		$p_Compare=(int)$p_Compare;
		$p_Terminal=(int)$p_Terminal;
		$p_TimeS=$p_TimeS!=''?$p_TimeS:($time_par[$p_MtaCycle][2].date('/Y-m-d', $c['time']));
		
		$time_s=where::time($p_TimeS);
		if($p_MtaCycle==1){
			$time_s[0]=strtotime(date('Y-m-01', $time_s[0]));
			$time_s[1]=strtotime(date('Y-m-'.date('t', $time_s[1]), $time_s[1]));
		}elseif($p_MtaCycle==2){
			$time_s[0]=strtotime(date('Y-01-01', $time_s[0]));
			$time_s[1]=strtotime(date('Y-12-31', $time_s[1]));
		}
		$orders_paid_data=array(
			'time_s'	=>	date('Y-m-d', $time_s[0]).date('/Y-m-d', $time_s[1]),
			'time_e'	=>	''
		);
		$date_field="FROM_UNIXTIME(OrderTime, '{$time_par[$p_MtaCycle][1]}') as order_time";
		$w="OrderTime between {$time_s[0]} and {$time_s[1]}";
		$p_Terminal>=1 && $w.=' and Source='.($p_Terminal-1);
		$order_row_0=ly200::get_table_data_to_ary('orders', $w.' group by order_time', 'order_time', 'row_count', "count(*) as row_count,$date_field");
		$order_row_1=ly200::get_table_data_to_ary('orders', $w.' and OrderStatus in(4,5,6) group by order_time', 'order_time', 'row_count', "count(*) as row_count,$date_field");
		if($p_Compare){
			$time_e=where::time($p_TimeE);
			$time_e[1]=$time_e[0]+($time_s[1]-$time_s[0]);
			if($p_MtaCycle==1){
				$time_e[0]=strtotime(date('Y-m-01', $time_e[0]));
				$time_e[1]=strtotime(date('Y-m-'.date('t', $time_e[1]), $time_e[1]));
			}elseif($p_MtaCycle==2){
				$time_e[0]=strtotime(date('Y-01-01', $time_e[0]));
				$time_e[1]=strtotime(date('Y-12-31', $time_e[1]));
			}
			$orders_paid_data['time_e']=date('Y-m-d', $time_e[0]).date('/Y-m-d', $time_e[1]);
			$w="OrderTime between {$time_e[0]} and {$time_e[1]}";
			$p_Terminal>=1 && $w.=' and Source='.($p_Terminal-1);
			$compare_order_row_0=ly200::get_table_data_to_ary('orders', $w.' group by order_time', 'order_time', 'row_count', "count(*) as row_count,$date_field");
			$compare_order_row_1=ly200::get_table_data_to_ary('orders', $w.' and OrderStatus in(4,5,6) group by order_time', 'order_time', 'row_count', "count(*) as row_count,$date_field");
		}
		
		//line charts
		$charts_data=array();
		$charts_data['chart']['height']=550;
		$charts_data['tooltip']['valuePrefix']=$c['manage']['lang_pack']['mta']['order_paid'].': ';
		$charts_data['tooltip']['valueSuffix']='%';
		$charts_data['plotOptions']['spline']['dataLabels']['enabled']=false;
		$charts_data['series'][0]['type']='spline';
		$charts_data['series'][0]['name']=$p_TimeS;
		$charts_data['legend']['enabled']=true;
		($p_MtaCycle==0 && $time_s[1]-$time_s[0]>86400) && $charts_data['xAxis']['tickInterval']=3;
		$time_s_obj=array(new DateTime(date($time_par[$p_MtaCycle][4], $time_s[0])), new DateTime(date($time_par[$p_MtaCycle][4], $time_s[1])));
		if($p_Compare){
			$charts_data['series'][1]['type']='spline';
			$charts_data['series'][1]['name']=$p_TimeE;
			$time_e_obj=array(new DateTime(date($time_par[$p_MtaCycle][4], $time_e[0])), new DateTime(date($time_par[$p_MtaCycle][4], $time_e[1])));
		}
		while($time_s_obj[0]<=$time_s_obj[1]){
			if($p_Compare){
				$compare_date_key=$time_e_obj[0]->format($time_par[$p_MtaCycle][0]);
				$d_compare_date_key=$time_e_obj[0]->format($time_par[$p_MtaCycle][5]);
				$charts_data['series'][1]['data'][]=(float)@sprintf('%01.2f', $compare_order_row_1[$compare_date_key]/$compare_order_row_0[$compare_date_key]*100);
				$time_e_obj[0]->modify($time_par[$p_MtaCycle][3]);
			}
			$date_key=$time_s_obj[0]->format($time_par[$p_MtaCycle][0]);
			$d_date_key=$time_s_obj[0]->format($time_par[$p_MtaCycle][5]);
			$charts_data['xAxis']['categories'][]=$d_date_key.($p_Compare?" vs $d_compare_date_key":'');
			$charts_data['series'][0]['data'][]=(float)@sprintf('%01.2f', $order_row_1[$date_key]/$order_row_0[$date_key]*100);
			$time_s_obj[0]->modify($time_par[$p_MtaCycle][3]);
		}
		$orders_paid_data['orders_paid_charts']=$charts_data;
		ly200::e_json($orders_paid_data, 1);
	}
	*/
	
	
	/*
	* 产品部分(products)
	* 1. 产品销量排行(get_products_sales_data)
	*/
	public static function get_products_sales_data(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$page_count=50;
		//$page=(int)$p_page?(int)$p_page:1;
		$page=1;
		$start_row=($page-1)*$page_count;
		$p_Terminal=(int)$p_Terminal;

		$w="o.OrderStatus in(4,5,6)";
		if($p_TimeS){
			$time_s=where::time($p_TimeS, '', !in_array($p_TimeS, array(0,-1)));
			$w.=" and (o.OrderTime between {$time_s[0]} and {$time_s[1]})";
		}
		$p_Terminal>=1 && $w.=' and o.Source='.($p_Terminal-1);
		$products_list=db::get_limit('(orders_products_list p left join orders o on p.OrderId=o.OrderId) left join products p1 on p.ProId=p1.ProId', $w." group by p.ProId", 'p.ProId,p.Name,p.PicPath,sum(p.Qty) as count,count(o.OrderId) as order_count,p1.Prefix,p1.Number', 'order_count desc, count desc, p.ProId desc', $start_row, $page_count);
		
		$return_data=array();
		foreach($products_list as $key=>$val){
			$path=ly200::str_to_url($val['Name']);
			$url='/'.$path.'_p'.sprintf('%04d', $val['ProId']).'.html';

			$country_row=db::get_one('orders_products_list p left join orders o on p.OrderId=o.OrderId',  "p.ProId='{$val['ProId']}' and {$w} group by o.ShippingCId", 'o.ShippingCId,o.ShippingCountry,sum(p.Qty) as count,count(o.OrderId) as order_count', 'count desc');
			
			$related_row=db::get_one('orders_products_list p left join products p1 on p.ProId=p1.ProId', "p.ProId!='{$val['ProId']}' and p.ProId!=0 and OrderId in(select o.OrderId from orders o left join orders_products_list t2 on o.OrderId=t2.OrderId where $w and t2.ProId='{$val['ProId']}') group by p.ProId",  'p.ProId,p.Name,sum(p.Qty) as count,count(OrderId) as order_count,p1.Prefix,p1.Number', 'order_count desc, count desc, p.ProId desc');//,p.PicPath
			
			$related_url='/'.ly200::str_to_url($related_row['Name']).'_p'.sprintf('%04d', $related_row['ProId']).'.html';
			
			$return_data[]=array(
				'No'				=>	$start_row+$key+1,
				'ProId'				=>	$val['ProId'],
				'Name'				=>	$val['Name'],
				'Number'			=>	$val['Prefix'].$val['Number'],
				'PicPath'			=>	is_file($c['root_path'].$val['PicPath'])?$val['PicPath']:'',
				'Url'				=>	$url,
				'BuyCount'			=>	$val['count'],
				'OrderCount'		=>	$val['order_count'],
				//'CId'				=>	$country_row['ShippingCId'],
				'Country'			=>	$country_row['ShippingCountry'],
				'CountryBuyCount'	=>	$country_row['count'],
				'CountryOrderCount'	=>	$country_row['order_count'],
				'CountryBuyRate'	=>	@round(($country_row['count']/$val['count'])*100).'%',
				'CountryOrderRate'	=>	@round(($country_row['order_count']/$val['order_count'])*100).'%',
				'Related'			=>	array(
											'ProId'		=>	$related_row['ProId'],
											'Name'		=>	$related_row['Name'],
											'Number'	=>	$related_row['Prefix'].$related_row['Number'],
											//'PicPath'	=>	is_file($c['root_path'].$related_row['PicPath'])?$related_row['PicPath']:'',
											'Url'		=>	$related_url,
											'BuyCount'	=>	$related_row['count'],
											'OrderCount'=>	$related_row['order_count']
										),
			);
		}
		
		ly200::e_json($return_data, 1);
	}
	
	public static function get_products_sales_related_data(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_ProId=(int)$p_ProId;
		$p_Terminal=(int)$p_Terminal;
		$return_data=array();

		$w="o.OrderStatus in(4,5,6)";
		if($p_TimeS){
			$time_s=where::time($p_TimeS, '', !in_array($p_TimeS, array(0,-1)));
			$w.=" and (o.OrderTime between {$time_s[0]} and {$time_s[1]})";
		}
		$p_Terminal>=1 && $w.=' and o.Source='.($p_Terminal-1);
		
		$products_list=db::get_one('orders_products_list p left join orders o on p.OrderId=o.OrderId', $w." and p.ProId='{$p_ProId}'", 'sum(p.Qty) as count,count(o.OrderId) as order_count', 'order_count desc, count desc, p.ProId desc');


		$country_row=db::get_limit('orders_products_list p left join orders o on p.OrderId=o.OrderId',  "p.ProId='{$p_ProId}' and {$w} group by o.ShippingCId", 'o.ShippingCountry,sum(p.Qty) as count,count(o.OrderId) as order_count', 'count desc', 0, 20);//o.ShippingCId,
		foreach($country_row as $key=>$val){
			$return_data['country'][]=array(
				'No'				=>	$key+1,
				'Country'			=>	$val['ShippingCountry'],
				'CountryBuyCount'	=>	$val['count'],
				'CountryOrderCount'	=>	$val['order_count'],
				'CountryBuyRate'	=>	@round(($val['count']/$products_list['count'])*100).'%',
				'CountryOrderRate'	=>	@round(($val['order_count']/$products_list['order_count'])*100).'%',
			);
		}
		
		$related_row=db::get_limit('orders_products_list p left join products p1 on p.ProId=p1.ProId', "p.ProId!='{$p_ProId}' and p.ProId!=0 and OrderId in(select o.OrderId from orders o left join orders_products_list t2 on o.OrderId=t2.OrderId where $w and t2.ProId='{$p_ProId}') group by p.ProId",  'p.ProId,p.Name,sum(p.Qty) as count,count(OrderId) as order_count,p1.Prefix,p1.Number', 'order_count desc, count desc, p.ProId desc', 0, 20);
		foreach($related_row as $key=>$val){
			$url='/'.ly200::str_to_url($val['Name']).'_p'.sprintf('%04d', $val['ProId']).'.html';
			$return_data['related'][]=array(
				'No'				=>	$key+1,
				'ProId'				=>	$val['ProId'],
				'Name'				=>	$val['Name'],
				'Number'			=>	$val['Prefix'].$val['Number'],
				'Url'				=>	$url,
				'BuyCount'			=>	$val['count'],
				'OrderCount'		=>	$val['order_count'],
			);
		}		
		
		ly200::e_json($return_data, 1);
	}
	
	/*
	* 会员部分(user)
	* 1. 会员统计(get_user_data)
	*/
	public static function get_user_data(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		$month_list=array();
		for($i=0; $i<12; $i++){
			$month_list[]=@date('Y-m', strtotime("-{$i} month", strtotime(date('Y-m-01', $c['time']))));
		}
		$StartTime=@strtotime(end($month_list));
		$user_data=array('total'=>array());
		
		$month_ary=array_reverse($month_list);
		$save_dir=$c['tmp_dir'].'manage/';
		$filename='user-statistics.json';
		if(!file::check_cache($c['root_path'].$save_dir.$filename, 0)){//文件是否存在、是否到更新时间
			/***********************************会员趋势(start)***********************************/
			$where="RegTime>{$StartTime}";
			$user_count='count(UserId) as user_count';
			$date_field="FROM_UNIXTIME(RegTime, '%Y-%m') as reg_time";
			$new_user_row=ly200::get_table_data_to_ary('user', $where.' group by reg_time', 'reg_time', 'user_count', "$user_count,$date_field");
			
			$user_statistics=array();
			$total_user_count=0;//最近12个月新会员总数量
			foreach($month_ary as $k=>$v){
				$time_s=strtotime($v);
				$time_e=$k==11?$c['time']:strtotime($month_ary[$k+1]);
				//活跃会员
				$w="OperationType=1 and (AccTime between {$time_s} and {$time_e})";
				$active_member=db::get_row_count('user_operation_log', $w.' group by UserId', 'LId');
				//核心会员
				$w="OrderStatus in(4,5,6) and UserId>0 and (OrderTime between {$time_s} and {$time_e})";
				$core_member=db::get_row_count('orders', $w.' group by UserId', 'OrderId');
				//总会员
				$w="RegTime<{$time_e}";
				$total_member=db::get_row_count('user', $w.' group by UserId', 'UserId');

				$user_statistics['trend'][$v]=array(
					'new'		=>	(int)$new_user_row[$v],
					'active'	=>	(int)$active_member,
					'core'		=>	(int)$core_member,
					'total'		=>	(int)$total_member
				);
				$total_user_count+=(int)$new_user_row[$v];
			}
			$user_statistics['total_member']=$total_user_count;
			/***********************************会员趋势(end)***********************************/

			if($total_user_count){
				/***********************************会员性别、等级(start)***********************************/
				$user_set=str::json_data(db::get_value('config', "GroupId='user' and Variable='RegSet'", 'Value'), 'decode');
				$lang=db::get_value('config', "GroupId='global' and Variable='LanguageDefault'", 'Value');
				//会员等级
				$level_row=db::get_all('user_level', 'IsUsed=1', "LId,Name_{$lang} as Name");
				$level_ary=array();
				foreach($level_row as $k=>$v){
					$key=$v['Name']?$v['Name']:'No Level';
					$level_user_count=db::get_row_count('user', $where." and Level='{$v['LId']}'", 'UserId');
					$level_ary[$key]=(int)$total_user_count?(float)sprintf('%01.2f', ($level_user_count/$total_user_count)*100):0;
				}
				$level_user_count=db::get_row_count('user', $where." and Level=0", 'UserId');
				$level_user_count && $level_ary['No Level']=(int)$total_user_count?(float)sprintf('%01.2f', ($level_user_count/$total_user_count)*100):0;
				@arsort($level_ary);
				$user_statistics['user_detail']['level']=$level_ary;
				
				//性别
				$gender_ary=array();
				if($user_set['Gender'][0]){
					foreach($c['gender'] as $k=>$v){
						$gender_count=db::get_row_count('user', "Gender='{$k}' and {$where}", 'UserId');
						$gender_ary[$v]=(int)$total_user_count?(float)sprintf('%01.2f', ($gender_count/$total_user_count)*100):0;
					}
				}
				@arsort($gender_ary);
				$user_statistics['user_detail']['gender']=$gender_ary;
				/***********************************会员性别、等级(end)***********************************/
	
	
				/***********************************国家分布(start)***********************************/
				$user_row=db::get_all('user_address_book a left join country c on a.CId=c.CId', "a.UserId in(select UserId from user where $where) group by a.UserId", "a.CId,c.Country");
				$country_ary=array();
				$country_user_count=0;//有国家记录的会员数量
				foreach($user_row as $v){
					if($v['Country']){
						$country_ary[$v['Country']]=(int)$country_ary[$v['Country']]+1;
						$country_user_count++;
					}
				}
				@arsort($country_ary);
				$user_statistics['country']['country_ary']=$country_ary;
				$user_statistics['country']['country_user_count']=$country_user_count;
				/***********************************国家分布(end)***********************************/
			}
			@file::write_file($save_dir, $filename, str::json_data($user_statistics));
		}else{
			//加载内容格式
			$theme_object=file_get_contents($c['root_path'].$save_dir.$filename);
			$user_statistics=str::json_data($theme_object, 'decode');
		}
		$user_data['total']=$user_statistics['trend'][$month_list[0]];

		if((int)$p_trend){//首次进入加载图表
			/******************************************会员趋势图(start)******************************************/
			//line charts
			$charts_data=array();
			$charts_data['title']['text']='会员趋势图';
			$charts_data['title']['style']['fontSize']='24px';
			$charts_data['title']['style']['fontWeight']='bold';
			$charts_data['chart']['height']=500;
			$charts_data['legend']['enabled']=true;
			$charts_data['plotOptions']['spline']['dataLabels']['enabled']=false;
			$charts_data['series'][0]['type']='spline';
			$charts_data['series'][1]['type']='spline';
			$charts_data['series'][2]['type']='spline';
			$charts_data['series'][3]['type']='spline';
			$charts_data['series'][0]['name']=$c['manage']['lang_pack']['mta']['user']['new_member'].': ';
			$charts_data['series'][1]['name']=$c['manage']['lang_pack']['mta']['user']['active_member'].': ';
			$charts_data['series'][2]['name']=$c['manage']['lang_pack']['mta']['user']['core_member'].': ';
			$charts_data['series'][3]['name']=$c['manage']['lang_pack']['mta']['user']['total_member'].': ';
			$charts_data['series'][0]['tooltip']['valuePrefix']=$c['manage']['lang_pack']['mta']['user']['new_member'].': ';
			$charts_data['series'][1]['tooltip']['valuePrefix']=$c['manage']['lang_pack']['mta']['user']['active_member'].': ';
			$charts_data['series'][2]['tooltip']['valuePrefix']=$c['manage']['lang_pack']['mta']['user']['core_member'].': ';
			$charts_data['series'][3]['tooltip']['valuePrefix']=$c['manage']['lang_pack']['mta']['user']['total_member'].': ';
	
			foreach($user_statistics['trend'] as $k=>$v){
				$charts_data['xAxis']['categories'][]=$k;
				$charts_data['series'][0]['data'][]=(int)$v['new'];//新会员
				$charts_data['series'][1]['data'][]=(int)$v['active'];//活跃会员
				$charts_data['series'][2]['data'][]=(int)$v['core'];//核心会员
				$charts_data['series'][3]['data'][]=(int)$v['total'];//总会员				
			}
			$user_data['trend_charts']=$charts_data;
			/******************************************会员趋势图(end)******************************************/


			/******************************************会员国家分布(start)******************************************/
			if($user_statistics['total_member']){
				//line charts
				$charts_data=array();
				$charts_data['title']['text']='会员国家分布';
				$charts_data['title']['style']['fontSize']='24px';
				$charts_data['title']['style']['fontWeight']='bold';
				$charts_data['chart']['height']=500;
				$charts_data['plotOptions']['spline']['dataLabels']['enabled']=false;
				$charts_data['series'][0]['type']='column';
				$charts_data['series'][0]['pointWidth']=40;
				$charts_data['series'][0]['tooltip']['valueSuffix']='%';
				/************************/
				$i=0;
				$total_ratio=0;
				$total_country_count=@count($user_statistics['country']['country_ary']);
				$other_count=$user_statistics['total_member']-$total_country_count;
				foreach($user_statistics['country']['country_ary'] as $k=>$v){
					if($i==10 || ($other_count>0 && $i++==9)){break;}
					
					$ratio=(int)$user_statistics['total_member']?(float)sprintf('%01.2f', ($v/$user_statistics['total_member'])*100):0;
					$total_ratio+=$ratio;
					$charts_data['xAxis']['categories'][]=$k;
					$charts_data['series'][0]['data'][]=$ratio;
				}
				if($total_country_count>10 || $other_count>0){
					$charts_data['xAxis']['categories'][]='Other';
					$charts_data['series'][0]['data'][]=100-$total_ratio;
				}
				/************************/
				$user_data['country_charts']=$charts_data;
			}
			/******************************************会员国家分布(end)******************************************/
			
			//会员等级
			@count($user_statistics['user_detail']['level']) && $user_data['user_detail']['level']=$user_statistics['user_detail']['level'];
			//性别
			@count($user_statistics['user_detail']['gender']) && $user_data['user_detail']['gender']=$user_statistics['user_detail']['gender'];
		}
		
		ly200::e_json($user_data, 1);
	}
}
?>