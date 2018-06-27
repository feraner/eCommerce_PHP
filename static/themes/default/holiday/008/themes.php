<?php !isset($c) && exit();?>
<?php
$where='0';
for($i=0; $i<3; ++$i){
	$proid_obj[$i] && $where.=','.implode(',', $proid_obj[$i]);
}
$pro_ary=array();
$pro_row=str::str_code(db::get_all('products', "ProId in($where)", '*', 'ProId desc'));
foreach((array)$pro_row as $v) $pro_ary[$v['ProId']]=$v;

ob_start();
?>
<style>
#bodyer{background:url(<?=$theme_ary[0]['PicPath'];?>) no-repeat top center #006634;}
</style>
<div id="bodyer" class="full">
	<div class="main_top wide"<?=$theme_ary[0]['Url']?' onClick="window.open(\''.$theme_ary[0]['Url'].'\');"':'';?>></div>
	<div id="main_bg" class="full">
		<div id="main" class="wide">
			<div class="pro_list pro_list_0">
				<div class="list_head"><?=$theme_ary[1]['Title'];?></div>
				<div class="list_body">
					<div class="list_box fl">
						<a href="#"><img src="<?=$theme_ary[1]['PicPath'];?>" /></a>
					</div>
					<div class="list_menu fl">
						<?php
						$pro_len=count($proid_obj[0]);
						foreach((array)$proid_obj[0] as $k=>$v){
							$proid=$v;
							$url=ly200::get_url($pro_ary[$proid], 'products');
							$img=ly200::get_size_img($pro_ary[$proid]['PicPath_0'], '240x240');
							$name=$pro_ary[$proid]['Name'.$c['lang']];
						?>
						<dl class="item fl">
							<dt><?=str::str_echo($name, 45, 0, '..');?></dt>
							<dd><a href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /></a></dd>
						</dl>
						<?php }?>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="pro_list pro_list_1">
				<div class="list_head"><?=$theme_ary[2]['Title'];?></div>
				<div class="list_body">
					<div class="list_menu fl">
						<?php
						$pro_len=count($proid_obj[1]);
						foreach((array)$proid_obj[1] as $k=>$v){
							$proid=$v;
							$url=ly200::get_url($pro_ary[$proid], 'products');
							$img=ly200::get_size_img($pro_ary[$proid]['PicPath_0'], '240x240');
							$name=$pro_ary[$proid]['Name'.$c['lang']];
						?>
						<dl class="item fl">
							<dt><?=str::str_echo($name, 45, 0, '..');?></dt>
							<dd><a href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /></a></dd>
						</dl>
						<?php }?>
					</div>
					<div class="list_box fl">
						<a href="#"><img src="<?=$theme_ary[2]['PicPath'];?>" /></a>
					</div>
					<div class="clear"></div>
				</div>
			</div>
			<div class="pro_list pro_list_2">
				<div class="list_head"><?=$theme_ary[3]['Title'];?></div>
				<div class="list_body">
					<div class="list_box fl">
						<a href="#"><img src="<?=$theme_ary[3]['PicPath'];?>" /></a>
					</div>
					<div class="list_menu fl">
						<?php
						$pro_len=count($proid_obj[2]);
						foreach((array)$proid_obj[2] as $k=>$v){
							$proid=$v;
							$url=ly200::get_url($pro_ary[$proid], 'products');
							$img=ly200::get_size_img($pro_ary[$proid]['PicPath_0'], '240x240');
							$name=$pro_ary[$proid]['Name'.$c['lang']];
						?>
						<dl class="item fl">
							<dt><?=str::str_echo($name, 45, 0, '..');?></dt>
							<dd><a href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /></a></dd>
						</dl>
						<?php }?>
					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
$theme_content=ob_get_contents();
ob_end_clean();
?>