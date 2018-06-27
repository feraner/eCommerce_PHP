<?php !isset($c) && exit();?>
<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

js::jump_301();	//301跳转

//网站基本设置
$config_row=db::get_all('config', "GroupId='global' or GroupId='translate' or GroupId='chat' or (GroupId='products_show' and Variable='Config') or (GroupId='products_show' and Variable='tab') or (GroupId='products_show' and Variable='item') or (GroupId='content_show' and Variable='Config') or (GroupId='email' and Variable='notice')");
foreach($config_row as $v){
	if(in_array("{$v['GroupId']}|{$v['Variable']}", array('content_show|Config', 'products_show|Config', 'global|OrdersSmsStatus', 'global|SearchTips', 'global|CopyRight', 'global|CloseWeb', 'global|Notice', 'global|ArrivalInfo', 'global|IndexContent', 'global|HeaderContent', 'global|TopMenu', 'global|ContactMenu', 'global|ShareMenu', 'global|LanguageFlag', 'global|LanguageCurrency', 'translate|TranLangs', 'email|notice'))){
		$c['config'][$v['GroupId']][$v['Variable']]=str::json_data(htmlspecialchars_decode($v['Value']), 'decode');
	}elseif("{$v['GroupId']}|{$v['Variable']}"=='global|Language'){
		$c['config'][$v['GroupId']][$v['Variable']]=explode(',', $v['Value']);
	}else{
		$c['config'][$v['GroupId']][$v['Variable']]=$v['Value'];
	}
}
//平台授权
$platform_row=db::get_all('sign_in', '1');
foreach($platform_row as $v){
	$c['config']['Platform'][$v['Title']]=str::json_data(htmlspecialchars_decode($v['Data']), 'decode');
	$c['config']['Platform'][$v['Title']]['ReturnUrl']=$v['ReturnUrl'];//第三方登录返回地址
}

//设置语言版
$c['lang_oth']=str_replace('-', '_', array_shift(explode('.', $_SERVER['HTTP_HOST'])));
$c['lang']='_'.(@in_array($c['lang_oth'], $c['config']['global']['Language'])?$c['lang_oth']:$c['config']['global']['LanguageDefault']);

//发货地
$overseas_row=str::str_code(db::get_all('shipping_overseas', '1', '*', $c['my_order'].'OvId asc'));
foreach($overseas_row as $v){
	if((int)$c['config']['global']['Overseas']==0 && $v['OvId']!=1) continue; //关闭海外仓功能，不是China的都踢走
	$v['Name'.$c['lang']]=='' && $v['Name'.$c['lang']]=$v['Name_en'];
	$c['config']['Overseas'][$v['OvId']]=$v;
}

//网站关闭
if(ly200::lock_close_website()){
	echo $c['config']['global']['CloseWeb']['CloseWeb'.$c['lang']];
	exit;
}
//浏览器语言跳转
if(!$_SESSION['jump_lang']){
	js::jump_lang();
}

//前台风格设置
if(!ly200::is_mobile_client(1)){//网页版设置
	$c['theme']='t057';
	$c['theme_path']=$c['root_path']."/static/themes/{$c['theme']}/";//当前风格的物理路径
}else{//手机版设置
	$c['theme_path']=$c['root_path'].$c['mobile']['tpl_dir'];	//手机版当前风格的物理路径
	$mobile_config=array();
	$set_row=db::get_all('config', "GroupId='mobile'");
	foreach($set_row as $v){
		$mobile_config[$v['Variable']]=$v['Value'];
	}
	$c['mobile']=array_merge($c['mobile'], array(
			'theme_path'=>	$c['root_path'].$c['mobile']['tpl_dir'],//手机版物理路径
			'HeadIcon'	=>	$mobile_config['HeadIcon'],//图标 0 白色 1 黑色
			'HeadFixed'	=>	$mobile_config['HeadFixed'],//固定头部
			'LogoPath'	=>	@is_file($c['root_path'].$mobile_config['LogoPath'])?$mobile_config['LogoPath']:$c['config']['global']['LogoPath'],//Logo
			'HomeTpl'	=>	@is_file("{$c['theme_path']}index/{$mobile_config['HomeTpl']}/template.php")?$mobile_config['HomeTpl']:'06',//首页模板
			'ListTpl'	=>	@is_file("{$c['theme_path']}products/{$mobile_config['ListTpl']}/template.php")?$mobile_config['ListTpl']:'03'//列表页模板
		)
	);
}

$c['static_path']=$c['root_path']."/static/static/";	//风格入口的物理路径
$c['default_path']=$c['root_path']."/static/themes/default/";	//默认风格的物理路径
$c['lang_pack']=include("{$c['static_path']}/lang/".substr($c['lang'], 1).'.php');//加载语言包
$c['holiday_theme']='/static/themes/default/holiday/';
$gender_ary=array();
foreach($c['gender'] as $k=>$v){
	$gender_ary[$k]=$c['lang_pack'][$v];
}
$c['gender'] = $gender_ary;

//产品分类下架
$c['procate_soldout']=array();
$pro_cate_row=db::get_all('products_category', 'IsSoldOut=1', 'CateId, UId, SubCateCount');
foreach($pro_cate_row as $v){
	$c['procate_soldout'][]=$v['CateId'];
	if($v['SubCateCount']){
		$row=db::get_all('products_category', "UId like '{$v['UId']}{$v['CateId']},%'", 'CateId');
		foreach($row as $vv){
			$c['procate_soldout'][]=$vv['CateId'];
		}
	}
}
array_unique($c['procate_soldout']);//去掉重复分类
unset($pro_cate_row, $row);

ly200::check_user_id();//检测保持登录会员ID

//查询条件
$c['where']=array(
	'user'			=>	(int)$_SESSION['User']['UserId']?"UserId='{$_SESSION['User']['UserId']}'":"SessionId='{$c['session_id']}'",
	'cart'			=>	(int)$_SESSION['User']['UserId']?"UserId='{$_SESSION['User']['UserId']}'":"SessionId='{$c['session_id']}'",
	'products'		=>	" and ((SoldOut=0 and IsSoldOut=0) or (SoldOut=0 and IsSoldOut=1 and {$c['time']} between SStartTime and SEndTime))".($c['procate_soldout']?' and CateId not in('.@implode(',', $c['procate_soldout']).')':''),	//产品上下架显示条件
	'products_ext'	=>	array(
							1	=>	' and IsNew=1',
							2	=>	' and IsHot=1',
							3	=>	' and IsBestDeals=1',
							4	=>	" and (IsPromotion=1 and {$c['time']} between StartTime and EndTime)"
						)
);
$c['cache_timeout']=3600*2;	//更新缓存文件间隔(s)
$c['cart']=array('timeout'=>86400*7);	//删除超过N天，非会员所加入的购物车产品(天)

//货币和汇率
$c['shopping_cart']=db::get_one('shopping_cart', $c['where']['user'], "count(CId) as TotalQty, sum((Price+PropertyPrice)*Discount/100*Qty) as TotalPrice");
$c['currency']=array();
$manage_default=0;
$currency_row=db::get_all('currency', "IsUsed='1'", '*', 'IsDefault desc, CId asc');
foreach((array)$currency_row as $k=>$v){
	$c['currency'][]=$v;
	$c['currency']['Symbol'][]=$v['Symbol'];
	$v['ManageDefault']==1 && $manage_default=$k;
}
if(!isset($_SESSION['Currency']) || !$_SESSION['Currency']){
	if(@!in_array($_SESSION['Currency']['Symbol'], $c['currency']['Symbol'])){
		$_SESSION['ManageCurrency']=$c['currency'][$manage_default];//后台默认货币
		/* 不同国家打开网站时对应不同默认货币 */
		$ip_country=ly200::ip(ly200::get_ip(), 'country');//获取当前登录的国家
		$country_row=str::str_code(db::get_all('country c left join currency cc on c.Currency=cc.CId', "c.Currency!='' and cc.IsUsed=1", 'cc.*, c.CountryData', 'c.CId asc'));
		$currency_result=$c['currency'][0];
		foreach($country_row as $v){
			$country_data=str::json_data(htmlspecialchars_decode($v['CountryData']), 'decode');
			if($country_data['zh-cn']==$ip_country){
				unset($v['CountryData']);
				$currency_result=$v;
				break;
			}
		}
		$_SESSION['Currency']=$currency_result;//前台默认货币
		unset($country_row, $country_data, $ip_country);
	}
	/* 不同语言打开网站时对应不同默认货币 */
	if($_currency_id=$c['config']['global']['LanguageCurrency'][substr($c['lang'], 1)]){
		$currency_row=db::get_one('currency', "IsUsed=1 and CId='{$_currency_id}'");
		if($currency_row){
			$_SESSION['Currency']=$currency_row;
		}
	}else{	//还原默认货币
		$_SESSION['Currency']=$currency_result?$currency_result:$c['currency'][0];
	}
}

//程序处理
$do_action=isset($_POST['do_action'])?$_POST['do_action']:$_GET['do_action'];
if($do_action){
	$_=@explode('.', $do_action);
	$do_action_file="{$c['static_path']}/do_action/{$_[0]}.php";
	if(@is_file($do_action_file)){
		include($do_action_file);
		if(method_exists($_[0].'_module', $_[1])){
			eval("{$_[0]}_module::{$_[1]}();");
			exit;
		}
	}
}

//开启“仅会员浏览”功能，非会员自动跳转会员登录页
($c['config']['global']['UserView']==1 && !(int)$_SESSION['User']['UserId'] && !substr_count($_SERVER['REQUEST_URI'], '/account/')) && js::location('/account/sign-up.html');

//默认产品列表排序 1:Name 2:Price 3:New 4:Best Sellers
$c['products_sort']=array(
	'1d'	=>	'IsBestDeals desc,',
	'2d'	=>	'Sales desc,',
	'3d'	=>	'FavoriteCount desc,',
	'4d'	=>	'IsNew desc,',
	'5a'	=>	'LowestPrice asc,',
	'5d'	=>	'LowestPrice desc,'
);

$c['powered_by']=ly200::powered_by((int)$c['HideSupport']);//技术支持
if($_COOKIE['REFERER']==''){//COOKIE记录来源ID
	$referrerId=str::referrer_filter($_SERVER['HTTP_REFERER']);
	$_COOKIE['REFERER']=$referrerId;
	setcookie('REFERER', $referrerId);
}

//加载内容
$m=$_GET['m'];
$a=$_GET['a']?$_GET['a']:($_POST['a']?$_POST['a']:'index');
$d=$_GET['d']?$_GET['d']:$_POST['d'];

ob_start();
if(ly200::is_mobile_client(0) && !in_array($m, array('blog'))){//是否为手机版		//holiday 节日显示PC版有错误
	$file=(in_array($m, array('user', 'cart', 'products', 'tuan', 'seckill', 'gateway')))?"$m/index.php":"$m.php";
	$m=='picture' && $file="products/detail/$a.php"; //产品详细页的主图加载
	if($m!='gateway' && (ly200::lock_china_ip() || ly200::lock_china_browser())){//屏蔽国内IP跳转到屏蔽页面
		include($c['theme_path'].'shield.php');
	}else{
		if($m=='gateway' && @is_file("{$c['default_path']}{$file}")){//支付接口返回路径
			include("{$c['default_path']}{$file}");
		}elseif(@is_file("{$c['theme_path']}{$file}")){
			include("{$c['theme_path']}{$file}");
		}else{//页面不存在跳转到404
			include("{$c['theme_path']}404.php");
		}
	}
}else{
	$shield=1;
	if($m!='gateway' && (ly200::lock_china_ip() || ly200::lock_china_browser())){//屏蔽国内IP跳转到屏蔽页面
		include($c['default_path'].'shield.php');
	}else{
		if($c['config']['translate']['IsTranslate']==1){//设置Google翻译部分
			foreach($c['config']['translate']['TranLangs'] as $k=>$v){
				if(@in_array($v, $c['config']['global']['Language'])){
					unset($c['config']['translate']['TranLangs'][$k]);
				}elseif($v=='zh-ch' && @in_array('cn', $c['config']['global']['Language'])){
					unset($c['config']['translate']['TranLangs'][$k]);
				}elseif($v=='zh-tw' && @in_array('zh_tw', $c['config']['global']['Language'])){
					unset($c['config']['translate']['TranLangs'][$k]);
				}elseif($v=='ja' && @in_array('jp', $c['config']['global']['Language'])){
					unset($c['config']['translate']['TranLangs'][$k]);
				}
			}
		}
		$file=(in_array($m, array('user', 'cart', 'products', 'holiday', 'blog', 'seckill', 'tuan', 'gateway')))?"$m/index.php":"$m.php";
		if($m=='cart' && (int)db::get_value('config', 'GroupId="cart" and Variable="IsOldCart"', 'value')==1){//新旧购物车文件的切换
			$file="{$m}_old/index.php";
		}
		if(@is_file("{$c['theme_path']}{$file}")){//判断当前风格文件是否存在
			include("{$c['theme_path']}{$file}");
		}elseif(@is_file("{$c['default_path']}{$file}")){//否则加载默认文件
			include("{$c['default_path']}{$file}");
		}else{//页面不存在跳转到404
			include($c['default_path'].'404.php');
		}
	}
}
$html=ob_get_contents();
ob_end_clean();
ly200::load_cdn_contents($html);
?>