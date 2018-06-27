<?php !isset($c) && exit();?>
<div itemscope itemtype="http://schema.org/Product" class="clearfix">
	<meta id="schemaorg_product_name" itemprop="name" content="<?=$Name;?>" />
	<meta id="schemaorg_product_description" itemprop="description" content="<?=htmlspecialchars($BriefDescription);?>" />
	<meta id="schemaorg_product_sku" itemprop="sku" content="<?=$products_row['SKU']?$products_row['SKU']:$products_row['Prefix'].$products_row['Number'];?>" />
	<div class="detail_left fl prod_gallery_x"></div>
	<div class="detail_right fr">
		<div class="widget prod_info_title"><h2 itemprop="name"><?=$Name;?></h2></div>
		<?php if($BriefDescription){?>
			<div class="widget prod_info_brief"><?=$BriefDescription;?></div>
		<?php }?>
		<div class="widget prod_info_number"><?=$c['lang_pack']['products']['itemCode'].': '.$products_row['Prefix'].$products_row['Number'];?></div>
		<?php if($c['config']['products_show']['Config']['sales']){?>
			<div class="widget prod_info_number"><?=$c['lang_pack']['sold'].': '.$products_row['Sales'];?></div>
		<?php }?>
		<div class="widget prod_info_review">
			<?php if($c['config']['products_show']['Config']['review']){?>
				<?php if($TotalRating){?><span class="star star_s<?=$Rating;?>"></span><a class="write_review review_count" href="#review_box">(<?=$TotalRating;?>)</a><?php }?><a class="write_review track" href="<?=ly200::get_url($products_row, 'write_review');?>"><?=$c['lang_pack']['products']['writeReview'];?></a>
			<?php
			}
			if($c['config']['products_show']['Config']['share']){
			?>
				<div class="prod_info_share"><?php include("{$c['default_path']}/products/detail/share.php");?><b><?=$c['lang_pack']['products']['share'];?>:</b></div>
			<?php }?>
            <?php if($c['FunVersion']){?>
            <div class="prod_info_inquiry"><a class="product_inquiry" href="javascript:;" data-user="<?=(int)$_SESSION['User']['UserId']?>" data-proid="<?=$ProId;?>"><?=$c['lang_pack']['products']['haq'];?></a></div>
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
				<div class="price_info_title"><?=$c['lang_pack']['price'];?>:</div>
				<div itemprop="offers" itemscope itemtype="http://schema.org/Offer" class="current_price">
					<meta id="schemaorg_offer_price" itemprop="price" content="<?=cart::iconv_price($CurPrice, 2);?>" />
					<meta id="schemaorg_offer_currency" itemprop="priceCurrency" content="<?=$_SESSION['Currency']['Currency'];?>" />
					<meta id="schemaorg_offer_availability" itemprop="availability" content="<?=$is_stockout?'OutOfStock':'InStock';?>" />
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
					<?php
					if((int)$SId && (int)$IsSeckill){
						$time=$sales_row['EndTime']-$c['time'];
					?>
						<div class="discount_price discount_attr fl">(<span><?=$discount;?>% OFF</span>)</div>
						<?php if($time<86400*31){?><div class="discount_count fl"><div class="discount_time" endtime="<?=date('Y/m/d H:i:s', $sales_row['EndTime']);?>"></div></div><?php }?>
					<?php
					}elseif($is_promotion){
						$time=$products_row['EndTime']-$c['time'];
						$promotion_discount=sprintf('%01.2f', ($Price_1-$ItemPrice)/$Price_1*100);
						$promotion_discount=($promotion_discount>0 && $promotion_discount<1)?1:@intval($promotion_discount);
						if($products_row['PromotionType']) $promotion_discount=100-$products_row['PromotionDiscount'];
						echo '<div class="discount_price discount_attr fl">(<span>'.$promotion_discount.'% OFF</span>)</div>';
						$month=ceil($time/86400);
						if($month<31) echo '<div class="discount_sales discount_attr fl">(<span>'.str_replace('%time%', $month, $c['lang_pack']['products']['onlyDays']).'</span>)</div>';
					}
					?>
					<?php if((int)$c['config']['products_show']['Config']['price']){?>
						<div class="clear"></div>
						<div class="save_price"><?=$c['lang_pack']['products']['save'].' <span class="save_p">'.cart::iconv_price($oldPrice-$CurPrice).'</span>';?><span class="save_style">(<?=$save_discount;?>% Off)</span></div>
					<?php }?>
				</div>
			</div>
			<ul class="prod_info_group">
				<?php if($c['config']['products_show']['Config']['pdf']){?><li><a class="prod_info_pdf" href="javascript:;"><?=$c['lang_pack']['products']['pdf'];?></a></li><?php }?>
				<li><a class="prod_info_ok" href="<?=$c['config']['global']['Skype']?"skype:{$c['config']['global']['Skype']}?chat":'javascript:;'?>"><?=$c['lang_pack']['products']['ask'];?></a></li>
				<li><a class="prod_info_ok" href="<?=$c['config']['global']['AdminEmail']?"mailto:{$c['config']['global']['AdminEmail']}":'javascript:;'?>"><?=$c['lang_pack']['products']['email'];?></a></li>
				<?php foreach((array)$help_ary as $k=>$v){?>
					<li><a class="prod_info_ok" href="<?=$v['Url']?$v['Url']:'javascript:;';?>"<?=$v['NewTarget']==1?' target="_blank"':'';?>><?=$v['Name'.$c['lang']];?></a></li>
				<?php }?>
				<li><a class="prod_info_ok" href="/help/" target="_blank"><?=$c['lang_pack']['products']['faq'];?></a></li>
				<li><a class="prod_info_ok" href="javascript:window.print();"><?=$c['lang_pack']['products']['print'];?></a></li>
			</ul>
		</div>
		<?php if(count($tab_row)){?>
			<ul class="widget prod_info_data">
				<?php foreach((array)$tab_row as $k=>$v){?>
					<li><em class="icon_data_<?=$k;?>"></em><a data="<?=$k+1;?>" href="javascript:;"><?=$v['TabName'];?></a></li>
				<?php }?>
			</ul>
		<?php }?>
		<?php if($c['config']['products_show']['Config']['sku']){?>
			<div class="widget prod_info_sku" sku="<?=$products_row['SKU'];?>"<?=!$products_row['SKU']?' style="display:none;"':'';?>><h5>SKU:</h5><span><?=$products_row['SKU'];?></span></div>
		<?php }?>
		<form class="prod_info_form" name="prod_info_form" id="goods_form" action="/cart/add.html" method="post" target="_blank">
			<?php include("{$c['default_path']}/products/detail/attribute.php");?>
			<?php if($c['config']['products_show']['Config']['wholesale'] && !$IsSeckill && $is_wholesale){?>
				<div class="widget prod_info_wholesale" data="<?=$products_row['Wholesale'];?>">
					<div class="pw_title"><?=$c['lang_pack']['products']['wholesale'];?>:</div>
					<div class="pw_table clearfix">
						<?php foreach((array)$wholesale_price as $k=>$v){?>
							<div class="pw_column" data-num="<?=$k;?>">
								<div class="pw_td"><?=$k;?></div>
								<div class="pw_td" data-price="<?=$v;?>" data-discount="<?=1-($v/$Price_1);?>"><?=cart::iconv_price($v);?></div>
							</div>
						<?php }?>
					</div>
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
			<?php if($MOQ>1){?>
				<div class="widget prod_info_moq">
					<label for="moq"><?=$c['lang_pack']['products']['moq'];?>: <?=$MOQ;?></label>
				</div>
			<?php }?>
			<div class="widget prod_info_quantity">
            	<label for="quantity"><?=$c['lang_pack']['products']['qty'];?>:</label>
				<div class="quantity_box" data="<?=htmlspecialchars('{"min":'.$MOQ.',"max":'.$Max.',"count":'.$MOQ.'}');?>"><input id="quantity" class="qty_num" name="Qty" autocomplete="off" type="text" value="<?=$MOQ;?>" stock="<?=$Max;?>" /></div>
                <div class="qty_box"><div id="btn_add">+</div><div id="btn_cut">-</div></div>
				<span><?=$Unit;?></span>
				<?php if($c['config']['products_show']['Config']['inventory']){?><span class="prod_info_inventory"><?=str_replace('%num%', '<b id="inventory_number">'.$Max.'</b>', $c['lang_pack']['products']['stock']);?></span><?php }?>
                <div class="clear"></div>
			</div>
			<div class="widget prod_info_actions">
				<?php if($is_stockout && $products_row['StockOut']){?>
					<input type="button" value="<?=$c['lang_pack']['products']['notice'];?>" class="add_btn arrival" id="arrival_button" />
				<?php }elseif($is_stockout && !$products_row['StockOut']){?>
					<input type="button" value="<?=$c['lang_pack']['products']['soldOut'];?>" class="add_btn soldout" />
				<?php }else{?>
					<input type="submit" value="<?=$c['lang_pack']['products']['addToCart'];?>" class="add_btn addtocart AddtoCartBgColor" id="addtocart_button" />
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
					foreach((array)$platform as $k => $v){
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
                <?php }else{?>
                <a href="<?=$v[0]['Url'.$c['lang']]?>" target="_blank" class="add_btn <?=$k?>_btn"><?=$c['lang_pack']['products'][$k];?></a>
                <?php }}?>
				<?php if($c['config']['products_show']['Config']['favorite']){?>
					<div class="clear"></div>
					<a href="javascript:;" class="favorite_btn add_favorite" data="<?=$ProId;?>"><?=$c['lang_pack']['products']['favorite'];?></a>
				<?php }?>
			</div>
			<input type="hidden" id="ProId" name="ProId" value="<?=$ProId;?>" />
			<input type="hidden" id="ItemPrice" name="ItemPrice" value="<?=$ItemPrice;?>" initial="<?=$ItemPrice;?>" sales="<?=$is_promotion?1:0;?>" salesPrice="<?=$is_promotion && !$products_row['PromotionType']?$products_row['PromotionPrice']:'';?>" discount="<?=$is_promotion && $products_row['PromotionType']?$products_row['PromotionDiscount']:'';?>" old="<?=$oldPrice;?>" />
			<input type="hidden" name="Attr" id="attr_hide" value="[]" />
			<input type="hidden" id="ext_attr" value="<?=htmlspecialchars(str::json_data(str::str_code($ext_ary,'htmlspecialchars_decode')));?>" />
			<input type="hidden" name="products_type" value="<?=((int)$IsSeckill && (int)$SId)?2:0;?>" />
			<input type="hidden" name="SId" value="<?=(int)$SId;?>"<?=((int)$IsSeckill && (int)$SId)?' stock="'.$Max.'"':'';?> />
			<input type="hidden" id="CId" value="<?=(int)$country_default_row['CId'];?>" />
			<input type="hidden" id="CountryName" value="<?=$country_default_row['Country'];?>" />
			<input type="hidden" id="CountryAcronym" value="<?=$country_default_row['Acronym'];?>" />
			<input type="hidden" id="ShippingId" value="0" />
			<input type="hidden" id="attrStock" value="<?=(int)$c['config']['products_show']['Config']['stock'];?>" />
		</form>
	</div>
</div>
<?php if((int)$c['FunVersion'] && $group_promotion_ary) include("{$c['default_path']}/products/detail/combination.php");?>
<div class="clearfix">
	<div class="prod_desc_left fl">
		<?php include("{$c['default_path']}/products/detail/description.php");?>
		<?php if($c['config']['products_show']['Config']['review']) include("{$c['default_path']}/products/review/review_box.php");?>
	</div>
	<div class="prod_desc_right fr">
		<div id="may_like" class="sidebar">
			<h3 class="b_title"><?=$c['lang_pack']['products']['mayLike'];?></h3>
			<div class="b_list">
				<?php
				if($TopCateId){
					$UId="0,{$TopCateId},";
					$cateid=$TopCateId;
				}else{
					$UId=category::get_UId_by_CateId($CateId);
					$cateid=$CateId;
				}
				$row=str::str_code(db::get_limit('products', "1 and (CateId in(select CateId from products_category where UId like '{$UId}%') or CateId='{$cateid}' or ".category::get_search_where_by_ExtCateId($cateid, 'products_category').')'.$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 9));
				$len=count($row);
				foreach((array)$row as $k=>$v){
					$url=ly200::get_url($v, 'products');
					$price_ary=cart::range_price_ext($v);
				?>
					<dl class="pro_item clearfix<?=$k+1==$len?' last':'';?>">
						<dt class="fl"><a class="pic_box" href="<?=$url;?>"><img src="<?=ly200::get_size_img($v['PicPath_0'], '240x240');?>" title="<?=$v['Name'.$c['lang']];?>" alt="<?=$v['Name'.$c['lang']];?>" /><span></span></a></dt>
						<dd class="fl pro_info">
							<div class="pro_name"><a href="<?=$url;?>" title="<?=$v['Name'.$c['lang']];?>"><?=str::cut_str($v['Name'.$c['lang']], 45);?>..</a></div>
							<div class="pro_price"><em class="currency_data PriceColor"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0], 2);?></span></div>
						</dd>
					</dl>
				<?php }?>
			</div>
		</div>
	</div>
</div>
<div class="blank12"></div>