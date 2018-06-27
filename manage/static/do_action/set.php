<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class set_module{
	public static function config_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$ImgPath=array();
		$PicPath=array($p_LogoPath, $p_WatermarkPath, $p_IcoPath);
		foreach((array)$PicPath as $k=>$v){
			$ImgPath[$k]=$v;
		}
		foreach((array)$p_Language as $k=>$v){	//防止非法提交系统未定义的语言
			if(!in_array($v, $c['manage']['web_lang_list'])){
				unset($p_Language[$k]);
			}
		}
		!$p_Language && ly200::e_json('请至少选择一个语言版本');
		!$p_LanguageDefault && ly200::e_json('请选择网站默认语言');
		!$p_ManageLanguage && ly200::e_json('请选择后台语言');
		!in_array($p_LanguageDefault, $p_Language) && $p_LanguageDefault=$p_Language[0];
		//订单短信通知
		$SmsAry=array(0, 0);
		foreach((array)$p_OrdersSmsStatus as $v){
			$SmsAry[$v]=1;
		}
		$SmsData=addslashes(str::json_data(str::str_code($SmsAry, 'stripslashes')));
		//自定义关闭网站
		$CloseWebAry=array();
		foreach($c['manage']['config']['Language'] as $k=>$v){
			$CloseWebAry['CloseWeb_'.$v]=${'p_CloseWeb_'.$v};
		}
		$CloseWebData=addslashes(str::json_data(str::str_code($CloseWebAry, 'stripslashes')));
		//通告栏
		$NoticeAry=array();
		foreach($c['manage']['config']['Language'] as $k=>$v){
			$NoticeAry['Notice_'.$v]=${'p_Notice_'.$v};
		}
		$NoticeData=addslashes(str::json_data(str::str_code($NoticeAry, 'stripslashes')));
		//到货信息
		$ArrivalInfoAry=array();
		foreach($c['manage']['config']['Language'] as $k=>$v){
			$ArrivalInfoAry['ArrivalInfo_'.$v]=${'p_ArrivalInfo_'.$v};
		}
		$ArrivalInfoData=addslashes(str::json_data(str::str_code($ArrivalInfoAry, 'stripslashes')));
		//搜索框提示语
		$SearchTipsAry=array();
		foreach($c['manage']['config']['Language'] as $k=>$v){
			$SearchTipsAry['SearchTips_'.$v]=${'p_SearchTips_'.$v};
		}
		$SearchTipsData=addslashes(str::json_data(str::str_code($SearchTipsAry, 'stripslashes')));
		//版权信息
		$CopyRightAry=array();
		foreach($c['manage']['config']['Language'] as $k=>$v){
			$CopyRightAry['CopyRight_'.$v]=${'p_CopyRight_'.$v};
		}
		$CopyRightData=addslashes(str::json_data(str::str_code($CopyRightAry, 'stripslashes')));
		
		$data=array(
			//******************基本信息******************
			'SiteName'			=>	$p_SiteName,
			'LogoPath'			=>	$ImgPath[0],
			'IcoPath'			=>	$ImgPath[2],
			'WebDisplay'		=>	(int)$p_WebDisplay,
			'OrdersSms'			=>	$p_OrdersSms,
			'OrdersSmsStatus'	=>	$SmsData,
			'IsIP'				=>	$c['FunVersion']>=1?(int)$p_IsIP:0,
			'IsChineseBrowser'	=>	$c['FunVersion']>=1?(int)$p_IsChineseBrowser:0,
			'IsCopy'			=>	(int)$p_IsCopy,
			'IsMobile'			=>	(int)$p_IsMobile,
			'IsCloseWeb'		=>	(int)$p_IsCloseWeb,
			'IsNotice'			=>	(int)$p_IsNotice,
			'CloseWeb'			=>	$CloseWebData,
			'Notice'			=>	$NoticeData,
			//******************会员设置******************
			'UserView'			=>	$c['FunVersion']>=1?(int)$p_UserView:0,
			'UserStatus'		=>	$c['FunVersion']>=1?(int)$p_UserStatus:0,
			'TouristsShopping'	=>	$c['FunVersion']>=1?(int)$p_TouristsShopping:0,
			'UserVerification'	=>	$c['FunVersion']>=1?(int)$p_UserVerification:0,
			'AutoRegister'		=>	$c['FunVersion']>=1?(int)$p_AutoRegister:0,
			'UserLogin'			=>	(int)$p_UserLogin,
			//******************购物设置******************
			'Overseas'			=>	$c['FunVersion']>1?(int)$p_Overseas:0,
			'LowConsumption'	=>	(int)$p_LowConsumption,
			'LowPrice'			=>	(float)$p_LowPrice,
			'RecentOrders'		=>	(int)$p_RecentOrders,
			'CheckoutEmail'		=>	(int)$p_CheckoutEmail,
			'LeftCateOpen'		=>	(int)$p_LeftCateOpen,
			'LessStock'			=>	(int)$p_LessStock,
			'CartWeight'		=>	(int)$p_CartWeight,
			'AutoCanceled'		=>	(int)$p_AutoCanceled,
			'AutoCanceledDay'	=>	(int)$p_AutoCanceledDay,
			'ArrivalInfo'		=>	$ArrivalInfoData,
			//******************语言设置******************
			'BrowserLanguage'	=>	(int)$p_BrowserLanguage,
			'PromptSteps'		=>	(int)$p_PromptSteps,
			'Language'			=>	implode(',', $p_Language),
			'LanguageDefault'	=>	$p_LanguageDefault,
			'ManageLanguage'	=>	$p_ManageLanguage,
			//******************网站资料******************
			'AdminEmail'		=>	$p_AdminEmail,
			'Skype'				=>	$p_Skype,
			'SearchTips'		=>	$SearchTipsData,
			'CopyRight'			=>	$CopyRightData,
			'ContactUrl'		=>	$p_ContactUrl,
			//******************水印设置******************
			'IsWater'			=>	(int)$p_IsWater,
			'IsThumbnail'		=>	(int)$p_IsThumbnail,
			'IsWaterPro'		=>	(int)$p_IsWaterPro,
			'Alpha'				=>	$p_Alpha,
			'WatermarkPath'		=>	$ImgPath[1],
			'WaterPosition'		=>	$p_WaterPosition
		);
		
		//首页设置
		$themes_set_file=$c['root_path']."/static/themes/{$c['manage']['web_themes']}/inc/themes_set.php";
		if(@is_file($themes_set_file)){
			@include($themes_set_file);
			$config_ary=(array)themes_set::config_edit_init();
		}
		$config_ary && $data=array_merge($data, manage::config_edit($config_ary));
		//开启会员审核，现有会员全改为已审核
		((int)$p_UserStatus && $c['manage']['config']['UserStatus']!=(int)$p_UserStatus) && db::update('user', 1, array('Status'=>1));
		
		$c['FunVersion']==2 && $data['Blog']=$p_Blog;
		manage::config_operaction($data, 'global');
		manage::config_operaction(array('code_301'=>(int)$p_code_301), 'http');
		manage::turn_on_language_database_operation($p_LanguageDefault, $p_Language);	//添加新增语言版多语言字段、默认内容
		manage::operation_log('修改网站基本设置');
		ly200::e_json('', 1);
	}
	
	public static function language_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$FlagAry=str::json_data(htmlspecialchars_decode($c['manage']['config']['LanguageFlag']), 'decode');
		$FlagAry[$p_Language]=$p_FlagPath;
		$FlagData=addslashes(str::json_data(str::str_code($FlagAry, 'stripslashes')));
		$CurrencyAry=str::json_data(htmlspecialchars_decode($c['manage']['config']['LanguageCurrency']), 'decode');
		$CurrencyAry[$p_Language]=$p_Currency;
		$CurrencyData=addslashes(str::json_data(str::str_code($CurrencyAry, 'stripslashes')));
		$data=array(
			'LanguageFlag'		=>	$FlagData,
			'LanguageCurrency'	=>	$CurrencyData
		);
		manage::config_operaction($data, 'global');
		manage::operation_log('修改语言设置');
		ly200::e_json('', 1);
	}
	
	public static function orders_print_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$ImgPath=$p_LogoPath;
		$data=array(
			'LogoPath'	=>	$ImgPath,
			'Compeny'	=>	$p_Compeny,
			'Address'	=>	$p_Address,
			'Email'		=>	$p_Email,
			'Telephone'	=>	$p_Telephone,
			'Fax'		=>	$p_Fax
		);
		manage::config_operaction($data, 'print');
		manage::operation_log('修改订单打印设置');
		ly200::e_json('', 1);
	}
	
	public static function exchange_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$CId=(int)$p_CId;
		$p_ExchangeRate=sprintf('%.4f', $p_ExchangeRate);
		$p_IsUsed=(int)$p_IsUsed;
		$FlagPath=$p_FlagPath;
		$data=array(
			'Currency'		=>	$p_Currency,
			'Symbol'		=>	$p_Symbol,
			'ExchangeRate'	=>	$p_ExchangeRate,
			'FlagPath'		=>	$FlagPath,
			'IsUsed'		=>	$p_IsUsed,
		);
		if($CId){
			$rows=str::str_code(db::get_one('currency', "CId='{$CId}'"));
			if((int)$rows['IsDefault']==1 || (int)$rows['ManageDefault']==1){
				$data['IsUsed']=1;
			}else{
				$data['Rate']=(float)$p_ExchangeRate;
			}
			if($rows['Currency']=='USD') $data['ExchangeRate']='1.0000';//固定美元对美元默认汇率
			$logs='修改货币：'.$rows['Currency'];
			db::update('currency', "CId='{$CId}'", $data);
			
			if($rows['ExchangeRate']!=$p_ExchangeRate){//更新其他现用汇率
				$currency_row=db::get_one('currency', 'ManageDefault=1');
				$rate_val=100*(100/(100*(float)$currency_row['ExchangeRate']));
				db::query("update currency set Rate=(ExchangeRate*$rate_val)/100 where ManageDefault=0");
			}
		}else{
			$currency_row=db::get_one('currency', 'ManageDefault=1');
			$rate_val=100*(100/(100*(float)$currency_row['ExchangeRate']));
			$data['Rate']=($p_ExchangeRate*$rate_val)/100;
			$logs='添加货币：'.$p_Currency;
			db::insert('currency', $data);
			$CId=db::get_insert_id();
		}
		manage::operation_log($logs);
		ly200::e_json('', 1);
	}
	
	public static function exchange_switch(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_CId=(int)$g_CId;
		$g_Type=(int)$g_Type;
		$currency_row=db::get_one('currency', "CId='{$g_CId}'");
		if($g_Type==1){//默认前台货币
			if(!$currency_row['IsDefault']){
				$logs='修改货币默认：'.$currency_row['Currency'];
				db::update('currency', '1', array('IsDefault'=>0));
				db::update('currency', "CId='{$g_CId}'", array('IsDefault'=>1, 'IsUsed'=>1));
				manage::operation_log($logs);
				ly200::e_json('', 1);
			}else{
				ly200::e_json('');
			}
		}elseif($g_Type==2){//默认后台货币
			if(!$currency_row['ManageDefault']){
				db::query("update currency set Rate=(100/((100/ExchangeRate/100)*{$currency_row['ExchangeRate']})/100), ManageDefault=0 where 1");
				$logs='修改货币后台默认：'.$currency_row['Currency'];
				db::update('currency', "CId='{$g_CId}'", array('ManageDefault'=>1, 'Rate'=>1, 'IsUsed'=>1));
				manage::operation_log($logs);
				ly200::e_json('', 1);
			}else{
				ly200::e_json('');
			}
		}else{//启用货币
			$data=array();
			if($currency_row['IsUsed']){
				$data['IsUsed']=0;
			}else{
				$data['IsUsed']=1;
			}
			$logs=($data['IsUsed']?'启用':'关闭').'货币：'.$currency_row['Currency'];
			db::update('currency', "CId='{$g_CId}'", $data);
			manage::operation_log($logs);
			ly200::e_json('', 1);
		}
	}
	
	public static function exchange_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$CId=(int)$g_CId;
		$currency_row=db::get_one('currency', "CId='{$CId}'");
		$logs='删除货币：'.$currency_row['Currency'];
		db::delete('currency', "CId='{$CId}'");
		if((int)$currency_row['ManageDefault']){
			$row=db::get_one('currency', 1, 'CId');
			db::update('currency', "CId='{$row['CId']}'", array('IsDefault'=>1));
		}
		if((int)$currency_row['ManageDefault']){
			$row=db::get_one('currency', 1, 'CId');
			db::update('currency', "CId='{$row['CId']}'", array('ManageDefault'=>1));
		}
		manage::operation_log($logs);
		ly200::e_json('', 1);
	}
	
	public static function oauth_edit(){
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_SId=(int)$p_SId;
		$json_ary=array();
		
		foreach($p_Value as $Key=>$Val){
			$json_ary[$Key]['IsUsed']=(int)$p_IsUsed[$Key];//开启
			$error=0;
			$name=$p_Name[$Key];
			$value=$p_Value[$Key];
			for($i=0, $len=count($value); $i<$len; $i++){
				if(str::str_str($value[$i], array("'", '"', '&', '<', '>', '&quot;', '&#039;', '&amp;', '&lt;', '&gt;'))){
					$value[$i]='';
				}
				$json_ary[$Key]['Data'][$name[$i]]=$value[$i];
				!$value[$i] && $error=1;//其中一项内容为空
			}
			$error==1 && $json_ary[$Key]['IsUsed']=0;
		}
		$json_data=addslashes(str::json_data(str::str_code($json_ary, 'stripslashes')));
		$data=array(
			'LogoPath'	=>	$p_LogoPath,
			//'IsUsed'	=>	(int)$p_IsUsed,
			'Data'		=>	$json_data,
		);
		db::update('sign_in', "SId='$p_SId'", $data);
		ly200::e_json('', 1);
	}
	
	public static function payment_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_PId=(int)$p_PId;
		$p_MyOrder=(int)$p_MyOrder;
		$LogoPath=$p_LogoPath;
		$MinPrice=$p_MinPrice;
		$MaxPrice=$p_MaxPrice;
		$MaxPrice<$MinPrice && $MaxPrice=0;
		$json_ary=array();
		for($i=0, $len=count($p_Value); $i<$len; $i++){
			$json_ary[$p_Name[$i]]=@trim($p_Value[$i]);
		}
		$json_data=addslashes(str::json_data(str::str_code($json_ary, 'stripslashes')));
		$data=array(
			'LogoPath'		=>	$LogoPath,
			'IsUsed'		=>	(int)$p_IsUsed,
			'IsCreditCard'	=>	(int)$p_IsCreditCard,
			'MinPrice'		=>	$MinPrice,
			'MaxPrice'		=>	$MaxPrice,
			'AdditionalFee'	=>	(float)$p_AdditionalFee,
			'AffixPrice'	=>	(float)$p_AffixPrice,
			'Attribute'		=>	$json_data,
			'MyOrder'		=>	$p_MyOrder
		);
		db::update('payment', "PId='$p_PId'", $data);
		manage::database_language_operation('payment', "PId='$p_PId'", array('Name'=>0, 'Description'=>3));
		ly200::e_json('', 1);
	}
	
	public static function country_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$CId=(int)$p_CId;
		$p_IsUsed=(int)$p_IsUsed;
		$p_IsHot=(int)$p_IsHot;
		
		$country_ary=array();
		foreach($c['manage']['config']['Language'] as $k=>$v){
			$country_ary[$v]=${'p_Country_'.$v};
			if($v=='en') $p_Country=${'p_Country_'.$v};
		}
		if(!in_array('en', $c['manage']['config']['Language'])){//没有英文版
			$p_Country=${'p_Country_'.$c['manage']['config']['LanguageDefault']};//默认语言
		}
		$CountryData=addslashes(str::json_data(str::str_code($country_ary, 'stripslashes')));
		
		$data=array(
			'Acronym'	=>	strtoupper($p_Acronym),
			'Code'		=>	$p_Code,
			'Currency'	=>	(int)$p_Currency,
			'FlagPath'	=>	$p_FlagPath,
			'IsUsed'	=>	$p_IsUsed,
			'IsHot'		=>	$p_IsUsed?(int)$p_IsHot:0,
			'IsDefault'	=>	$p_IsUsed&&$p_IsHot?(int)$p_IsDefault:0,
			'HasState'	=>	$p_IsUsed?(int)$p_HasState:$p_IsUsed
		);
		(int)$p_Continent && $data['Continent']=$p_Continent;
		if(!$CId || $CId>240){
			$data['Country']=$p_Country;
			$data['CountryData']=$CountryData;
		}
		(int)$data['IsDefault'] && db::update('country', '1', array('IsDefault'=>0));
		if($CId){
			$logs='修改国家信息：'.str::str_code(db::get_value('country', "CId='{$CId}'", 'Country'));
			db::update('country', "CId='{$CId}'", $data);
			
			$default_count=(int)db::get_row_count('country', "IsDefault=1");
			if(!$default_count){//突然关闭整个默认国家，或者关闭当前国家的默认选项，防止没有默认国家
				db::update('country', "CId='".($CId==1?2:1)."'", array('IsUsed'=>1, 'IsHot'=>1, 'IsDefault'=>1));
			}
		}else{
			$logs='添加国家：'.$p_Country;
			db::insert('country', $data);
		}
		manage::operation_log($logs);
		ly200::e_json('', 1);
	}
	
	public static function country_switch(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_CId=(int)$g_CId;
		$g_Type=(int)$g_Type;
		$g_Check=(int)$g_Check;
		$country_row=db::get_one('country', "CId='{$g_CId}'");
		if($g_Type==0){//开启国家
			$logs=($g_Check?'开启':'关闭').'国家：'.$country_row['Country'];
			if($g_Check==1){//开启
				$data=array('IsUsed'=>1);
			}else{//关闭
				$data=array('IsUsed'=>0, 'IsHot'=>0);
			}
			db::update('country', "CId='{$g_CId}'", $data);
		}else{//开启热门国家
			$logs=($g_Check?'开启':'关闭').'热门国家：'.$country_row['Country'];
			if($g_Check==1){//开启
				$data=array('IsHot'=>1, 'IsUsed'=>1);
			}else{//关闭
				$data=array('IsHot'=>0);
			}
			db::update('country', "CId='{$g_CId}'", $data);
		}
		manage::operation_log($logs);
		ly200::e_json('', 1);
	}
	
	public static function country_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$CId=(int)$g_CId;
		$logs='删除国家：'.str::str_code(db::get_value('country', "CId='{$CId}'", 'Country'));
		db::delete('country_states', "CId='{$CId}'");
		db::delete('country', "CId='{$CId}'");
		manage::operation_log($logs);
		ly200::e_json('', 1);
	}
	
	public static function country_used_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_cid && ly200::e_json('');
		$g_used=(int)$g_used;
		$bat_where="CId in(".str_replace('-',',',$g_group_cid).")";
		db::update('country', $bat_where, array('IsUsed'=>$g_used));
		manage::operation_log($g_used?'批量开启国家':'批量关闭国家');
		ly200::e_json('', 1);
	}
	
	public static function country_states_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$CId=(int)$g_CId;
		$SId=(int)$g_SId;
		$rows=str::str_code(db::get_one('country_states s left join country c on s.CId=c.CId', "s.SId='{$SId}'", 'c.Country, s.States'));
		$logs="删除{$rows['Country']}省份：（{$rows['States']}）";
		db::delete('country_states', "CId='{$CId}' and SId='{$SId}'");
		manage::operation_log($logs);
		ly200::e_json('', 1);
	}
	
	public static function country_states_order(){
		global $c;
		$order=1;
		$sort_order=@array_filter(@explode('|', $_GET['sort_order']));
		foreach($sort_order as $v){
			db::update('country_states', "SId='$v'", array('MyOrder'=>$order++));
		}
		manage::operation_log('省份排序');
		ly200::e_json('', 1);
	}
	
	public static function country_states_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$CId=(int)$p_CId;
		!$CId && ly200::e_json('');
		$SId=(int)$p_SId;
		$data=array(
			'CId'			=>	(int)$p_CId,
			'States'		=>	$p_States,
			'AcronymCode'	=>	strtoupper($p_AcronymCode),
		);
		if($SId){
			$rows=str::str_code(db::get_one('country_states s left join country c on s.CId=c.CId', "s.SId='{$SId}'", 'c.Country, s.States'));
			$logs="修改{$rows['Country']}省份：（{$rows['States']}）";
			db::update('country_states', "CId='{$CId}' and SId='{$SId}'", $data);
		}else{
			$Country=str::str_code(db::get_value('country', "CId='{$CId}'", 'Country'));
			$logs="添加{$Country}省份：".$p_States;
			db::insert('country_states', $data);
		}
		manage::operation_log($logs);
		ly200::e_json('', 1);
	}

	public static function themes_products_list_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$list_row=db::get_one('config_module', "Themes='".$c['manage']['web_themes']."'", 'Themes, ListData');
		$data_ary=str::json_data(htmlspecialchars_decode($list_row['ListData']), 'decode');
		foreach($data_ary['IsLeftbar'] as $k=>$v){
			if(${'p_Leftbar_'.$k}){
				$list_ary[$k]=1;
			}else{
				$list_ary[$k]=0;
			}
		}
		$data=array(
			'IsColumn'		=>	(int)$p_IsColumn,
			'Narrow'		=>	(int)$p_Narrow,
			'IsLeftbar'		=>	$list_ary,
			'Order'			=>	$p_Order,
			'OrderNumber'	=>	(int)$p_OrderNumber,
			'Effects'		=>	(int)$p_Effects,
		);
		$ListData=addslashes(str::json_data(str::str_code($data, 'stripslashes')));
		db::update('config_module', "Themes='".$c['manage']['web_themes']."'", array('ListData'=>$ListData));
		manage::operation_log('修改列表设置');
		ly200::e_json('', 1);
	}
	
	public static function themes_products_list_reset(){
		global $c;
		$themes_set_file=$c['root_path']."/static/themes/{$c['manage']['web_themes']}/inc/themes_set.php";
		if(@is_file($themes_set_file)){
			include($themes_set_file);
			$data=themes_set::themes_products_list_reset();
			if($data){
				db::update('config_module', "Themes='{$c['manage']['web_themes']}'", array('ListData'=>$data));
				manage::operation_log('重置为默认列表设置');
				ly200::e_json('', 1);
			}
			ly200::e_json('重置失败！');
		}
		ly200::e_json('重置失败！');
	}
	
	public static function themes_products_detail_themes_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$detail_row=db::get_one('config_module', "Themes='".$c['manage']['web_themes']."'", 'Themes, DetailData');
		$data_ary=str::json_data(htmlspecialchars_decode($detail_row['DetailData']), 'decode');
		foreach($data_ary as $k=>$v){
			$data_ary[$k]=0;
		}
		$data_ary[$p_Key]=1;
		$DetailData=addslashes(str::json_data(str::str_code($data_ary, 'stripslashes')));
		db::update('config_module', "Themes='".$c['manage']['web_themes']."'", array('DetailData'=>$DetailData));
		manage::operation_log('选择产品详细风格');
		ly200::e_json('', 1);
	}
	
	public static function themes_products_detail_nav_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$Share=addslashes(str::json_data(str::str_code($p_Share, 'stripslashes')));
		$data_ary=array();
		foreach((array)$p_Url as $k=>$v){
			foreach($c['manage']['config']['Language'] as $k2=>$v2){
				$data_ary[$k]["Name_{$v2}"]=${'p_Name_'.$v2}[$k];
			}
			$data_ary[$k]['Url']=$p_Url[$k];
			$data_ary[$k]['NewTarget']=$p_NewTarget[$k];
		}
		$ProDetail=addslashes(str::json_data(str::str_code($data_ary, 'stripslashes')));
		$data=array(
			'Share'		=>	$Share,
			'ProDetail'	=>	$ProDetail
		);
		manage::config_operaction($data, 'global');
		manage::operation_log('修改产品详细设置');
		ly200::e_json('', 1);
	}
	
	public static function themes_nav_edit(){
		global $c;
		str::keywords_filter();
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Id=(int)$p_Id;
		$p_Page=(int)$p_Page;
		$p_Info=(int)$p_Info;
		$p_Cate=(int)$p_Cate;
		$p_Down=(int)$p_Down;
		$p_DownWidth=(int)$p_DownWidth;
		$p_NewTarget=(int)$p_NewTarget;
		$nav_row=db::get_value('config', "GroupId='themes' and Variable='".($p_Type=='nav'?'NavData':'FooterData')."'", 'Value');
		$nav_data=str::json_data($nav_row, 'decode');
		if($p_Nav==-1){//自定义项
			$data=array();
			$data['Custom']=1;
			foreach($c['manage']['config']['Language'] as $k2=>$v2){
				$data["Name_{$v2}"]=${'p_Name_'.$v2};
			}
			$data['Url']=$p_Url;
			$data['NewTarget']=$p_NewTarget;
		}else{//固定项
			$data=array(
				'Nav'		=>	$p_Nav,//导航栏目，详细请看 $c['nav_cfg']
				'Page'		=>	$p_Nav==1?$p_Page:0,//单页
				'Info'		=>	$p_Nav==2?$p_Info:0,//文章
				'Cate'		=>	$p_Nav==3?$p_Cate:0,//产品
				'Down'		=>	$p_Down,//下拉
				'DownWidth'	=>	$p_DownWidth,//下拉框宽度
				'NewTarget'	=>	$p_NewTarget,//新窗口
			);
		}
		if($p_Id==0){//添加
			$nav_data[]=$data;
		}else{//修改
			$nav_data[$p_Id-1]=$data;
		}
		$NavData=addslashes(str::json_data(str::str_code($nav_data, 'stripslashes')));
		manage::config_operaction(array(($p_Type=='nav'?'NavData':'FooterData')=>$NavData), 'themes');
		manage::operation_log($p_Type=='nav'?'修改导航设置':'修改底部栏目设置');
		ly200::e_json('', 1);
	}
	
	public static function themes_nav_order(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$MyOrderAry=@explode('|', $g_sort_order);
		$nav_row=db::get_value('config', "GroupId='themes' and Variable='".($g_Type=='nav'?'NavData':'FooterData')."'", 'Value');
		$nav_data=str::json_data($nav_row, 'decode');
		$data_ary=array();
		foreach((array)$MyOrderAry as $num){
			$data_ary[]=$nav_data[$num];
		}
		$NavData=addslashes(str::json_data(str::str_code($data_ary, 'stripslashes')));
		manage::config_operaction(array(($g_Type=='nav'?'NavData':'FooterData')=>$NavData), 'themes');
		manage::operation_log(($g_Type=='nav'?'导航排序':'底部栏目排序'));
		ly200::e_json('', 1);
	}
	
	public static function themes_nav_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_Id=(int)$g_Id;
		!$g_Id && ly200::e_json('');
		$nav_row=db::get_value('config', "GroupId='themes' and Variable='".($g_Type=='nav'?'NavData':'FooterData')."'", 'Value');
		$nav_data=str::json_data($nav_row, 'decode');
		unset($nav_data[$g_Id-1]);
		$NavData=addslashes(str::json_data(str::str_code($nav_data, 'stripslashes')));
		manage::config_operaction(array(($g_Type=='nav'?'NavData':'FooterData')=>$NavData), 'themes');
		manage::operation_log(($g_Type=='nav'?'删除导航':'删除底部栏目'));
		ly200::e_json('', 1);
	}
	
	public static function themes_nav_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$IdAry=@explode('-', $g_group_id);
		$nav_row=db::get_value('config', "GroupId='themes' and Variable='".($g_Type=='nav'?'NavData':'FooterData')."'", 'Value');
		$nav_data=str::json_data($nav_row, 'decode');
		foreach((array)$IdAry as $v){
			unset($nav_data[$v]);
		}
		$NavData=addslashes(str::json_data(str::str_code($nav_data, 'stripslashes')));
		manage::config_operaction(array(($g_Type=='nav'?'NavData':'FooterData')=>$NavData), 'themes');
		manage::operation_log(($g_Type=='nav'?'批量删除导航':'批量删除底部栏目'));
		ly200::e_json('', 1);
	}
	
	public static function themes_style_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$data=array();
		@trim($p_FontColor, '#')		&& $data['FontColor']		='#'.trim($p_FontColor, '#');
		@trim($p_NavBgColor, '#')		&& $data['NavBgColor']		='#'.trim($p_NavBgColor, '#');
		@trim($p_NavHoverBgColor, '#')	&& $data['NavHoverBgColor']	='#'.trim($p_NavHoverBgColor, '#');
		@trim($p_NavBorderColor1, '#')	&& $data['NavBorderColor1']	='#'.trim($p_NavBorderColor1, '#');
		@trim($p_NavBorderColor2, '#')	&& $data['NavBorderColor2']	='#'.trim($p_NavBorderColor2, '#');
		@trim($p_CategoryBgColor, '#')	&& $data['CategoryBgColor']	='#'.trim($p_CategoryBgColor, '#');
		@trim($p_PriceColor, '#')		&& $data['PriceColor']		='#'.trim($p_PriceColor, '#');
		@trim($p_SearchBgColor, '#')	&& $data['SearchBgColor']	='#'.trim($p_SearchBgColor, '#');
		@trim($p_AddtoCartBgColor, '#')	&& $data['AddtoCartBgColor']='#'.trim($p_AddtoCartBgColor, '#');
		@trim($p_BuyNowBgColor, '#')	&& $data['BuyNowBgColor']	='#'.trim($p_BuyNowBgColor, '#');
		@trim($p_ReviewBgColor, '#')	&& $data['ReviewBgColor']	='#'.trim($p_ReviewBgColor, '#');
		@trim($p_DiscountBgColor, '#')	&& $data['DiscountBgColor']	='#'.trim($p_DiscountBgColor, '#');
		@trim($p_ProListBgColor, '#')	&& $data['ProListBgColor']	='#'.trim($p_ProListBgColor, '#');
		@trim($p_ProListHoverBgColor, '#')	&& $data['ProListHoverBgColor']	='#'.trim($p_ProListHoverBgColor, '#');
		@trim($p_GoodBorderColor, '#')	&& $data['GoodBorderColor']	='#'.trim($p_GoodBorderColor, '#');
		@trim($p_GoodBorderHoverColor, '#')	&& $data['GoodBorderHoverColor']	='#'.trim($p_GoodBorderHoverColor, '#');
		for($i=0;$i<3;++$i){
			@trim(${'p_IndexMenuBg_'.$i}, '#')	&& $data['IndexMenuBg_'.$i]	='#'.trim(${'p_IndexMenuBg_'.$i}, '#');
		}
		$StyleData=addslashes(str::json_data(str::str_code($data, 'stripslashes')));
		db::update('config_module', "Themes='".$c['manage']['web_themes']."'", array('StyleData'=>$StyleData));
		manage::operation_log('修改风格样式');
		ly200::e_json('', 1);
	}
	
	public static function themes_style_reset(){
		global $c;
		$themes_set_file=$c['root_path']."/static/themes/{$c['manage']['web_themes']}/inc/themes_set.php";
		if(@is_file($themes_set_file)){
			include($themes_set_file);
			$data=themes_set::themes_style_reset();
			if($data){
				db::update('config_module', "Themes='".$c['manage']['web_themes']."'", array('StyleData'=>$data));
				manage::operation_log('重置为默认样式管理');
				ly200::e_json('', 1);
			}
			ly200::e_json('重置失败！');
		}
		ly200::e_json('重置失败！');
	}
	
	public static function shipping_area_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$AId=(int)$g_AId;
		$w="AId='$AId'";
		db::delete('shipping_area', $w);
		db::delete('shipping_country', $w);
		manage::operation_log('删除快递分区');
		ly200::e_json('', 1);
	}
	
	public static function shipping_set_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$IsAir=(int)$p_IsAir;
		$AirWeightCfg=(float)$p_AirWeightCfg;
		$AirVolumeCfg=(float)$p_AirVolumeCfg;
		$IsOcean=(int)$p_IsOcean;
		$OceanWeightCfg=(float)$p_OceanWeightCfg;
		$OceanVolumeCfg=(float)$p_OceanVolumeCfg;
		$data=array(
			'IsAir'				=>	$IsAir,
			'AirWeightCfg'		=>	$AirWeightCfg,
			'AirVolumeCfg'		=>	$AirVolumeCfg,
			'IsOcean'			=>	$IsOcean,
			'OceanWeightCfg'	=>	$OceanWeightCfg,
			'OceanVolumeCfg'	=>	$OceanVolumeCfg,
		);
		db::get_row_count('shipping_config')?db::update('shipping_config', '1', $data):db::insert('shipping_config', $data);
		manage::operation_log('修改运费设置');
		ly200::e_json('', 1);
	}
	
	public static function shipping_overseas_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_OvId=(int)$p_OvId;
		$data=array('MyOrder'=>0);
		if($p_OvId>0){
			db::update('shipping_overseas', "OvId='$p_OvId'", $data);
			manage::operation_log('修改海外仓');
		}else{
			db::insert('shipping_overseas', $data);
			$p_OvId=db::get_insert_id();
			manage::operation_log('添加海外仓');
		}
		manage::database_language_operation('shipping_overseas', "OvId='$p_OvId'", array('Name'=>1));
		ly200::e_json('', 1);
	}
	
	public static function shipping_overseas_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_OvId=(int)$g_OvId;
		$overseas=db::get_value('shipping_overseas', "OvId='$g_OvId'", 'Name'.$c['manage']['web_lang']); //海外仓名称
		db::delete('shipping_overseas', "OvId='$g_OvId'");
		db::delete('shipping_country', "AId in(select AId from shipping_area where OvId='$g_OvId')");
		db::delete('shipping_area', "OvId='$g_OvId'");
		db::delete('products_selected_attribute', "OvId='$g_OvId'");
		db::delete('products_selected_attribute_combination', "OvId='$g_OvId'");
		manage::operation_log('删除海外仓 '.$overseas);
		ly200::e_json('', 1);
	}
	
	public static function shipping_express_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$SId=(int)$g_SId;
		$Logo=db::get_value('shipping', "SId='$SId'", 'Logo');
		if($Logo){
			$resize_ary=$c['manage']['resize_ary']['shipping'];
			$ext_name=file::get_ext_name($Logo);
			file::del_file($Logo);//删除旧图
			foreach($resize_ary as $v){
				file::del_file($Logo.".{$v}.{$ext_name}");//删除旧图
			}
		}
		db::delete('shipping', "SId='$SId'");
		db::delete('shipping_area', "SId='$SId'");
		db::delete('shipping_country', "SId='$SId'");
		manage::operation_log('删除快递公司');
		ly200::e_json('', 1);
	}
	
	public static function shipping_express_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$SId=(int)$p_SId;
		$Express=$p_Express;
		$Logo=$p_Logo;
		$IsUsed=(int)$p_IsUsed;
		$Brief=$p_Brief;
		$IsWeightArea=(int)$p_IsWeightArea;//是否按照重量区间计算运费 1是 0否 2两者一齐计
		$FirstWeight=$IsWeightArea!=1?$p_FirstWeight:0;
		$ExtWeight=$IsWeightArea!=1?$p_ExtWeight:0;
		$StartWeight=$IsWeightArea==2?(float)$p_StartWeight:0;//重量区间开始计算的重量
		$StartWeight=($FirstWeight>$StartWeight || $StartWeight<=0)?$FirstWeight:$StartWeight;
		$MinWeight=$p_MinWeight;
		$MaxWeight=$p_MaxWeight;
		$MinVolume=$p_MinVolume;
		$MaxVolume=$p_MaxVolume;
		$MaxWeight<$MinWeight && $MaxWeight=0;
		$FirstMinQty=$p_FirstMinQty;
		$FirstMaxQty=$p_FirstMaxQty;
		$ExtQty=$p_ExtQty;
		$WeightArea=array();//重量区间
		if($IsWeightArea==1 || $IsWeightArea==2){
			$WeightArea=$p_WeightArea;
			$WeightArea[0]<$StartWeight && $WeightArea[0]=$StartWeight;
		}
		if($IsWeightArea==0 || $IsWeightArea==2){
			$ExtWeightArea=$p_ExtWeightArea;
			$ExtWeightArea[0]<=$FirstWeight && $ExtWeightArea[0]=$FirstWeight+0.001;
		}
		if($IsWeightArea==3){//按数量
			if($FirstMaxQty<$FirstMinQty){//首重最高数量 < 首重最低数量，互换一下各自的位置
				$_FirstMaxQty=$FirstMaxQty;
				$FirstMaxQty=$FirstMinQty;
				$FirstMinQty=$_FirstMaxQty;
			}
		}
		if($IsWeightArea==4){//重量体积混合
			$WeightArea=$p_WeightArea;
			$WeightArea[0]<$MinWeight && $WeightArea[0]=$MinWeight;
			$VolumeArea=$p_VolumeArea;
			$VolumeArea[0]<$MinVolume && $VolumeArea[0]=$MinVolume;
		}
		if($IsWeightArea==2){
			$MinW=$StartWeight;
		}elseif($IsWeightArea==4){
			$MinW=$MinWeight;
		}else $MinW=0;
		$WeightArea=str::ary_del_min($WeightArea, $MinW);//清除重量区间低于区间开始重量的值
		$VolumeArea=str::ary_del_min($VolumeArea, $MinVolume);//清除体积区间低于最低限制体积的值
		$ExtWeightArea=str::ary_del_min($ExtWeightArea, $ExtWeight);//清除续重区间低于续重的值
		//接口的数据
		$json_ary=array();
		for($i=0, $len=count($p_Value); $i<$len; $i++){
			$json_ary[$p_Name[$i]]=$p_Value[$i];
		}
		$json_data=addslashes(str::json_data(str::str_code($json_ary, 'stripslashes')));
		$data=array(
			'Express'		=>	$Express,
			'Logo'			=>	$Logo,
			'IsUsed'		=>	$IsUsed,
			'IsAPI'			=>	(int)$p_IsAPI,
			'Brief'			=>	$Brief,
			'Query'			=>	$p_Query,
			'IsWeightArea'	=>	$IsWeightArea,
			'WeightArea'	=>	addslashes(str::json_data(str::str_code($WeightArea, 'stripslashes'))),
			'ExtWeightArea'	=>	addslashes(str::json_data(str::str_code($ExtWeightArea, 'stripslashes'))),
			'VolumeArea'	=>	addslashes(str::json_data(str::str_code($VolumeArea, 'stripslashes'))),
			'FirstWeight'	=>	$FirstWeight,
			'ExtWeight'		=>	$ExtWeight,
			'StartWeight'	=>	$StartWeight,
			'MinWeight'		=>	$MinWeight,
			'MaxWeight'		=>	$MaxWeight,
			'MinVolume'		=>	$MinVolume,
			'MaxVolume'		=>	$MaxVolume,
			'FirstMinQty'	=>	$FirstMinQty,
			'FirstMaxQty'	=>	$FirstMaxQty,
			'ExtQty'		=>	$ExtQty,
			'WeightType'	=>	(int)$p_WeightType,
			'MyOrder'		=>	(int)$p_MyOrder
		);
		if($SId){
			db::update('shipping', "SId='$SId'", $data);
			manage::operation_log('修改快递公司');
		}else{
			db::insert('shipping', $data);
			manage::operation_log('添加快递公司');
		}
		if($p_IsAPI>0){//更新接口数据
			db::update('shipping_api', "AId='$p_IsAPI'", array('Attribute'=>$json_data));
		}
		ly200::e_json('', 1);
	}
	
	public static function shipping_express_area_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$SId=(int)$p_SId;
		$OvId=(int)$p_OvId;
		$AId=(int)$p_AId;
		$Name=$p_Name;
		$Brief=$p_Brief;
		$FirstPrice=$p_FirstPrice;
		$ExtPrice=$p_ExtPrice;
		$FirstQtyPrice=$p_FirstQtyPrice;
		$ExtQtyPrice=$p_ExtQtyPrice;
		$IsFreeShipping=(int)$p_IsFreeShipping;
		$FreeShippingPrice=$p_IsFreeShipping?$p_FreeShippingPrice:0;
		$FreeShippingWeight=$p_IsFreeShipping?$p_FreeShippingWeight:0;
		$WeightAreaPrice=addslashes(str::json_data(str::str_code((array)$p_WeightAreaPrice, 'stripslashes')));
		$ExtWeightAreaPrice=addslashes(str::json_data(str::str_code((array)$p_ExtWeightAreaPrice, 'stripslashes')));
		$VolumeAreaPrice=addslashes(str::json_data(str::str_code((array)$p_VolumeAreaPrice, 'stripslashes')));
		$data=array(
			'SId'				=>	$SId,
			'OvId'				=>	$OvId,
			'Name'				=>	$Name,
			'Brief'				=>	$Brief,
			'FirstPrice'		=>	$FirstPrice,
			'ExtPrice'			=>	$ExtPrice,
			'WeightAreaPrice'	=>	$WeightAreaPrice,
			'ExtWeightAreaPrice'=>	$ExtWeightAreaPrice,
			'VolumeAreaPrice'	=>	$VolumeAreaPrice,
			'IsFreeShipping'	=>	$IsFreeShipping,
			'FreeShippingPrice'	=>	$FreeShippingPrice,
			'FreeShippingWeight'=>	$FreeShippingWeight,
			'FirstQtyPrice'		=>	$FirstQtyPrice,
			'ExtQtyPrice'		=>	$ExtQtyPrice,
			'AffixPrice'		=>	(float)$p_AffixPrice
		);
		if($AId){
			db::update('shipping_area', "AId='$AId' and SId='$SId'", $data);
			manage::operation_log('修改快递分区');
		}else{
			db::insert('shipping_area', $data);
			manage::operation_log('添加快递分区');
		}
		ly200::e_json($OvId, 1);
	}
	
	public static function shipping_express_area_country_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$SId=$p_SId;
		$AId=$p_AId;
		$CId=(array)$p_CId;
		db::delete('shipping_country', "SId='$SId' and AId='$AId'");//删除原有的
		if($CId){
			$sql='INSERT INTO `shipping_country` (`Id`, `SId`, `AId`, `CId`, `type`) VALUES';
			foreach($CId as $k=>$v){
				$sql.="('', '$SId', '$AId', '$v', ''),";
			}
			$sql=trim($sql, ',');
			db::query($sql);
		}
		manage::operation_log('修改分区国家');
		ly200::e_json('', 1);
	}
	
	public static function shipping_insurance_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$ProPrice=$p_ProPrice;
		$AreaPrice=$p_AreaPrice;
		$arr=array();
		foreach($ProPrice as $k=>$v){
			if($v>=0) $arr[]=array((int)$v, (float)$AreaPrice[$k]);
		}
		$arr=str::ary_unique($arr);//删除重复，空值，0
		sort($arr);//从小到大排序，防止故意乱填
		$json_str=addslashes(str::json_data(str::str_code($arr, 'stripslashes')));
		$data=array('AreaPrice'=>$json_str,);
		db::get_row_count('shipping_insurance')?db::update('shipping_insurance', '1', $data):db::insert('shipping_insurance', $data);
		$p_IsInsurance=(int)$p_IsInsurance;
		db::update('shipping_config', '1', array('IsInsurance'=>$p_IsInsurance));
		manage::operation_log('修改运费保险');
		ly200::e_json('', 1);
	}
	
	public static function photo_choice(){//图片银行选择图片
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$CateId=(int)$p_CateId;
		$PId=(array)$p_PId;
		$type=$p_type;//type类型,例如products,editor
		$maxpic=$p_maxpic;//最大允许图片数，0未不允许再上传图片，-1为没有数量限制
		$sum=0;//图片数量
		$Path=array('ret'=>1, 'msg'=>'', 'type'=>$type, 'Pic'=>array());//保存图片路径
		if($maxpic==0){
			$Path['ret']=0;
			$Path['msg']='超过允许上传图片的数量';
			exit(str::json_data($Path));
		}
		$save_dir='';
		$sub_save_dir=$c['manage']['sub_save_dir'];
		if($sub_save_dir[$type]){//来自缩略图，先保存到tmp临时文件夹
			$save_dir=$c['tmp_dir'].'photo/';
		}
		$save_dir && file::mk_dir($save_dir);
		if($p_sort){
			$id_ary=array();
			$p_sort=explode('|', $p_sort);
			foreach((array)$p_sort as $k=>$v){
				if(in_array($v, $PId)){
					$id_ary[]=$v;
				}
			}
			$PId=$id_ary;
		}
		foreach($PId as $k=>$v){//复制选择的图片到指定路径
			$sPic=db::get_value('photo', "PId='$v'", 'PicPath');
			$Pic=str_replace('\\', '/', $c['root_path']).ltrim($sPic, '/');
			if(is_file($Pic)){
				if ($save_dir){//有缩略图的保存到临时文件，没有使用图片银行路径
					$ext_name=file::get_ext_name($Pic);
					$temp=$Path['Pic'][]=$save_dir.str::rand_code().'.'.$ext_name;
					@copy($Pic, $c['root_path'].ltrim($temp, '/'));
				}else{
					$Path['Pic'][]=$sPic;
				}
				$sum++;
			}
			if($sum>=$maxpic && $maxpic>0){break;}//判断是否已超过允许图片数量
		}
		if(!$sum){//没有上传任何图片
			$Path['ret']=0;
			$Path['msg']='没有添加任何图片';
		}elseif ($type!='editor' && $save_dir){//非编辑器时，根据配置生成压缩图片
			$water_ary=array();
			$resize_ary=$c['manage']['resize_ary'];
			if(array_key_exists($type, $resize_ary)){
				foreach($Path['Pic'] as $key=>$value){
					(!$c['manage']['config']['IsWaterPro'] && $c['manage']['config']['IsWater']) && $water_ary[$key]=$value;
					if(in_array('default', $resize_ary[$type])){//保存不加水印的原图
						$ext_name=file::get_ext_name($value);
						@copy($c['root_path'].$value, $c['root_path'].$value.".default.{$ext_name}");
					}
					if(!$c['manage']['config']['IsWaterPro'] && $c['manage']['config']['IsWater'] && $c['manage']['config']['IsThumbnail']){//缩略图加水印
						img::img_add_watermark($value);
						unset($water_ary[$key]);
					}
					foreach((array)$resize_ary[$type] as $v){
						if($v=='default') continue;
						$size_w_h=explode('x', $v);
						$resize_path=img::resize($value, $size_w_h[0], $size_w_h[1]);
					}
				}
			}
			foreach((array)$water_ary as $v){
				img::img_add_watermark($v);
			}
		}
		exit(str::json_data($Path));
	}
	
	public static function photo_category(){//图片银行分类
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$CateId=(int)$p_CateId;
		$Category=$p_Category;
		$UnderTheCateId=(int)$p_UnderTheCateId;
		if($UnderTheCateId==0){
			$UId='0,';
			$Dept=1;
		}else{
			$UId=category::get_UId_by_CateId($UnderTheCateId, 'photo_category');
			$Dept=substr_count($UId, ',');
		}
		$data=array(
			'Category'	=>	$Category,
			'UId'		=>	$UId,
			'Dept'		=>	$Dept
		);
		if($CateId){
			db::update('photo_category', "CateId='$CateId'", $data);
			manage::operation_log('修改图片银行分类');
		}else{
			db::insert('photo_category', $data);
			$CateId=db::get_insert_id();
			manage::operation_log('添加图片银行分类');
		}
		$UId!='0,' && $CateId=category::get_top_CateId_by_UId($UId);
		$statistic_where.=category::get_search_where_by_CateId($CateId, 'photo_category');
		category::category_subcate_statistic('photo_category', $statistic_where);
		ly200::e_json('', 1);
	}
	
	public static function photo_category_edit_myorder(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$order=1;
		$sort_order=@array_filter(@explode('|', $g_sort_order));
		if($sort_order){
			$sql="UPDATE `photo_category` SET `MyOrder` = CASE `CateId`";
			foreach((array)$sort_order as $v){
				$sql.=" WHEN $v THEN ".$order++;
			}
			$sql.=" END WHERE `CateId` IN (".str_replace('|', ',', $g_sort_order).")";
			db::query($sql);
		}
		manage::operation_log('图片管理分类修改排序');
		ly200::e_json('', 1);
	}
	
	public static function photo_category_order(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$order=1;
		$sort_order=@array_filter(@explode('|', $g_sort_order));
		if($sort_order){
			$sql="UPDATE `photo_category` SET `MyOrder` = CASE `CateId`";
			foreach((array)$sort_order as $v){
				$sql.=" WHEN $v THEN ".$order++;
			}
			$sql.=" END WHERE `CateId` IN (".str_replace('|', ',', $g_sort_order).")";
			db::query($sql);
		}
		manage::operation_log('批量图片管理分类排序');
		ly200::e_json('', 1);
	}
	
	public static function photo_category_del(){//图片银行分类删除
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$CateId=(int)$g_CateId;
		$row=db::get_one('photo_category', "CateId='$CateId'", 'UId');
		$del_where=category::get_search_where_by_CateId($CateId, 'photo_category');
		db::delete('photo_category', $del_where);
		//删除分类下的图片
		$photo_row=db::get_all('photo', $del_where, 'PicPath');
		foreach($photo_row as $k=>$v){
			file::del_file($v['PicPath']);
		}
		db::delete('photo', $del_where);
		if($row['UId']!='0,'){
			$CateId=category::get_top_CateId_by_UId($row['UId']);
			$statistic_where=category::get_search_where_by_CateId($CateId, 'photo_category');
			category::category_subcate_statistic('photo_category', $statistic_where);
		}
		manage::operation_log('删除图片管理分类');
		ly200::e_json('', 1);
	}
	
	public static function photo_category_del_bat(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		!$g_group_id && ly200::e_json('');
		$del_where="CateId in(".str_replace('-',',',$g_group_id).")";
		$row=str::str_code(db::get_all('photo_category', $del_where));
		db::delete('photo_category', $del_where);
		//删除分类下的图片
		$photo_row=db::get_all('photo', $del_where, 'PicPath');
		foreach($photo_row as $k=>$v){
			file::del_file($v['PicPath']);
		}
		db::delete('photo', $del_where);
		manage::operation_log('批量删除图片管理分类');
		foreach($row as $v){
			if($v['UId']!='0,'){
				$CateId=category::get_top_CateId_by_UId($v['UId']);
				$statistic_where=category::get_search_where_by_CateId($CateId, 'photo_category');
				category::category_subcate_statistic('photo_category', $statistic_where);
			}
		}
		ly200::e_json('', 1);
	}
	
	public static function photo_category_select(){
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_ParentId=(int)$p_ParentId;
		$p_CateId=(int)$p_CateId;
		$category_one=str::str_code(db::get_one('photo_category', "CateId='$p_CateId'"));
		$ext_where="CateId!='{$category_one['CateId']}' and Dept<2";
		echo category::ouput_Category_to_Select('UnderTheCateId', ($ParentId?$ParentId:category::get_CateId_by_UId($category_one['UId'])), 'photo_category', "UId='0,' and $ext_where", $ext_where, '', $c['manage']['lang_pack']['global']['select_index']);
		exit;
	}
	
	public static function photo_file_upload(){//图片银行图片上传
		global $c;
		exit(file::file_upload_swf($c['tmp_dir'].'photo/', '', false));
	}
	
	public static function photo_upload(){//图片银行图片添加提交处理函数
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$CateId=(int)$p_CateId;
		//上传图片
		$PicPath=$p_PicPath;
		$Name=$p_Name;
		//检查图片
		foreach((array)$PicPath as $k=>$v){
			file::photo_add_item($v, $Name[$k], 0, $CateId);
		}
		ly200::e_json(array('jump'=>'./?m=set&a=photo&CateId='.$CateId), 1);
	}
	
	public static function photo_list_del(){//图片银行批量删除图片
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$CateId=(int)$p_CateId;
		$PId=(array)$p_PId;
		$count=count($PId);
		foreach($PId as $k=>$v){
			$Pic=db::get_value('photo', "PId='$v'", 'PicPath');
			file::del_file($Pic);
			db::delete('photo', "PId='$v'");
		}
		manage::operation_log('图片银行批量删除图片 数目：'.$count);
		ly200::e_json('', 1);
	}
	
	public static function photo_upload_del(){	//flash上传删除单个产品图片
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$Model=$g_Model;
		$PicPath=$g_Path;
		$Index=(int)$g_Index;
		if(is_file($c['root_path'].$PicPath)){
			file::del_file($PicPath);
		}
		ly200::e_json(array($Index), 1);
	}
	
	public static function photo_move(){//图片移动
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_PId=(array)$p_PId;
		$p_CateId=(int)$p_CateId;
		foreach($p_PId as $k=>$PId){
			$photo_row=str::str_code(db::get_one('photo', "PId='$PId'"));
			if($photo_row['CateId']!=$p_CateId){
				db::update('photo', "PId='$PId'", array('CateId'=>$p_CateId));
			}
		}
		ly200::e_json('', 1);
	}
	
	public static function photo_clear_folder(){//清空临时文件夹
		global $c; 
		file::del_dir($c['tmp_dir'].'photo/');
		ly200::e_json('', 1);
	}
	
	public static function chat_set(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$w="GroupId='chat' and Variable='chat_bg'";
		$Color['Color'] = $p_Color;
		$Color['ColorTop'] = $p_ColorTop;
		foreach ((array)$c['chat']['type'] as $k=>$v){
			$Color[$k] = $_POST['Color'.$k];
		}
		$Color['Bg3_0'] = $p_Bg3_0;
		$Color['Bg3_1'] = $p_Bg3_1;
		$Color['Bg4_0'] = $p_Bg4_0;
		$Color['IsHide'] = (int)$p_IsHide;
		$ValueColor = json_encode($Color);
		
		$data=array(
			'GroupId'	=>	'chat',
			'Variable'	=>	'chat_bg',
			'Value'		=>	$ValueColor
		);
		(int)db::get_row_count('config', $w)?db::update('config', $w, $data):db::insert('config', $data);
		$w="GroupId='chat' and Variable='IsFloatChat'";
		$data=array(
			'GroupId'	=>	'chat',
			'Variable'	=>	'IsFloatChat',
			'Value'		=>	(int)$p_IsFloatChat
		);
		(int)db::get_row_count('config', $w)?db::update('config', $w, $data):db::insert('config', $data);
		manage::operation_log('修改浮动客服');

		ly200::e_json('', 1);
	}
	
	public static function chat_style(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_Type=(int)$p_Type;
		$OldType=(int)db::get_value('config', 'GroupId="chat" and Variable="Type"', 'Value');
		if($OldType!=$p_Type){
			manage::config_operaction(array('Type'=>$p_Type), 'chat');
			manage::operation_log('修改浮动客服样式');
		}
		ly200::e_json('', 1);
	}

	public static function chat_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_CId=(int)$p_CId;
		$data=array(
			'Name'		=>	$p_Name,
			'Type'		=>	$p_Type,
			'PicPath'	=>	$p_PicPath,
			'Account'	=>	$p_Account
		);
		if($p_CId){
			db::update('chat', "CId='{$p_CId}'", $data);
			$log='修改在线客服: '.$p_Name;
		}else{
			db::insert('chat', $data);
			$log='添加在线客服: '.$p_Name;
		}
		manage::operation_log($log);
		ly200::e_json('', 1);
	}
	
	public static function chat_my_order(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$order=1;
		$sort_order=@array_filter(@explode('|', $g_sort_order));
		if ($sort_order){
			$sql = "UPDATE `chat` SET `MyOrder` = CASE `CId`";
			foreach((array)$sort_order as $v){
				$sql .= " WHEN $v THEN ".$order++;
			}
			$sql .= " END WHERE `CId` IN (".str_replace('|', ',', $g_sort_order).")";
			db::query($sql);
		}
		manage::operation_log('批量在线客服排序');
		ly200::e_json('', 1);
	}

	public static function chat_del(){
		global $c;
		@extract($_GET, EXTR_PREFIX_ALL, 'g');
		$g_CId=(int)$g_CId;
		$name=db::get_value('chat', "CId='{$g_CId}'", 'Name');
		db::delete('chat', "CId='{$g_CId}'");
		manage::operation_log('删除在线客服: '.$name);
		ly200::e_json('', 1);
	}

	/*******************************平台授权(start)*****************************/
	public static function set_open_api(){
		global $c;
		$appkey=str::rand_code(20);
		
		if(!db::get_row_count('config', "GroupId='API' and Variable='AppKey'", 'CId')){
			db::insert('config', array(
					'GroupId'	=>	'API',
					'Variable'	=>	'AppKey',
					'Value'		=>	$appkey
				)
			);
			$return_data['jump']=1;
		}else{
			db::update('config', "GroupId='API' and Variable='AppKey'", array('Value'=>$appkey));
			$return_data['appkey']=$appkey;
		}
		
		ly200::e_json($return_data, 1);
	}

	public static function del_open_api(){
		global $c;
		
		db::delete('config', "GroupId='API' and Variable='AppKey'");
		ly200::e_json('', 1);
	}

	public static function authhz_url(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$data=array();
		
		if($p_d=='aliexpress'){
			$notify_url=@base64_encode(ly200::get_domain().'/gateway/');
			$return_url=@base64_encode(ly200::get_domain().'/manage/?m=set&a=authorization&d=aliexpress&callback=1&iframe=1');
			$query_string="notify_url={$notify_url}&return_url={$return_url}";
			$p_Name && $query_string.="&name={$p_Name}";
			$p_Account && $query_string.="&account={$p_Account}";
			
			$data=array(
				'ApiKey'		=>	'ueeshop_sync',
				'Number'		=>	$c['Number'],
				'ApiName'		=>	'aliexpress',
				'Action'		=>	'authhz_url',
				'query_string'	=>	$query_string,
				'timestamp'		=>	$c['time']
			);
			$data['sign']=ly200::sign($data, $c['ApiKey']);
			$result=str::json_data(ly200::curl($c['sync_url'], $data), 'decode');
			if($result['ret']==1){
				ly200::e_json($result['msg'], 1);
			}
		}
		
		ly200::e_json('');
	}

	public static function authorization_add(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$data=array();
		
		if($p_d=='amazon'){
			if($c['FunVersion']<2) return;
			db::get_row_count('authorization', "Platform='amazon' and Name='$p_Name'") && ly200::e_json("店铺名({$p_Name})已存在，请重新设置店铺名！");
			db::get_row_count('authorization', "Platform='amazon' and Account='$p_MerchantId'") && ly200::e_json("Merchant ID({$p_MerchantId})已授权，不可重复授权！");
			
			$Token=array(
				'MerchantId'	=>	$p_MerchantId,
				'AWSAccessKeyId'=>	$p_AWSAccessKeyId,
				'SecretKey'		=>	$p_SecretKey,
				'MarkectPlace'	=>	$p_MarkectPlace
			);
			$data=@array_merge($Token, array(
					'ApiKey'		=>	'ueeshop_sync',
					'Number'		=>	$c['Number'],
					'ApiName'		=>	'amazon',
					'Action'		=>	'authorization',
					'timestamp'		=>	$c['time']
				)
			);
			$data['sign']=ly200::sign($data, $c['ApiKey']);
			
			$result=str::json_data(ly200::curl($c['sync_url'], $data), 'decode');
			if($result['ret']==1){//授权成功
				db::insert('authorization', array(
						'Platform'	=>	'amazon',
						'Name'		=>	$p_Name,
						'Account'	=>	$p_MerchantId,
						'Token'		=>	str::json_data($Token),
						'Data'		=>	@implode(',', $result['msg']),
						'AccTime'	=>	$c['time']
					)
				);

				ly200::e_json('', 1);
			}else ly200::e_json('授权失败，请检查输入信息是否正确!');
		}
		ly200::e_json('', 1);
	}

	public static function authorization_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_AId=(int)$p_AId;
		(!$p_AId || !$p_Name || !$p_d) && ly200::e_json('数据缺失，请重试！');
		db::get_row_count('authorization', "Platform='{$p_d}' and Name='{$p_Name}' and AId!='{$p_AId}'") && ly200::e_json('数据缺失，请重试！');
		$return=array('AId'=>$p_AId,'Name'=>$p_Name, 'token'=>$data['Token']);		
		
		$data=array('Name'=>addslashes(stripslashes($p_Name)));
		if($p_d=='amazon'){
			if($c['FunVersion']<2) return;
			$row=db::get_one('authorization', "Platform='{$p_d}' and AId='{$p_AId}'");
			$MarkectPlaceAry=@explode(',', $row['Data']);
			!in_array($p_MarkectPlace, $MarkectPlaceAry) && ly200::e_json('开户站选择不正确！');
			$token=str::json_data($row['Token'], 'decode');
			if($p_MarkectPlace && $p_MarkectPlace!=$token['MarkectPlace']){
				$token['MarkectPlace']=$p_MarkectPlace;
				$data['Token']=str::json_data($token);
				$return['token']=str::str_code($data['Token']);
			}
		}
		db::update('authorization', "AId='{$p_AId}'", $data);
		
		
		ly200::e_json($return, 1);
	}
	
	public static function authorization_del(){//删除授权
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_AId=(int)$p_AId;
		!$p_AId && ly200::e_json('删除的对象不存在！');
		
		$config=array(
			'ApiKey'		=>	'ueeshop_sync',
			'Number'		=>	$c['Number'],
			'Action'		=>	'authorization_del',
			'timestamp'		=>	$c['time']
		);

		
		$row=db::get_one('authorization', "AId='{$p_AId}'", '*');
		if($row['Platform']=='aliexpress'){//速卖通
			($row['Account']==$_SESSION['Manage']['Aliexpress']['Token']['Account']) && aliexpress::set_default_authorization();
			
			$data=@array_merge($config, array(
					'Account'		=>	$row['Account'],
					'ApiName'		=>	'aliexpress',
				)
			);
			$data['sign']=ly200::sign($data, $c['ApiKey']);
			ly200::curl($c['sync_url'], $data);
		}elseif($row['Platform']=='amazon'){//亚马逊
			if($c['FunVersion']<2) return;
			$data=@array_merge($config, array(
					'MerchantId'	=>	$row['Account'],
					'ApiName'		=>	'amazon',
				)
			);
			$data['sign']=ly200::sign($data, $c['ApiKey']);
			ly200::curl($c['sync_url'], $data);
		}
		
		db::delete('authorization', "AId='{$p_AId}'");
		ly200::e_json('', 1);
	}
	/*******************************平台授权(end)*****************************/
}
?>