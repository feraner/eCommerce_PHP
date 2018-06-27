<?php !isset($c) && exit();?>
<?php
$cur_lang=str_replace('-', '_', substr($c['lang'], 1));
?>
<script type="text/javascript">$(document).ready(function(){user_obj.user_address()});</script>
<div class="editAddr">
	<form action="" method="post" class="user_address_form">
		<div class="shipping_address">
			<div class="rows">
				<div class="form_box clean">
					<div class="box">
						<label class="input_box">
							<span class="input_box_label"><?=$c['lang_pack']['mobile']['first_name'];?></span>
							<input type="text" class="input_box_txt" name="FirstName" placeholder="<?=$c['lang_pack']['mobile']['first_name'];?>" notnull />
						</label>
						<p class="error"></p>
					</div>
					<div class="box">
						<label class="input_box">
							<span class="input_box_label"><?=$c['lang_pack']['mobile']['last_name'];?></span>
							<input type="text" class="input_box_txt" name="LastName" placeholder="<?=$c['lang_pack']['mobile']['last_name'];?>" notnull />
						</label>
						<p class="error"></p>
					</div>
				</div>
			</div>
			<?php if($cur_lang!='jp' && $cur_lang!='zh_tw'){//除了 日语 和 繁体中文 之外?>
				<div class="rows">
					<div class="input clean">
						<label class="input_box">
							<span class="input_box_label"><?=$c['lang_pack']['mobile']['addr_line_1'];?></span>
							<input type="text" class="input_box_txt" name="AddressLine1" placeholder="<?=$c['lang_pack']['mobile']['addr_line_1'];?>" notnull />
						</label>
						<p class="error"></p>
					</div>
				</div>
				<div class="rows">
					<div class="input clean">
						<label class="input_box">
							<span class="input_box_label"><?=$c['lang_pack']['mobile']['addr_line_2'];?></span>
							<input type="text" class="input_box_txt" name="AddressLine2" placeholder="<?=$c['lang_pack']['mobile']['addr_line_2'];?>" />
						</label>
						<p class="error"></p>
					</div>
				</div>
			<?php }?>
			<?php
			if($cur_lang=='jp' || $cur_lang=='zh_tw'){//日语 或者 繁体中文
			?>
				<div class="rows">
					<div class="form_box clean">
						<div class="box">
							<label class="input_box">
								<span class="input_box_label"><?=$c['lang_pack']['mobile']['zip_code'];?></span>
								<input type="text" class="input_box_txt" name="ZipCode" placeholder="<?=$c['lang_pack']['mobile']['zip_code'];?>" notnull />
							</label>
							<p class="error"></p>
						</div>
						<div class="box">
							<?php
							$country_ary=$shipto_country_ary=array();
							$hot_country_len=0;
							if($OvId_where){
								$shipping_country_row=db::get_all('shipping_area a left join shipping_country c on a.AId=c.AId', $OvId_where.' Group By c.CId', 'c.CId');
								foreach($shipping_country_row as $v){ $shipto_country_ary[]=$v['CId']; }
							}
							$country_row=str::str_code(db::get_all('country', "IsUsed=1", '*', 'Country asc'));
							foreach((array)$country_row as $k=>$v){
								if($OvId_where && !in_array($v['CId'], $shipto_country_ary)) continue;//所有快递方式都没有的国家，给过滤掉
								$country_ary[$k]=$v;
								$v['IsHot'] && $hot_country_len+=1;
								if($c['lang']!='_en'){
									$country_data=str::json_data(htmlspecialchars_decode($v['CountryData']), 'decode');
									$country_ary[$k]['Country']=$country_data[substr($c['lang'], 1)];
								}
							}
							?>
							<select name="country_id" id="country" placeholder="<?=$c['lang_pack']['user']['countrySelect'];?>" style="display:none;" class="chzn-done">
								<option value="-1"></option>
								<?php if($hot_country_len>10){?>
									<optgroup label="---------">
										<?php
										foreach((array)$country_ary as $v){
											if($v['IsHot']!=1) continue;
										?>
											<option value="<?=$v['CId'];?>"><?=$v['Country'];?></option>
										<?php }?>
									</optgroup>
								<?php }?>
								<optgroup label="---------">
									<?php 
									foreach((array)$country_ary as $v){
									?>
										<option value="<?=$v['CId'];?>"><?=$v['Country'];?></option>
									<?php }?>
								</optgroup>
							</select>
							<div id="country_chzn" class="chzn-container chzn-container-single">
								<a href="javascript:;" class="chzn-single"><span><?=$c['lang_pack']['user']['pleaseSelect'];?>---</span><div><b></b></div></a>
								<div class="chzn-drop">
									<div class="chzn-search clearfix"><input type="text" autocomplete="off" class="" /></div>
									<ul class="chzn-results">
										<?php
										if($hot_country_len>10){
											foreach((array)$country_ary as $k=>$v){
												if($v['IsHot']!=1) continue;
												echo '<li class="group-option active-result">'.$v['Country'].'</li>';
											}
											echo '<li class="group-result active-result">---------</li>';
										}
										foreach((array)$country_ary as $k=>$v){
											echo '<li class="group-option active-result">'.$v['Country'].'</li>';
										}?>
									</ul>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="rows">
					<div class="form_box clean">
						<div class="box">
							<div id="zoneId">
								<select name="Province" placeholder="<?=$c['lang_pack']['user']['pleaseSelect'];?>---" class="chzn-done" style="display:none;"><option value="-1"></option></select><p class="error"></p>
								<div class="chzn-container chzn-container-single">
									<a href="javascript:;" class="chzn-single" tabindex="0"><span><?=$c['lang_pack']['user']['pleaseSelect'];?>---</span><div><b></b></div></a>
									<div class="chzn-drop">
										<div class="chzn-search clearfix"><input type="text" autocomplete="off" tabindex="-1" class="" /></div>
										<ul class="chzn-results"></ul>
									</div>
								</div>
							</div>
							<div id="state" style="display:none;">
								<label class="input_box">
									<span class="input_box_label"><?=$c['lang_pack']['mobile']['state_oth'];?></span>
									<input type="text" class="input_box_txt" name="State" placeholder="<?=$c['lang_pack']['mobile']['state_oth'];?>" disabled />
								</label>
								<p class="error"></p>
							</div>
						</div>
						<div class="box">
							<label class="input_box">
								<span class="input_box_label"><?=$c['lang_pack']['mobile']['city'];?></span>
								<input type="text" class="input_box_txt" name="City" placeholder="<?=$c['lang_pack']['mobile']['city'];?>" notnull />
							</label>
							<p class="error"></p>
						</div>
					</div>
				</div>
			<?php
			}else{//其他语言版
			?>
				<div class="rows">
					<div class="form_box clean">
						<div class="box">
							<label class="input_box">
								<span class="input_box_label"><?=$c['lang_pack']['mobile']['city'];?></span>
								<input type="text" class="input_box_txt" name="City" placeholder="<?=$c['lang_pack']['mobile']['city'];?>" notnull />
							</label>
							<p class="error"></p>
						</div>
						<div class="box">
							<label class="input_box">
								<span class="input_box_label"><?=$c['lang_pack']['mobile']['zip_code'];?></span>
								<input type="text" class="input_box_txt" name="ZipCode" placeholder="<?=$c['lang_pack']['mobile']['zip_code'];?>" notnull />
							</label>
							<p class="error"></p>
						</div>
					</div>
				</div>
				<div class="rows">
					<div class="form_box clean">
						<div class="box">
							<?php
							$country_ary=$shipto_country_ary=array();
							$hot_country_len=0;
							if($OvId_where){
								$shipping_country_row=db::get_all('shipping_area a left join shipping_country c on a.AId=c.AId', $OvId_where.' Group By c.CId', 'c.CId');
								foreach($shipping_country_row as $v){ $shipto_country_ary[]=$v['CId']; }
							}
							$country_row=str::str_code(db::get_all('country', "IsUsed=1", '*', 'Country asc'));
							foreach((array)$country_row as $k=>$v){
								if($OvId_where && !in_array($v['CId'], $shipto_country_ary)) continue;//所有快递方式都没有的国家，给过滤掉
								$country_ary[$k]=$v;
								$v['IsHot'] && $hot_country_len+=1;
								if($c['lang']!='_en'){
									$country_data=str::json_data(htmlspecialchars_decode($v['CountryData']), 'decode');
									$country_ary[$k]['Country']=$country_data[substr($c['lang'], 1)];
								}
							}
							?>
							<select name="country_id" id="country" placeholder="<?=$c['lang_pack']['user']['countrySelect'];?>" style="display:none;" class="chzn-done">
								<option value="-1"></option>
								<?php if($hot_country_len>10){?>
									<optgroup label="---------">
										<?php
										foreach((array)$country_ary as $v){
											if($v['IsHot']!=1) continue;
										?>
											<option value="<?=$v['CId'];?>"><?=$v['Country'];?></option>
										<?php }?>
									</optgroup>
								<?php }?>
								<optgroup label="---------">
									<?php 
									foreach((array)$country_ary as $v){
									?>
										<option value="<?=$v['CId'];?>"><?=$v['Country'];?></option>
									<?php }?>
								</optgroup>
							</select>
							<div id="country_chzn" class="chzn-container chzn-container-single">
								<a href="javascript:;" class="chzn-single"><span><?=$c['lang_pack']['user']['pleaseSelect'];?>---</span><div><b></b></div></a>
								<div class="chzn-drop">
									<div class="chzn-search clearfix"><input type="text" autocomplete="off" class="" /></div>
									<ul class="chzn-results">
										<?php
										if($hot_country_len>10){
											foreach((array)$country_ary as $k=>$v){
												if($v['IsHot']!=1) continue;
												echo '<li class="group-option active-result">'.$v['Country'].'</li>';
											}
											echo '<li class="group-result active-result">---------</li>';
										}
										foreach((array)$country_ary as $k=>$v){
											echo '<li class="group-option active-result">'.$v['Country'].'</li>';
										}?>
									</ul>
								</div>
							</div>
						</div>
						<div class="box">
							<div id="zoneId">
								<select name="Province" placeholder="<?=$c['lang_pack']['user']['pleaseSelect'];?>---" class="chzn-done" style="display:none;"><option value="-1"></option></select><p class="error"></p>
								<div class="chzn-container chzn-container-single">
									<a href="javascript:;" class="chzn-single" tabindex="0"><span><?=$c['lang_pack']['user']['pleaseSelect'];?>---</span><div><b></b></div></a>
									<div class="chzn-drop">
										<div class="chzn-search clearfix"><input type="text" autocomplete="off" tabindex="-1" class="" /></div>
										<ul class="chzn-results"></ul>
									</div>
								</div>
							</div>
							<div id="state" style="display:none;">
								<label class="input_box">
									<span class="input_box_label"><?=$c['lang_pack']['mobile']['state_oth'];?></span>
									<input type="text" class="input_box_txt" name="State" placeholder="<?=$c['lang_pack']['mobile']['state_oth'];?>" disabled />
								</label>
								<p class="error"></p>
							</div>
						</div>
					</div>
				</div>
			<?php }?>
			<div class="rows" id="taxCode" style="display:none;">
				<div class="form_box clean">
					<div class="box">
						<div class="box_select">
							<select name="tax_code_type" class="addr_select" id="taxCodeOption" disabled>
								<option value="1"><?=$c['lang_pack']['mobile']['cpf'];?> (<?=$c['lang_pack']['mobile']['per_order'];?>)</option>
								<option value="2"><?=$c['lang_pack']['mobile']['cnpj'];?> (<?=$c['lang_pack']['mobile']['com_order'];?>)</option>
							</select>
						</div>
					</div>
					<div class="box">
						<label class="input_box">
							<span class="input_box_label"><?=$c['lang_pack']['mobile']['cpf_code'];?></span>
							<input type="text" class="input_box_txt" name="tax_code_value" id="taxCodeValue" placeholder="<?=$c['lang_pack']['mobile']['cpf_code'];?>" maxlength="11" format="Length|11" disabled />
						</label>
						<p class="error"></p>
					</div>
				</div>
			</div>
			<div class="rows" id="tariffCode" style="display:none;">
				<div class="form_box clean">
					<div class="box">
						<div class="box_select">
							<select name="tax_code_type" class="addr_select" id="tariffCodeOption" disabled>
								<option value="3"><?=$c['lang_pack']['mobile']['per_id_num'];?> (<?=$c['lang_pack']['mobile']['per_order'];?>)</option>
								<option value="4"><?=$c['lang_pack']['mobile']['vat_id_num'];?> (<?=$c['lang_pack']['mobile']['com_order'];?>)</option>
							</select>
						</div>
					</div>
					<div class="box">
						<label class="input_box">
							<span class="input_box_label">Personal or VAT ID</span>
							<input type="text" class="input_box_txt" name="tax_code_value" id="tariffCodeValue" placeholder="Personal or VAT ID" maxlength="14" format="Length|11" disabled />
						</label>
						<p class="error"></p>
					</div>
				</div>
			</div>
			<?php if($cur_lang=='jp' || $cur_lang=='zh_tw'){//日语 或者 繁体中文?>
				<div class="rows">
					<div class="input clean">
						<label class="input_box">
							<span class="input_box_label"><?=$c['lang_pack']['mobile']['addr_line_1'];?></span>
							<input type="text" class="input_box_txt" name="AddressLine1" placeholder="<?=$c['lang_pack']['mobile']['addr_line_1'];?>" notnull />
						</label>
						<p class="error"></p>
					</div>
				</div>
				<div class="rows">
					<div class="input clean">
						<label class="input_box">
							<span class="input_box_label"><?=$c['lang_pack']['mobile']['addr_line_2'];?></span>
							<input type="text" class="input_box_txt" name="AddressLine2" placeholder="<?=$c['lang_pack']['mobile']['addr_line_2'];?>" />
						</label>
						<p class="error"></p>
					</div>
				</div>
			<?php }?>
			<div class="rows">
				<div class="input clean">
					<div class="box_input_group clearfix">
						<input id="countryCode" class="box_input input_group_addon" name="CountryCode" type="text" value="+0000" readonly />
						<label class="input_box">
							<span class="input_box_label"><?=$c['lang_pack']['mobile']['phone_num'];?></span>
							<input type="text" class="input_box_txt input_group" name="PhoneNumber" placeholder="<?=$c['lang_pack']['mobile']['phone_num'];?>" notnull />
						</label>
					</div>
					<p class="error"></p>
				</div>
			</div>
			<div class="button">
				<a href="javascript:;" class="btn_cancel btn_global sys_shadow_button" id="cancel_address" style="display:<?=!(int)$_SESSION['User']['UserId']?'none':'';?>;"><?=$c['lang_pack']['user']['cancel'];?></a>
				<button type="submit" class="btn_save btn_global sys_shadow_button" id="save_address"><?=$c['lang_pack']['user']['save'];?></button>
			</div>
		</div>
		<input type="hidden" name="edit_address_id" value="0" id="addressId" />
	</form>
</div>
