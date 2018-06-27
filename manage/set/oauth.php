<?php !isset($c) && exit();?>
<?php
manage::check_permit('set', 1, array('a'=>'oauth'));//检查权限

$permit_ary['edit']=manage::check_permit('set', 0, array('a'=>'oauth', 'd'=>'edit'));

echo ly200::load_static('/static/js/plugin/operamasks/operamasks-ui.css', '/static/js/plugin/operamasks/operamasks-ui.min.js');
?>
<script type="text/javascript">$(document).ready(function(){set_obj.oauth_init()});</script>
<div class="r_nav">
	<h1>{/module.set.oauth/}</h1>
</div>
<div id="oauth" class="r_con_wrap">
	<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
		<thead>
			<tr>
				<td width="10%" nowrap="nowrap">{/global.serial/}</td>
				<td width="25%" nowrap="nowrap">{/global.name/}</td>
				<td width="25%" nowrap="nowrap">{/global.logo/}</td>
				<td width="15%" nowrap="nowrap">{/global.turn_on/}</td>
				<?php /*<td width="15%" nowrap="nowrap">{/global.reference/}</td>*/?>
				<?php if($permit_ary['edit']){?><td width="10%" nowrap="nowrap">{/global.operation/}</td><?php }?>
			</tr>
		</thead>
		<tbody>
			<?php
			$i=1;
			$oauth_row=str::str_code(db::get_all('sign_in', 1, '*', $c['my_order'].'SId asc'));
			foreach($oauth_row as $k=>$v){
				if($c['FunVersion']==0 && $v['Title']!='Facebook') continue; //标准版的踢出，除了Facebook
				$pic=($v['LogoPath'] && is_file($c['root_path'].$v['LogoPath']))?$v['LogoPath']:'';
				$v['IsLogoPath']=$pic?1:0; //判断图片是否存在
				$data=str::json_data(htmlspecialchars_decode($v['Data']), 'decode');
			?>
			<tr data="<?=htmlspecialchars(str::json_data($v));?>">
				<td nowrap="nowrap"><?=$k+1;?></td>
				<td nowrap="nowrap"><?=$v['Title'];?></td>
				<td nowrap="nowrap" class="img"><?php if($pic){?><img src="<?=$pic;?>" <?=img::img_width_height($pic, 200, 100);?> /><?php }?></td>
				<?php /*<td nowrap="nowrap"><?=$c['manage']['lang_pack']['global']['n_y'][$v['IsUsed']];?></td>*/?>
				<td nowrap="nowrap">
					<?php
					foreach($data as $k2=>$v2){
						if($c['FunVersion']==0 && $k2=='SignIn') continue; //标准版的踢出，第三方登录
						echo $c['manage']['lang_pack']['set']['oauth']['platform_ary'][$k2].': '.($v2['IsUsed']==1?'<img src="/static/manage/images/set/current.png" />':'');
						if($k2=='SignIn') echo '&nbsp;&nbsp;<a href="http://www.ueeshop.com/u_file/other/'.strtolower($v['Title']).'.pdf" target="_blank" title="{/global.reference/}"><img src="/static/ico/notes.png" /></a>';
						echo '<br />';
					}?>
				</td>
				<?php /*<td nowrap="nowrap"><a href="http://www.ueeshop.com/u_file/other/<?=strtolower($v['Title']);?>.pdf" target="_blank"><img src="/static/ico/explode.png" /></a></td>*/?>
				<?php if($permit_ary['edit']){?>
					<td nowrap="nowrap"><a class="tip_ico tip_min_ico edit" href="javascript:;" label="{/global.edit/}" data-id="<?=$v['SId'];?>"><img src="/static/ico/edit.png" alt="{/global.edit/}" title="{/global.edit/}" /></a></td>
				<?php }?>
			</tr>
			<?php }?>
		</tbody>
	</table>
	<input type="hidden" value="<?=$c['FunVersion'];?>" id="FunVersion" />
	<?php /***************************** 第三方登录编辑 Start *****************************/?>
	<div class="pop_form box_oauth_edit">
		<form id="edit_form">
			<div class="t"><h1></h1><h2>×</h2></div>
			<div class="r_con_form">
				<div class="rows">
					<label>{/global.pic/}</label>
					<span class="input upload_file upload_logo">
						<div class="img">
							<div id="LogoDetail" class="upload_box preview_pic"><input type="button" id="LogoUpload" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="" /></div>
						</div>
						<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
						<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
					</span>
					<div class="clear"></div>
				</div>
				<div class="account_info"></div>
				<input type="hidden" name="LogoPath" value="" save="0" />
				<input type="hidden" id="SId" name="SId" value="" />
				<input type="hidden" name="do_action" value="set.oauth_edit" />
			</div>
			<div class="button">
				<input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" />
				<input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" />
			</div>
		</form>
	</div>
	<?php /***************************** 第三方登录编辑 End *****************************/?>
</div>