<?php !isset($c) && exit();?>
<style type="text/css">
#header_fix{overflow:visible;}
<?php if($c['mobile']['HeadFixed']){?>
	.header_top{width:100%; max-width:100%; position:fixed; top:0; left:0; z-index:100;}
<?php }?>
</style>
<?=ly200::set_custom_style();?>
<?php
$FbData=$c['config']['Platform']['Facebook']['SignIn']['Data'];
?>
<script type="text/javascript">
var ueeshop_config={
	"domain":"<?=ly200::get_domain();?>",
	"date":"<?=date('Y/m/d H:i:s', $c['time']);?>",
	"lang":"<?=substr($c['lang'], 1);?>",
	"currency":"<?=$_SESSION['Currency']['Currency'];?>",
	"currency_symbols":"<?=$_SESSION['Currency']['Symbol'];?>",
	"currency_rate":"<?=$_SESSION['Currency']['Rate'];?>",
	"FbAppId":"<?=$FbData?$FbData['appId']:'';?>",
	"FbPixelOpen":"<?=(int)$c['config']['Platform']['Facebook']['Pixel']['IsUsed'];?>",
	"UserId":"<?=(int)$_SESSION['User']['UserId'];?>",
	"TouristsShopping":"<?=(int)$c['config']['global']['TouristsShopping'];?>"
}
</script>
<?php if($c['config']['global']['IsCopy']){?>
	<script type="text/javascript">
		document.oncontextmenu=new Function("event.returnValue=false;");
		document.onselectstart=new Function("event.returnValue=false;");
		document.oncontextmenu=function(e){return false;}
	</script>
	<style>
	html, img{-moz-user-select:none; -webkit-user-select:none;}
	</style>
<?php }?>
<?php
$HeadIcon=$c['mobile']['HeadIcon']==0?'_0':'';
?>
<header>
    <div class="header_top clean FontBgColor">
		<div class="head_bg_col clean ui_border_b">
			<div class="logo fl pic_box"><a href="/"><img src="<?=$c['mobile']['LogoPath']?>" alt="" /></a><span></span></div>
			<aside class="fr">
				<div class="fr i1"><a href="javascript:;"><img src="<?=$c['mobile']['tpl_dir'];?>images/header_icon<?=$HeadIcon;?>_2_new.png" alt="" /></a></div>
				<div class="fr i3"><a href="/cart/"><img src="<?=$c['mobile']['tpl_dir'];?>images/header_icon<?=$HeadIcon;?>_1.png" alt="" /><span class="cart_count"><?=(int)$c['shopping_cart']['TotalQty'];?></span></a></div>
				<div class="fr i2"><a href="/account/"><img src="<?=$c['mobile']['tpl_dir'];?>images/header_icon<?=$HeadIcon;?>_0.png" alt="" /></a></div>
			</aside>
		</div>
    </div>
	<?php if($c['mobile']['HeadFixed']){?>
        <div class="header_fill"></div>
    <?php }?>
</header>

<div class="pop_up nav_side">
    <a class="close" href="javascript:;"><em></em></a>
    <div class="pop_up_container nav_container clean">
        <?php
            if((int)$_SESSION['User']['UserId']){
                $_UserName=substr($c['lang'], 1)=='jp'?$_SESSION['User']['LastName'].' '.$_SESSION['User']['FirstName']:$_SESSION['User']['FirstName'].' '.$_SESSION['User']['LastName'];
        ?>
        <div class="user clean">
            <div class="user_logo"></div>
            <a rel="nofollow" href="/account/" class="FontColor"><?=($_SESSION['User']['FirstName'] || $_SESSION['User']['LastName'])?$_UserName:$_SESSION['User']['Email'];?></a>
        </div>
        <?php }?>
        <?php
            $cur_lang=substr($c['lang'], 1);
            if(in_array(array_shift(explode('.', $_SERVER['HTTP_HOST'])), $c['config']['global']['Language']) || reset(explode('.', $_SERVER['HTTP_HOST']))=='www'){
                $dir=str_replace(array_shift(explode('.', $_SERVER['HTTP_HOST'])).'.', '', $_SERVER['HTTP_HOST']);
            }else{
                $dir=$_SERVER['HTTP_HOST'];
            }
            $currency_row=db::get_all('currency', 'IsUsed=1');
        ?>
        <div class="currency clean"><a href="javascript:;"><span class="title"><?=$c['lang_name'][$cur_lang];?> / <?=$_SESSION['Currency']['Currency'].(is_file($c['root_path'].$_SESSION['Currency']['FlagPath'])?'<img src="'.$_SESSION['Currency']['FlagPath'].'" alt="'.$_SESSION['Currency']['Currency'].'" />':'');?></span><div class="icon"><em><i></i></em></div></a></div>
        <div class="search clean">
            <form action="<?=$c['mobile_url'];?>/search/" method="get">
                <input type="search" value="" name="Keyword" placeholder="<?=$c['config']['global']['SearchTips']["SearchTips{$c['lang']}"];?>" class="text fl" />
                <input type="submit" value="" class="fr sub" />
            </form>
        </div>
        <nav class="menu_list">
            <div class="ui_border_b item"><a href="/"><?=$c['lang_pack']['home'];?></a></div>
            <div class="ui_border_b item son"><a href="javascript:;" class="btn_all_category" rel="nofollow"><?=$c['lang_pack']['all_category'];?></a><div class="icon"><em><i></i></em></div></div>
            <?php
            $nav_row=db::get_value('config', "GroupId='themes' and Variable='NavData'", 'Value');
            $nav_data=str::json_data($nav_row, 'decode');
            foreach((array)$nav_data as $k=>$v){
                $nav=ly200::nav_style($v, 1);
                if(!$nav['Name']) continue;
                if($nav['Name']==$c['nav_cfg'][0]['name'.$c['lang']] && $nav['Url']==$c['nav_cfg'][0]['url']) continue; //重复的Home
                if($nav['Url']=='/holiday.html' || $nav['Url']=='/sitemap.html') continue; //临时取消节目模板 博客 网站地图
            ?>
                <div class="ui_border_b item"><a href="<?=$nav['Url'];?>"><?=$nav['Name'];?></a></div>
            <?php }?>
        </nav>
    </div>
</div>
<?php
$navcate_row=str::str_code(db::get_all('products_category', 'IsSoldOut=0', "CateId,UId,Category{$c['lang']},PicPath",  $c['my_order'].'CateId asc'));
$navcate_ary=array();
foreach($navcate_row as $k=>$v){
    $navcate_ary[$v['UId']][]=$v;
}
$CName='Category'.$c['lang'];
?>
<div class="pop_up category_side">
    <div class="pop_up_container nav_container clean">
        <a class="fl close category_close" href="javascript:;"><em><i></i></em></a>
        <div class="fl category_title"><?=$c['lang_pack']['all_category'];?></div>
        <div class="clear"></div>
        <div class="menu_list">
            <?php
            foreach((array)$navcate_ary['0,'] as $k=>$v){
                $ary=$navcate_ary["0,{$v['CateId']},"];
            ?>
                <div class="ui_border_b item<?=$ary?' son':'';?>">
                    <a href="<?=$ary?'javascript:;':ly200::get_url($v, 'products_category')?>" title="<?=$v[$CName];?>"><?=$v[$CName];?></a><div class="icon"><em><i></i></em></div>
                    <?php if($ary){?>
                        <ul class="ui_border_t menu_son">
                            <?php
                            foreach((array)$ary as $k2=>$v2){
                                $ary=$navcate_ary["{$v2['UId']}{$v2['CateId']},"];
                            ?>
                                <li class="item<?=$ary?' son':'';?>">
                                    <a href="<?=$ary?'javascript:;':ly200::get_url($v2, 'products_category')?>" title="<?=$v2[$CName];?>"><?=$v2[$CName];?></a><div class="icon"><em><i></i></em></div>
                                    <?php if($ary){?>
                                        <ul class="menu_son menu_grandson">
                                            <?php foreach((array)$ary as $k3=>$v3){?>
                                                <li class="item"><a href="<?=ly200::get_url($v3, 'products_category')?>" title="<?=$v3[$CName];?>"><?=$v3[$CName];?></a></li>
                                            <?php }?>
                                        </ul>
                                    <?php }?>
                                </li>
                            <?php }?>
                        </ul>
                    <?php }?>
                </div>
            <?php }?>
        </div>
    </div>
</div>
<div class="pop_up footer_side">
    <div class="pop_up_container nav_container clean">
        <a class="fl close category_close" href="javascript:;"><em><i></i></em></a>
        <div class="fl category_title"><?=$c['lang_name'][$cur_lang];?> / <?=$_SESSION['Currency']['Currency'];?></div>
        <div class="clear"></div>
        <div class="menu_list">
            <?php
            //语言
            if(count($c['config']['global']['Language'])>1){
                echo '<div class="ui_border_b item"><a href="javascript:;"><b>'.$c['lang_pack']['language'].'</b></a></div>'; 
            }
            foreach($c['config']['global']['Language'] as $v){
                if($v==$cur_lang) continue;
                $dir_url='http://'.($v==$c['config']['global']['LanguageDefault']&&!(int)db::get_value('config', 'GroupId="global" and Variable="BrowserLanguage"', 'Value')?'':$v.'.').$dir.($_SERVER['REQUEST_URI']!='/'?'/'.$_SERVER['REQUEST_URI']:'');
                echo '<div class="ui_border_b item"><a href="'.$dir_url.'">'.$c['lang_name'][$v].'</a></div>';
            }
            //货币
            if(count($currency_row)>1){
                echo '<div class="ui_border_b item"><a href="javascript:;"><b>'.$c['lang_pack']['currency'].'</b></a></div>';
            }
            foreach((array)$currency_row as $v){
                echo '<div class="ui_border_b item"><a href="javascript:;" class="currency_item" data="'.$v['Currency'].'">'.(is_file($c['root_path'].$v['FlagPath'])?'<img src="'.$v['FlagPath'].'" alt="'.$v['Currency'].'" />':'').$v['Currency'].'</a></div>';
            }?>
        </div>
    </div>
</div>
<?php
$chat_row=str::str_code(db::get_all('chat', '`Type` IN (1,2,5)', '*', 'CId asc')); //浮动在线客服，只显示Skype,Email,Whatsapp
?>
<div id="float_chat">
    <div class="float_list">
        <?php 
        if((int)$c['config']['Platform']['Facebook']['PageId']['IsUsed']){
            $messenger_url='https://m.me/'.$c['config']['Platform']['Facebook']['PageId']['Data']['page_id'];
        ?>
            <a href="<?=$messenger_url;?>" class="btn_global FontBgColor message_us" target="_blank"><img src="/static/ico/mobile-message-us-blue.png" alt="Message Us" /></a>
        <?php }?>
        <?php if($c['config']['chat']['IsFloatChat']==1 && $chat_row){?><a href="javascript:;" class="btn_global btn_chat global_btn FontBgColor">Chat</a><?php }?>
        <a href="javascript:;" class="btn_global btn_top">Top</a>
    </div>
    <?php if($c['config']['chat']['IsFloatChat']==1 && $chat_row){?>
        <dl class="inner_chat">
            <?php /*<dt class="chat_hd"><?=$c['lang_pack']['mobile']['chat'];?></dt>*/?>
            <dd class="chat_bd">
                <?php 
                foreach((array)$chat_row as $v){
                    $name=$v['Name'];
                    $url=sprintf($c['chat']['link'][$v['Type']], $v['Account']);
                    //$c['chat']['type'][$v['Type']]=='WhatsApp' && $name=$v['Account'];
                ?>
                <a class="item <?=strtolower($c['chat']['type'][$v['Type']]);?>" href="<?=$url?$url:'javascript:;';?>" title="<?=$name;?>"><?=$name;?></a><div class="blank6"></div>
                <?php }?>
            </dd>
            <dd class="chat_close"></dd>
        </dl>
    <?php }?>
</div>