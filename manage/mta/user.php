<?php !isset($c) && exit();?>
<?php
manage::check_permit('mta', 1);
echo ly200::load_static('/static/js/plugin/highcharts/highcharts.js', '/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');

$tips_ary=array(date('Y', $c['time']), date('m', $c['time']));
$c['manage']['config']['ManageLanguage']=='en' && $tips_ary=array(date('m', $c['time']), date('Y', $c['time']));
?>
<?=ly200::load_static('/static/js/plugin/radialIndicator/radialIndicator.min.js');?>
<script language="javascript">$(document).ready(mta_obj.user_init);</script>
<div id="mta" class="r_con_wrap">
    <h3><?=@date('Y-m', $c['time']);?></h3>
    <ul class="box notop data_list">
        <li>
            <div>
                <h1>{/mta.user.new_member/}<span class="tool_tips_ico" content="<?=sprintf($c['manage']['lang_pack']['mta']['user']['new_member_tips'], $tips_ary[0], $tips_ary[1]);?>">&nbsp;</span></h1>
                <h2><span class="new_member">0</span></h2>
            </div>
        </li>
        <li>
            <div>
                <h1>{/mta.user.active_member/}<span class="tool_tips_ico" content="<?=sprintf($c['manage']['lang_pack']['mta']['user']['active_member_tips'], $tips_ary[0], $tips_ary[1]);?>">&nbsp;</span></h1>
                <h2><span class="active_member">0</span></h2>
            </div>
        </li>
        <li>
            <div>
                <h1>{/mta.user.core_member/}<span class="tool_tips_ico" content="<?=sprintf($c['manage']['lang_pack']['mta']['user']['core_member_tips'], $tips_ary[0], $tips_ary[1]);?>">&nbsp;</span></h1>
                <h2><span class="core_member">0</span></h2>
            </div>
        </li>
        <li>
            <div>
                <h1>{/mta.user.total_member/}<span class="tool_tips_ico" content="{/mta.user.total_member_tips/}">&nbsp;</span></h1>
                <h2><span class="total_member">0</span></h2>
            </div>
        </li>
    </ul>
    <h3><?=@date('Y-m', strtotime("-11 month", strtotime(date('Y-m-01', $c['time'])))).' 至 '.date('Y-m', $c['time']);?></h3>
	<div class="box charts trend_charts"></div>
	<div class="box charts country_charts"></div>
    <div class="box user_detail">
    	<ul>
			<li class="double gender fl">
            	<div class="rate"></div>
                <div class="data">
                	<dl>
                    	<dt>{/mta.user.gender_statistics/}</dt>
                        <dd></dd>
                    </dl>
                </div>
            </li>
			<li class="double level fr">
            	<div class="rate"></div>
                <div class="data">
                	<dl>
                    	<dt>{/mta.user.level_statistics/}</dt>
                        <dd></dd>
                    </dl>
                </div>
            </li>
        </ul>
        <div class="clear"></div>
    </div>
</div>