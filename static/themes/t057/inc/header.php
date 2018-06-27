<?php !isset($c) && exit();?>
<?php include("{$c['static_path']}inc/header.php");?>
<div id="header" class="FontBgColor">
    <?php /*<div class="top">
        <div class="wide">
            <ul class="crossn top_info clean">
				<li class="fl"><?=$c['lang_pack']['welcomeTitle'];?></li>
				<li class="fl sign"><?php include("{$c['static_path']}inc/sign_in.php");?></li>
				<?php if(($c['FunVersion']>1 && count($c['config']['global']['Language'])>1) || ($c['config']['translate']['IsTranslate']==1 && count($c['config']['translate']['TranLangs']))){?><li class="block fr drop"><?php include("{$c['static_path']}inc/language.php");?></li><?php }?>
				
				<?php if($c['FunVersion']){?><li class="block fr border_r drop"><?php include("{$c['static_path']}inc/currency.php");?></li><?php }?>
				<?php if($_SESSION['User']['UserId']){ ?>
                <li class="block fr border_r"><a href="/account/favorite/"><?=$c['lang_pack']['wishList'];?></a></li>
				<li class="block fr border_r"><a href="/account/"><?=$c['lang_pack']['my_account'];?></a></li>
				<?php } ?>
			</ul>
        </div>
    </div>*/ ?>
	<div class="wide">
		<div class="logo fl"><h1><a href="/"><img src="<?=$c['config']['global']['LogoPath'];?>" alt="<?=$c['config']['global']['SiteName'];?>" /></a></h1></div>
		<div class="search ajax_search fr">
			<?php
			$procate_row=str::str_code(db::get_all('products_category', 'UId="0,"', '*',  $c['my_order'].'CateId asc'));
			?>
			<form action="/search/" method="get" class="form">
				<div class="category">
					<div class="head"><?=$c['lang_pack']['all_category'];?><em></em></div>
					<ul class="list">
						<li cateid="0"><?=$c['lang_pack']['all_category'];?></li>
						<?php
						foreach((array)$procate_row as $k=>$v){
						?>
						<li cateid="<?=$v['CateId'];?>" title="<?=$v['Category'.$c['lang']];?>"><?=$v['Category'.$c['lang']];?></li>
						<?php }?>
					</ul>
				</div>
				<input type="text" class="text fl" placeholder="<?=$c['config']['global']['SearchTips']["SearchTips{$c['lang']}"];?>" name="Keyword" autocomplete="off" notnull="" value="<?=$Keyword;?>" />
				<input type="submit" class="button fr FontBgColor" value="<?=$c['lang_pack']['search'];?>" />
				<input type="hidden" name="CateId" value="" />
				<div class="clear"></div>
			</form>
		</div>
		<div class="clear"></div>
	</div>
</div>
<div id="nav" class="NavBgColor">
	<div class="wide clean">
        <?php
		if(!file::check_cache('header.html')||1){
			ob_start();
			
			$allcate_row=str::str_code(db::get_all('products_category', 'IsSoldOut=0', "CateId,UId,Category{$c['lang']},IsIndex",  $c['my_order'].'CateId asc'));
			$allcate_ary=array();
			foreach((array)$allcate_row as $k=>$v){
				$allcate_ary[$v['UId']][]=$v;
			}
		?>
		<div class="nav fl NavBorderColor1">
        	<?php
			$nav_row=db::get_value('config', "GroupId='themes' and Variable='NavData'", 'Value');
			$nav_data=str::json_data($nav_row, 'decode');
			foreach((array)$nav_data as $k=>$v){
				$nav=ly200::nav_style($v, 1);
				if($nav['Name']!='Home') continue;
				$isSelect=($nav['Select'] && count($allcate_ary[$nav['UId']]));
			?>
			<div class="item fl">
				<a href="<?=$nav['Url'];?>"<?=$nav['Target'];?> class="navlink NavHoverBgColor NavBorderColor1"><?=$nav['Name'];?><?=$isSelect?'<em></em>':'';?></a>
				<?php if($isSelect){?>
				<dl<?=$v['DownWidth']?' class="down_width_'.$v['DownWidth'].'"':'';?>>
					<?php
					foreach((array)$allcate_ary[$nav['UId']] as $vv){
						$n=$vv['Category'.$c['lang']];
					?>
					<dd><a href="<?=ly200::get_url($vv, 'products_category');?>" title="<?=$n;?>"><?=$n;?></a></dd>
					<?php }?>
				</dl>
				<?php }?>
			</div>
			<?php }?>
        </div>
		<div class="nav_menu fl">
			<div class="nav_title CategoryBgColor"><?=$c['lang_pack']['all_category'];?><i></i><em></em></div>
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
        <div class="nav fl NavBorderColor1">
        	<?php
			$nav_row=db::get_value('config', "GroupId='themes' and Variable='NavData'", 'Value');
			$nav_data=str::json_data($nav_row, 'decode');
			foreach((array)$nav_data as $k=>$v){
				$nav=ly200::nav_style($v, 1);
				if(!$nav['Name'] || $nav['Name']=='Home') continue;
				$isSelect=($nav['Select'] && count($allcate_ary[$nav['UId']]));
			?>
			<div class="item fl">
				<a href="<?=$nav['Url'];?>"<?=$nav['Target'];?> class="navlink NavHoverBgColor NavBorderColor1"><?=$nav['Name'];?><?=$isSelect?'<em></em>':'';?></a>
				<?php if($isSelect){?>
				<dl<?=$v['DownWidth']?' class="down_width_'.$v['DownWidth'].'"':'';?>>
					<?php
					foreach((array)$allcate_ary[$nav['UId']] as $vv){
						$n=$vv['Category'.$c['lang']];
					?>
					<dd><a href="<?=ly200::get_url($vv, 'products_category');?>" title="<?=$n;?>"><?=$n;?></a></dd>
					<?php }?>
				</dl>
				<?php }?>
			</div>
			<?php }?>
        </div>
        <ul class="crossn fr top_info clean">
			<li class="fl sign"><?php include("{$c['static_path']}inc/sign_in.php");?></li>
		</ul>
		<?php 
			$cache_contents=ob_get_contents();
			ob_end_clean();
			file::write_file(ly200::get_cache_path($c['theme'], 0), 'header.html', $cache_contents);
		}
		include(ly200::get_cache_path($c['theme']).'header.html');
		?>
    </div>
</div>
<div id="section">
	<div class="wide clean">
		<div class="category_list fl">
			<div class="title fl"><?=$c['lang_pack']['all_category'];?></div>
			<div class="list fl">
				<?php
				$cate_row=str::str_code(db::get_limit('products_category', 'IsIndex=1', "CateId,UId,Category{$c['lang']}",  $c['my_order'].'CateId asc', 0, 5));
				foreach($cate_row as $k=>$v){
				?>
				<a href="<?=ly200::get_url($v, 'products_category');?>" title="<?=$v['Category'.$c['lang']];?>"><?=$v['Category'.$c['lang']];?></a>
				<?php }?>
			</div>
		</div>
		<div class="header_cart fr" lang="<?=$c['lang'];?>">
        	<em class="cart_inner fl"></em>
			<a href="/cart/" class="fl"><?=$c['lang_pack']['shoppingCart'];?> <span class="FontColor">(<i class="cart_count"><?=(int)$c['shopping_cart']['TotalQty'];?></i>)</span></a>
		</div>
	</div>
</div>
