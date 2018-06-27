<?php !isset($c) && exit();?>
<?php
if((int)$_GET['module']==1){//弹窗显示
?>
	<div id="binding_module">
		<div class="box_bg"></div>
		<a class="noCtrTrack" id="binding_close">×</a>
		<div id="lb-wrapper">
			<form class="login" method="post" action="/">
				<div class="title">Congratulations, you have completed <?=$_SESSION['Oauth']['User']['Type'];?> authorization.</div>
				<div class="provide">To finish the login process, please provide email address.</div>
				<div id="error_login_box" class="error_note_box">Incorrect email address. Please try again.</div>
				<div class="row">
					<label for="Email">Email Address:</label>
					<input name="Email" class="lib_txt" type="text" maxlength="100" size="43" format="Email" notnull />
					<div class="note">*Please note that all important emails will be sent to this email address.</div>
				</div>
				<div class="row">
					<button class="signbtn signin FontBgColor FontBorderColor" type="submit">Submit</button>
					<a href="javascript:;" class="signbtn signup" id="btn_cannel">Cannel</a>
				</div>
				<input type="hidden" name="Type" value="<?=$_SESSION['Oauth']['User']['Type'];?>" />
				<input type="hidden" name="do_action" value="user.user_oauth_binding" />
			</form>
		</div>
	</div>
<?php
}else{//页面显示
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<?=ly200::seo_meta();?>
	<?php include("{$c['static_path']}/inc/static.php");?>
	</head>
	
	<body>
	<div id="customer" class="binding">
		<?php include("include/header.php");?>
		<style>
		#binding_module{width:440px; margin:50px auto; position:relative; top:0; box-shadow:0 0 20px #ccc; -webkit-box-shadow:0 0 20px #ccc; -moz-box-shadow:0 0 20px #ccc;}
		#binding_module .box_bg{box-shadow:0 0 20px #eee; -webkit-box-shadow:0 0 20px #eee; -moz-box-shadow:0 0 20px #eee;}
		#binding_module .noCtrTrack{display:none;}
		#binding_module #lb-wrapper .signup{display:none;}
		</style>
		<script type="text/javascript">$(document).ready(function(){user_obj.user_login_binding()});</script>
		<div id="binding_module">
			<div class="box_bg"></div>
			<a class="noCtrTrack" id="binding_close">×</a>
			<div id="lb-wrapper">
				<form class="login" method="post" action="/">
					<div class="title">Congratulations, you have completed <?=$_SESSION['Oauth']['User']['Type'];?> authorization.</div>
					<div class="provide">To finish the login process, please provide email address.</div>
					<div id="error_login_box" class="error_note_box">Incorrect email address. Please try again.</div>
					<div class="row">
						<label for="Email">Email Address:</label>
						<input name="Email" class="lib_txt" type="text" maxlength="100" size="43" format="Email" notnull />
						<div class="note">*Please note that all important emails will be sent to this email address.</div>
					</div>
					<div class="row">
						<button class="signbtn signin FontBgColor FontBorderColor" type="submit">Submit</button>
						<a href="javascript:;" class="signbtn signup" id="btn_cannel">Cannel</a>
					</div>
					<input type="hidden" name="Type" value="<?=$_SESSION['Oauth']['User']['Type'];?>" />
					<input type="hidden" name="do_action" value="user.user_oauth_binding" />
				</form>
			</div>
		</div>
		<?php include("include/footer.php");?>
	</div>
	</body>
	</html>
<?php }?>