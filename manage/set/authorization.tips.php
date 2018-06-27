<?php !isset($c) && exit();?>
<?php
$Platform=$_GET['Platform'];
$Status=(int)$_GET['Status'];
$Account=$_GET['Account'];

if($Platform=='aliexpress'){
	$PlatformName='速卖通';
	if($Status==1) $tips=$PlatformName."授权成功！";
	elseif($Status==-1){
		$Name=db::get_value('authorization', "Platform='aliexpress' and Account='{$Account}'", 'Name');
		$tips=$PlatformName."授权失败，此账号已授权给店铺：{$Name}，不能重复授权！";
	}elseif($Status==-2) $tips=$PlatformName."授权失败，授权账号与保存账号不一致：{$Account}！";
}
?>
<style>
body, html{background:#fff;}
.authorization{width:680px; margin:20px auto; text-align:center; font-size:16px;}
.authorization .tips_box{padding:12px 0; text-align:center; line-height:32px;}
.authorization .close_box{padding-top:20px;}
.authorization .close_box span{color:#333;}
.authorization .close_box a.close{text-decoration:none; color:#09C;}
.authorization .error{color:#f00;}
</style>
<div class="authorization">
	<div class="tips_box"><?=$tips;?></div>
	<div class="close_box"><span>5</span> 秒后，该页面自动关闭...<a href="javascript:;" class="close">立即关闭</a></div>
</div>



<script type="text/javascript">
$(document).ready(function(){
	$('title').text('<?=$tips;?>');
	//自动保存店铺名称
	window.opener && window.opener.submitPlatformAuth && window.opener.submitPlatformAuth();
	
	function closeWindow(num){
		$('.authorization .close_box>span').text(num);
		if(num--==0){
			window.opener=null;
			window.open('','_self');
			window.close();
		}else{
			setTimeout(function(){closeWindow(num);}, 1000);
		}
	}
	$('.close').click(function(){closeWindow(0);});
	closeWindow(10);
	//setTimeout(function(){closeWindow();}, 5000);
	
});
</script>



















<?php exit;?>
