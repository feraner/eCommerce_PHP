<?php !isset($c) && exit();?>
<?=ly200::load_static("{$c['mobile']['tpl_dir']}js/user.js");?>
<form method="post" action="?">
    <input id="addressId" type="hidden" name="edit_address_id" value="0">
    <?php if(!(int)$_SESSION['User']['UserId']){?>
    <div class="addr_row">
        <div class="field"><?=$c['lang_pack']['mobile']['email'];?>:</div>
        <div class="input clean">
            <span class="input_span"><input type="email" class="input_text" name="Email" notnull="notnull" /></span>
        </div>
    </div>
    <?php }?>
    <div class="addr_row">
        <div class="field"><?=$c['lang_pack']['mobile']['your_name'];?>:</div>
        <div class="input clean">
            <span class="input_span fl whalf"><input type="text" class="input_text" placeholder="<?=$c['lang_pack']['mobile']['first_name'];?>" name="FirstName" notnull="notnull" /></span>
            <span class="input_span fr whalf"><input type="text" class="input_text" placeholder="<?=$c['lang_pack']['mobile']['last_name'];?>" name="LastName" notnull="notnull" /></span>
        </div>
    </div>
    <div class="addr_row">
        <div class="field"><?=$c['lang_pack']['mobile']['addr_line_1'];?>:</div>
        <div class="input clean">
            <span class="input_span"><input type="text" class="input_text" name="AddressLine1" notnull="notnull" /></span>
        </div>
    </div>
    <div class="addr_row">
        <div class="field"><?=$c['lang_pack']['mobile']['addr_line_2'];?>:</div>
        <div class="input clean">
            <span class="input_span"><input type="text" class="input_text" name="AddressLine2" /></span>
        </div>
    </div>
    <div class="addr_row">
        <div class="field"><?=$c['lang_pack']['mobile']['city'];?>:</div>
        <div class="input clean">
            <span class="input_span"><input type="text" name="City" class="input_text" notnull="notnull" /></span>
        </div>
    </div>
    <div class="addr_row">
        <div class="field"><?=$c['lang_pack']['mobile']['destin'];?>:</div>
        <div class="input clean">
            <select class="addr_select" name="country_id" id="country" notnull="notnull">
                <option value=""><?=$c['lang_pack']['mobile']['plz_country'];?></option>
                <?php
                $country_row=str::str_code(db::get_all('country', "IsUsed=1", '*', 'Country asc'));
                foreach($country_row as $v){
					$selected=((int)$address_row[0]['CId']?$address_row[0]['CId']==$v['CId']:$v['IsDefault']==1)?' selected="selected"':'';
                ?>
                    <option value="<?=$v['CId'];?>"<?=$selected;?>><?=$v['Country'];?></option>
                <?php }?>
            </select>
        </div>
    </div>
    <div class="addr_row" id="zoneId">
        <div class="field"><?=$c['lang_pack']['mobile']['state_oth'];?>:</div>
        <div class="input clean">
            <select class="addr_select" name="Province">
                <option value=""><?=$c['lang_pack']['mobile']['plz_sel'];?></option>
            </select>
        </div>
    </div>
    <div class="addr_row" id="state" style="display:none;">
        <div class="field"><?=$c['lang_pack']['mobile']['state_oth'];?>:</div>
        <div class="input clean">
            <span class="input_span"><input type="text" class="input_text" name="State" disabled="disabled" /></span>
        </div>
    </div>
    <div class="addr_row" id="taxCode" style="display:none;">
        <div class="field"><?=$c['lang_pack']['mobile']['cpf_code'];?>:</div>
        <div class="input clean">
            <select name="tax_code_type" class="addr_select" id="taxCodeOption" disabled="disabled">
                <option value="1"><?=$c['lang_pack']['mobile']['cpf'];?> (<?=$c['lang_pack']['mobile']['per_order'];?>)</option>
                <option value="2"><?=$c['lang_pack']['mobile']['cnpj'];?> (<?=$c['lang_pack']['mobile']['com_order'];?>)</option>
            </select>
            <div class="blank10"></div>
            <span class="input_span"><input type="text" name="tax_code_value" id="taxCodeValue" class="input_text" placeholder="<?=$c['lang_pack']['mobile']['cpf_code'];?>" disabled="disabled" /></span>
        </div>
    </div>
    <div class="addr_row" id="tariffCode" style="display:none;">
        <div class="field"><?=$c['lang_pack']['mobile']['per_vat_id'];?>:</div>
        <div class="input clean">
            <select name="tax_code_type" class="addr_select" id="tariffCodeOption" disabled="disabled">
                <option value="1"><?=$c['lang_pack']['mobile']['per_id_num'];?> (<?=$c['lang_pack']['mobile']['per_order'];?>)</option>
                <option value="2"><?=$c['lang_pack']['mobile']['vat_id_num'];?> (<?=$c['lang_pack']['mobile']['com_order'];?>)</option>
            </select>
            <div class="blank10"></div>
            <span class="input_span"><input type="text" name="tax_code_value" id="tariffCodeValue" class="input_text" placeholder="Personal or VAT ID" disabled="disabled" /></span>
        </div>
    </div>
    <div class="addr_row">
        <div class="field"><?=$c['lang_pack']['mobile']['zip_code'];?>:</div>
        <div class="input clean">
            <span class="input_span"><input type="text" class="input_text" name="ZipCode" notnull="notnull" /></span>
        </div>
    </div>
    
    <div class="addr_row">
        <div class="field"><?=$c['lang_pack']['mobile']['phone_num'];?>:</div>
        <div class="input clean">
            <span class="input_span fl" style="width:16%; background:#ddd;"><input id="countryCode" class="input_text" name="CountryCode" type="text" value="+0000" readonly /></span>
            <span class="input_span fr" style="width:80%;"><input type="text" class="input_text" name="PhoneNumber" notnull="notnull" /></span>
        </div>
    </div>
    
    <div class="addr_row">
        <div class="input clean">
        	<span class="input_btn global_btn" id="useAddress"><?=$c['lang_pack']['mobile']['save'];?></span>
            <?php if((int)$_SESSION['User']['UserId']){?>
            <span class="back" id="useAddressBack"><?=$c['lang_pack']['mobile']['back'];?></span>
            <?php }?>
        </div>
    </div>
    <input type="reset" id="resetAddr" style="display:none;">
    <input type="hidden" name="typeAddr" value="<?=!(int)$_SESSION['User']['UserId']?1:0;?>" />
</form>
