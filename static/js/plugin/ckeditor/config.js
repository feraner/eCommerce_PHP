/**
 * @license Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.md or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	config.filebrowserUploadUrl='/manage/?do_action=action.file_upload_ckeditor';
	config.filebrowserImageUploadUrl='/manage/?do_action=action.file_upload_ckeditor&file_type=img';
	config.filebrowserFlashUploadUrl='/manage/?do_action=action.file_upload_ckeditor&file_type=flash';
	config.toolbarCanCollapse=true;
	//config.resize_enabled=false;
	config.enterMode=CKEDITOR.ENTER_BR;
	config.fontSize_sizes='10px;12px;14px;15px;16px;18px;20px;22px;24px;28px;36px;48px;60px;72px';
	config.width='95%';
	config.height=400;
	//config.line_height="1em;1.2em;1.5em;1.8em;2em;2.3em;2.8em;3em;3.5em;4em;5em";
	config.line_height="120%;150%;180%;200%;250%;300%;350%;400%";
	config.font_names='Arial;Arial Black/arial black,Arial;Andale Mono/andale mono,Arial;Comic Sans Ms/comic sans ms,Arial;Times New Roman/Times New Roman,Arial;Cambria/Cambria,Arial;Calibri/Calibri,Arial;Courier New/Courier New,Arial;Verdana/Verdana,Arial;Georgia/Georgia,Arial;Tahoma/Tahoma,Arial;Times/Times,Arial;serif/serif,Arial;黑体/黑体,Arial;隶书/隶书,Arial;宋体/宋体,Arial;新宋体/新宋体,Arial;微软雅黑/微软雅黑,Arial;楷体_GB2312/楷体_GB2312,Arial';
	
	//基础工具栏
	config.toolbar='simple';
	config.toolbar_simple=[
		{ name: 'document', items: [ 'Source' ] },
		{ name: 'paragraph', items: [ 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
		{ name: 'links', items: [ 'Link', 'Unlink' ] },
		{ name: 'insert', items: [ 'ueeshopimage', 'Image' ] },
		{ name: 'styles', items: [ 'Font', 'FontSize', 'lineheight' ] },
		{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike'] }
	];
	
	//完整工具栏
	config.toolbar='full';
	config.toolbar_full=[
		{ name: 'document', items: [ 'Source', '-', /*'Save', */'NewPage', 'Preview', 'Print', '-', 'Templates' ] },
		{ name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', 'RemoveFormat', '-', 'Undo', 'Redo' ] },
		{ name: 'editing', items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
		/*{ name: 'forms', items: [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },*/
		{ name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
		'/',
		{ name: 'paragraph', items: [ /*'NumberedList', 'BulletedList', '-', */'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl', 'Language' ] },
		{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
		{ name: 'insert', items: [ 'ueeshopimage', 'Image', 'Flash', 'Table', 'Youtube', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe' ] },
		'/',
		{ name: 'styles', items: [ /*'Styles', */'Format', 'Font', 'FontSize', 'lineheight' ] },
		{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript' ] }
		/*,{ name: 'about', items: [ 'About' ] }*/
	];
	config.extraPlugins='ueeshopimage'; 
	config.allowedContent=true;
	config.pasteFromWordRemoveFontStyles=false;//不清除word文档格式
	config.pasteFromWordRemoveStyles=false;//不清除word文档格式
};