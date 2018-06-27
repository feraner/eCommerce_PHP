<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

$MId=(int)$_GET['MId'];
$UserId=$_SESSION['User']['UserId'];
?>
<script type="text/javascript">$(document).ready(function(){user_obj.products_init()});</script>
<div id="lib_user_products" class="clearfix">
	<?php
	if($MId){
		echo ly200::load_static('/static/js/plugin/lightbox/js/lightbox.min.js','/static/js/plugin/lightbox/css/lightbox.min.css');
		$row=str::str_code(db::get_one('user_message', "MId='$MId' and Module='products'"));
		if($row['IsReply']) db::update('user_message', "MId='$MId'", array('IsReply'=>0));
		$reply_row=str::str_code(db::get_all('user_message_reply', "MId='$MId'"));
		$proId=(int)$row['Subject'];
		$prod_row=db::get_one('products', "ProId='$proId'");
		$name=$prod_row['Name'.$c['lang']];
		$url=ly200::get_url($prod_row,'products');
		$img=ly200::get_size_img($prod_row['PicPath_0'], '240x240');
		?>
		<a href="javascript:javascript :history.back(-1);" class="user_back"><?=$c['lang_pack']['user']['productsTitle'];?></a>
		<div class="review_detail_box">
			<dl class="item">
				<dt class="fl">
					<a class="pic_box" href="<?=$url;?>" title="<?=$name;?>" target="_blank"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a>
					<div class="name"><?=$name;?></div>
				</dt>
				<dd class="reply">
					<div class="writer"><span><?=date('M d,Y H:i:s', $row['AccTime']);?></span></div>
					<p><?=$row['Content'];?></p>
					<p>
						<?php if($v['PicPath']){?>
							<a class="light_box_pic" target="_blank" href="<?=$row['PicPath'];?>"><img src="<?=$row['PicPath'];?>" /></a>
						<?php }?>
					</p>
				</dd>
				<?php
				if(count($reply_row)){
					foreach((array)$reply_row as $k=>$v){
				?>
				<dd class="reply <?=$v['UserId']?'':'mine';?>">
					<div class="writer"><span><?=date('M d,Y H:i:s', $v['AccTime']);?></span></div>
					<p><?=$v['Content'];?></p>
					<p>
						<?php if($v['PicPath']){?>
							<a class="light_box_pic" target="_blank" href="<?=$v['PicPath'];?>"><img src="<?=$v['PicPath'];?>" /></a>
						<?php }?>
					</p>
				</dd>
				<?php
					}
				}
				?>
				<dd>
					<form id="reply_form" class="reply_form user_form" name="reply_form"  method="post" enctype="multipart/form-data">
						<div class="reply_tips"><?=$c['lang_pack']['products']['reply'].' '.$c['lang_pack']['user']['content'];?></div>
						<div class="rows">
							<label><?=$c['lang_pack']['user']['content'];?>:</label>
							<span class="input"><textarea name="Content" placeholder="<?=$c['lang_pack']['user']['content'];?>" class="form_text" notnull=""></textarea></span>
							<div class="clear"></div>
						</div>
						<div class="rows">
							<label><?=$c['lang_pack']['user']['image'];?>:</label>
							<div class="input upload_box">
			                    <input class="upload_file" id="upload_file" type="file" name="PicPath" onchange="loadImg(this);" accept="image/gif,image/jpeg,image/png">
			                    <div id="pic_show" class="pic_box"></div>
			                </div>
			                <div class="submit">
			                	<input type="submit" class="submit_btn" name="submit_button" value="<?=$c['lang_pack']['user']['submit'];?>" />
			                </div>
							<div class="clear"></div>
						</div>
						<div class="clear"></div>
						<input type="hidden" name="MId" value="<?=$MId;?>" />
						<input type="hidden" name="UserId" value="<?=(int)$row['UserId'];?>" />
						<input type="hidden" name="JumpUrl" value="/account/products/" />
						<input type="hidden" name="do_action" value="user.reply_inbox" />
					</form>
				</dd>
			</dl>
		</div>
	<?php }else{ ?>
		<div id="user_heading" class="fl">
			<h2><?=$c['lang_pack']['user']['productsTitle'];?></h2>
		</div>
        <?php include('include/message_menu.php'); ?>
		<div class="clear"></div>
		<?php
		$page=(int)$_GET['page'];
		$page_count=10;
		$row=str::str_code(db::get_limit_page('user_message', "{$c['where']['user']} and Module='products'", '*', 'IsReply desc,MId desc', $page, $page_count));
		if($row[0]){
			$pro_ary=array();
			foreach($row[0] as $v){
				$pro_ary[]=(int)$v['Subject'];
			}
			$pro_ary=implode(',',$pro_ary);
			$pro_ary=db::get_all('products',"ProId in ($pro_ary)");
			$pro_new_ary=array();
			foreach($pro_ary as $v){
				$pro_new_ary[$v['ProId']]=$v;
			}
			unset($pro_ary);
			?>
			<div class="blank20"></div>
			<table class="review_table">
				<tr>
					<th class="fir" colspan="2"><?=$c['lang_pack']['cart']['products']; ?></th>
					<th><?=$c['lang_pack']['user']['status']; ?></th>
					<th class="tac" width="18%"><?=$c['lang_pack']['user']['action']; ?></th>
				</tr>
			<?php
			foreach((array)$row[0] as $k=>$v){
				$ProId=(int)$v['Subject'];
				$name=$pro_new_ary[$ProId]['Name'.$c['lang']];
				$img=ly200::get_size_img($pro_new_ary[$ProId]['PicPath_0'], '240x240');
				$url='/account/products/view'.sprintf('%04d', $v['MId']).'.html';

				?>
				<tr>
					<td width="87">
						<a class="pic_box" href="<?=ly200::get_url($pro_new_ary[$ProId],'products');?>" title="<?=$name;?>" target="_blank"><img src="<?=$img;?>" alt="<?=$name;?>" /><span></span></a>
					</td>
					<td width="35%">
						<a href="<?=ly200::get_url($pro_new_ary[$ProId],'products'); ?>" class="name" target="_blank" title="<?=$name;?>"><?=$name;?></a>
						<div class="sku"><?=$pro_new_ary[$ProId]['SKU'] ? $pro_new_ary[$ProId]['SKU'] : $pro_new_ary[(int)$v['Subject']]['Prefix'].$pro_new_ary[(int)$v['Subject']]['Number']; ?></div>
					</td>
					<td>
						<div class="date"><?=date('M d,Y H:i:s', $v['AccTime']);?></div>
						<div class="content"><?=$v['Content'];?></div>
					</td>
					<td class="tac last">
						<div class="user_action_down">
							<a href="<?=$url; ?>"><?=$c['lang_pack']['user']['view_more']; ?></a>
						</div>
					</td>
				</tr>
			<?php }?>
			</table>
			<div class="blank20"></div>
			<div id="turn_page"><?=ly200::turn_page_html($row[1], $row[2], $row[3], $query_string, $c['lang_pack']['user']['previous'], $c['lang_pack']['user']['next'], 3, '.html', $html=1);?></div>
		<?php } ?>
	<?php }?>
</div>