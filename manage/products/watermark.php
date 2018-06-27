<?php !isset($c) && exit();?>
<?php
manage::check_permit('products', 1, array('a'=>'watermark'));//检查权限

echo ly200::load_static('/static/js/plugin/operamasks/operamasks-ui.css', '/static/js/plugin/operamasks/operamasks-ui.min.js');
?>
<script type="text/javascript">$(document).ready(function(){products_obj.watermark_init();});</script>
<div class="r_nav">
	<h1>{/module.products.watermark/}</h1>
</div>
<div id="upload" class="r_con_wrap">
    <form id="edit_form" class="r_con_form">
		<h3 class="rows_hd">{/products.upload.upload_title/}</h3>
		<div class="rows">
			<label>{/products.classify/}</label>
			<span class="input"><?=category::ouput_Category_to_Select('CateId', '', 'products_category', 'UId="0,"', 1, 'notnull', '{/global.select_index/}');?></span>
			<div class="clear"></div>
		</div>
        <div class="rows">
            <label></label>
            <span class="input">
                <input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
                <input type="hidden" name="do_action" value="products.watermark_update" />
				<input type="hidden" name="Number" value="0" />
            </span>
            <div class="clear"></div>
        </div>
		
		<h3 class="rows_hd">{/products.watermark.progress/}</h3>
		<div id="explode_progress"></div>
    </form>
</div>