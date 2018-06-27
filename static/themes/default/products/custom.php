<?php !isset($c) && exit();?>
<?php
$ProId=(int)$_GET['ProId'];
$products_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
if(!$products_row){
	@header('HTTP/1.1 404');
	exit;
}

//首先更新评论
$count=(int)db::get_row_count('products_review', "ProId='{$ProId}' and ReId=0");
$rating=(float)db::get_sum('products_review', "ProId='{$ProId}' and ReId=0", 'Rating');
db::update('products', "ProId='{$ProId}'", array('Rating'=>($count?($rating/$count):0), 'TotalRating'=>$count));

//模块设置
$cfg_module_row=db::get_value('config_module', "Themes='{$c['theme']}'", 'ListData');
$list_data=str::json_data($cfg_module_row, 'decode');
$cfg_module_ary=array();
foreach((array)$list_data as $k=>$v){
	$cfg_module_ary[$k]=$v;
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

//产品属性
$ParentId=(int)$products_row['AttrId']; //产品属性分类ID
$IsCombination=(int)$products_row['IsCombination']; //是否开启规格组合
$attr_ary=$color_attr_ary=$selected_ary=$color_picpath_ary=array();
$isHaveOversea=count($c['config']['Overseas']); //是否开启海外仓
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
		$v['AttrId']>0 && $v['VId']==0 && $v['OvId']==0 && $selected_ary['Value'][$v['AttrId']]=$v['Value']; //文本框内容
		$v['AttrId']==0 && $v['VId']==0 && $v['OvId']>=0 && $selected_ary['Overseas'][]=$v['OvId']; //记录勾选属性ID 发货地
		$v['VId'] && $color_where.=",{$v['VId']}";
	}
	$color_attr_status=0;
	$color_row=str::str_code(db::get_all('products_color', "ProId='{$ProId}' and VId in($color_where)", 'VId, PicPath_0'));
	if(count($color_row)){ //统计产品颜色图片
		foreach((array)$color_row as $k=>$v){
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
	$Max=($sales_row['MaxQty'] && $sales_row['RemainderQty']>0?$sales_row['MaxQty']:$sales_row['RemainderQty']); //最大购买上限
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

//SEO
$products_seo_row=str::str_code(db::get_one('products_seo', "ProId='$ProId'"));
$spare_ary=array(
	'SeoTitle'		=>	$Name.','.$category_row['Category'.$c['lang']],
	'SeoKeyword'	=>	$Name.','.$category_row['Category'.$c['lang']],
	'SeoDescription'=>	$Name.','.$category_row['Category'.$c['lang']].','.$TopCategory_row['Category'.$c['lang']]
);

//快捷支付
$is_paypal_checkout=(int)db::get_row_count('payment', "Method='Excheckout' and IsUsed=1");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?=substr($c['lang'], 1);?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=ly200::seo_meta($products_seo_row, $spare_ary);?>
<?php
include("{$c['static_path']}/inc/static.php");
include("{$c['static_path']}/inc/header.php");
echo ly200::load_static('/static/themes/default/css/products/detail/module_1.css', '/static/themes/default/css/products/detail/custom.css');

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
<div id="shopbox_outer" class="clearfix">
	<div class="detail_left fl"></div>
	<div class="detail_right fr">
		<div class="widget prod_info_title"><h1 itemprop="name"><?=$Name;?></h1></div>
		<div class="prod_info_number"><?=$c['lang_pack']['products']['itemCode'];?>: <?=$products_row['Prefix'].$products_row['Number'];?></div>
		<?php if($BriefDescription){?>
			<div class="widget prod_info_brief"><?=$BriefDescription;?></div>
		<?php }?>
		<div class="widget prod_info_view">
			<?php if($c['config']['products_show']['Config']['review']){?>
				<span class="star star_s<?=$Rating;?> fl"></span><?php if($TotalRating){?><a class="write_review review_count fl" href="javascript:;">(<?=$TotalRating;?> votes)</a><?php }?>
			<?php }?>
			<?php if($c['config']['products_show']['Config']['sales']){?>
				<div class="prod_info_sold fr"><?=$c['lang_pack']['sold'];?>: <?=$products_row['Sales'];?></div>
			<?php }?>
		</div>
		<div class="widget prod_info_price">
			<?php if($c['config']['products_show']['Config']['price']){?>
			<div class="widget price_left price_0">
				<div class="price_info_title"><?=(((int)$SId && (int)$IsSeckill) || $is_promotion)?$c['lang_pack']['products']['originalPrice']:$c['lang_pack']['products']['marketPrice'];?>:</div>
				<del><?=$_SESSION['Currency']['Currency'].' '.cart::iconv_price($oldPrice);?></del>
			</div>
			<?php }?>
			<div class="widget price_left price_1">
				<div class="price_info_title"><?=$c['lang_pack']['products']['unitPrice'];?>:</div>
				<div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="current_price">
					<meta id="schemaorg_offer_price" itemprop="price" content="<?=cart::iconv_price($CurPrice, 2);?>" />
					<meta id="schemaorg_offer_currency" itemprop="priceCurrency" content="<?=$_SESSION['Currency']['Currency'];?>" />
					<meta id="schemaorg_offer_availability" itemprop="availability" content="<?=$products_row['StockOut']?'OutOfStock':'InStock';?>" />
                    <?php $currency_row=db::get_all('currency', "IsUsed='1'");?>
					<div class="left">
						<dl class="widget prod_info_currency <?=count($currency_row)>1?'prod_info_currency_more':''?>">
							<dt><a href="javascript:;"><?=$_SESSION['Currency']['Currency'];?><div class="arrow"><em></em><i></i></div></a></dt>
							<dd>
								<ul>
									<?php foreach((array)$currency_row as $v){?>
									<li><a href="javascript:;" data="<?=$v['Currency'];?>"><?=$v['Currency'];?></a></li>
									<?php }?>
								</ul>
							</dd>
						</dl>
						<strong id="cur_price" class="price"><?=$_SESSION['Currency']['Symbol'].cart::iconv_price($CurPrice, 2);?></strong>
					</div>
					<?php if((int)$c['config']['products_show']['Config']['price']){?>
						<span class="save_style"><?=$save_discount;?>% OFF</span><div class="save_price"><?=$c['lang_pack']['products']['save'].' <span class="save_p">'.cart::iconv_price($oldPrice-$CurPrice).'</span>';?></div>
					<?php }?>
				</div>
			</div>
		</div>
		<form class="prod_info_form" name="prod_info_form" id="goods_form" action="/cart/add.html" method="post" target="_blank">
			<?php include("{$c['default_path']}/products/detail/attribute.php");?>
			<div class="prod_info_detail">
				<?php if($c['config']['products_show']['Config']['wholesale'] && !$IsSeckill && $is_wholesale){?>
					<div class="widget prod_info_wholesale" data="<?=$products_row['Wholesale'];?>">
						<div class="pw_title"><?=$c['lang_pack']['products']['wholesale'];?>:</div>
						<div class="pw_table clearfix">
							<div class="pw_table_box clearfix" data-show="3" data-sort="right">
								<?php foreach((array)$wholesale_price as $k=>$v){?>
									<div class="pw_column" data-num="<?=$k;?>">
										<div class="pw_td"><?=$k;?>+</div>
										<div class="pw_td" data-price="<?=$v;?>" data-discount="<?=sprintf('%01.2f', 1-($v/$Price_1));?>"><?=cart::iconv_price($v);?></div>
									</div>
								<?php }?>
							</div>
						</div>
						<?php if(count($wholesale_price)>3){?><a href="javascirpt:;" class="pw_btn"><i></i><em></em></a><?php }?>
					</div>
				<?php }?>
				<?php if($c['config']['products_show']['Config']['freight']){?>
					<div class="widget key_info_line">
						<div class="key_info_left"><?=$c['lang_pack']['products']['shippingCost'];?>:</div>
						<div class="key_info_right"> 
							<div class="shipping_cost_detail">
								<span class="shipping_cost_price"></span>
								<span class="shipping_cost_to"><?=$c['lang_pack']['products']['to'];?></span>
								<span id="shipping_flag" class="icon_flag"></span>
								<span id="shipping_cost_button" class="shipping_cost_button FontColor"></span>
							</div>
							<div class="shipping_cost_info"><?=$c['lang_pack']['products']['shipEstimated'];?>:<span class="delivery_day"></span></div>
							<div class="shipping_cost_error"><?=$c['lang_pack']['products']['shipError'];?></div>
						</div>
					</div>
				<?php }?>
				<div class="widget prod_info_quantity">
					<label for="quantity"><?=$c['lang_pack']['products']['qty'];?>:</label>
					<div class="qty_box">
						<div id="btn_cut">-</div>
						<div class="quantity_box" data="<?=htmlspecialchars('{"min":'.$MOQ.',"max":'.$Max.',"count":'.$MOQ.'}');?>"><input id="quantity" class="qty_num" name="Qty" autocomplete="off" type="text" value="<?=$MOQ;?>" stock="<?=$Max;?>" /></div>
						<div id="btn_add">+</div>
					</div>
					<?=$Unit;?><?php if($c['config']['products_show']['Config']['inventory']){?><span class="prod_info_inventory">(<?=str_replace('%num%', '<b id="inventory_number">'.$Max.'</b>', $c['lang_pack']['products']['available']);?>)</span><?php }?>
					<div class="clear"></div>
				</div>
			</div>
			<div class="widget prod_info_actions">
				<?php if($is_stockout && $products_row['StockOut']){?>
					<input type="button" value="<?=$c['lang_pack']['products']['notice'];?>" class="add_btn arrival" id="arrival_button">
				<?php }elseif($is_stockout && !$products_row['StockOut']){?>
					<input type="button" value="<?=$c['lang_pack']['products']['soldOut'];?>" class="add_btn soldout">
				<?php }else{?>
					<input type="submit" value="<?=$c['lang_pack']['products']['addToCart'];?>" class="add_btn addtocart AddtoCartBgColor" id="addtocart_button">
					<?php if($is_paypal_checkout){?>
						<input type="button" value="" class="add_btn paypal_checkout_button" id="paypal_checkout_button" />
						<?=ly200::load_static('/static/themes/default/css/cart.css', '/static/themes/default/js/cart.js');?>
						<script src="//www.paypalobjects.com/api/checkout.js" async></script>
					<?php }else{?>
						<input type="button" value="<?=$c['lang_pack']['products']['buyNow'];?>" class="add_btn buynow BuyNowBgColor" id="buynow_button" />
					<?php }?>
				<?php }?>
                <?php
					//平台导流
					$platform=str::json_data(str::str_code($products_row['Platform'],'htmlspecialchars_decode'),'decode');
					foreach((array)$platform as $k => $v){
				?>
                <a href="<?=$v?>" target="_blank" class="add_btn <?=$k?>_btn"><?=$c['lang_pack']['products'][$k];?></a>
                <?php }?>
				<?php if($c['config']['products_show']['Config']['favorite']){?>
					<div class="clear"></div>
					<a href="javascript:;" class="favorite_btn add_favorite" data="<?=$ProId;?>"><?=$c['lang_pack']['products']['favorite'];?></a>
				<?php }?>
				<a href="<?=ly200::get_url($products_row, 'products');?>" class="view_btn">View Full Details<i></i></a>
			</div>
			<div class="FontColor" style="display:none;">test</div>
			<input type="hidden" id="ProId" name="ProId" value="<?=$ProId;?>" />
			<input type="hidden" id="ItemPrice" name="ItemPrice" value="<?=$ItemPrice;?>" initial="<?=$ItemPrice;?>" sales="<?=$is_promotion?1:0;?>" discount="<?=$is_promotion && $products_row['PromotionType']?$products_row['PromotionDiscount']:'';?>" old="<?=$oldPrice;?>" />
			<input type="hidden" name="Attr" id="attr_hide" value="[]" />
			<input type="hidden" id="ext_attr" value="<?=htmlspecialchars(str::json_data($ext_ary));?>" />
			<input type="hidden" name="products_type" value="<?=((int)$IsSeckill && (int)$SId)?2:0;?>" />
			<input type="hidden" name="SId" value="<?=(int)$SId;?>"<?=((int)$IsSeckill && (int)$SId)?' stock="'.$sales_row['RemainderQty'].'"':'';?> />
			<input type="hidden" id="CId" value="<?=(int)$country_default_row['CId'];?>" />
			<input type="hidden" id="CountryName" value="<?=$country_default_row['Country'];?>" />
			<input type="hidden" id="CountryAcronym" value="<?=$country_default_row['Acronym'];?>" />
			<input type="hidden" id="ShippingId" value="0" />
			<input type="hidden" id="attrStock" value="<?=(int)$c['config']['products_show']['Config']['stock'];?>" />
		</form>
		<?php /*
		<div class="prod_info_item">
			<?php
			if($attr_ary['Common']){
				$all_value_ary=$attrid=array();
				foreach($attr_ary['Common'] as $v){ $attrid[]=$v['AttrId']; }
				$attrid_list=implode(',', $attrid);
				$value_row=str::str_code(db::get_all('products_attribute_value', "AttrId in ($attrid_list)", '*', $c['my_order'].'VId asc')); //属性选项
				foreach($value_row as $v){ $all_value_ary[$v['AttrId']][$v['VId']]=$v; }
			?>
				<div class="item_specifics">
					<div class="title"><?=$c['lang_pack']['products']['specifics'];?></div>
					<?php
					foreach((array)$attr_ary['Common'] as $k=>$v){
						if(!$v || !$v['Name'.$c['lang']] || ($v['Type']==1 && !$selected_ary['Id'][$v['AttrId']]) || ($v['Type']==0 && !$selected_ary['Value'][$v['AttrId']])) continue;
					?>
						<span>
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
						</span>
					<?php }?>
				</div>
			<?php }?>
		</div>
		*/?>
	</div>
</div>
<div class="blank12"></div>
<?=ly200::load_static("/static/js/plugin/products/detail/module.js");?>
<?=ly200::out_put_third_code();?>
</body>
</html>