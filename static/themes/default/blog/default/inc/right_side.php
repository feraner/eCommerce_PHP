<?php !isset($c) && exit();?>
<?php
$pop_row=str::str_code(db::get_limit('blog', 'IsHot=1', '*', $c['my_order'].'AId desc', 0, 5));
$cate_row=str::str_code(db::get_all('blog_category', 1, '*', $c['my_order'].'CateId asc'));
$blog_all_row=str::str_code(db::get_all('blog', 1, 'AccTime,Tag'));
$blog_time_ary=array();
foreach($blog_all_row as $v){
	$blog_time_ary[date('F Y', $v['AccTime'])]=1;
}
if($AId && $blog_row['Tag']){
	$blog_tags_ary=array();
	$tags_ary=explode('|',$blog_row['Tag']);
	$tags_ary=array_filter($tags_ary);
	foreach((array)$tags_ary as $v){
		if(!in_array($v,$blog_tags_ary)){
			$blog_tags_ary[]=$v;
		}
	}
}
?>
<div class="container">
	<div class="search">
        <form action="/blog/" method="get">
            <input class="txt" name="Keyword" type="text" placeholder="Search" value="<?=stripslashes($Keyword)?>" />
            <input class="btn" type="submit" value="Search" />
            <div class="clear"></div>
        </form>
    </div>
</div>
<div class="spacing"></div>
<div class="container">
	<div class="category">
    	<div class="title">POPPULAR BLOG</div>
        <div class="bg"></div>
        <ul>
        	<?php foreach((array)$pop_row as $k=>$v){?>
            <li <?=$k?'':'style="margin-top:0; border-top:0;"'?>><a href="<?=ly200::get_url($v, 'blog');?>"><?=$v['Title'];?></a></li>
            <?php }?>
        </ul>
    </div>
</div>
<div class="spacing"></div>
<div class="container">
	<div class="category">
    	<div class="title">ARCHIVES</div>
        <div class="bg"></div>
        <ul>
            <?php
			$i=0;
            foreach($blog_time_ary as $k=>$v){
				if($i>5) break;
			?>
            <li <?=$i?'':'style="margin-top:0; border-top:0;"'?>><a href="<?=ly200::get_url(@strtotime($k), 'blog_date');?>"><?=$k;?></a></li>
            <?php
				++$i;
			}?>
        </ul>
    </div>
</div>
<div class="spacing"></div>
<div class="container">
	<div class="category">
    	<div class="title">CATEGORIES</div>
        <div class="bg"></div>
        <ul>
            <?php foreach((array)$cate_row as $k=>$v){?>
            <li <?=$k?'':'style="margin-top:0; border-top:0;"'?>><a href="<?=ly200::get_url($v, 'blog_category');?>"><?=$v['Category_en'];?></a></li>
            <?php }?>
        </ul>
    </div>
</div>
<div class="spacing"></div>
<?php if($blog_tags_ary){?>
<div class="container">
	<div class="category">
    	<div class="title">TAGS</div>
        <div class="bg"></div>
        <div class="tags">
        	<?php foreach((array)$blog_tags_ary as $k => $v){?>
            <a href="/blog/?Tags=<?=$v?>"><?=$v?></a>
            <?php }?>
        </div>
    </div>
</div>
<div class="spacing"></div>
<?php }?>