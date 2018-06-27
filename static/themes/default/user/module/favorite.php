<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

$g_page=(int)$_GET['page'];
$page_count=50;//显示数量
$row=str::str_code(db::get_limit_page('user_favorite f left join products p on f.ProId=p.ProId', $c['where']['user'], 'p.*, f.AccTime', 'FId desc', $g_page, $page_count));

$query_string=ly200::query_string('m,a,page');
?>
<script>$(document).ready(function(){user_obj.user_index_init()});</script>
<div id="user_heading">
	<h2><?=$c['lang_pack']['user']['favoriteTitle'];?></h2>
</div>
<div id="lib_user_favorite" class="index_pro_list clearfix">
	<div class="user_page_pro">
		<?php
		if($row[1]){
			$row_count=count($row[0]);
			foreach((array)$row[0] as $k=>$v){
				$url=ly200::get_url($v, 'products');
				$img=ly200::get_size_img($v['PicPath_0'], '240x240');
				$name=$v['Name'.$c['lang']];
				$like=(int)db::get_row_count('user_favorite', "ProId='{$v['ProId']}'");
				$price_ary=cart::range_price_ext($v);
				$rating=($v['IsDefaultReview'] && $v['DefaultReviewRating'] && (float)$v['Rating']==0)?(int)$v['DefaultReviewRating']:(int)$v['Rating'];
				$total_rating=($v['IsDefaultReview'] && $v['DefaultReviewTotalRating'] && (int)$v['TotalRating']==0)?$v['DefaultReviewTotalRating']:$v['TotalRating'];
		?>
		<dl class="pro_item fl<?=$k%4==0?' first':'';?>">
			<dt>
				<a class="pic_box" href="<?=$url;?>" title="<?=$name;?>" target="_blank"><img src="<?=$img;?>" /><span></span></a>
			</dt>
			<dd class="name"><a href="<?=$url;?>" title="<?=$name;?>" target="_blank"><?=mb_substr($name, 0, 45);?>..</a></dd>
			<dd class="price">
				<em class="currency_data PriceColor"></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>" keyid="<?=$v['ProId'];?>"></span>
				<?php if($c['config']['products_show']['Config']['price']){?><del><em class="currency_data"></em><span class="price_data" data="<?=$v['Price_0'];?>"></span></del><?php }?>
			</dd>
			<dd class="pro_review"><?php if($c['config']['products_show']['Config']['review'] && $total_rating){?><span class="star star_s<?=$rating;?>"></span><a class="review_count" href="<?=$url;?>#review_box" target="_blank"><?=$total_rating;?> <?=$c['lang_pack']['user']['reviewCount'];?></a><?php }?></dd>
			<dd class="pro_view">
				<a class="pro_btn view add_cart AddtoCartBgColor" href="javascript:;" data="<?=$v['ProId']; ?>"><?=$c['lang_pack']['user']['view'];?></a>
				<a class="pro_btn remove" href="/account/favorite/remove<?=sprintf('%04d', $v['ProId']);?>.html"><?=$c['lang_pack']['user']['remove'];?></a>
			</dd>
		</dl>
		<?php if($k%4==3){ ?><div class="clear"></div><?php } ?>
		<?php }?>
	</div>
	<?php /*
	<div id="turn_page"><?=ly200::turn_page_html($row[1], $row[2], $row[3], $query_string, $c['lang_pack']['user']['previous'], $c['lang_pack']['user']['next'], 3, '.html', $html=1);?></div>*/ ?>
	<?php
	}else{
		echo '<div class="tips">'.$c['lang_pack']['user']['noFavorite'].'</div>';
	}?>
	<div class="blank20"></div>
	<div class="user_line"></div>
	<div class="blank20"></div>
	<div class="user_ind_ptype">
		<a href="javascript:;" class="cur">You may also like</a>
		<span></span>
		<a href="javascript:;">Recommendations</a>
	</div>
	<div class="user_page_pro">
		<?php 
			$w_ary=array('1','IsHot=1');
			foreach((array)$w_ary as $key=>$val){
				?>
				<div class="pro_list clearfix" <?=$key==0 ? 'style="display:block;"' : ''; ?>>
					<?php
					$products_list_row=str::str_code(db::get_limit('products', $val.$c['where']['products'], '*', $c['my_order'].'ProId desc', 0, 8));
					$key == 0 && $products_list_row=orders::you_may_also_like(1,8);
					foreach((array)$products_list_row as $k=>$v){
						$is_promition=($v['IsPromotion'] && $v['StartTime']<$c['time'] && $c['time']<$v['EndTime'])?1:0;
						$url=ly200::get_url($v, 'products');
						$img=ly200::get_size_img($v['PicPath_0'], '240x240');
						$name=$v['Name'.$c['lang']];
						$price_ary=cart::range_price_ext($v);
						$price_0=$v["Price_{$is_promition}"];
						?>
						<dl class="pro_item fl<?=$k%4==0?' first':'';?>">
							<dt><a class="pic_box" href="<?=$url;?>" title="<?=$name;?>"><img src="<?=$img;?>" /><span></span></a></dt>
							<dd class="name"><a href="<?=$url;?>" title="<?=$name;?>"><?=str::str_echo($name, 45, 0, '..');?></a></dd>
							<dd class="price">
								<em class="currency_data PriceColor"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data PriceColor" data="<?=$price_ary[0];?>" keyid="<?=$v['ProId'];?>"><?=cart::iconv_price($price_ary[0], 2);?></span>
								<?php if($c['config']['products_show']['Config']['price']){?><del><em class="currency_data"><?=$_SESSION['Currency']['Symbol'];?></em><span class="price_data" data="<?=$price_0;?>"><?=cart::iconv_price($price_0, 2);?></span></del><?php }?>
							</dd>
						</dl>
						<?php if($k%4==3){ ?><div class="clear"></div><?php } ?>
					<?php }?>
				</div>
		<?php }?>
	</div>
</div>