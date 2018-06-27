<?php !isset($c) && exit();?>
<?php
if($c['config']['chat']['IsFloatChat']==1 && $chat_row=str::str_code(db::get_all('chat', '1', '*', $c['my_order'].'CId asc'))){
	$chat_data = json_decode($c['config']['chat']['chat_bg'], !0);
	$chat_data['Bg3_0'] = $chat_data['Bg3_0']?$chat_data['Bg3_0']:'/static/ico/bg3_0.png';
	$chat_data['Bg3_1'] = $chat_data['Bg3_1']?$chat_data['Bg3_1']:'/static/ico/bg3_1.png';
	$chat_data['Bg4_0'] = $chat_data['Bg4_0']?$chat_data['Bg4_0']:'/static/ico/bg4_0.png';
	if ($c['config']['chat']['Type']==0){
?>
		<div class="chathide chattrans chatfloat0">
            <div id="float_window" class="Color" style="background-color:#<?=$chat_data['Color'];?>;">
                <div id="inner_window">
                    <div id="demo_window" style=" background-color:#<?=$chat_data['Color'];?>;" class="Color">
                        <?php 
                            foreach($chat_row as $v){
                                $link = sprintf($c['chat']['link'][$v['Type']],$v['Account']);
                        ?>
                            <a class="<?=$c['chat']['type'][$v['Type']];?>" href="<?=$v['Type']==4?"javascript:void(0);":$link;?>" title="<?=$v['Name'];?>" target="_blank">
                                <?php if ($v['Type']==4){?>
                                    <span class='relimg'><img src='<?=$v['PicPath']?>' /></span>
                                <?php /*?><?php }else if ($v['Type']==5){?>
                                    <span class='relimg'><?=$v['Account'];?></span><?php */?>
                                <?php }?>
                            </a>
                            <div class="blank6"></div>
                        <?php }?>
                    </div>
                </div>
                <a href="#" id="go_top">TOP</a>
            </div>
        </div>
    <?php }elseif ($c['config']['chat']['Type']==1){?>
    	<div class="chathide chattrans chatfloat1">
            <div id="service_0">
                <?php 
                    foreach($chat_row as $v){
                        $link = sprintf($c['chat']['link'][$v['Type']],$v['Account']);
                ?>
                    <div class="r r<?=$v['Type'];?> Color<?=$v['Type'];?>" style="background-color:#<?=$chat_data[$v['Type']];?>;">
                        <a href="<?=$v['Type']==4?"javascript:void(0);":$link;?>" target="_blank" title="<?=$v['Name'];?>"><?=$v['Name'];?></a>
                        <?php if($v['Type']==4){?>
                            <span class="relimg"><img src="<?=$v['PicPath'];?>" /></span>
                        <?php /*?><?php }else if ($v['Type']==5){?>
                            <span class='relimg'><?=$v['Account'];?></span><?php */?>
                        <?php }?>
                    </div>
                <?php }?>
                <div class="r top ColorTop" style=" background-color:#<?=$chat_data['ColorTop'];?>;"><a href="#">TOP</a></div>
            </div>
        </div>
    <?php }elseif ($c['config']['chat']['Type']==2){?>
    	<div class="chathide chattrans chatfloat2">
            <div id="service_1">
                <?php 
                    foreach($chat_row as $v){
                        $link = sprintf($c['chat']['link'][$v['Type']],$v['Account']);
                ?>
                        <div class="r r<?=$v['Type'];?> Color" style=" background-color:#<?=$chat_data['Color'];?>;">
                            <a href="<?=$v['Type']==4?"javascript:void(0);":$link;?>" title="<?=$v['Name'];?>" target="_blank"></a>
                            <?php if($v['Type']==4){?>
                                <span class="relimg"><img src="<?=$v['PicPath'];?>" /></span>
                            <?php /*?><?php }else if ($v['Type']==5){?>
                                <span class='relimg'><?=$v['Account'];?></span><?php */?>
                            <?php }?>
                        </div>
                <?php }?>
                <div class="r top Color" style=" background-color:#<?=$chat_data['Color'];?>;"><a href="#"></a></div>
            </div>
        </div>
    <?php }elseif ($c['config']['chat']['Type']==3){?>
    	<div class="chathide chattrans chatfloat3">
            <div id="service_2">
                <div class="sert">
                    <div class="img0"><img src="<?=$chat_data['Bg3_0'];?>" /></div>
                    <div class="img1"><img src="<?=$chat_data['Bg3_1'];?>" /></div>
                </div>
                <?php 
                    foreach($chat_row as $v){
                        $link = sprintf($c['chat']['link'][$v['Type']],$v['Account']);
                ?>
                        <div class="r r<?=$v['Type'];?> Color hoverColor<?=$v['Type'];?>" style=" background-color:#<?=$chat_data['Color'];?>;" color="#<?=$chat_data['Color'];?>" hover-color="#<?=$chat_data[$v['Type']];?>">
                            <a href="<?=$v['Type']==4?"javascript:void(0);":$link;?>" title="<?=$v['Name'];?>" target="_blank"></a>
                            <?php if($v['Type']==4){?>
                                <span class="relimg"><img src="<?=$v['PicPath'];?>" /></span>
                            <?php /*?><?php }else if ($v['Type']==5){?>
                                <span class='relimg'><?=$v['Account'];?></span><?php */?>
                            <?php }?>
                        </div>
                <?php }?>
                <div class="r top Color hoverColorTop" style=" background-color:#<?=$chat_data['Color'];?>;" color="#<?=$chat_data['Color'];?>" hover-color="#<?=$chat_data['ColorTop'];?>"><a href="#"></a></div>
            </div>
        </div>
        <script>
        $(function(){
            $('#service_2 .Color').hover(function (){
                $(this).css('background-color', $(this).attr('hover-color'));
            }, function (){
                $(this).css('background-color', $(this).attr('color'));
            });
        });
        </script>
    <?php }elseif ($c['config']['chat']['Type']==4){?>
    	<div class="chathide chattrans chatfloat4">
            <div id="service_3">
                <div class="sert"><img src="<?=$chat_data['Bg4_0'];?>" /></div>
                <?php 
                    foreach($chat_row as $v){
                        $link = sprintf($c['chat']['link'][$v['Type']],$v['Account']);
                ?>
                        <div class="r r<?=$v['Type'];?> Color" style=" background-color:#<?=$chat_data['Color'];?>;">
                            <a href="<?=$v['Type']==4?"javascript:void(0);":$link;?>" target="_blank" title="<?=$v['Name'];?>"><?=$v['Name'];?></a>
                            <?php if($v['Type']==4){?>
                                <span class="relimg"><img src="<?=$v['PicPath'];?>" /></span>
                            <?php /*?><?php }else if ($v['Type']==5){?>
                                <span class='relimg'><?=$v['Account'];?></span><?php */?>
                            <?php }?>
                        </div>
                <?php }?>
                <div class="r top Color" style=" background-color:#<?=$chat_data['Color'];?>;"><a href="#">TOP</a></div>
            </div>
        </div>
    <?php }?>
    <div id="chat_float_btn" style="background-color:#<?=$chat_data['Color'];?>;"></div>
    <script type="text/javascript">
    <?php if ($chat_data['IsHide']){?>
        $('#chat_float_btn').click(function(e) {
            $(this).css('display', 'none');
            $('.chattrans').removeClass('chathide');
        });
        $('.chattrans').mouseleave(function (e){
            var self = this;
            $(self).addClass('chathide');
            $('#chat_float_btn').css('display', 'block');
        });
    <?php }else{?>
        $('.chattrans').removeClass('chathide');
        $('#chat_float_btn').remove();
    <?php }?>
    </script>
<?php }?>
<?php 
if((int)$c['config']['Platform']['Facebook']['PageId']['IsUsed']){
	$messenger_url='https://m.me/'.$c['config']['Platform']['Facebook']['PageId']['Data']['page_id'];
?>
	<div id="facebook-messenger" class="message_us"><a href="<?=$messenger_url;?>" target="_blank"><img src="/static/ico/message-us-blue.png" alt="Message Us" /></a></div>
<?php }?>

