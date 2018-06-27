<?php !isset($c) && exit();?>
<?php
$review_cfg=str::json_data(db::get_value('config', "GroupId='products_show' and Variable='review'", 'Value'), 'decode');

$g_page=0;
$page_count=20;//显示数量

$where="p.ProId='{$ProId}' and p.ReId=0";
$review_cfg['display']==1 && $where.=" and p.Audit=1";
$review_row=str::str_code(db::get_limit_page('products_review p left join user u on p.UserId=u.UserId left join user_level l on u.Level=l.LId', $where, "p.*, u.FirstName, u.LastName, u.Level, l.Name{$c['lang']}, l.PicPath", 'p.RId desc', $g_page, $page_count));
$total_rating=$TotalRating;
$review_row[1] && $review_row[1]>$total_rating && $total_rating=$review_row[1];
?>
<?php if($a!='goods'){?>
	<div class="write_review">
		<a href="<?=ly200::get_url($products_row, 'write_review');?>" class="btn_write_review btn_global ReviewBgColor" rel="nofollow"><?=$c['lang_pack']['products']['writeReview'];?></a>
	</div>
	<div class="prod_info_divide"></div>
<?php } ?>
<section class="detail_desc detail_review">
	<div class="text">
		<div class="reviews_list" data-proid="<?=$ProId;?>" data-action="<?=$a;?>" data-number="0" data-page="1" data-total="<?=$review_row[3];?>"></div>
		<div class="prod_review_view clean">
			<?php if($review_cfg['range']==1 && !$_SESSION['User']['UserId']){?>
				<div class="review_sign"><?=$c['lang_pack']['products']['memberLogin'];?></div>
			<?php }?>
			<?php if($a=='goods' && $review_row[0]){?>
				<a class="customer_btn clearfix" href="<?=ly200::get_url($products_row, 'review');?>"><?=$c['lang_pack']['mobile']['all_reviews'];?></a>
			<?php }?>
		</div>
	</div>
</section>
<script type="text/javascript" src="<?="{$c['mobile']['tpl_dir']}js/review.js?v=1";?>"></script>
