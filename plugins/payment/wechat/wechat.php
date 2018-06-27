<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>WhChatPay</title>
<?php include("{$c['static_path']}/inc/static.php");?>
<script>$('#payment_loading').remove();</script>
</head>
<body>
<?php if($is_mobile){	//手机版?>
	<style>
    #wechat{ padding:10px 12px;}
	#wechat .rows{ border-bottom:1px dashed #c9c9c9; color:#666; font-size:14px; padding:15px 0;}
	#wechat .rows span{ float:right; font-weight:bold;}
	#wechat .price{ text-align:center; color:#ee6209; font-size:26px; padding:20px 0;}
	#wechat .btn a{ display:block; height:42px; line-height:42px; color:#fff; text-align:center; border-radius:5px; background-image:-webkit-gradient(linear, left top, left bottom, color-stop(0, #43C750), color-stop(1, #31AB40)); border:1px solid #2E993C; box-shadow:0 1px 0 0 #69D273 inset; text-decoration:none; font-size:20px;}
    </style>
    <script>
    $(function(){
        var query_count=100;
        var timer='';
        query=function(){
			$.post('<?=$data['domain'].'/payment/wechat/query/'.$data['order_row']['OId'].'.html'?>',function(query_data){
                clearTimeout(timer);
                if(query_data.ret==1){
                    query_count=0;
                }else{
                    query_count--;
                }
                if(query_count==0){
                    if(query_data.ret==1){
                        window.location.href='<?=$success_url?>';
                    }
                    return false;
                } 
                timer=setTimeout(function(){query()},5000);
            },'json');
        }
        setTimeout(function(){query();},2000);
    })
    </script>
    <div id="wechat">
    	<div class="rows"><label>Order No.</label><span class="pay_bill_value"><?=$data['order_row']['OId']?></span></div>
        <div class="rows"><label>Order Date</label><span class="pay_bill_value"><?=date("Y-m-d H:i:s",$data['order_row']['OrderTime'])?></span></div>
        <div class="price"><span>￥</span><?=$total_price?></div>
        <div class="btn"><a href="<?=$result['mweb_url']?>">Pay Now</a></div>
    </div>
<?php }else{	//PC版?>
    <style>
	body{ background:url(/plugins/payment/wechat/images/bg_pay.png) #d4d5d7;}
	body *{ font-family:"Microsoft YaHei",Helvetica,Verdana,Arial,Tahoma;}
    #wechat .pay_logo{ height:66px; background:url(/plugins/payment/wechat/images/logo_bg.png); text-align:center; padding-top:10px; box-sizing:border-box;}
	#wechat .pay_logo .h1{ height:50px;}
	#wechat .pay_con{ padding-top:7px; padding-bottom:40px;}
	#wechat .pay_con .mail_box{ width:920px; margin:0 auto; background:url(/plugins/payment/wechat/images/mail_box_bg.png) #fff repeat-x 0 -60px; box-shadow:0 1px 1px rgba(0,0,0,0.35); position:relative;}
	#wechat .pay_con .mail_box .corner{ position:absolute; top:0; width:6px; height:30px; background:url(/plugins/payment/wechat/images/mail_box_bg.png) no-repeat;}
	#wechat .pay_con .mail_box .corner.left{ background-position:0 0; left:-5px;}
	#wechat .pay_con .mail_box .corner.right{ background-position:0 -30px; right:-5px;}
	#wechat .pay_con .mail_box .inner{ overflow:hidden; padding:30px 170px 50px; background:url(/plugins/payment/wechat/images/mail_box_inner.png) repeat-x bottom left; text-align:center; position:relative; bottom:-10px;}
	#wechat #qr_normal img{ width:306px; height:306px;}
	#wechat #qr_normal .msg{ width:258px; padding:12px 0; border:1px solid #2b4d69; background:#445f85; border-radius:3px; letter-spacing:6px; color:#fff; display:inline-block; margin-top:5px;}
	#wechat #qr_normal .msg i{ background-position:0 -60px; margin-left:-16px; width:60px; height:60px; display:inline-block; vertical-align:middle; background:url(/plugins/payment/wechat/images/ico_qr.png) no-repeat 0 -60px;}
	#wechat #qr_normal .msg p{ font-size:16px; text-align:left; vertical-align:middle; letter-spacing:normal; display:inline-block; line-height:25px;}
	#wechat #pay_succ{ display:none;}
	#wechat #pay_succ i{ display:inline-block; width:110px; height:110px; background:url(/plugins/payment/wechat/images/ico_suc.png) no-repeat;}
	#wechat #pay_succ h3{ font-size:26px; padding:15px 0;}
	#wechat #pay_succ p{ font-size:14px; color:#565656; line-height:25px;}
	#wechat #pay_succ p a{ text-decoration:underline; color:#374673;}
	#wechat #price{ color:#585858; font-size:60px; border-bottom:1px solid #ddd; margin-top:30px; padding-bottom:20px;}
	#wechat #info{ padding:18px 0 10px;}
	#wechat #info p{ overflow:hidden; line-height:26px;}
	#wechat #info label{ float:left; font-size:14px; color:#8e8e8e;}
	#wechat #info span{ float:right; font-size:14px;}
	#wechat .aside{ clear:both; margin-top:14px; padding-top:20px; border-top:3px solid #e0e3eb;}
    </style>
    <script>
    $(function(){
        var query_count=100;
        var timer='';
        query=function(){
			$.post('<?=$data['domain'].'/payment/wechat/query/'.$data['order_row']['OId'].'.html'?>','qrcode=<?=$wechat_qrcode?>',function(query_data){
                clearTimeout(timer);
                if(query_data.ret==1){
                    query_count=0;
                }else{
                    query_count--;
                }
                if(query_count==0){
                    if(query_data.ret==1){
                        $('#qr_normal').hide();
                        $('#pay_succ').show();
                        setInterval(function(){
                            var second=$('#redirectTimer').text();
                            if(second==0){
                                window.location.href='<?=$success_url?>';
                            }else{
                                $('#redirectTimer').text(second-1);
                            }
                        },1000);
                    }
                    return false;
                } 
                timer=setTimeout(function(){query()},5000);
            },'json');
        }
        setTimeout(function(){query();},2000);
    })
    </script>
    <div style="display:none;"><?php include("{$c['theme_path']}/inc/header.php");?></div>
    <div id="wechat">
    	<div class="pay_logo">
        	<h1><a href="/"><img src="/plugins/payment/wechat/images/logo_pay_en.png" class="pngFix"></a></h1>
        </div>
        <div class="pay_con">
        	<div class="mail_box">
            	<div class="inner">
                	<div class="top">
                        <div id="qr_normal">
                        	<div><img src="<?=$wechat_qrcode?>" /></div>
                            <div class="msg">
                            	<i></i>
                                <p>Use the WeChat to<br/>scan the QR code</p>
                            </div>
                        </div>
                        <div id="pay_succ">
                        	<i></i>
                            <h3>Payment success</h3>
                            <p>
                                <span id="redirectTimer">5</span>
                                seconds after returning to the merchant page, you can also
                                <a href="<?=$success_url?>">click here</a>
                                to return immediately.
                            </p>
                        </div>
                    </div>
                    <div class="bot">
                    	<div id="price"> <span>￥</span><?=$total_price?></div>
                        <div id="info">
                            <p><label>Order No.</label><span class="pay_bill_value"><?=$data['order_row']['OId']?></span></p>
                            <p><label>Order Date</label><span class="pay_bill_value"><?=date("Y-m-d H:i:s",$data['order_row']['OrderTime'])?></span></p>
                        </div>
                        <div class="aside"></div>
                    </div>
                </div>
                <div class="corner left"></div>
                <div class="corner right"></div>
            </div>
        </div>
    </div>
<?php }?>
</body>
</html>