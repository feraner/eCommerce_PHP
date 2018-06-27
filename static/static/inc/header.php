<?php !isset($c) && exit();?>
<?=ly200::set_custom_style();?>
<script type="text/javascript">
	$(window).resize(function(){$(window).webDisplay(<?=$c['config']['global']['WebDisplay']?>);});
	$(window).webDisplay(<?=$c['config']['global']['WebDisplay']?>);
	<?php if($c['config']['global']['IsCopy']){?>
		var omitformtags=["input","textarea", "select"];//过滤掉的标签
		omitformtags=omitformtags.join("|")
		function disableselect(e){
			var e=e || event;//IE 中可以直接使用 event 对象 ,FF e
			var obj=e.srcElement ? e.srcElement : e.target;//在 IE 中 srcElement 表示产生事件的源,FF 中则是 target
			if(omitformtags.indexOf(obj.tagName.toLowerCase())==-1){
				if(e.srcElement) document.onselectstart=new Function ("return false");//IE
				return false;
			}else{
				if(e.srcElement) document.onselectstart=new Function ("return true");//IE
				return true;  
			} 
		}
		function reEnable(){
			return true
		}
		
		document.onmousedown=disableselect;//按下鼠标上的设备(左键,右键,滚轮……)
		document.onmouseup=reEnable;//设备弹起
		document.oncontextmenu=new Function("event.returnValue=false;");
		document.onselectstart=new Function("event.returnValue=false;");
		document.oncontextmenu=function(e){return false;};//屏蔽鼠标右键
	<?php }?>
	<?php
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
<?php
if((int)$c['config']['global']['IsNotice'] && !($m=='products' && $a=='custom') && $m!='holiday' && !($m=='cart' && ($a=='checkout' || $a=='buynow'))){ //通告栏
	$notice_ary=str::json_data(db::get_value('config', "GroupId='global' and Variable='Notice'", 'Value'), 'decode');
	if(trim($notice_ary['Notice'.$c['lang']])){
?>
		<div id="top_banner">
			<div class="wide">
				<a href="javascript:;" class="top_banner_close" rel="nofollow"></a>
				<?=$notice_ary['Notice'.$c['lang']];?>
			</div>
		</div>
<?php
	}
}?>

