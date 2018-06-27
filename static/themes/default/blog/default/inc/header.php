<?php !isset($c) && exit();?>
<div id="b_header">
	<div class="wrap">
    	<div class="leftbar">
        	<div class="title"><?=$blog_set_row['Title'];?></div>
            <div class="brief"><?=$blog_set_row['BriefDescription'];?></div>
        </div>
        <div class="rightbar">
        	<div class="menu"><i></i><i></i><i></i></div>
            <div class="nav">
                <?php foreach((array)$Nav as $k=>$v){?>
                <div class="item"><a href="<?=$v[1];?>" target="_blank"><?=$v[0];?></a></div>
                <?php }?>
                <div class="clear"></div>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>