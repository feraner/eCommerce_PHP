<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class where{
	public static function equal($data){
		$w='';
		foreach($data as $k=>$v){
			if((!is_array($v) && $v!='') || (is_array($v) && $v[0]!='')){
				is_array($v) && $v=$v[0];
				$w.=" and $k='$v'";
			}
		}
		if($w){return $w;}
	}
	
	public static function keyword($keyword, $field){
		if(!$keyword){return;};
		$w=array();
		foreach($field as $v){
			if(is_array($v)){
				$w[]="{$v[0]} like '%,$keyword,%'";
			}else{
				$w[]="$v like '%$keyword%'";
			}
		}
		$w=str::ary_format($w, 3);
		return " and ($w)";
	}
	
	public static function time($time, $field='', $is_range=0){	//时间范围
		global $c;
		substr($time, 0, 1)!='-' && $time=str_replace('-', '', $time);
		$ts=$te=0;
		if(substr_count($time, '/')){
			$t=@explode('/', $time);
			$ts=@strtotime($t[0]);
			$te=@strtotime($t[1])+86399;
			$t[0]==$t[1] && $time=$t[0];
		}elseif(is_numeric($time)){
			$time=(int)$time;
			if($time<=0){
				$ts=@strtotime(date('Y-m-d', strtotime("$time days")));
				$te=$is_range?$c['time']:$ts+86399;
			}elseif(strlen($time)==4){
				$ts=@strtotime($time.'0101');
				$te=@strtotime($time.'1231')+86399;
			}elseif(strlen($time)==6){
				$ts=@strtotime($time.'01');
				$te=@strtotime(date('Y-m-'.date('t', $ts), $ts))+86399;
			}elseif(strlen($time)==8){
				$ts=@strtotime($time);
				$te=$ts+86399;
			}
		}
		if($ts && $te){return $field!=''?" and $field between $ts and $te":array($ts, $te, $time);}
	}
	
	public static function price($price){
		global $c;
		if(!$price){return;};
		$price_range=explode('-', $price);
		$price_0=cart::currency_price($price_range[0], $_SESSION['Currency']['ExchangeRate'], $_SESSION['ManageCurrency']['ExchangeRate']);
		$price_1=cart::currency_price($price_range[1], $_SESSION['Currency']['ExchangeRate'], $_SESSION['ManageCurrency']['ExchangeRate']);
		if(!is_numeric($price_0) || !is_numeric($price_1)){ return; }
		if($price_0>0 && $price_1==0){//价格以上
			$w=" and ((LowestPrice>={$price_0}) or (IsPromotion=1 and PromotionType=0 and StartTime<='{$c['time']}' and EndTime>='{$c['time']}' and PromotionPrice>{$price_0}))";
		}elseif($price_0==0 && $price_1>0){//价格以下
			$w=" and ((LowestPrice<={$price_1}) or (IsPromotion=1 and PromotionType=0 and StartTime<='{$c['time']}' and EndTime>='{$c['time']}' and PromotionPrice<{$price_1}))";
		}elseif($price_0<$price_1){
			$w=" and ((LowestPrice between {$price_0} and {$price_1}) or (IsPromotion=1 and PromotionType=0 and StartTime<='{$c['time']}' and EndTime>='{$c['time']}' and PromotionPrice between {$price_0} and {$price_1}))";	
		}else{//相同
			$w=" and ((LowestPrice={$price_0}) or (IsPromotion=1 and PromotionType=0 and StartTime<='{$c['time']}' and EndTime>='{$c['time']}' and PromotionPrice={$price_0}))";	
		}
		return array($w, $price_range);
	}
	
	public static function products($PriceRange, $Narrow, $Ajax=0){ //$Ajax 这个参数用于搜索框 ajax 提交
		global $c;
		$Column='';
		$price_range=array();
		$Narrow_ary=array();
		$order_by='';
		if($PriceRange){
			$priceAry=where::price($PriceRange);
			if(is_array($priceAry)){
				$where.=$priceAry[0];
				$price_range=$priceAry[1];
			}
		}
		if(substr_count($_SERVER['REQUEST_URI'], '/search/') || $Ajax){//产品搜索无筛选
			$Keyword=$_GET['Keyword'] ? $_GET['Keyword'] : $_POST['Keyword'];
			if($Keyword){
				$i=0;
				$OrderByAry=array();
				$where.=" and ( concat_ws('', Prefix, Number) like '%$Keyword%' or SKU like '%$Keyword%' or (";
				$KeywordAry=str::str_code(explode(' ', stripslashes($Keyword)), 'addslashes');
				foreach($KeywordAry as $v){
					foreach(array("Name{$c['lang']}", 'concat_ws("", Prefix, Number)', 'SKU', "BriefDescription{$c['lang']}") as $v2){
						$where.=($i?' or ':'')."$v2 like '%$v%'";
						++$i;
						$OrderByAry[$v2][]=$v;
					}
				}
				$where.=')';
				//关键词排序  产品名称 > 产品编号 > 产品SKU > 产品简介
				//整体关键词
				$order_by.='(case when ';
				foreach(array("Name{$c['lang']}", 'concat_ws("", Prefix, Number)', 'SKU', "BriefDescription{$c['lang']}") as $k=>$v){
					$order_by.=($k?' or ':'')."$v like '%$Keyword%'";
				}
				$order_by.=" then 1";
				//同时拥有关键词 目前仅支持产品名称
				$order_by.=' when ';
				foreach($KeywordAry as $k=>$v){
					$order_by.=($k?' and ':'')."Name{$c['lang']} like '%$v%'";
				}
				$order_by.=" then 2";
				//分词关键词
				$i=3;
				foreach((array)$OrderByAry as $k=>$v){
					$order_by.=' when ';
					foreach($v as $k2=>$v2){
						$order_by.=($k2?' or ':'')."$k like '%$v2%'";
					}
					$order_by.=" then $i";
					++$i;
				}
				$order_by.=" else 0 end) asc, ";
				//产品标签
				$column_ary=db::get_table_fields('products_tags', 1);
				if(@!in_array("Name{$c['lang']}", $column_ary)){	//没有该字段自动创建
					db::query("ALTER TABLE `products_tags` ADD Name{$c['lang']} VARCHAR( 150 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;");
				}
				$tags_row=db::get_one('products_tags',"Name{$c['lang']}='$Keyword'");
				$tags_row && $where.=" or Tags like '%|{$tags_row['TId']}|%'";
				$where.=')';
			}
			$Column='%s-%s of %s Items for "'.str::str_code(stripslashes($Keyword)).'"';
		}else{
			if($Narrow){
				$Narrow_ary=explode('+', $Narrow);
				$arr=array();
				$v_ary=array();
				foreach((array)$Narrow_ary as $k=>$v){ $arr[]=(int)$v; }
				sort($arr); //从小到大排序
				if($arr){
					if(ly200::is_mobile_client(1)==1){//移动端
						$attr_ary=$value_ary=array();
						$attr_str=implode(',', $arr);
						$value_row=db::get_all('products_selected_attribute', 'VId in ('.$attr_str.') and IsUsed=1', 'ProId, AttrId, VId', 'VId asc');
						foreach((array)$value_row as $v){
							if(!in_array($v['VId'], $value_ary[$v['ProId']][$v['AttrId']])){
								$value_ary[$v['ProId']][$v['AttrId']][]=$v['VId'];
							}
							if(!in_array($v['VId'], $attr_ary[$v['AttrId']])){
								$attr_ary[$v['AttrId']][]=$v['VId'];
							}
						}
						$attr_len=count($attr_ary);
						$narrow_where.=' and ProId in (0';
						foreach((array)$value_ary as $k=>$v){
							$count=0;
							foreach($attr_ary as $k2=>$v2){
								foreach($v2 as $k3=>$v3){
									if(in_array($v3, $v[$k2])){//拥有其中一个选项，就可以跳出循环，直接记录
										$count+=1;
										break;
									}
								}
							}
							if($count>=$attr_len){
								$narrow_where.=",{$k}";
							}
						}
						$narrow_where.=')';
					}else{//PC端
						$attr_ary=$value_ary=array();
						$attr_str=implode(',', $arr);
						$value_row=db::get_all('products_selected_attribute', 'VId in ('.$attr_str.') and IsUsed=1', 'ProId, AttrId, VId', 'VId asc');
						foreach((array)$value_row as $v){
							if(!$value_ary[$v['ProId']]){
								$value_ary[$v['ProId']]="|{$v['VId']}|";
							}else{
								$value_ary[$v['ProId']].=$v['VId'].'|';
							}
						}
						$attr_str='|';
						foreach((array)$arr as $k=>$v){ $attr_str.=($k?'|':'').$v; }
						$attr_str.='|';
						$i=0;
						$narrow_where.=' and ProId in (0';
						foreach((array)$value_ary as $k=>$v){
							if($attr_str==$v){
								$narrow_where.=",{$k}";
								++$i;
							}
						}
						$narrow_where.=')';
					}
				}
				$where.=$narrow_where;
				unset($v_ary, $arr);
			}
		}
		return array($where, $Column, $price_range, $Narrow_ary, $order_by);
	}
}
?>