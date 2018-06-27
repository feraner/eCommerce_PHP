<?php !isset($c) && exit();?>
<?php
//manage::check_permit('products', 1, array('a'=>'upload'));//检查权限

echo ly200::load_static('/static/js/plugin/operamasks/operamasks-ui.css', '/static/js/plugin/operamasks/operamasks-ui.min.js');
?>
<script type="text/javascript">$(document).ready(function(){products_obj.upload_init();});</script>
<div class="r_nav">
	<h1>{/module.products.upload/}</h1>
</div>
<div id="upload" class="r_con_wrap">
    <form id="edit_form" class="r_con_form">
		<h3 class="rows_hd">{/products.upload.upload_title/}<a class="old_version" href="./?m=products&a=upload_new">{/global.new_version/}</a></h3>
        <div class="rows">
            <label>{/products.upload.excel_file/}</label>
            <span class="input upload_file">
            	<input name="ExcelFile" value="" type="text" class="form_input" id="excel_path" size="50" maxlength="100" readonly notnull />
                <div class="blank6"></div>
            	<div><input name="ExcelUpload" id="ExcelUpload" type="file">{/products.upload.upload_tips/}</div>
			</span>
            <div class="clear"></div>
        </div>
        <div class="rows">
            <label>{/products.upload.excel_format/}</label>
            <span class="input"><a href="./?do_action=products.upload_excel_download" class="btn_ok">{/products.upload.download/}</a></span>
            <div class="clear"></div>
        </div>
        <div class="rows">
            <label></label>
            <span class="input">
                <input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
                <input type="hidden" name="do_action" value="products.upload" />
				<input type="hidden" name="Number" value="0" />
            </span>
            <div class="clear"></div>
        </div>
		
		<h3 class="rows_hd">{/products.upload.progress/}</h3>
		<div id="explode_progress"></div>
    </form>
</div>