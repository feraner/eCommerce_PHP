<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
姓名：sheldon
日期: 2014-07-25
备注:分工的语言包开发，上线后该文件统一整合 
*/
return array(
	'url'		=>	'链接地址',
	'code'		=>	'代码内容',
	'page'		=>	'页面名称',
	'info'		=>	array(
						'link'		=>	'内部链接',
						'meta'		=>	'页面标题与标签',
						'last_gTime'=>	'上次生成时间',
						'generate'	=>	'点击生成'
					),
	'link'		=>	array(
						'link_notes'		=>	'用于网站的热门关键词、产品详细介绍里的关键词链接。链接地址可以是网站内部的链接，也可以是其他网站的外部链接'
					),
	'meta'		=>	array(
						'home'				=>	'首页',
						'tuan'				=>	'团购页',
						'seckill'			=>	'抢购页',
						'new'				=>	'新品页',
						'hot'				=>	'热卖页',
						'best_deals'		=>	'畅销页',
						'special_offer'		=>	'特价页',
						'article'			=>	'单页内容页',
						'info_category'		=>	'文章类目页',
						'info'				=>	'文章内容页',
						'products_category'	=>	'产品类目页',
						'products'			=>	'产品详细页',
						'blog'				=>	'博客页'
					),
	'third'		=>	array(
						'is_meta'			=>	'Meta代码',
						'meta_notes'		=>	'默认为关闭，代码添加在<font>&lt;</font>body<font>&gt;</font>标签之间，<font>&lt;</font>/body<font>&gt;</font>标签之前；开启后此代码设置在<font>&lt;</font>head<font>&gt;</font>标签之间，<font>&lt;</font>/head<font>&gt;</font>之前',
						'used_notes'		=>	'默认为开启，取消开启后此代码不作用',
						'code_type'			=>	'类型',
						'code_type_ary'		=>	array('PC与手机', 'PC', '手机')
					)
)
?>