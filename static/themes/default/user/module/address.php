<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

$ship_row=str::str_code(db::get_all('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', 'a.'.$c['where']['user']." and a.IsBillingAddress=0", 'a.*, c.Country, c.CountryData, s.States as StateName', 'a.AccTime desc, a.AId desc'));
$ship_len=count($ship_row);

$bill_row=str::str_code(db::get_one('user_address_book a left join country c on a.CId=c.CId left join country_states s on a.SId=s.SId', 'a.'.$c['where']['user']." and a.IsBillingAddress=1", 'a.*, c.Country, c.CountryData, s.States as StateName'));

$bill_country=$bill_row['Country'];
if($c['lang']!='_en'){
	$bill_country_data=str::json_data(htmlspecialchars_decode($bill_row['CountryData']), 'decode');
	$bill_country=$bill_country_data[substr($c['lang'], 1)];
}

count($ship_row) && $Default_AId=$_SESSION['Cart']['ShippingAddressAId']?$_SESSION['Cart']['ShippingAddressAId']:$ship_row[0]['AId'];
?>
<script type="text/javascript">$(document).ready(function(){user_obj.address_init()});</script>
<div id="lib_user_address" class="index_pro_list clearfix">
    <div id="user_heading" class="fl">
        <h2><?=$c['lang_pack']['user']['addressTitle'];?></h2>
    </div>
    <div class="clear"></div>
    <div class="address_menu">
        <ul class="menu_title">
            <li class="shipping"><a href="javascript:;" hidefocus="true" class="current FontBorderColor"><?=$c['lang_pack']['user']['shipping'];?></a></li>
            <li class="billing"><a href="javascript:;" hidefocus="true"><?=$c['lang_pack']['user']['billing'];?></a></li>
            <?php if(count($ship_row)<5){?>
            	<li class="add"><a href="javascript:;" hidefocus="true"><?=$c['lang_pack']['user']['add_shipping'];?></a></li>
            <?php }?>
        </ul>
        <div class="menu_content">
            <div class="menu">
            	<div class="address_list shipping_addr">
                	<?php
						foreach((array)$ship_row as $k => $v){
							$country=$v['Country'];
							if($c['lang']!='_en'){
								$country_data=str::json_data(htmlspecialchars_decode($v['CountryData']), 'decode');
								$country=$country_data[substr($c['lang'], 1)];
							}
					?>
                	<div class="add_item" <?=$k%2==0?'style="margin-left:0; clear:both;"':''?>>
                    	<div class="rows"><strong><?=$v['FirstName'].' '.$v['LastName'];?></strong><?=$Default_AId==$v['AId']?"<em>{$c['lang_pack']['mobile']['default']}</em>":''?></div>
                        <div class="rows"><?=$v['AddressLine1'];?></div>
                        <?php if($v['AddressLine2']){?>
                        <div class="rows"><?=$v['AddressLine2'];?></div>
                        <?php }?>
                        <div class="rows"><?=$v['City'];?> <?=$country;?> <?=($v['StateName']?$v['StateName']:$v['State']);?> (<?=$v['ZipCode'];?>)</div>
                        <div class="rows">+<?=$v['CountryCode'];?> <?=$v['PhoneNumber'];?></div>
                        <div class="options">
							<div class="user_action_down">
								<a href="javascript:;" name="edit" data-addrid="<?=$v['AId'];?>"><?=$c['lang_pack']['user']['edit'];?></a>
								<i></i>
								<ul>
									<li><a href="/account/address/remove<?=sprintf('%04d', $v['AId']);?>.html" name="del" data-addrid="<?=$v['AId'];?>"><?=$c['lang_pack']['user']['delete'];?></a></li>
									<li><a href="javascript:;" name="default" data-addrid="<?=$v['AId'];?>"><?=$c['lang_pack']['mobile']['default'];?></a></li>
                                </ul>
							</div>
                        </div>
                    </div>
                    <?php }?>
                </div>
            </div>
            <div class="menu hide">
            	<div class="address_list billing_addr">
                	<div class="add_item" style="margin-left:0;">
                    	<div class="rows"><strong><?=$bill_row['FirstName'].' '.$bill_row['LastName'];?></strong></div>
                        <div class="rows"><?=$bill_row['AddressLine1'];?></div>
                        <?php if($bill_row['AddressLine2']){?>
                        <div class="rows"><?=$bill_row['AddressLine2'];?></div>
                        <?php }?>
                        <div class="rows"><?=$bill_row['City'];?> <?=$bill_country;?> <?=($bill_row['StateName']?$bill_row['StateName']:$bill_row['State']);?> (<?=$bill_row['ZipCode'];?>)</div>
                        <div class="rows">+<?=$bill_row['CountryCode'];?> <?=$bill_row['PhoneNumber'];?></div>
                        <div class="options">
							<div class="user_action_down">
								<a href="javascript:;" name="edit" data-addrid="<?=$bill_row['AId'];?>"><?=$c['lang_pack']['user']['edit'];?></a>
							</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="addressForm"><?php include('shippingAddress.php');?></div>
    </div>
</div>