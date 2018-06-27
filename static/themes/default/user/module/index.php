<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

$ship_row=str::str_code(db::get_one('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', 'a.'.$c['where']['user']." and a.IsBillingAddress=0", 'a.*, c.Country, c.CountryData, s.States as StateName', 'a.AccTime desc, a.AId desc'));
$msg_row=str::str_code(db::get_limit('message', 1, 'MId, Title', 'MId desc', 0, 4));
$review_row=str::str_code(db::get_limit('products_review', $c['where']['user']." and ReId=0", '*', 'RId desc', 0, 3));
$newsletter_row=db::get_one('newsletter', "Email='{$user_row['Email']}'");

$country=$ship_row['Country'];
if($c['lang']!='_en'){
	$country_data=str::json_data(htmlspecialchars_decode($ship_row['CountryData']), 'decode');
	$country=$country_data[substr($c['lang'], 1)];
}

$level_row = db::get_one('user_level',"IsUsed=1 and LId = '{$user_row['Level']}'");
$next_level_row = db::get_one('user_level',"IsUsed=1 and FullPrice > '{$level_row['FullPrice']}'",'*','FullPrice asc');
$next_level_row || $next_level_row = $level_row;
$_UserName=substr($c['lang'], 1)=='jp'?$user_row['LastName'].' '.$user_row['FirstName']:$user_row['FirstName'].' '.$user_row['LastName'];

$vid_data_ary=array();
$attribute_row=str::str_code(db::get_all('products_attribute', '1', "AttrId, Type, Name{$c['lang']}, ParentId, CartAttr, ColorAttr"));
foreach($attribute_row as $v){
	$attribute_ary[$v['AttrId']]=array(0=>$v['Type'], 1=>$v["Name{$c['lang']}"]);
}
$value_row=str::str_code(db::get_all('products_attribute_value', '1', '*', $c['my_order'].'VId asc')); //属性选项
foreach($value_row as $v){
	$vid_data_ary[$v['AttrId']][$v['VId']]=$v["Value{$c['lang']}"];
}

$long = $user_row['Consumption']/$next_level_row['FullPrice']*100;
($user_row['IsLocked'] || $long>100) && $long = 100;
$Poor = $next_level_row['FullPrice']-$user_row['Consumption'];
($user_row['IsLocked'] || $Poor<0) && $Poor=0;
?>
<script type="text/javascript">
$(document).ready(function(){user_obj.user_index_init()});
</script>
<div class="user_index">
	<div id="user_heading" class="fl">
		<div class="ind_head">	
			<div class="welcome"><?=$c['lang_pack']['user']['welcome'].', ';?><?= $_UserName ? $_UserName : $user_row['Email'];  ?></div>
			<div class="level">
				<?php if($level_row){ ?>
					<span class="num"> <img src="<?=$level_row['PicPath']; ?>" alt=""> <?=$level_row['Name'.$c['lang']]; ?></span>
				<?php } ?>
				<div class="line">
					<span class="long" style="width:<?=$long; ?>%;"></span>
				</div>
				<span class="condition"><?=str_replace('%price%', cart::iconv_price($Poor, 0), $c['lang_pack']['user']['conditions']); ?></span>
			</div>
		</div>
	</div>
	<div id="user_right_menu" class="fr">
		<a href="/account/orders/?OrderStatus=1" class="m0">
			<?=$c['lang_pack']['user']['OrderStatusAry'][1];?>
			<?php if($num=db::get_row_count('orders', "UserId='{$_SESSION['User']['UserId']}' and OrderStatus=1")){
				echo '<span>'.$num.'</span>';
				} ?>
		</a>
		<a href="/account/orders/?OrderStatus=5" class="m1">
			<?=$c['lang_pack']['user']['OrderStatusAry'][5];?>
			<?php if($num=db::get_row_count('orders', "UserId='{$_SESSION['User']['UserId']}' and OrderStatus=5")){
				echo '<span>'.$num.'</span>';
				} ?>
		</a>
		<a href="/account/orders/?Review=1" class="m2">
			<?=$c['lang_pack']['user']['awaiting_review']; ?>
			<?php 
				$revies_count =  db::get_row_count('orders o left join user_message m on o.UserId=m.UserId and o.OId=m.Subject',  "o.{$c['where']['user']} and OrderId not in(select OrderId from products_review where UserId='{$_SESSION['User']['UserId']}' and {$c['time']}<(AccTime+{$c['orders']['review']})) and OrderStatus=6 and {$c['time']}<(o.PayTime+{$c['orders']['review']})");
				echo $revies_count?'<span>'.$revies_count.'</span>':'';
			?>
		</a>
		<a href="/account/coupon/" class="m3">
			<?=$c['lang_pack']['user']['coupons']; ?>
			<?php
				// 15天之内会过期的优惠券
				$out_time_where = "CouponWay=0 and {$c['time']} < EndTime and {$c['time']} > StartTime and (UseNum=0 or (UseNum > 0 and BeUseTimes < UseNum)) and ({$c['time']}+15*24*60*60 >= EndTime) and (".$c['where']['user']." or UserId = -1";
				$Level = (int)$user_row['Level'];
				if($Level) $out_time_where.=" or (LevelId = -1 or LevelId like '|{$Level}|')";
				$out_time_where.=")"; 
				if($out_time_num = db::get_row_count('sales_coupon',$out_time_where)){ ?>
				<span><?=$out_time_num; ?></span>
			<?php } ?>
		</a>
		<span class="br"></span>
	</div>
	<div class="clear"></div>
	<div class="row"></div>
	<?php 
		$orders_row = db::get_one('orders',"UserId='{$_SESSION['User']['UserId']}' and OrderStatus in (1,3)",'*','OrderId desc');
		$isFee=($orders_row['OrderStatus']>=4 && $orders_row['OrderStatus']!=7)?1:0;
		$total_price=orders::orders_price($orders_row, $isFee);
		if($orders_row){
		$orders_products_list = db::get_all('orders_products_list',"OrderId='{$orders_row['OrderId']}'");
		?>
		<div class="top_title">
			<?=$c['lang_pack']['user']['OrderStatusAry'][1];?>
		</div>
		<table class="order_table">
			<tbody>
				<tr>
					<th><?=$c['lang_pack']['mobile']['order_info'];?></th>
					<th width="20%"><?=$c['lang_pack']['user']['grandTotal'];?></th>
					<th width="16%"><?=$c['lang_pack']['user']['action'];?></th>
				</tr>
				<tr class="ind_opl">
					<td>
						<?php
						$hide_num=$hide_item_num=0;
						foreach((array)$orders_products_list as $k => $v){ 
							$attr=str::json_data(str::attr_decode($v['Property']), 'decode');
							!$attr && $attr=str::json_data($v['Property'], 'decode');
							$products_row = array();
							if($v['BuyType']==4){
								$package_row=str::str_code(db::get_one('sales_package', "PId='{$v['KeyId']}'"));
								if(!$package_row) continue;
								$products_row=str::str_code(db::get_all('products', "SoldOut=0 and ProId='{$package_row['ProId']}'"));
								$pro_where=str_replace('|', ',', substr($package_row['PackageProId'], 1, -1));
								$pro_where=='' && $pro_where=0;
								$products_row=array_merge($products_row, str::str_code(db::get_all('products', "SoldOut=0 and ProId in($pro_where)")));
								$data_ary=str::json_data(htmlspecialchars_decode($package_row['Data']), 'decode');
							}else{
								$products_row[]=$v;
							}
							?>
							<div class="list <?=$hide_num > 2 || $hide_item_num > 2 ? 'hide' : ''; ?>">
								<?php foreach((array)$products_row as $k1=>$v1){ 
									$name = $v1['Name']; 
									$img = @is_file($c['root_path'].$v1['PicPath'])?$v1['PicPath']:ly200::get_size_img(db::get_value('products',"ProId='{$v1['ProId']}'",'PicPath_0'), '240x240');
									$url = ly200::get_url($v1,'products');
									$sku = $v1['SKU'];
									if($v['BuyType']==4){
										$name=$v1['Name'.$c['lang']];
										$img=ly200::get_size_img($v1['PicPath_0'], '240x240');
										$sku=$v1['Prefix'].$v1['Number'];
									}
									?>
									<div class="<?=$v['BuyType']==4 && $hide_item_num>2 ? 'hide' : ''; ?>">
										<?php if($k1){ ?><div class="br"></div><?php } ?>
										<a href="<?=$url; ?>" class="pic">
											<img src="<?=$img; ?>" alt="<?=$name; ?>" />
											<span></span>
										</a>
										<div class="desc">
											<p class="name"><?=$name; ?></p>
											<?php if($sku){ ?>
												<p class="sku"><?=$sku; ?></p>
											<?php } ?>
											<ul>
												<?php
												if($k1==0){
													foreach((array)$attr as $k2=>$v2){
														if($k2=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
														echo '<li>'.($k2=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k2).': '.$v2.'</li>';
													}
													if((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1){
														echo '<li>'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</li>';
													}
												}elseif($data_ary[$v1['ProId']]){ ?>
													<?php
													$OvId=0;
													foreach((array)$data_ary[$v1['ProId']] as $k2=>$v2){
														if($k2=='Overseas'){ //发货地
															$OvId=str_replace('Ov:', '', $v2);
															if((int)$c['config']['global']['Overseas']==0 || $OvId==1) continue; //发货地是中国，不显示
															echo '<li>'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</li>';
														}else{
															echo '<li>'.$attribute_ary[$k2][1].': '.$vid_data_ary[$k2][$v2].'</li>';
														}
													}
													if((int)$c['config']['global']['Overseas']==1 && $OvId==1){
														echo '<li>'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</li>';
													}?>
												<?php }?>
											</ul>
										</div>
										<?php if($k1==0){ ?>
											<span class="p_price"><?=cart::iconv_price(0, 1, $_SESSION['Currency']['Currency']).cart::currency_format($v['Price'], 0, $_SESSION['Currency']['Currency']);?></span>
											<span class="p_qty">x<?=$v['Qty']; ?></span>
										<?php } ?>
										<div class="clear"></div>
									</div>
								<?php 
								$hide_item_num++;
								} ?>
							</div>
						<?php 
						$hide_num++;
						} ?>
					</td>
					<td class="o_price"><?=cart::iconv_price(0, 1, $orders_row['Currency']).cart::currency_format($total_price, 0, $orders_row['Currency']);?></td>
					<td class="options">
						<a href="/cart/complete/<?=$orders_row['OId'];?>.html" oid="<?=$orders_row['OId'];?>" pid="<?=$orders_row['PId'];?>" class="pay_now edit_pay_btn"><?=$c['lang_pack']['cart']['paynow'];?></a>
						<a href="/account/orders/view<?=$orders_row['OId'];?>.html" class="view"><?=$c['lang_pack']['user']['view_more'];?></a>
					</td>
				</tr>
			</tbody>
			<?php if($hide_num > 3 || $hide_item_num > 3){ ?>
				<tfoot>
					<tr>
						<td colspan="4"> <a href="javascript:;" class="see_more sys_bg_button"><?=$c['lang_pack']['more']; ?></a> </td>
					</tr>
				</tfoot>
			<?php } ?>
		</table>
	<?php } ?>
	<?php 
		$coupons_row = db::get_limit('sales_coupon',"CouponWay=1 and ({$c['time']} < EndTime and {$c['time']} > StartTime) and CId not in (select ParentId from sales_coupon where CouponWay=0 and UserId = '{$_SESSION['User']['UserId']}' and ParentId != 0)",'*','UseCondition asc,Discount asc',0,3);
		if($coupons_row){
		?>
		<div class="top_title">
			<?=$c['lang_pack']['user']['coupons']; ?>
		</div>
		<div class="user_get_coupons">
			<?php 
			$user_count = db::get_row_count('user');
			foreach((array)$coupons_row as $k => $v){ 
				$get_count = db::get_row_count('sales_coupon',"CouponWay=0 and ParentId='{$v['CId']}'");
				$only = (int)(($user_count-$get_count)/$user_count*100);
				?>
				<div class="item <?=$k==0 ? 'fir' : ''; ?>">
					<div class="cou">
						<p class="price">
							<?php if($v['CouponType']){ ?>
								<span><?=cart::iconv_price(0, 1); ?></span><?=cart::iconv_price($v['Money'], 2, '', 0); ?>
							<?php }else{ ?>
								<?=$v['Discount']; ?><span>% off</span>
							<?php } ?>
						</p>
						<p class="over"><?=$v['UseCondition']>0 ? str_replace('%price%', cart::iconv_price($v['UseCondition'], 0), $c['lang_pack']['user']['order_over']) : ''; ?></p>
						<p class="only"><?=$c['lang_pack']['only']; ?> <?=$only; ?>% <span><em style="width:<?=$only; ?>%;"></em></span></p>
					</div>
					<p class="date"><?=date('d/m/Y',$v['StartTime']); ?> - <?=date('d/m/Y',$v['EndTime']); ?></p>
					<a href="javascript:;" data-cid="<?=$v['CId']; ?>" class="get_it"><?=$c['lang_pack']['user']['get_it']; ?></a>
				</div>
			<?php } ?>
			<div class="clear"></div>
		</div>
		<div class="row"></div>
	<?php } ?>
	<div class="user_ind_ptype">
		<a href="javascript:;" class="cur"><?=$c['lang_pack']['cart']['sLikeProd']; ?></a>
		<span></span>
		<a href="javascript:;"><?=$c['lang_pack']['recommended']; ?></a>
	</div>
	<div class="user_page_pro">
		<?php 
			$w_ary=array('1','IsHot=1');
			foreach((array)$w_ary as $key=>$val){
				?>
				<div class="pro_list clearfix" <?=$key==0 ? 'style="display:block;"' : ''; ?>>
					<?php
					$products_list_row=str::str_code(db::get_limit('products', $val.$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 8));
					$key == 0 && $products_list_row=orders::you_may_also_like(1,8);
					foreach((array)$products_list_row as $k=>$v){
						$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
						$url=ly200::get_url($v, 'products');
						$img=ly200::get_size_img($v['PicPath_0'], '240x240');
						$name=$v['Name'.$c['lang']];
						$price_ary=cart::range_price_ext($v);
						$price_0=$v["Price_{$is_promition}"];
						?>
						<dl class="pro_item fl<?=$k%4==0?' first':'';?>">
							<dt><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" /><span></span></a></dt>
							<dd class="name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
							<dd class="price">
								<em class="currency_data PriceColor"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0], 2);?></span>
								<?php if($c['config']['products_show']['Config']['price']){?><del><em class="currency_data"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data" data="<?=$price_0;?>"><?=cart::iconv_price($price_0, 2);?></span></del><?php }?>
							</dd>
						</dl>
					<?php }?>
				</div>
		<?php }?>
	</div>
</div>