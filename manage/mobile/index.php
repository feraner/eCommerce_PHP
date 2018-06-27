<?php !isset($c) && exit();?>
<?php
(!(int)$c['FunVersion'] && (int)$c['NewFunVersion']) && js::location('./');
manage::check_permit('mobile', 1);//检查权限

if(!$c['manage']['do'] || $c['manage']['do']=='index'){//重新指向“首页管理”页面
	$c['manage']['do']='themes';
}
?>
<div class="r_nav">
	<h1>{/frame.mobile/}</h1>
	<div class="turn_page"></div>
	<dl class="edit_form_part">
		<?php
		$out=0;
		$open_ary=array();
		foreach($c['manage']['permit']['mobile']['mobile'] as $k=>$v){
			if(!manage::check_permit('mobile', 0, array('a'=>$k)) || ($c['FunVersion']==100 && in_array($k, array('themes', 'list')))){
				if($k=='themes' && $c['manage']['do']=='themes') $out=1;
				continue;
			}else{
				$open_ary[]=$k;
			}
		?>
			<dt></dt>
			<dd><a href="./?m=mobile&d=<?=$k;?>"<?=$c['manage']['do']==$k?' class="current"':'';?>>{/module.mobile.<?=$k;?>/}</a></dd>
			<?php
		}
		if($out) js::location('?m=mobile&d='.$open_ary[0]);//当第一个选项没有权限打开，就跳转能打开的第一个页面
		?>
	</dl>
</div>
<div id="mobile" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='themes'){
		//首页管理
		$set_ary=array();
		$set_row=db::get_all('config', "GroupId='mobile'");
		foreach($set_row as $v){
			$set_ary[$v['Variable']]=$v['Value'];
		}
		$tpl_dir=$c['mobile']['tpl_dir'].'index/';//模板目录
		$base_dir=$c['root_path'].$tpl_dir;//邮件模板目录绝对路径
	?>
		<script type="text/javascript">$(document).ready(function(){mobile_obj.themes_edit_init()});</script>
		<div class="temp_list clean">
			<?php
			for($i=1; $i<=9; ++$i){
				$tpl="0{$i}";
				if(!is_dir($base_dir.$tpl)) continue;
				$tpl_img="{$tpl_dir}{$tpl}/cover.jpg";//封面
			?>
				<div class="item fl<?=$set_ary['HomeTpl']==$tpl?' current':'';?>" data-tpl="<?=$tpl;?>">
					<div class="img"><img src="<?=$tpl_img;?>" /><div class="img_mask"></div></div>
					<div class="info"><span><?=$tpl;?></span></div>
				</div>
			<?php }?>
			<div class="blank20"></div>
		</div>
	<?php
	}elseif($c['manage']['do']=='list'){
		//列表页管理
		$set_ary=array();
		$set_row=db::get_all('config', "GroupId='mobile'");
		foreach($set_row as $v){
			$set_ary[$v['Variable']]=$v['Value'];
		}
		$tpl_dir=$c['mobile']['tpl_dir'].'products/';//模板目录
		$base_dir=$c['root_path'].$tpl_dir;//邮件模板目录绝对路径
	?>
		<script type="text/javascript">$(document).ready(function(){mobile_obj.list_edit_init()});</script>
		<div class="temp_list clean">
			<?php
			for($i=1; $i<=5; ++$i){
				$tpl="0{$i}";
				if(!is_dir($base_dir.$tpl)) continue;
				$tpl_img="{$tpl_dir}{$tpl}/cover.jpg";//封面
			?>
				<div class="item fl<?=$set_ary['ListTpl']==$tpl?' current':'';?>" data-tpl="<?=$tpl;?>">
					<div class="img"><img src="<?=$tpl_img;?>" /><div class="img_mask"></div></div>
					<div class="info"><span><?=$tpl;?></span></div>
				</div>
			<?php }?>
			<div class="blank20"></div>
		</div>
	<?php
	}elseif($c['manage']['do']=='config'){
		//基本设置
		$set_ary=array();
		$set_row=db::get_all('config', "GroupId='mobile'");
		foreach($set_row as $v){
			$set_ary[$v['Variable']]=$v['Value'];
		}
		echo ly200::load_static('/static/js/plugin/jscolor/jscolor.js');
	?>
		<script type="text/javascript">$(document).ready(function(){mobile_obj.config_edit_init()});</script>
		<form id="edit_form" class="r_con_form">
			<h3 class="rows_hd">{/set.config.basic_info/}</h3>
			<div class="rows">
				<label>{/set.config.logo/}</label>
				<span class="input upload_file upload_logo">
					<div class="img">
						<div id="LogoDetail" class="upload_box preview_pic"><input type="button" id="LogoUpload" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.png_tips/}{/notes.pic_size_tips/}'), 'auto*58');?>" /></div>
						{/notes.png_tips/}<?=sprintf(manage::language('{/notes.pic_size_tips/}'), 'auto*58');?>
					</div>
					<a href="javascript:;" label="{/global.edit/}" class="tip_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
					<a href="javascript:;" label="{/global.del/}" class="tip_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
				</span>
				<div class="clear"></div>
			</div>
			
			<?php if($c['FunVersion']!=100){?>
				<h3 class="rows_hd">{/mobile.config.header/}</h3>
				<div class="rows">
					<label>{/mobile.icon/}</label>
					<span class="input clean">
						<div class="fl headicon">
							<div class="img <?=!$set_ary['HeadIcon']?'on':'';?>" data-icon="0"><img src="/static/manage/images/mobile/white_icon.png" /></div>
							{/mobile.color.0/}
						</div>
						<div class="fl headicon">
							<div class="img <?=$set_ary['HeadIcon']?'on':'';?>" data-icon="1"><img src="/static/manage/images/mobile/gray_icon.png" /></div>
							{/mobile.color.1/}
						</div>
						<input type="hidden" name="icon" value="<?=$set_ary['HeadIcon']?>" />
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/mobile.fixed/}</label>
					<span class="input">
						<div class="switchery<?=$set_ary['HeadFixed']?' checked':'';?>">
							<input type="checkbox" name="fixed" value="1"<?=$set_ary['HeadFixed']?' checked':'';?>>
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
					</span>
					<div class="clear"></div>
				</div>
			<?php }?>
			<div class="rows">
				<label></label>
				<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
				<div class="clear"></div>
			</div>
			
			<input type="hidden" name="LogoPath" value="<?=$set_ary['LogoPath'];?>" save="<?=is_file($c['root_path'].$set_ary['LogoPath'])?1:0;?>" />
			<input type="hidden" name="do_action" value="mobile.config_edit" />
		</form>
		<div id="cus_html">
			<div class="rows">
				<label>{/global.name/}</label>
				<span class="input">
					<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
						<span class='price_input'><b>{/language.<?=$v;?>/}<div class='arrow'><em></em><i></i></div></b><input type='text' name='Name_<?=$v;?>[]' value='' notnull="" class='form_input' size='41' maxlength='20'></span>
						<div class="blank6"></div>
					<?php }?>
					{/mobile.link/}:<input type="text" notnull="" size="40" maxlength="150" class="form_input" value="" name="Url[]"> <a class="del" href="javascript:void(0);"><img src="/static/ico/del.png" /></a>
				</span>
				<div class="clear"></div>
			</div>
		</div>
	<?php }?>
</div>