<?php !isset($c) && exit();?>
<?php
ob_start();
?>
<style>
#bodyer .main_top{background:url(<?=$theme_ary[0]['PicPath'];?>) no-repeat top center;}
.banner_list .list_head{background:url(<?=$theme_ary[1]['PicPath'];?>) no-repeat center center;}
.pro_list .list_head{background:url(<?=$theme_ary[8]['PicPath'];?>) no-repeat center center;}
</style>
<div id="bodyer" class="full">
	<div class="main_top full"<?=$theme_ary[0]['Url']?' onClick="window.open(\''.$theme_ary[0]['Url'].'\');"':'';?>></div>
	<div class="main wide">
		<div class="banner_list">
			<div class="list_head"></div>
			<div class="list_body">
				<div class="t1 fl"><a href="<?=$theme_ary[2]['Url'];?>" title="<?=$theme_ary[2]['Title'];?>"><img src="<?=$theme_ary[2]['PicPath'];?>" alt="<?=$theme_ary[2]['Title'];?>" /></a></div>
				<div class="fl">
					<div class="t2"><a href="<?=$theme_ary[3]['Url'];?>" title="<?=$theme_ary[3]['Title'];?>"><img src="<?=$theme_ary[3]['PicPath'];?>" alt="<?=$theme_ary[3]['Title'];?>" /></a></div>
					<div class="t2"><a href="<?=$theme_ary[4]['Url'];?>" title="<?=$theme_ary[4]['Title'];?>"><img src="<?=$theme_ary[4]['PicPath'];?>" alt="<?=$theme_ary[4]['Title'];?>" /></a></div>
				</div>
				<div class="clear"></div>
				<div class="fl">
					<div class="t2"><a href="<?=$theme_ary[5]['Url'];?>" title="<?=$theme_ary[5]['Title'];?>"><img src="<?=$theme_ary[5]['PicPath'];?>" alt="<?=$theme_ary[5]['Title'];?>" /></a></div>
					<div class="t2"><a href="<?=$theme_ary[6]['Url'];?>" title="<?=$theme_ary[6]['Title'];?>"><img src="<?=$theme_ary[6]['PicPath'];?>" alt="<?=$theme_ary[6]['Title'];?>" /></a></div>
				</div>
				<div class="t1 fl"><a href="<?=$theme_ary[7]['Url'];?>" title="<?=$theme_ary[7]['Title'];?>"><img src="<?=$theme_ary[7]['PicPath'];?>" alt="<?=$theme_ary[7]['Title'];?>" /></a></div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
	<div class="main_bottom full">
		<div class="wide">
			<div class="pro_list">
				<div class="list_head"></div>
				<div class="list_menu">
					<?php
					$cate_row=str::str_code(db::get_all('products_category', 'UId="0,"', '*',  $c['my_order'].'CateId asc'));
					foreach((array)$cate_row as $k=>$v){
					?>
					<a href="#"<?=$k==0?' class="current"':'';?>><?=$v['Category'.$c['lang']];?></a>
					<?php }?>
				</div>
				<div class="list_body">
					<?php
					foreach((array)$cate_row as $k=>$v){
					?>
					<div style="display:<?=$k==0?'block':'none';?>;">
						<?php
						$where=category::get_search_where_by_CateId($v['CateId'], 'products_category');
						$pro_row=str::str_code(db::get_limit('products', $where.$c['pro_where'], '*',  $c['my_order'].'ProId desc', 0, 10));
						foreach((array)$pro_row as $k=>$v){
							$url=ly200::get_url($v, 'products');
							$img=ly200::get_size_img($v['PicPath_0'], '240x240');
							$name=$v['Name'.$c['lang']];
							$price_0=(float)$v['Price_0'];
							$price_ary=cart::range_price_ext($v);
							$discount=($price_0?($price_0-$price_ary[0]):0)/((float)$price_0?$price_0:1)*100;
							$discount=sprintf('%01.1f', $discount);
						?>
						<dl class="pro_item fl">
							<dt><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a></dt>
							<dd class="pro_name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
							<dd class="pro_price"><em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>"></span></dd>
							<dd class="pro_view"><?=$discount;?>% off</dd>
						</dl>
						<?php
							echo (($k+1)%5==0 || ($k+1)==count($pro_row))?'<div class="blank25"></div>':'';
						}?>
					</div>
					<?php }?>
				</div>
			</div>
		</div>
	</div>
</div>
<?=ly200::load_static("/static/themes/default/holiday/{$theme}/js/template.js");?>
<?php
$theme_content=ob_get_contents();
ob_end_clean();
?>