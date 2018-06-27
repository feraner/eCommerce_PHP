<?php !isset($c) && exit();?>
<?php
$category_row=str::str_code(db::get_all('products_category', 'UId="0," and IsSoldOut=0', "CateId, UId, Category{$c['lang']}", $c['my_order'].'CateId asc'));
?>
<div class="wrapper">
    <div class="banner" id="banner_box">
    	<ul>
            <?php
			$ad_ary=ly200::ad_custom(0, 86);
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
    		<div class="item"><a href="<?=ly200::get_url($v, 'products_category');?>"><?=$v['Category'.$c['lang']];?></a></div>
        <?php }?>
    </div>
    <div class="join clean">
    	<div class="fl txt"><?=$c['lang_pack']['mobile']['join_email'];?>:</div>
        <div class="fl search">
            <form id="newsletter">
                <input type="email" name="Email" class="fl text" value="" placeholder="<?=$c['lang_pack']['mobile']['email_addr'];?>" />
                <input type="button" name="submit" class="fr sub" value="" />
                <input type="hidden" name="typ" value="newsletter" />
            </form>
        </div>
    </div>
</div>
