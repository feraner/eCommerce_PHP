<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

include('../inc/global.php');

//@extract($_POST['Action']!=''?$_POST:$_GET, EXTR_PREFIX_ALL, 'p');
@extract($_POST, EXTR_PREFIX_ALL, 'p');
if(!$_POST['Action'] && $_GET['ApiName']=='spdcat') @extract($_GET, EXTR_PREFIX_ALL, 'p');

($p_Number=='' || $p_Number!=$c['Number'] || $p_Action=='' || $p_timestamp=='' || $p_sign=='') && ueeshop::e_json('非法的请求！');
abs($p_timestamp-$c['time'])>1800 && ly200::e_json('请求超时!');

$c['ApiKey']=ly200::appkey($p_ApiName);
$c['ApiKey']=='' && ly200::e_json('查找不到对应的Api Key！');
ly200::sign(str::str_code($_POST['Action']!=''?$_POST:$_GET, 'stripslashes'), $c['ApiKey'])!=$p_sign && ly200::e_json('签名错误');
$action_ary=array();
$c['FunVersion']>=1 && $action_ary[]='aliexpress';
$c['FunVersion']>=2 && $action_ary[]='amazon';
$c['FunVersion']>=2 && $action_ary[]='spdcat';
$c['FunVersion']>=2 && $action_ary[]='dianxiaomi';

$class=@in_array($p_ApiName, $action_ary)?$p_ApiName:'action';
include("../inc/class/api/$class.class.php");
method_exists($class.'_api', $p_Action) && call_user_func(array($class.'_api', $p_Action));

ly200::e_json('what are you doing?');
?>