<?php !isset($c) && exit();?>
<?php
$category_row=str::str_code(db::get_all('products_category', 'UId="0," and IsSoldOut=0', "CateId, UId, Category{$c['lang']}, PicPath", $c['my_order'].'CateId asc'));
?>
<div class="wrapper">
	<div class="banner" id="banner_box">
        <ul>
            <?php
			$ad_ary=ly200::ad_custom(0, 77);
            for($i=0; $i<$ad_ary['Count']; ++$i){
				if(!is_file($c['root_path'].$ad_ary['PicPath'][$i][$ad_ary['Lang']])) continue;
				$url=$ad_ary['Url'][$i][$ad_ary['Lang']];
            ?>
            <li><a href="<?=$url?$url:'javascript:;';?>"><img src="<?=$ad_ary['PicPath'][$i][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][$i][$ad_ary['Lang']];?>" /></a></li>
            <?php }?>
        </ul>
    </div>
    <div class="home_list">
    	<?php foreach((array)$category_row as $k=>$v){?>
			<div class="item clean">
				<div class="img fl pic_box">
					<?php if($v['PicPath'] && is_file($c['root_path'].$v['PicPath'])){?>
					<img src="<?=$v['PicPath'];?>" /><span></span>
					<?php }?>
				</div>
				<div class="name fl"><a href="<?=ly200::get_url($v, 'products_category');?>"><?=$v['Category'.$c['lang']];?></a></div>
			</div>
        <?php }?>
    </div>
</div>
