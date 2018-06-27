<?php
manage::check_permit('products', 1, array('a'=>'aliexpress'));//检查权限
?>
<div class="r_nav">
	<h1>{/module.products.aliexpress/}</h1>
</div>
<div id="aliexpress" class="r_con_wrap">
	<?php
	aliexpress::get_aliexpress_api_info();
	if($_SESSION['Manage']['Aliexpress']['Token']['time']+1800<$c['time']){
		unset($_SESSION['Manage']['Aliexpress']['Token']);
	}
	if(!$_SESSION['Manage']['Aliexpress']['Token']){
		if($_GET['code']){
			//print_r($_GET);
			/*echo $_GET['code'];exit;*/
			$token=aliexpress::get_access_token($_GET['code']);
			//print_r($token);
			if(is_array($token) && $token['access_token']){
				$_SESSION['Manage']['Aliexpress']['Token']=$token;
				$_SESSION['Manage']['Aliexpress']['Token']['time']=$c['time'];
			}
			js::location('./?m=products&a=aliexpress', '', '.top');
		}else{
		?>
        	<iframe frameborder="0" src="<?=aliexpress::authhz_url();?>"></iframe>
        <?php
		}
	}else{
	?>
		<script language="javascript">$(document).ready(products_obj.aliexpress_sync_init);</script>
        <input type="button" class="btn_ok" value="{/products.aliexpress.beign/}" />
        <div class="blank12"></div>
        <div class="sync"></div>
    <?php }?>
</div>