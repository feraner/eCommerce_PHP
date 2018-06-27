<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
$IsShipping=(int)$_GET['Shipping']; //仅显示送货地址
$IsForm=(int)$_GET['Form']; //编辑表单显示

if((int)$_SESSION['User']['UserId']){
	$ship_row=str::str_code(db::get_all('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', 'a.'.$c['where']['user']." and a.IsBillingAddress=0", 'a.*, c.Country, s.States as StateName', 'a.AccTime desc, a.AId desc'));
	$ship_len=count($ship_row);
	
	$bill_row=str::str_code(db::get_one('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', 'a.'.$c['where']['user']." and a.IsBillingAddress=1", 'a.*, c.Country, s.States as StateName'));
	
	$ship_len && $Default_AId=$_SESSION['Cart']['ShippingAddressAId']?$_SESSION['Cart']['ShippingAddressAId']:$ship_row[0]['AId'];
}else{
	$address_ary=$_SESSION['Cart']['ShippingAddress']?$_SESSION['Cart']['ShippingAddress']:array('CId'=>0, 'SId'=>0);
	$country_val=str::str_code(db::get_value('country', "CId='{$address_ary['CId']}'", 'Country'));
	$states_val=str::str_code(db::get_value('country_states', "SId='{$address_ary['SId']}'", 'States'));
	if($country_val || $states_val){
		$address_ary['Country']=$country_val;
		$address_ary['StateName']=$states_val;
	}
	$ship_row[0]=$address_ary;
	unset($address_ary);
}
?>
<script type="text/javascript">$(function(){user_obj.user_address()});</script>
<div id="user">
	<?php if($IsForm==0){?>
    	<?php if(!$IsShipping){ //账单地址?>
        	<div class="global_titile"><?=$c['lang_pack']['mobile']['your_bill_addr'];?></div>
            <div class="address_row">
            	<div class="info">
                    <strong><?=$bill_row['FirstName'].' '.$bill_row['LastName'];?> (<?=$bill_row['Country'];?>)</strong>
                    <p><?=($bill_row['StateName']?$bill_row['StateName']:$bill_row['State']).', '.$bill_row['City'].', '.$bill_row['AddressLine1'].' '.($bill_row['AddressLine2']?$bill_row['AddressLine2'].' ':'').($bill_row['ZipCode']?', '.$bill_row['ZipCode']:'');?></p>
                    <p><?=$bill_row['CountryCode'].' '.$bill_row['PhoneNumber'];?></p>
                </div>
                <div class="para"><a class="edit" href="/account/address/?Form=1&AId=<?=$bill_row['AId'];?>"><?=$c['lang_pack']['user']['edit']?></a></div>
            </div>
        <?php }?>
        <div class="global_titile"><?=$c['lang_pack']['mobile']['your_ship_addr'];?></div>
		<?php
        foreach($ship_row as $k=>$v){
        ?>
        	<?=$k?'<div class="global_line"></div>':''?>
            <div class="address_row">
                <div class="info">
                    <strong><?=$v['FirstName'].' '.$v['LastName'];?> (<?=$v['Country'];?>)</strong>
                    <p><?=($v['StateName']?$v['StateName']:$v['State']).', '.$v['City'].', '.$v['AddressLine1'].' '.($v['AddressLine2']?$v['AddressLine2'].' ':'').($v['ZipCode']?', '.$v['ZipCode']:'');?></p>
                    <p><?=$v['CountryCode'].' '.$v['PhoneNumber'];?></p>
                </div>
                
                <div class="para">
                	<?php if((int)$_SESSION['User']['UserId']){?>
                    <a class="fl <?=$Default_AId==$v['AId']?'selected FontColor':'noselected';?>" href="javascript:;" data-aid="<?=(int)$v['AId'];?>"><i class="FontBgColor"></i><?=$c['lang_pack']['mobile']['selected']?></a>
                    <?php }?>
                    <a class="edit" href="/<?=(int)$_SESSION['User']['UserId']?'account':'cart';?>/address/?Form=1&AId=<?=$v['AId'];?>&Shipping=<?=$IsShipping;?>"><?=$c['lang_pack']['user']['edit']?></a>
                    <?php if((int)$_SESSION['User']['UserId']){?>
                    <span>|</span>
                    <a class="del" href="javascript:;" url="/account/address/remove<?=sprintf('%04d', $v['AId']);?>.html"><?=$c['lang_pack']['user']['delete']?></a>
                    <?php }?>
                </div>
            </div>
        <?php }?>
        <a href="/account/address/?Form=1&Shipping=<?=$IsShipping;?>" class="address_btn FontBgColor"><em>+</em><?=$c['lang_pack']['mobile']['add_new_addr'];?></a>
        <a href="<?=$IsShipping?'/cart/checkout.html':'/account/';?>" class="address_btn back"><?=$c['lang_pack']['mobile']['back'];?></a>
    <?php
    	}else{ //编辑表单
			echo ly200::load_static("{$c['mobile']['tpl_dir']}js/user.js");	
	?>
		<form action="?" method="post" class="user_address_form">
			<div class="title"><?=$c['lang_pack']['mobile']['ship_addr'];?></div>
			<?php if(!(int)$_SESSION['User']['UserId']){?>
				<div class="rows">
					<label class="field"><?=$c['lang_pack']['mobile']['email'];?> <span class="fc_red">*</span></label>
					<div class="input clean"><input type="email" class="box_input" name="Email" data-field="<?=$c['lang_pack']['mobile']['email'];?>" notnull /><p class="error"></p></div>
				</div>
			<?php }?>
			<div class="rows">
				<div class="form_name clean">
					<div class="box">
						<label class="field"><?=$c['lang_pack']['mobile']['first_name'];?> <span class="fc_red">*</span></label>
                		<input type="text" class="box_input" name="FirstName" data-field="<?=$c['lang_pack']['mobile']['first_name'];?>" notnull /><p class="error"></p>
					</div>
					<div class="box">
						<label class="field"><?=$c['lang_pack']['mobile']['last_name'];?> <span class="fc_red">*</span></label>
                		<input type="text" class="box_input" name="LastName" data-field="<?=$c['lang_pack']['mobile']['last_name'];?>" notnull /><p class="error"></p>
					</div>
				</div>
			</div>
			<div class="rows">
				<label class="field"><?=$c['lang_pack']['mobile']['addr_line_1'];?> <span class="fc_red">*</span></label>
				<div class="input clean"><input type="text" class="box_input" name="AddressLine1" data-field="<?=$c['lang_pack']['mobile']['addr_line_1'];?>" notnull /><p class="error"></p></div>
			</div>
			<div class="rows">
				<label class="field"><?=$c['lang_pack']['mobile']['addr_line_2'];?></label>
				<div class="input clean"><input type="text" class="box_input" name="AddressLine2" /></div>
			</div>
			<div class="rows">
				<label class="field"><?=$c['lang_pack']['mobile']['city'];?> <span class="fc_red">*</span></label>
				<div class="input clean"><input type="text" name="City" class="box_input" data-field="<?=$c['lang_pack']['mobile']['city'];?>" notnull /><p class="error"></p></div>
			</div>
			<div class="rows">
				<label class="field"><?=$c['lang_pack']['mobile']['destin'];?> <span class="fc_red">*</span></label>
				<div class="input clean">
					<div class="box_select">
						<select class="addr_select" name="country_id" id="country" notnull>
							<option value=""><?=$c['lang_pack']['mobile']['plz_country'];?></option>
							<?php
							$shipto_country_ary=array();
							if($IsShipping){
								$cart_row=db::get_all('shopping_cart', $c['where']['cart'], 'OvId', 'CId desc');
								$OvId_where='a.OvId in(-1';
								foreach($cart_row as $v){ $OvId_where.=",{$v['OvId']}"; }
								$OvId_where.=')';
								$shipping_country_row=db::get_all('shipping_area a left join shipping_country c on a.AId=c.AId', $OvId_where.' Group By c.CId', 'c.CId');
								foreach($shipping_country_row as $v){ $shipto_country_ary[]=$v['CId']; }
							}
							$country_row=str::str_code(db::get_all('country', "IsUsed=1", '*', 'Country asc'));
							foreach($country_row as $v){
								if($IsShipping && !in_array($v['CId'], $shipto_country_ary)) continue;//所有快递方式都没有的国家，给过滤掉
								$selected=((int)$address_row[0]['CId']?$address_row[0]['CId']==$v['CId']:$v['IsDefault']==1)?' selected="selected"':'';
							?>
								<option value="<?=$v['CId'];?>"<?=$selected;?>><?=$v['Country'];?></option>
							<?php }?>
						</select>
					</div>
				</div>
			</div>
			<div class="rows" id="zoneId">
				<label class="field"><?=$c['lang_pack']['mobile']['state_oth'];?> <span class="fc_red">*</span></label>
				<div class="input clean">
					<div class="box_select">
						<select class="addr_select" name="Province" notnull>
							<option value=""><?=$c['lang_pack']['mobile']['plz_sel'];?></option>
						</select>
					</div>
				</div>
			</div>
			<div class="rows" id="state" style="display:none;">
				<label class="field"><?=$c['lang_pack']['mobile']['state_oth'];?></label>
				<div class="input clean"><input type="text" class="box_input" name="State" disabled /></div>
			</div>
			<div class="rows" id="taxCode" style="display:none;">
				<label class="field"><?=$c['lang_pack']['mobile']['cpf_code'];?></label>
				<div class="input clean">
					<div class="box_select">
						<select name="tax_code_type" class="addr_select" id="taxCodeOption" disabled>
							<option value="1"><?=$c['lang_pack']['mobile']['cpf'];?> (<?=$c['lang_pack']['mobile']['per_order'];?>)</option>
							<option value="2"><?=$c['lang_pack']['mobile']['cnpj'];?> (<?=$c['lang_pack']['mobile']['com_order'];?>)</option>
						</select>
					</div>
					<div class="blank10"></div>
					<input type="text" name="tax_code_value" id="taxCodeValue" class="box_input" maxlength="11" placeholder="<?=$c['lang_pack']['mobile']['cpf_code'];?>" disabled />
				</div>
			</div>
			<div class="rows" id="tariffCode" style="display:none;">
				<label class="field"><?=$c['lang_pack']['mobile']['per_vat_id'];?></label>
				<div class="input clean">
					<div class="box_select">
						<select name="tax_code_type" class="addr_select" id="tariffCodeOption" disabled>
							<option value="3"><?=$c['lang_pack']['mobile']['per_id_num'];?> (<?=$c['lang_pack']['mobile']['per_order'];?>)</option>
							<option value="4"><?=$c['lang_pack']['mobile']['vat_id_num'];?> (<?=$c['lang_pack']['mobile']['com_order'];?>)</option>
						</select>
					</div>
					<div class="blank10"></div>
					<input type="text" name="tax_code_value" id="tariffCodeValue" class="box_input" maxlength="14" placeholder="Personal or VAT ID" disabled />
				</div>
			</div>
			<div class="rows">
				<label class="field"><?=$c['lang_pack']['mobile']['zip_code'];?> <span class="fc_red">*</span></label>
				<div class="input clean"><input type="text" class="box_input" name="ZipCode" data-field="<?=$c['lang_pack']['mobile']['zip_code'];?>" notnull /><p class="error"></p></div>
			</div>
			<div class="rows">
				<label class="field"><?=$c['lang_pack']['mobile']['phone_num'];?> <span class="fc_red">*</span></label>
				<div class="input clean">
					<div class="box_input_group">
						<input id="countryCode" class="box_input input_group_addon" name="CountryCode" type="text" value="+0000" readonly />
                        <input type="text" class="box_input input_group" name="PhoneNumber" data-field="<?=$c['lang_pack']['mobile']['phone_num'];?>" notnull /><p class="error"></p>
					</div>
				</div>
			</div>
			<div class="address_button">
				<input type="submit" value="<?=$c['lang_pack']['mobile']['save_address'];?>" class="btn_global btn btn_save FontBgColor" id="save_address" />
				<input type="button" value="<?=$c['lang_pack']['mobile']['back'];?>" class="btn_global btn btn_back" id="btn_back" />
			</div>
			<input type="hidden" name="edit_address_id" value="<?=(int)$_GET['AId'];?>" id="addressId" />
			<input type="hidden" name="typeAddr" value="<?=!(int)$_SESSION['User']['UserId']?1:0;?>" />
			<input type="hidden" name="back_url" value="<?=$_SERVER['HTTP_REFERER'];?>" />
		</form>
		<script>
		$(function(){
			user_obj.set_default_address(<?=(int)$_GET['AId'];?>, <?=$_SESSION['Cart']['ShippingAddress']?1:0;?>);
		});
		</script>
    <?php }?>
</div>
