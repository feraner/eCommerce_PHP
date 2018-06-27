<?php !isset($c) && exit();?>
<?php
manage::check_permit('set', 1, array('a'=>'photo'));//检查权限

$out=0;
$open_ary=array();
foreach($c['manage']['permit']['pc']['set']['photo']['menu'] as $k=>$v){
	if(!manage::check_permit('set', 0, array('a'=>'photo', 'd'=>$v))){
		if($v=='photo' && $c['manage']['do']=='index') $out=1;
		continue;
	}else{
		$v=='photo' && $v='index';
		$open_ary[]=$v;
	}
}
if($out) js::location('?m=set&a=photo&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面

if($c['manage']['do']=='index' || $c['manage']['do']=='photo_upload' || $c['manage']['do']=='category' || $c['manage']['do']=='category_edit' || $c['manage']['do']=='choice'){
	$CateId=0;
	$IsSystem='';
	$CateId=$_GET['CateId'];
	$CateMenu=$_GET['CateMenu'];
	if($CateMenu){
		$CateMenu=explode(':', $CateMenu);
		if($CateMenu[0]=='IsSystem'){
			$IsSystem=$CateMenu[1];
		}elseif($CateMenu[0]=='CateId'){
			$CateId=(int)$CateMenu[1];
		}
	}
	if($CateId){
		$category_one=str::str_code(db::get_one('photo_category', "CateId='$CateId'"));
		!$category_one && js::location('./?m=set&a=photo');
		$UId=$category_one['UId'];
		$UId!='0,' && $TopCateId=category::get_top_CateId_by_UId($UId);
		$column=$category_one['Category'];
	}
	if(in_array($IsSystem, $c['manage']['photo_type'])){
		$column="{/set.photo.SystemType.{$IsSystem}/}";
	}
	
	$ParentId=(int)$_GET['ParentId'];
}

$permit_ary=array(
	'add'		=>	manage::check_permit('set', 0, array('a'=>'photo', 'd'=>'category', 'p'=>'add')),
	'edit'		=>	manage::check_permit('set', 0, array('a'=>'photo', 'd'=>'category', 'p'=>'edit')),
	'del'		=>	manage::check_permit('set', 0, array('a'=>'photo', 'd'=>'category', 'p'=>'del')),
	'photo_add'	=>	manage::check_permit('set', 0, array('a'=>'photo', 'd'=>'photo', 'p'=>'add')),
	'photo_edit'=>	manage::check_permit('set', 0, array('a'=>'photo', 'd'=>'photo', 'p'=>'edit')),
	'photo_del'	=>	manage::check_permit('set', 0, array('a'=>'photo', 'd'=>'photo', 'p'=>'del'))
);

echo ly200::load_static('/static/js/plugin/operamasks/operamasks-ui.css', '/static/js/plugin/operamasks/operamasks-ui.min.js');

if($c['manage']['do']=='index' || $c['manage']['do']=='choice'){
?>
	<script src="/static/js/plugin/file_upload/js/vendor/jquery.ui.widget.js"></script>
	<script src="/static/js/plugin/file_upload/js/external/tmpl.js"></script>
	<script src="/static/js/plugin/file_upload/js/external/load-image.js"></script>
	<script src="/static/js/plugin/file_upload/js/external/canvas-to-blob.js"></script>
	<script src="/static/js/plugin/file_upload/js/external/jquery.blueimp-gallery.js"></script>
	<?php /* 2017.01.05
	<script src="//blueimp.github.io/JavaScript-Templates/js/tmpl.min.js"></script>
	<script src="//blueimp.github.io/JavaScript-Load-Image/js/load-image.all.min.js"></script>
	<script src="//blueimp.github.io/JavaScript-Canvas-to-Blob/js/canvas-to-blob.min.js"></script>
	<script src="//blueimp.github.io/Gallery/js/jquery.blueimp-gallery.min.js"></script>
	<script src="/static/js/plugin/file_upload/js/jquery.iframe-transport.js"></script>
	*/ ?>
	<script src="/static/js/plugin/file_upload/js/jquery.iframe-transport.js"></script>
	<script src="/static/js/plugin/file_upload/js/jquery.fileupload.js"></script>
	<script src="/static/js/plugin/file_upload/js/jquery.fileupload-process.js"></script>
	<script src="/static/js/plugin/file_upload/js/jquery.fileupload-image.js"></script>
	<script src="/static/js/plugin/file_upload/js/jquery.fileupload-audio.js"></script>
	<script src="/static/js/plugin/file_upload/js/jquery.fileupload-video.js"></script>
	<script src="/static/js/plugin/file_upload/js/jquery.fileupload-validate.js"></script>
	<script src="/static/js/plugin/file_upload/js/jquery.fileupload-ui.js"></script>
	<!--[if (gte IE 8)&(lt IE 10)]><script src="/static/js/plugin/file_upload/js/cors/jquery.xdr-transport.js"></script><![endif]-->
<?php }?>
<div class="r_nav">
	<?php if($c['manage']['do']!='choice'){?><h1><?=$c['manage']['do']=='move'?'{/global.move_to/}':'{/module.set.photo.module_name/}';?></h1><?php }?>
	<div class="turn_page"></div>
	<?php if($c['manage']['do']=='index'){?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<div class="ext">
					<div class="rows">
						<label>{/products.classify/}</label>
						<span class="input">
							<select name="CateMenu">
								<option value="">{/global.select_index/}</option>
								<?php
								foreach($c['manage']['photo_type'] as $k=>$v){	//系统图片分类
								?>
									<option value="IsSystem:<?=$v;?>"<?=$IsSystem==$v?' selected':'';?>>├{/set.photo.SystemType.<?=$v;?>/}</option>
								<?php
								}
								$photo_category=str::str_code(db::get_all('photo_category', '1', '*', 'CateId asc'));
								$allcate_ary=array();
								foreach($photo_category as $k=>$v) $allcate_ary[$v['UId']][]=$v;
								foreach((array)$allcate_ary['0,'] as $v){
								?>
									<option value="CateId:<?=$v['CateId'];?>"<?=$CateId==$v['CateId']?' selected':'';?>>├<?=$v['Category'];?></option>
									<?php
									if($v['SubCateCount']){
										$len=count($allcate_ary["0,{$v['CateId']},"]);
										foreach((array)$allcate_ary["0,{$v['CateId']},"] as $v2){
									?>
										<option value="CateId:<?=$v2['CateId'];?>"<?=$CateId==$v2['CateId']?' selected':'';?>><?=$len>1?'｜├':'｜└'?><?=$v2['Category'];?></option>
								<?php
										}
									}
								}?>
							</select>
						</span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="set" />
				<input type="hidden" name="a" value="photo" />
			</form>
		</div>
		<ul class="ico">
			<?php if($permit_ary['photo_add']){?>
				<li><a class="tip_ico_down add" href="./?m=set&a=photo&d=add" label="{/global.add/}"></a></li>
			<?php }?>
			<?php if($permit_ary['photo_edit']){?>
				<li><a class="tip_ico_down bat_open" href="javascript:;" label="{/global.select_all/}"></a></li>
				<li><a class="tip_ico_down move" href="javascript:;" label="{/set.photo.move_bat/}"></a></li>
			<?php }?>
			<?php if($permit_ary['photo_del']){?>
				<li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li>
				<li><a class="tip_ico_down clears" href="javascript:;" label="{/set.photo.clear_tmp/}"></a></li>
			<?php }?>
		</ul>
	<?php
	}elseif($c['manage']['do']=='choice'){
		$obj=$_GET['obj'];
		$save=$_GET['save'];
		$id=$_GET['id'];//元素ID，可以是编译器，div等等。。
		$maxpic=(int)$_GET['maxpic'];//最大允许图片数，0为没有限制，1为单张上传
		$type=$_GET['type'];//记录根据那种类型尺寸压缩图片，例如products，info
	?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<div class="ext">
					<div class="rows">
						<label>{/products.classify/}</label>
						<span class="input">
							<select name="CateMenu">
								<option value="">{/global.select_index/}</option>
								<?php
								foreach($c['manage']['photo_type'] as $k=>$v){	//系统图片分类
								?>
									<option value="IsSystem:<?=$v;?>"<?=$IsSystem==$v?' selected':'';?>>├{/set.photo.SystemType.<?=$v;?>/}</option>
								<?php
								}
								$photo_category=str::str_code(db::get_all('photo_category', '1', '*', 'CateId asc'));
								$allcate_ary=array();
								foreach($photo_category as $k=>$v) $allcate_ary[$v['UId']][]=$v;
								foreach((array)$allcate_ary['0,'] as $v){
								?>
									<option value="CateId:<?=$v['CateId'];?>"<?=$CateId==$v['CateId']?' selected':'';?>>├<?=$v['Category'];?></option>
									<?php
									if($v['SubCateCount']){
										$len=count($allcate_ary["0,{$v['CateId']},"]);
										foreach((array)$allcate_ary["0,{$v['CateId']},"] as $v2){
									?>
										<option value="CateId:<?=$v2['CateId'];?>"<?=$CateId==$v2['CateId']?' selected':'';?>><?=$len>1?'｜├':'｜└'?><?=$v2['Category'];?></option>
								<?php
										}
									}
								}?>
							</select>
						</span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="set" />
				<input type="hidden" name="a" value="photo" />
				<input type="hidden" name="d" value="choice" />
				<input type="hidden" name="id" value="<?=$id;?>" />
				<input type="hidden" name="type" value="<?=$type;?>" />
				<input type="hidden" name="maxpic" value="<?=$maxpic;?>" />
				<input type="hidden" name="obj" value="<?=$obj;?>" />
				<input type="hidden" name="save" value="<?=$save;?>" />
				<input type="hidden" name="iframe" value="1" />
			</form>
		</div>
		<div class="upload">
			<h3>{/set.photo.local/}</h3>
			<?php /*
			<div class="up_input"><input name="PicUpload" id="PicUpload" type="file" /></div>
			*/?>
			<form name="upload_form" action="//jquery-file-upload.appspot.com/" method="POST" enctype="multipart/form-data" class="up_input">
				<noscript><input type="hidden" name="redirect" value="https://blueimp.github.io/jQuery-File-Upload/"></noscript>
				<div class="fileupload-buttonbar">
					<span class="btn_file btn-success fileinput-button">
						<i class="glyphicon glyphicon-plus"></i>
						<span>{/global.file_upload/}</span>
						<input type="file" name="Filedata" multiple>
					</span>
					<div class="fileupload-progress fade"><div class="progress-extended">&nbsp;</div></div>
					<div class="clear"></div>
					<div class="photo_multi_img template-box files hide"></div>
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
						<div class="pic template-download fade">
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
						<div class="template-download fade">
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
			</form>
			<div class="tips"></div>
		</div>
	<?php }elseif($c['manage']['do']=='category'){?>
		<?php /*
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<div class="ext">
					<div class="rows">
						<label>{/products.classify/}</label>
						<span class="input">
							<select name="CateMenu">
								<option value="">{/global.select_index/}</option>
								<?php
								foreach($c['manage']['photo_type'] as $k=>$v){	//系统图片分类
								?>
									<option value="IsSystem:<?=$v;?>"<?=$IsSystem==$v?' selected':'';?>>├{/set.photo.SystemType.<?=$v;?>/}</option>
								<?php
								}
								$photo_category=str::str_code(db::get_all('photo_category', '1', '*', 'CateId asc'));
								$allcate_ary=array();
								foreach($photo_category as $k=>$v) $allcate_ary[$v['UId']][]=$v;
								foreach((array)$allcate_ary['0,'] as $v){
								?>
									<option value="CateId:<?=$v['CateId'];?>"<?=$CateId==$v['CateId']?' selected':'';?>>├<?=$v['Category'];?></option>
									<?php
									if($v['SubCateCount']){
										$len=count($allcate_ary["0,{$v['CateId']},"]);
										foreach((array)$allcate_ary["0,{$v['CateId']},"] as $v2){
									?>
										<option value="CateId:<?=$v2['CateId'];?>"<?=$CateId==$v2['CateId']?' selected':'';?>><?=$len>1?'｜├':'｜└'?><?=$v2['Category'];?></option>
								<?php
										}
									}
								}?>
							</select>
						</span>
						<div class="clear"></div>
					</div>
				</div>
				<div class="clear"></div>
				<input type="hidden" name="m" value="set" />
				<input type="hidden" name="a" value="photo" />
				<input type="hidden" name="d" value="category" />
			</form>
		</div>
		*/?>
		<ul class="ico">
			<?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="javascript:;" label="{/global.add/}" data-id="0"></a></li><?php }?>
			<?php if($permit_ary['del']){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php }?>
	<?php if($c['manage']['do']!='choice' && $c['manage']['do']!='move'){?>
		<dl class="edit_form_part">
			<?php if(manage::check_permit('set', 0, array('a'=>'photo', 'd'=>'category'))){?>
				<dt></dt>
				<dd><a href="./?m=set&a=photo&d=category"<?=($c['manage']['do']=='category' || $c['manage']['do']=='category_edit')?' class="current"':'';?>>{/global.category/}</a></dd>
			<?php }?>
			<?php if(manage::check_permit('set', 0, array('a'=>'photo', 'd'=>'photo'))){?>
				<dt></dt>
				<dd><a href="./?m=set&a=photo"<?=($c['manage']['do']=='index' || $c['manage']['do']=='photo_upload' || $c['manage']['do']=='choice')?' class="current"':'';?>>{/set.photo.pic_list/}<?=$column?" ($column)":'';?></a></dd>
			<?php }?>
		</dl>
	<?php }?>
</div>
<div id="photo" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		//图片银行主页
	?>
		<script type="text/javascript">$(document).ready(function(){set_obj.photo_init(); set_obj.photo_upload_init()});</script>
		<div class="wrap_content photo_list">
			<form id="photo_list_form">
				<?php
				$Keyword=$_GET['Keyword'];
				$where='1';//条件
				$page_count=50;//显示数量
				if($CateId){
					$where.=' and '.category::get_search_where_by_CateId($CateId, 'photo_category');
				}else if(in_array($IsSystem, $c['manage']['photo_type'])){
					$where.=" and CateId=0 and IsSystem='$IsSystem'";		
				}
				$Keyword && $where.=" and Name like '%$Keyword%'";
				$photo_row=str::str_code(db::get_limit_page('photo', $where, '*', 'PId desc', (int)$_GET['page'], $page_count));
				foreach($photo_row[0] as $v){
				?>
					<div class="item">
						<div class="img"><img src="<?=$v['PicPath'];?>" /><span></span><input type="checkbox" name="PId[]" class="PIds" value="<?=$v['PId'];?>" /><div class="img_mask"></div></div>
						<div class="name"><a href="<?=$v['PicPath']?>" target="_blank" title="<?=$v['Name'];?>"><?=$v['Name'];?></a></div>
					</div>
				<?php }?>
				<div class="clear"></div>
				<input type="hidden" name="IsSystem" value="<?=$IsSystem;?>" />
				<input type="hidden" name="CateId" value="<?=$CateId;?>" />
				<input type="hidden" name="Page" value="<?=(int)$_GET['page'];?>" />
				<input type="hidden" name="do_action" value="set.photo_list_del">
			</form>
			<div id="turn_page"><?=manage::turn_page($photo_row[1], $photo_row[2], $photo_row[3], '?'.ly200::query_string('page').'&page=');?></div>
		</div>
		<?php /***************************** 图片银行编辑 Start *****************************/?>
		<div class="pop_form box_photo_edit">
			<form id="edit_form" name="upload_form" action="//jquery-file-upload.appspot.com/" method="POST" enctype="multipart/form-data">
				<div class="t"><h1><span></span>{/global.pic/}</h1><h2>×</h2></div>
				<div class="r_con_form">
					<div class="rows">
						<label>{/set.photo.category/}</label>
						<span class="input">
							<div class="fl"><?=category::ouput_Category_to_Select('CateId', $CateId, 'photo_category', 'UId="0,"', 1, 'notnull', '{/global.select_index/}');?></div>
						</span>
						<div class="clear"></div>
					</div>
					<?php /*
					<div class="rows">
						<label>{/set.photo.upfile/}</label>
						<span class="input upload_file">
							<div><input name="PicUpload" id="PicUpload" type="file" />{/notes.pic_five/}</div>
							<div class="photo_multi_img" id="PicDetail"></div>
						</span>
						<div class="clear"></div>
					</div>
					*/?>
					<div class="rows">
						<label>{/set.photo.upfile/}</label>
						<span class="input">
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
					<input type="hidden" name="do_action" value="set.photo_upload" />
				</div>
				<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
			</form>
		</div>
		<div class="pop_form box_move_edit">
			<form id="move_edit_form">
				<div class="t"><h1>{/set.photo.move_bat/}</h1><h2>×</h2></div>
				<div class="r_con_form">
					<div class="rows">
						<label>{/global.move_to/}</label>
						<span class="input"><?=category::ouput_Category_to_Select('CateId', '', 'photo_category', 'UId="0,"',1,'','{/global.select_index/}');?></span>
						<div class="clear"></div>
					</div>
					<input type="hidden" name="do_action" value="set.photo_move" />
				</div>
				<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
			</form>
		</div>
		<?php /***************************** 图片银行编辑 End *****************************/?>
	<?php
	}elseif($c['manage']['do']=='choice'){
		//选择器，文件框调用页面
	?>
		<script type="text/javascript">$(document).ready(function(){set_obj.photo_choice_init()});</script>
		<div class="wrap_content photo_list">
			<form id="photo_list_form">
				<?php
				//图片银行列表
				$Keyword=$_GET['Keyword'];
				$where='1';//条件
				$page_count=50;//显示数量
				if($CateId){
					$where.=' and '.category::get_search_where_by_CateId($CateId, 'photo_category');
				}elseif(in_array($IsSystem, $c['manage']['photo_type'])){
					$where.=" and CateId=0 and IsSystem='$IsSystem'";		
				}
				$Keyword && $where.=" and Name like '%$Keyword%'";
				$photo_row=str::str_code(db::get_limit_page('photo', $where, '*', 'PId desc', (int)$_GET['page'], $page_count));
				foreach($photo_row[0] as $v){
				?>
					<div class="item">
						<div class="img"><img src="<?=$v['PicPath']?>" <?=img::img_width_height(180, 180, $v['PicPath']);?> /><span></span><input type="checkbox" name="PId[]" value="<?=$v['PId'];?>" /><div class="img_mask"></div></div>
						<div class="name"><a href="<?=$v['PicPath']?>" target="_blank" title="<?=$v['Name'];?>"><?=$v['Name'];?></a></div>
					</div>
				<?php }?>
				<input type="hidden" name="id" value="<?=$id;?>" />
				<input type="hidden" name="type" value="<?=$type;?>" />
				<input type="hidden" name="maxpic" value="<?=$maxpic;?>" />
				<input type="hidden" name="CateId" value="<?=$CateId;?>" />
				<input type="hidden" name="obj" value="<?=$obj;?>" />
				<input type="hidden" name="save" value="<?=$save;?>" />
				<input type="hidden" name="sort" value="|" />
				<input type="hidden" name="do_action" value="set.photo_choice" />
			</form>
			<div id="turn_page"><?=manage::turn_page($photo_row[1], $photo_row[2], $photo_row[3], '?'.ly200::query_string('page').'&page=');?></div>
			<div class="clear"></div>
		</div>
		<?php /*
		<div class="list_foot clean">
			<input type="button" id="button_add" value="{/global.confirm/}" class="btn_ok" />
			<input type="button" value="{/global.cancel/}" class="btn_cancel" />
		</div>
		*/?>
	<?php
	}elseif($c['manage']['do']=='category'){
		echo ly200::load_static('/static/js/plugin/dragsort/dragsort-0.5.1.min.js');
	?>
		<script type="text/javascript">$(document).ready(function(){set_obj.photo_category_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<?php if($permit_ary['del']){?><td width="4%" nowrap="nowrap"><input type="checkbox" name="select_all" value="" class="va_m" /></td><?php }?>
					<?php if($permit_ary['edit']){?><td width="4%" nowrap="nowrap">{/global.my_order/}</td><?php }?>
					<td width="21%" nowrap="nowrap">{/global.category/}{/global.name/}</td>
					<td width="65%" nowrap="nowrap">{/global.sub_category/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?><td width="5%" nowrap="nowrap">{/global.operation/}</td><?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				//获取类别列表
				$cate_ary=str::str_code(db::get_all('photo_category', '1', '*', $c['my_order'].'CateId asc'));
				$all_cate_ary=$category_ary=array();
				foreach((array)$cate_ary as $v){
					$category_ary[$v['CateId']]=$v;
					$all_cate_ary[$v['UId']][]=$v;
				}
				$category_count=count($category_ary);
				unset($cate_ary);
				
				foreach((array)$all_cate_ary['0,'] as $v){
					$Name=$v['Category'];
					if($Keyword && !stripos($Name, $Keyword)) continue;
				?>
					<tr cateid="<?=$v['CateId'];?>" data="<?=htmlspecialchars(str::json_data($v));?>">
						<?php if($permit_ary['del']){?><td nowrap="nowrap"><input type="checkbox" name="select" value="<?=$v['CateId'];?>" class="va_m" /></td><?php }?>
						<?php if($permit_ary['edit']){?><td nowrap="nowrap" class="myorder move_myorder" data="move_myorder"><img src="/static/manage/images/products/move.png" align="absmiddle" /></td><?php }?>
						<td><?=$Name;?></td>
						<td class="attr_list">
							<dl class="attr_box hide"></dl><?php /* 不要删掉 是用来处理兼容的 */?>
							<?php
							foreach((array)$all_cate_ary["{$v['UId']}{$v['CateId']},"] as $vv){
								$vv['TopCateId']=category::get_top_CateId_by_UId($vv['UId']);
							?>
								<dl class="attr_box" cateid="<?=$vv['CateId'];?>" data="<?=htmlspecialchars(str::json_data($vv));?>">
									<dd class="attr_ico"></dd>
									<dd class="attr_txt"><?=$vv['Category'];?></dd>
									<?php if($permit_ary['edit'] || $permit_ary['del']){?>
										<dd class="attr_menu">
											<?php if($permit_ary['edit']){?><a class="edit" href="javascript:;" label="{/global.edit/}" data-id="<?=$vv['CateId'];?>"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
											<?php if($permit_ary['del']){?><a class="del" href="./?do_action=set.photo_category_del&CateId=<?=$vv['CateId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
										</dd>
									<?php }?>
								</dl>
							<?php }?>
							<?php if($permit_ary['add']){?><div class="attr_add"><a class="add" href="javascript:;" data-id="0">+</a></div><?php }?>
						</td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico edit" href="javascript:;" label="{/global.edit/}" data-id="<?=$v['CateId'];?>"><img src="/static/ico/edit.png" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="./?do_action=set.photo_category_del&CateId=<?=$v['CateId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<?php /***************************** 图片管理分类编辑 Start *****************************/?>
		<div class="pop_form box_photo_edit">
			<form id="edit_form">
				<div class="t"><h1><span></span>{/news.classify/}</h1><h2>×</h2></div>
				<div class="r_con_form">
					<div class="rows">
						<label>{/global.category/}{/global.name/}</label>
						<span class="input"><input name="Category" value="<?=$category_one['Category'];?>" type="text" class="form_input" maxlength="100" size="30" notnull> <font class="fc_red">*</font></span>
						<div class="clear"></div>
					</div>
					<div class="rows">
						<label>{/set.photo.children/}</label>
						<span class="input">
							<?php
							$ext_where="CateId!='{$category_one['CateId']}' and Dept<2";
							echo category::ouput_Category_to_Select('UnderTheCateId', ($ParentId?$ParentId:category::get_CateId_by_UId($category_one['UId'])), 'photo_category', "UId='0,' and $ext_where", $ext_where, '', '{/global.select_index/}');
							?>
						</span>
						<div class="clear"></div>
					</div>
					<input type="hidden" name="CateId" value="<?=$CateId;?>" />
					<input type="hidden" name="do_action" value="set.photo_category" />
				</div>
				<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
			</form>
		</div>
		<?php /***************************** 图片管理分类编辑 End *****************************/?>	
	<?php }?>
</div>