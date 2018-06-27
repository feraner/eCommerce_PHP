<?php
include('../../../../../inc/global.php');

//设置语言版
$config_row=db::get_all('config', "GroupId='global' and (Variable='Language' || Variable='LanguageDefault')");
foreach($config_row as $v){
	if("{$v['GroupId']}|{$v['Variable']}"=='global|Language'){
		$c['config'][$v['GroupId']][$v['Variable']]=explode(',', $v['Value']);
	}else{
		$c['config'][$v['GroupId']][$v['Variable']]=$v['Value'];
	}
}
$host_ary=@explode('.', $_SERVER['HTTP_HOST']);
$c['lang_oth']=str_replace('-', '_', array_shift($host_ary));
$c['lang']='_'.(@in_array($c['lang_oth'], $c['config']['global']['Language'])?$c['lang_oth']:$c['config']['global']['LanguageDefault']);

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