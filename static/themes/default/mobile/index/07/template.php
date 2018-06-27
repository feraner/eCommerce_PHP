<?php !isset($c) && exit();?>
<?php
$article_category_row=str::str_code(db::get_limit('article_category', 'CateId not in(1)', "CateId, Category{$c['lang']}", 'CateId asc', 0, 2));
$article_row=str::str_code(db::get_all('article', 'CateId not in(1)', "AId, CateId, Title{$c['lang']}"));
$article_ary=array();
foreach($article_row as $v){
	$article_ary[$v['CateId']][]=$v;
}
?>
<div class="wrapper">
	<div class="banner clean" id="banner_box">
    	<ul>
            <?php
            $ad_ary=ly200::ad_custom(0, 93);
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
	<?php
	$ad_ary=ly200::ad_custom(0, 94);
	?>
    <div class="banner clean"><a href="<?=$ad_ary['Url'][0][$ad_ary['Lang']]?$ad_ary['Url'][0][$ad_ary['Lang']]:'javascript:;';?>"><img src="<?=$ad_ary['PicPath'][0][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][0][$ad_ary['Lang']];?>" /></a></div>
	<?php
	$ad_ary=ly200::ad_custom(0, 95);
	?>
    <div class="banner clean">
    	<div class="element"><a href="<?=$ad_ary['Url'][0][$ad_ary['Lang']]?$ad_ary['Url'][0][$ad_ary['Lang']]:'javascript:;';?>"><img src="<?=$ad_ary['PicPath'][0][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][0][$ad_ary['Lang']];?>" /></a></div>
        <div class="element"><a href="<?=$ad_ary['Url'][1][$ad_ary['Lang']]?$ad_ary['Url'][1][$ad_ary['Lang']]:'javascript:;';?>"><img src="<?=$ad_ary['PicPath'][1][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][1][$ad_ary['Lang']];?>" /></a></div>
    </div>
    <div class="banner clean">
    	<div class="element"><a href="<?=$ad_ary['Url'][2][$ad_ary['Lang']]?$ad_ary['Url'][2][$ad_ary['Lang']]:'javascript:;';?>"><img src="<?=$ad_ary['PicPath'][2][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][2][$ad_ary['Lang']];?>" /></a></div>
        <div class="element"><a href="<?=$ad_ary['Url'][3][$ad_ary['Lang']]?$ad_ary['Url'][3][$ad_ary['Lang']]:'javascript:;';?>"><img src="<?=$ad_ary['PicPath'][3][$ad_ary['Lang']];?>" alt="<?=$ad_ary['Name'][3][$ad_ary['Lang']];?>" /></a></div>
    </div>
    <?php foreach((array)$article_category_row as $k=>$v){?>
		<section class="h-container<?=$k==0?' on':'';?>">
			<h2 class="clean"><?=$v['Category'.$c['lang']];?></h2>
			<div class="list clean" style="display:<?=$k==0?'block':'none';?>;">
				<?php foreach((array)$article_ary[$v['CateId']] as $k2=>$v2){?>
					<div class="item fl"><a href="<?=$c['mobile_url'].ly200::get_url($v2, 'article');?>"><?=$v2['Title'.$c['lang']];?></a></div>
					<?php if(($k2+1)%2==0 || $k2==count($article_ary[$v['CateId']])-1){?><p class="line"></p><?php }?>
				<?php }?>
			</div>
		</section>
    <?php }?>
</div>