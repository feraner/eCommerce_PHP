<?php !isset($c) && exit();?>
<?=ly200::set_custom_style();?>
<style>body{ background-image:none;}</style>
<script type="text/javascript">
<?php if($c['config']['global']['IsCopy']){?>document.oncontextmenu=new Function("event.returnValue=false;");document.onselectstart=new Function("event.returnValue=false;");<?php }?>
<?php
$logo_path=str::str_code(db::get_value('config', "GroupId='print' and Variable='LogoPath'", 'Value'));
$FbData=$c['config']['Platform']['Facebook']['SignIn']['Data'];
$payment_row=db::get_one('payment', "PId=2 and IsUsed=1", 'IsOnline, Method, Attribute'); //Paypal快捷支付
$payment_row && $account=str::json_data($payment_row['Attribute'], 'decode');
?>
var ueeshop_config={
	"domain":"<?=ly200::get_domain();?>",
	"date":"<?=date('Y/m/d H:i:s', $c['time']);?>",
	"lang":"<?=substr($c['lang'], 1);?>",
	"currency":"<?=$_SESSION['Currency']['Currency'];?>",
	"currency_symbols":"<?=$_SESSION['Currency']['Symbol'];?>",
	"currency_rate":"<?=$_SESSION['Currency']['Rate'];?>",
	"FbAppId":"<?=$FbData?$FbData['appId']:'';?>",
	"FbPixelOpen":"<?=(int)$c['config']['Platform']['Facebook']['Pixel']['IsUsed'];?>",
	"UserId":"<?=(int)$_SESSION['User']['UserId'];?>",
	"TouristsShopping":"<?=(int)$c['config']['global']['TouristsShopping'];?>",
	"PaypalExcheckout":"<?=$acount['Username'];?>"
}
</script>
<div class="header">
    <div class="logo fl pic_box"><a href="/"><img src="<?=$logo_path?$logo_path:db::get_value('config', "GroupId='global' and Variable='LogoPath'", 'Value');?>" /><span></span></a></div>
    <div class="user_language fr"><i></i><?php include("{$c['static_path']}/inc/language.php");?></div>
</div>