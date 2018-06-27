<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class mobile_module{
	public static function themes_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$HomeTpl=$p_tpl;
		$data=array(
			'HomeTpl'	=>	$HomeTpl
		);
		manage::config_operaction($data, 'mobile');
		manage::operation_log('修改手机模板首页');
		ly200::e_json('', 1);
	}
	
	public static function products_list_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$ListTpl=$p_tpl;
		$data=array(
			'ListTpl'	=>	$ListTpl
		);
		manage::config_operaction($data, 'mobile');
		manage::operation_log('修改手机模板产品列表页');
		ly200::e_json('', 1);
	}
	
	public static function config_edit(){
		global $c;
		@extract($_POST, EXTR_PREFIX_ALL, 'p');
		$BtnColor='#'.trim($p_btn_color, '#');//按钮字体色
		$BtnBg='#'.trim($p_btn_bg, '#');//按钮背景色
		$CBtnColor='#'.trim($p_cart_btn_color, '#');//购物车按钮字体色
		$CBtnBg='#'.trim($p_cart_btn_bg, '#');//购物车按钮背景色
		$HeadIcon=(int)$p_icon;//头部图标选项
		$HeadBg='#'.trim($p_head_bg, '#');//头部背景色
		$HeadFixed=$p_fixed;//头部固定
		$FootFont='#'.trim($p_font_color, '#');//底部字体颜色
		$FootBg='#'.trim($p_foot_bg, '#');//底部背景色
		$FootNav=array();
		foreach((array)$p_Url as $k=>$v){
			$_arr=array();
			foreach($c['manage']['config']['Language'] as $kk=>$vv){//多语言
				$_temp=(array)$_POST['Name_'.$vv];//保存提交的变量
				$_arr['Name_'.$vv]=$_temp[$k];//保存与url索引对应的名称
			}
			$_arr['Url']=$v;
			$FootNav[]=$_arr;
		}
		$FootNav=addslashes(str::json_data($FootNav));
		$data=array(
			//******************基本信息******************
			'LogoPath'	=>	$p_LogoPath,
			'BtnColor'	=>	$BtnColor,
			'BtnBg'		=>	$BtnBg,
			'CBtnColor'	=>	$CBtnColor,
			'CBtnBg'	=>	$CBtnBg,
			//******************头部管理******************
			'HeadIcon'	=>	$HeadIcon,
			'HeadBg'	=>	$HeadBg,
			'HeadFixed'	=>	$HeadFixed,
			//******************底部管理******************
			'FootFont'	=>	$FootFont,
			'FootBg'	=>	$FootBg,
			'FootNav'	=>	$FootNav
		);
		manage::config_operaction($data, 'mobile');
		manage::operation_log('修改手机版基本设置');
		ly200::e_json('', 1);
	}
}