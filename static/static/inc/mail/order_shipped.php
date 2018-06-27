<?php !isset($c) && exit();?>
<?php
!isset($orders_row) && $orders_row=db::get_one('orders', "OId='$OId'");
$isFee=($orders_row['OrderStatus']>=4 && $orders_row['OrderStatus']!=7)?1:0;
$total_price=orders::orders_price($orders_row, $isFee);

$shipping_cfg=(int)$orders_row['ShippingMethodSId']?db::get_one('shipping', "SId='{$orders_row['ShippingMethodSId']}'"):db::get_one('shipping_config', "Id='1'");
$shipping_row=db::get_one('shipping_area', "AId in(select AId from shipping_country where CId='{$orders_row['ShippingCId']}' and  SId='{$orders_row['ShippingMethodSId']}' and type='{$orders_row['ShippingMethodType']}')");

$Template='order_shipped';//事件名称
$mail_data=array('orders_row'=>$orders_row, 'isFee'=>$isFee, 'shipping_cfg'=>$shipping_cfg, 'shipping_row'=>$shipping_row, 'trackingNumberStr'=>$trackingNumberStr, 'ShippingTimeStr'=>$ShippingTimeStr);//传入模板的数据
include('inc/static.php');

if($mail_contents==''){//默认模板
	ob_start();
	$c['lang_pack_email']=include('lang/'.$mail_lang.'.php');//加载语言包
	$mail_title=ly200::system_email_tpl($c['lang_pack_email'][$Template], $mail_data);
?>
    <div style="width:700px; margin:10px auto;">
        <?php include('inc/header.php');?>
        <div style="font-family:Arial; padding:15px 0; line-height:150%; min-height:100px; _height:100px; color:#333; font-size:12px;">
            <?=str_replace('%name%', '<strong>'.htmlspecialchars($orders_row['ShippingFirstName'].' '.$orders_row['ShippingLastName']).'</strong>', $c['lang_pack_email']['dear']);?>:<br /><br />
    
            <?=str_replace('%domain%', '<a href="'.ly200::get_domain().'" target="_blank" style="color:#1E5494; text-decoration:underline; font-family:Arial; font-size:12px;">'.ly200::get_domain(0).'</a>', $c['lang_pack_email']['not_reply']);?><br /><br />
            
            <?=str_replace('%oid%', '<a href="'.ly200::get_domain().'/account/orders/view'.$orders_row['OId'].'.html" target="_blank" style="color:#1E5494; text-decoration:underline; font-family:Arial; font-size:12px;">'.$orders_row['OId'].'</a>', $c['lang_pack_email']['shippedInfo']);?><br /><br />
            
            <?=$c['lang_pack_email']['shippingMethod'];?>: <?=(int)$orders_row['ShippingMethodSId']?$shipping_cfg['Express']:($orders_row['ShippingMethodType']=='air'?$shipping_cfg['AirName']:$shipping_cfg['OceanName']);?> <?=$shipping_row['Brief']?"({$shipping_row['Brief']})":'';?><br />
            <?=$c['lang_pack_email']['trackingNumber'];?>: <?=$trackingNumberStr?$trackingNumberStr:$orders_row['TrackingNumber'];?> (<?=date('m/d-Y', ($ShippingTimeStr?$ShippingTimeStr:$orders_row['ShippingTime']));?>)
            <?php if($orders_row['TrackingNumber'] && $shipping_cfg['Query']){?>
                <br /><a class="query" href="<?=$shipping_cfg['Query'];?>" target="_blank"><?=$c['lang_pack_email']['query'];?></a>
            <?php }?>
            <br /><br />
            
            <?php include('order_detail.php');?>
            
            <?=$c['lang_pack_email']['sincerely'];?>,<br /><br />
            
            <?=str_replace('%domain%', ly200::get_domain(0), $c['lang_pack_email']['customer']);?>
        </div>
        <?php include('inc/footer.php');?>
    </div>
<?php
	$mail_contents=ob_get_contents();
	ob_end_clean();
}
?>