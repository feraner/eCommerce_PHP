<?php !isset($c) && exit();?>
<?php
manage::check_permit('set', 1, array('a'=>'country'));//检查权限

$permit_ary=array(
	'add'	=>	manage::check_permit('set', 0, array('a'=>'country', 'd'=>'add')),
	'edit'	=>	manage::check_permit('set', 0, array('a'=>'country', 'd'=>'edit')),
	'del'	=>	manage::check_permit('set', 0, array('a'=>'country', 'd'=>'del'))
);
?>
<?php if($c['manage']['do']!='state_list'){?>
	<div class="r_nav">
		<h1>{/module.set.country/}</h1>
		<?php if($c['manage']['do']=='index'){?>
			<div class="search_form">
				<form method="get" action="?">
					<div class="k_input">
						<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
						<input type="button" value="" class="more" />
					</div>
					<input type="submit" class="search_btn" value="{/global.search/}" />
					<div class="ext">
						<div class="rows">
							<label>{/set.country.continent/}</label>
							<span class="input">
								<select name="Continent">
									<option value="">{/global.select_index/}</option>
									<?php foreach($c['continent'] as $k=>$v){?>
										<option value="<?=$k;?>">{/continent.<?=$k;?>/}</option>
									<?php }?>
								</select>
							</span>
							<div class="clear"></div>
						</div>
					</div>
					<div class="clear"></div>
					<input type="hidden" name="m" value="set" />
					<input type="hidden" name="a" value="country" />
				</form>
			</div>
			<ul class="ico">
				<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="./?m=set&a=country&d=edit" label="{/global.add/}"></a></li></ul><?php }?>
				<?php if($permit_ary['edit']){?><li><a class="tip_ico_down bat_open" href="javascript:;" label="{/set.country.uesd_bat/}"></a></li><?php }?>
				<?php if($permit_ary['edit']){?><li><a class="tip_ico_down bat_close" href="javascript:;" label="{/set.country.close_bat/}"></a></li><?php }?>
			</ul>
			<div class="quick_search">
				{/set.country.quick_search/}:
				<?php
				foreach(range('a', 'z') as $v){
				?>
					<a href="./?m=set&a=country&k=<?=$v;?>"><?=$v;?></a>
				<?php }?>
			</div>
		<?php }?>
	</div>
<?php }?>
<div id="country" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		$currency_ary=array();
		$currency_row=db::get_all('currency', 'IsUsed=1', 'CId, Currency, Symbol', $c['my_order'].'CId asc');
		foreach($currency_row as $v){
			$currency_ary[$v['CId']]=$v['Currency'];
		}
	?>
		<script type="text/javascript">$(document).ready(function(){set_obj.country_init()});</script>
        <table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
            <thead>
                <tr>
					<td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" /></td>
                    <td width="8%" nowrap="nowrap">{/global.serial/}</td>
                    <td width="20%" nowrap="nowrap">{/set.country.country/}</td>
                    <td width="10%" nowrap="nowrap">{/set.country.country/}{/set.country.acronym/}</td>
					<td width="8%" nowrap="nowrap">{/set.country.continent/}</td>
                    <td width="10%" nowrap="nowrap">{/set.country.code/}</td>
					<td width="10%" nowrap="nowrap">{/set.exchange.currency/}</td>
					<td width="10%" nowrap="nowrap">{/set.country.flag/}</td>
                    <td width="10%" nowrap="nowrap">{/global.used/}</td>
                    <td width="8%" nowrap="nowrap">{/set.country.hot/}</td>
                    <td width="8%" nowrap="nowrap">{/set.country.state/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
                    	<td width="8%" nowrap="nowrap">{/global.operation/}</td>
					<?php }?>
                </tr>
            </thead>
            <tbody>
                <?php
				$where='1';
				$k=$_GET['k'];
				$Keyword=str::str_code($_GET['Keyword']);
				$Continent=(int)$_GET['Continent'];
				$k!='' && $where.=" and Country like '$k%'";
				$Keyword && $where.=" and Country like '%$Keyword%'";
				$Continent && $where.=" and Continent='$Continent'";
				$country_row=str::str_code(db::get_all('country', $where, '*', 'Country asc'));
				foreach($country_row as $k=>$v){
					$IsUsed=(int)$v['IsUsed'];
					$IsHot=(int)$v['IsHot'];
					$IsDefault=(int)$v['IsDefault'];
				?>
					<tr cid="<?=$v['CId'];?>">
						<td><input type="checkbox" name="select" value="<?=$v['CId'];?>" /></td>
						<td nowrap="nowrap"><?=$k+1;?></td>
						<td nowrap="nowrap" class="<?=(int)$IsDefault?'default':'';?>"><?=$v['Country'];?></td>
						<td nowrap="nowrap"><?=$v['Acronym'];?></td>
						<td nowrap="nowrap">{/continent.<?=$v['Continent'];?>/}</td>
						<td nowrap="nowrap">+<?=$v['Code'];?></td>
						<td nowrap="nowrap"><?=$currency_ary[$v['Currency']];?></td>
						<td nowrap="nowrap" class="img"><?=$v['CId']<=240?'<div class="icon_flag flag_'.strtolower($v['Acronym']).'"></div>':'<img src="'.$v['FlagPath'].'" />';?></td>
						<td class="used_checkbox">
							<?php if($permit_ary['edit']){?>
								<div class="switchery<?=$IsUsed?' checked':'';?><?=$IsDefault?' no_drop':'';?>">
									<div class="switchery_toggler"></div>
									<div class="switchery_inner">
										<div class="switchery_state_on"></div>
										<div class="switchery_state_off"></div>
									</div>
								</div>
							<?php
							}else{
								echo $IsUsed?'{/global.n_y.1/}':'';
							}
							?>
						</td>
						<td class="hot_checkbox">
							<?php if($permit_ary['edit']){?>
								<div class="switchery<?=$IsHot?' checked':'';?><?=$IsDefault?' no_drop':'';?>">
									<div class="switchery_toggler"></div>
									<div class="switchery_inner">
										<div class="switchery_state_on"></div>
										<div class="switchery_state_off"></div>
									</div>
								</div>
							<?php
							}else{
								echo $IsHot?'{/global.n_y.1/}':'';
							}
							?>
						</td>
						<td nowrap="nowrap"><?=$v['HasState']?'<a href="javascript:;" class="open_state" data-url="./?m=set&a=country&d=state_list&CId='.$v['CId'].'"><img src="/static/ico/set.png" title="{/global.set/}" align="absmiddle" /></a>':'';?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico" href="./?m=set&a=country&d=edit&CId=<?=$v['CId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" title="{/global.edit/}{/set.country.info/}" /></a><?php }?>
								<?php if($v['CId']>240 && $permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=set.country_del&CId=<?=$v['CId'];?>" label="{/global.del/}"><img src="/static/ico/del.png" title="{/global.del/}{/set.country.info/}" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
                <?php }?>
            </tbody>
        </table>
    <?php 
	}elseif($c['manage']['do']=='edit'){
		$CId=(int)$_GET['CId'];
		$used_checked=' checked';
		$hot_checked=$state_checked='';
		if($CId){
			$country_row=str::str_code(db::get_one('country', "CId='{$CId}'"));
			$used_checked=$country_row['IsUsed']==1?' checked':'';
			$hot_checked=$country_row['IsHot']==1?' checked':'';
			$default_checked=$country_row['IsUsed']==1&&$country_row['IsHot']==1&&$country_row['IsDefault']==1?' checked':'';
			$state_checked=$country_row['HasState']==1?' checked':'';
		}
	?>
		<script type="text/javascript">$(document).ready(function(){set_obj.country_edit_init()});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/set.country.country/}</label>
				<span class="input">
					<?php
					$country_data=str::json_data(htmlspecialchars_decode($country_row['CountryData']), 'decode');
					if(!$CId || $country_row['CId']>240){
						$country_ary=array();
						foreach($country_data as $k=>$v){
							$country_ary['Country_'.$k]=$v;
						}
						echo manage::form_edit($country_ary, 'text', 'Country', 25, 100, 'notnull');
					}else{
						foreach($country_data as $k=>$v){
							echo "{/language.{$k}/}: ".$v.'<br />';
						}
					}?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/set.country.country/}{/set.country.acronym/}</label>
				<span class="input"><input type="text" name="Acronym" value="<?=$country_row['Acronym'];?>" class="form_input" size="5" maxlength="2" notnull=""<?=($CId && $country_row['CId']<240)?' readonly':'';?>></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/set.country.continent/}</label>
				<span class="input">
					<?php
					if($CId && $country_row['CId']<240){
						echo $c['manage']['lang_pack']['continent'][$country_row['Continent']];
					}else{?>
						<select name="Continent">
							<?php foreach($c['continent'] as $k=>$v){?>
								<option value="<?=$k;?>"<?=$country_row['Continent']==$k?' selected':'';?>>{/continent.<?=$k;?>/}</option>
							<?php }?>
						</select>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/set.country.code/}</label>
				<span class="input">+<input type="text" name="Code" value="<?=$country_row['Code'];?>" class="form_input" size="5" maxlength="5" notnull=""<?=($CId && $country_row['CId']<240)?' readonly':'';?>></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
                <label>{/set.country.flag/}</label>
                <span class="input upload_file upload_flag">
					<?php
					if(!$CId || $country_row['CId']>240){
						if($permit_ary['edit']){
					?>
						<div class="img">
							<div id="FlagDetail" class="upload_box preview_pic"><input type="button" id="FlagUpload" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="" /></div>
						</div>
						<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
						<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
					<?php
						}else{
							if(is_file($c['root_path'].$country_row['FlagPath'])) echo '<img src="'.$country_row['FlagPath'].'" />';
						}
					}else{
					?>
						<div class="icon_flag flag_<?=strtolower($country_row['Acronym']);?>"></div>
					<?php }?>
                </span>
                <div class="clear"></div>
            </div>
			<div class="rows">
				<label>{/set.exchange.currency/}</label>
				<span class="input">
					<?php
					$currency_row=db::get_all('currency', 'IsUsed=1', 'CId, Currency, Symbol', $c['my_order'].'CId asc');
					?>
					<select name="Currency">
						<option value="">{/global.select_index/}</option>
						<?php
						foreach($currency_row as $v){
						?>
						<option value="<?=$v['CId'];?>"<?=$country_row['Currency']==$v['CId']?' selected':'';?>><?=$v['Currency'];?></option>
						<?php }?>
					</select>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/global.used/}</label>
				<span class="input">
					<div class="switchery<?=$used_checked;?>">
						<input type="checkbox" name="IsUsed" value="1"<?=$used_checked;?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
					<span class="tool_tips_ico" content="{/set.country.used_notes/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows" style="display:<?=$used_checked==''?'none':'block';?>;" id="hot">
				<label>{/set.country.hot/}</label>
				<span class="input">
					<div class="switchery<?=$hot_checked;?>">
						<input type="checkbox" name="IsHot" value="1"<?=$hot_checked;?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
					<span class="tool_tips_ico" content="{/set.country.hot_notes/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows" style="display:<?=$used_checked==''||$hot_checked==''?'none':'block';?>;" id="default">
				<label>{/set.country.default/}{/set.country.country/}</label>
				<span class="input">
					<div class="switchery<?=$default_checked;?>">
						<input type="checkbox" name="IsDefault" value="1"<?=$default_checked;?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows" style="display:<?=$used_checked==''?'none':'block';?>;" id="state">
				<label>{/global.turn_on/}{/set.country.state/}</label>
				<span class="input">
					<div class="switchery<?=$state_checked;?>">
						<input type="checkbox" name="HasState" value="1"<?=$state_checked;?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
					<span class="tool_tips_ico" content="{/set.country.state_notes/}"></span>
				</span>
				<div class="clear"></div>
			</div>

			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=set&a=country" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="CId" value="<?=$CId;?>" />
			<input type="hidden" name="FlagPath" value="<?=$country_row['FlagPath'];?>" save="<?=is_file($c['root_path'].$country_row['FlagPath'])?1:0;?>" />
			<input type="hidden" name="do_action" value="set.country_edit" />
		</form>
    <?php 
	}elseif($c['manage']['do']=='state_list'){
		$CId=(int)$_GET['CId'];
		$Country=str::str_code(db::get_value('country', "CId='{$CId}'", 'Country'));
		!$Country && js::location('./?m=set&a=country');
		$states_row=str::str_code(db::get_all('country_states', "CId='{$CId}'", '*', $c['my_order'].'States asc'));
		echo ly200::load_static('/static/js/plugin/dragsort/dragsort-0.5.1.min.js');
	?>
		<script type="text/javascript">$(document).ready(function(){set_obj.country_states_init()});</script>
		<div class="country_states" CId="<?=$CId;?>">
			<ul class="menu_list">
				<li>
					<div class="menu_one"><h4 class="fl"><?=$Country;?></h4></div>
					<dl>
						<?php
						foreach((array)$states_row as $k=>$v){
						?>
						<dt sid="<?=$v['SId'];?>">
							<div class="sub" data="&CId=<?=$CId;?>&SId=<?=$v['SId'];?>">
								<h5 class="fl"><strong><?=$v['AcronymCode']?'['.$v['AcronymCode'].'] ':'';?></strong><?=$v['States'];?></h5>
								<?php if($permit_ary['edit']){?>
									<a class="del menu_view fr" href="./?do_action=set.country_states_del&CId=<?=$CId;?>&SId=<?=$v['SId'];?>"><img src="/static/ico/del.png" /></a>
									<a class="edit menu_view fr" href="javascript:;"><img src="/static/ico/edit.png" /></a>
								<?php }?>
							</div>
						</dt>
						<?php }?>
					</dl>
				</li>
			</ul>
			<div class="edit_form country_states_edit"></div>
		</div>
	<?php 
	}elseif($c['manage']['do']=='state_edit'){
		$CId=(int)$_GET['CId'];
		$SId=(int)$_GET['SId'];
		($CId && $SId) && $state_row=str::str_code(db::get_one('country_states', "CId='{$CId}' and SId='{$SId}'"));
	?>
		<div class="country_states_edit">
			<form id="country_states_edit_form" class="r_con_form">
				<div class="rows">
					<label>{/set.country.state/}</label>
					<span class="input"><input type="text" name="States" value="<?=$state_row['States'];?>" class="form_input" size="25" maxlength="50" notnull=""></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/set.country.state/}{/set.country.acronym/}</label>
					<span class="input"><input type="text" name="AcronymCode" value="<?=$state_row['AcronymCode'];?>" class="form_input" size="5" maxlength="5" notnull=""></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label></label>
					<span class="input">
						<input type="submit" class="btn_ok" name="submit_button" value="<?=$SId?'{/global.edit/}':'{/global.add/}';?>{/products.country_states.classify/}" />
					</span>
					<div class="clear"></div>
				</div>
				<input type="hidden" name="CId" value="<?=$CId;?>" />
				<input type="hidden" name="SId" value="<?=$SId;?>" />
				<input type="hidden" name="do_action" value="set.country_states_edit">
			</form>
		</div>
    <?php }?>
</div>