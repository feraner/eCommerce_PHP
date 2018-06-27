<html>
<head>
<title>review upload</title>
<script type="text/javascript">
function getExt(file){
	return (-1!==file.indexOf('.'))?file.replace(/.*[.]/, ''):'';
}

function valid(el){
	var ext=getExt(el.value);
	if(el.value=='' || ext=='' || (ext.toLowerCase()!=='jpg' && ext.toLowerCase()!=='gif' && ext.toLowerCase()!=='png')){
		if(typeof(window.parent.reviewText1)!="undefined"){
			alert(window.parent.reviewText1);
		}else{
			alert("Please only provide JPG/GIF/PNG files.");
		}
		el.value='';
        return false;
	}
}
</script>
<style>body{margin:0; padding:0}</style>
</head>

<body>
<form id="review_img_form" action="/account/" method="post" enctype="multipart/form-data">
	<table border="0">
		<tbody>
			<tr>
				<td><input type="file" onchange="valid(this)" name="PicPath_0"></td>
				<td><input type="file" onchange="valid(this)" name="PicPath_2"></td>
			</tr>
			<tr>
				<td><input type="file" onchange="valid(this)" name="PicPath_1"></td>
				<td>&nbsp;</td>
			</tr>
		</tbody>
	</table>
	<input type="hidden" name="do_action" value="user.review_img" />
</form>
</body>
</html>