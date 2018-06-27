<?php !isset($c) && exit();?>
<?php
if($a=='shopping_cart'){
	//购物车弹窗
	$cart_row=db::get_all('shopping_cart c left join products p on c.ProId=p.ProId', 'c.'.$c['where']['cart'], "c.*, p.Name{$c['lang']}", 'c.CId desc');
	$cart_len=count($cart_row);
?>
	<div class="cart_empty<?=$cart_len?' hide':'';?>"><?=$c['lang_pack']['empty'];?></div>
	<?php if($cart_len){?>
		<div class="cart_list">
			<ul<?=$cart_len>4?' class="more_pro"':'';?>>
				<?php
				$total_price=0;
				foreach((array)$cart_row as $v){
					if($v['BuyType']==4){
						/********* 组合促销 Start *********/
						$total_price+=$v['Price'];
						$package_row=str::str_code(db::get_one('sales_package', "PId='{$v['KeyId']}'"));
						if(!$package_row) continue;
						$attr=array();
						$v['Property']!='' && $attr=str::json_data(str::attr_decode($v['Property']), 'decode');
						$products_row=str::str_code(db::get_all('products', "SoldOut=0 and ProId='{$package_row['ProId']}'"));
						$pro_where=str_replace('|', ',', substr($package_row['PackageProId'], 1, -1));
						$pro_where=='' && $pro_where=0;
						$products_row=array_merge($products_row, str::str_code(db::get_all('products', "SoldOut=0 and ProId in($pro_where)")));
						$data_ary=str::json_data(htmlspecialchars_decode($package_row['PackageData']), 'decode');
						echo '<h4 class="sales_title">'.$package_row['Name'].' <span class="sales_price fr FontColor">'.$_SESSION['Currency']['Currency'].' '.cart::iconv_price($v['Price']).'</span></h4>';
						foreach((array)$products_row as $k2=>$v2){
							$img=ly200::get_size_img($v2['PicPath_0'], '240x240');
							$url=ly200::get_url($v2, 'products');
							$quantity+=$v['Qty'];
				?>
						
						<li class="cart_box sales_box<?=($k2+1)==count($products_row)?' sales_last':'';?>">
							<div class="cart_pro_img">
								<a href="<?=$url;?>"><img src="<?=$img;?>" /></a>
								<span><?=$v['Qty'];?></span>
							</div>
							<span class="cart_pro_name"><a href="<?=$url;?>"><?=$v2['Name'.$c['lang']];?></a></span>
							<span class="cart_pro_property">
								<?php foreach((array)$attr as $k=>$z){
									if($k=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
									?>
									<span class="attr_<?=$k;?>"><?=$k.': '.$z;?></span> <br />
								<?php }?>
							</span>
						</li>
				<?php
						}
						/********* 组合促销 End *********/
					}else{
						$attr=array();
						$v['Property']!='' && $attr=str::json_data(str::attr_decode($v['Property']), 'decode');
						$type = 'products';
						$v['BuyType']==1 && $type='tuan';
						$url=ly200::get_url($v, $type);
						$price=$v['Price']+$v['PropertyPrice'];
						$v['Discount']<100 && $price*=$v['Discount']/100;
						$total_price+=cart::iconv_price($price, 2, '', 0)*$v['Qty'];
						$img=ly200::get_size_img($v['PicPath'], '240x240');
				?>
						<li class="cart_box">
							<div class="cart_pro_img">
								<a href="<?=$url;?>"><img src="<?=$img;?>" /></a>
								<span><?=$v['Qty'];?></span>
							</div>
							<span class="cart_pro_name"><a href="<?=$url;?>"><?=$v['Name'.$c['lang']];?></a></span>
							<span class="cart_pro_property">
								<?php 
									if((int)$c['config']['global']['Overseas']==1 && $v['OvId']==1) $Overseas = 1;
									foreach((array)$attr as $k=>$z){
									if($k=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v['OvId']==1)) continue; //发货地是中国，不显示
									?>
									<span class="attr_<?=$k;?>"><?=$k.': '.$z;?></span> <br />
								<?php } ?>
								<?php 
									if($Overseas){
										echo '<span class="attr_Overseas ">'.$c['lang_pack']['products']['shipsFrom'].': '.$c['config']['Overseas'][$v['OvId']]['Name'.$c['lang']].'</span>';
									}
								?>
							</span>
							<span class="cart_pro_price FontColor"><?=$_SESSION['Currency']['Currency'].' '.cart::iconv_price($price);?></span>
							<div class="clear"></div>
						</li>
				<?php
					}
				}?>
			</ul>
			<div class="cart_pro_view">
				<span class="cart_total FontColor"><?=$_SESSION['Currency']['Currency'].' '.cart::iconv_price(0, 1).cart::currency_format($total_price, 0, $_SESSION['Currency']['Currency']);?></span>
				<span class=""><span class="cart_num FontColor"><?=$cart_len;?></span> <?=$c['lang_pack']['items'];?></span>
				<div class="clear"></div>
			</div>
			<div class="cart_pro_btn"><a href="/cart/"><span class="cart_view BuyNowBgColor"><?=$c['lang_pack']['view_cart'];?></span></a></div>
		</div>
	<?php
	}
	unset($c, $cart_row);
	?>
<?php
}elseif($a=='seckill_default'){
	//秒杀专题页（default风格）
	$d_ary=array('dealing', 'upcoming', 'past');
	$typ=$_POST['typ'];
	!in_array($typ, $d_ary) && $typ=$d_ary[0];
	$sort_ary=array(
		'1a'	=>	's.Price asc,',
		'1d'	=>	's.Price desc,',
		'2d'	=>	'p.TotalRating desc,',
		'3d'	=>	'p.IsHot desc,'
	);
	
	$CateId=(int)$_POST['CateId'];
	$page=(int)$_POST['page'];
	$Sort=$_POST['Sort'];
	$page_count=12;
	$page<1 && $page=1;
	$where='1';
	if($typ=='upcoming'){//还没开始
		$where.=" and s.StartTime>{$c['time']}";
	}elseif($typ=='past'){//过去
		$where.=" and {$c['time']}>s.EndTime";
	}else{//现在
		$where.=" and s.StartTime<={$c['time']} and s.EndTime>{$c['time']}";
	}
	$CateId && $where.=' and p.'.category::get_search_where_by_CateId($CateId, 'products_category');
	
	$row=str::str_code(db::get_limit_page('sales_seckill s left join products p on s.ProId=p.ProId', $where.' and s.RemainderQty>0', "s.*, p.Name{$c['lang']}, p.PicPath_0, p.Price_0, p.Price_1, p.TotalRating, p.IsHot, p.Unit", $sort_ary[$Sort].'if(s.MyOrder>0, if(s.MyOrder=999, 1000001, s.MyOrder), 1000000) asc, s.SId desc', $page, $page_count));
	
	if(count($row)){
		$total_pages=$row[3];
?>
		<script type="text/javascript">
		$(document).ready(function(e){
			for(i in seckill_timer){
				clearInterval(seckill_timer[i]);//清除计时器，防止时间乱跳
			}
			$("ul[page="+<?=$page;?>+"] .time").each(function(){
				var obj=$(this).find("span"),
					time=new Date(),
					proid=obj.attr("proId");
				obj.genTimer({
					beginTime: ueeshop_config.date,
					targetTime: obj.attr("endTime"),
					callback: function(e){
						$('#flashsale_'+proid).html(e);
					}
				});
			});
		});
		</script>
		<ul page="<?=$page;?>" total="<?=$total_pages;?>">
			<?php 
			foreach((array)$row[0] as $k=>$v){
				$url=ly200::get_url($v, 'seckill');
				$img=ly200::get_size_img($v['PicPath_0'], '500x500');
				$discount=sprintf('%01.0f', ($v['Price_1']-$v['Price'])/$v['Price_1']*100);
				$discount=$discount<1?1:$discount;
				$progress=ceil((1-$v['RemainderQty']/$v['Qty'])*100);
				if($v['Unit']){//产品自身设置单位
					$Unit=$v['Unit'];
				}elseif($c['config']['products_show']['Config']['item'] && $c['config']['products_show']['item']){//产品统一设置单位
					$Unit=$c['config']['products_show']['item'];
				}else{
					$Unit=$c['lang_pack']['piece'];
				}
			?>
				<li class="item<?=($k+1)%4==1?' first':'';?>">
					<div class="prod_box_pic">
						<a href="<?=$url;?>" title="<?=$v['Name'.$c['lang']];?>" class="pic_box">
							<img src="<?=$img;?>" alt="<?=$v['Name'.$c['lang']];?>" /><span></span>
						</a>
						<div class="percent"><?=$discount;?></div>
						<?php
						if($typ=='dealing'){
							$m=(int)@date('m', $v['EndTime'])-1;
							$d=date("Y, $m, j, G, i, s", $v['EndTime']);
						?>
							<div class="time"><?=str_replace('%time%', '<span id="flashsale_'.$v['ProId'].'" endTime="'.date('Y/m/d H:i:s', $v['EndTime']).'" proId="'.$v['ProId'].'"></span>', $c['lang_pack']['dealsEnd']);?></div>
						<?php }?>
					</div>
					<div class="name"><a href="<?=$url;?>" title="<?=$v['Name'.$c['lang']];?>"><?=$v['Name'.$c['lang']];?></a></div>
					<div class="discount"><div class="progress"><div class="progress_current" style="width:<?=$progress;?>%;"></div></div><div class="progress_count"><?=$c['lang_pack']['only'];?> <?=100-$progress;?>%</div></div>
					<div class="original"><span><?=$c['lang_pack']['originalPrice'];?></span><del><?=cart::iconv_price($v['Price_1']);?></del></div>
					<div class="price">
						<div class="price_1 fl"><strong><?=cart::iconv_price($v['Price']);?></strong><span> / <?=$Unit;?></span></div>
						<div class="save fr"><strong><?=$c['lang_pack']['save'];?></strong><span><?=cart::iconv_price($v['Price_1']-$v['Price']);?></span></div>
					</div>
					<div class="clear"></div>
				</li>
			<?php }?>
		</ul>
		<div class="clear"></div>
		<?php if($row[3]>1){?>
			<div class="blank30"></div>
			<div id="turn_page"><?=ly200::turn_page_html($row[1], $row[2], $row[3], 'Page_', $c['lang_pack']['previous'], $c['lang_pack']['next'], 3, '', 0);?></div>
		<?php }?>
<?php
	}
}elseif($a=='tuan_default'){
	//团购专题页（default风格）
	$d_ary=array('this', 'previous');
	$typ=$_POST['typ'];
	!in_array($typ, $d_ary) && $typ=$d_ary[0];
	$sort_ary=array(
		'1a'	=>	's.Price asc,',
		'1d'	=>	's.Price desc,',
		'2d'	=>	'p.TotalRating desc,',
		'3d'	=>	'p.IsHot desc,'
	);
	
	$CateId=(int)$_POST['CateId'];
	$page=(int)$_POST['page'];
	$Sort=$_POST['Sort'];
	$page_count=12;
	$page<1 && $page=1;
	$where='1';
	if($typ=='previous'){//过去
		$where.=" and {$c['time']}>s.EndTime";
	}else{//现在
		$where.=" and s.StartTime<={$c['time']} and s.EndTime>{$c['time']}";
	}
	$CateId && $where.=' and p.'.category::get_search_where_by_CateId($CateId, 'products_category');
	
	$row=str::str_code(db::get_limit_page('sales_tuan s left join products p on s.ProId=p.ProId', $where.' and s.BuyerCount<s.TotalCount', "s.*, p.Name{$c['lang']}, p.PicPath_0, p.Price_0, p.Price_1, p.TotalRating, p.IsHot, p.IsDefaultReview, p.DefaultReviewRating, p.DefaultReviewTotalRating, p.Rating, p.TotalRating", $sort_ary[$Sort].'if(s.MyOrder>0, if(s.MyOrder=999, 1000001, s.MyOrder), 1000000) asc, s.TId desc', $page, $page_count));
	
	if(count($row)){
		$total_pages=$row[3];
?>
		<ul page="<?=$page;?>" total="<?=$total_pages;?>">
			<?php 
			foreach((array)$row[0] as $k=>$v){
				$url=ly200::get_url($v, 'tuan');
				$img=ly200::get_size_img($v['PicPath_0'], '500x500');
				$price_ary=cart::range_price_ext($v);
				$old_price=$v['Price_1'];
				$discount=sprintf('%d', (($old_price-$v['Price'])/((float)$old_price?$old_price:1)*100));
				$discount=$discount<1?1:$discount;
				$rating=($v['IsDefaultReview'] && $v['DefaultReviewRating'])?(int)$v['DefaultReviewRating']:ceil($v['Rating']);
				$total_rating=($v['IsDefaultReview'] && $v['DefaultReviewTotalRating'])?$v['DefaultReviewTotalRating']:$v['TotalRating'];
			?>
				<li class="item<?=($k+1)%4==1?' first':'';?>">
					<div class="prod_box_pic">
						<a href="<?=$url;?>" title="<?=$v['Name'.$c['lang']];?>" class="pic_box">
							<img src="<?=$img;?>" alt="<?=$v['Name'.$c['lang']];?>" /><span></span>
						</a>
						<div class="percent"><?=$discount;?></div>
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
						<div class="review fl"><span class="star star_s<?=$rating;?>"></span><strong>(<?=$total_rating;?>)</strong></div>
						<div class="price_1 fr"><?=cart::iconv_price($v['Price']);?></div>
					</div>
				</li>
			<?php }?>
		</ul>
		<div class="clear"></div>
		<?php if($row[3]>1){?>
			<div class="blank30"></div>
			<div id="turn_page"><?=ly200::turn_page_html($row[1], $row[2], $row[3], 'Page_', $c['lang_pack']['previous'], $c['lang_pack']['next'], 3, '', 0);?></div>
		<?php }?>
<?php
	}
}elseif($a=='cart_modify_attribute'){
	//购物车列表页编辑产品属性
	$CId=(int)$_POST['CId'];
	$ProId=(int)$_POST['ProId'];
	$products_row=str::str_code(db::get_one('products', "ProId='{$ProId}'")); //产品资料
	$ParentId=$products_row['AttrId'];
	$IsCombination=(int)$products_row['IsCombination'];
	$cart_row=db::get_one('shopping_cart', "CId='{$CId}'"); //购物车资料
	$attr_ary=@str::json_data(str::attr_decode($cart_row['Attr']), 'decode');
	$selected_ary=$combinatin_ary=$all_value_ary=$attrid=array();
	$selected_row=str::str_code(db::get_all('products_selected_attribute', "ProId='{$ProId}' and IsUsed=1", 'SeleteId, AttrId, VId, OvId, Value', 'SeleteId asc')); //已选属性
	foreach($selected_row as $v){ //记录勾选属性ID
		$selected_ary['Id'][$v['AttrId']][]=$v['VId'];
		$v['AttrId']==0 && $v['VId']==0 && $v['OvId']>0 && $selected_ary['Overseas'][]=$v['OvId']; //记录勾选属性ID 发货地
	}
	$products_attr=str::str_code(db::get_all('products_attribute', "ParentId='{$ParentId}' and CartAttr=1", "AttrId, Name{$c['lang']}", $c['my_order'].'AttrId asc'));
	foreach((array)$products_attr as $k=>$v){ $attrid[]=$v['AttrId'];}
	$attrid_list=@implode(',', $attrid);
	!$attrid_list && $attrid_list='-1';
	$value_row=str::str_code(db::get_all('products_attribute_value', "AttrId in ($attrid_list)", '*', $c['my_order'].'VId asc')); //属性选项
	foreach($value_row as $v){ $all_value_ary[$v['AttrId']][$v['VId']]=$v; }
	//属性组合数据 Start
	$combinatin_row=str::str_code(db::get_all('products_selected_attribute_combination', "ProId='{$ProId}'", '*', 'CId asc'));
	foreach($combinatin_row as $v){
		$combinatin_ary[$v['Combination']][$v['OvId']]=array($v['Price'], $v['Stock'], $v['Weight'], $v['SKU'], $v['IsIncrease']);
		$key=str_replace('|', '_', substr($v['Combination'], 1, -1));
		(int)$v['OvId'] && $key.=($key?'_':'').'Ov:'.$v['OvId'];
		$ext_ary[$key]=array($v['Price'], $v['Stock'], $v['Weight'], $v['SKU'], $v['IsIncrease']);
	}
	//属性组合数据 End
	$isHaveOversea=count($c['config']['Overseas']); //是否开启海外仓
?>
	<ul class="widget attributes" default_selected="<?=(int)$c['config']['products_show']['Config']['selected'];?>" data-combination="<?=$IsCombination;?>">
		<?php
		//规格属性
		foreach((array)$products_attr as $k=>$v){
			if(!$selected_ary['Id'][$v['AttrId']]) continue; //踢走
		?>
			<li class="clearfix">
				<div class="name"><?=$v['Name'.$c['lang']];?></div>
				<select id="attr_<?=$v['AttrId'];?>" attr="<?=$v['AttrId'];?>">
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
						<option value="<?=$v2['VId'];?>" data="<?=htmlspecialchars('{"Price":'.$price.',"Qty":'.$qty.',"Weight":'.$weight.',"SKU":'.$sku.',"IsIncrease":'.$increase.'}');?>"<?=((int)$c['config']['products_show']['Config']['stock'] && $IsCombination && $value && $qty<1)?' class="hide" disabled':'';?><?=$attr_ary[$v['AttrId']]==$v2['VId']?' selected':'';?>><?=$v2['Value'.$c['lang']].' '.($price>0?' (+'.cart::iconv_price($price).')':'');?></option>
					<?php }?>
				</select>
			</li>
		<?php
		}
		//发货地
		if($isHaveOversea && $selected_ary['Overseas']){
		?>
			<li class="clearfix" style="display:<?=((int)$c['config']['global']['Overseas']==1 && count($selected_ary['Overseas'])>1 && $IsCombination==1)?'block':'none';?>;">
				<div class="name"><?=$c['lang_pack']['products']['shipsFrom'];?></div>
				<select id="attr_Overseas" attr="Overseas">
					<option value=""><?=str_replace('%name%', $c['lang_pack']['products']['shipsFrom'], $c['lang_pack']['products']['select']);?></option>
					<?php
					foreach($c['config']['Overseas'] as $k=>$v){
						$Ovid='Ov:'.$v['OvId'];
						//if($v['OvId']>1 && !in_array($v['OvId'], $selected_ary['Overseas'])) continue; //踢走
						if(!$selected_ary['Overseas'] && $v['OvId']>1) continue; //踢走
						if($selected_ary['Overseas'] && !in_array($v['OvId'], $selected_ary['Overseas'])) continue; //踢走
						$value=$combinatin_ary['||'][$v['OvId']];
						$price=(float)$value[0];
						$qty=(int)$value[1];
						$weight=(float)$value[2];
						$sku=$value[3];
						$increase=(int)$value[4];
					?>
						<option value="<?=$Ovid;?>" data="<?=htmlspecialchars('{"Price":'.$price.',"Qty":'.$qty.',"Weight":'.$weight.',"SKU":'.$sku.',"IsIncrease":'.$increase.'}');?>"<?=((int)$c['config']['products_show']['Config']['stock'] && $IsCombination && $value && $qty<1)?' class="hide" disabled':'';?><?=$attr_ary['Overseas']==$Ovid?' selected':'';?>><?=$v['Name'.$c['lang']].' '.($price>0?' (+'.cart::iconv_price($price).')':'');?></option>
					<?php }?>
				</select>
			</li>
		<?php }?>
	</ul>
	<div class="remark"><div class="name"><?=$c['lang_pack']['cart']['remark'];?></div><textarea name="Remark" maxlength="200" data="<?=$_POST['Remark'];?>"><?=htmlspecialchars($_POST['Remark']);?></textarea></div>
	<div class="operate"><a href="javascript:;" class="cancel"><?=$c['lang_pack']['cart']['cancel'];?></a><a href="javascript:;" class="add btn_global sys_shadow_button"><span><?=$c['lang_pack']['cart']['submit'];?></span></a></div>
	<input type="hidden" id="CId" value="<?=$CId;?>" />
	<input type="hidden" id="ProId" value="<?=$ProId;?>" />
	<input type="hidden" id="attr_hide" value="<?=htmlspecialchars($cart_row['Attr']);?>" />
	<input type="hidden" id="ext_attr" value="<?=htmlspecialchars(str::json_data($ext_ary));?>" />
	<input type="hidden" id="attrStock" value="<?=(int)$c['config']['products_show']['Config']['stock'];?>" />
<?php
}elseif($a=='review_list'){
	//产品评论列表页
	$ProId=(int)$_POST['ProId'];
	$Rating=(int)$_POST['Rating'];
	$Action=$_POST['Action'];
	$review_cfg=str::json_data(db::get_value('config', "GroupId='products_show' and Variable='review'", 'Value'), 'decode');
	if($Action=='goods'){
		$g_page=0;
		$page_count=4;//显示数量
	}else{
		$g_page=(int)$_POST['page'];
		$page_count=10;//显示数量
	}
	$where="p.ProId='{$ProId}' and p.ReId=0";
	$Rating && $where.=" and p.Rating='{$Rating}'";
	$review_cfg['display']==1 && $where.=" and p.Audit=1";
	$review_row=str::str_code(db::get_limit_page('products_review p left join user u on p.UserId=u.UserId left join user_level l on u.Level=l.LId', $where, "p.*, u.FirstName, u.LastName, u.Level, l.Name{$c['lang']}, l.PicPath", 'p.RId desc', $g_page, $page_count));
	$total_rating=$review_row[1];
	$products_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
	
	if($review_row[0]){
		foreach((array)$review_row[0] as $k=>$v){
			$rating_ary=explode(',', $v['Assess']);
			$reply_row=str::str_code(db::get_all('products_review p left join user u on p.UserId=u.UserId', "p.ProId='{$ProId}' and p.ReId='{$v['RId']}'".($review_cfg['display']==1?' and p.Audit=1':''), 'p.*, u.FirstName, u.LastName', 'p.RId desc'));
			$reply_len=count($reply_row);
			$name=($v['FirstName'] || $v['LastName'])?$v['FirstName'].' '.$v['LastName']:$v['CustomerName'];
			(int)$review_cfg['anonymous']==1 && $name=str::cut_str($name, 3).'***';//匿名显示
?>
			<div class="widget review_item">
				<ul class="user fl">
					<li><span class="star star_s<?=$v['Rating'];?>"></span></li>
					<li><span class="by_text"><?=$c['lang_pack']['products']['by'];?></span><span><?=$name;?></span></li>
					<?php if($v['Level']){?><li><i class="icon_level"><img src="<?=$v['PicPath'];?>" /></i><strong class="level_text FontColor"><?=$v['Name'.$c['lang']];?></strong></li><?php }?>
					<li><span class="time"><?=date('M d, Y', $v['AccTime']);?></span></li>
				</ul>
				<div class="like fr">
					<div class="vote">
						<p><?=$c['lang_pack']['products']['helpful'];?></p>
						<div class="likeWrapper">
							<a href="/account/like_p<?=sprintf('%04d', $ProId);?>r<?=$v['RId'];?>l1.html" class="like"><span class="icon_agree">(<?=$v['Agree'];?>)</span></a>
							<span class="gap">|</span>
							<a href="/account/like_p<?=sprintf('%04d', $ProId);?>r<?=$v['RId'];?>l-1.html" class="unlike"><span class="icon_oppose">(<?=$v['Oppose'];?>)</span></a>
						</div>
					</div>
					<?php /*
					<a href="https://api.addthis.com/oexchange/0.8/forward/facebook/offer?url=<?=htmlspecialchars('http://'.$_SERVER['HTTP_HOST'].ly200::get_url($products_row, 'review'));?>&pubid=ra-52b80aeb367a2886&ct=1&title=<?=$products_row['Name'.$c['lang']];?>&pco=tbxnj-1.0" class="fb_share" target="_blank" class="fb_share" data-share="<?=htmlspecialchars('{"fbuser":"'.$v['FirstName'].' '.$v['LastName'].'","fblink":"'.'http://'.$_SERVER['HTTP_HOST'].ly200::get_url($products_row, 'products').'","fblink2":"'.'http://'.$_SERVER['HTTP_HOST'].ly200::get_url($products_row, 'review').'","fbpicture":"'.'http://'.$_SERVER['HTTP_HOST'].ly200::get_size_img($products_row['PicPath_0'], '240x240').'","fbname":"'.$products_row['Name'.$c['lang']].'"}');?>"><em class="icon_facebook_mini"></em><?=$c['lang_pack']['products']['shareFB'];?></a>
					*/?>
				</div>
				<div class="review_main">
					<div class="content"><?=str::format($v['Content']);?></div>
					<div class="pic_list">
						<?php
						for($i=0; $i<3; ++$i){
							if(!$v['PicPath_'.$i] || !is_file($c['root_path'].$v['PicPath_'.$i])) continue;
						?>
						<a href="<?=$v['PicPath_'.$i];?>" class="pic_box" target="_blank"><img src="<?=ly200::get_size_img($v['PicPath_'.$i], '85x85');?>" /><span></span></a>
						<?php }?>
						<div class="clear"></div>
					</div>
					<div class="reply">
						<div class="edit"><a href="javascript:;" class="reply_btn" reply="<?=$reply_len;?>"><?=$c['lang_pack']['products']['reply'].($reply_len?" <span>({$reply_len})</span>":'');?></a></div>
						<?php
						if($reply_len){
						?>
						<div class="w_review_replys hide">
							<span class="arrow"></span>
							<?php foreach((array)$reply_row as $v2){?>
							<div class="review_reply">
								<p><?=$v2['Content'];?></p>
								<p class="writer"><span class="light_gray"><?=$c['lang_pack']['products']['by'];?></span> <cite class="replier"><?=$v2['CustomerName'];?></cite> <cite class="light_gray"><?=date('M d,Y H:i:s', $v2['AccTime']);?></cite></p>
							</div>
							<?php }?>
						</div>
						<?php }?>
						<div class="write_reply hide">
							<form>
								<div class="textarea_holder"><textarea class="default" name="ReviewComment"><?=$c['lang_pack']['products']['addReply'];?></textarea></div>
								<p class="error"></p>
								<button class="btn textbtn"><?=$c['lang_pack']['products']['reply'];?></button>
								<input type="hidden" name="ProId" value="<?=$ProId;?>">
								<input type="hidden" name="RId" value="<?=$v['RId'];?>">
							</form>
						</div>
					</div>
				</div>
			</div>
<?php
		}
	}
	
	if($Action=='goods' && $review_row[0]){
		echo '<div class="prod_review_more"><a class="customer_btn clearfix" href="'.ly200::get_url($products_row, 'review').'">'.$c['lang_pack']['products']['seeAll'].'</a></div>';
	}else{
		echo '<div class="prod_review_more"><div id="turn_page">'.ly200::turn_page_html($review_row[1], $review_row[2], $review_row[3], '', $c['lang_pack']['previous'], $c['lang_pack']['next']).'</div></div>';
	}
	
	if(!$review_row[3]){
		echo '<div class="content_blank">'.$c['lang_pack']['mobile']['no_data'].'</div>';
	}elseif($page>=$review_row[3] && $page){
		echo '<div class="content_more">'.$c['lang_pack']['mobile']['no_data'].'</div>';
	}
}elseif($a=='ajax_search'){
	//搜索下拉
	$Keyword = $_POST['Keyword'];
	$Keyword || exit;
	$where = '1';
	$screenAry=where::products($PriceRange, $Narrow,1);
	$where.=$screenAry[0];
	$products_row = db::get_limit('products',$where.$c['where']['products'],"ProId,PicPath_0,Name{$c['lang']},PageUrl",$c['my_order'].'ProId desc',0,8);
	if($products_row){
?>
	<div class="search_content_box">
		<?php foreach((array)$products_row as $k => $v){ 
			$name = $v['Name'.$c['lang']];
			$img = ly200::get_size_img($v['PicPath_0'],'240x240');
			$url = ly200::get_url($v,'products');
			?>
			<div class="item <?=$k==0 ? 'first' : ''; ?>">
				<a href="<?=$url; ?>" class="pic pic_box" title="<?=$name; ?>">
					<img src="<?=$img; ?>" alt="<?=$name; ?>">
					<span></span>
				</a>
				<a href="<?=$url; ?>" class="name" title="<?=$name; ?>"><?=$name; ?></a>
				<div class="clear"></div>
			</div>
		<?php } ?>
	</div>
<?php
	}
	unset($products_row);
}elseif($a=='ajax_coupon'){
	//优惠券下拉
	if((int)$_SESSION['User']['UserId']){ //登录会员
		$Keyword=$_POST['keyword'];
		$where="1 and CouponWay=0 and ({$c['time']} between StartTime and EndTime) and (UseNum=0 or (UseNum>0 and UseNum>BeUseTimes))";
		$Keyword && $where.=" and CouponNumber like '%$Keyword%'";
		$where.=" and (UserId='-1' or (UserId='{$_SESSION['User']['UserId']}' or UserId like '%|{$_SESSION['User']['UserId']}|%'))";
		$coupon_row=str::str_code(db::get_limit('sales_coupon', $where, '*', 'CId desc', 0, 8));
	}
	if($coupon_row){
	?>
	<div class="coupon_content_box">
		<?php foreach((array)$coupon_row as $k=>$v){?>
			<div class="item" data-cid="<?=$v['CId'];?>" data-number="<?=$v['CouponNumber'];?>">
				<p><?='<span>'.($v['CouponType']?cart::iconv_price($v['Money']):(100-$v['Discount']).'% off').'</span>('.$v['CouponNumber'].')';?></p>
			</div>
		<?php }?>
	</div>
<?php
	}
	unset($cart_row);
}elseif($a=='goods_detail_pic'){
	//产品详细页主图 小图的横向显示
	$ProId=(int)$_GET['ProId'];
	$ColorId=(int)$_GET['ColorId'];
	$row=str::str_code(db::get_one('products_color', "ProId='$ProId' and VId='$ColorId' and VId>0 and PicPath_0!=''"));
	$pro_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
	if(!$row || !is_file($c['root_path'].$row['PicPath_0'])) $row=$pro_row;
?>
	<div class="detail_pic">
		<div class="up pic_shell">
			<div class="big_box">
				<div class="magnify" data="{}">
					<a class="big_pic" href="<?=$row['PicPath_0'];?>"><img itemprop="image" class="normal" src="<?=ly200::get_size_img($row['PicPath_0'], '500x500');?>" alt="<?=$pro_row['Name'.$c['lang']];?>" /></a>
				</div>
			</div>
		</div>
		<div class="down">
			<div class="small_carousel">
				<div class="viewport" data="<?=htmlspecialchars('{"small":"240x240","normal":"500x500","large":"v"}');?>">
					<ul class="list" style="width:640px;">
						<?php
						for($i=0; $i<10; $i++){
							$pic=$row['PicPath_'.$i];
							if(!is_file($c['root_path'].$pic)) continue;
						?>
						<li class="item FontBgColor<?=$i==0?' current':'';?>" pos="<?=$i+1;?>"><a href="javascript:;" class="pic_box FontBorderHoverColor" alt="" title="" hidefocus="true"><img src="<?=ly200::get_size_img($pic, '240x240');?>" title="<?=$pro_row['Name'.$c['lang']];?>" alt="<?=$pro_row['Name'.$c['lang']];?>" normal="<?=ly200::get_size_img($pic, '500x500');?>" mask="<?=$pic;?>" onerror="$.imgOnError(this)"><span></span></a><em class="arrow FontPicArrowColor"></em></li>
						<?php }?>
					</ul>
				</div>
				<a href="javascript:void(0);" hidefocus="true" class="btn left prev"><span class="icon_left_arraw icon_arraw"></span></a>
				<a href="javascript:void(0);" hidefocus="true" class="btn right next"><span class="icon_right_arraw icon_arraw"></span></a>
			</div>
		</div>
	</div>
<?php
}elseif($a=='goods_detail_pic_row'){
	//产品详细页主图 小图的竖向显示
	$ProId=(int)$_GET['ProId'];
	$ColorId=(int)$_GET['ColorId'];
	$row=str::str_code(db::get_one('products_color', "ProId='$ProId' and VId='$ColorId' and VId>0 and PicPath_0!=''"));
	$pro_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
	if(!$row || !is_file($c['root_path'].$row['PicPath_0'])) $row=$pro_row;
	
	$IsSeckill=(int)$_GET['IsSeckill'];
	$IsTuan=(int)$_GET['IsTuan'];
	$big_pic_size='500x500';
	if($IsSeckill){//秒杀详情页
		$detailWidth=$detailHeight=500;
		$detailLeft=526;
	}elseif($IsTuan){//团购详情页
		$detailWidth=$detailHeight=706;
		$detailLeft=710;
		$big_pic_size='';//原图
	}else{
		$detailWidth=$detailHeight=453;
		$detailLeft=426;
	}
?>
	<div class="detail_pic">
		<div class="left">
			<div class="small_carousel">
				<div class="viewport" data="<?=htmlspecialchars('{"small":"240x240","normal":"500x500","large":"x","xlarge":"x"}');?>">
					<ul class="list" style="width:47px; height:470px;">
						<?php
						for($i=0; $i<10; $i++){
							$pic=$row['PicPath_'.$i];
							if(!is_file($c['root_path'].$pic)) continue;
						?>
						<li class="item FontBgColor<?=$i==0?' current':'';?>" pos="<?=$i+1;?>"><a href="javascript:;" class="pic_box FontBorderHoverColor" alt="" title="" hidefocus="true"><img src="<?=ly200::get_size_img($pic, '240x240');?>" title="<?=$pro_row['Name'.$c['lang']];?>" alt="<?=$pro_row['Name'.$c['lang']];?>" normal="<?=ly200::get_size_img($pic, $big_pic_size);?>" mask="<?=$pic;?>" onerror="$.imgOnError(this)"><span></span></a><em class="arrow FontPicArrowXColor"></em></li>
						<?php }?>
					</ul>
				</div>
				<a href="javascript:;" hidefocus="true" class="btn top prev"></a>
				<a href="javascript:;" hidefocus="true" class="btn bottom next"></a>
			</div>
		</div>
		<div class="right pic_shell">
			<div class="big_box">
				<div class="magnify" data="<?=htmlspecialchars('{"detailWidth":"'.$detailWidth.'","detailHeight":"'.$detailHeight.'","detailLeft":"'.$detailLeft.'"}');?>">
					<a class="big_pic" href="<?=$row['PicPath_0'];?>"><img class="normal" src="<?=ly200::get_size_img($row['PicPath_0'], $big_pic_size);?>" alt="<?=$pro_row['Name'.$c['lang']];?>" /></a>
				</div>
			</div>
		</div>
	</div>
<?php
}
?>