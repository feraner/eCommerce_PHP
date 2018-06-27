<?php !isset($c) && exit();?>
<?php
$ProId=(int)$_GET['ProId'];
$ColorId=(int)$_GET['ColorId'];
$products_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
if(!$products_row){
	@header('HTTP/1.1 404');
	exit;
}

$Name=$products_row['Name'.$c['lang']];
$Price_0=(float)$products_row['Price_0'];
$Price_1=(float)$products_row['Price_1'];
$MOQ=(int)$products_row['MOQ'];
$Max=(int)$products_row['Stock']; //最大购买上限
$products_row['MaxOQ']>0 && $products_row['MaxOQ']<$Max && $Max=$products_row['MaxOQ'];//最大购买量
$CateId=(int)$products_row['CateId'];
$CateId && $category_row=str::str_code(db::get_one('products_category', "CateId='$CateId'"));
$products_description_row=str::str_code(db::get_one('products_description', "ProId='$ProId'"));
$review_cfg=str::json_data(db::get_value('config', "GroupId='products_show' and Variable='review'", 'Value'), 'decode');//评论显示设置

//产品分类
if($category_row['UId']!='0,'){
	$TopCateId=category::get_top_CateId_by_UId($category_row['UId']);
	$TopCategory_row=str::str_code(db::get_one('products_category', "CateId='$TopCateId'"));
}
$UId_ary=@explode(',', $category_row['UId']);

//面包屑
$Column=ly200::get_web_position($category_row, 'products_category', '', '<em><i></i></em>', 12);

//产品售卖状态
$is_stockout=($products_row['Stock']<$products_row['MOQ'] || $products_row['Stock']<1 || $products_row['SoldOut'] || ($products_row['IsSoldOut'] && ($products_row['SStartTime']>$c['time'] || $c['time']>$products_row['SEndTime'])) || in_array($CateId, $c['procate_soldout']));

//产品评论
$Rating=($products_row['IsDefaultReview'] && $products_row['DefaultReviewRating'])?(int)$products_row['DefaultReviewRating']:(int)$products_row['Rating'];
$TotalRating=($products_row['IsDefaultReview'] && $products_row['DefaultReviewTotalRating'])?$products_row['DefaultReviewTotalRating']:$products_row['TotalRating'];

//产品属性
$ParentId=$products_row['AttrId']; //产品属性分类ID
$IsCombination=(int)$products_row['IsCombination']; //是否开启规格组合
$attr_ary=$color_attr_ary=$selected_ary=$color_picpath_ary=array();
$isHaveOversea=count($c['config']['Overseas']); //是否开启海外仓
if((int)$c['config']['global']['Overseas']==0){ //关闭海外仓功能
	$isHaveOversea=1;
}
if($ParentId || $isHaveOversea){
	$products_attr=str::str_code(db::get_all('products_attribute', "ParentId='{$ParentId}'", "AttrId, Name{$c['lang']}, CartAttr, ColorAttr, Type", $c['my_order'].'AttrId asc'));
	foreach((array)$products_attr as $v){
		if($v['CartAttr']){ //购物车属性
			$attr_ary['Cart'][$v['AttrId']]=$v;
		}else{ //普通属性
			$attr_ary['Common'][$v['AttrId']]=$v;
		}
		(int)$v['ColorAttr'] && $color_attr_ary[]=$v['AttrId'];
	}
	$color_where='-1';
	$selected_row=str::str_code(db::get_all('products_selected_attribute', "ProId='{$ProId}' and IsUsed=1", 'SeleteId, AttrId, VId, OvId, Value', 'SeleteId asc'));
	foreach($selected_row as $v){
		$selected_ary['Id'][$v['AttrId']][]=$v['VId']; //记录勾选属性ID
		$v['AttrId']>0 && $v['VId']==0 && $v['Value'] && $v['OvId']<2 && $selected_ary['Value'][$v['AttrId']]=$v['Value']; //文本框内容
		$v['AttrId']==0 && $v['VId']==0 && $v['OvId']>0 && $selected_ary['Overseas'][]=$v['OvId']; //记录勾选属性ID 发货地
		$v['VId'] && $color_where.=",{$v['VId']}";
	}
	$color_attr_status=0;
	$color_row=str::str_code(db::get_all('products_color', "ProId='{$ProId}' and VId in($color_where)", 'VId, PicPath_0'));
	if(count($color_row)){ //统计产品颜色图片
		foreach((array)$color_row as $k=>$v){
			if(!$v['PicPath_0']) continue;
			if(is_file($c['root_path'].$v['PicPath_0'])){
				$color_picpath_ary[$v['VId']]=$v['PicPath_0'];
			}else $color_attr_status=1;
		}
	}else $color_attr_status=1;
}

//产品价格和折扣
$CurPrice=$products_row['Price_1'];
$is_wholesale=($products_row['Wholesale'] && $products_row['Wholesale']!='[]');
if($is_wholesale){
	$wholesale_price=str::json_data(htmlspecialchars_decode($products_row['Wholesale']), 'decode');
	foreach((array)$wholesale_price as $k=>$v){
		if($MOQ<$k) break;
		$CurPrice=(float)$v;
	}
	$maxPrice=reset($wholesale_price);
	$minPrice=end($wholesale_price);
}
$discount=($Price_1-$CurPrice)/((float)$Price_1?$Price_1:1)*100;

//产品促销
$is_promotion=((int)$products_row['IsPromotion'] && $products_row['StartTime']<$c['time'] && $c['time']<$products_row['EndTime']);
if($is_promotion && !$products_row['PromotionType']){//现金类型
	$CurPrice=$products_row['PromotionPrice'];
}

//秒杀
$sec_where="ProId='$ProId' and RemainderQty>0 and {$c['time']} between StartTime and EndTime";
$SId=(int)$_GET['SId'];
$SId && $sec_where="SId='{$SId}' and ".$sec_where;
$sales_row=str::str_code(db::get_one('sales_seckill', $sec_where));
if($sales_row){
	$is_promotion=0;//秒杀优先于促销
	$IsSeckill=1;
	$CurPrice=$sales_row['Price'];
	$discount=($Price_1-$CurPrice)/((float)$Price_1?$Price_1:1)*100;
	$SId=$sales_row['SId'];
	$SMax=($sales_row['MaxQty'] && $sales_row['RemainderQty'] && $sales_row['RemainderQty']>=$sales_row['MaxQty']?$sales_row['MaxQty']:$sales_row['RemainderQty']); //最大购买上限
	$SMax<=$Max && $Max=$SMax;
}

//团购
$TId=(int)$_GET['TId'];
if($TId){
	$tuan_row=str::str_code(db::get_one('sales_tuan', "TId='{$TId}' and ProId='$ProId' and BuyerCount<TotalCount and {$c['time']} between StartTime and EndTime"));
	if($tuan_row){
		$is_promotion=0;//团购优先于促销
		$IsTuan=1;
		$CurPrice=$tuan_row['Price'];
		$discount=($Price_1-$CurPrice)/((float)$Price_1?$Price_1:1)*100;
		//$Max=$tuan_row['TotalCount']-$tuan_row['BuyerCount']; //最大购买上限
		$Max=1;//最大购买上限
		$Column='<em><i></i></em><a href="'.$c['nav_cfg'][5]['url'].'">'.$c['nav_cfg'][5]['name'.$c['lang']].'</a>';
	}
}

//最后拍板
if(!$IsSeckill && !$IsTuan && $is_wholesale){
	$CurPrice>$maxPrice && $maxPrice=$CurPrice;
	$CurPrice<$minPrice && $minPrice=$CurPrice;
}
$discount=sprintf('%01.0f', $discount);
$oldPrice=(($SId && $IsSeckill) || ($TId && $IsTuan) || $is_promotion)?$Price_1:$Price_0;
$ItemPrice=$CurPrice;
$CurPrice=($is_promotion && $products_row['PromotionType']?$CurPrice*($products_row['PromotionDiscount']/100):$CurPrice);
$save_discount=@intval(sprintf('%01.2f', ($oldPrice-$CurPrice)/$oldPrice*100));
$save_discount=$save_discount<1?1:$save_discount;
$Max=$Max<1?1:$Max;

//默认国家参数
$country_default_row=str::str_code(db::get_one('country', 'IsUsed=1', 'CId, Country, Acronym, CountryData', 'IsDefault desc, Country asc'));
if($country_default_row['CountryData']){
	$country_default_data=str::json_data(htmlspecialchars_decode($country_default_row['CountryData']), 'decode');
	$country_default_row['Country']=$country_default_data[substr($c['lang'], 1)];
}

//组合产品
$group_promotion_ary[0]=ly200::get_products_package($ProId);//组合购买
$group_promotion_ary[1]=ly200::get_products_package($ProId, 1);//组合促销
if(!array_filter($group_promotion_ary)) unset($group_promotion_ary);

//SEO
$products_seo_row=str::str_code(db::get_one('products_seo', "ProId='$ProId'"));
$spare_ary=array(
	'SeoTitle'		=>	$Name.','.$category_row['Category'.$c['lang']],
	'SeoKeyword'	=>	$Name.','.$category_row['Category'.$c['lang']],
	'SeoDescription'=>	$Name.','.$category_row['Category'.$c['lang']].','.$TopCategory_row['Category'.$c['lang']]
);
if($IsTuan){
	$products_type=1;
}elseif($IsSeckill){
	$products_type=2;
}else{
	$products_type=0;
}
//产品选项卡
$tab_row=array();
$desc_row=str::str_code(db::get_one('products_category_description', "CateId='$CateId'"));//产品分类详细
$ProIsDesc=str::json_data(htmlspecialchars_decode($products_row['IsDesc']), 'decode');
$CateIsDesc=str::json_data(htmlspecialchars_decode($category_row['IsDesc']), 'decode');
if($TopCateId){//顶级分类
	$top_desc_row=str::str_code(db::get_one('products_category_description', "CateId='$TopCateId'"));
	$top_CateIsDesc=str::json_data(htmlspecialchars_decode($TopCategory_row['IsDesc']), 'decode');
}
$page_row=str::str_code(db::get_limit('article', 'CateId=1', '*', 'AId asc'));
for($i=0; $i<3; ++$i){
	if($products_description_row["Tab_{$i}{$c['lang']}"] && $ProIsDesc[$i]){//产品
		$tab_row[$i]['TabName']=$products_description_row["TabName_{$i}{$c['lang']}"];
		$tab_row[$i]['Tab']=$products_description_row["Tab_{$i}{$c['lang']}"];
	}elseif($desc_row["Tab_{$i}{$c['lang']}"] && $CateIsDesc[$i]){//产品当前分类
		$tab_row[$i]['TabName']=$desc_row["TabName_{$i}{$c['lang']}"];
		$tab_row[$i]['Tab']=$desc_row["Tab_{$i}{$c['lang']}"];
	}elseif($TopCateId && $top_desc_row["Tab_{$i}{$c['lang']}"] && $top_CateIsDesc[$i]){//产品顶级分类
		$tab_row[$i]['TabName']=$top_desc_row["TabName_{$i}{$c['lang']}"];
		$tab_row[$i]['Tab']=$top_desc_row["Tab_{$i}{$c['lang']}"];
	}else{//整站
		if(!($TopCateId?$top_CateIsDesc[$i]:$CateIsDesc[$i])) continue;
		$tab_row[$i]['TabName']=$page_row[$i]['Title'.$c['lang']];
		$content=str::str_code(db::get_one('article_content', "AId='{$page_row[$i]['AId']}'"));
		$tab_row[$i]['Tab']=$content['Content'.$c['lang']];
	}
}

ly200::set_products_history($products_row, $CurPrice, $oldPrice);
$view_num=count($_SESSION['Ueeshop']['ViewHistory']);
if($view_num==0){
	$_SESSION['Ueeshop']['ViewHistory']=array($products_row['ProId']);
	db::query("update products set View=View+1 where ProId='{$products_row['ProId']}'");
}else{
	if(!in_array($products_row['ProId'], $_SESSION['Ueeshop']['ViewHistory'])){
		$_SESSION['Ueeshop']['ViewHistory'][]=$products_row['ProId'];
		db::query("update products set View=View+1 where ProId='{$products_row['ProId']}'");
	}
}

//快捷支付
$is_paypal_checkout=(int)db::get_row_count('payment', "Method='Excheckout' and IsUsed=1");
?>
<!DOCTYPE HTML>
<html lang="us">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">
<meta content="telephone=no" name="format-detection" />
<?=ly200::seo_meta($products_seo_row, $spare_ary);?>
<?php include("{$c['mobile']['theme_path']}inc/resource.php");?>
<?=ly200::load_static("{$c['mobile']['tpl_dir']}css/goods.css", "{$c['mobile']['tpl_dir']}js/goods.js");?>
<style>
.detail_desc table{border-collapse:collapse; width:100%;}
.detail_desc table td{border:1px solid #ccc;}
</style>
<?php
if((int)$c['config']['Platform']['Facebook']['Pixel']['IsUsed']){
	//Facebook Pixel
?>
	<!-- Facebook Pixel Code -->
	<script type="text/javascript">
	<!-- When a page viewed such as landing on a product detail page. -->
	fbq('track', 'ViewContent', {
		content_type: 'product',//产品类型为产品
		content_ids: ['<?=$products_row['SKU']?addslashes(htmlspecialchars_decode($products_row['SKU'])):addslashes(htmlspecialchars_decode($products_row['Prefix'])).addslashes(htmlspecialchars_decode($products_row['Number']));?>'],//产品ID
		content_name: '<?=addslashes($Name);?>',//产品名称
		value: <?=cart::iconv_price($CurPrice, 2, '', 0);?>,//产品价格
		currency: '<?=$_SESSION['Currency']['Currency'];?>'//货币类型
	});
	
	<!-- When some adds a product to a shopping cart. -->
	$.fn.fbq_addtocart=function(val){
		fbq('track', 'AddToCart', {
			content_type: 'product',//产品类型为产品
			content_ids: ['<?=$products_row['SKU']?addslashes(htmlspecialchars_decode($products_row['SKU'])):addslashes(htmlspecialchars_decode($products_row['Prefix'])).addslashes(htmlspecialchars_decode($products_row['Number']));?>'],//产品ID
			content_name: '<?=addslashes($Name);?>',//产品名称
			value: val,//数值
			currency: '<?=$_SESSION['Currency']['Currency'];?>'//货币类型
		});
	}
	</script>
	<!-- End Facebook Pixel Code -->
<?php }?>
</head>

<body class="lang<?=$c['lang'];?>">
<?php include("{$c['mobile']['theme_path']}inc/header.php");?>
<div class="wrapper">
	<?=html::mobile_crumb($Column);?>
    <div class="detail_pic clean ui_border_b"></div>
	<?php
	//团购盒子
	if((int)$IsTuan){
	?>
		<div class="clean prod_info_tuan">
			<div class="item"><i class="icon_time"></i><?=str_replace('%time%', '<span class="flashsale_time" endTime="'.date('Y/m/d H:i:s', $tuan_row['EndTime']).'"></span>', $c['lang_pack']['saleEnd']);?></div>
			<div class="item"><i class="icon_bought"></i><?=$tuan_row['BuyerCount'];?><br /><?=$c['lang_pack']['bought'];?></div>
			<div class="item"><?=html::mobile_review_star($Rating);?><br /><?=$TotalRating;?><br /><?=$c['lang_pack']['ratings'];?></div>
		</div>
	<?php }?>
    <div class="goods_info clean">
    	<form id="goods_form" action="?" method="post">
        	<div class="prod_info_name"><?=$Name;?></div>
			<?php if($products_row['BriefDescription'.$c['lang']]){?>
				<div class="prod_info_brief"><?=htmlspecialchars_decode($products_row['BriefDescription'.$c['lang']]);?></div>
			<?php }?>
			<?php if($Rating){?>
				<div class="prod_info_star"><?=html::mobile_review_star($Rating);?><span>(<?=$TotalRating;?>)</span></div>
			<?php }?>
			<?php
			//秒杀盒子
			if((int)$IsSeckill){
				$time=$sales_row['EndTime']-$c['time'];
				$progress=ceil((1-$sales_row['RemainderQty']/$sales_row['Qty'])*100);
			?>
				<div class="clean prod_info_seckill">
					<div class="title"><?=$c['lang_pack']['flashSale'];?></div>
					<div class="clear"></div>
					<div class="time"><i class="icon_time"></i><?=str_replace('%time%', '<span class="flashsale_time" endTime="'.date('Y/m/d H:i:s', $sales_row['EndTime']).'"></span>', $c['lang_pack']['dealsEnd']);?></div>
					<div class="progress_count"><?=($sales_row['Qty']-$sales_row['RemainderQty']).' '.$c['lang_pack']['sold'];?></div>
					<div class="progress"><div class="progress_current" style="width:<?=$progress;?>%;"></div></div>
				</div>
			<?php }?>
			<div class="clean prod_info_price">
				<?php if($c['config']['products_show']['Config']['price']){?>
				<div class="box_price clean price_0">
					<div class="fl title"><?=(((int)$SId && (int)$IsSeckill) || $is_promotion)?$c['lang_pack']['products']['originalPrice']:$c['lang_pack']['products']['marketPrice'];?>:</div>
					<del class="fl"><?=cart::iconv_price($oldPrice);?></del>
				</div>
				<?php }?>
				<div class="box_price clean price_1 last_price">
					<div class="fl title"><?=$c['lang_pack']['price'];?>:</div>
					<div class="fl">
						<div class="price cur_price"><span><?=$_SESSION['Currency']['Currency'].' '.$_SESSION['Currency']['Symbol'];?></span><?=cart::iconv_price($CurPrice, 2);?></div>
						<?php
						if($is_promotion){
							$time=$products_row['EndTime']-$c['time'];
							$promotion_discount=@round(sprintf('%01.2f', ($Price_1-$ItemPrice)/$Price_1*100));
							if($products_row['PromotionType']) $promotion_discount=100-$products_row['PromotionDiscount'];
							$month=ceil($time/86400);
							if($month<31) echo '<div class="onlydays">('.sprintf($c['lang_pack']['mobile']['only_days'], $month).')</div>';
						}?>
					</div>
					<?php if((int)$c['config']['products_show']['Config']['price']){?>
						<div class="clear"></div>
						<div class="save_price"><?=$c['lang_pack']['products']['save'].' <span class="save_p">'.cart::iconv_price($oldPrice-$CurPrice).'</span>';?><span class="save_style">(<?=$save_discount;?>% Off)</span></div>
					<?php }?>
				</div>
			</div>
			<div class="clean prod_info_line ui_border_t">
				<?php
				$ext_ary=array();
				$isHaveAttr=(int)($attr_ary['Cart'] && $products_row['AttrId']==($TopCategory_row?$TopCategory_row['AttrId']:$category_row['AttrId'])); //是否有规格属性
				
				if($isHaveAttr || $isHaveOversea){
					$combinatin_ary=$all_value_ary=$attrid=array();
					foreach($attr_ary['Cart'] as $v){ $attrid[]=$v['AttrId']; }
					$attrid_list=implode(',', $attrid);
					!$attrid_list && $attrid_list='0';
					$value_row=str::str_code(db::get_all('products_attribute_value', "AttrId in ($attrid_list)", '*', $c['my_order'].'VId asc')); //属性选项
					foreach($value_row as $v){ $all_value_ary[$v['AttrId']][$v['VId']]=$v; }
					//属性组合数据 Start
					$combinatin_row=str::str_code(db::get_all('products_selected_attribute_combination', "ProId='{$ProId}'", '*', 'CId asc'));
					foreach($combinatin_row as $v){
						$combinatin_ary[$v['Combination']][$v['OvId']]=array($v['Price'], $v['Stock'], $v['Weight'], $v['SKU'], $v['IsIncrease']);
						$key=str_replace('|', '_', substr($v['Combination'], 1, -1));
						$v['OvId']<1 && $v['OvId']=1;
						$IsCombination==1 && $key.=($key?'_':'').'Ov:'.$v['OvId'];
						$ext_ary[$key]=array($v['Price'], $v['Stock'], $v['Weight'], $v['SKU'], $v['IsIncrease']);
					}
					//属性组合数据 End
					foreach((array)$attr_ary['Cart'] as $k=>$v){
						if(!$selected_ary['Id'][$v['AttrId']]) continue; //踢走
						$v['ColorAttr'] && count($color_picpath_ary)<count($selected_ary['Id'][$v['AttrId']]) && $color_attr_status=1; //图片总数量少于选项总数量，图片不给予显示
				?>
						<div class="clean rows attr_show none" name="<?=$v['Name'.$c['lang']];?>">
							<div class="title"><?=$v['Name'.$c['lang']];?>:</div>
							<div class="txt">
								<?php
								foreach((array)$all_value_ary[$v['AttrId']] as $k2=>$v2){
									if(!in_array($k2, $selected_ary['Id'][$v['AttrId']])) continue; //踢走
									$value=$combinatin_ary["|{$k2}|"][1];
									$price=(float)$value[0];
									$qty=(int)$value[1];
									$weight=(float)$value[2];
									$sku=$value[3];
									$increase=(int)$value[4];
								?>
									<span value="<?=$v2['VId'];?>" data="<?=htmlspecialchars('{"Price":'.$price.',"Qty":'.$qty.',"Weight":'.$weight.',"SKU":'.$sku.',"IsIncrease":'.$increase.'}');?>" class="<?=((int)$c['config']['products_show']['Config']['stock'] && $IsCombination && $value && $qty<1)?' out_stock':'';?><?=($v['ColorAttr'] && !$color_attr_status)?' pic_color':'';?>" title="<?=htmlspecialchars($v2['Value'.$c['lang']]);?>">
										<?php
										if($v['ColorAttr'] && !$color_attr_status){
											echo '<a class="attr_pic"><img src="'.$color_picpath_ary[$v2['VId']].'" alt="'.$v2['Value'.$c['lang']].'" /></a>';
										}else{
											echo $v2['Value'.$c['lang']];
										}
										?>
									</span>
								<?php }?>
								<input type="hidden" name="id[<?=$v['AttrId'];?>]" id="attr_<?=$v['AttrId'];?>" attr="<?=$v['AttrId'];?>" value="" class="attr_value<?=$v['ColorAttr']?' colorid':'';?>" />
							</div>
						</div>
					<?php }?>
					<?php
					//发货地
					if($isHaveOversea){
					?>
						<div class="clean rows attr_show none" name="<?=$c['lang_pack']['products']['shipsFrom'];?>" style="display:<?=((int)$c['config']['global']['Overseas']==1 && count($selected_ary['Overseas'])>1 && $IsCombination==1)?'block':'none';?>;">
							<div class="title"><?=$c['lang_pack']['products']['shipsFrom'];?>:</div>
							<div class="txt">
								<?php
								foreach($c['config']['Overseas'] as $k=>$v){
									$Ovid='Ov:'.$v['OvId'];
									if(!$selected_ary['Overseas'] && $v['OvId']>1) continue; //踢走
									if($selected_ary['Overseas'] && !in_array($v['OvId'], $selected_ary['Overseas'])) continue; //踢走
									$value=$combinatin_ary['||'][$v['OvId']];
									$price=(float)$value[0];
									$qty=(int)$value[1];
									$weight=(float)$value[2];
									$sku=$value[3];
									$increase=(int)$value[4];
								?>
									<span value="<?=$Ovid;?>" data="<?=htmlspecialchars('{"Price":'.$price.',"Qty":'.$qty.',"Weight":'.$weight.',"SKU":'.$sku.',"IsIncrease":'.$increase.'}');?>" class="<?=((int)$c['config']['products_show']['Config']['stock'] && $IsCombination && $value && $qty<1)?' out_stock':'';?>" title="<?=htmlspecialchars($v['Name'.$c['lang']]);?>"><?=$v['Name'.$c['lang']];?><em>X</em></span>
								<?php }?>
								<input type="hidden" name="id[Overseas]" id="attr_Overseas" attr="Overseas" value="" class="attr_value" />
							</div>
						</div>
					<?php }?>
				<?php }?>
				<?php if($products_row['MOQ']>1){ //起订量?>
					<div class="clean rows">
						<div class="title"><?=$c['lang_pack']['mobile']['moq'];?>:</div>
						<div class="txt"><?=$products_row['MOQ'];?></div>
					</div>
				<?php }?>
				<?php if((int)$SId && (int)$IsSeckill && $sales_row['Qty']>0){?>
					<div class="clean rows">
						<div class="title"><?=$c['lang_pack']['mobile']['stock'];?>:</div>
						<div class="txt"><?=$sales_row['Qty'];?></div>
					</div>
					<div class="clean rows">
						<div class="title"><?=$c['lang_pack']['mobile']['remaining'];?>:</div>
						<div class="txt"><?=$sales_row['RemainderQty'];?></div>
					</div>
				<?php }elseif($c['config']['products_show']['Config']['sales']){ //下单数量?>
					<div class="clean rows">
						<div class="title"><?=$c['lang_pack']['sold'];?>:</div>
						<div class="txt"><?=$products_row['Sales'];?></div>
					</div>
                <?php }?>
				<div class="clean rows prod_info_qty" data="<?=htmlspecialchars('{"min":'.$MOQ.',"max":'.$Max.',"count":'.$MOQ.'}');?>">
                    <div class="title"><?=$c['lang_pack']['mobile']['QTY'];?>:</div>
                    <div class="txt">
						<div class="cut">-</div>
						<div class="qty"><input type="number" name="Qty" value="<?=$products_row['MOQ']?$products_row['MOQ']:1;?>" id="quantity" data-stock="<?=$Max;?>" /></div>
						<div class="add">+</div>
						<?php if($c['config']['products_show']['Config']['inventory']){?>
							<div class="stock"><?=str_replace('%num%', '<b id="inventory_number">'.$Max.'</b>', $c['lang_pack']['products']['stock']);?></div>
						<?php }?>
					</div>
                </div>
			</div>
			<div class="widget prod_info_actions clean">
				<?php if(!$is_stockout){?>
					<?php if($is_paypal_checkout){?>
						<div class="btn_buynow"><input type="button" value="" class="btn_global add_btn paypal_checkout_button" id="paypal_checkout_button" /></div>
					<?php }else{?>
						<div class="btn_buynow"><input type="button" value="<?=$c['lang_pack']['products']['buyNow'];?>" class="btn_global add_btn buynow BuyNowBgColor" id="buynow_button" /></div>
					<?php }?>
					<div class="btn_add"><input type="submit" value="<?=$c['lang_pack']['products']['addToCart'];?>" class="btn_global add_btn addtocart AddtoCartBgColor" id="addtocart_button" /></div>
				<?php }else{?>
					<input type="button" value="<?=$c['lang_pack']['products']['soldOut'];?>" class="btn_global add_btn soldout" />
				<?php }?>
                <?php
				//平台导流
				$platform=str::json_data(str::str_code($products_row['Platform'],'htmlspecialchars_decode'),'decode');
				if(count($platform)) echo '<div class="clear"></div>';
				foreach((array)$platform as $k => $v){
					if(!$v[0]['Url'.$c['lang']]) continue;
						if(count($v)>1){
				?>
						<div class="btn_global add_btn platform_btn btn_<?=$k?>">
							<i></i><?=$c['lang_pack']['products'][$k];?><em></em>
							<div class="platform_ab">
								<?php foreach((array)$v as $v1){?>
								<a href="<?=$v1['Url'.$c['lang']]?>" target="_blank"><?=$v1['Name'.$c['lang']]?></a>
								<?php }?>
							</div>
						</div>
                <?php
					}else{
						echo '<a href="'.$v[0]['Url'.$c['lang']].'" target="_blank" class="btn_global add_btn platform_btn btn_'.$k.'"><i></i>'.$c['lang_pack']['products'][$k].'</a>';
					}
				}?>
                <div class="clear"></div>
				<?php
				//收藏按钮
				if($c['config']['products_show']['Config']['favorite']){
					echo '<a href="javascript:;" class="add_favorite" data="'.$ProId.'">'.$c['lang_pack']['mobile']['add_wish'].'</a>';
				}
				//分享按钮
				if($c['config']['products_show']['Config']['share']){
					$sahre_data=str::json_data(db::get_value('config', "GroupId='global' and Variable='Share'", 'Value'), 'decode');
				?>
					<div class="clean share_toolbox" data-title="<?=$Name;?>" data-url="<?=ly200::get_domain().$_SERVER['REQUEST_URI'];?>">
						<ul>
							<li><?=$c['lang_pack']['shareThis'];?>: </li>
							<?php
							foreach($sahre_data as $k=>$v){
							?>
								<li><a href="javascript:;" rel="nofollow" class="share_s_btn share_s_<?=$v;?>" data="<?=$v;?>"><?=$v;?></a></li>
							<?php }?>
						</ul>
					</div>
				<?php }?>
			</div>
        	<input type="hidden" id="ProId" name="ProId" value="<?=$ProId;?>" />
			<input type="hidden" id="ItemPrice" name="ItemPrice" value="<?=$ItemPrice;?>" initial="<?=$ItemPrice;?>" sales="<?=$is_promotion?1:0;?>" salesPrice="<?=$is_promotion && !$products_row['PromotionType']?$products_row['PromotionPrice']:'';?>" discount="<?=$is_promotion && $products_row['PromotionType']?$products_row['PromotionDiscount']:'';?>" old="<?=$oldPrice;?>" />
			<input type="hidden" name="Attr" id="attr_hide" value="{}" />
			<input type="hidden" id="ext_attr" value="<?=htmlspecialchars(str::json_data($ext_ary));?>" />
			<input type="hidden" name="products_type" value="<?=$products_type;?>" />
			<input type="hidden" name="SId" value="<?=(int)$SId;?>"<?=((int)$IsSeckill && (int)$SId)?' stock="'.$Max.'"':'';?> />
            <input type="hidden" name="TId" value="<?=(int)$TId;?>" />
			<input type="hidden" id="CId" value="<?=(int)$country_default_row['CId'];?>" />
			<input type="hidden" id="CountryName" value="<?=$country_default_row['Country'];?>" />
			<input type="hidden" id="CountryAcronym" value="<?=$country_default_row['Acronym'];?>" />
			<input type="hidden" id="ShippingId" value="0" />
			<input type="hidden" id="attrStock" value="<?=(int)$c['config']['products_show']['Config']['stock'];?>" />
			<input type="hidden" id="IsCombination" value="<?=$IsCombination;?>" />
			<input type="hidden" id="IsDefaultSelected" value="<?=(int)$c['config']['products_show']['Config']['selected'];?>" />
        </form>
    </div>
	<div class="detail_list ui_border_b">
		<?php if($c['config']['products_show']['Config']['wholesale'] && !$IsSeckill && $is_wholesale){?>
			<div class="list_wholesale clean ui_border_tb" style="display:none;">
				<div class="wholesale_title"><?=$c['lang_pack']['mobile']['whole_price'];?></div>
				<dl class="wholesale_list" data="<?=$products_row['Wholesale'];?>">
					<dt class="item fl clean ui_border_b">
						<div class="wunits first"><?=$c['lang_pack']['mobile']['quantity'];?></div>
						<div class="wprice first"><?=$c['lang_pack']['mobile']['price'];?></div>
					</dt>
					<?php foreach($wholesale_price as $k=>$v){?>
						<dd class="item fl clean ui_border_b" data-num="<?=$k;?>">
							<div class="wunits"><?=$k;?>+</div>
							<div class="wprice" data-price="<?=$v;?>" data-discount="<?=1-($v/$Price_1);?>"><?=$_SESSION['Currency']['Currency'].' '.cart::iconv_price($v);?></div>
						</dd>
					<?php }?>
				</dl>
			</div>
		<?php }?>
		<div class="prod_info_divide"></div>
		<?php if((int)$c['FunVersion'] && $group_promotion_ary){?>
			<div class="list list_sale clean ui_border_tb">
				<a href="javascript:;" id="detail_sale">
					<div class="sale_info"><?=$c['lang_pack']['products']['sales_group'];?></div>
					<div class="sale_info_to"><?=$c['lang_pack']['products']['more_sales'];?></div>
					<em></em><i></i>
				</a>
			</div>
			<div class="prod_info_divide"></div>
		<?php }?>
		<?php if($c['config']['products_show']['Config']['freight']){?>
			<div class="list clean ui_border_tb">
				<a href="javascript:;" id="detail_shipping">
					<div class="shipping_cost_detail">
						<span class="shipping_cost_price FontColor"></span>
						<span class="shipping_cost_to"><?=$c['lang_pack']['products']['to'];?></span>
						<span id="shipping_cost_button" class="shipping_cost_button"></span>
					</div>
					<div class="shipping_cost_info"><?=$c['lang_pack']['products']['shipEstimated'];?>:<span class="delivery_day"></span></div>
					<div class="shipping_cost_error"><?=$c['lang_pack']['products']['shipError'];?></div>
					<em></em><i></i>
				</a>
			</div>
		<?php }?>
	</div>
	<div class="prod_info_divide"></div>
	<div class="prod_info_detail ui_border_b">
		<?php
		if($attr_ary['Common']){
			$all_value_ary=$attrid=array();
			foreach($attr_ary['Common'] as $v){ $attrid[]=$v['AttrId']; }
			$attrid_list=implode(',', $attrid);
			!$attrid_list && $attrid_list='0';
			$value_row=str::str_code(db::get_all('products_attribute_value', "AttrId in ($attrid_list)", '*', $c['my_order'].'VId asc')); //属性选项
			foreach($value_row as $v){ $all_value_ary[$v['AttrId']][$v['VId']]=$v; }
		?>
			<section class="detail_desc detail_close">
				<div class="t"><?=$c['lang_pack']['products']['specifics'];?><em></em><i></i></div>
				<div class="text ui_border_t">
					<?php
					foreach((array)$attr_ary['Common'] as $k=>$v){
						if(!$v || !$v['Name'.$c['lang']] || ($v['Type']==1 && !$selected_ary['Id'][$v['AttrId']]) || ($v['Type']==0 && !$selected_ary['Value'][$v['AttrId']])) continue;
					?>
					<div class="specifics_text">
						<strong><?=$v['Name'.$c['lang']];?>:</strong>
						<?php
						if($v['Type']==1 && is_array($all_value_ary[$v['AttrId']])){
							$i=0;
							foreach($all_value_ary[$v['AttrId']] as $k2=>$v2){
								if(in_array($v2['VId'], $selected_ary['Id'][$v['AttrId']])){
									echo ($i?', ':'').$v2['Value'.$c['lang']];
									++$i;
								}
							}
						}else echo $selected_ary['Value'][$v['AttrId']];
						?>
					</div>
					<?php }?>
				</div>
			</section>
		<?php }?>
		<section class="detail_desc">
			<div class="t<?=$attr_ary['Common']?' ui_border_t':'';?>"><?=$c['lang_pack']['mobile']['pro_detail'];?><em></em><i></i></div>
			<div class="text editor_txt ui_border_t">
				<?=str_replace('%nbsp;', ' ', str::str_code($products_description_row['Description'.$c['lang']], 'htmlspecialchars_decode'));?>
			</div>
		</section>
		<?php foreach($tab_row as $k=>$v){?>
			<section class="detail_desc detail_close">
				<div class="t ui_border_t"><?=$v['TabName'];?><em></em><i></i></div>
				<div class="text editor_txt ui_border_t">
					<?=str_replace('%nbsp;', ' ', str::str_code($v['Tab'], 'htmlspecialchars_decode'));?>
				</div>
			</section>
		<?php }?>
        <?php if($c['config']['products_show']['Config']['review']){?>
            <div class="prod_info_divide ui_border_t"></div>
            <div class="goods_review ui_border_b">
                <div class="title"><?=$c['lang_pack']['reviews'];?></div>
                <?=html::mobile_review_star($Rating);?><br />
                <?php if($TotalRating){?>
                    <div class="num">
                        <span class="review_nums">(<?=str_replace('%TotalRating%', $TotalRating, $c['lang_pack']['products']['basedOn']);?>)</span>
                    </div>
                <?php }?>
                <a href="<?=ly200::get_url($products_row, 'write_review');?>" class="btn_write_review FontBorderColor FontColor" rel="nofollow"><?=$c['lang_pack']['products']['writeReview'];?></a>
            </div>
            <?php include('review_box.php');?>
        <?php }?>
	</div>
</div>
<?php include("{$c['mobile']['theme_path']}inc/footer.php");?>

<div id="tips_cart">
	<p><?=$c['lang_pack']['mobile']['cart_add'];?></p><p><?=str_replace('%num%', '<span class="tips_cart_count"></span>', $c['lang_pack']['mobile']['cart_items']);?></p><p><?=$c['lang_pack']['cart']['total'];?>: <span class="tips_cart_total"></span><p class="consumption"><?=str_replace('%price%', '<span class="FontColor"></span>', $c['lang_pack']['mobile']['consumption']);?></p></p>
	<div class="blank5"></div>
	<a href="/cart/" class="btn_global btn_check"><?=$c['lang_pack']['mobile']['pro_to_check'];?></a>
	<a href="javascript:;" class="btn_global btn_return"><?=$c['lang_pack']['mobile']['re_to_shop'];?></a>
</div>

<?php
if((int)$c['FunVersion'] && $group_promotion_ary){//组合产品
	include("{$c['mobile']['theme_path']}products/combination.php");
}
if($c['config']['products_show']['Config']['freight']){//查询运费
?>
	<section id="detail_shipping_layer" class="prod_layer">
		<nav class="layer_head ui_border_b">
			<a class="layer_back" href="javascript:;"><em><i></i></em></a>
		</nav>
		<div class="layer_body">
			<div class="shipping_info">
				<div class="shipping_info_weight"><b><?=$c['lang_pack']['cart']['weight'];?>: </b><span>0.000</span>KG</div>
			</div>
			<form class="shipping_cost_form" name="shipping_cost_form" target="_blank" method="POST" action="">
				<div class="shipping_cost_country clean ui_border_tb">
					<a href="javascript:;" id="shipping_country">
						<span class="country_left"><?=$c['lang_pack']['user']['shipTo'];?></span>
						<span class="country_right">
							<em></em><i></i>
							<span class="title"><span class="title_wrap"><?=$country_default_row['Country'];?></span></span>
							<span id="shipping_flag" class="icon_flag flag_<?=strtolower($country_default_row['Acronym']);?>"></span>
							<select name="CId"></select>
						</span>
					</a>
				</div>
				<div class="prod_info_divide"></div>
				<div class="shipping_method clean ui_border_t">
					<div class="title"><?=$c['lang_pack']['mobile']['choose_ship'];?></div>
					<ul id="shipping_method_list"></ul>
				</div>
				<input type="hidden" name="ShippingSId" value="0" />
				<input type="hidden" name="ShippingMethodType" value="" />
				<input type="hidden" name="ShippingPrice" value="0" />
				<input type="hidden" name="ShippingExpress" value="" />
				<input type="hidden" name="ShippingBrief" value="" />
			</form>
		</div>
	</section>
<?php }?>
</body>
</html>