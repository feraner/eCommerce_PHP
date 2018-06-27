<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
$get_user_coupons = (int)$_GET['get_user_coupons'];
$g_page=(int)$_GET['page'];
$type=(int)$_GET['type'];
$page_count=10;
$user_coupons_where = "CouponWay=1 and ({$c['time']} < EndTime and {$c['time']} > StartTime)  and CId not in (select ParentId from sales_coupon where CouponWay=0 and UserId = '{$_SESSION['User']['UserId']}' and ParentId != 0)";
$user_coupons_count = db::get_row_count('sales_coupon',$user_coupons_where);
if($get_user_coupons){
	$page_count=12;
	$where = $user_coupons_where;
}else{	
	$where = 'CouponWay=0 and ('.$c['where']['user']." or UserId = -1";
	$Level = (int)$user_row['Level'];
	if($Level) $where.=" or (LevelId = -1 or LevelId like '|{$Level}|')";
	$where.=')';
	if($type==1){
		$where.=" and ({$c['time']} > EndTime or {$c['time']} < StartTime or (UseNum > 0 and BeUseTimes >= UseNum))";
	}else{
		$where.=" and {$c['time']} < EndTime and {$c['time']} > StartTime and (UseNum=0 or (UseNum > 0 and BeUseTimes < UseNum))";
	}
}
$row=str::str_code(db::get_limit_page('sales_coupon', $where, '*', 'UseCondition asc,Discount asc', $g_page, $page_count));
$query_string=ly200::query_string('m,a,page');
?>
<script type="text/javascript">$(document).ready(function(){user_obj.coupon_init()});</script>
<div class="user_coupons">
	<?php if($_GET['get_user_coupons']){ ?>
		<a href="javascript:javascript :history.back(-1);" class="user_back"><?=$c['lang_pack']['user']['coupons']; ?></a>
	<?php }else{ ?>
		<div id="user_heading">
			<h2>
				<?=$c['lang_pack']['user']['couponTitle']; ?>
			</h2>
		</div>
	<?php } ?>
	<?php if($_GET['get_user_coupons']){ ?>
		<script>$(document).ready(function(){user_obj.user_index_init()});</script>
		<div class="user_get_coupons">
			<?php 
			$user_count = db::get_row_count('user');
			foreach((array)$row[0] as $k => $v){ 
				$get_count = db::get_row_count('sales_coupon',"CouponWay=0 and ParentId='{$v['CId']}'");
				$only = (int)(($user_count-$get_count)/$user_count*100);
				?>
				<div class="item <?=$k%3==0 ? 'fir' : ''; ?>">
					<div class="cou">
						<p class="price">
							<?php if($v['CouponType']){ ?>
								<span><?=cart::iconv_price(0, 1); ?></span><?=cart::iconv_price($v['Money'], 2, '', 0); ?>
							<?php }else{ ?>
								<?=$v['Discount'];?><span>% off</span>
							<?php } ?>
						</p>
						<p class="over"><?=$v['UseCondition'] > 0 ? str_replace('%price%', cart::iconv_price($v['UseCondition'], 0), $c['lang_pack']['user']['order_over']) : ''; ?></p>
						<p class="only"><?=$c['lang_pack']['only']; ?> <?=$only; ?>% <span><em style="width:<?=$only; ?>%;"></em></span></p>
					</div>
					<p class="date"><?=date('d/m/Y',$v['StartTime']); ?> - <?=date('d/m/Y',$v['EndTime']); ?></p>
					<a href="javascript:;" data-cid="<?=$v['CId']; ?>" class="get_it"><?=$c['lang_pack']['user']['get_it']; ?></a>
				</div>
			<?php } ?>
			<div class="clear"></div>
		</div>
	<?php }else{ ?>
		<ul class="menu_title cou_type">
			<?php if($user_coupons_count){ ?>
				<li class="fr"><a href="/account/coupon/?get_user_coupons=1" class="more"><?=$c['lang_pack']['user']['get_more_cou']; ?></a></li>
			<?php } ?>
			<li><a href="/account/coupon/" class="item <?=$type==0 ? 'current FontBorderColor' : ''; ?>"><?=$c['lang_pack']['user']['already_received']; ?></a></li>
			<li>
				<a href="/account/coupon/?type=1" class="item <?=$type==1 ? 'current FontBorderColor' : ''; ?>"><?=$c['lang_pack']['user']['expired']; ?></a>
			</li>
		</ul>
		<div class="cou_list">
			<?php foreach((array)$row[0] as $k=>$v){?>
				<div class="item<?=$k%2==1?' fr':' fl';?>">
					<div class="itl<?=$type==1?' old':'';?>">
						<?php if($v['CouponType']){?>
							<span class="price <?=$_SESSION['Currency']['Currency'];?>"><span class="symbols"><?=cart::iconv_price(0, 1);?></span><?=cart::iconv_price($v['Money'], 2, '', 0);?></span>
						<?php }else{?>
							<span class="discount"><?=100-$v['Discount'];?></span><p>% off</p>
						<?php }?>
					</div>
					<div class="itr">
						<div class="code"><?=$c['lang_pack']['user']['code'];?>:<?=$v['CouponNumber'];?></div>
						<div class="over"><?=$v['UseCondition'] > 0 ? str_replace('%price%', cart::iconv_price($v['UseCondition'], 0), $c['lang_pack']['user']['order_over']) : ''; ?></div>
						<div class="date <?=$c['time']+15*24*60*60 >= $v['EndTime'] && !$type ? 'red' : ''; ?>">
							<?php if($v['UseNum']){?>
								<div class="time fr"><?=str_replace('%num%', $v['UseNum']-$v['BeUseTimes'], $c['lang_pack']['user']['times']); ?></div>
							<?php }?>
							<?=date('d/m/Y',$v['StartTime']); ?> - <?=date('d/m/Y',$v['EndTime']); ?>
						</div>
						<?php if($type==1){ ?><div class="expired"></div><?php } ?>
					</div>
					<div class="clear"></div>
				</div>
			<?php } ?>
			<div class="clear"></div>
		</div>
	<?php } ?>
	
	<?php if($row[3]>1){?>
	    <div class="blank20"></div>
	    <div id="turn_page"><?=ly200::turn_page_html($row[1], $row[2], $row[3], $query_string, $c['lang_pack']['user']['previous'], $c['lang_pack']['user']['next'], 3, '.html', $html=1);?></div>
	<?php }?>
</div>
