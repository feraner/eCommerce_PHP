<?php !isset($c) && exit();?>
<?php
$AId=(int)$_GET['AId'];
$blog_row=str::str_code(db::get_one('blog', "AId='$AId'"));
if(!$blog_row){
	@header('HTTP/1.1 404');
	exit;
}

$review_count=(int)db::get_row_count('blog_review', "AId='$AId'");

//SEO
$seo_ary=array(
	'SeoTitle'.$c['lang']		=>	$blog_row['SeoTitle'],
	'SeoKeyword'.$c['lang']		=>	$blog_row['SeoKeyword'],
	'SeoDescription'.$c['lang']	=>	$blog_row['SeoDescription']
);

$spare_ary=array(
	'SeoTitle'		=>	$blog_row['Title'],
	'SeoKeyword'	=>	$blog_row['Title'],
	'SeoDescription'=>	$blog_row['Title']
);
//分享
$Name=$blog_row['Title'];
?>
<!doctype html>
<html lang="<?=substr($c['lang'], 1);?>">
<head>
<meta charset="utf-8" />
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<meta name="apple-mobile-web-app-status-bar-style" content="black" />
<meta content="telephone=no" name="format-detection" />
<?=ly200::seo_meta($seo_row, $spare_ary);?>
<?php include("inc/static.php");?>
</head>
<body>
<?php include('inc/header.php');?>
<div class="spacing"></div>
<div class="main_con wrap">
	<div class="leftbar fl">
		<div class="container">
        	<div class="item">
                <div class="para">By <?=$blog_row['Author'];?> | <span><?=date('d', $blog_row['AccTime']);?></span> <?=date('F Y', $blog_row['AccTime']);?> | <span><?=$review_count;?></span> Comments</div>
                <h2><?=$blog_row['Title'];?></h2>
                <div class="bg"></div>
                <div class="share"><?php include("{$c['default_path']}/products/detail/share.php");?></div>
                <div class="clear"></div>
                <div class="con"><?=db::get_value('blog_content', "AId='$AId'", 'Content');?></div>
                <div class="line"></div>
                <?php if($review_count){?>
                <div class="review">
                    <h2>Recently Reviews</h2>
                    <div class="bg"></div>
                    <div class="review_content"></div>
                    <a href="javascript:;" class="more">Read More</a>
                </div>
                <?php }?>
                <div class="blank20"></div>
                <h2>Leave a Reply</h2>
                <div class="bg"></div>
                <div class="con">Your email address will not be published.Required fields are marked. *</div>
                <div class="blank20"></div>
                <div class="form">
                	<form>
                    	<div class="label">Name</div>
                        <div class="input"><input type="text" class="text" name="Name" notnull /></div>
                        <div class="blank20"></div>
                    	<div class="label">E-mail</div>
                        <div class="input"><input type="text" class="text" name="Email" format="Email" notnull /></div>
                        <div class="blank20"></div>
                    	<div class="label">Content</div>
                        <div class="input"><textarea name="Content" class="textarea" notnull maxlength="255" ></textarea></div>
                        <div class="blank20"></div>
                        <div class="label">Verification code</div>
                        <div class="input">
                            <input name="VCode" type="text" class="text vcode" size="15" maxlength="4" notnull />
                            <?=v_code::create('blog');?>
                        </div>
                        <div class="clear"></div>
                        <input type="submit" class="submit" value="WRITE REVIEW" />
                        <input type="hidden" name="AId" value="<?=$AId;?>" />
                        <input type="hidden" name="do_action" value="action.blog_review" />
                        <div class="clear"></div>
                    </form>
                </div>
            </div>
        </div>
        <div class="spacing"></div>
    </div>
    <div class="rightbar fr"><?php include('inc/right_side.php');?></div>
    <div class="clear"></div>
</div>
<?php include('inc/footer.php');?>
</body>
</html>