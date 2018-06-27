<?php !isset($c) && exit();?>
<?php
manage::check_permit('mta', 1);
echo ly200::load_static('/static/js/plugin/highcharts/highcharts.js', '/static/js/plugin/daterangepicker/daterangepicker.css', '/static/js/plugin/daterangepicker/moment.min.js', '/static/js/plugin/daterangepicker/daterangepicker.js');
?>
<script language="javascript">$(document).ready(mta_obj.visits_conversion_init);</script>
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
	<ul class="box data_list visits_conversion">
		<li>
			<div>
            	<div class="title">
                    <h1>{/mta.ratio/}</h1>
                    <?php /*?><h2><span class="ratio">0</span><span class="compare vs">VS</span><span class="compare compare_ratio">0</span></h2><?php */?>
                    <h2><span class="complete_ratio"><span class="ratio_operate_complete">0</span>%</span></h2>
                    <h3 class="compare"><span class="compare_ratio_operate_complete">0</span>%</h3>
                </div>
                <ul class="contents">
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.enter/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="ratio_uv">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_ratio_uv">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.addtocart/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="ratio_addtocart">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_ratio_addtocart">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="ratio_operate_addtocart">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_ratio_operate_addtocart">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.placeorder/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="ratio_placeorder">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_ratio_placeorder">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="ratio_operate_placeorder">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_ratio_operate_placeorder">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.complete/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="ratio_complete">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_ratio_complete">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="ratio_operate_complete">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_ratio_operate_complete">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                </ul>
			</div>
		</li>
		<li>
			<div>
            	<div class="title">
                    <h1>{/mta.conversion.enter_directly/}</h1>
                    <?php /*?><h2><span class="enter_directly">0</span><span class="compare vs">VS</span><span class="compare compare_enter_directly">0</span></h2><?php */?>
                    <h2><span class="complete_ratio"><span class="enter_directly_operate_complete">0</span>%</span></h2>
                    <h3 class="compare"><span class="compare_enter_directly_operate_complete">0</span>%</h3>
                </div>
                <ul class="contents">
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.enter/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="enter_directly_uv">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_enter_directly_uv">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.addtocart/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="enter_directly_addtocart">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_enter_directly_addtocart">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="enter_directly_operate_addtocart">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_enter_directly_operate_addtocart">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.placeorder/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="enter_directly_placeorder">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_enter_directly_placeorder">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="enter_directly_operate_placeorder">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_enter_directly_operate_placeorder">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.complete/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="enter_directly_complete">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_enter_directly_complete">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="enter_directly_operate_complete">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_enter_directly_operate_complete">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                </ul>
			</div>
		</li>
		<li>
			<div>
            	<div class="title">
                    <h1>{/mta.conversion.share_platform/}</h1>
                    <?php /*?><h2><span class="share_platform">0</span><span class="compare vs">VS</span><span class="compare compare_share_platform">0</span></h2><?php */?>
                    <h2><span class="complete_ratio"><span class="share_platform_operate_complete">0</span>%</span></h2>
                    <h3 class="compare"><span class="compare_share_platform_operate_complete">0</span>%</h3>
                </div>
                <ul class="contents">
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.enter/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="share_platform_uv">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_share_platform_uv">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.addtocart/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="share_platform_addtocart">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_share_platform_addtocart">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="share_platform_operate_addtocart">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_share_platform_operate_addtocart">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.placeorder/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="share_platform_placeorder">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_share_platform_placeorder">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="share_platform_operate_placeorder">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_share_platform_operate_placeorder">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.complete/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="share_platform_complete">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_share_platform_complete">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="share_platform_operate_complete">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_share_platform_operate_complete">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                </ul>
			</div>
		</li>
		<li>
			<div>
            	<div class="title">
                    <h1>{/mta.conversion.search_engine/}</h1>
                    <?php /*?><h2><span class="search_engine">0</span><span class="compare vs">VS</span><span class="compare compare_search_engine">0</span></h2><?php */?>
                    <h2><span class="complete_ratio"><span class="search_engine_operate_complete">0</span>%</span></h2>
                    <h3 class="compare"><span class="compare_search_engine_operate_complete">0</span>%</h3>
                </div>
                <ul class="contents">
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.enter/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="search_engine_uv">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_search_engine_uv">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.addtocart/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="search_engine_addtocart">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_search_engine_addtocart">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="search_engine_operate_addtocart">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_search_engine_operate_addtocart">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.placeorder/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="search_engine_placeorder">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_search_engine_placeorder">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="search_engine_operate_placeorder">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_search_engine_operate_placeorder">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.complete/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="search_engine_complete">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_search_engine_complete">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="search_engine_operate_complete">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_search_engine_operate_complete">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                </ul>
			</div>
		</li>
		<li>
			<div>
            	<div class="title">
                    <h1>{/mta.conversion.other/}</h1>
                    <?php /*?><h2><span class="other">0</span><span class="compare vs">VS</span><span class="compare compare_other">0</span></h2><?php */?>
                    <h2><span class="complete_ratio"><span class="other_operate_complete">0</span>%</span></h2>
                    <h3 class="compare"><span class="compare_other_operate_complete">0</span>%</h3>
                </div>
                <ul class="contents">
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.enter/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="other_uv">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_other_uv">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.addtocart/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="other_addtocart">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_other_addtocart">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="other_operate_addtocart">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_other_operate_addtocart">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.placeorder/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="other_placeorder">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_other_placeorder">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="other_operate_placeorder">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_other_operate_placeorder">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                	<li>
                    	<div class="contents_left">
                        	<div class="operate">{/mta.conversion.complete/}</div>
                            <div class="operate_data">{/mta.conversion.visitors/} (<span class="other_complete">0</span>{/mta.conversion.person/})</div>
                            <div class="operate_data compare">{/mta.compared/} (<span class="compare_other_complete">0</span>{/mta.conversion.person/})</div>
                        </div>
                        <div class="contents_right">
                        	<div class="operate_ratio"><span class="other_operate_complete">0</span>%</div>
                            <div class="operate_ratio compare"><span class="compare_other_operate_complete">0</span>%</div>
                        </div>
                        <div class="clear"></div>
                    </li>
                </ul>
			</div>
		</li>
	</ul>
</div>