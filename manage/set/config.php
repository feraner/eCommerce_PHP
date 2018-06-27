<?php !isset($c) && exit();?>
<?php
manage::check_permit('set', 1, array('a'=>'config'));//检查权限

echo ly200::load_static('/static/js/plugin/operamasks/operamasks-ui.css', '/static/js/plugin/operamasks/operamasks-ui.min.js', '/static/js/plugin/jquery-ui/jquery-ui.min.css', '/static/js/plugin/jquery-ui/jquery-ui.min.js', '/static/js/plugin/ckeditor/ckeditor.js');
?>
<script type="text/javascript">$(document).ready(function(){set_obj.config_edit_init();});</script>
<div class="r_nav">
	<h1>{/module.set.config/}</h1>
</div>
<div id="config" class="r_con_wrap">
	<form id="edit_form" class="r_con_form">
		<?php /***************************** 基本信息 Start *****************************/?>
		<h3 class="rows_hd rows_hd_part">{/set.config.basic_info/}</h3>
		<div class="rows">
			<label>{/set.config.site_name/}</label>
			<span class="input"><input name="SiteName" value="<?=$c['manage']['config']['SiteName'];?>" type="text" class="form_input" maxlength="100" size="30" notnull> <font class="fc_red">*</font></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/set.config.logo/}</label>
			<span class="input upload_file upload_logo">
				<div class="img">
					<div id="LogoDetail" class="upload_box preview_pic"><input type="button" id="LogoUpload" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.png_tips/}{/notes.pic_size_tips/}'), '230*60');?>" /></div>
					{/notes.png_tips/}<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '230*60');?>
				</div>
				<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
				<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
			</span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/set.config.ico/}</label>
			<span class="input upload_file upload_ico">
				<div class="img">
					<div id="IcoDetail" class="upload_box preview_pic"><input type="button" id="IcoUpload" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.png_tips/}{/notes.ico_tips/}'), '16*16');?>" /></div>
					{/notes.ico_tips/}<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '16*16');?>
				</div>
				<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
				<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
			</span>
			<div class="clear"></div>
		</div>
		<?php if($c['FunVersion']!=100){?>
			<div class="rows">
				<label>{/set.config.web_display/}</label>
				<span class="input web_display">
					<?php
					for($i=0; $i<3; ++$i){
					?>
						<span class="choice_btn<?=$c['manage']['config']['WebDisplay']==$i?' current':'';?>"><b>{/set.config.web_display_ary.<?=$i;?>/}</b><input type="radio" name="WebDisplay" class="hide" <?=$c['manage']['config']['WebDisplay']==$i?'checked':'';?> value="<?=$i;?>" /></span>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/set.config.orders_sms/}</label>
				<span class="input">
					<input type="text" name="OrdersSms" value="<?=$c['manage']['config']['OrdersSms'];?>" class="form_input" maxlength="11" size="11" />&nbsp;&nbsp;&nbsp;&nbsp;
					<span class="orders_sms_radio">
						<?php for($i=0; $i<2; ++$i){?>
							<span class="choice_btn<?=(int)$c['manage']['config']['OrdersSmsStatus'][$i]?' current':'';?>"><b>{/set.config.orders_sms_ary.<?=$i;?>/}</b><input type="checkbox" name="OrdersSmsStatus[]" class="hide" value="<?=$i;?>"<?=(int)$c['manage']['config']['OrdersSmsStatus'][$i]?' checked':'';?> /></span>
						<?php }?>
					</span>
				</span>
				<div class="clear"></div>
			</div>
		<?php }?>
        <div class="rows">
            <label>{/set.config.quick_switch/}</label>
            <span class="input">
                <div class="quick_box">
                    <?php if($c['FunVersion']>=1){?>
						<div class="quick_td">
							<label>{/set.config.shield_ip/}</label>
							<div class="switchery<?=$c['manage']['config']['IsIP']?' checked':'';?>">
								<input type="checkbox" name="IsIP" value="1"<?=$c['manage']['config']['IsIP']?' checked':'';?>>
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
							<span class="tool_tips_ico" content="{/set.config.ip_notes/}"></span>
						</div>
						<div class="quick_td">
							<label>{/set.config.shield_browser/}</label>
							<div class="switchery<?=$c['manage']['config']['IsChineseBrowser']?' checked':'';?>">
								<input type="checkbox" name="IsChineseBrowser" value="1"<?=$c['manage']['config']['IsChineseBrowser']?' checked':'';?>>
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
							<span class="tool_tips_ico" content="{/set.config.browser_notes/}"></span>
						</div>
                    <?php }?>
                    <div class="quick_td">
                        <label>{/set.config.cannot_copy/}</label>
                        <div class="switchery<?=$c['manage']['config']['IsCopy']?' checked':'';?>">
                            <input type="checkbox" name="IsCopy" value="1"<?=$c['manage']['config']['IsCopy']?' checked':'';?>>
                            <div class="switchery_toggler"></div>
                            <div class="switchery_inner">
                                <div class="switchery_state_on"></div>
                                <div class="switchery_state_off"></div>
                            </div>
                        </div>
                        <span class="tool_tips_ico" content="{/set.config.cannot_copy_notes/}"></span>
                    </div>
					<?php if($c['FunVersion']!=100){?>
						<div class="quick_td">
							<label>{/set.config.browser_language/}</label>
							<div class="switchery<?=$c['manage']['config']['BrowserLanguage']?' checked':'';?>">
								<input type="checkbox" name="BrowserLanguage" value="1"<?=$c['manage']['config']['BrowserLanguage']?' checked':'';?>>
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
							<span class="tool_tips_ico" content="{/set.config.browser_language_notes/}"></span>
						</div>
						<div class="quick_td">
							<label>{/set.config.prompt_steps/}</label>
							<div class="switchery<?=$c['manage']['config']['PromptSteps']?' checked':'';?>">
								<input type="checkbox" name="PromptSteps" value="1"<?=$c['manage']['config']['PromptSteps']?' checked':'';?>>
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
						</div>
						<?php $http_301=(int)db::get_value('config', "GroupId='http' and Variable='code_301'", 'Value');?>
						<div class="quick_td">
							<label>{/set.config.301/}</label>
							<div class="switchery<?=$http_301?' checked':'';?>">
								<input type="checkbox" name="code_301" value="1"<?=$http_301?' checked':'';?>>
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
							<span class="tool_tips_ico" content="{/set.config.http_301_notes/}"></span>
						</div>
						<div class="quick_td" style="display:<?=(!(int)$c['FunVersion'] && (int)$c['NewFunVersion'])?'none':'block';?>;">
							<label>{/set.config.is_mobile/}</label>
							<div class="switchery<?=$c['manage']['config']['IsMobile']?' checked':'';?>">
								<input type="checkbox" name="IsMobile" value="1"<?=$c['manage']['config']['IsMobile']?' checked':'';?>>
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
						</div>
						<div class="quick_td">
							<label>{/set.config.close_web/}</label>
							<div class="switchery<?=$c['manage']['config']['IsCloseWeb']?' checked':'';?>">
								<input type="checkbox" name="IsCloseWeb" value="1"<?=$c['manage']['config']['IsCloseWeb']?' checked':'';?>>
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
						</div>
						<div class="quick_td">
							<label>{/set.config.bulletin_board/}</label>
							<div class="switchery<?=$c['manage']['config']['IsNotice']?' checked':'';?>">
								<input type="checkbox" name="IsNotice" value="1"<?=$c['manage']['config']['IsNotice']?' checked':'';?>>
								<div class="switchery_toggler"></div>
								<div class="switchery_inner">
									<div class="switchery_state_on"></div>
									<div class="switchery_state_off"></div>
								</div>
							</div>
						</div>
					<?php }?>
                    <div class="clear"></div>
                </div>
            </span>
            <div class="clear"></div>
        </div>
		<?php if($c['FunVersion']!=100){?>
			<div class="rows tab_box close_web_box" style="display:<?=$c['manage']['config']['IsCloseWeb']?'block':'none';?>;">
				<label>{/set.config.close_web/}</label>
				<span class="input">
					<?=manage::html_tab_button();?>
					<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
						<div class="tab_txt tab_txt_<?=$k;?>"><?=manage::Editor_Simple("CloseWeb_{$v}", $c['manage']['config']['CloseWeb']["CloseWeb_{$v}"]);?></div>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
			<?php
			$notice_ary=str::json_data(db::get_value('config', "GroupId='global' and Variable='Notice'", 'Value'), 'decode');
			?>
			<div class="rows tab_box notice_box" style="display:<?=$c['manage']['config']['IsNotice']?'block':'none';?>;">
				<label>{/set.config.bulletin_board/}</label>
				<span class="input">
					<?=manage::html_tab_button();?>
					<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
						<div class="tab_txt tab_txt_<?=$k;?>"><?=manage::Editor_Simple("Notice_{$v}", $notice_ary["Notice_{$v}"]);?></div>
					<?php }?>
				</span>
				<div class="clear"></div>
			</div>
		<?php }?>
		<?php /***************************** 基本信息 End *****************************/?>
		<?php /***************************** 会员设置 Start *****************************/?>
		<?php if($c['FunVersion']>=1){?>
			<div class="rows_hd_blank"></div>
			<h3 class="rows_hd rows_hd_part">{/set.config.user_info/}</h3>
			<div class="rows">
				<label>{/set.config.user_view/}</label>
				<span class="input">
					<div class="switchery<?=$c['manage']['config']['UserView']?' checked':'';?>">
						<input type="checkbox" name="UserView" value="1"<?=$c['manage']['config']['UserView']?' checked':'';?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
					<span class="tool_tips_ico" content="{/set.config.user_view_notes/}"></span>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/set.config.user_status/}</label>
				<span class="input">
					<div class="switchery<?=$c['manage']['config']['UserStatus']?' checked':'';?>">
						<input type="checkbox" name="UserStatus" value="1"<?=$c['manage']['config']['UserStatus']?' checked':'';?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>&nbsp;&nbsp;&nbsp;&nbsp;{/set.config.user_verification/}&nbsp;
					<div class="switchery<?=$c['manage']['config']['UserVerification']?' checked':'';?>">
						<input type="checkbox" name="UserVerification" value="1"<?=$c['manage']['config']['UserVerification']?' checked':'';?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/set.config.tourists_shopping/}</label>
				<span class="input">
					<div class="switchery<?=$c['manage']['config']['TouristsShopping']?' checked':'';?>">
						<input type="checkbox" name="TouristsShopping" value="1"<?=$c['manage']['config']['TouristsShopping']?' checked':'';?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>&nbsp;&nbsp;&nbsp;&nbsp;{/set.config.auto_register/}&nbsp;
					<div class="switchery<?=$c['manage']['config']['AutoRegister']?' checked':'';?>">
						<input type="checkbox" name="AutoRegister" value="1"<?=$c['manage']['config']['AutoRegister']?' checked':'';?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
				</span>
				<div class="clear"></div>
			</div>
		<?php }?>
		<div class="rows">
			<label>{/set.config.user_login/}</label>
			<span class="input">
				<div class="switchery<?=$c['manage']['config']['UserLogin']?' checked':'';?>">
					<input type="checkbox" name="UserLogin" value="1"<?=$c['manage']['config']['UserLogin']?' checked':'';?>>
					<div class="switchery_toggler"></div>
					<div class="switchery_inner">
						<div class="switchery_state_on"></div>
						<div class="switchery_state_off"></div>
					</div>
				</div>
				<span class="tool_tips_ico" content="{/set.config.user_login_notes/}"></span>
			</span>
			<div class="clear"></div>
		</div>
		<?php /***************************** 会员设置 End *****************************/?>
		<?php /***************************** 购物设置 Start *****************************/?>
		<div class="rows_hd_blank"></div>
		<h3 class="rows_hd rows_hd_part">{/set.config.shopping_info/}</h3>
		<?php if($c['FunVersion']>1){?>
			<div class="rows">
				<label>{/module.set.shipping.overseas/}</label>
				<span class="input">
					<div class="switchery<?=$c['manage']['config']['Overseas']?' checked':'';?>">
						<input type="checkbox" name="Overseas" value="1"<?=$c['manage']['config']['Overseas']?' checked':'';?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
				</span>
				<div class="clear"></div>
			</div>
		<?php }?>
		<div class="rows">
            <label>{/set.config.low_consumption/}</label>
            <span class="input">
                <div class="switchery<?=$c['manage']['config']['LowConsumption']?' checked':'';?>">
                    <input type="checkbox" name="LowConsumption" value="1"<?=$c['manage']['config']['LowConsumption']?' checked':'';?>>
                    <div class="switchery_toggler"></div>
                    <div class="switchery_inner">
                        <div class="switchery_state_on"></div>
                        <div class="switchery_state_off"></div>
                    </div>
                </div>&nbsp;&nbsp;&nbsp;&nbsp;
				<span class="price_input"><b>{/products.products.price/}<?=$c['manage']['currency_symbol']?><div class="arrow"><em></em><i></i></div></b><input name="LowPrice" value="<?=$c['manage']['config']['LowPrice'];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /></span>
            </span>
            <div class="clear"></div>
        </div>
		<div class="rows">
            <label>{/set.config.checkout_email/}</label>
            <span class="input">
                <div class="switchery<?=$c['manage']['config']['CheckoutEmail']?' checked':'';?>">
                    <input type="checkbox" name="CheckoutEmail" value="1"<?=$c['manage']['config']['CheckoutEmail']?' checked':'';?>>
                    <div class="switchery_toggler"></div>
                    <div class="switchery_inner">
                        <div class="switchery_state_on"></div>
                        <div class="switchery_state_off"></div>
                    </div>
                </div>
            </span>
            <div class="clear"></div>
        </div>
		<?php if($c['FunVersion']!=100){?>
			<div class="rows">
				<label>{/set.config.recent_orders/}</label>
				<span class="input">
					<div class="switchery<?=$c['manage']['config']['RecentOrders']?' checked':'';?>">
						<input type="checkbox" name="RecentOrders" value="1"<?=$c['manage']['config']['RecentOrders']?' checked':'';?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/set.config.left_category/}</label>
				<span class="input">
					{/set.config.default_open/}&nbsp;
					<div class="switchery<?=$c['manage']['config']['LeftCateOpen']?' checked':'';?>">
						<input type="checkbox" name="LeftCateOpen" value="1"<?=$c['manage']['config']['LeftCateOpen']?' checked':'';?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
				</span>
				<div class="clear"></div>
			</div>
		<?php }?>
		<div class="rows">
            <label>{/set.config.less_stock/}</label>
            <span class="input">
				{/set.config.less_stock_ary.0/}&nbsp;
                <div class="switchery<?=$c['manage']['config']['LessStock']==0?' checked':'';?>">
                    <input type="checkbox" name="LessStock" value="0"<?=$c['manage']['config']['LessStock']==0?' checked':'';?>>
                    <div class="switchery_toggler"></div>
                    <div class="switchery_inner">
                        <div class="switchery_state_on"></div>
                        <div class="switchery_state_off"></div>
                    </div>
                </div>&nbsp;&nbsp;&nbsp;&nbsp;
				{/set.config.less_stock_ary.1/}&nbsp;
                <div class="switchery<?=$c['manage']['config']['LessStock']==1?' checked':'';?>">
                    <input type="checkbox" name="LessStock" value="1"<?=$c['manage']['config']['LessStock']==1?' checked':'';?>>
                    <div class="switchery_toggler"></div>
                    <div class="switchery_inner">
                        <div class="switchery_state_on"></div>
                        <div class="switchery_state_off"></div>
                    </div>
                </div>
            </span>
            <div class="clear"></div>
        </div>
		<div class="rows">
            <label>{/set.config.cart_weight/}</label>
            <span class="input">
                <div class="switchery<?=$c['manage']['config']['CartWeight']?' checked':'';?>">
                    <input type="checkbox" name="CartWeight" value="1"<?=$c['manage']['config']['CartWeight']?' checked':'';?>>
                    <div class="switchery_toggler"></div>
                    <div class="switchery_inner">
                        <div class="switchery_state_on"></div>
                        <div class="switchery_state_off"></div>
                    </div>
                </div>
            </span>
            <div class="clear"></div>
        </div>
		<div class="rows">
            <label>{/set.config.auto_canceled/}</label>
            <span class="input">
                <div class="switchery<?=$c['manage']['config']['AutoCanceled']?' checked':'';?>">
                    <input type="checkbox" name="AutoCanceled" value="1"<?=$c['manage']['config']['AutoCanceled']?' checked':'';?>>
                    <div class="switchery_toggler"></div>
                    <div class="switchery_inner">
                        <div class="switchery_state_on"></div>
                        <div class="switchery_state_off"></div>
                    </div>
                </div>&nbsp;&nbsp;&nbsp;&nbsp;
				<span class="price_input"><input name="AutoCanceledDay" value="<?=$c['manage']['config']['AutoCanceledDay'];?>" type="text" class="form_input" size="5" maxlength="10" rel="amount" /><b class="last">{/set.config.day_unit/}</b></span>
				<span class="tool_tips_ico" content="{/set.config.auto_canceled_notes/}"></span>
            </span>
            <div class="clear"></div>
        </div>
		<div class="rows tab_box">
			<label>{/set.config.arrival_info/}</label>
			<span class="input">
				<?=manage::html_tab_button();?>
				<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
					<div class="tab_txt tab_txt_<?=$k;?>"><?=manage::Editor_Simple("ArrivalInfo_{$v}", $c['manage']['config']['ArrivalInfo']["ArrivalInfo_{$v}"]);?></div>
				<?php }?>
			</span>
			<div class="clear"></div>
		</div>
		<?php /***************************** 购物设置 End *****************************/?>
		<?php /***************************** 语言设置 Start *****************************/?>
		<?php
		$LanguageFlag=str::json_data(htmlspecialchars_decode($c['manage']['config']['LanguageFlag']), 'decode');
		?>
		<div class="rows_hd_blank"></div>
		<h3 class="rows_hd rows_hd_part">{/set.config.language_config/}</h3>
		<div class="rows">
			<label>{/set.config.language_list/}</label>
			<span class="input lang_list">
				<?php
				foreach($c['manage']['web_lang_list'] as $v){
				?>
					<span class="choice_btn<?=in_array($v, $c['manage']['config']['Language'])?' current':'';?>"><b>{/language.<?=$v;?>/}</b><input type="checkbox" name="Language[]" class="hide" <?=in_array($v, $c['manage']['config']['Language'])?'checked':'';?> value="<?=$v;?>" /></span><?=($LanguageFlag[$v] && is_file($c['root_path'].$LanguageFlag[$v]))?'<img src="'.$LanguageFlag[$v].'" class="small_flag" align="absmiddle" />':'';?><a href="javascript:;" class="edit" lang="<?=$v;?>"><img src="/static/ico/edit.png" align="absmiddle" /></a>
				<?php }?>
			</span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/set.config.default_language/}</label>
			<span class="input default_lang">
				<?php
				foreach($c['manage']['config']['Language'] as $v){
				?>
					<span class="choice_btn<?=$c['manage']['config']['LanguageDefault']==$v?' current':'';?>"><b>{/language.<?=$v;?>/}</b><input type="radio" name="LanguageDefault" class="hide" <?=$c['manage']['config']['LanguageDefault']==$v?'checked':'';?> value="<?=$v;?>" /></span>
				<?php }?>
			</span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/set.config.manage_language/}</label>
			<span class="input manage_lang">
				<?php
				foreach($c['manage']['manage_lang_list'] as $v){
					if($c['FunVersion']<1 && $v=='en') continue;
				?>
					<span class="choice_btn<?=$c['manage']['config']['ManageLanguage']==$v?' current':'';?>"><b>{/language.<?=$v;?>/}</b><input type="radio" name="ManageLanguage" class="hide" <?=$c['manage']['config']['ManageLanguage']==$v?'checked':'';?> value="<?=$v;?>" /></span>
				<?php }?>
			</span>
			<div class="clear"></div>
		</div>
		<?php /***************************** 语言设置 End *****************************/?>
		<?php /***************************** 网站资料 Start *****************************/?>
		<div class="rows_hd_blank"></div>
		<h3 class="rows_hd rows_hd_part">{/set.config.site_info/}</h3>
		<div class="rows">
			<label>{/set.config.admin_email/}</label>
			<span class="input"><input name="AdminEmail" value="<?=$c['manage']['config']['AdminEmail'];?>" type="text" class="form_input" maxlength="100" size="30" notnull> <font class="fc_red">*</font></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/set.config.skype/}</label>
			<span class="input"><input name="Skype" value="<?=$c['manage']['config']['Skype'];?>" type="text" class="form_input" maxlength="100" size="30"></span>
			<div class="clear"></div>
		</div>
		<?php if($c['FunVersion']==2){?>
			<div class="rows">
				<label>{/set.config.blog/}</label>
				<span class="input"><input name="Blog" value="<?=$c['manage']['config']['Blog'];?>" type="text" class="form_input" maxlength="100" size="50"></span>
				<div class="clear"></div>
			</div>
		<?php }?>
		<div class="rows">
			<label>{/set.config.contactmenu/}{/seo.url/}</label>
			<span class="input"><input name="ContactUrl" value="<?=$c['manage']['config']['ContactUrl'];?>" type="text" class="form_input" maxlength="150" size="50"></span>
			<div class="clear"></div>
		</div>
		<div class="rows">
			<label>{/global.other/}</label>
			<span class="input tab_box">
				<?=manage::html_tab_button('border');?>
				<div class="blank9"></div>
				<?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
					<div class="tab_txt tab_txt_<?=$k;?>">
						<span class="price_input lang_input"><b>{/set.config.search_tips/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="SearchTips_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($c['manage']['config']['SearchTips']["SearchTips_{$v}"]), ENT_QUOTES);?>" class="form_input" size="50" maxlength="150" /></span>
						<div class="blank9"></div>
						<span class="price_input lang_input"><b>{/set.config.copyright/}<div class='arrow'><em></em><i></i></div></b><input type="text" name="CopyRight_<?=$v;?>" value="<?=htmlspecialchars(htmlspecialchars_decode($c['manage']['config']['CopyRight']["CopyRight_{$v}"]), ENT_QUOTES);?>" class="form_input" size="50" maxlength="255" /></span>
					</div>
				<?php }?>
			</span>
			<div class="clear"></div>
		</div>
		<?php /***************************** 网站资料 End *****************************/?>
		<?php
		/***************************** 首页设置 Start *****************************/
		$file=$c['root_path']."/static/themes/{$c['manage']['web_themes']}/inc/themes_set.php";
		if(@is_file($file)){
			echo '<div class="rows_hd_blank"></div>';
			@include($file);
			$config_ary=(array)themes_set::config_edit_init();
			echo html::config_edit_form($config_ary);
		}
		/***************************** 首页设置 End *****************************/
		?>
		<?php /***************************** 水印设置 Start *****************************/?>
		<div class="rows_hd_blank"></div>
		<h3 class="rows_hd rows_hd_part">{/set.config.watermark_config/}</h3>
		<div class="rows">
			<label>{/set.config.basic_settings/}</label>
			<span class="input">
				<div class="switchery<?=$c['manage']['config']['IsWater']?' checked':'';?>">
					<input type="checkbox" name="IsWater" value="1"<?=$c['manage']['config']['IsWater']?' checked':'';?>>
					<div class="switchery_toggler"></div>
					<div class="switchery_inner">
						<div class="switchery_state_on"></div>
						<div class="switchery_state_off"></div>
					</div>
				</div>
				{/set.config.is_watermark/}
				<span class="thumbnail_box" style="display:<?=$c['manage']['config']['IsWater']?'':'none';?>;">
					<div class="switchery<?=$c['manage']['config']['IsThumbnail']?' checked':'';?>">
						<input type="checkbox" name="IsThumbnail" value="1"<?=$c['manage']['config']['IsThumbnail']?' checked':'';?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
					{/set.config.is_thumbnail/}<span class="tool_tips_ico" content="{/set.config.thumbnail_notes/}"></span>
					<div class="switchery<?=$c['manage']['config']['IsWaterPro']?' checked':'';?>">
						<input type="checkbox" name="IsWaterPro" value="1"<?=$c['manage']['config']['IsWaterPro']?' checked':'';?>>
						<div class="switchery_toggler"></div>
						<div class="switchery_inner">
							<div class="switchery_state_on"></div>
							<div class="switchery_state_off"></div>
						</div>
					</div>
					{/set.config.is_watermark_pro/}<span class="tool_tips_ico" content="{/set.config.watermark_pro_notes/}"></span>
				</span>
			</span>
			<div class="clear"></div>
		</div>
		<div class="watermark_box" style="display:<?=$c['manage']['config']['IsWater']?'block':'none';?>;">
			<div class="rows">
				<label>{/set.config.watermark_upfile/}</label>
				<span class="input upload_file upload_watermark">
					<div class="img">
						<div id="WatermarkDetail" class="upload_box preview_pic"><input type="button" id="WatermarkUpload" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '210*75');?>" /></div>
						<?=sprintf(manage::language('{/notes.pic_size_tips/}'), '210*75');?>
					</div>
					<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
					<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/set.config.alpha/}</label>
				<span class="input">
					<div id="slider_box">
						<div id="slider" class="fl"></div>
						<div id="slider_value" class="fl"><?=$c['manage']['config']['Alpha'];?>%</div>
						<span>{/set.config.alpha_png/}</span>
					</div>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/set.config.water_position/}</label>
				<span class="input">
					<table border="0" cellpadding="0" cellspacing="0" class="watermark_tab">
						<?php for($i=0; $i<3; ++$i){?>
							<tr>
								<?php
								for($j=1; $j<=3; ++$j){
									$num=$i+$j+($i*2);
								?>
									<td class="item<?=$c['manage']['config']['WaterPosition']==$num?' item_on':'';?>" data="<?=$num;?>">
										<div><span>{/set.config.water_position_ary.<?=$num;?>/}</span><div class="filter"></div><div class="check"></div></div>
									</td>
								<?php }?>
							</tr>
						<?php }?>
					</table>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/global.preview/}</label>
				<span class="input">
					<div class="preview">
						<table border="0" cellpadding="0" cellspacing="0" class="watermark_tab">
							<?php for($i=0; $i<3; ++$i){?>
								<tr>
									<?php
									for($j=1; $j<=3; ++$j){
										$num=$i+$j+($i*2);
									?>
										<td class="item" data="<?=$num;?>"><?php if(is_file($c['root_path'].$c['manage']['config']['WatermarkPath']) && $c['manage']['config']['WaterPosition']==$num){?><img id="preview_pic" src="<?=$c['manage']['config']['WatermarkPath'];?>" /><?php }?></td>
									<?php }?>
								</tr>
							<?php }?>
						</table>
					</div>
				</span>
				<div class="clear"></div>
			</div>
		</div>
		<?php /***************************** 水印设置 End *****************************/?>
		<div class="rows">
			<label></label>
			<span class="input"><input type="submit" class="btn_ok" name="submit_button" value="{/global.submit/}" /></span>
			<div class="clear"></div>
		</div>
		<input type="hidden" name="WaterPosition" value="<?=$c['manage']['config']['WaterPosition'];?>" />
		<input type="hidden" name="Alpha" value="<?=$c['manage']['config']['Alpha'];?>" />
		<input type="hidden" name="LogoPath" value="<?=$c['manage']['config']['LogoPath'];?>" save="<?=is_file($c['root_path'].$c['manage']['config']['LogoPath'])?1:0;?>" />
        <input type="hidden" name="IcoPath" value="<?=$c['manage']['config']['IcoPath'];?>" save="<?=is_file($c['root_path'].$c['manage']['config']['IcoPath'])?1:0;?>" />
		<input type="hidden" name="WatermarkPath" value="<?=$c['manage']['config']['WatermarkPath'];?>" save="<?=is_file($c['root_path'].$c['manage']['config']['WatermarkPath'])?1:0;?>" />
		<input type="hidden" name="do_action" value="set.config_edit">
	</form>
	
	<?php /***************************** 语言国旗 Start *****************************/?>
	<div class="pop_form box_language_edit" data-flag="<?=htmlspecialchars($c['manage']['config']['LanguageFlag']);?>" data-currency="<?=htmlspecialchars($c['manage']['config']['LanguageCurrency']);?>">
		<form>
			<div class="t"><h1>{/set.config.edit_lang/} (<em></em>)</h1><h2>×</h2></div>
			<div class="r_con_form">
				<div class="rows">
					<label>{/set.country.flag/}</label>
					<span class="input upload_file upload_flag">
						<div class="img">
							<div id="FlagDetail" class="upload_box preview_pic"><input type="button" id="FlagUpload" class="upload_btn" name="submit_button" value="{/global.upload_pic/}" tips="<?=sprintf(manage::language('{/notes.ico_tips/}'), '16*13');?>" /></div><?=sprintf(manage::language('{/notes.pic_size_tips/}'), '16*13');?>
						</div>
						<a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a>
						<a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
					</span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/set.exchange.currency/}</label>
					<span class="input">
						<?php $currency_row=db::get_all('currency', 'IsUsed=1', 'CId, Currency, Symbol', $c['my_order'].'CId asc');?>
						<select name="Currency">
							<option value="">{/global.select_index/}</option>
							<?php foreach($currency_row as $v){?>
								<option value="<?=$v['CId'];?>"<?=$country_row['Currency']==$v['CId']?' selected':'';?>><?=$v['Currency'];?></option>
							<?php }?>
						</select>
					</span>
					<div class="clear"></div>
				</div>
			</div>
			<div class="button"><input type="submit" class="btn_ok" name="submit_button" value="{/global.save/}" /><input type="button" class="btn_cancel" name="submit_button" value="{/global.cancel/}" /></div>
			<input type="hidden" name="do_action" value="set.language_edit" />
			<input type="hidden" name="FlagPath" value="" />
			<input type="hidden" name="Language" value="" />
		</form>
	</div>
	<?php /***************************** 语言国旗 End *****************************/?>
</div>