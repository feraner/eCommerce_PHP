<?php !isset($c) && exit();?>
<?php
class themes_set{
	public static function config_edit_init(){
		global $c;
		$ary=array(
			'Effects'		=>	1, //特效方式
			'HeaderContent'	=>	1, //头部内容
			'IndexContent'	=>	0, //首页内容
			'TopMenu'		=>	0, //顶部栏目
			'ContactMenu'	=>	0, //联系我们
			'ShareMenu'		=>	1, //分享栏目
			'IndexTitle'	=>	0, //首页标题
		);
		return $ary;
	}
	
	public static function themes_products_list_reset(){
		return '{"IsColumn":1,"Narrow":1,"IsLeftbar":{"Category":1,"Hot":1,"Special":1,"Banner":1},"Order":"row_4","OrderNumber":40}';
	}
	
	public static function themes_style_reset(){
		return '{"FontColor":"#9ABE14","NavBgColor":"#005AB0","NavHoverBgColor":"#004D93","NavBorderColor1":"#02468D","NavBorderColor2":"#357CBE","CategoryBgColor":"#063F74","PriceColor":"#005AB0","AddtoCartBgColor":"#9ABE14","BuyNowBgColor":"#F28810","ReviewBgColor":"#F28810","DiscountBgColor":"#FE8A27"}';
	}
}
?>