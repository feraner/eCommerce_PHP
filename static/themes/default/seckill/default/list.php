<?php !isset($c) && exit();?>
<?php
$ad_ary=array();
$cur_lang=substr($c['lang'], 1);
$ad_row=db::get_one('ad', 'Themes="seckill"');
for($i=0; $i<$ad_row['PicCount']; ++$i){
	$ad_ary['Name'][$i]=str::json_data(htmlspecialchars_decode($ad_row['Name_'.$i]), 'decode');
	$ad_ary['Url'][$i]=str::json_data(htmlspecialchars_decode($ad_row['Url_'.$i]), 'decode');
	$ad_ary['PicPath'][$i]=str::json_data(htmlspecialchars_decode($ad_row['PicPath_'.$i]), 'decode');
}
$ad_url=$ad_ary['Url'][0][$cur_lang];
?>
<script type="text/javascript">
$(document).ready(function(){seckill_obj.seckill_init()});
var seckill_timer=new Array();
</script>
<div id="seckill" class="wide">
	<div class="seck_ad"><a href="<?=$ad_url?$ad_url:'javascript:;';?>"<?=$ad_url?' target="_blank"':'';?>><?php if(is_file($c['root_path'].$ad_ary['PicPath'][0][$cur_lang])){?><img src="<?=$ad_ary['PicPath'][0][$cur_lang];?>" alt="<?=$ad_ary['Name'][0][$cur_lang];?>" /><?php }?></a></div>
	<div class="seck_head">
		<div class="title fl"><?=$c['lang_pack']['flashSale'];?></div>
		<div class="view fr" id="seck_title">
			<a href="javascript:;" rel="nofollow" data-type="dealing" class="current"><?=$c['lang_pack']['dailyDeals'];?></a>
			<a href="javascript:;" rel="nofollow" data-type="upcoming"><?=$c['lang_pack']['upcomingDeals'];?></a>
			<a href="javascript:;" rel="nofollow" data-type="past"><?=$c['lang_pack']['pastDeals'];?></a>
		</div>
		<div class="clear"></div>
    </div>
	<div class="seck_menu">
		<div class="category" catalog="0" past="" page="0">
			<a href="javascript:;" data="0" title="<?=$c['lang_pack']['all_category'];?>" class="current"><?=$c['lang_pack']['all'];?></a>
			<?php
			$cate_row=str::str_code(db::get_all('products_category', 'UId="0," and IsSoldOut=0', "CateId, Category{$c['lang']}",  $c['my_order'].'CateId asc'));
			foreach((array)$cate_row as $k=>$v){
			?>
				<a href="javascript:;" data="<?=$v['CateId'];?>" title="<?=$v['Category'.$c['lang']];?>"><?=$v['Category'.$c['lang']];?></a>
			<?php }?>
		</div>
		<div class="seck_sort">
			<a href="javascript:;" data-sort="1"><?=$c['lang_pack']['price'];?><i class="icon_sort"></i></a>
			<a href="javascript:;" data-sort="2"><?=$c['lang_pack']['customer_review'];?><i class="icon_sort"></i></a>
			<a href="javascript:;" data-sort="3"><?=$c['lang_pack']['most_popular'];?><i class="icon_sort"></i></a>
		</div>
        <div class="clear"></div>
    </div>
	<div id="prolist" class="over" Num="0"></div>
</div>



