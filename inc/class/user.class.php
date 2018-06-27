<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class user{
	public static function check_login($url='', $type=0){//type 0:跳转返回 1:返回结果
		if((int)$_SESSION['User']['UserId']){
			$data=array();
			$data=$_SESSION['User'];
			$data['fetch_where']="UserId={$data[UserId]}";
			return $data;
		}else{
			if($type){
				return false;
			}else{
				js::location('/account/sign-up.html'.($url?'?&jumpUrl='.str_replace('&', '%ap;', $url):''), '', '.top');
			}
		}
	}
	
	public static function operation_log($UserId, $Logs, $OperationType=0){
		global $c;
		$data='';
		if($_POST){
			$post_data=@array_filter($_POST);
			foreach($post_data as $k=>$v){
				$post_data[$k]=substr_count(strtolower($k), 'password')?'<font color=red>removed</font>':(is_array($v)?'Array':htmlspecialchars(substr($v, 0, 200)));
			}
			$data.=($data?"\n":'').'POST='.print_r($post_data, true);
		}
		$data=str_replace(array("Array\n(", "\n)\n"), array('Array(', "\n)"), $data);
		db::insert('user_operation_log', array(
				'UserId'		=>	$UserId,
				'OperationType'	=>	(int)$OperationType,
				'Log'			=>	addslashes($Logs),
				'Data'			=>	$data,
				'AccTime'		=>	$c['time'],
				'Ip'			=>	ly200::get_ip()
			)
		);
	}

	public static function user_reg_edit($name, $notnull, $class, $row=''){
		global $c;
		$result='';
		$notnull=$notnull?' notnull=""':'';
		switch($name){
			case 'Gender':
				$result=ly200::form_select($c['gender'], $name, $row['Gender'], '', '', '--'.$c['lang_pack']['plesaeSelect'].'--', $notnull);
				break;
			case 'Age':
				$result=user::form_edit($row, 'text', $name, 10, 3, "class='{$class} amount'".$notnull);
				break;
			case 'NickName':
				$result=user::form_edit($row, 'text', $name, 30, 50, "class='{$class}'".$notnull);
				break;
			case 'Telephone':
				$result=user::form_edit($row, 'text', $name, 30, 20, "class='{$class} amount'".$notnull);
				break;
			case 'Fax':
				$result=user::form_edit($row, 'text', $name, 30, 20, "class='{$class} amount' format='Fax'".$notnull);
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
	
	public static function form_edit($row, $type='text', $name, $size=0, $max=0, $attr=''){
		global $c;
		$result='';
		$value=$row[$name];
		if($type=='text'){
			$result.="<input type='text' name='{$name}' value='{$value}' size='{$size}' maxlength='{$max}' {$attr}>";
			$attr=='notnull' && $result.=' <font class="fc_red">*</font>';
		}elseif($type=='textarea'){
			$result.="<textarea name='{$name}' {$attr}>{$value}</textarea>";
		}
		return $result;
	}
	
	public static function user_new_reg_edit($name, $title, $notnull, $class, $row=''){
		global $c;
		$result='';
		$notnull=$notnull?' notnull=""':'';
		switch($name){
			case 'Gender':
				$result=ly200::form_select($c['gender'], $name, $title, $row['Gender'], '', '', '--'.$c['lang_pack']['plesaeSelect'].'--', $notnull);
				break;
			case 'Age':
				$result=user::new_form_edit($row, 'text', $name, $title, 10, 3, "class='{$class} amount'".$notnull);
				break;
			case 'NickName':
				$result=user::new_form_edit($row, 'text', $name, $title, 30, 50, "class='{$class}'".$notnull);
				break;
			case 'Telephone':
				$result=user::new_form_edit($row, 'text', $name, $title, 30, 20, "class='{$class} amount'".$notnull);
				break;
			case 'Fax':
				$result=user::new_form_edit($row, 'text', $name, $title, 30, 20, "class='{$class} amount' format='Fax'".$notnull);
				break;
			case 'Birthday':
				$result=user::new_form_edit($row, 'text', $name, $title, 30, 20, "class='{$class}'".$notnull);
				break;
			case 'Facebook':
				$result=user::new_form_edit($row, 'text', $name, $title, 30, 50, "class='{$class}'".$notnull);
				break;
			case 'Company':
				$result=user::new_form_edit($row, 'text', $name, $title, 30, 50, "class='{$class}'".$notnull);
				break;
		}
		return $result;
	}
	
	public static function new_form_edit($row, $type='text', $name, $title, $size=0, $max=0, $attr=''){
		global $c;
		$result='';
		$value=$row[$name];
		if($type=='text'){
			$result.='<label class="input_box'.($value!=''?' filled':'').'">';
			$result.=	'<span class="input_box_label">'.$title.'</span>';
			$result.=	'<input type="text" class="input_box_txt" name="'.$name.'" value="'.$value.'" size="'.$size.'" maxlength="'.$max.'" placeholder="'.$title.'" '.$attr.($name=='Birthday'?' readonly':'').' />';
			$result.='</label>';
		}elseif($type=='textarea'){
			$result.="<textarea name='{$name}' {$attr}>{$value}</textarea>";
		}
		return $result;
	}
	
	public static function get_tax_info($row){
		$data=array();
		$data['CodeOptionId']=(int)$row['CodeOption'];
		if((int)$row['CodeOption']){
			$TaxCodeOption=array(
				1	=>	'CPF',
				2	=>	'CNPJ',
				3	=>	'ID',
				4	=>	'VAT ID',
			);
			$data['CodeOption']=$TaxCodeOption[$row['CodeOption']];
			$data['TaxCode']=$row['TaxCode'];
		}
		return $data;
	}
	
	public static function check_email($email){
		$email_ary=array(
			'@163.com'=>'http://mail.163.com/',
			'@126.com'=>'http://www.126.com/',
			
			'@aol.com'=>'http://www.mail.aol.com/',
			'@att.net'=>'http://www.att.net/',
			'@aim.com'=>'http://www.aim.com/',
			'@abv.bg'=>'http://mail.bg/',
			'@aon.at'=>'http://mail.@aon.at/',
			'@azet.sk'=>'http://www.azet.sk/',
			
			'@btinternet.com'=>'http://mail.@btinternet.com/',
			'@bellsouth.net'=>'http://mail.@bellsouth.net/',
			'@bigpond.com'=>'http://mail.@bigpond.com/',
			'@bluewin.ch'=>'http://mail.@bluewin.ch/',
			'@bol.com.br'=>'http://mail.@bol.com.br/',
			'@blueyonder.co.uk'=>'http://mail.@blueyonder.co.uk/',
			'@bigpond.net.au'=>'http://mail.@bigpond.net.au/',
			
			'@comcast.net'=>'http://mail.@comcast.net/',
			'@cox.net'=>'http://mail.@cox.net/',
			'@charter.net'=>'http://mail.@charter.net/',
			'@cegetel.net'=>'http://mail.@cegetel.net/',
			
			'@dbmail.com'=>'http://www.dbmail.org/',
			
			'@earthlink.net'=>'http://webmail.earthlink.net/',
			'@eircom.net'=>'http://www.eir.ie/email/',
			'@embarqmail.com'=>'http://secure.centurylink.net/',
			'@ebuyclub.com'=>'http://mail.@ebuyclub.com/',
			
			'@fsmail.net'=>'http://mail.@fsmail.net/',
			'@freemail.hu'=>'http://mail.@freemail.hu/',
			
			'@gmail.com'=>'http://www.gmail.com/',
			'@googlemail.com'=>'http://www.googlemail.com/',
			'@gmx.net'=>'http://mail.@gmx.net/',
			'@gmx.at'=>'http://mail.@gmx.at/',
			'@gmx.ch'=>'http://mail.@gmx.ch/',
			'@gmx.com'=>'http://mail.@gmx.com/',
			
			'@hotmail.com'=>'http://login.live.com/',
			'@hotmail.co.uk'=>'http://login.live.com/',
			'@hotmail.be'=>'http://login.live.com/',
			'@hotmail.ca'=>'http://login.live.com/',
			
			'@ig.com.br'=>'http://mail.@ig.com.br/',
			'@inbox.lv'=>'http://mail.@inbox.lv/',
			'@iinet.net.au'=>'http://mail.@iinet.net.au/',
			
			'@juno.com'=>'http://mail.@juno.com/',
			
			'@live.com'=>'http://mail.@live.com/',
			'@laposte.net'=>'http://mail.@laposte.net/',
			'@live.co.uk'=>'http://mail.@live.co.uk/',
			'@live.com.au'=>'http://mail.@live.com.au/',
			'@live.be'=>'http://mail.@live.be/',
			'@live.ca'=>'http://mail.@live.ca/',
			'@lightinthebox.com'=>'http://mail.@lightinthebox.com/',
			'@live.ie'=>'http://mail.@live.ie/',
			
			'@msn.com'=>'http://www.msn.com/',
			'@me.com'=>'http://mail.@me.com/',
			'@mac.com'=>'http://mail.@mac.com/',
			'@mail.com'=>'http://www.mail.com/',
			'@mchsi.com'=>'http://www.mchsi.com/',
			
			'@ntlworld.com'=>'http://mail.@ntlworld.com/',
			'@netzero.com'=>'http://mail.@netzero.com/',
			'@netzero.net'=>'http://mail.@netzero.net/',
			'@netscape.net'=>'http://mail.@netscape.net/',
			
			'@optonline.net'=>'http://mail.@optonline.net/',
			'@optusnet.com.au'=>'http://mail.@optusnet.com.au/',
			'@ono.com'=>'http://mail.@ono.com/',
			'@o2.pl'=>'http://mail.@o2.pl/',
			
			'@qq.com'=>'http://mail.qq.com/',
			'@q.com'=>'http://mail.@q.com/',
			
			'@rocketmail.com'=>'http://mail.@rocketmail.com/',
			'@rogers.com'=>'http://mail.@rogers.com/',
			'@roadrunner.com'=>'http://mail.@roadrunner.com/',
			
			'@sbcglobal.net'=>'http://mail.@sbcglobal.net/',
			'@sky.com'=>'http://mail.@sky.com/',
			'@skynet.be'=>'http://mail.@skynet.be/',
			'@shaw.ca'=>'http://mail.@shaw.ca/',
			'@seznam.cz'=>'http://mail.@seznam.cz/',
			'@sympatico.ca'=>'http://mail.@sympatico.ca/',
			
			'@talktalk.net'=>'http://mail.@talktalk.net/',
			'@tiscali.co.uk'=>'http://mail.@tiscali.co.uk/',
			'@terra.com.br'=>'http://mail.@terra.com.br/',
			'@telenet.be'=>'http://mail.@telenet.be/',
			'@telefonica.net'=>'http://mail.@telefonica.net/',
			'@telus.net'=>'http://mail.@telus.net/',
			
			'@uol.com.br'=>'http://mail.@uol.com.br/',
			'@us.army.mil'=>'http://mail.@us.army.mil/',
			
			'@verizon.net'=>'http://mail.@verizon.net/',
			'@videotron.ca'=>'http://mail.@videotron.ca/',
			'@virgin.net'=>'http://mail.@virgin.net/',
			
			'@windowslive.com'=>'http://mail.@windowslive.com/',
			'@wp.pl'=>'http://mail.@wp.pl/',
			'@windstream.net'=>'http://mail.@windstream.net/',
			'@walla.com'=>'http://mail.@walla.com/',
			
			'@xtra.co.nz'=>'http://mail.@xtra.co.nz/',
			
			'@yahoo.com'=>'http://login.yahoo.com/',
			'@yahoo.co.uk'=>'http://login.yahoo.com/',
		
			'@ymail.com'=>'http://login.yahoo.com/',
			'@yahoo.com.br'=>'http://login.yahoo.com/',
			'@yahoo.ca'=>'http://login.yahoo.com/',
			'@yahoo.com.au'=>'http://login.yahoo.com/',
			'@yahoo.gr'=>'http://login.yahoo.com/',
			'@yahoo.ie'=>'http://login.yahoo.com/',
			'@yahoo.co.in'=>'http://login.yahoo.com/',
			'@yahoo.com.ar'=>'http://login.yahoo.com/',
			'@yahoo.com.mx'=>'http://login.yahoo.com/'
		);
		$return='';
		foreach($email_ary as $k=>$v){
			if((int)stripos($email, $k)){
				$return=array('account'=>str_replace($k, '', $email), 'suffix'=>$k, 'url'=>$v);
				break;
			}
		}
		
		return $return;
	}

	public static function make_card($char='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', $pre='', $len='10'){ //构造优惠码
		$card=$pre;
		for($i=0; $i<$len; $i++){
			$card.=substr($char, rand(0,strlen($char)-1), 1);
		}
		$num=db::get_row_count('sales_coupon', "CouponNumber='$card'");
		if(!$num){
			return $card;
		}else{
			self::make_card($char, $pre, $len);
		}
	}

	public static function get_user_coupons($UserId=0, $ParentId=0){ //领取优惠券
		global $c;
		$Parent_row = db::get_one('sales_coupon',"CId='{$ParentId}'");
		if(!db::get_row_count('sales_coupon',"CouponWay=0 and UserId = '{$UserId}' and ParentId = '{$ParentId}'")){
			$ExtAry = str::json_data(htmlspecialchars_decode($Parent_row['CouponExt']),'decode');
			$char1='ABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$char2='0123456789';
			$char='';
			$ExtAry['c1']=='1' && $char.=$char1;
			$ExtAry['c2']=='1' && $char.=$char2;
			$CouponNumber=self::make_card($char,$ExtAry['Prefix'],$ExtAry['CodeLen']);
			$data=array(
				'ParentId'		=>	$ParentId,
				'CouponNumber'	=>	$CouponNumber,
				'Discount'		=>	$Parent_row['Discount'],
				'Money'			=>	$Parent_row['Money'],
				'CouponType'	=>	$Parent_row['CouponType'],
				'UseCondition'	=>	$Parent_row['UseCondition'],
				'StartTime'		=>	$Parent_row['StartTime'],
				'EndTime'		=>	$Parent_row['EndTime'],
				'UseNum'		=>	$Parent_row['UseNum'],
				'UserId'		=>	$UserId,
				'LevelId'		=>	$Parent_row['LevelId'],
				'CateId'		=>	$Parent_row['CateId'],
				'ProId'			=>	$Parent_row['ProId'],
				'TagId'			=>	$Parent_row['TagId'],
				'AccTime'		=>	$c['time']
			);
			db::insert('sales_coupon', $data);
			return $CouponNumber;
		}
	}
}
?>