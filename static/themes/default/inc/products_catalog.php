<?php !isset($c) && exit();?>
<?php
if(!file::check_cache('products_catalog.html')){
	ob_start();

	$allcate_row=str::str_code(db::get_all('products_category', ' IsSoldOut=0', "CateId,UId,Category{$c['lang']},SubCateCount",  $c['my_order'].'CateId asc'));
	$allcate_ary=array();
	foreach((array)$allcate_row as $k=>$v){
		$allcate_ary[$v['UId']][]=$v;
	}
?>
	<div class="nav_menu">
		<div class="nav_title CategoryBgColor"><a href="/products/"><?=$c['lang_pack']['all_category'];?><b></b></a></div>
		<div class="nav_categories">
			<?php if(count($allcate_ary["0,"])){?>
			<ul>
				<?php 
				foreach((array)$allcate_ary["0,"] as $k=>$v){
					$data_ary=array();
					if(count($allcate_ary["0,{$v['CateId']},"])){
						foreach((array)$allcate_ary["0,{$v['CateId']},"] as $kk=>$vv){
							$data_ary[$kk]['text']=htmlspecialchars($vv['Category'.$c['lang']], ENT_QUOTES, 'UTF-8');
							$data_ary[$kk]['url']=ly200::get_url($vv);
							if(count($allcate_ary["{$vv['UId']}{$vv['CateId']},"])){
								$children=array();
								foreach((array)$allcate_ary["{$vv['UId']}{$vv['CateId']},"] as $kkk=>$vvv){
									$children[$kkk]['text']=htmlspecialchars($vvv['Category'.$c['lang']], ENT_QUOTES, 'UTF-8');
									$children[$kkk]['url']=ly200::get_url($vvv);
								}
								$data_ary[$kk]['children']=$children;
							}
						}
					}
					$data=str::json_data($data_ary);
				?>
					<li data='<?=$data;?>'>
						<h2>
							<a href="<?=ly200::get_url($v);?>" title="<?=$name=$v['Category'.$c['lang']];?>"><?=$name=$v['Category'.$c['lang']];?></a>
							<?php if(count($data_ary)){?><em class="NavArrowColor"></em><i></i><?php }?>
						</h2>
					</li>
				<?php }?>
			</ul>
			<?php }?>
		</div>
	</div>
	<ul class="nav_item">
		<?php
		$nav_row=db::get_value('config', "GroupId='themes' and Variable='NavData'", 'Value');
		$nav_data=str::json_data($nav_row, 'decode');
		foreach((array)$nav_data as $k=>$v){
			$nav=ly200::nav_style($v, 1);
			if(!$nav['Name']) continue;
		?>
		<li>
			<a href="<?=$nav['Url'];?>"<?=$nav['Target'];?>><?=$nav['Name'];?></a>
			<?php
			if($nav['Select'] && count($allcate_ary[$nav['UId']])){
				$navLen=count((array)$allcate_ary[$nav['UId']]);
			?>
			<dl class="<?=($navLen>5?'long':'').($v['DownWidth']?" down_width_{$v['DownWidth']}":'');?>">
				<?php
				foreach((array)$allcate_ary[$nav['UId']] as $kk=>$vv){
					$n=$vv['Category'.$c['lang']];
				?>
				<dd<?php if($navLen>5){?> class="<?=$kk%2==0?'fl':'fr';?>"<?php }?>><a href="<?=ly200::get_url($vv, 'products_category');?>" title="<?=$n;?>"><?=$n;?></a></dd>
				<?php }?>
			</dl>
			<?php }?>
		</li>
		<?php }?>
	</ul>
<?php 
	$cache_contents=ob_get_contents();
	ob_end_clean();
	file::write_file(ly200::get_cache_path($c['theme'], 0), 'products_catalog.html', $cache_contents);
}
include(ly200::get_cache_path($c['theme']).'products_catalog.html');
?>
