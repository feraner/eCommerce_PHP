<?php !isset($c) && exit();?>
<?php
$query_string=ly200::get_query_string(ly200::query_string('m, a, p, page'));

$g_page=(int)$_GET['page'];
$row=db::get_all('user_favorite f left join products p on f.ProId=p.ProId', $c['where']['user'], 'p.*, f.AccTime', 'FId desc', $g_page, $page_count);
?>
<script type="text/javascript">$(function(){user_obj.user_fav()});</script>
<div id="user">
	<?=html::mobile_crumb('<em><i></i></em><a href="/account/">'.$c['lang_pack']['mobile']['my_account'].'</a><em><i></i></em><a href="/account/favorite/">'.$c['lang_pack']['my_fav'].'</a>');?>
    <div class="user_favorite">
    	<?php
		if($row){
			$row_count=count($row);
		?>
		<div class="detail_prolist">
			<?php
			foreach($row as $k=>$v){
				$url=ly200::get_url($v, 'products');
				$img=ly200::get_size_img($v['PicPath_0'], '500x500');
				$name=$v['Name'.$c['lang']];
				$price_ary=cart::range_price_ext($v);
			?>
			<div class="item clean ui_border_b">
				<div class="img fav_img fl"><a href="<?=$url;?>"><img src="<?=$img;?>" alt="<?=$name;?>" /></a></div>
				<div class="info fav_info clean">
					<div class="name"><a href="<?=$url;?>"><?=$name;?></a></div>
					<div class="price"><?=cart::iconv_price($price_ary[0])?></div>
					<div class="del" data-proid="<?=$v['ProId'];?>"><?=$c['lang_pack']['mobile']['remove'];?></div>
				</div>
			</div>
			<?php }?>
		</div>
        <?php }else{?>
        	<div class="content_blank"><?=$c['lang_pack']['mobile']['no_fav_file'];?></div>
        <?php }?>
    </div>
    <div class="blank15"></div>
</div>