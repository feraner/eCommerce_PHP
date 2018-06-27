<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class action_module{
	//订阅提交
	public static function newsletter(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$ret=0;
		if(empty($p_Email) || !preg_match('/^[a-z0-9]+[a-z0-9_\.\'\-]*@[a-z0-9]+[a-z0-9\.\-]*\.(([a-z]{2,6})|([0-9]{1,3}))$/i', $p_Email)){
			ly200::e_json($p_Email, $ret);
		}
		if(!db::get_row_count('newsletter', "Email='{$p_Email}'")){
			$ret=1;
			$time=$c['time'];
			db::insert('newsletter', array(
					'Email'		=>	$p_Email,
					'AccTime'	=>	$time,
					'IsUsed'	=>	1
				)
			);
		}
		ly200::e_json($p_Email, $ret);
	}
	
	//到货通知提交
	public static function arrival_notice(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$ret=1;
		if((int)$_SESSION['User']['UserId']){
			if(!db::get_row_count('arrival_notice', "ProId='{$p_ProId}' and UserId='{$_SESSION['User']['UserId']}' and IsSend=0")){
				db::insert('arrival_notice', array(
						'ProId'		=>	(int)$p_ProId,
						'UserId'	=>	(int)$_SESSION['User']['UserId'],
						'AccTime'	=>	$c['time']
					)
				);
			}else{
				$ret=3;
			}
		}else{
			$ret=2;
		}
		ly200::e_json('', $ret);
	}
	
	//货币切换
	public static function currency(){
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$currency_row=db::get_one('currency', "IsUsed='1' and Currency='{$p_currency}'");
		!$currency_row && ly200::e_json('', -1);
		$_SESSION['Currency']=$currency_row;
		ly200::e_json('', 1);
	}
	
	//货币切换和语言切换
	public static function change_language_currency(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if($_SESSION['Currency']['Currency']!=$p_currency){ //货币切换
			$currency_row=db::get_one('currency', "IsUsed='1' and Currency='{$p_currency}'");
			$currency_row && $_SESSION['Currency']=$currency_row;
		}
		
		$cur_lang=str_replace('-', '_', substr($c['lang'], 1));
		!(int)$c['config']['translate']['IsTranslate'] && $c['config']['translate']['TranLangs']=array();
		if(in_array($p_language, $c['config']['global']['Language'])){ //优先判断是否为系统默认语言
			$p_language=='zh_tw' && $p_language='zh-tw';
			if(in_array($c['lang_oth'], $c['config']['global']['Language']) || reset(explode('.', $_SERVER['HTTP_HOST']))=='www'){
				$dir=preg_replace('/^'.reset(explode('.', $_SERVER['HTTP_HOST'])).'\./i', '', $_SERVER['HTTP_HOST']);
			}else{
				$dir=$_SERVER['HTTP_HOST'];
			}
			$http=$_SERVER['SERVER_PORT']==443?'https://':'http://';
			$query_string=$_SERVER['REQUEST_URI']!='/'?$_SERVER['REQUEST_URI']:'';
			$domain=($p_language==$c['config']['global']['LanguageDefault']?'':$p_language.'.').$dir;
			if(!$query_string){
				$str=str_replace(array($http, $_SERVER['HTTP_HOST']), '', $_SERVER['HTTP_REFERER']);
				($str && $str!='/') && $query_string=$str;
			}
			$url=$http.$domain.$query_string;

			ly200::e_json($url, 1); //打开页面
		}else{
			$translate_url=urlencode(ly200::get_domain().$_SERVER['REQUEST_URI']);
			$from_lang=$cur_lang=='cn'?'zh-cn':($cur_lang=='jp'?'ja':$cur_lang);
			$lang_link="https://translate.google.com/translate?sl=$from_lang&tl=%s&u=";
			$url=sprintf($lang_link, $p_language).$translate_url;
			ly200::e_json($url, 2); //打开新页面
		}
	}
	
	//秒杀查询
	public static function seckill(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_ProId=str::ary_format($p_ProId, 2);
		if ($p_ProId){
			$seckill_row=db::get_all('sales_seckill', "ProId in({$p_ProId}) and RemainderQty>0 and {$c['time']} between StartTime and EndTime");
			!$seckill_row && ly200::e_json('', -1);
			$seckill_ary=array();
			foreach((array)$seckill_row as $k=>$v){
				$seckill_ary[$v['ProId']]=cart::iconv_price((float)$v['Price'], 2);
			}
			ly200::e_json($seckill_ary, 1);
		}
	}
	
	//博客列表加载
	public static function blog_list_loading(){
		global $c;
		
		$date=(int)$_GET['date'];
		$Keyword=$_GET['Keyword'];
		$CateId=(int)$_GET['CateId'];
		$Tags=$_GET['Tags'];
		$page=(int)$_GET['page'];
		
		$where='1';//条件
		$page_count=10;//显示数量
		$CateId && $where.=' and '.category::get_search_where_by_CateId($CateId, 'blog_category');
		$Keyword && $where.=" and Title like '%$Keyword%'";
		$Tags && $where.=" and Tag like '%|$Tags|%'";
		if($date){
			if(date('m', $date)==12){
				$next_m=mktime(0,0,0,1,1,date('Y',$date)+1);
			}else $next_m=mktime(0,0,0,date('m',$date)+1,1,date('Y',$date));
			$where.=" and AccTime BETWEEN $date and $next_m";
		}
		$blog_row=str::str_code(db::get_limit_page('blog', $where, '*', $c['my_order'].'AId desc', $page, $page_count));
		$blog_row_new=array();
		foreach((array)$blog_row[0] as $k => $v){
			$blog_row_new[$k]['Author']=$v['Author'];
			$blog_row_new[$k]['Day']=date('d', $v['AccTime']);	
			$blog_row_new[$k]['YearMonth']=date('F Y', $v['AccTime']);
			$blog_row_new[$k]['Comments']=(int)db::get_row_count('blog_review', "AId='{$v['AId']}'");
			$blog_row_new[$k]['Url']=ly200::get_url($v, 'blog');
			$blog_row_new[$k]['Title']=$v['Title'];
			$blog_row_new[$k]['BriefDescription']=str::format($v['BriefDescription']);
			$blog_row_new[$k]['PicPath']=$v['PicPath'];
		}
		$blog_row[0]=$blog_row_new;
		ly200::e_json($blog_row, 1);
	}
	//博客评论加载
	public static function blog_review_loading(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$p_AId=(int)$p_AId;
		$p_page=(int)$p_page;
		$review_row=str::str_code(db::get_limit_page('blog_review', "AId='$p_AId'", '*', 'RId desc', $p_page, 10));
		foreach((array)$review_row[0] as $k => $v){
			$review_row[0][$k]['AccTime']=date('F m,Y H:i',$v['AccTime']);
		}
		ly200::e_json($review_row, 1);
	}
	
	//博客评论
	public static function blog_review(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		
		$AId=(int)$p_AId;
		$Name=$p_Name;
		$Email=$p_Email;
		$Content=$p_Content;
		$p_VCode=trim($p_VCode);
		if(strtoupper($p_VCode)!=strtoupper($_SESSION['Ueeshop']['VCode'][md5('blog')])){
			ly200::e_json('Verification code error!', 0);
		}
		$data=array(
			'AId'		=>	$AId,//博客ID
			'Name'		=>	$Name,
			'Email'		=>	$Email,
			'Content'	=>	$Content,
			'AccTime'	=>	$c['time'],
			'Praise'	=>	0
		);
		
		db::insert('blog_review', $data);
		ly200::e_json('Submit Success', 1);
	}
	
	//注册会员邮件验证(重发邮件)
	public static function verification_mail(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		if(!$p_Email || $c['time']<($_SESSION['User']['TmpTime']+60)) ly200::e_json('', 0);//等候一分钟
		$UserId=(int)$p_UserId;
		
		if((int)$c['config']['global']['UserVerification']){
			include($c['static_path'].'/inc/mail/validate_mail.php');
			ly200::sendmail($p_Email, $mail_title, $mail_contents);
			$_SESSION['User']['TmpTime']=$c['time'];
		}
		ly200::e_json('Send Success', 1);
	}
	
}
?>