<?php !isset($c) && exit();?>
<?php
manage::check_permit('mta', 1);
echo ly200::load_static('/static/js/plugin/highcharts/highcharts.js', '/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');
?>
<script language="javascript">$(document).ready(mta_obj.visits_referrer_init);</script>
<div id="mta" class="r_con_wrap">
    <div class="box nav">
        <dl class="time">
            <dt>{/global.time/}:</dt>
            <?php
            foreach(array(0,-1,-7,-30) as $k=>$v){
            ?>
                <dd><a href="javascript:void(0);" rel="<?=$v;?>" class="<?=$v==0?'cur':'';?>">{/mta.time_ary.<?=$k;?>/}</a></dd>
            <?php }?>
        </dl>
        <ul>
            <li><input type="text" name="TimeS" value="" readonly class="form_input" notnull /></li>
            <li class="compared_input"><input type="text" name="TimeE" value="" readonly class="form_input" /></li>
            <li><input type="submit" class="btn_ok" value="{/global.view/}" /></li>
            <li class="compared"></li>
            <li class="compared_txt">{/mta.compared/}</li>
        </ul>
        <dl class="terminal">
            <dt>{/mta.terminal/}:</dt>
            <?php
            foreach(array(0,1,2) as $v){
            ?>
                <dd><a href="javascript:void(0);" rel="<?=$v;?>" class="<?=$v==0?'cur':'';?>">{/mta.terminal_ary.<?=$v;?>/}</a></dd>
            <?php }?>
        </dl>
    </div>
	<div id="mta_total_data">
        <div class="box charts line_charts"></div>
        <div class="box total">
            <table border="0" cellpadding="5" cellspacing="0" class="r_con_table data_table">
                <thead>
                    <tr>
                        <td width="30%" nowrap="nowrap">{/mta.referrer/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.pv/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.average_pv/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.ip/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.uv/}</td>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div id="mta_detail_data">
    	<div class="back"><a href="javascript:;" class="btn_ok">{/global.return/}</a></div>
        <div class="box detail">
            <table border="0" cellpadding="5" cellspacing="0" class="r_con_table data_table search_engine">
                <thead>
                    <tr>
                        <td width="30%" nowrap="nowrap">{/mta.referrer/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.pv/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.average_pv/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.ip/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.uv/}</td>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <table border="0" cellpadding="5" cellspacing="0" class="r_con_table data_table share_platform">
                <thead>
                    <tr>
                        <td width="30%" nowrap="nowrap">{/mta.referrer/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.pv/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.average_pv/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.ip/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.uv/}</td>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
            <table border="0" cellpadding="5" cellspacing="0" class="r_con_table data_table other">
                <thead>
                    <tr>
                        <td width="30%" nowrap="nowrap">{/mta.referrer/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.pv/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.average_pv/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.ip/}</td>
                        <td width="18%" nowrap="nowrap">{/mta.uv/}</td>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>