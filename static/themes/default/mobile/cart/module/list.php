<?php !isset($c) && exit();?>
<script type="text/javascript">$(function(){cart_obj.cart_list();});</script>
<?=html::mobile_crumb('<em><i></i></em><a href="/cart/">'.$c['lang_pack']['mobile']['shopping_cart'].'</a>');?>
<div id="cart">
	<?php
	$cart_row=db::get_all('shopping_cart c left join products p on c.ProId=p.ProId', 'c.'.$c['where']['cart'], "c.*, p.Name{$c['lang']}, p.CateId, p.PreFix, p.Number, p.AttrId, p.Attr, p.PageUrl, p.MOQ, p.Stock, p.SoldOut, p.IsSoldOut, p.SStartTime, p.SEndTime", 'c.CId desc');
	if(count($cart_row)){
	?>
		<div class="cart_list">
			<?php
			$total_price=0;
			$cart_attr=$cart_attr_data=$cart_attr_value=array();
			if((int)$_SESSION['User']['UserId']){//会员收藏夹
				$favorite_row=db::get_all('user_favorite', $c['where']['cart'], 'ProId');
				$favorite_pro=array();
				foreach($favorite_row as $k=>$v){
					$favorite_pro[]=$v['ProId'];
				}
			}
			//检查产品资料是否完整
			$error_ary=array();
			$products_attribute_error=0;
			foreach((array)$cart_row as $v){
				$AttrId=$v['AttrId'];
				if($AttrId && !$v['Property'] && $attr_row=db::get_all('products_attribute', "ParentId='{$AttrId}' and CartAttr=1", 'AttrId')){
					$AttrAry=@str::json_data(htmlspecialchars_decode($v['Attr']), 'decode');
					foreach($attr_row as $vv){
						if($AttrAry[$vv['AttrId']]){	//检查此产品是否有选择购物车属性
							$products_attribute_error=1;
							$error_ary["{$v['ProId']}_{$v['CId']}"]=1;
						}
					}
				}
				if(!$v['Name'.$c['lang']] && !$v['Number'] && (float)$v['Price']==0){ //产品资料丢失
					$error_ary["{$v['ProId']}_{$v['CId']}"]=1;
				}
				if($v['BuyType']!=4 && ($v['Stock']<$v['MOQ'] || $v['Stock']<1 || $v['SoldOut'] || ($v['IsSoldOut'] && ($v['SStartTime']>$c['time'] || $c['time']>$v['SEndTime'])) || in_array($v['CateId'], $c['procate_soldout']))){	//产品库存量不足（包括产品下架）
					$error_ary["{$v['ProId']}_{$v['CId']}"]=1;
				}
			}
			
			$cart_qty=$quantity=0;
			foreach($cart_row as $v){
				$attr=array();
				$v['Property']!='' && $attr=str::json_data(str::attr_decode($v['Property']), 'decode');
				!$attr && $attr=str::json_data(htmlspecialchars_decode($v['Property']), 'decode');
				$price=$v['Price']+$v['PropertyPrice'];
				$v['Discount']<100 && $price*=$v['Discount']/100;
				$img=ly200::get_size_img($v['PicPath'], '240x240');
				$url=ly200::get_url($v, 'products');
				if(!$error_ary["{$v['ProId']}_{$v['CId']}"]){
					$total_price+=cart::iconv_price($price, 2, '', 0)*$v['Qty'];
					$cart_qty+=1;
					$quantity+=$v['Qty'];
				}
			?>
				<div class="item clean ui_border_b<?=($error_ary["{$v['ProId']}_{$v['CId']}"] && $v['BuyType']!=1)?' null':'';?>" cid="<?=$v['CId'];?>">
					<div class="check fl">
						<em class="btn_checkbox<?=$error_ary["{$v['ProId']}_{$v['CId']}"]?' style="display:none;"':' current';?> FontBgColor"></em>
						<input type="checkbox" name="select" value="<?=$v['CId'];?>" class="va_m"<?=$error_ary["{$v['ProId']}_{$v['CId']}"]?'':' checked';?> />
					</div>
					<div class="img fl"><?php if(is_file($c['root_path'].$img)){?><img src="<?=$img;?>" alt="<?=$v['Name'.$c['lang']];?>" /><?php }?></div>
					<div class="info">
						<?php if($error_ary["{$v['ProId']}_{$v['CId']}"]){?>
							<div class="rows clean">
								<div class="error fl"><?=$c['lang_pack']['mobile']['product_error'];?></div>
								<div class="del fr" url="/cart/remove_c<?=$v['CId'];?>.html">X</div>
							</div>
						<?php }else{?>
							<div class="name"><a href="<?=$url;?>"><?=$v['Name'.$c['lang']];?></a></div>
							<div class="rows clean attr"><?=$v['PreFix'].$v['Number'];?></div>
							<?php
							if(count($attr)){
								foreach($attr as $k=>$z){
									if($k=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
									echo '<div class="rows clean attr">'.($k=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k).': &nbsp;'.$z.'</div>';
								}
							}
							if((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1){
								echo '<div class="rows clean attr">'.$c['lang_pack']['products']['shipsFrom'].': &nbsp;'.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</div>';
							}?>
							<div class="rows clean ui_border_t">
								<div class="price fl" data-price="<?=$v['Price'];?>" data-discount="<?=$v['Discount'];?>"><?=cart::iconv_price($price);?></div>
							</div>
							<div class="rows clean">
								<div class="qty_box fl">
									<div class="cut fl">-</div>
									<div class="qty fl"><input type="number" name="Qty[]" data-start="<?=$v['StartFrom'];?>" data-cid="<?=$v['CId'];?>" data-proid="<?=$v['ProId'];?>" value="<?=$v['Qty'];?>" class="fl" /></div>
									<div class="add fl">+</div>
								</div>
								<div class="del fr" url="/cart/remove_c<?=$v['CId'];?>.html"><?=$c['lang_pack']['user']['delete'];?></div>
							</div>
							<div class="rows clean remark_box">
								<input type="text" name="Remark[]" value="<?=htmlspecialchars($v['Remark']);?>" class="box_input" maxlength="100" placeholder="<?=$c['lang_pack']['mobile']['message'];?>..." data-cid="<?=$v['CId'];?>" />
							</div>
						<?php }?>
						<input type="hidden" name="S_Qty[]" value="<?=$v['Qty'];?>" />
						<input type="hidden" name="CId[]" value="<?=$v['CId'];?>" />
						<input type="hidden" name="ProId[]" value="<?=$v['ProId'];?>" />
					</div>
				</div>
			<?php }?>
		</div>
		<div class="cart_total">
			<?php
			//会员优惠价 与 全场满减价 比较
			$cutprice=$AfterPrice_0=$AfterPrice_1=0;
			$user_discount=100;
			if((int)$_SESSION['User']['UserId'] && (int)$_SESSION['User']['Level']){
				$user_discount=(float)db::get_value('user_level', "LId='{$_SESSION['User']['Level']}' and IsUsed=1", 'Discount');
				$user_discount=($user_discount>0 && $user_discount<100)?$user_discount:100;
				$AfterPrice_0=$total_price-($total_price*($user_discount/100));
			}
			if($cutArr['IsUsed']==1 && $c['time']>=$cutArr['StartTime'] && $c['time']<=$cutArr['EndTime']){
				foreach((array)$cutArr['Data'] as $k=>$v){
					if($total_price<$k) break;
					$AfterPrice_1=($cutArr['Type']==1?cart::iconv_price($v[1], 2, '', 0):($total_price*(100-$v[0])/100));
				}
			}
			if($AfterPrice_0==$AfterPrice_1){//当会员优惠价和全场满减价一致，默认只保留会员优惠价
				$AfterPrice_1=0;
			}
			(($AfterPrice_0 && !$AfterPrice_1) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_0>$AfterPrice_1)) && $cutprice=$AfterPrice_0;//会员优惠价
			(($AfterPrice_1 && !$AfterPrice_0) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_1>$AfterPrice_0)) && $cutprice=$AfterPrice_1;//全场满减价
			?>
			<div class="clean savings"<?=$AfterPrice_0?' userPrice="'.$AfterPrice_0.'" userRatio="'.$user_discount.'"':' userPrice="0" userRatio="100"';?><?=$cutprice?'':' style="display:none;"';?>>
				<span class="title"><?=$c['lang_pack']['mobile']['total_savings'];?>: </span>
				<span class="cutprice_p">-<?=cart::iconv_price(0, 1).cart::currency_format($cutprice, 0, $_SESSION['Currency']['Currency']);?></span>
			</div>
			<div class="clean total">
				<span class="title"><?=$c['lang_pack']['mobile']['cart_total'];?> <strong>(<?=str_replace('%num%', "<b>{$quantity}</b>", $c['lang_pack']['cart'][($quantity>1?'itemsCount':'itemCount')]);?>)</strong>:</span>
				<span class="p"><?=cart::iconv_price(0, 1).cart::currency_format($total_price-$cutprice, 0, $_SESSION['Currency']['Currency']);?></span>
			</div>
		</div>
        <input type="hidden" name="CartProductPrice" value="<?=cart::iconv_price($total_price, 2, '', 0);?>" />
        <div class="cart_btn">
			<?php if($a && (int)db::get_row_count('payment', "Method='Excheckout' and IsUsed=1")){?>
				<button class="btn paypal_checkout_button"></button>
			<?php }?>
            <div class="btn checkout BuyNowBgColor" data-name="<?=$c['lang_pack']['mobile']['checkout'];?>"><?=$c['lang_pack']['mobile']['checkout'];?></div>
        </div>
	<?php
	}else{ //购物车为空
	?>
		<div class="nocart"><?=$c['lang_pack']['mobile']['cart_empty'];?></div>
		<div class="cart_btn"><a href="/" class="btn_global btn_continue"><?=$c['lang_pack']['mobile']['continue_shop'];?></a></div>
	<?php }?>
	<div class="divide_8px"></div>
    <div class="cart_recently">
    	<div class="t"><?=$c['lang_pack']['mobile']['recen_item'];?></div>
        <div class="list clean">
        	<?php
			$i=0;
			foreach((array)$_COOKIE['history'] as $v){
				if(is_file($c['root_path'].$v['PicPath']) && $i<4){
					$pro_row=db::get_one('products', "ProId='{$v['ProId']}'");
					$url=ly200::get_url($pro_row, 'products');
					$img=ly200::get_size_img($v['PicPath'], '240x240');
					$name=$v['Name'];
			?>
				<div class="item fl ui_border_radius">
					<div class="pic pic_box"><a href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a></div>	
					<div class="name"><a href="<?=$url;?>" title="<?=$name;?>"><?=$name;?></a></div>
					<div class="price"><?=$_SESSION['Currency']['Currency'].' '.cart::iconv_price($v['Price']);?></div>
				</div>
            <?php
				}
				++$i;
			}?>
        </div>
		<div class="blank40"></div>
    </div>
</div>