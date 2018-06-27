<?php !isset($c) && exit();?>
<?php
$default_lang=$c['manage']?$c['manage']['config']['LanguageDefault']:$c['config']['global']['LanguageDefault'];
$mail_lang=$c['lang']?substr($c['lang'], 1):$default_lang;
$mail_row=db::get_one('system_email_tpl', "Template='{$Template}' and IsUsed=1");//查询系统模板数据
$mail_title=$mail_contents='';
if($mail_row['Content_'.$mail_lang]){//读取自定义数据
	$mail_title=ly200::system_email_tpl($mail_row['Title_'.$mail_lang], $mail_data);
	$mail_contents=ly200::system_email_tpl($mail_row['Content_'.$mail_lang], $mail_data);
	$mail_title=trim($mail_title);
	$mail_title=$mail_title?$mail_title:ly200::system_email_tpl($c['sys_email_tpl_title'][$Template], $mail_data);//为空时使用默认标题
	/**************** 部分图片追加域名 Start ****************/
	$FullDomain=ly200::get_domain();
	$replace_before_ary=array('"/u_file/', "'/u_file/");
	$replace_after_ary=array('"'.$FullDomain.'/u_file/', "'".$FullDomain."/u_file/");
	$mail_contents=str_replace($replace_before_ary, $replace_after_ary, $mail_contents);
	/**************** 部分图片追加域名 End ****************/
}