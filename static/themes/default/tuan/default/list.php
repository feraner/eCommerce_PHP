<?php !isset($c) && exit();?>
<?php
$CateId=(int)$_GET['CateId'];

$cate_row=str::str_code(db::get_limit('products_category', 'UId="0,"', '*',  $c['my_order'].'CateId asc', 0, 8));

$ad_ary=array();
$cur_lang=substr($c['lang'], 1);
$ad_row=db::get_one('ad', 'Themes="tuan"');
for($i=0; $i<$ad_row['PicCount']; ++$i){
	$ad_ary['Name'][$i]=str::json_data(htmlspecialchars_decode($ad_row['Name_'.$i]), 'decode');
	$ad_ary['Url'][$i]=str::json_data(htmlspecialchars_decode($ad_row['Url_'.$i]), 'decode');
	$ad_ary['PicPath'][$i]=str::json_data(htmlspecialchars_decode($ad_row['PicPath_'.$i]), 'decode');
}
$ad_url=$ad_ary['Url'][0][$cur_lang];
?>
<script type="text/javascript">$(document).ready(function(){tuan_obj.tuan_init()});</script>
<div id="tuan" class="wide">
	<div class="tuan_ad"><a href="<?=$ad_url?$ad_url:'javascript:;';?>"<?=$ad_url?' target="_blank"':'';?>><?php if(is_file($c['root_path'].$ad_ary['PicPath'][0][$cur_lang])){?><img src="<?=$ad_ary['PicPath'][0][$cur_lang];?>" alt="<?=$ad_ary['Name'][0][$cur_lang];?>" /><?php }?></a></div>
	<div class="tuan_head">
		<div class="title fl"><?=$c['lang_pack']['groupBuy'];?></div>
		<div class="view fr" id="tuan_title">
			<a href="javascript:;" rel="nofollow" data-type="this" class="current"><?=$c['lang_pack']['groupThis'];?></a>
			<a href="javascript:;" rel="nofollow" data-type="previous"><?=$c['lang_pack']['groupPrevious'];?></a>
		</div>
		<div class="clear"></div>
    </div>
	<div class="tuan_menu">
		<div class="category" catalog="0" past="" page="0">
			<a href="javascript:;" data="0" title="<?=$c['lang_pack']['all_category'];?>" class="current"><?=$c['lang_pack']['all'];?></a>
			<?php
			$cate_row=str::str_code(db::get_all('products_category', 'UId="0," and IsSoldOut=0', "CateId, Category{$c['lang']}",  $c['my_order'].'CateId asc'));
			foreach((array)$cate_row as $k=>$v){
			?>
				<a href="javascript:;" data="<?=$v['CateId'];?>" title="<?=$v['Category'.$c['lang']];?>"><?=$v['Category'.$c['lang']];?></a>
			<?php }?>
		</div>
		<div class="tuan_sort">
			<a href="javascript:;" data-sort="1"><?=$c['lang_pack']['price'];?><i class="icon_sort"></i></a>
			<a href="javascript:;" data-sort="2"><?=$c['lang_pack']['customer_review'];?><i class="icon_sort"></i></a>
			<a href="javascript:;" data-sort="3"><?=$c['lang_pack']['most_popular'];?><i class="icon_sort"></i></a>
		</div>
        <div class="clear"></div>
    </div>
	<div id="prolist" class="over" Num="0"></div>
</div>


