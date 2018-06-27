<?php !isset($c) && exit();?>
<div class="prod_description">
	<ul class="pd_title">
		<li class="current"><span data="0"><?=$c['lang_pack']['products']['description'];?></span></li>
		<?php foreach((array)$tab_row as $k=>$v){?>
			<li><span data="<?=$k+1;?>"><?=$v['TabName'];?></span></li>
		<?php }?>
	</ul>
	<div class="pd_content editor_txt">
		<div class="desc" data-number="0">
			<?php
			if($attr_ary['Common']){
				$all_value_ary=$attrid=array();
				foreach($attr_ary['Common'] as $v){ $attrid[]=$v['AttrId']; }
				$attrid_list=implode(',', $attrid);
				!$attrid_list && $attrid_list=0;
				$value_row=str::str_code(db::get_all('products_attribute_value', "AttrId in ($attrid_list)", '*', $c['my_order'].'VId asc')); //属性选项
				foreach($value_row as $v){ $all_value_ary[$v['AttrId']][$v['VId']]=$v; }
			?>
				<div class="item_specifics">
					<div class="title"><?=$c['lang_pack']['products']['specifics'];?></div>
					<?php
					$item = 0;
					foreach((array)$attr_ary['Common'] as $k=>$v){
						if(!$v || !$v['Name'.$c['lang']] || ($v['Type']==1 && !$selected_ary['Id'][$v['AttrId']]) || ($v['Type']==0 && !$selected_ary['Value'][$v['AttrId']])) continue;
						$item++;
					?>
						<span>
							<strong><?=$v['Name'.$c['lang']];?>:</strong>
							<?php
							if($v['Type']==1 && is_array($all_value_ary[$v['AttrId']])){
								$i=0;
								foreach($all_value_ary[$v['AttrId']] as $k2=>$v2){
									if(in_array($v2['VId'], $selected_ary['Id'][$v['AttrId']])){
										echo ($i?', ':'').$v2['Value'.$c['lang']];
										++$i;
									}
								}
							}else echo stripslashes($selected_ary['Value'][$v['AttrId']]);
							?>
						</span>
					<?php }?>
				</div>
			<?php }?>
			<?php if(!$item){ ?>
				<script>
					$('.item_specifics').remove();
				</script>
			<?php } ?>
			<?=str::str_code($products_description_row['Description'.$c['lang']], 'htmlspecialchars_decode');?>
		</div>
		<?php foreach((array)$tab_row as $k=>$v){?>
			<div class="desc hide" data-number="<?=$k+1;?>"><?=str::str_code($v['Tab'], 'htmlspecialchars_decode');?></div>
		<?php }?>
	</div>
</div>