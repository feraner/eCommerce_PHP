<?php !isset($c) && exit();?>
<?php
$ProId=(int)$_GET['ProId'];
$products_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
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
$products_description_row=str::str_code(db::get_one('products_description', "ProId='$ProId'"));

//产品价格
$CurPrice=$products_row['Price_1'];

//秒杀
$SId=(int)$_GET['SId'];
$sales_row=str::str_code(db::get_one('sales_seckill', "SId='{$SId}' and ProId='$ProId' and RemainderQty>0 and {$c['time']} between StartTime and EndTime"));
if($sales_row){
	$IsSeckill=1;
	$CurPrice=$sales_row['Price'];
	$discount=($Price_1-$CurPrice)/((float)$Price_1?$Price_1:1)*100;
	$SMax=($sales_row['MaxQty'] && $sales_row['RemainderQty'] && $sales_row['RemainderQty']>=$sales_row['MaxQty']?$sales_row['MaxQty']:$sales_row['RemainderQty']); //最大购买上限
	$SMax<=$Max && $Max=$SMax;
	$progress=(1-$sales_row['RemainderQty']/$sales_row['Qty'])*100;
}else{
	include($c['default_path'].'404.php');
	exit;
}

//产品分类
$CateId=(int)$products_row['CateId'];
$CateId && $category_row=str::str_code(db::get_one('products_category', "CateId='$CateId'"));
if($category_row['UId']!='0,'){
	$TopCateId=category::get_top_CateId_by_UId($category_row['UId']);
	$SecCateId=category::get_FCateId_by_UId($category_row['UId']);
	$TopCategory_row=str::str_code(db::get_one('products_category', "CateId='$TopCateId'"));
}
$UId_ary=@explode(',', $category_row['UId']);

//产品售卖状态
$is_stockout=(((int)$c['config']['products_show']['Config']['stock'] && ($products_row['Stock']<$products_row['MOQ'] || $products_row['Stock']<1)) || $products_row['SoldOut'] || ($products_row['IsSoldOut'] && ($products_row['SStartTime']>$c['time'] || $c['time']>$products_row['SEndTime'])) || in_array($CateId, $c['procate_soldout']));

//产品评论
$Rating=($products_row['IsDefaultReview'] && $products_row['DefaultReviewRating'])?(int)$products_row['DefaultReviewRating']:ceil($products_row['Rating']);
$TotalRating=($products_row['IsDefaultReview'] && $products_row['DefaultReviewTotalRating'])?$products_row['DefaultReviewTotalRating']:$products_row['TotalRating'];

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

//最后拍板
$discount=sprintf('%01.0f', $discount);
$discount=$discount<1?1:$discount;
$oldPrice=$Price_1;
$ItemPrice=$CurPrice;
$save_discount=@intval(sprintf('%01.2f', ($oldPrice-$CurPrice)/$oldPrice*100));
$save_discount=$save_discount<1?1:$save_discount;
$Max=$Max<1?0:$Max;

//默认国家参数
$country_default_row=str::str_code(db::get_one('country', 'IsUsed=1', 'CId, Country, Acronym, CountryData', 'IsDefault desc, Country asc'));
if($country_default_row['CountryData']){
	$country_default_data=str::json_data(htmlspecialchars_decode($country_default_row['CountryData']), 'decode');
	$country_default_row['Country']=$country_default_data[substr($c['lang'], 1)];
}

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

//加载文件
echo ly200::load_static('/static/js/plugin/lightbox/css/lightbox.min.css');

$StyleData=(int)db::get_row_count('config_module', 'IsDefault=1')?db::get_value('config_module', 'IsDefault=1', 'StyleData'):db::get_value('config_module', "Themes='{$c['theme']}'", 'StyleData');
$style_data=str::json_data($StyleData, 'decode');
?>
<style>
.detail_pic .viewport .list .item.current>a{border-color:<?=$style_data['FontColor'];?>;}
.detail_pic .small_carousel .top:hover, .detail_pic .small_carousel .bottom:hover{background-color:<?=$style_data['FontColor'];?>;}
</style>
<div id="seckill" class="wide">
	<div id="location"><?=$c['lang_pack']['products']['position'];?>: <a href="/"><?=$c['lang_pack']['products']['home'];?></a> &gt; <a href="/FlashSale.html"><?=$c['lang_pack']['flashSale'];?></a></div>
	<div class="clearfix">
		<div class="sec_picture fl">
			<div class="detail_left prod_gallery_x clearfix"></div>
			<?php if($c['config']['products_show']['Config']['share']){?>
				<div class="prod_info_share">
					<div class="follow_us_list follow_us_type_0 clearfix" data-title="<?=$Name;?>" data-url="<?=ly200::get_domain().$_SERVER['REQUEST_URI'];?>">
						<ul>
							<?php
							foreach($c['follow'] as $v){
								if($v=='Instagram' || $v=='YouTube') continue;
							?>
								<li><a rel="nofollow" class="icon_follow_<?=strtolower($v);?>" href="javascript:;" title="<?=$v;?>" data="<?=strtolower($v);?>"><?=$v;?></a></li>
							<?php }?>
						</ul>
					</div>
				</div>
			<?php }?>
		</div>
		<div class="detail_right fr">
			<div class="widget prod_info_title"><h2 itemprop="name"><?=$Name;?></h2></div>
			<?php if($BriefDescription){?>
				<div class="widget prod_info_brief"><?=$BriefDescription;?></div>
			<?php }?>
			<div class="widget prod_info_infomation">
				<?php if($c['config']['products_show']['Config']['review']){?>
					<div class="review"><?php if($TotalRating){?><span class="star star_s<?=$Rating;?>"></span> <a class="write_review review_count" href="#review_box">(<?=$TotalRating;?>)</a><?php }?><a class="write_review track" href="<?=ly200::get_url($products_row, 'write_review');?>"><?=$c['lang_pack']['products']['writeReview'];?></a></div>
				<?php }?>
				<?php if($c['FunVersion']){?>
					<div class="inquiry"><a class="product_inquiry" href="javascript:;" data-user="<?=(int)$_SESSION['User']['UserId'];?>" data-proid="<?=$ProId;?>"><?=$c['lang_pack']['products']['haq'];?></a></div>
				<?php }?>
				<div class="number"><?=$c['lang_pack']['products']['itemCode'].': '.$products_row['Prefix'].$products_row['Number'];?></div>
			</div>
			<div class="widget prod_info_seckill">
				<div class="title"><?=$c['lang_pack']['flashSale'];?></div>
				<div class="time"><i class="icon_time"></i><?=str_replace('%time%', '<span class="flashsale_time" endTime="'.date('Y/m/d H:i:s', $sales_row['EndTime']).'"></span>', $c['lang_pack']['dealsEnd']);?></div>
				<div class="progress"><div class="progress_current" style="width:<?=$progress;?>%;"></div></div><div class="progress_count"><?=$c['lang_pack']['only'];?> <?=100-$progress;?>%</div>
			</div>
			<div class="widget prod_info_price">
				<?php if($c['config']['products_show']['Config']['price']){?>
					<div class="widget price_left price_0">
						<div class="price_info_title"><?=$c['lang_pack']['products']['originalPrice'];?>:</div>
						<del><?=$_SESSION['Currency']['Currency'].' '.cart::iconv_price($oldPrice);?></del>
					</div>
				<?php }?>
				<div class="widget price_left price_1">
					<div class="price_info_title"><?=$c['lang_pack']['price'];?>:</div>
					<div class="current_price">
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
					</div>
				</div>
			</div>
			<form class="prod_info_form" name="prod_info_form" id="goods_form" action="/cart/add.html" method="post" target="_blank">
				<?php
				$ext_ary=array();
				$isHaveAttr=(int)($attr_ary['Cart'] && $products_row['AttrId']==($TopCategory_row?$TopCategory_row['AttrId']:$category_row['AttrId'])); //是否有规格属性
				if($isHaveAttr || $isHaveOversea){
					$combinatin_ary=$all_value_ary=$attrid=array();
					foreach($attr_ary['Cart'] as $v){ $attrid[]=$v['AttrId']; }
					$attrid_list=implode(',', $attrid);
					!$attrid_list && $attrid_list=0;
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
				?>
					<ul class="widget attributes" default_selected="<?=(int)$c['config']['products_show']['Config']['selected'];?>" data-combination="<?=$IsCombination;?>" data-stock="<?=(int)$c['config']['products_show']['Config']['stock'];?>">
						<div class="attr_sure"><span class="attr_sure_choice"><?=$c['lang_pack']['products']['attributes_tips'];?></span><span class="attr_sure_close">X</span></div>
						<?php
						foreach((array)$attr_ary['Cart'] as $k=>$v){
							if(!$selected_ary['Id'][$v['AttrId']]) continue; //踢走
						?>
							<li name="<?=$v['Name'.$c['lang']];?>">
								<span><?=$v['Name'.$c['lang']];?>:</span>
								<div class="box_select">
									<select name="id[<?=$v['AttrId'];?>]" id="attr_<?=$v['AttrId'];?>" attr="<?=$v['AttrId'];?>"<?=$v['ColorAttr']?' class="colorid"':'';?>>
										<option value=""><?=str_replace('%name%', $v['Name'.$c['lang']], $c['lang_pack']['products']['select']);?></option>
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
										<option value="<?=$v2['VId'];?>" data="<?=htmlspecialchars('{"Price":'.$price.',"Qty":'.$qty.',"Weight":'.$weight.',"SKU":'.$sku.',"IsIncrease":'.$increase.'}');?>"<?=((int)$c['config']['products_show']['Config']['stock'] && $IsCombination && $value && $qty<1)?' class="hide" disabled':'';?>><?=$v2['Value'.$c['lang']].' '.((!$IsCombination || $increase) && $price>0?' (+'.cart::iconv_price($price).')':'');?></option>
										<?php }?>
									</select>
								</div>
							</li>
						<?php
						}
						if($isHaveOversea){
						?>
							<li name="<?=$c['lang_pack']['products']['shipsFrom'];?>" style="display:<?=((int)$c['config']['global']['Overseas']==1 && count($selected_ary['Overseas'])>1 && $IsCombination==1)?'block':'none';?>;">
								<span><?=$c['lang_pack']['products']['shipsFrom'];?>:</span>
								<div class="box_select">
									<select name="id[Overseas]" id="attr_Overseas" attr="Overseas">
										<option value=""><?=str_replace('%name%', $c['lang_pack']['products']['shipsFrom'], $c['lang_pack']['products']['select']);?></option>
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
										<option value="<?=$Ovid;?>" data="<?=htmlspecialchars('{"Price":'.$price.',"Qty":'.$qty.',"Weight":'.$weight.',"SKU":'.$sku.',"IsIncrease":'.$increase.'}');?>"<?=((int)$c['config']['products_show']['Config']['stock'] && $IsCombination && $value && $qty<1)?' class="hide" disabled':'';?>><?=$v['Name'.$c['lang']].' '.((!$IsCombination || $increase) && $price>0?' (+'.cart::iconv_price($price).')':'');?></option>
										<?php }?>
									</select>
								</div>
							</li>
						<?php }?>
					</ul>
				<?php }?>
				<div class="widget prod_info_quantity">
					<label for="quantity"><?=$c['lang_pack']['products']['qty'];?>:</label>
					<div class="qty_box">
						<div id="btn_cut">-</div>
						<div class="quantity_box" data="<?=htmlspecialchars('{"min":'.$MOQ.',"max":'.$Max.',"count":'.$MOQ.'}');?>"><input id="quantity" class="qty_num" name="Qty" autocomplete="off" type="text" value="<?=$MOQ;?>" stock="<?=$Max;?>" /></div>
						<div id="btn_add">+</div>
					</div>
					<?php if($c['config']['products_show']['Config']['favorite']){?>
						<a href="javascript:;" class="favorite_btn add_favorite" data="<?=$ProId;?>"><?=$c['lang_pack']['products']['favorite'];?></a>
					<?php }?>
					<div class="clear"></div>
				</div>
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
				<div class="widget prod_info_actions">
					<?php if($is_stockout && $products_row['StockOut']){?>
						<input type="button" value="<?=$c['lang_pack']['products']['notice'];?>" class="add_btn arrival" id="arrival_button" />
					<?php }elseif($is_stockout && !$products_row['StockOut']){?>
						<input type="button" value="<?=$c['lang_pack']['products']['soldOut'];?>" class="add_btn soldout" />
					<?php }else{?>
						<input type="submit" value="+ <?=$c['lang_pack']['products']['addToCart'];?>" class="add_btn addtocart AddtoCartBgColor" id="addtocart_button" />
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
					if(count($platform)) echo '<div class="clear"></div>';
					foreach((array)$platform as $k=>$v){
						if(!$v[0]['Url'.$c['lang']]) continue;
						if(count($v)>1){
					?>
						<span class="add_btn platform_btn <?=$k?>_btn">
							<?=$c['lang_pack']['products'][$k];?><em></em>
							<div class="platform_ab">
								<?php foreach((array)$v as $v1){?>
									<a href="<?=$v1['Url'.$c['lang']]?>" target="_blank"><?=$v1['Name'.$c['lang']]?></a>
								<?php }?>
							</div>
						</span>
					<?php
						}else{
					?>
						<a href="<?=$v[0]['Url'.$c['lang']]?>" target="_blank" class="add_btn platform_once_btn <?=$k?>_btn"><?=$c['lang_pack']['products'][$k];?></a>
					<?php
						}
					}?>
				</div>
				<input type="hidden" id="ProId" name="ProId" value="<?=$ProId;?>" />
				<input type="hidden" id="ItemPrice" name="ItemPrice" value="<?=$ItemPrice;?>" initial="<?=$ItemPrice;?>" sales="<?=$is_promotion?1:0;?>" salesPrice="<?=$is_promotion && !$products_row['PromotionType']?$products_row['PromotionPrice']:'';?>" discount="<?=$is_promotion && $products_row['PromotionType']?$products_row['PromotionDiscount']:'';?>" old="<?=$oldPrice;?>" />
				<input type="hidden" name="Attr" id="attr_hide" value="[]" />
				<input type="hidden" id="ext_attr" value="<?=htmlspecialchars(str::json_data($ext_ary));?>" />
				<input type="hidden" name="products_type" value="2" />
				<input type="hidden" name="SId" value="<?=(int)$SId;?>"<?=((int)$IsSeckill && (int)$SId)?' stock="'.$Max.'"':'';?> />
				<input type="hidden" id="CId" value="<?=(int)$country_default_row['CId'];?>" />
				<input type="hidden" id="CountryName" value="<?=$country_default_row['Country'];?>" />
				<input type="hidden" id="CountryAcronym" value="<?=$country_default_row['Acronym'];?>" />
				<input type="hidden" id="ShippingId" value="0" />
				<input type="hidden" id="attrStock" value="<?=(int)$c['config']['products_show']['Config']['stock'];?>" />
				<input type="hidden" id="IsSeckill" value="1" />
			</form>
		</div>
	</div>
	<div class="clearfix">
		<?php include("{$c['default_path']}/products/detail/description.php");?>
	</div>
	<?php if($c['config']['products_show']['Config']['review']) include("{$c['default_path']}/products/review/review_box.php");?>
	<div class="blank12"></div>
</div>
<?=ly200::load_static('/static/js/plugin/products/detail/module.js', '/static/js/plugin/products/review.js', '/static/js/plugin/lightbox/js/lightbox.min.js');?>
<script type="text/javascript">
(function($){
	$('.follow_us_list a').on('click', function(){//分享
		var $obj=$('.follow_us_list');
		$(this).shareThis($(this).attr('data'), $obj.attr('data-title'), $obj.attr('data-url'));
	});
})(jQuery);
</script>


