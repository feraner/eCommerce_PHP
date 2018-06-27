<?php !isset($c) && exit();?>
<script type="text/javascript">$(function(){user_obj.inbox_init();})</script>
<div id="user">
    <ul class="global_tab tab_inbox">
        <li class="current" data-module="inbox"><a href="javascript:;"><?=$c['lang_pack']['user']['inbox'];?></a></li>
        <li data-module="outbox"><a href="javascript:;"><?=$c['lang_pack']['user']['outbox'];?></a></li>
        <li data-module="write"><a href="javascript:;"><?=$c['lang_pack']['user']['writeInbox'];?></a></li>
    </ul>
    <div class="inbox_tab_con">
        <div class="tab_con inbox"></div>
        <div class="tab_con outbox"></div>
        <div class="tab_con">
            <form  method="post" action="/" enctype="multipart/form-data">
                <div class="rows">
                    <label><?=$c['lang_pack']['user']['subject'];?>:</label>
                </div>
                <div class="rows">
                    <input type="text" name="Subject" value="" size="50" maxlength="100" notnull="" />
                </div>
                <div class="rows">
                    <label><?=$c['lang_pack']['user']['content'];?>:</label>
                </div>
                <div class="rows">
                    <textarea name="Content" notnull=""></textarea>
                </div>
                <div class="rows">
                    <div class="upload_box">
                        <div class="upload_btn"><img src="/static/themes/default/mobile/images/u_icon_upload_add.png" /></div>
                        <input class="upload_file" type="file" name="PicPath" accept="image/gif,image/jpeg,image/png" onchange="loadImg(this);">
                    </div>
                </div>
                <input type="submit" class="submit_btn" name="submit_button" value="<?=$c['lang_pack']['user']['submit'];?>" />
                <input type="hidden" name="do_action" value="user.write_inbox" />        
            </form>
        </div>
    </div>
    <div class="inbox_detail_pop">
        <div class="box">
            <div class="title"></div>
            <div class="date"></div>
            <div class="con"></div>
            <div class="img"></div>
        </div>
    </div>
    <div class="no_data"><div class="content_blank"><?=$c['lang_pack']['mobile']['no_data'];?></div></div>
</div>
<script>
function loadImg(obj){
    //获取文件  
    var file = obj.files[0];  
    //创建读取文件的对象  
    var reader = new FileReader();  
    //为文件读取成功设置事件  
    reader.onload=function(e) {    
        var imgFile = e.target.result;
		$(obj).prev('.upload_btn').html('<img src="'+imgFile+'"/>');
    }; 
    //正式读取文件  
    reader.readAsDataURL(file);
}
</script>
