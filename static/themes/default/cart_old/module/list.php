<?php !isset($c) && exit();?>
<script type="text/javascript">$(document).ready(function(){cart_obj.cart_list()});</script>
<div id="lib_cart">
<?php
$cart_row=db::get_all('shopping_cart c left join products p on c.ProId=p.ProId', 'c.'.$c['where']['cart'], "c.*, p.Name{$c['lang']}, p.Prefix, p.Number", 'c.CId desc');
if(count($cart_row)){
	$attribute_cart_ary=$vid_data_ary=array();
	$attribute_row=str::str_code(db::get_all('products_attribute', '1', "AttrId, Type, Name{$c['lang']}, ParentId, CartAttr, ColorAttr"));
	foreach($attribute_row as $v){
		$attribute_ary[$v['AttrId']]=array(0=>$v['Type'], 1=>$v["Name{$c['lang']}"]);
	}
	$value_row=str::str_code(db::get_all('products_attribute_value', '1', '*', $c['my_order'].'VId asc')); //属性选项
	foreach($value_row as $v){
		$vid_data_ary[$v['AttrId']][$v['VId']]=$v["Value{$c['lang']}"];
	}
	//检查产品资料是否完整
	$error_ary=array();
	foreach((array)$cart_row as $v){
		if(!$v['Name'.$c['lang']] && !$v['Number'] && (float)$v['Price']==0){ //产品资料丢失
			$error_ary["{$v['ProId']}_{$v['CId']}"]=1;
		}
	}
?>
    <div class="step"><div></div></div>
	<div class="cartHeader">
        <a name="continue_shopping" class="textbtn fl" title="<?=$c['lang_pack']['cart']['continue'];?>"><?=$c['lang_pack']['cart']['continue'];?></a>
        <a class="checkoutBtn fr"><?=$c['lang_pack']['cart']['checkout'];?></a>
    </div>
    <form name="shopping_cart">
    <div class="cartFrom">
    	<table width="100%" align="center" cellpadding="12" cellspacing="0" border="0" class="itemFrom">
        	<thead>
            	<tr>
					<td width="1%" class="first"><input type="checkbox" name="select_all" value="" class="va_m"<?=!count($error_ary)?' checked':'';?> /></td>
                	<td width="48%"><?=$c['lang_pack']['cart']['item'];?></td>
                	<td width="16%"><?=$c['lang_pack']['cart']['price'];?></td>
                	<td width="20%" class="quantity"><?=$c['lang_pack']['cart']['qty'];?></td>
                	<td width="16%"><?=$c['lang_pack']['cart']['amount'];?></td>
                </tr>
            </thead>
            <tbody>
            	<?php 
				$total_price=$s_total_price=$quantity=0;//$ptotal=
				$cart_attr=$cart_attr_data=$cart_attr_value=array();
				foreach((array)$cart_row as $v){
					/********* 组合促销 Start *********/
					if($v['BuyType']==4){
						$total_price+=$v['Price'];
						$package_row=str::str_code(db::get_one('sales_package', "PId='{$v['KeyId']}'"));
						if(!$package_row) continue;
						$attr=array();
						$v['Property']!='' && $attr=str::json_data(str::attr_decode($v['Property']), 'decode');
						$products_row=str::str_code(db::get_all('products', "SoldOut=0 and ProId='{$package_row['ProId']}'"));
						$pro_where=str_replace('|', ',', substr($package_row['PackageProId'], 1, -1));
						$pro_where=='' && $pro_where=0;
						$products_row=array_merge($products_row, str::str_code(db::get_all('products', "SoldOut=0 and ProId in($pro_where)")));
						$data_ary=str::json_data(htmlspecialchars_decode($package_row['Data']), 'decode');
				?>
            	<tr>
					<td><input type="checkbox" name="select" value="<?=$v['CId'];?>" class="va_m" checked /></td>
                	<td class="prList">
						<h4>[ <?=$c['lang_pack']['cart']['package']?> ] <?=$package_row['Name'];?></h4>
						<?php
						foreach((array)$products_row as $k2=>$v2){
							$img=ly200::get_size_img($v2['PicPath_0'], '240x240');
							$url=ly200::get_url($v2, 'products');
							$quantity+=$v['Qty'];
						?>
						<dl class="clearfix pro_list<?=$k2?'':' first';?>">
                        	<dt><a href="<?=$url;?>"><img src="<?=$img;?>" alt="<?=$v2['Name'.$c['lang']];?>" name="<?=$v2['Name'.$c['lang']];?>" /></a></dt>
                            <dd>
								<h4><a href="<?=$url;?>"><?=$v2['Name'.$c['lang']];?></a></h4>
								<?php if($k2==0){?>
									<div>
										<?php
										foreach((array)$attr as $k=>$z){
											if($k=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
											echo '<p class="attr_'.$k.'">'.($k=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k).': '.$z.'</p>';
										}
										if((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1){
											echo '<p class="attr_Overseas">'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</p>';
										}?>
									</div>
								<?php }elseif($data_ary[$v2['ProId']]){?>
									<div>
										<?php
										$OvId=1;
										foreach((array)$data_ary[$v2['ProId']] as $k3=>$v3){
											if($k3=='Overseas'){ //发货地
												$OvId=str_replace('Ov:', '', $v3);
												if((int)$c['config']['global']['Overseas']==0 || $OvId==1) continue; //发货地是中国，不显示
												echo '<p class="attr_Overseas">'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</p>';
											}else{
												echo '<p class="attr_'.$k3.'">'.$attribute_ary[$k3][1].': '.$vid_data_ary[$k3][$v3].'</p>';
											}
										}
										if((int)$c['config']['global']['Overseas']==1 && $OvId==1){
											echo '<p class="attr_Overseas">'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</p>';
										}?>
									</div>
								<?php }?>
							</dd>
							<?=$k2?'<dd class="list_dot"></dd>':'';?>
                        </dl>
						<?php }?>
                    	<dl>
                            <dd>
                                <p class="remark"><?=$c['lang_pack']['cart']['remark'];?>: <input type="text" name="Remark[]" value="<?=htmlspecialchars($v['Remark']);?>" maxlength="200" data="<?=$v['Remark'];?>" /><?php /*?> <span><img src="/static/themes/default/images/cart/edit.png" /></span><?php */?></p>
                            </dd>
                        </dl>
					</td>	
                	<td class="prPrice"><p><?=cart::iconv_price($v['Price']);?></p></td>
                	<td class="prQuant" start="<?=$v['StartFrom'];?>">
                        <input type="text" name="Qty[]" value="<?=$v['Qty'];?>" maxlength="4" readonly />
                        <input type="hidden" name="S_Qty[]" value="<?=$v['Qty'];?>" />
                        <input type="hidden" name="CId[]" value="<?=$v['CId'];?>" />
                        <input type="hidden" name="ProId[]" value="<?=$v['ProId'];?>" />
                    </td>
					<td class="prAmount"><p price="<?=cart::iconv_price($v['Price'], 2, '', 0)*$v['Qty'];?>"><?=cart::iconv_price(0, 1).cart::currency_format(cart::iconv_price($v['Price'], 2, '', 0)*$v['Qty'], 0, $_SESSION['Currency']['Currency']);?></p><a href="/cart/remove_c<?=sprintf('%04d', $v['CId']);?>.html" rel="del" title="<?=$c['lang_pack']['cart']['removeitem'];?>"><?=$c['lang_pack']['cart']['removeitem'];?></a></td>
                </tr>
				<?php
					/********* 组合促销 End *********/
					}else{
						$attr=array();
						$v['Property']!='' && $attr=str::json_data(str::attr_decode($v['Property']), 'decode');
						$attr_len=count($attr);
						$oversea_len=db::get_row_count('products_selected_attribute', "ProId='{$v['ProId']}' and AttrId=0 and VId=0 and OvId>1 and IsUsed=1");
						(int)$c['config']['global']['Overseas']==0 && $attr_len==1 && $attr['Overseas'] && $attr_len-=1;
						$price=$v['Price']+$v['PropertyPrice'];
						$v['Discount']<100 && $price*=$v['Discount']/100;
						$img=ly200::get_size_img($v['PicPath'], '240x240');
						$s_total_price+=$price*$v['Qty'];
						$total_price+=cart::iconv_price($price, 2, '', 0)*$v['Qty'];
						$quantity+=$v['Qty'];
						$url=ly200::get_url($v, 'products');
				?>
				<tr cid="<?=$v['CId'];?>">
					<td><input type="checkbox" name="select" value="<?=$v['CId'];?>" class="va_m<?=$error_ary["{$v['ProId']}_{$v['CId']}"]?' null':'';?>"<?=!$error_ary["{$v['ProId']}_{$v['CId']}"]?' checked':' disabled';?> /></td>
                	<td class="prList">
                    	<dl>
                        	<dt><a href="<?=$url;?>"><img src="<?=$img;?>" alt="<?=$v['Name'.$c['lang']];?>" name="<?=$v['Name'.$c['lang']];?>" /></a></dt>
                            <dd>
                            	<h4><a href="<?=$url;?>"><?=$v['Name'.$c['lang']];?></a></h4>
								<p><?=$v['Prefix'].$v['Number'];?></p>
								<?php if($attr_len){?>
									<div<?=(($attr_len>1 && $v['BuyType']!=3) || ($attr_len==1 && $oversea_len>0))?' class="prAttr"':'';?>>
										<?php
										foreach((array)$attr as $k=>$z){
											if($k=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
											echo '<p class="attr_'.$k.'">'.($k=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k).': '.$z.'</p>';
										}
										if((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1){
											echo '<p class="attr_Overseas">'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</p>';
										}?>
										<span class="prAttr_mod"></span>
										<div class="attr_edit"><div class="attr_edit_content clean"></div><span class="arrow"></span><em class="arrow arrow_bg"></em></div>
									</div>
								<?php }elseif((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1){?>
									<p class="attr_Overseas"><?=$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']];?></p>
								<?php }?>
                                <p class="remark"><?=$c['lang_pack']['cart']['remark'];?>: <input type="text" name="Remark[]" value="<?=htmlspecialchars($v['Remark']);?>" maxlength="200" data="<?=$v['Remark'];?>" /><?php /*?> <span><img src="/static/themes/default/images/cart/edit.png" /></span><?php */?></p>
                            </dd>
                        </dl>
                    </td>	
                	<td class="prPrice"><p price="<?=$v['Price'];?>" discount="<?=$v['Discount'];?>"><?=cart::iconv_price($price);?></p></td>
                	<td class="prQuant" start="<?=$v['StartFrom'];?>">
                    	<img src="/static/themes/default/images/cart/reduce.png" name="reduce" />
                        <input type="text" name="Qty[]" value="<?=$v['Qty'];?>" maxlength="4" />
                        <img src="/static/themes/default/images/cart/add.png" name="add" />
                        <input type="hidden" name="S_Qty[]" value="<?=$v['Qty'];?>" />
                        <input type="hidden" name="CId[]" value="<?=$v['CId'];?>" />
                        <input type="hidden" name="ProId[]" value="<?=$v['ProId'];?>" />
                    </td>
					<td class="prAmount"><p price="<?=cart::iconv_price($price, 2, '', 0)*$v['Qty'];?>"><?=cart::iconv_price(0, 1).cart::currency_format(cart::iconv_price($price, 2, '', 0)*$v['Qty'], 0, $_SESSION['Currency']['Currency']);?></p><a href="/cart/remove_c<?=sprintf('%04d', $v['CId']);?>.html" rel="del" title="<?=$c['lang_pack']['cart']['removeitem'];?>"><?=$c['lang_pack']['cart']['removeitem'];?></a></td>
                </tr>
                <?php
					}
				}?>
            </tbody>
            <tfoot>
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
				(($AfterPrice_0 && !$AfterPrice_1) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_0>$AfterPrice_1)) && $cutprice=$AfterPrice_0;//会员优惠价
				(($AfterPrice_1 && !$AfterPrice_0) || ($AfterPrice_0 && $AfterPrice_1 && $AfterPrice_1>$AfterPrice_0)) && $cutprice=$AfterPrice_1;//全场满减价
				?>
				<tr class="cutprice_box" style="display:<?=$cutprice?'table-row':'none';?>;"<?=$AfterPrice_0?' userPrice="'.$AfterPrice_0.'" userRatio="'.$user_discount.'"':' userPrice="0" userRatio="100"';?>>
					<td colspan="4" align="right">(-) <?=$c['lang_pack']['mobile']['total_savings']?> : </td>
					<td class="cutprice_p" price="<?=$cutprice;?>"><?=cart::iconv_price(0, 1).cart::currency_format($cutprice, 0, $_SESSION['Currency']['Currency']);?></td>
				</tr>
				<tr class="total_box" style="display:<?=$cutprice?'table-row':'none';?>;">
					<td colspan="4" align="right"><?=$c['lang_pack']['cart']['totalamount']?> : </td>
					<td class="total_p"><?=cart::iconv_price(0, 1).cart::currency_format($total_price, 0, $_SESSION['Currency']['Currency']);?></td>
				</tr>
                <tr class="shopping_cart_total">
                	<td colspan="4" align="right"><label><?=$c['lang_pack']['mobile']['cart_total'];?></label> (<span><?=str_replace('%num%', $quantity, $c['lang_pack']['cart'][($quantity>1?'itemsCount':'itemCount')]);?></span>) : </td>
                    <td><strong><?=cart::iconv_price(0, 1).cart::currency_format($total_price-$cutprice, 0, $_SESSION['Currency']['Currency']);?></strong></td>
                </tr>
            </tfoot>
        </table>
        <input type="hidden" name="CartProductPrice" value="<?=$total_price;?>" />
		<input type="hidden" name="DiscountPrice" value="<?=$cutprice;?>" data-type="<?=$cutArr['Type'];?>" data-value="<?=($cutArr && $cutArr['IsUsed']==1 && $c['time']>=$cutArr['StartTime'] && $c['time']<=$cutArr['EndTime'])?htmlspecialchars(str::json_data($cutArr['Data'])):'[]';?>" />
    </div>
    </form>
    <div class="cartFooter">
        <a name="continue_shopping" class="textbtn fl" title="<?=$c['lang_pack']['cart']['continue'];?>"><?=$c['lang_pack']['cart']['continue'];?></a>
        <a name="remove" class="textbtn fl" title="<?=$c['lang_pack']['cart']['remove'];?>"><?=$c['lang_pack']['cart']['remove'];?></a>
        <a class="checkoutBtn fr"><?=$c['lang_pack']['cart']['checkout'];?></a>
        <?php 
		if($a && (int)db::get_row_count('payment', "Method='Excheckout' and IsUsed=1")){
		?>
            <span class="fr"><?=$c['lang_pack']['cart']['or'];?></span>
            <button class="paypal_checkout_button fr"></button>
			<script src="//www.paypalobjects.com/api/checkout.js" async></script>
        <?php }?>
        <div class="clear"></div>
    </div>
    <div class="clear"></div>
<?php
}else{
	$products_category=db::get_limit('products_category', 'UId="0,"', "Category{$c['lang']}, CateId", $c['my_order'].'CateId asc', 0, 5);
	$cate_str='';
	foreach((array)$products_category as $k=>$v){
		$cate_str.=($k?', ':'').'<a href="'.ly200::get_url($v).'">'.$v["Category{$c['lang']}"].'</a>';
	}
?>
    <div class="cartBox">
    	<h2><?=$c['lang_pack']['cart']['cart'];?></h2>
        <div class="contents empty">
        	<h3><?=$c['lang_pack']['cart']['empty'];?></h3>
            <div class="cartDraft"><?=$c['lang_pack']['cart']['notes']?></div>
            <ul>
                <li><span class="roundRedDot">•</span> <?=str_replace('%cate%', $cate_str, $c['lang_pack']['cart']['tips_1']);?></li>
                <?php if ($c['lang_pack']['cart']['tips_2']){?><li><span class="roundRedDot">•</span> <?=$c['lang_pack']['cart']['tips_2'];?></li><?php }?>
            </ul>
            <p><a name="continue_shopping" class="continueShoppingBtn" title="<?=$c['lang_pack']['cart']['continue'];?>"><b><?=$c['lang_pack']['cart']['continue'];?></b></a></p>
        </div>
    </div>
<?php }?>
<?php
if($_COOKIE['history']){
	krsort($_COOKIE['history']);
?>
    <div class="cartBox">
    	<h2><?=$c['lang_pack']['cart']['history'];?></h2>
        <div class="contents products clearfix">
			<?php
			$i=0;
			foreach((array)$_COOKIE['history'] as $v){
				if(is_file($c['root_path'].$v['PicPath']) && $i<7){
				$url=ly200::get_url($v, 'products');
				$img=ly200::get_size_img($v['PicPath'], '500x500');
				$name=stripslashes($v['Name']);
			?>
			<dl class="pro_item fl<?=$i?'':' first';?>">
				<dt><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" /><span></span></a></dt>
				<dd class="pro_name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
			</dl>
			<?php
				}
				++$i;
			}?>
        </div>
    </div>
<?php }?>
</div>
