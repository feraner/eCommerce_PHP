<?php !isset($c) && exit();?>
<?php
manage::check_permit('extend', 1, array('a'=>'google'));//检查权限

echo ly200::load_static('/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');
?>
<script language="javascript">
$(document).ready(function(){
	$('input[name=DeadLine]').daterangepicker({
		showDropdowns:true,
		timePicker:false,
		format:	'YYYY-MM-DD'
	});//时间插件
	$('#edit_form').delegate('input:button[name=submit_button]', 'click', function(){
		if($('input[name=DeadLine]').val()==''){
			$('input[name=DeadLine]').css('border', '1px solid #ff0000');
			return false;
		}else{
			$('input[name=DeadLine]').removeAttr('style');
			var t=$('input[name=DeadLine]').val().split(' - ');

			get_google_data($('select[name=project_id]').val(), t[0], t[1]);
		}
	});

	function get_google_data(project_id, startdate, enddate){	//获取google排名数据
		var url='http://api.semalt.com/api.php?key=6296479f2c&method=json&need=positions';
		$('#google_analytics_result table').find('tbody:last-child').remove();
		$('#google_analytics_result .loading').show();

		$.get(url+'&id='+project_id+'&startdate='+startdate+'&enddate='+enddate, function(data){
			var str = '<tbody>';
			for(k in data){
				str = str + '<tr>';
				str = str + '<td>' + k + '</td>';
				for(v in data[k]){
					var p = data[k][v]['position']==null ? 0 : data[k][v]['position'];
					var c = data[k][v]['change']=='n/a' ? '&nbsp;' : data[k][v]['change'];
					
					str = str + '<td>' + '<span class="' + data[k][v]['class'] + '">' + p + '</span>' + '<em>' + c + '</em>' + '</td>';
				}
				str = str + '</tr>';
			}
			str = str + '</tbody>';
			
			$('#google_analytics_result table').append(str).siblings('.loading').hide();;
		}, 'json');
	}
	get_google_data($('select[name=project_id] option:selected').val(), '<?=date('Y-m-d', $c['time']-86400*7);?>', '<?=date('Y-m-d', $c['time']+86400);?>');
});
</script>
<div class="r_nav">
	<h1>{/module.sales.coupon/}</h1>
	<div class="turn_page"></div>
</div>
<!--
http://api.semalt.com/api.php?key=6296479f2c&method=json&need=positions&id=2577282&startdate=2015-10-23&enddate=2015-10-28
-->
<style>
input{vertical-align:middle;}
input[name=DeadLine]{ margin-right:10px;}
#google_analytics_result{ min-height:500px; padding:20px 30px;}
#google_analytics_result table{border-collapse:collapse; border-spacing:0; background:#ffffff;}
#google_analytics_result tr td{border-bottom:1px solid #eee; border-right:1px solid #eee; height:30px; padding:8px; text-align:center;}
#google_analytics_result tr td:first-child{border-left:1px solid #eee; text-align:left; text-indent:10px;}
#google_analytics_result thead td{line-height:30px; border-top:1px solid #eee; font-size:14px; font-family:"微软雅黑"; font-weight:bold;}
#google_analytics_result tbody tr:hover{background:#eeeeee;}
#google_analytics_result tbody td:first-child{ color:#666;}
#google_analytics_result tbody td span{display:inline-block; width:40px; height:20px; line-height:20px; text-align:center; border-radius:3px; color:#ffffff;}
#google_analytics_result tbody td span.red{background:#ff0000;}
#google_analytics_result tbody td span.green{background:#009900;}
#google_analytics_result tbody td span.gray{background:#cccccc;}
#google_analytics_result tbody td em{display:inline-block; width:25px; height:20px; line-height:20px; text-align:right;}

#google_analytics_result .loading{background:url(../../static/manage/images/frame/loading.gif) center center no-repeat; width:100%; height:400px;}
</style>
<div id="google" class="r_con_wrap">
    <form id="edit_form" class="r_con_form">
        <div class="rows">
            <label>{/global.date/}</label>
            <span class="input">
            	<select name="project_id">
                	<option value="2577282" selected="selected">www.ueeshop.com</option>
                	<option value="2577640">www.360sportwatches.com</option>
                </select>
                <input name="DeadLine" value="<?=date('Y-m-d', $c['time']-86400*7).' - '.date('Y-m-d', $c['time']);?>" type="text" class="form_input fl" size="25" readonly />
                <input type="button" class="btn_ok" name="submit_button" value="{/global.submit/}" />
            </span>
            <div class="clear"></div>
        </div>
        <div id="google_analytics_result" class="rows">
        	<table width="100%">
            	<thead>
                	<tr>
                    	<td width="30%">关键词</td>
                        <?php for($i=-7;$i<0;$i++){?>
                        <td width="10%"><?=date('m-d', $c['time']+86400*$i);?></td>
                        <?php }?>
                    </tr>
                </thead>
            </table>
            <div class="loading"></div>
        </div>
    </form>
</div>