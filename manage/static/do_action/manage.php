<?php
/*
Powered by ly200.com		http://www.ly200.com
广州联雅网络科技有限公司		020-83226791
*/

class manage_module{
	/******************************************************************************************************************************************************************/
	public static function manage_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		substr_count($p_UserName, '.') && ly200::e_json('用户名不能带有“.”');
		$p_Locked=(int)$p_Locked==1?1:0;
		$p_Method=(int)$p_Method==1?1:0;
		$p_GroupId=(int)$p_GroupId?(int)$p_GroupId:1;
		
		if($p_Method==1){
			$p_Password!='' && strlen($p_Password)<6 && ly200::e_json(manage::get_language('manage.manage.password_len_tips'));
		}else{
			(strlen($p_UserName)<6 || strlen($p_Password)<6) && ly200::e_json(manage::get_language('manage.manage.len_tips'));
		}
		$data=array(
			'Action'	=>	'ueeshop_web_manage_edit',
			'UserName'	=>	$p_UserName,
			'Locked'	=>	$p_Locked,
			'GroupId'	=>	$p_GroupId,
			'Method'	=>	$p_Method
		);
		$p_Password!='' && $data['Password']=ly200::password($p_Password);
		$result=ly200::api($data, $c['ApiKey'], $c['api_url']);
		
		$p_GroupId>1 && manage::update_permit($p_UserName);
		if($p_GroupId==3){
			!db::get_row_count('manage_sales', "UserName='{$p_UserName}'") && db::insert('manage_sales', array('UserName'=>$p_UserName));
		}else{
			db::get_row_count('manage_sales', "UserName='{$p_UserName}'") && db::delete('manage_sales', "UserName='{$p_UserName}'");
		}
		
		manage::operation_log($p_Method==1?'编辑管理员':'添加管理员');
		ly200::e_json('', 1);
	}
	
	public static function manage_del(){
		global $c;
		$UserName=$_GET['u'];
		($_SESSION['Manage']['UserName'] && $_SESSION['Manage']['UserName']==$UserName) && ly200::e_json(manage::get_language('manage.manage.del_current_user'));
		$w="UserName='$UserName'";
		db::delete('manage_operation_log', $w);
		db::delete('manage_permit', $w);
		
		$data=array(
			'Action'	=>	'ueeshop_web_manage_del',
			'UserName'	=>	$UserName
		);
		$result=ly200::api($data, $c['ApiKey'], $c['api_url']);
		db::delete('manage_sales', $w);
		
		manage::operation_log('删除管理员');
		ly200::e_json('', 1);
	}
	/******************************************************************************************************************************************************************/
}
?>