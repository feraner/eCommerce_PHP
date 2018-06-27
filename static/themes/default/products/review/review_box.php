<?php !isset($c) && exit();?>
<?php
$review_cfg=str::json_data(db::get_value('config', "GroupId='products_show' and Variable='review'", 'Value'), 'decode');

if($a=='goods'){
	$g_page=0;
	$page_count=4;//显示数量
}else{
	$g_page=(int)$_GET['page'];
	$page_count=10;//显示数量
}
$where="p.ProId='{$ProId}' and p.ReId=0";
$review_cfg['display']==1 && $where.=" and p.Audit=1";
$review_row=str::str_code(db::get_limit_page('products_review p left join user u on p.UserId=u.UserId left join user_level l on u.Level=l.LId', $where, "p.*, u.FirstName, u.LastName, u.Level, l.Name{$c['lang']}, l.PicPath", 'p.RId desc', $g_page, $page_count));
$total_rating=$TotalRating;
$review_row[1] && $review_row[1]>$total_rating && $total_rating=$review_row[1];

//整理各种评级情况
$all_review_row=db::get_all('products_review', "ProId='{$ProId}' and ReId=0", 'Rating');
$all_rating_ary=array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0);
$all_review_count=count($all_review_row);
foreach($all_review_row as $v){
	$all_rating_ary[$v['Rating']]+=1;
}
?>
<script type="text/javascript">
$(function(){
	$('.pic_list>a').lightBox();
});
</script>
<div id="review_box">
	<div class="widget prod_write_review<?=$a=='review'?' prod_write_review_side':'';?>">
		<div class="review_title"><span><?=$c['lang_pack']['products']['customerReviews'];?></span></div>
		<div class="review_main">
			<div class="review_main_box average_rating">
				<h6><?=$c['lang_pack']['products']['averageRating'];?>:</h6>
				<?php /*<span class="star star_b<?=$total_rating?$Rating:5;?>"></span>*/?>
				<?=html::review_star($total_rating?$Rating:5);?>
				<p><?php if($total_rating){?><strong><?=$Rating;?></strong><span class="review_nums">(<?=str_replace('%TotalRating%', $total_rating, $c['lang_pack']['products']['basedOn']);?>)</span><?php }?></p>
				<?php if($IsTuan==1){//来自团购详情页?>
					<a href="<?=ly200::get_url($products_row, 'write_review');?>" class="write_review_btn ReviewBgColor" rel="nofollow"><?=$c['lang_pack']['products']['writeReview'];?></a>
				<?php }?>
			</div>
			<div class="review_main_box review_histogram">
				<ul class="histogram_list">
					<?php
					for($i=5; $i>0; --$i){
						$percent=(float)substr(sprintf('%01.3f', $all_rating_ary[$i]/$all_review_count), 0, -1)*100;
					?>
						<li>
							<a href="javascirpt:;" data-rating="<?=$i;?>"><span class="name"><?=$i;?> star</span><span class="size_base"><span class="meter" style="width:<?=$percent;?>%;"></span></span><span class="count"><?=$percent;?>%</span></a>
						</li>
					<?php }?>
				</ul>
			</div>
			<div class="review_main_box review_write">
				<p><?=$c['lang_pack']['review_share']?></p>
				<div><a href="<?=ly200::get_url($products_row, 'write_review');?>" class="write_review_btn ReviewBgColor" rel="nofollow"><?=$c['lang_pack']['products']['writeReview'];?></a></div>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<?php if($review_row[0]){?>
		<div class="widget prod_recent_review">
			<div class="reviews_list" data-proid="<?=$ProId;?>" data-action="<?=$a;?>" data-rating="0" data-number="0" data-page="1" data-total="<?=$review_row[3];?>"></div>
		</div>
	<?php }?>
	<div class="prod_review_view">
		<?php if($review_cfg['range']==1 && !$_SESSION['User']['UserId']){?>
			<div class="review_sign"><?=$c['lang_pack']['products']['memberLogin'];?></div>
		<?php }?>
		<div class="blank12"></div>
	</div>
</div>