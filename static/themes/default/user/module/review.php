<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

$RId=(int)$_GET['RId'];
$g_page=(int)$_GET['page'];

$query_string=ly200::query_string('m,a,page');
?>
<div id="lib_user_review">
	<?php if($RId){ 
		$review_row=str::str_code(db::get_one('products_review', $c['where']['user']." and RId='{$RId}'", '*'));
		$ProId=$review_row['ProId'];
		$pro_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
		$url=ly200::get_url($pro_row, 'products');
		$img=ly200::get_size_img($pro_row['PicPath_0'], '168x168');
		$name=$pro_row['Name'.$c['lang']];
		$reply_row=str::str_code(db::get_all('products_review', "ProId='{$ProId}' and ReId='{$review_row['RId']}' and Audit=1"));
		$reply_len=count($reply_row);
		?>
		<a href="javascript:javascript :history.back(-1);" class="user_back"><?=$c['lang_pack']['user']['reviewTitle'];?></a>
		<div class="review_detail_box">
			<dl class="item">
				<dt class="fl">
					<a class="pic_box" href="<?=$url;?>" title="<?=$name;?>" target="_blank"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a>
					<div class="name"><?=$name;?></div>
				</dt>
				<dd class="reply">
					<div class="writer"><span class="star star_s<?=$review_row['Rating'];?>"></span><cite> </cite><?=$review_row['Title'];?><span class="time"><?=date('M d,Y H:i:s', $review_row['AccTime']);?></span></div>
					<p><?=$review_row['Content'];?></p>
				</dd>
				<?php
				if($reply_len){
					foreach((array)$reply_row as $v){
				?>
				<dd class="reply <?=$v['UserId'] && $v['UserId']==$_SESSION['User']['UserId'] ? '' : 'mine'; ?>">
					<div class="writer"><?php if($v['CustomerName']){ ?><?=$c['lang_pack']['user']['by'];?> <cite class="replier"><?=$v['CustomerName'];?></cite><?php } ?> <span><?=date('M d,Y H:i:s', $v['AccTime']);?></span></div>
					<p><?=$v['Content'];?></p>
				</dd>
				<?php
					}
				}
				?>
			</dl>
		</div>
	<?php }else{ 
		$page_count=10;
		$row=str::str_code(db::get_limit_page('products_review', $c['where']['user']." and ReId=0", '*', 'RId desc', $g_page, $page_count));
		if($row[0]){
		?>
		<div id="user_heading">
			<h2><?=$c['lang_pack']['user']['reviewTitle'];?></h2>
		</div>
		<table class="review_table">
			<tr>
				<th class="fir" colspan="2"><?=$c['lang_pack']['cart']['products']; ?></th>
				<th><?=$c['lang_pack']['user']['status']; ?></th>
				<th class="tac" width="18%"><?=$c['lang_pack']['user']['action']; ?></th>
			</tr>
		<?php
		foreach((array)$row[0] as $k=>$v){
			$ProId=$v['ProId'];
			$pro_row=str::str_code(db::get_one('products', "ProId='$ProId'"));
			$url=ly200::get_url($pro_row, 'products');
			$img=ly200::get_size_img($pro_row['PicPath_0'], '168x168');
			$name=$pro_row['Name'.$c['lang']];
			?>
			<tr>
				<td width="87">
					<a class="pic_box" href="<?=$url;?>" title="<?=$name;?>" target="_blank"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a>
				</td>
				<td width="35%">
					<a href="<?=$url; ?>" class="name" title="<?=$name;?>"><?=$name;?></a>
					<div class="sku"><?=$pro_row['SKU'] ? $pro_row['SKU'] : $pro_row['Prefix'].$pro_row['Number']; ?></div>
				</td>
				<td>
					<div class="date"><?=date('M d,Y H:i:s', $v['AccTime']);?></div>
					<div class="content"><?=$v['Content'];?></div>
				</td>
				<td class="tac last">
					<div class="user_action_down">
						<a href="/account/review/?RId=<?=$v['RId']; ?>"><?=$c['lang_pack']['user']['view_more']; ?></a>
					</div>
				</td>
			</tr>
		<?php }?>
		</table>
		<div class="blank20"></div>
		<div id="turn_page"><?=ly200::turn_page_html($row[1], $row[2], $row[3], $query_string, $c['lang_pack']['user']['previous'], $c['lang_pack']['user']['next'], 3, '.html', $html=1);?></div>
		<?php } ?>
	<?php } ?>
</div>