<?php !isset($c) && exit();?>
<?php 
manage::check_permit('extend', 1, array('a'=>'search_logs'));//检查权限
?>
<script type="text/javascript">$(document).ready(function(){extend_obj.search_logs_init();});</script>
<div class="r_nav">
	<h1>{/module.extend.search_logs/}</h1>
	<div class="turn_page"></div>
    <div class="search_form">
        <form method="get" action="?">
            <div class="k_input">
                <input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
                <input type="button" value="" class="more" />
            </div>
            <input type="submit" class="search_btn" value="{/global.search/}" />
            <input type="hidden" name="m" value="extend" />
            <input type="hidden" name="a" value="search_logs" />
        </form>
    </div>
    <ul class="ico">
        <li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li>
    </ul>
</div>
<div id="search_logs" class="r_con_wrap">
    <table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
        <thead>
            <tr>
            	<td width="5%"><input type="checkbox" name="select_all" value="" class="va_m" /></td>
                <td width="30%" nowrap="nowrap">{/global.keyword/}</td>
                <td width="10%" nowrap="nowrap">{/search_logs.number/}</td>
                <td width="10%" nowrap="nowrap">{/search_logs.result/}</td>
                <td width="30%" nowrap="nowrap">{/set.country.country/}</td>
                <td width="15%" class="last" nowrap="nowrap">{/search_logs.time/}</td>
            </tr>
        </thead>
        <tbody>
            <?php
            $page_count=20;//显示数量
			$where=1;
			$_GET['Keyword'] && $where.=" and Keyword='{$_GET['Keyword']}'";
            $logs_row=str::str_code(db::get_limit_page('search_logs', $where, '*', 'LId desc', (int)$_GET['page'], $page_count));
            foreach($logs_row[0] as $v){
            ?>
                <tr>
                	<td><input type="checkbox" name="select" value="<?=$v['LId'];?>" class="va_m" /></td>
                    <td><?=$v['Keyword']?></td>
                    <td><?=$v['Number']?></td>
                    <td><?=$v['Result']?></td>
                    <td><?=$v['Country']?></td>
                    <td><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
                </tr>
            <?php }?>
        </tbody>
    </table>
    <div id="turn_page"><?=manage::turn_page($logs_row[1], $logs_row[2], $logs_row[3], '?'.ly200::query_string('page').'&page=');?></div>
</div>