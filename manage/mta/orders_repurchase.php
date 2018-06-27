<?php !isset($c) && exit();?>
<?php
manage::check_permit('mta', 1);
echo ly200::load_static('/static/js/plugin/highcharts/highcharts.js', '/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');
?>
<script language="javascript">$(document).ready(mta_obj.orders_repurchase_init);</script>
<div id="mta" class="r_con_wrap">
	<div class="box nav">
		<dl class="mta_cycle">
			<dt>{/mta.mta_cycle/}:</dt>
			<?php
			foreach((array)$c['manage']['lang_pack']['mta']['mta_cycle_ary'] as $k=>$v){
				if(!$k) continue;
			?>
				<dd><a href="javascript:void(0);" rel="<?=$k;?>" class="<?=$k==2?'cur':'';?>"><?=$v;?></a></dd>
			<?php }?>
		</dl>
		<dl class="terminal">
			<dt>{/mta.terminal/}:</dt>
			<?php
			foreach(array(0,1,2) as $v){
			?>
				<dd><a href="javascript:void(0);" rel="<?=$v;?>" class="<?=$v==0?'cur':'';?>">{/mta.terminal_ary.<?=$v;?>/}</a></dd>
			<?php }?>
		</dl>
	</div>
	<div class="box charts repurchase_charts"></div>
</div>