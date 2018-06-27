<html>
<head>
<title>review upload</title>
<script type="text/javascript" src="/static/themes/default/mobile/js/jquery-min.js"></script>
<script type="text/javascript">
function getExt(file){
	return (-1!==file.indexOf('.'))?file.replace(/.*[.]/, ''):'';
}

function valid(el){
	var ext=getExt(el.value);
	if(el.value=='' || ext=='' || (ext.toLowerCase()!=='jpg' && ext.toLowerCase()!=='jpeg' && ext.toLowerCase()!=='gif' && ext.toLowerCase()!=='png')){
		if(typeof(window.parent.reviewText1)!="undefined"){
			alert(window.parent.reviewText1);
		}else{
			alert("Please only provide JPG/GIF/PNG files.");
		}
		el.value='';
        return false;
	}
	var name=el.value.replace(/.*[\\]/, '');
	$(el).parents('.file_box').find('.file_name').text(name);
}
</script>
<style>
body{margin:0; padding:0}
.file_box{width:3.5rem; height:3.5rem; margin-right:2rem; background:url(../images/icon_camera.png) no-repeat center #f7f7f7; border:.0625rem #ddd solid; position:relative; float:left;}
.file_box .file_upload{width:3.5rem; height:3.5rem; background:transparent; opacity:0; -moz-opacity:0; -webkit-opacity:0; -khtml-opacity:0; position:absolute; left:0; top:0; z-index:1;}
.file_box .file_upload>input{width:3.5rem; height:3.5rem; cursor:pointer; position:absolute; display:inline-block;}
.file_box .file_name{width:4.5rem; line-height:1rem; overflow:hidden; margin-top:3.5rem; font-family:Arial, Helvetica, sans-serif; font-size:.625rem; display:block;}
</style>
</head>

<body>
<form id="review_img_form" action="/account/" method="post" enctype="multipart/form-data">
	<?php for($i=0; $i<3; ++$i){?>
		<div class="file_box">
			<div class="file_upload">
				<input type="file" onchange="valid(this)" name="PicPath_<?=$i;?>" accept="image/jpg" />
			</div>
			<span class="file_name"></span>
		</div>
	<?php }?>
	<input type="hidden" name="do_action" value="user.review_img" />
</form>
</body>
</html>