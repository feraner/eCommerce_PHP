<?php !isset($c) && exit();?>
<?php
manage::check_permit('content', 1, array('a'=>'set'));//检查权限

$show_ary=array('share'=>'');
$show_row=db::get_value('config', 'GroupId="content_show" and Variable="Config"', 'Value');
$show_check=str::json_data($show_row, 'decode');
?>
<script language="javascript">$(document).ready(function(){content_obj.set_init();});</script>
<div class="r_nav">
	<h1>{/module.content.set/}</h1>
</div>
<div id="set" class="r_con_wrap set_box">
	<?php
	foreach($show_ary as $k=>$v){
	?>
		<div class="box item fl">
			<div class="box child">
				<div class="model">
					<div class="title">{/page.set.<?=$k;?>/}</div>
					<div class="brief">{/page.set.<?=$k;?>_info/}</div>
				</div>
				<div class="view">
					<div class="btn fr">
						<div class="switchery<?=$show_check[$k]?' checked':'';?>" data="<?=$k;?>">
							<div class="switchery_toggler"></div>
							<div class="switchery_inner">
								<div class="switchery_state_on"></div>
								<div class="switchery_state_off"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="txt"></div>
			</div>
		</div>
	<?php }?>
	<div class="clear"></div>
</div>