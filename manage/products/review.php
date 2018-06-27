<?php !isset($c) && exit();?>
<?php
manage::check_permit('products', 1, array('a'=>'review'));//检查权限

$review_cfg=str::json_data(db::get_value('config', "GroupId='products_show' and Variable='review'", 'Value'), 'decode');

$permit_ary=array(
	'edit'	=>	manage::check_permit('products', 0, array('a'=>'review', 'd'=>'edit')),
	'del'	=>	manage::check_permit('products', 0, array('a'=>'review', 'd'=>'del'))
);
?>
<div class="r_nav">
	<h1>{/module.products.review/}</h1>
	<div class="turn_page"></div>
	<?php if($c['manage']['do']=='index'){?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<input type="hidden" name="m" value="products" />
				<input type="hidden" name="a" value="review" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php }?>
</div>
<div id="review" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
	?>
		<script type="text/javascript">$(document).ready(function(){products_obj.review_init();});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['del']){?><td width="4%"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
					<td width="20%" nowrap="nowrap">{/products.product/}{/products.name/}</td>
					<td width="10%" nowrap="nowrap">{/module.orders.module_name/}</td>
					<td width="15%" nowrap="nowrap">{/products.review.customer_name/}</td>
					<td width="10%" nowrap="nowrap">{/products.review.rating/}</td>
					<?php if($review_cfg['display']==1){?><td width="8%" nowrap="nowrap">{/products.review.audit/}</td><?php }?>
					<td width="10%" nowrap="nowrap">{/products.review.ip/}</td>
					<td width="10%" nowrap="nowrap">{/products.review.time/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="8%" class="last" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$Keyword=$_GET['Keyword'];
				$where='p.ReId=0';//条件
				$page_count=30;//显示数量
				$Keyword && $where.=" and p.ProId in(select ProId from products where Name{$c['manage']['web_lang']} like '%$Keyword%')";
				$review_row=str::str_code(db::get_limit_page('products_review p left join orders o on p.OrderId=o.OrderId', $where, '*, o.OId', 'p.RId desc', (int)$_GET['page'], $page_count));
				$w="-1";
				$products_list=array();
				foreach($review_row[0] as $v){$w.=",{$v['ProId']}";}
				$products_row=db::get_all('products', "ProId in($w)", "ProId,Name{$c['manage']['web_lang']}");
				foreach($products_row as $v){$products_list[$v['ProId']]=str::str_code($v);}
				
				$i=1;
				foreach($review_row[0] as $v){
					$products_row=$products_list[$v['ProId']];
					$url=$products_row?ly200::get_url($products_row, 'products', $c['manage']['web_lang']):'javascript:;';
					$name=$products_row?$products_row['Name'.$c['manage']['web_lang']]:'N/A';
				?>
					<tr>
						<?php if($permit_ary['del']){?><td><input type="checkbox" name="select" value="<?=$v['RId'];?>" class="va_m" /></td><?php }?>
						<td><a href="<?=$url;?>" target="_blank"><?=$name;?></a></td>
						<td><a href="./?m=orders&a=orders&d=view&OrderId=<?=$v['OrderId'];?>"><?=$v['OId'];?></a></td>
						<td><?=$v['CustomerName'];?></td>
						<td><span class="star star_s<?=$v['Rating'];?>"></span></td>
						<?php if($review_cfg['display']==1){?><td><?=$v['Audit']?'{/products.review.through/}':'';?></td><?php }?>
						<td><?=$v['Ip'];?></td>
						<td><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td class="last">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=products&a=review&d=reply&RId=<?=$v['RId'];?>" label="{/global.view/}"><img src="/static/ico/search.png" alt="{/global.view/}" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=products.review_del&RId=<?=$v['RId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" alt="{/global.del/}" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($review_row[1], $review_row[2], $review_row[3], '?'.ly200::query_string('page').'&page=');?></div>
	<?php
	}else{
		$RId=(int)$_GET['RId'];
		$review_row=str::str_code(db::get_one('products_review', "RId='$RId'"));
		!$review_row && js::location('./?m=products&a=review');
		$products_row=db::get_one('products', "ProId='{$review_row['ProId']}'", "ProId,Name{$c['manage']['web_lang']}");
		$products_row=is_array($products_row)?str::str_code($products_row):array("Name{$c['manage']['web_lang']}"=>'N/A');
		$rating_ary=explode(',', $review_row['Assess']);
	?>
		<script type="text/javascript">$(document).ready(function(){products_obj.review_reply_init();});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/products.product/}{/products.name/}</label>
				<span class="input"><a href="<?=$products_row['ProId']?ly200::get_url($products_row, 'products', $c['manage']['web_lang']):'javascript:;';?>" target="_blank"><?=$products_row['Name'.$c['manage']['web_lang']];?></a></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.review.customer_name/}</label>
				<span class="input"><?=$review_row['CustomerName'];?> (<?=str::str_code(db::get_value('user', "UserId='{$review_row['UserId']}'", 'Email'));?>)</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.review.rating/}</label>
				<span class="input">
					<span class="star star_b<?=$review_row['Rating'];?>"></span>
					<?php /*
					<div class="blank12"></div>
					<ul>
						<?php for($i=0; $i<5; ++$i){?>
						<li>{/products.review.rating_ary.<?=$i;?>/}: <span class="star star_s<?=$rating_ary[$i];?>"></span></li>
						<?php }?>
					</ul>
					*/?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.picture/}</label>
				<span class="input">
					<?php
					for($i=0; $i<3; ++$i){
						if(!$review_row['PicPath_'.$i] || !is_file($c['root_path'].$review_row['PicPath_'.$i])) continue;
					?>
						<div><img src="<?=$review_row['PicPath_'.$i];?>" /></div>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/module.content.module_name/}</label>
				<span class="input"><?=$review_row['Content'];?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.review.comment/}</label>
				<span class="input">{/products.review.agree/}<span class="fc_red">(<?=$review_row['Agree'];?>)</span> / {/products.review.oppose/}<span class="fc_red">(<?=$review_row['Oppose'];?>)</span></span>
				<div class="clear"></div>
			</div>
			<?php
			$order_row=db::get_one('orders', "OrderId='{$review_row['OrderId']}'");
			if($order_row){
				?>
				<div class="rows">
					<label>{/module.orders.module_name/}</label>
					<span class="input"><a href="./?m=orders&a=orders&d=view&OrderId=<?=$review_row['OrderId'];?>"><?=$order_row['OId'];?></a></span>
					<div class="clear"></div>
				</div>
			<?php }?>
			<?php
			if($review_cfg['display']==1){
			?>
				<div class="rows">
					<label>{/products.review.through/}</label>
					<span class="input"><input type="checkbox" name="Audit" value="1"<?=$review_row['Audit']?' checked':'';?> /><span class="tool_tips_ico" content="{/products.review.audit_notes/}"></span></span>
					<div class="clear"></div>
				</div>
			<?php }?>
			<div class="rows">
				<label>{/products.review.ip/}</label>
				<span class="input"><?=$review_row['Ip'].' 【'.ly200::ip($review_row['Ip']).'】';?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.review.time/}</label>
				<span class="input"><?=date('Y-m-d H:i:s', $review_row['AccTime']);?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/products.review.reply/}</label>
				<span class="input">
					<?php
					$reply_row=str::str_code(db::get_all('products_review', "ProId='{$review_row['ProId']}' and ReId='{$review_row['RId']}'", '*', 'RId asc'));
					$reply_len=count($reply_row);
					if($reply_len){
					?>
					<table border="0" cellpadding="5" cellspacing="0" class="relation_box">
						<thead>
							<tr>
								<td width="15%">{/products.review.customer_name/}</td>
								<td width="60%">{/products.review.content/}</td>
								<td width="5%">{/products.review.audit/}</td>
								<td width="20%">{/products.review.time/}</td>
							</tr>
						</thead>
						<tbody>
							<?php foreach($reply_row as $v){?>
							<tr>
								<td><?=($v['CustomerName']=='Manager' && !$v['UserId'])?'{/manage.manage.manager/}':$v['CustomerName'];?></td>
								<td align="left"><?=$v['Content'];?></td>
								<td><input type="checkbox" name="ReAudit[<?=$v['RId'];?>]" value="1"<?=$v['Audit']?' checked':'';?> /></td>
								<td><?=date('Y-m-d H:i:s', $v['AccTime']);;?></td>
							</tr>
							<?php }?>
						</tbody>
					</table>
					<?php }?>
					<div class="textarea_holder">
					{/manage.manage.manager/}{/products.review.reply/}:<br />
					<textarea class="default" name="ReviewComment"></textarea>
					</div>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=products&a=review" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" id="RId" name="RId" value="<?=$RId;?>" />
			<input type="hidden" name="ProId" value="<?=$review_row['ProId'];?>" />
			<input type="hidden" name="do_action" value="products.review_reply" />
		</form>
	<?php }?>
</div>