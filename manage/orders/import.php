<?php !isset($c) && exit();?>
<?php
manage::check_permit('orders', 1, array('a'=>'import'));//检查权限
echo ly200::load_static('/static/js/plugin/operamasks/operamasks-ui.css', '/static/js/plugin/operamasks/operamasks-ui.min.js');
?>
<script src="/static/js/plugin/file_upload/js/vendor/jquery.ui.widget.js"></script>
<script src="/static/js/plugin/file_upload/js/external/tmpl.js"></script>
<script src="/static/js/plugin/file_upload/js/external/load-image.js"></script>
<script src="/static/js/plugin/file_upload/js/external/canvas-to-blob.js"></script>
<script src="/static/js/plugin/file_upload/js/external/jquery.blueimp-gallery.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.iframe-transport.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.fileupload.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.fileupload-process.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.fileupload-image.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.fileupload-audio.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.fileupload-video.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.fileupload-validate.js"></script>
<script src="/static/js/plugin/file_upload/js/jquery.fileupload-ui.js"></script>
<!--[if (gte IE 8)&(lt IE 10)]><script src="/static/js/plugin/file_upload/js/cors/jquery.xdr-transport.js"></script><![endif]-->
<script type="text/javascript">$(document).ready(function(){orders_obj.orders_import_init()});</script>
<div class="r_nav">
	<h1>{/module.orders.export/}</h1>
</div>
<div id="orders" class="r_con_wrap">
	<form class="r_con_form" name="import_form" action="//jquery-file-upload.appspot.com/" method="POST" enctype="multipart/form-data">
		<h3 class="rows_hd">{/orders.export.import/}</h3>
		<div class="rows">
            <label>{/products.upload.excel_format/}</label>
            <span class="input"><a href="./?do_action=orders.import_excel_download" class="btn_ok">{/products.upload.download/}</a></span>
            <div class="clear"></div>
        </div>
		<div class="rows">
			<label>{/products.upload.excel_file/}</label>
			<span class="input upload_file">
				<input name="ExcelFile" value="" type="text" class="form_input" id="excel_path" size="50" maxlength="100" readonly notnull />
                <div class="blank6"></div>
				<noscript><input type="hidden" name="redirect" value="https://blueimp.github.io/jQuery-File-Upload/"></noscript>
				<div class="row fileupload-buttonbar">
					<span class="btn_file btn-success fileinput-button">
						<i class="glyphicon glyphicon-plus"></i>
						<span>{/global.file_upload/}</span>
						<input type="file" name="Filedata" multiple>
					</span>
					<div class="fileupload-progress fade"><div class="progress-extended">&nbsp;</div></div>
					<div class="clear"></div>
					<div class="photo_multi_img template-box files"></div>
					<div class="photo_multi_img" id="PicDetail"></div>
				</div>
				<script id="template-upload" type="text/x-tmpl">
				{% for (var i=0, file; file=o.files[i]; i++) { %}
					<div class="template-upload fade">
						<div class="clear"></div>
						<div class="items">
							<p class="name">{%=file.name%}</p>
							<strong class="error text-danger"></strong>
						</div>
						<div class="items">
							<p class="size">Processing...</p>
							<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>
						</div>
						<div class="items">
							{% if (!i) { %}
								<button class="btn_file btn-warning cancel">
									<i class="glyphicon glyphicon-ban-circle"></i>
									<span>{/global.cancel/}</span>
								</button>
							{% } %}
						</div>
						<div class="clear"></div>
					</div>
				{% } %}
				</script>
				<script id="template-download" type="text/x-tmpl">
				{% for (var i=0, file; file=o.files[i]; i++) { %}
					{% if (file.thumbnailUrl) { %}
						<div class="pic template-download fade hide">
							<div>
								<a href="javascript:;" title="{%=file.name%}" download="{%=file.name%}" data-gallery><img src="{%=file.thumbnailUrl%}" /><em></em></a>
								<a href="{%=file.url%}" class="zoom" target="_blank"></a>
								{% if (file.deleteUrl) { %}
									<button class="btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>{/global.del/}</button>
									<input type="checkbox" name="delete" value="1" class="toggle" style="display:none;">
								{% } %}
								<input type="hidden" name="PicPath[]" value="{%=file.url%}" disabled />
							</div>
							<input type="text" maxlength="30" class="form_input" value="{%=file.name%}" name="Name[]" placeholder="'+lang_obj.global.picture_name+'" disabled notnull />
						</div>
					{% } else { %}
						<div class="template-download fade hide">
							<div class="clear"></div>
							<div class="items">
								<p class="name">
									{% if (file.url) { %}
										<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'data-gallery':''%}>{%=file.name%}</a>
									{% } else { %}
										<span>{%=file.name%}</span>
									{% } %}
								</p>
								{% if (file.error) { %}
									<div><span class="label label-danger">Error</span> {%=file.error%}</div>
								{% } %}
							</div>
							<div class="items">
								<span class="size">{%=o.formatFileSize(file.size)%}</span>
							</div>
							<div class="items">
								{% if (file.deleteUrl) { %}
									<button class="btn_file btn-danger delete" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}>
										<i class="glyphicon glyphicon-trash"></i>
										<span>{/global.del/}</span>
									</button>
									<input type="checkbox" name="delete" value="1" class="toggle" style="display:none;">
								{% } else { %}
									<button class="btn_file btn-warning cancel">
										<i class="glyphicon glyphicon-ban-circle"></i>
										<span>{/global.cancel/}</span>
									</button>
								{% } %}
							</div>
							<div class="clear"></div>
						</div>
					{% } %}
				{% } %}
				</script>
			</span>
			<div class="clear"></div>
		</div>
        <div class="rows">
            <label></label>
            <span class="input">
                <input type="submit" name="submit_button" value="{/global.submit/}" class="btn_ok submit_btn" />
                <input type="hidden" name="do_action" value="orders.orders_import" />
				<input type="hidden" name="Number" value="0" />
            </span>
            <div class="clear"></div>
        </div>
		
		<h3 class="rows_hd">{/products.upload.progress/}</h3>
		<div id="explode_progress_import"></div>
    </form>
</div>