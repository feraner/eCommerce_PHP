<?php !isset($c) && exit();?>
<?=ly200::load_static($c['mobile']['tpl_dir'].'js/swipe.js', $c['mobile']['tpl_dir'].'products/'.$c['mobile']['ListTpl'].'/js/products.js');?>
<?php
if($page==0){ //第一页才需要显示
?>
	<div class="pbanner clean" id="banner_box">
		<ul>
			<?php
			$ad_ary=ly200::ad_custom(0, 96);
			for($i=$sum=0; $i<$ad_ary['Count']; ++$i){
				if(!is_file($c['root_path'].$ad_ary['PicPath'][$i][$ad_ary['Lang']])) continue;
				$url=$ad_ary['Url'][$i][$ad_ary['Lang']];
				$sum++;
			?>
				<li><a href="<?=$url?$url:'javascript:;';?>"><img src="<?=$ad_ary['PicPath'][$i][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][$i][$ad_ary['Lang']];?>" /></a></li>
			<?php }?>
		</ul>
		<div class="btn">
			<?php for($i=0; $i<$sum; ++$i){?>
				<span class="<?=$i==0?'on':'';?>"></span>
			<?php }?>
		</div>
	</div>
<?php }?>
<div class="prolist">
	<?php if($page==0){?><h2 class="t"><?=$c['lang_pack']['mobile']['view_list'];?></h2><?php }?>
	<?php
	$list_ProId_ary=array();
	foreach($products_list_row[0] as $k=>$v){
		$list_ProId_ary[]=$v['ProId'];
		$url=$c['mobile_url'].ly200::get_url($v, 'products');
		$name=$v['Name'.$c['lang']];
		$price_ary=cart::range_price_ext($v);
		$rating=($v['IsDefaultReview'] && $v['DefaultReviewRating'])?(int)$v['DefaultReviewRating']:(int)$v['Rating'];
		$total_rating=($v['IsDefaultReview'] && $v['DefaultReviewTotalRating'])?$v['DefaultReviewTotalRating']:$v['TotalRating'];
	?>
	<div class="item">
		<div class="name"><a href="<?=$url;?>" title="<?=$name;?>"><?=$name;?></a></div>
		<div class="ex clean">
			<div class="price fl">
            	<?=cart::iconv_price(0, 1);?><span class="price_data" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0], 2);?></span>
                <?php if($c['config']['products_show']['Config']['price']){?>
                	<del><?=cart::iconv_price(0, 1);?><span class="price_data" data="<?=($is_promition && $v['PromotionType']==1)?$v['Price_1']:$v['Price_0'];?>"><?=cart::iconv_price((($is_promition && $v['PromotionType']==1)?$v['Price_1']:$v['Price_0']), 2);?></span></del>
				<?php }?>
            </div>
			<?php if($rating){?>
				<div class="star fl"><?=html::mobile_review_star($rating);?><span>(<?=$total_rating;?>)</span></div>
			<?php }?>
		</div>
	</div>
	<?php }?>
</div>