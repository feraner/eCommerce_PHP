<?php !isset($c) && exit();?>
<?php
$ProId=(int)$_GET['ProId'];
//判断“秒杀”或者“团购”页面的另行加载
$SId=(int)$_GET['SId'];
if($SId){//秒杀
	include("{$c['default_path']}seckill/index.php");
	exit;
}
$TId=(int)$_GET['TId'];
if($TId){//团购
	include("{$c['default_path']}tuan/index.php");
	exit;
}	

//首先更新评论
$count=(int)db::get_row_count('products_review', "ProId='{$ProId}' and Audit=1 and ReId=0");
$rating=(float)db::get_sum('products_review', "ProId='{$ProId}' and Audit=1 and ReId=0", 'Rating');
db::update('products', "ProId='{$ProId}'", array('Rating'=>($count?($rating/$count):0), 'TotalRating'=>$count));

$products_row=str::str_code(db::get_one('products', "ProId='$ProId'"));

//模块设置
$cfg_module_row=db::get_value('config_module', "Themes='{$c['theme']}'", 'ListData');
$list_data=str::json_data($cfg_module_row, 'decode');
$cfg_module_ary=array();
foreach((array)$list_data as $k=>$v){
	$cfg_module_ary[$k]=$v;
}

if(!$products_row){
	@header('HTTP/1.1 404');
	exit;
}

$Name=htmlspecialchars_decode($products_row['Name'.$c['lang']]);
$BriefDescription=htmlspecialchars_decode($products_row['BriefDescription'.$c['lang']]);
$Price_0=(float)$products_row['Price_0'];
$Price_1=(float)$products_row['Price_1'];
$MOQ=(int)$products_row['MOQ'];
$Max=(int)$products_row['Stock']; //最大购买上限
$products_row['MaxOQ']>0 && $products_row['MaxOQ']<$Max && $Max=$products_row['MaxOQ'];//最大购买量
$CateId=(int)$products_row['CateId'];
$CateId && $category_row=str::str_code(db::get_one('products_category', "CateId='$CateId'"));
$products_description_row=str::str_code(db::get_one('products_description', "ProId='$ProId'"));

//产品分类
if($category_row['UId']!='0,'){
	$TopCateId=category::get_top_CateId_by_UId($category_row['UId']);
	$SecCateId=category::get_FCateId_by_UId($category_row['UId']);
	$TopCategory_row=str::str_code(db::get_one('products_category', "CateId='$TopCateId'"));
}
$UId_ary=@explode(',', $category_row['UId']);

//产品售卖状态
$is_stockout=($products_row['Stock']<$products_row['MOQ'] || $products_row['Stock']<1 || $products_row['SoldOut'] || ($products_row['IsSoldOut'] && ($products_row['SStartTime']>$c['time'] || $c['time']>$products_row['SEndTime'])) || in_array($CateId, $c['procate_soldout']));

//产品单位
if($products_row['Unit']){//产品自身设置单位
	$Unit=$products_row['Unit'];
}elseif($c['config']['products_show']['Config']['item'] && $c['config']['products_show']['item']){//产品统一设置单位
	$Unit=$c['config']['products_show']['item'];
}else{
	$Unit=$c['lang_pack']['products']['units'];
}

//产品评论
$Rating=($products_row['IsDefaultReview'] && $products_row['DefaultReviewRating'])?(int)$products_row['DefaultReviewRating']:ceil($products_row['Rating']);
$TotalRating=($products_row['IsDefaultReview'] && $products_row['DefaultReviewTotalRating'])?$products_row['DefaultReviewTotalRating']:$products_row['TotalRating'];

//产品客户帮助
$help_data=str::json_data(db::get_value('config', '1'.where::equal(array('GroupId'=>'global', 'Variable'=>'ProDetail')), 'Value'), 'decode');
$help_ary=array();
foreach((array)$help_data as $k=>$v){
	$help_ary[$k]=$v;
}

//产品属性
$ParentId=(int)$products_row['AttrId']; //产品属性分类ID
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
$SId=(int)$_GET['SId'];
$secWhere="ProId='$ProId' and RemainderQty>0 and {$c['time']} between StartTime and EndTime";
$SId && $secWhere="SId='{$SId}' and ".$secWhere;
$sales_row=str::str_code(db::get_one('sales_seckill', $secWhere));
if($sales_row){
	$is_promotion=0;//秒杀优先于促销
	$IsSeckill=1;
	$CurPrice=$sales_row['Price'];
	$discount=($Price_1-$CurPrice)/((float)$Price_1?$Price_1:1)*100;
	$SId=$sales_row['SId'];
	$SMax=($sales_row['MaxQty'] && $sales_row['RemainderQty'] && $sales_row['RemainderQty']>=$sales_row['MaxQty']?$sales_row['MaxQty']:$sales_row['RemainderQty']); //最大购买上限
	$SMax<=$Max && $Max=$SMax;
}

//最后拍板
if(!$IsSeckill && $is_wholesale){
	$CurPrice>$maxPrice && $maxPrice=$CurPrice;
	$CurPrice<$minPrice && $minPrice=$CurPrice;
}
$discount=sprintf('%01.0f', $discount);
$discount=$discount<1?1:$discount;
$oldPrice=(($SId && $IsSeckill) || $is_promotion)?$Price_1:$Price_0;
$ItemPrice=$CurPrice;
$CurPrice=($is_promotion && $products_row['PromotionType']?$CurPrice*($products_row['PromotionDiscount']/100):$CurPrice);
$save_discount=@intval(sprintf('%01.2f', ($oldPrice-$CurPrice)/$oldPrice*100));
$save_discount=$save_discount<1?1:$save_discount;
$Max=$Max<1?0:$Max;

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

//产品选项卡
$tab_row=array();
$desc_row=str::str_code(db::get_one('products_category_description', 'CateId="'.$CateId.'"'));//产品分类详细
$ProIsDesc=str::json_data(htmlspecialchars_decode($products_row['IsDesc']), 'decode');
$CateIsDesc=str::json_data(htmlspecialchars_decode($category_row['IsDesc']), 'decode');
if($TopCateId){//顶级分类
	$top_desc_row=str::str_code(db::get_one('products_category_description', 'CateId="'.$TopCateId.'"'));
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

//快捷支付
$is_paypal_checkout=(int)db::get_row_count('payment', "Method='Excheckout' and IsUsed=1");

include('static.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=ly200::seo_meta($products_seo_row, $spare_ary);?>
<meta property="og:title" content="<?=$products_row['Name'.$c['lang']];?>"/>
<meta property="og:type" content="product"/>
<meta property="og:url" content="<?=ly200::get_domain().$_SERVER['REQUEST_URI'];?>"/>
<meta property="og:image" content="<?=ly200::get_domain().$products_row['PicPath_0'];?>"/>
<?php include("{$c['static_path']}/inc/static.php");?>
<?=ly200::load_static('/static/js/plugin/lightbox/css/lightbox.min.css');?>
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
<?php include("{$c['theme_path']}/inc/header.php");?>
<div id="main" class="wide"><?=$products_page_contents;?></div>
<?php include("{$c['theme_path']}/inc/footer.php");?>
<?=ly200::load_static("/static/js/plugin/products/detail/module.js", '/static/js/plugin/products/review.js', '/static/js/plugin/lightbox/js/lightbox.min.js');?>
<?php if($_GET['AddToCart']){//添加购物车时立即注册会员返回执行程序?>
<script type="text/javascript">
	global_obj.div_mask(1);
	$('#signin_module').remove();
	<?php
	if($_GET['Attr']){
		$Attr=str::json_data(htmlspecialchars_decode(str_replace('\\', '', $_GET['Attr'])), 'decode');
		foreach($Attr as $k=>$v){
			echo "$('ul.attributes #attr_{$k}').val({$v});";
		}
	}?>
	$('#addtocart_button').click();
</script>
<?php }?>
</body>
</html>