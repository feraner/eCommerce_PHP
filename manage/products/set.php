<?php !isset($c) && exit();?>
<?php
manage::check_permit('products', 1, array('a'=>'set'));//检查权限

$set_ary=array('review'=>array('range'=>2, 'display'=>2, 'code'=>2, 'anonymous'=>2), 'favorite'=>'', 'share'=>'', 'freight'=>'', 'pdf'=>'', 'myorder'=>'', 'attr'=>'', 'price'=>'', 'fixed_price'=>'', 'stock'=>'', 'selected'=>'', 'wholesale'=>'', 'wholesale_type'=>'', 'sales'=>'', 'inventory'=>'', 'weight'=>'', 'freeshipping'=>'', 'item'=>'', 'manage_myorder'=>'', 'sku'=>'');
$c['FunVersion']==100 && $set_ary=array('review'=>array('range'=>2, 'display'=>2, 'code'=>2, 'anonymous'=>2), 'myorder'=>'', 'freight'=>'', 'attr'=>'', 'stock'=>'', 'wholesale_type'=>'', 'weight'=>'');
$set_row=db::get_all('config', 'GroupId="products_show"');
foreach($set_row as $k=>$v){
	if($v['Variable']=='Config' || $v['Variable']=='review' || $v['Variable']=='favorite' || $v['Variable']=='wholesale'){
		$set_check[$v['Variable']]=str::json_data($v['Value'], 'decode');
	}else{
		$set_check[$v['Variable']]=$v['Value'];
	}
}
?>
<script type="text/javascript">$(document).ready(function(){products_obj.set_init();});</script>
<div class="r_nav">
	<h1>{/module.products.set/}</h1>
</div>
<div id="set" class="r_con_wrap set_box">
	<?php
    foreach($set_ary as $k=>$v){
    ?>
        <div class="box item fl">
            <div class="box child">
                <div class="model">
                    <div class="title">{/products.set.<?=$k;?>/}</div>
                    <div class="brief brief_<?=$c['manage']['config']['ManageLanguage'];?>">{/products.set.<?=$k;?>_info/}</div>
                </div>
                <div class="view">
                    <?php if($v){?><a class="set fl" href="javascript:;">{/global.set/}</a><?php }?>
					<?php if($k=='favorite'){?>
                    	<div class="favorite fl">{/products.set.range/}<span class="tool_tips_ico" content="{/products.set.favorite_notes/}"></span>: <input name="favorite[]" value="<?=(int)$set_check['favorite'][0];?>" type="text" class="form_input" size="1" maxlength="5" notnull />~<input name="favorite[]" value="<?=(int)$set_check['favorite'][1];?>" type="text" class="form_input" size="1" maxlength="5" notnull /><button class="sub_btn">{/global.submit/}</button></div>
                    <?php }?>
                    <?php if($k=='myorder'){?>
                    	<div class="number fl">{/products.set.prefix/}: <input name="Number" value="<?=$set_check['myorder'];?>" type="text" class="form_input" size="8" maxlength="15" notnull /><button class="sub_btn">{/global.submit/}</button></div>
                    <?php }?>
					<?php if($k=='item'){?>
                    	<div class="item fl"><input name="Item" value="<?=$set_check['item'];?>" type="text" class="form_input" size="8" maxlength="15" notnull /><button class="sub_btn">{/global.submit/}</button></div>
                    <?php }?>
                    <div class="btn fr">
                        <div class="switchery<?=$set_check['Config'][$k]?' checked':'';?>" data="<?=$k;?>">
                            <div class="switchery_toggler"></div>
                            <div class="switchery_inner">
                                <div class="switchery_state_on"></div>
                                <div class="switchery_state_off"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="txt">
                    <?php
                    if($v){
                        foreach((array)$v as $k2=>$v2){
                    	?>
                            <div name="<?=$k;?>">
                                <div class="txt_name">{/products.set.<?=$k;?>_<?=$k2?>.0/}:</div>
                                <?php for($i=1; $i<=$v2; ++$i){?>
                                    <span class="choice_btn<?=$set_check[$k][$k2]==$i?' current':'';?>" data="<?=$k2;?>" value="<?=$i;?>">{/products.set.<?=$k;?>_<?=$k2?>.<?=$i;?>/}</span>
                                <?php }?>
                            </div>
                    	<?php
                        }
                    }
					?>
                </div>
            </div>
        </div>
    <?php }?>
</div>