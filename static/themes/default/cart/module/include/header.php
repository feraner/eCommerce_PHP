<?php !isset($c) && exit();?>
<?php
$logo_path=str::str_code(db::get_value('config', "GroupId='print' and Variable='LogoPath'", 'Value'));
?>
<style>
.cart_header .step>a.current{color:<?=$style_data['FontColor'];?>;}
.cart_header .step>a.current>b{background-color:<?=$style_data['FontColor'];?>;}
.cart_header .step>a.click:hover{color:<?=$style_data['FontColor'];?>;}
.cart_header .step>a.click:hover>b{background-color:<?=$style_data['FontColor'];?>;}
</style>
<div class="cart_header">
	<div class="logo fl"><h1><a href="/"><img src="<?=$logo_path?$logo_path:$c['config']['global']['LogoPath'];?>" alt="<?=$c['config']['global']['SiteName'];?>" /></a></h1></div>
	<div class="step fr">
		<?php
		for($i=0; $i<3; ++$i){
			$url='javascript:;';
			if($i==0 && ($a=='checkout' || $a=='buynow')) $url='/cart/';
		?>
			<a href="<?=$url;?>" class="step_<?=$i.($i==1?' current':'');?><?=$url!='javascript:;'?' click':'';?>"><b><?=$i+1;?></b><?=$c['lang_pack']['cart']['step_ary'][$i];?></a>
		<?php }?>
	</div>
</div>