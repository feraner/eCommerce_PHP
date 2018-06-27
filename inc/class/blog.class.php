<?php
/*
Powered by ly200.com		http://www.ly200.com
广州联雅网络科技有限公司		020-83226791
*/

class blog{
	public static function user_reg_edit($name, $notnull, $class, $row=''){
		global $c;
		$result='';
		$notnull=$notnull?' notnull=""':'';
		
		switch($name){
			case 'Gender':
				$result=ly200::form_select($c['gender'], $name, '', '', '', 'Please select ...', $notnull);
				break;
			case 'Age':
				$result=user::form_edit($row, 'text', $name, 10, 3, "class='{$class}'".$notnull);
				break;
			case 'NickName':
				$result=user::form_edit($row, 'text', $name, 30, 50, "class='{$class}'".$notnull);
				break;
			case 'Telephone':
				$result=user::form_edit($row, 'text', $name, 30, 20, "class='{$class}' format='Telephone'".$notnull);
				break;
			case 'Fax':
				$result=user::form_edit($row, 'text', $name, 30, 20, "class='{$class}' format='Fax'".$notnull);
				break;
			case 'Birthday':
				$result=user::form_edit($row, 'text', $name, 30, 20, "class='{$class}'".$notnull);
				break;
			case 'Facebook':
				$result=user::form_edit($row, 'text', $name, 30, 50, "class='{$class}'".$notnull);
				break;
			case 'Company':
				$result=user::form_edit($row, 'text', $name, 30, 50, "class='{$class}'".$notnull);
				break;
		}
		return $result;
	}
	
}
?>