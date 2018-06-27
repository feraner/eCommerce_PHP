<?php !isset($c) && exit();?>
<?php
class themes_set{
	public static function config_edit_init(){
		global $c;
		$ary=array(
			'Effects'		=>	0, //特效方式
			'HeaderContent'	=>	0, //头部内容
			'IndexContent'	=>	0, //首页内容
			'TopMenu'		=>	0, //顶部栏目
			'ContactMenu'	=>	1, //联系我们
			'ShareMenu'		=>	1, //分享栏目
			'IndexTitle'	=>	1, //首页标题
		);
		return $ary;
	}
	
	public static function themes_products_list_reset(){
		return '{"IsColumn":"1","Narrow":"2","IsLeftbar":{"Category":"1","Special":"1","Hot":"1","Seckill":"1","Banner":"1"},"Order":"row_1","OrderNumber":"5","Effects":"0"}';
	}
	
	public static function themes_style_reset(){
		return '{"FontColor":"#00C17E","NavBgColor":"#00935F","NavHoverBgColor":"#01A46A","NavBorderColor1":"#00935F","CategoryBgColor":"#00935F","PriceColor":"#00935F","AddtoCartBgColor":"#00935F","BuyNowBgColor":"#00C17E","ReviewBgColor":"#00935F","DiscountBgColor":"#00935F","ProListBgColor":"#00935F","ProListHoverBgColor":"#01A46A","GoodBorderColor":"#CCCCCC","GoodBorderHoverColor":"#00C17E"}';
	}
}
?>