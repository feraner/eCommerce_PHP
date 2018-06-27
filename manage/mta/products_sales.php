<?php !isset($c) && exit();?>
<?php
manage::check_permit('mta', 1);
echo ly200::load_static('/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');
?>
<script language="javascript">$(document).ready(mta_obj.products_sales_init);</script>
<div id="mta" class="r_con_wrap">
	<div class="box nav">
		<ul class="clean">
        	<li>{/global.time/}:</li>
			<li><input type="text" name="TimeS" value="" readonly class="form_input" notnull /></li>
			<li><input type="submit" class="btn_ok" value="{/global.view/}" /></li>
		</ul>
        <dl class="time">
        	<dt><a href="javascript:void(0);" rel="0" class="clean">{/global.clear/}</a></dt>
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
	<div class="box detail sale_detail">
    	<ul>
        	<li class="title">
            	<div class="number">{/mta.product.number/}</div>
            	<div class="products">{/mta.product.product_info/}</div>
            	<div class="country">{/mta.product.country/}</div>
            	<div class="related">{/mta.product.related/}</div>
            	<div class="operate"></div>
                <div class="clear"></div>
            </li>
        </ul>
    	<ul id="sales-list" data-page="1"></ul>
	</div>
</div>