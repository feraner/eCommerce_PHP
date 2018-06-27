<?php
$pay_data=$data; //转换一下，防止其他地方已经调用这一变量

if(!$is_mobile){
?>
	<!doctype html>
	<html>
	<head>
	<meta charset="utf-8">
	<title><?=$title;?></title>
	<?php include("{$c['static_path']}/inc/static.php");?>
	</head>
	
	<body>
	<?php include("{$c['theme_path']}/inc/header.php");?>
	<div class="blank25"></div>
	<style>
	.qiwi{width:600px; margin:0 auto; padding:12px 0;}
	.qiwi .qiwi_logo{padding:12px 0; text-align:center; border-bottom:2px solid #999;}
	.qiwi .qiwi_rows{padding:20px 0 25px; overflow:hidden; border-bottom:1px solid #cacaca;}
	.qiwi .qiwi_rows>label{width:150px; height:24px; line-height:24px; padding-left:20px; overflow:hidden; font-size:16px; float:left;}
	.qiwi .qiwi_rows>span{width:425px; line-height:24px; float:right;}
	.qiwi .qiwi_rows>span select{height:24px;}
	.qiwi .qiwi_rows>span input{height:24px; line-height:24px; padding:2px 3px; border:1px solid #ddd;}
	.qiwi .qiwi_rows>span em{font-size:12px; color:#999;}
	.qiwi .qiwi_rows>span strong{font-size:16px; font-weight:bold;}
	.qiwi .qiwi_button{padding:16px 0; text-align:center;}
	.qiwi .qiwi_button button{display:inline-block; margin:0 auto; color:#333; font-size:15px; padding:.15em 2.5em; background:#fcfcfc; background:-webkit-gradient(linear,left top,left bottom,from(#fcfcfc),to(#dedede)); background:-moz-linear-gradient(top,#fcfcfc,#dedede); background:-o-linear-gradient(top,#fcfcfc,#dedede); background:-ms-linear-gradient(top,#fcfcfc,#dedede); background:linear-gradient(top,#fcfcfc,#dedede); filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#fcfcfc',endColorstr='#dedede',GradientType=0);	border:1px solid #ccc; border-top-color:#ccc; border-bottom-color:#aaa; min-height:45px; max-width:; white-space:normal; line-height:1.2; vertical-align:top; font-family:Arial, Helvetica, sans-serif; cursor:pointer;}
	.qiwi .qiwi_button button:hover, .qiwi .qiwi_button button:focus{box-shadow:0 0 7px #ccc,inset 0 1px 0 rgba(255,255,255,0.5);-webkit-box-shadow:0 0 7px #ccc,inset 0 1px 0 rgba(255,255,255,0.5);-moz-box-shadow:0 0 7px #ccc,inset 0 1px 0 rgba(255,255,255,0.5);-o-box-shadow:0 0 7px #ccc,inset 0 1px 0 rgba(255,255,255,0.5);}
	.qiwi .qiwi_button button:active{background:#fcfcfc;background:-webkit-gradient(linear,left top,left bottom,from(#dedede),to(#fcfcfc));background:-moz-linear-gradient(top,#dedede,#fcfcfc);background:-o-linear-gradient(top,#dedede,#fcfcfc);background:-ms-linear-gradient(top,#dedede,#fcfcfc);background:linear-gradient(top,#dedede,#fcfcfc);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#dedede',endColorstr='#fcfcfc',GradientType=0);border-color:#ccc;border-top-color:#ccc;border-bottom-color:#aaa;box-shadow:inset 0 -1px 0 rgba(255,255,255,0.5);-webkit-box-shadow:inset 0 -1px 0 rgba(255,255,255,0.5);-moz-box-shadow:inset 0 -1px 0 rgba(255,255,255,0.5);-o-box-shadow:inset 0 -1px 0 rgba(255,255,255,0.5)}
	</style>
<?php }else{?>
	<style>
	.qiwi{width:90%; margin:0 auto; padding:.75rem 0;}
	.qiwi .qiwi_logo{padding:.75rem 0; text-align:center; border-bottom:.125rem solid #999;}
	.qiwi .qiwi_rows{padding:.2rem 0; overflow:hidden; border-bottom:.0625rem solid #cacaca;}
	.qiwi .qiwi_rows>label{width:9.375rem; height:1.5rem; line-height:1.5rem; padding-left:20px; overflow:hidden; font-size:.75rem; display:block; margin-bottom:6px;}
	.qiwi .qiwi_rows>span{width:425px; line-height:1.5rem; padding-left:30px; display:inline-block;}
	.qiwi .qiwi_rows>span select{height:1.5rem; font-size:.75rem; margin-right:.3125rem;}
	.qiwi .qiwi_rows>span input{height:1.5rem; line-height:1.5rem; padding:.125rem .1875rem; border:.0625rem solid #ddd; font-size:.75rem;}
	.qiwi .qiwi_rows>span em{color:#999; display:block; margin-left:5rem; font-size:.75rem;}
	.qiwi .qiwi_rows>span strong{font-size:1rem; font-weight:bold;}
	.qiwi .qiwi_button{padding:1rem 0; text-align:center;}
	.qiwi .qiwi_button button{display:inline-block; margin:0 auto; color:#333; font-size:1rem; padding:.15rem 2.5rem; background:#fcfcfc; background:-webkit-gradient(linear,left top,left bottom,from(#fcfcfc),to(#dedede)); background:-moz-linear-gradient(top,#fcfcfc,#dedede); background:-o-linear-gradient(top,#fcfcfc,#dedede); background:-ms-linear-gradient(top,#fcfcfc,#dedede); background:linear-gradient(top,#fcfcfc,#dedede); filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#fcfcfc',endColorstr='#dedede',GradientType=0);	border:1px solid #ccc; border-top-color:#ccc; border-bottom-color:#aaa; min-height:2.8125rem; max-width:; white-space:normal; line-height:1.2; vertical-align:top; font-family:Arial, Helvetica, sans-serif; cursor:pointer;}
	.qiwi .qiwi_button button:hover, .qiwi .qiwi_button button:focus{box-shadow:0 0 .4375rem #ccc,inset 0 1px 0 rgba(255,255,255,0.5);-webkit-box-shadow:0 0 .4375rem #ccc,inset 0 1px 0 rgba(255,255,255,0.5);-moz-box-shadow:0 0 .4375rem #ccc,inset 0 1px 0 rgba(255,255,255,0.5);-o-box-shadow:0 0 .4375rem #ccc,inset 0 1px 0 rgba(255,255,255,0.5);}
	.qiwi .qiwi_button button:active{background:#fcfcfc;background:-webkit-gradient(linear,left top,left bottom,from(#dedede),to(#fcfcfc));background:-moz-linear-gradient(top,#dedede,#fcfcfc);background:-o-linear-gradient(top,#dedede,#fcfcfc);background:-ms-linear-gradient(top,#dedede,#fcfcfc);background:linear-gradient(top,#dedede,#fcfcfc);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#dedede',endColorstr='#fcfcfc',GradientType=0);border-color:#ccc;border-top-color:#ccc;border-bottom-color:#aaa;box-shadow:inset 0 -1px 0 rgba(255,255,255,0.5);-webkit-box-shadow:inset 0 -1px 0 rgba(255,255,255,0.5);-moz-box-shadow:inset 0 -1px 0 rgba(255,255,255,0.5);-o-box-shadow:inset 0 -1px 0 rgba(255,255,255,0.5)}
	</style>
<?php }?>

<!--main start-->
<div id="main" class="w">
	<div id="creditcart">
		<script type="text/javascript">
		$(function(){
			$.fn.numeral=function(){//文本框只能输入数字，并屏蔽输入法和粘贴
				$(this).css("ime-mode", "disabled");
				this.bind("keypress",function(e){
					var code=(e.keyCode?e.keyCode:e.which);//兼容火狐 IE
					if(!$.browser.msie&&(e.keyCode==0x8)){ return; }//火狐下不能使用退格键
					return code >= 48 && code<= 57;     
				});     
				this.bind("blur", function(){     
					if(this.value.lastIndexOf(".")==(this.value.length - 1)){     
						this.value = this.value.substr(0, this.value.length - 1);     
					}else if(isNaN(this.value)){     
						this.value = "";     
					}     
				});     
				this.bind("paste", function(){
					var s = clipboardData.getData('text');
					if (!/\D/.test(s));
					value = s.replace(/^0*/, '');
					return false;     
				});     
				this.bind("dragenter", function(){ return false; });     
				this.bind("keyup", function(){
					if(/(^0+)/.test(this.value)){
						this.value=this.value.replace(/^0*/, '');
						}
					}
				);     
			};   
			$('form').submit(function(){if(global_obj.check_form($(this).find('*[notnull]'))){return false;}});
			$("input[name=qiwiUsername]").numeral();
		});  
		</script>
		<div class="qiwi">
			<form action="?" method="post">
				<div class="qiwi_logo"><img src="/static/themes/default/images/cart/globebill/qiwi.png" /></div>
				<div class="qiwi_rows">
					<label>Mobile Phone:</label>
					<span>
						<select name="qiwiCountryCode" notnull>
							<?php foreach($country_code_ary as $k=>$v){?>
								<option value="<?=$v;?>" data="<?=$k;?>">+<?=$v;?></option>
							<?php }?>
						</select>
						<input type="text" name="qiwiUsername" class="form_input" value="" placeholder="9057772233" maxlength="15" notnull />
						<em>(e.g. 9057772233)</em>
					</span>
					<div class="clear"></div>
				</div>
				<div class="qiwi_rows">
					<label>Subtotal:</label>
					<span><strong><?=$pay_data['total_price'].' '.$pay_data['order_row']['Currency'];?></strong></span>
					<div class="clear"></div>
				</div>
				<div class="qiwi_button"><button type="submit">Pay Now</button></div>
			</form>
		</div>
    </div>
</div>
<div class="blank25"></div>
<div class="blank25"></div>
<?php include("{$c['theme_path']}/inc/footer.php");?>
</body>
</html>
