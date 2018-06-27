<?php !isset($c) && exit();?>
<?php
$ProId=(int)$_GET['ProId'];
$products_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
if(!$products_row){
	@header('HTTP/1.1 404');
	exit;
}

$IsTuan=1;
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

//团购
$tuan_row=str::str_code(db::get_one('sales_tuan', "ProId='$ProId' and BuyerCount<TotalCount and {$c['time']} between StartTime and EndTime"));
if($tuan_row){
	$CurPrice=$tuan_row['Price'];
	$discount=($Price_1-$CurPrice)/((float)$Price_1?$Price_1:1)*100;
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
	$color_row=str::str_code(db::get_all('products_color', "ProId='{$ProId}'", 'VId, PicPath_0'));
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

//默认国家参数
$country_default_row=str::str_code(db::get_one('country', 'IsUsed=1', 'CId, Country, Acronym, CountryData', 'IsDefault desc, Country asc'));
if($country_default_row['CountryData']){
	$country_default_data=str::json_data(htmlspecialchars_decode($country_default_row['CountryData']), 'decode');
	$country_default_row['Country']=$country_default_data[substr($c['lang'], 1)];
}

//加载文件
echo ly200::load_static('/static/js/plugin/lightbox/css/lightbox.min.css');

$StyleData=(int)db::get_row_count('config_module', 'IsDefault=1')?db::get_value('config_module', 'IsDefault=1', 'StyleData'):db::get_value('config_module', "Themes='{$c['theme']}'", 'StyleData');
$style_data=str::json_data($StyleData, 'decode');
?>
<style>
.detail_pic .viewport .list .item.current>a{border-color:<?=$style_data['FontColor'];?>;}
.detail_pic .small_carousel .top:hover, .detail_pic .small_carousel .bottom:hover{background-color:<?=$style_data['FontColor'];?>;}
</style>
<div id="tuan" class="wide">
	<div id="location"><a href="/"><?=$c['lang_pack']['products']['home'];?></a> &gt; <a href="/GroupBuying.html"><?=$c['lang_pack']['groupBuy'];?></a></div>
	<div class="clearfix">
		<h2 class="detail_title"><?=$Name;?></h2>
		<div class="detail_side fl">
			<div class="detail_left prod_gallery_x clearfix"></div>
			<div class="description">
				<div class="title"><?=$c['lang_pack']['proDetails'];?></div>
				<div class="content"><?=str::str_code($products_description_row['Description'.$c['lang']], 'htmlspecialchars_decode');?></div>
			</div>
			<?php if($c['config']['products_show']['Config']['review']) include("{$c['default_path']}/products/review/review_box.php");?>
		</div>
		<div class="detail_right fr">
			<div class="widget prod_info_tuan">
				<div class="item"><i class="icon_time"></i><?=str_replace('%time%', '<span class="flashsale_time" endTime="'.date('Y/m/d H:i:s', $tuan_row['EndTime']).'"></span>', $c['lang_pack']['saleEnd']);?></div>
				<div class="item"><i class="icon_bought"></i><?=$tuan_row['BuyerCount'];?><br /><?=$c['lang_pack']['bought'];?></div>
				<div class="item"><span class="star star_b<?=$Rating;?>"></span><br /><?=$TotalRating;?><br /><?=$c['lang_pack']['ratings'];?></div>
			</div>
			<div class="widget prod_info_price">
				<div class="cur_price"><span id="cur_price"><?=cart::iconv_price($CurPrice);?></span></div>
				<div class="price_1">
					<div class="price_0 fl"><del><?=cart::iconv_price($Price_1);?></del></div>
					<div class="save save_price fr"><strong><?=$c['lang_pack']['save'];?></strong><span class="save_p"><?=cart::iconv_price($Price_1-$CurPrice);?></span></div>
				</div>
			</div>
			<div class="widget prod_info_number"><?=$c['lang_pack']['products']['itemCode'].': '.$products_row['Prefix'].$products_row['Number'];?></div>
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
				<div class="widget prod_info_quantity hide">
					<div class="qty_box">
						<div class="quantity_box" data="<?=htmlspecialchars('{"min":1,"max":1,"count":1}');?>"><input id="quantity" class="qty_num" name="Qty" autocomplete="off" type="text" value="1" stock="1" /></div>
					</div>
				</div>
				<div class="widget prod_info_actions">
					<input type="button" value="<?=$c['lang_pack']['products']['buyNow'];?>" class="buynow BuyNowBgColor" id="buynow_button" />
				</div>
				<?php if($c['config']['products_show']['Config']['share']){?>
					<div class="prod_info_share">
						<div class="title"><?=$c['lang_pack']['shareThis'];?></div>
						<div class="content">
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
					</div>
				<?php }?>
				<input type="hidden" id="ProId" name="ProId" value="<?=$ProId;?>" />
				<input type="hidden" id="ItemPrice" name="ItemPrice" value="<?=$ItemPrice;?>" initial="<?=$ItemPrice;?>" sales="<?=$is_promotion?1:0;?>" salesPrice="<?=$is_promotion && !$products_row['PromotionType']?$products_row['PromotionPrice']:'';?>" discount="<?=$is_promotion && $products_row['PromotionType']?$products_row['PromotionDiscount']:'';?>" old="<?=$oldPrice;?>" />
				<input type="hidden" name="Attr" id="attr_hide" value="[]" />
				<input type="hidden" id="ext_attr" value="<?=htmlspecialchars(str::json_data($ext_ary));?>" />
				<input type="hidden" name="products_type" value="1" />
				<input type="hidden" name="SId" value="0" />
				<input type="hidden" id="TId" name="TId" value="<?=$tuan_row['TId'];?>" />
				<input type="hidden" id="CId" value="<?=(int)$country_default_row['CId'];?>" />
				<input type="hidden" id="CountryName" value="<?=$country_default_row['Country'];?>" />
				<input type="hidden" id="CountryAcronym" value="<?=$country_default_row['Acronym'];?>" />
				<input type="hidden" id="ShippingId" value="0" />
				<input type="hidden" id="attrStock" value="<?=(int)$c['config']['products_show']['Config']['stock'];?>" />
				<input type="hidden" id="IsTuan" value="1" />
			</form>
		</div>
	</div>
	<div class="related_pro clearfix">
		<div class="title"><?=$c['lang_pack']['relatedPro'];?></div>
		<div class="list">
			<?php
			$related_pro_row=str::str_code(db::get_limit('sales_tuan s left join products p on s.ProId=p.ProId', '1 and s.BuyerCount<s.TotalCount', "s.*, p.Name{$c['lang']}, p.PicPath_0, p.Price_0, p.Price_1, p.TotalRating, p.IsHot, p.IsDefaultReview, p.DefaultReviewRating, p.Rating, p.TotalRating", 'if(s.MyOrder>0, if(s.MyOrder=999, 1000001, s.MyOrder), 1000000) asc, s.TId desc', 0, 4));
			foreach((array)$related_pro_row as $k=>$v){
				$url=ly200::get_url($v, 'tuan');
				$img=ly200::get_size_img($v['PicPath_0'], '500x500');
				$price_ary=cart::range_price_ext($v);
				$old_price=$v['Price_1'];
				$discount=sprintf('%d', (($old_price-$v['Price'])/((float)$old_price?$old_price:1)*100));
				$discount=$discount<1?1:$discount;
				$rating=($v['IsDefaultReview'] && $v['DefaultReviewRating'])?(int)$v['DefaultReviewRating']:ceil($v['Rating']);
				$total_rating=($v['IsDefaultReview'] && $v['DefaultReviewTotalRating'])?$v['DefaultReviewTotalRating']:$v['TotalRating'];
			?>
				<div class="item<?=($k+1)%4==1?' first':'';?>">
					<div class="prod_box_pic">
						<a href="<?=$url;?>" title="<?=$v['Name'.$c['lang']];?>" class="pic_box">
							<img src="<?=$img;?>" alt="<?=$v['Name'.$c['lang']];?>" /><span></span>
						</a>
						<?php
						if($typ=='dealing'){
							$m=(int)@date('m', $v['EndTime'])-1;
							$d=date("Y, $m, j, G, i, s", $v['EndTime']);
						?>
							<div class="time"><?=str_replace('%time%', '<span id="flashsale_'.$v['ProId'].'" endTime="'.date('Y/m/d H:i:s', $v['EndTime']).'" proId="'.$v['ProId'].'"></span>', $c['lang_pack']['dealsEnd']);?></div>
						<?php }?>
					</div>
					<div class="name"><a href="<?=$url;?>" title="<?=$v['Name'.$c['lang']];?>"><?=$v['Name'.$c['lang']];?></a></div>
					<div class="info_first clearfix">
						<div class="sold fl"><span><?=$v['TotalCount']-$v['BuyerCount'];?></span> <?=$c['lang_pack']['sold'];?></div>
						<del class="old_price fr"><?=cart::iconv_price($v['Price_1']);?></del>
					</div>
					<div class="info_second clearfix">
						<div class="review fl"><span class="star star_s<?=$rating;?>"></span><strong>(<?=$v['TotalRating'];?>)</strong></div>
						<div class="price_1 fr"><?=cart::iconv_price($v['Price']);?></div>
					</div>
				</div>
			<?php }?>
		</div>
	</div>
	<div class="blank12"></div>
</div>
<?php /*<?=ly200::load_static('/static/tuan/default/js/tuan.js');*/?>
<?=ly200::load_static('/static/js/plugin/products/detail/module.js', '/static/js/plugin/products/review.js', '/static/js/plugin/lightbox/js/lightbox.min.js');?>
<script type="text/javascript">
(function($){
	$('.follow_us_list a').on('click', function(){//分享
		var $obj=$('.follow_us_list');
		$(this).shareThis($(this).attr('data'), $obj.attr('data-title'), $obj.attr('data-url'));
	});
})(jQuery);
</script>

