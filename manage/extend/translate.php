<?php !isset($c) && exit();?>
<?php 
manage::check_permit('extend', 1, array('a'=>'translate'));//检查权限

$rows=db::get_all('config', "GroupId='translate'");
$translate=array();
foreach($rows as $k=>$v){
	$translate[$v['Variable']]=$v['Value'];
}
?>
<script language="javascript">$(document).ready(function(){extend_obj.translate_init()});</script>
<div class="r_nav">
	<h1>{/module.extend.translate/}</h1>
	<div class="switchery<?=$translate['IsTranslate']?' checked':'';?>">
		<div class="switchery_toggler"></div>
		<div class="switchery_inner">
			<div class="switchery_state_on"></div>
			<div class="switchery_state_off"></div>
		</div>
	</div>
</div>
<div id="translate" class="r_con_wrap">
	<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
		<thead>
			<tr>
				<td width="50%" nowrap="nowrap">{/set.config.language_list/}</td>
				<td width="50%" nowrap="nowrap">{/global.used/}</td>
			</tr>
		</thead>
		<tbody>
			<?php
			$key=$c['manage']['config']['ManageLanguage']=='en'?1:0;
			$LangArr=str::json_data($translate['TranLangs'], 'decode');
			foreach((array)$c['translate'] as $k=>$v){
				$Used=@in_array($k, (array)$LangArr)?1:0;
			?>
				<tr lang="<?=$k;?>">
					<td><?=$v[$key];?></td>
					<td class="used_checkbox">
						<div class="switchery<?=$Used?' checked':'';?>">
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
					</td>
				</tr>
			<?php }?>
		</tbody>
	</table>
</div>