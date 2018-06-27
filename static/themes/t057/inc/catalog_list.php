<?php !isset($c) && exit();?>
<?php
if(!file::check_cache('catalog_list.html')){
	ob_start();

	if(!$allcate_ary){
		$allcate_row=str::str_code(db::get_all('products_category', 'IsSoldOut=0', '*',  $c['my_order'].'CateId asc'));
		$allcate_ary=array();
		foreach((array)$allcate_row as $k=>$v){
			$allcate_ary[$v['UId']][]=$v;
		}
	}
	!is_array($UId_ary) && $UId_ary=array();
?>
	<div class="side_category sidebar">
		<div class="cate_title b_title"><?=$c['lang_pack']['prodCategory'];?></div>
		<div class="cate_menu b_main">
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
							<?php if(count($data_ary)){?><em class="NavArrowColor"></em><?php }?>
						</h2>
					</li>
				<?php }?>
			</ul>
			<?php }?>
		</div>
	</div>
<?php 
	$cache_contents=ob_get_contents();
	ob_end_clean();
	file::write_file(ly200::get_cache_path($c['theme'], 0), 'catalog_list.html', $cache_contents);
}
include(ly200::get_cache_path($c['theme']).'catalog_list.html');
?>

