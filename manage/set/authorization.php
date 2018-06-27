<?php !isset($c) && exit();?>
<?php
manage::check_permit('set', 1, array('a'=>'authorization'));//检查权限

@extract($_GET, EXTR_PREFIX_ALL, 'g');
$g_callback==1 && include('authorization.tips.php');//授权返回处理、提示页面

if(!$c['manage']['do'] || $c['manage']['do']=='index'){
	$c['manage']['do']='open';//aliexpress
}

$permit_ary=array(
	'add'	=>	$c['manage']['do']!='open'?manage::check_permit('set', 0, array('a'=>'authorization', 'd'=>$c['manage']['do'], 'p'=>'add')):0,
	'edit'	=>	manage::check_permit('set', 0, array('a'=>'authorization', 'd'=>$c['manage']['do'], 'p'=>'edit')),
	'del'	=>	manage::check_permit('set', 0, array('a'=>'authorization', 'd'=>$c['manage']['do'], 'p'=>'del'))
);
?>
<?php if($c['manage']['do']!='open'){?><script type="text/javascript">$(document).ready(function(){set_obj.authorization_init();});</script><?php }?>
<div class="r_nav">
	<?php if($_GET['iframe']!=1){?>
    <h1>{/module.set.authorization.module_name/}</h1>
    <ul class="ico">
        <?php if($permit_ary['add']){?><li><a class="tip_ico_down add" href="javascript:;" label="{/global.add/}"></a></li><?php }?>
    </ul>
    <?php }?>
	<dl class="edit_form_part">
		<?php
		$out=0;
		$open_ary=array();
		foreach($c['manage']['permit']['pc']['set']['authorization']['menu'] as $k=>$v){
			if($c['FunVersion']<2 && $v!='aliexpress') continue;
			if(!manage::check_permit('set', 0, array('a'=>'authorization', 'd'=>$v))){
				if($v=='open') $out=1;
				continue;
			}else{
				$open_ary[]=$v;
			}
		?>
            <dt></dt>
            <dd><a href="./?m=set&a=authorization&d=<?=$v;?>"<?=$c['manage']['do']==$v?' class="current"':'';?>>{/module.set.authorization.<?=$v;?>/}</a></dd>
		<?php
		}
		if($out && !@in_array($c['manage']['do'], $open_ary)) js::location('?m=set&a=authorization&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面
		?>
	</dl>
</div>
<div id="authorization" class="r_con_wrap set_box">
	<?php 
	if($c['manage']['do']=='open'){//开放接口
		$AppKey=db::get_value('config', "GroupId='API' and Variable='AppKey'", 'Value');
	?>
		<script type="text/javascript">$(document).ready(function(){set_obj.open_init();});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table open_api">
			<thead>
				<tr>
					<td width="30%" nowrap="nowrap">{/set.authorization.url/}</td>
					<td width="25%" nowrap="nowrap">{/set.authorization.access_code/}</td>
					<td width="35%" nowrap="nowrap">{/set.authorization.appkey/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td width="10%" nowrap="nowrap">{/global.operation/}</td>
					<?php }?>
				</tr>
			</thead>
			<tbody>
            	<?php if($AppKey){?>
                    <tr data-appkey="<?=$AppKey;?>">
                        <td nowrap="nowrap"><?=((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on')?'https://':'http://').$_SERVER['HTTP_HOST'].'/';?></td>
                        <td nowrap="nowrap"><?=$c['Number'];?></td>
                        <td nowrap="nowrap" class="appkey"><?=$AppKey;?></td>
                        <?php if($permit_ary['edit'] || $permit_ary['del']){?>
                            <td nowrap="nowrap">
                                <?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico enable refresh" href="javascript:;" label="{/set.authorization.refresh/}"><img src="/static/ico/refresh.png" alt="{/set.authorization.refresh/}" align="absmiddle" width="16" height="16" /></a><?php }?>
                                <?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="javascript:;" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" alt="{/global.del/}" align="absmiddle" /></a><?php }?>
                            </td>
                        <?php }?>
                    </tr>
                <?php }else{?>
                    <tr>
                        <td nowrap="nowrap" colspan="<?=($permit_ary['edit'] || $permit_ary['del'])?4:3;?>">
                            <center><p>{/set.authorization.api_disable/}</p></center>
                            <?php if($permit_ary['edit']){?><center><a class="add btn_ok enable" href="javascript:;">{/global.used/}</a></center><?php }?>
                        </td>
                    </tr>
                <?php }?>
			</tbody>
		</table>
	<?php }elseif($c['manage']['do']=='aliexpress'){//速卖通授权?>
		<script type="text/javascript">
        var submitPlatformAuth = function(){
            window.top.location.reload();
            $('.box_authorization_add').hide();
            $('#div_mask').remove();
        };
        $(document).ready(function(){set_obj.aliexpress_init();});
        </script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<td width="25%" nowrap="nowrap">{/set.authorization.shop_name/}</td>
					<td width="25%" nowrap="nowrap">{/module.set.authorization.aliexpress/}{/set.chat.account/}</td>
					<td width="40%" nowrap="nowrap">{/set.authorization.token_valid/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td width="10%" nowrap="nowrap">{/global.operation/}</td>
					<?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$row=db::get_all('authorization', "Platform='{$c['manage']['do']}'");
				foreach($row as $v){
				?>
					<tr aid="<?=$v['AId'];?>" account="<?=$v['Account'];?>">
						<td class="name" nowrap="nowrap"><?=$v['Name'];?></td>
						<td nowrap="nowrap"><?=$v['Account'];?></td>
						<td nowrap="nowrap"><?=@date('Y-m-d H:i:s', $v['AccTime']);?>{/set.authorization.to/}<?=@date('Y-m-d H:i:s', $v['ValidTime']);?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico mod" href="javascript:;" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico refresh" href="javascript:;" label="{/set.authorization.refresh/}"><img src="/static/ico/refresh.png" alt="{/set.authorization.refresh/}" align="absmiddle" width="16" height="16" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="javascript:;" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" alt="{/global.del/}" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<?php /***************************** 添加店铺弹出框 Start *****************************/?>
        <div class="pop_form box_authorization_add">
            <form name="authorization_add">
                <div class="t"><h1>{/global.add/}{/module.set.authorization.<?=$c['manage']['do'];?>/}{/set.authorization.authorization/}<em></em></h1><h2>×</h2></div>
                <div class="r_con_form">
                    <div class="rows">
                        <label><font class="fc_red">*</font> {/set.authorization.shop_name/}</label>
                        <span class="input"><input type="text" class='form_input' value="" name="Name" maxlength="50" notnull /><label>{/set.authorization.shop_name_tips/}</label></span>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/set.authorization.authorization/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
                <input type="hidden" name="d" value="<?=$c['manage']['do'];?>" />
            </form>
        </div>
        <?php /***************************** 添加店铺弹出框 End *****************************/?>
        <?php /***************************** 编辑店铺弹出框 Start *****************************/?>
        <div class="pop_form box_authorization_edit">
            <form name="authorization_mod">
                <div class="t"><h1>{/global.mod/}{/set.authorization.shop_name/}<em></em></h1><h2>×</h2></div>
                <div class="r_con_form">
                    <div class="rows">
                        <label><font class="fc_red">*</font> {/set.authorization.shop_name/}</label>
                        <span class="input"><input type="text" class='form_input' value="" name="Name" maxlength="50" notnull /></span>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
                <input type="hidden" name="AId" value="" notnull />
                <input type="hidden" name="d" value="" />
                <input type="hidden" name="do_action" value="" />
            </form>
        </div>
        <?php /***************************** 编辑店铺弹出框 End *****************************/?>
	<?php }elseif($c['manage']['do']=='amazon'){?>
		<script type="text/javascript">$(document).ready(function() {set_obj.amazon_init();});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<td width="20%" nowrap="nowrap">{/set.authorization.shop_name/}</td>
					<td width="20%" nowrap="nowrap">{/set.authorization.seller_id/}</td>
					<td width="30%" nowrap="nowrap">{/set.authorization.marketplace/}</td>
					<td width="20%" nowrap="nowrap">{/set.authorization.authorization_time/}</td>
					<?php if($permit_ary['edit'] || $permit_ary['del']){?>
						<td width="10%" nowrap="nowrap">{/global.operation/}</td>
					<?php }?>
				</tr>
			</thead>
			<tbody>
				<?php
				$row=db::get_all('authorization', "Platform='{$c['manage']['do']}'");
				foreach($row as $v){
					$MarkectPlace=@explode(',', $v['Data']);
				?>
					<tr aid="<?=$v['AId'];?>" account="<?=str::str_code($v['Token']);?>">
						<td class="name" nowrap="nowrap"><?=$v['Name'];?></td>
						<td nowrap="nowrap"><?=$v['Account'];?></td>
						<td nowrap="nowrap"><?php foreach($MarkectPlace as $key=>$val){echo ($key?', ':'')."{/set.authorization.{$val}/}";}?></td>
						<td nowrap="nowrap"><?=@date('Y-m-d H:i:s', $v['AccTime']);?></td>
						<?php if($permit_ary['edit'] || $permit_ary['del']){?>
							<td nowrap="nowrap">
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico mod" href="javascript:;" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a><?php }?>
								<?php if($permit_ary['edit']){?><a class="tip_ico tip_min_ico refresh" href="javascript:;" label="{/set.authorization.refresh/}"><img src="/static/ico/refresh.png" alt="{/set.authorization.refresh/}" align="absmiddle" width="16" height="16" /></a><?php }?>
								<?php if($permit_ary['del']){?><a class="tip_ico tip_min_ico del" href="javascript:;" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" alt="{/global.del/}" align="absmiddle" /></a><?php }?>
							</td>
						<?php }?>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<?php /***************************** 添加店铺弹出框 Start *****************************/?>
        <div class="pop_form box_authorization_add pop_form_amazon">
            <form name="authorization_add">
                <div class="t"><h1>{/global.add/}{/module.set.authorization.<?=$c['manage']['do'];?>/}{/set.authorization.authorization/}<em></em></h1><h2>×</h2></div>
                <div class="r_con_form">
                    <div class="rows">
                        <label><font class="fc_red">*</font> {/set.authorization.shop_name/}</label>
                        <span class="input"><input type="text" class="form_input" placeholder="{/set.authorization.shop_name/}" value="" name="Name" maxlength="50" notnull /></span>
                        <div class="clear"></div>
                    </div>
                    <div class="rows">
                        <label><font class="fc_red">*</font> {/set.authorization.account_site/}</label>
                        <span class="input">
                        	<select name="MarkectPlace" notnull>
                            	<option value="">{/global.select_index/}</option>
                            	<?php foreach($c['manage']['sync_ary']['amazon'] as $k=>$v){?>
                                	<option value="<?=$k;?>" data-url="<?=$v[0];?>">{/set.authorization.<?=$k;?>/}</option>
                                <?php }?>
                            </select>&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="javascript:;" class="blue">{/set.authorization.go/}</a>
                        </span>
                        <div class="clear"></div>
                    </div>
                    <div class="rows">
                        <label><font class="fc_red">*</font> {/set.authorization.merchant_id/}</label>
                        <span class="input"><input type="text" class='form_input' value="" name="MerchantId" size="40" maxlength="20" placeholder="{/set.authorization.seller_id/}" notnull />&nbsp;&nbsp;<a href="http://www.ueeshop.com/u_file/other/amazon.pdf" target="_blank" title="{/global.reference/}"><img src="/static/ico/notes.png" /></a></span>
                        <div class="clear"></div>
                    </div>
                    <div class="rows">
                        <label><font class="fc_red">*</font> {/set.authorization.aws_access_key_id/}</label>
                        <span class="input"><input type="text" class='form_input' value="" name="AWSAccessKeyId" size="40" maxlength="30" placeholder="{/set.authorization.access_key_tips/}" notnull /></span>
                        <div class="clear"></div>
                    </div>
                    <div class="rows">
                        <label><font class="fc_red">*</font> {/set.authorization.secret_key/}</label>
                        <span class="input"><input type="text" class='form_input' value="" name="SecretKey" size="40" maxlength="50" placeholder="{/set.authorization.secret_key_tips/}" notnull /></span>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/set.authorization.authorization/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
                <input type="hidden" name="d" value="amazon" />
                <input type="hidden" name="do_action" value="" />
            </form>
        </div>
        <?php /***************************** 添加店铺弹出框 End *****************************/?>
        <?php /***************************** 编辑店铺弹出框 Start *****************************/?>
        <div class="pop_form box_authorization_edit pop_form_amazon">
            <form name="authorization_mod">
                <div class="t"><h1>{/global.mod/}{/module.set.authorization.<?=$c['manage']['do'];?>/}{/set.authorization.authorization/}<em></em></h1><h2>×</h2></div>
                <div class="r_con_form">
                    <div class="rows">
                        <label><font class="fc_red">*</font> {/set.authorization.shop_name/}</label>
                        <span class="input"><input type="text" class="form_input" value="" name="Name" maxlength="50" notnull /></span>
                        <div class="clear"></div>
                    </div>
                    <div class="rows">
                        <label><font class="fc_red">*</font> {/set.authorization.account_site/}</label>
                        <span class="input">
                        	<select name="MarkectPlace" notnull>
                            	<option value="">{/global.select_index/}</option>
                            	<?php foreach($c['manage']['sync_ary']['amazon'] as $k=>$v){?>
                                	<option value="<?=$k;?>" data-url="<?=$v[0];?>">{/set.authorization.<?=$k;?>/}</option>
                                <?php }?>
                            </select>&nbsp;&nbsp;&nbsp;&nbsp;
                            <a href="javascript:;" class="amazon_url blue">{/set.authorization.go/}</a>
                        </span>
                        <div class="clear"></div>
                    </div>
                    <div class="rows">
                        <label><font class="fc_red">*</font> {/set.authorization.merchant_id/}</label>
                        <span class="input"><input type="text" class='form_input' value="" name="MerchantId" size="40" maxlength="20" readonly /></span>
                        <div class="clear"></div>
                    </div>
                    <div class="rows">
                        <label><font class="fc_red">*</font> {/set.authorization.aws_access_key_id/}</label>
                        <span class="input"><input type="text" class='form_input' value="" name="AWSAccessKeyId" size="40" maxlength="30" readonly /></span>
                        <div class="clear"></div>
                    </div>
                    <div class="rows">
                        <label><font class="fc_red">*</font> {/set.authorization.secret_key/}</label>
                        <span class="input"><input type="text" class='form_input' value="" name="SecretKey" size="40" maxlength="50" readonly /></span>
                        <div class="clear"></div>
                    </div>
                </div>
                <div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
                <input type="hidden" name="AId" value="" notnull />
                <input type="hidden" name="d" value="" />
                <input type="hidden" name="do_action" value="" />
                <input type="hidden" name="method" value="" />
            </form>
        </div>
        <?php /***************************** 编辑店铺弹出框 End *****************************/?>
    <?php }?>
</div>
