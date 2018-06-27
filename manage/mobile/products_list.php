<?php !isset($c) && exit();?>
<?php
$set_ary=array();
$set_row=db::get_all('config', "GroupId='mobile'");
foreach($set_row as $v){
	$set_ary[$v['Variable']]=$v['Value'];
}
$tpl_dir=$c['mobile']['tpl_dir'].'products/';//模板目录
$base_dir=$c['root_path'].$tpl_dir;//邮件模板目录绝对路径
$handle=@opendir($base_dir);
?>
<script language="javascript">$(document).ready(function(){mobile_obj.products_list_edit_init();});</script>
<div class="r_nav">
	<h1>{/module.mobile.products_list/}</h1>
</div>
<div id="mobile_products_list" class="r_con_wrap">
	<form id="edit_form" class="r_con_form">
		<div class="temp_list clean">
			<?php
			while($tpl=readdir($handle)){
				if($tpl!='.' && $tpl!='..' && is_dir($base_dir.$tpl)){
					$tpl_img=$tpl_dir.$tpl.'/cover.jpg';//封面
				?>
					<div class="item fl <?=$set_ary['ListTpl']==$tpl?'on':'';?>" data-tpl="<?=$tpl;?>">
						<div class="img"><img src="<?php echo $tpl_img;?>" /><div class="icon"></div></div>
					</div>
				<?php 
				}
			}
			closedir($handle);
			?>
		</div>
		<div class="rows">
			<label></label>
			<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
			<div class="clear"></div>
		</div>
        <input type="hidden" name="tpl" value="<?=$set_ary['ListTpl'];?>">
        <input type="hidden" name="do_action" value="mobile.products_list_edit">
    </form>
</div>