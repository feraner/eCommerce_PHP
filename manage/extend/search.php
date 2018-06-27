<?php !isset($c) && exit();?>
<?php
manage::check_permit('extend', 1, array('a'=>'search'));//检查权限
?>
<div class="r_nav">
	<h1>{/module.extend.search/}</h1>
	<div class="turn_page"></div>
	<?php if($c['manage']['do']=='index'){?>
		<div class="search_form">
			<form method="get" action="?">
				<div class="k_input">
					<input type="text" name="Keyword" value="" class="form_input" size="15" autocomplete="off" />
					<input type="button" value="" class="more" />
				</div>
				<input type="submit" class="search_btn" value="{/global.search/}" />
				<input type="hidden" name="m" value="extend" />
				<input type="hidden" name="a" value="search" />
			</form>
		</div>
		<ul class="ico">
        	<?php if(manage::check_permit('extend', 0, array('a'=>'search', 'd'=>'add'))){?><li><a class="tip_ico_down add" href="./?m=extend&a=search&d=edit" label="{/global.add/}"></a></li><?php }?>
			<?php if(manage::check_permit('extend', 0, array('a'=>'search', 'd'=>'del'))){?><li><a class="tip_ico_down del" href="javascript:;" label="{/global.del_bat/}"></a></li><?php }?>
		</ul>
	<?php }?>
</div>
<div id="search" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		$del_ok=0;
		manage::check_permit('extend', 0, array('a'=>'search', 'd'=>'del')) && $del_ok=1;//删除权限
	?>
		<script type="text/javascript">$(document).ready(function(){extend_obj.search_init();});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<td width="5%"><input type="checkbox" name="select_all" value="" class="va_m" /></td>
					<td width="42%" nowrap="nowrap">{/global.keyword/}</td>
                    <td width="8%" nowrap="nowrap">{/search.custom_url/}</td>
					<td width="30%" nowrap="nowrap">{/search.edit_time/}</td>
					<td width="8%" class="last" nowrap="nowrap">{/global.operation/}</td>
				</tr>
			</thead>
			<tbody>
				<?php
				$Name=$_GET['Keyword'];
				$where='1=1';//条件
				$page_count=20;//显示数量
				$Name && $where.=" and Name{$c['manage']['web_lang']} like '%$Name%'";
				$search_row=str::str_code(db::get_limit_page('popular_search', $where, '*', 'SId desc', (int)$_GET['page'], $page_count));
				$i=1;
				foreach($search_row[0] as $v){
				?>
					<tr>
						<td><input type="checkbox" name="select" value="<?=$v['SId'];?>" class="va_m" /></td>
                        <td><?=$v['Name'.$c['manage']['web_lang']];?></td>
                        <td><?=$v['Url']?'{/global.n_y.1/}':'{/global.n_y.0/}';?></td>
						<td><?=date('Y-m-d H:i:s', $v['AccTime']);?></td>
						<td class="last">
                        	<a class="tip_ico tip_min_ico" href="./?m=extend&a=search&d=edit&SId=<?=$v['SId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" align="absmiddle" /></a>
							<?php if($del_ok){?><a class="tip_ico tip_min_ico del" href="./?do_action=extend.search_del&SId=<?=$v['SId'];?>" label="{/global.del/}" rel="del"><img src="/static/ico/del.png" alt="{/global.del/}" align="absmiddle" /></a><?php }?>
						</td>
					</tr>
				<?php }?>
			</tbody>
		</table>
		<div id="turn_page"><?=manage::turn_page($search_row[1], $search_row[2], $search_row[3], '?'.ly200::query_string('page').'&page=');?></div>
	<?php
	}else{
		$SId=(int)$_GET['SId'];
		$search_row=str::str_code(db::get_one('popular_search', "SId='$SId'"));
		
		$edit_ok=0;
		if(($SId && manage::check_permit('extend', 0, array('a'=>'search', 'd'=>'edit'))) || (!$SId && manage::check_permit('extend', 0, array('a'=>'search', 'd'=>'add')))) $edit_ok=1;//修改权限
	?>
    	<script type="text/javascript">$(document).ready(function(){extend_obj.search_edit_init();});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/global.keyword/}</label>
				<span class="input"><?=manage::form_edit($search_row, 'text', 'Name', 53, 150, 'notnull');?></span>
				<div class="clear"></div>
			</div>
            <div class="rows">
                <label>{/search.custom_url/}</label>
                <span class="input"><input name="Url" value="<?=$search_row['Url'];?>" type="text" class="form_input" size="53" maxlength="150" /><span class="tool_tips_ico" content="{/search.custom_url_notes/}"></span></span>
                <div class="clear"></div>
            </div>
			<div class="rows">
				<label></label>
				<span class="input">
					<?php if($edit_ok){?>
						<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<?php }?>
					<a href="./?m=extend&a=search" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" id="SId" name="SId" value="<?=$SId;?>" />
			<input type="hidden" name="do_action" value="extend.search_edit" />
		</form>
	<?php }?>
</div>