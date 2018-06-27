<?php !isset($c) && exit();?>
<?php
$g_page=(int)$_GET['page'];
$page_count=10;
$row=str::str_code(db::get_limit_page('sales_coupon', $c['where']['user'], '*', 'CId desc', $g_page, $page_count));
$query_string=ly200::get_query_string(ly200::query_string('m, a, p, page'));
?>
<script type="text/javascript">$(function (){user_obj.user_order()});</script>
<div id="user">
	<?=html::mobile_crumb('<em><i></i></em><a href="/account/">'.$c['lang_pack']['mobile']['my_account'].'</a><em><i></i></em><a href="/account/coupon/">'.$c['lang_pack']['my_coupon'].'</a>');?>
    <div class="user_coupon">
    	<?php
		if($row[0]){
			foreach($row[0] as $k=>$v){
		?>
			<div class="item clean ui_border_b">
            	<div class="clean">
					<div class="fl cpnum"><?=$c['lang_pack']['mobile']['coupon_code'];?>: <?=$v['CouponNumber'];?> (<span class="fcr"><?=$v['BeUseTimes']?$c['lang_pack']['user']['alreadyUse'].($v['UseNum']>0?" ({$v['BeUseTimes']}/{$v['UseNum']})":" ({$v['BeUseTimes']})"):$c['lang_pack']['user']['effective'];?></span>)</div>
                </div>
                <div class="cpnum"><?=$v['CouponType']?"{$c['lang_pack']['mobile']['redu_of']}".cart::iconv_price($v['Money']):(100-$v['Discount']).'% off';?></div>
				<div class="fl cpnum"><?=$c['lang_pack']['mobile']['failure_time']?>: <span class="fcg"><?=date('m/d/Y', $v['EndTime']+60);?></span></div>
                <div class="fr cpdate"><?=$c['lang_pack']['mobile']['con_of_use']?>: <?=$c['lang_pack']['mobile']['full']?> <?=cart::iconv_price($v['UseCondition']);?></div>
			</div>
			<?php }?>
        <?php }else{?>
        	<div class="content_blank"><?=$c['lang_pack']['mobile']['you_no_coupon']?></div>
        <?php }?>
    </div>
</div>