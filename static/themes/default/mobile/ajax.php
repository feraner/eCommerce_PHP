<?php !isset($c) && exit();?>
<?php
if($a=='tuan_default'){
	//团购专题页（default风格）
	$d_ary=array('this', 'previous');
	$typ=$_POST['typ'];
	!in_array($typ, $d_ary) && $typ=$d_ary[0];
	
	$CateId=(int)$_POST['CateId'];
	$page=(int)$_POST['page'];
	$page_count=6;
	$page<1 && $page=1;
	$where='1';
	if($typ=='previous'){//过去
		$where.=" and {$c['time']}>s.EndTime";
	}else{//现在
		$where.=" and s.StartTime<={$c['time']} and s.EndTime>{$c['time']}";
	}
	$CateId && $where.=' and p.'.category::get_search_where_by_CateId($CateId, 'products_category');
	
	$row=str::str_code(db::get_limit_page('sales_tuan s left join products p on s.ProId=p.ProId', $where.' and s.BuyerCount<s.TotalCount', "s.*, p.Name{$c['lang']}, p.PicPath_0, p.Price_0, p.Price_1, p.TotalRating, p.IsHot, p.IsDefaultReview, p.DefaultReviewRating, p.DefaultReviewTotalRating, p.Rating, p.TotalRating", 'if(s.MyOrder>0, if(s.MyOrder=999, 1000001, s.MyOrder), 1000000) asc, s.TId desc', $page, $page_count));
	
	if(count($row)){
		foreach((array)$row[0] as $k=>$v){
			$name=$v['Name'.$c['lang']]=$v['Name'.$c['lang']];
			$number=$v['Prefix'].$v['Number'];
			$url=ly200::get_url($v, 'tuan_mobile');
			$img=ly200::get_size_img($v['PicPath_0'], '350x350');
			$old_price=$v['Price_1'];
			$rating=($v['IsDefaultReview'] && $v['DefaultReviewRating'])?(int)$v['DefaultReviewRating']:(int)$v['Rating'];
			$total_rating=($v['IsDefaultReview'] && $v['DefaultReviewTotalRating'])?$v['DefaultReviewTotalRating']:$v['TotalRating'];
?>
			<div class="item clean" data-url="<?=$url;?>">
				<div class="img fl pic_box">
					<a href="javascript:;" title="<?=$name;?>"><img src="<?=$img?>" /><span></span></a>
				</div>
				<div class="desc">
					<div class="name"><a href="javascript:;" title="<?=$name;?>"><?=$name;?></a></div>
					<div class="rows clean">
						<div class="view_review star"><?=html::mobile_review_star($rating);?><span>(<?=$total_rating;?>)</span></div>
						<div class="view_sold"><?=($v['TotalCount']-$v['BuyerCount']).' '.$c['lang_pack']['sold'];?></div>
					</div>
					<div class="rows clean">
						<div class="price"><span class="price_data" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($v['Price']);?></span></div>
						<div class="old_price"><del><?=cart::iconv_price($old_price);?></del></div>
					</div>
					<?php /*<div class="rows clean"><a href="<?=$url;?>" class="btn_cart" data="<?=$v['ProId'];?>"><?=$c['lang_pack']['products']['buyNow'];?></a></div>*/?>
				</div>
			</div>
<?php
		}
	}
	
	if(!$row[3]){
		echo '<div class="content_blank">'.$c['lang_pack']['mobile']['no_data'].'</div>';
	}elseif(($page==1 && $row[3]==1) || ($page && $page>=$row[3])){ //总共只有一页 或者 最后一页
		echo '<div class="clear"></div><div class="content_more">'.$c['lang_pack']['mobile']['no_data'].'</div>';
	}else{
		echo '<div class="btn_global btn_view"><button class="btn_global FontBgColor">'.$c['lang_pack']['mobile']['load_more'].'</button></div>';
	}

}elseif($a=='seckill_default'){
	//秒杀专题页（default风格）
	$d_ary=array('dealing', 'upcoming', 'past');
	$typ=$_POST['typ'];
	!in_array($typ, $d_ary) && $typ=$d_ary[0];
	
	$CateId=(int)$_POST['CateId'];
	$page=(int)$_POST['page'];
	$page_count=6;
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
	
	$row=str::str_code(db::get_limit_page('sales_seckill s left join products p on s.ProId=p.ProId', $where.' and s.RemainderQty>0', "s.*, p.Name{$c['lang']}, p.PicPath_0, p.Price_0, p.Price_1, p.TotalRating, p.IsHot", 'if(s.MyOrder>0, if(s.MyOrder=999, 1000001, s.MyOrder), 1000000) asc, s.SId desc', $page, $page_count));
	
	if(count($row)){
		foreach((array)$row[0] as $k=>$v){
			$name=$v['Name'.$c['lang']]=$v['Name'.$c['lang']];
			$number=$v['Prefix'].$v['Number'];
			$url=ly200::get_url($v, 'seckill_mobile');
			$img=ly200::get_size_img($v['PicPath_0'], '350x350');
			$old_price=$v['Price_1'];
			//$discount=sprintf('%01.0f', ($old_price-$v['Price'])/$old_price*100);
			$discount=ceil((1-$v['RemainderQty']/$v['Qty'])*100);
?>
			<div class="item clean" data-url="<?=$url;?>">
				<div class="img fl pic_box">
					<a href="javascript:;" title="<?=$name;?>"><img src="<?=$img?>" /><span></span></a>
				</div>
				<div class="desc">
					<div class="name"><a href="javascript:;" title="<?=$name;?>"><?=$name;?></a></div>
					<div class="rows clean">
						<div class="price"><span class="price_data" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($v['Price']);?></span></div>
						<div class="old_price"><del><?=cart::iconv_price($old_price);?></del></div>
					</div>
					<div class="rows clean progress_box">
						<div class="progress_count"><?=$c['lang_pack']['only'];?> <?=100-$discount;?>%</div>
						<div class="progress_sold"><?=($v['Qty']-$v['RemainderQty']).' '.$c['lang_pack']['sold'];?></div>
						<div class="progress"><div class="progress_current" style="width:<?=$discount;?>%;"></div></div>
					</div>
					<?php
					if($typ=='dealing'){
						$m=(int)@date('m', $v['EndTime'])-1;
						$d=date("Y, $m, j, G, i, s", $v['EndTime']);
					?>
						<div class="rows clean time_box time_box_<?=$page;?>">
							<i></i><span id="flashsale_<?=$v['ProId'];?>" data-endTime="<?=date('Y/m/d H:i:s', $v['EndTime']);?>" data-proId="<?=$v['ProId'];?>"></span>
						</div>
					<?php }?>
					<?php /*<div class="rows clean"><a href="<?=$url;?>" class="btn_cart" data="<?=$v['ProId'];?>"><?=$c['lang_pack']['products']['buyNow'];?></a></div>*/?>
				</div>
			</div>
<?php
		}
	}
	
	if(!$row[3]){
		echo '<div class="content_blank">'.$c['lang_pack']['mobile']['no_data'].'</div>';
	}elseif(($page==1 && $row[3]==1) || ($page && $page>=$row[3])){ //总共只有一页 或者 最后一页
		echo '<div class="content_more">'.$c['lang_pack']['mobile']['no_data'].'</div>';
	}else{
		echo '<div class="btn_global btn_view"><button class="btn_global FontBgColor">'.$c['lang_pack']['mobile']['load_more'].'</button></div>';
	}
	
}elseif($a=='products_list'){
	//产品列表页
	$_GET=str::json_data(htmlspecialchars_decode(stripslashes($_POST['Data'])), 'decode');
	$page_count=(int)$_POST['page_count'];
	$page=(int)$_POST['page'];
	if($page_count==10){
		$page++;
	}
	$CateId=(int)$_GET['CateId'];
	$Keyword=$_GET['Keyword'];
	$Narrow=str::str_code($_GET['Narrow'], 'urlencode');
	$Ext=(int)$_GET['Ext'];
	$Sort=($_GET['Sort'] && $c['products_sort'][$_GET['Sort']])?$_GET['Sort']:'1a';
	$where=1;
	if($CateId){
		$UId=category::get_UId_by_CateId($CateId);
		$where.=" and (CateId in(select CateId from products_category where UId like '{$UId}%') or CateId='{$CateId}' or ".category::get_search_where_by_ExtCateId($CateId, 'products_category').')';
		$category_row=db::get_one('products_category', "CateId='$CateId'");
	}
	if($Ext){
		$Ext=($Ext<1 || $Ext>4)?1:$Ext;
		$where.=$c['where']['products_ext'][$Ext];
	}
	if((int)$_POST['IsSearch']){//来源搜索页
		$_SERVER['REQUEST_URI']='/search/';//伪造来源地址
	}
	$screenAry=where::products('', $Narrow);
	$where.=$screenAry[0];//条件
	$Narrow_ary=$screenAry[3];//筛选属性
	$OrderBy=$screenAry[4];//条件排序
	$products_list_row=str::str_code(db::get_limit_page('products', $where.$c['where']['products'], '*', $OrderBy.$c['products_sort'][$Sort].$c['my_order'].'ProId desc', $page, $page_count));
	include("{$c['mobile']['theme_path']}products/{$c['mobile']['ListTpl']}/template.php");
	echo "<script>$('html').seckillPrice();</script>"; //秒杀价格
	
	if(!$products_list_row[3]){
		echo '<div class="content_blank">'.$c['lang_pack']['mobile']['no_data'].'</div>';
	}elseif(($page==0 && $products_list_row[3]==1) || ($page && $page>=$products_list_row[3])){ //总共只有一页 或者 最后一页
		echo '<div class="content_more">'.$c['lang_pack']['mobile']['no_data'].'</div>';
	}else{
		echo '<div class="btn_global btn_view"><button class="btn_global FontBgColor">'.$c['lang_pack']['mobile']['load_more'].'</button></div>';
	}

}elseif($a=='review_list'){
	//产品评论列表页
	$ProId=(int)$_POST['ProId'];
	$Action=$_POST['Action'];
	$review_cfg=str::json_data(db::get_value('config', "GroupId='products_show' and Variable='review'", 'Value'), 'decode');
	if($Action=='goods'){
		$g_page=0;
		$page_count=8;//显示数量
	}else{
		$g_page=(int)$_POST['page'];
		$page_count=20;//显示数量
	}
	$where="p.ProId='{$ProId}' and p.ReId=0";
	$review_cfg['display']==1 && $where.=" and p.Audit=1";
	$review_row=str::str_code(db::get_limit_page('products_review p left join user u on p.UserId=u.UserId left join user_level l on u.Level=l.LId', $where, "p.*, u.FirstName, u.LastName, u.Level, l.Name{$c['lang']}, l.PicPath", 'p.RId desc', $g_page, $page_count));
	
	if($review_row[0]){
		foreach((array)$review_row[0] as $k=>$v){
			$rating_ary=explode(',', $v['Assess']);
			$reply_row=str::str_code(db::get_all('products_review p left join user u on p.UserId=u.UserId', "p.ProId='{$ProId}' and p.ReId='{$v['RId']}'".($review_cfg['display']==1?' and p.Audit=1':''), 'p.*, u.FirstName, u.LastName', 'p.RId desc'));
			$reply_len=count($reply_row);
			$name=($v['FirstName'] || $v['LastName'])?$v['FirstName'].' '.$v['LastName']:$v['CustomerName'];
			(int)$review_cfg['anonymous']==1 && $name=str::cut_str($name, 3).'***';//匿名显示
	?>
		<div class="item<?=($k+1)<$review_row[1]?' ui_border_b':'';?>">
			<div class="title">
				<span class="name"><?=$name;?></span><?=html::mobile_review_star($v['Rating']);?><span class="date"><?=date('M / d / Y', $v['AccTime']);?></span>
			</div>
			<div class="txt"><?=str::format($v['Content']);?></div>
			<div class="pic_list clean">
				<?php
				for($i=0; $i<3; ++$i){
					if(!$v['PicPath_'.$i] || !is_file($c['root_path'].$v['PicPath_'.$i])) continue;
				?>
					<a href="<?=$v['PicPath_'.$i];?>" class="pic_box show_image" data-lightbox="example-set"><img src="<?=ly200::get_size_img($v['PicPath_'.$i], '85x85');?>" /><span></span></a>
				<?php }?>
			</div>
			<div class="reply">
				<?php if($reply_len){?>
					<div class="w_review_replys">
						<span class="arrow"></span>
						<?php foreach((array)$reply_row as $v2){?>
							<div class="review_reply">
								<p><?=$v2['Content'];?></p>
								<p class="writer"><span class="light_gray"><?=$c['lang_pack']['products']['by'];?></span> <cite class="replier"><?=$v2['CustomerName'];?></cite> <cite class="light_gray date"><?=date('M / d / Y', $v2['AccTime']);?></cite></p>
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
	<?php
		}
	}
	
	if(!$review_row[3]){
		// echo '<div class="content_blank">'.$c['lang_pack']['mobile']['no_data'].'</div>';
	}elseif($page>=$review_row[3] && $page){
		echo '<div class="content_more">'.$c['lang_pack']['mobile']['no_data'].'</div>';
	}
	
}elseif($a=='user_orders_list'){
	//会员订单页
	$row_count=5;
	$page=(int)$_POST['page'];
	$order_row=str::str_code(db::get_limit_page('orders', $c['where']['user'], '*', 'OrderId desc', $page, $row_count));
	include("{$c['mobile']['theme_path']}products/{$c['mobile']['ListTpl']}/template.php");
	
	//所有产品属性
	$attribute_cart_ary=$vid_data_ary=array();
	$attribute_row=str::str_code(db::get_all('products_attribute', '1', "AttrId, Type, Name{$c['lang']}, ParentId, CartAttr, ColorAttr"));
	foreach($attribute_row as $v){
		$attribute_ary[$v['AttrId']]=array(0=>$v['Type'], 1=>$v["Name{$c['lang']}"]);
	}
	$value_row=str::str_code(db::get_all('products_attribute_value', '1', '*', $c['my_order'].'VId asc')); //属性选项
	foreach($value_row as $v){
		$vid_data_ary[$v['AttrId']][$v['VId']]=$v["Value{$c['lang']}"];
	}
	
	if($order_row[0]){
		foreach($order_row[0] as $k=>$v){
			$isFee=($v['OrderStatus']>=4 && $v['OrderStatus']!=7)?1:0;
			$total_price=orders::orders_price($v, $isFee);
			$url="/account/orders/view{$v['OId']}.html";
	?>
			<div class="item clean" data-url="<?=$url?>">
				<div class="title clean ui_border_b">
					<span class="oid"><?=$c['lang_pack']['mobile']['no.'].$v['OId'];?></span>
					<em><i></i></em>
					<span class="status"><?=$c['lang_pack']['user']['OrderStatusAry'][$v['OrderStatus']];?></span>
				</div>
				<div class="prod_list">
					<?php
					$subtotal=0;
					$order_list_row=db::get_all('orders_products_list o left join products p on o.ProId=p.ProId', "o.OrderId='{$v['OrderId']}'", 'o.*, p.PicPath_0, p.Prefix, p.Number', 'o.LId asc');
					foreach($order_list_row as $k2=>$v2){
						if($v2['BuyType']==4){
							//组合促销产品
							$package_row=str::str_code(db::get_one('sales_package', "PId='{$v2['KeyId']}'"));
							if(!$package_row) continue;
							$attr=array();
							$v2['Property']!='' && $attr=str::json_data(str::attr_decode($v2['Property']), 'decode');
							$products_row=str::str_code(db::get_all('products', "SoldOut=0 and ProId='{$package_row['ProId']}'"));
							$pro_where=str_replace('|', ',', substr($package_row['PackageProId'], 1, -1));
							$pro_where=='' && $pro_where=0;
							$products_row=array_merge($products_row, str::str_code(db::get_all('products', "SoldOut=0 and ProId in($pro_where)")));
							$data_ary=str::json_data(htmlspecialchars_decode($package_row['Data']), 'decode');
					?>
							<div class="prod_box clean">
								<h4 class="fl">[ <?=$c['lang_pack']['cart']['package']?> ] <?=$package_row['Name'];?></h4>
								<div class="fr"><?=cart::iconv_price($v2['Price'], 0, $orders_row['Currency']);?></div>
								<div class="clear"></div>
								<?php
								foreach((array)$products_row as $k3=>$v3){
									$name=$v3['Name'.$c['lang']];
									$number=$v3['Prefix'].$v3['Number'];
									$img=ly200::get_size_img($v3['PicPath_0'], '240x240');
									$url=ly200::get_url($v3, 'products');
									$subtotal+=$v2['Qty'];
								?>
								<div class="plist package clean<?=$k3?'':' first';?>">
									<div class="img fl"><img src="<?=$img;?>" alt="<?=$name;?>" /></div>
									<div class="info">
										<div class="name"><a href="<?=$url;?>"><?=$name;?></a></div>
										<?php if($number){?><div class="number"><?=$number;?></div><?php }?>
										<?php
										if($k3==0){
											foreach((array)$attr as $k4=>$v4){
												if($k4=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v2['OvId']==1)) continue; //发货地是中国，不显示
												echo '<div class="attr clean">'.($k4=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k4).': &nbsp;'.$v4.'</div>';
											}
											if((int)$c['config']['global']['Overseas']==1 && $v2['OvId']==1){
												echo '<div class="attr clean">'.$c['lang_pack']['products']['shipsFrom'].': &nbsp;'.$c['config']['Overseas'][$v2['OvId']]['Name'.$c['lang']].'</div>';
											}
										}elseif($data_ary[$v3['ProId']]){
											$OvId=0;
											foreach((array)$data_ary[$v3['ProId']] as $k4=>$v4){
												if($k4=='Overseas'){ //发货地
													$OvId=str_replace('Ov:', '', $v4);
													if((int)$c['config']['global']['Overseas']==0 || $OvId==1) continue; //发货地是中国，不显示
													echo '<div class="attr clean">'.$c['lang_pack']['products']['shipsFrom'].': &nbsp;'.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</div>';
												}else{
													echo '<div class="attr clean">'.$attribute_ary[$k4][1].': &nbsp;'.$vid_data_ary[$k4][$v4].'</div>';
												}
											}
											if((int)$c['config']['global']['Overseas']==1 && $OvId==1){
												echo '<div class="attr clean">'.$c['lang_pack']['products']['shipsFrom'].': &nbsp;'.$c['config']['Overseas'][$OvId]['Name'.$c['lang']].'</div>';
											}
										}?>
									</div>
									<div class="value">
										<div class="qty">x<?=$v2['Qty'];?></div>
									</div>
								</div>
								<?php }?>
							</div>
					<?php
						}else{
							//其他产品
							$name=$v2['Name'];
							$number=$v2['Prefix'].$v2['Number'];
							$attr=str::json_data($v2['Property'], 'decode');
							$subtotal+=1;
							$price=$v2['Price']+$v2['PropertyPrice'];
							$v2['Discount']<100 && $price*=$v2['Discount']/100;
							if($k2==3) echo '<div class="prod_tr">';
					?>
							<div class="prod_box clean">
								<div class="plist clean first">
									<div class="img fl"><img src="<?=$v2['PicPath'];?>" alt="<?=$name;?>" /></div>
									<div class="info">
										<div class="name"><a href="<?=$url;?>"><?=$name;?></a></div>
										<?php if($number){?><div class="number"><?=$number;?></div><?php }?>
										<?php
										if(count($attr)){
											foreach($attr as $k3=>$v3){
												if($k3=='Overseas' && ((int)$c['config']['global']['Overseas']==0 || $v2['OvId']==1)) continue; //发货地是中国，不显示
												echo '<div class="attr clean">'.($k3=='Overseas'?$c['lang_pack']['products']['shipsFrom']:$k3).': &nbsp;'.$v3.'</div>';
											}
										}
										if((int)$c['config']['global']['Overseas']==1 && $v2['OvId']==1){
											echo '<div class="attr clean">'.$c['lang_pack']['products']['shipsFrom'].': &nbsp;'.$c['config']['Overseas'][$v2['OvId']]['Name'.$c['lang']].'</div>';
										}?>
									</div>
									<div class="value">
										<div class="price"><?=cart::iconv_price($price, 0, $v2['Currency']);?></div>
										<div class="qty">x<?=$v2['Qty'];?></div>
									</div>
								</div>
							</div>
					<?php
						}
					}
					if($k2>2) echo '</div>';
					?>
				</div>
				<div class="total ui_border_tb">
					<?php if($k2>2){?><a href="javascript:;" class="btn_global btn_more ui_border_radius"><?=$c['lang_pack']['more'];?><em><i></i></em></a><?php }?>
					<?=$subtotal.' '.$c['lang_pack']['cart']['items'].'&nbsp;&nbsp;&nbsp;'.$c['lang_pack']['cart']['amount'].': '.$v['Currency'].' '.cart::currency_format($total_price, 0, $v['Currency']);?>
				</div>
			</div>
			<div class="divide_8px"></div>
	<?php
		}
	}
	
	if(!$order_row[3]){ //没有
		echo '<div class="content_blank">'.$c['lang_pack']['mobile']['no_data'].'</div>';
	}elseif($page>=$order_row[3] && $page!=1){ //没有更多
		echo '<div class="content_more">'.$c['lang_pack']['mobile']['no_data'].'</div>';
	}

}elseif($a=='goods_detail_pic'){
	//产品详细页主图 小图的横向显示
	$ProId=(int)$_GET['ProId'];
	$ColorId=(int)$_GET['ColorId'];
	$row=str::str_code(db::get_one('products_color', "ProId='$ProId' and VId='$ColorId' and VId>0 and PicPath_0!=''"));
	$pro_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
	if(!$row || !is_file($c['root_path'].$row['PicPath_0'])) $row=$pro_row;
?>
	<div class="goods_pic">
		<ul class="clean">
			<?php
			for($i=0; $i<10; ++$i){
				$pic=$row['PicPath_'.$i];
				if(!is_file($c['root_path'].$pic)) continue;
			?>
				<li class="fl"><img src="<?=ly200::get_size_img($pic, '500x500');?>"></li>
			<?php }?>
		</ul>
		<div class="trigger clean">
			<?php
			for($i=0; $i<10; $i++){
				$pic=$row['PicPath_'.$i];
				if(!is_file($c['root_path'].$pic)) continue;
			?>
				<div class="item<?=$i==0?' FontBgColor':' off';?>"><?=$i;?></div>
			<?php }?>
		</div>
	</div>
	<!-- 抛物线的div -->
	<div class="big_pic" style="display:none;"><img src="<?=ly200::get_size_img($row['PicPath_0'], '240x240');?>" class="normal" alt="<?=$pro_row['Name'.$c['Lang']];?>"></div>
<?php
}
?>