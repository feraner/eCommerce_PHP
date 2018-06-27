<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/
return array(
	'pc'	=>	array(
					'account'	=>	array('index'),
					'set'		=>	array(
										'config'	=>	'',
										'themes'	=>	array(
															'menu'		=>	array('products_list', 'products_detail', 'nav', 'footer_nav', 'style'),
															'permit'	=>	array(
																					'nav' => array('add', 'edit', 'del'),
																					'footer_nav' => array('add', 'edit', 'del')
																			)
														),
										'orders_print'=>	'',
										'exchange'	=>	array('permit'	=>	array('add', 'edit', 'del')),
										'oauth' 	=>	array('permit'	=>	array('edit')),
										'payment'	=>	array('permit'	=>	array('edit')),
										'country'	=>	array('permit'	=>	array('add', 'edit', 'del')),
										'photo'		=>	array(
															'menu'		=>	array('category', 'photo'),
															'permit'	=>	array(
																				'category' => array('add', 'edit', 'del'),
																				'photo' => array('add', 'edit', 'del')
																			)
														),
										'shipping'	=>	array(
															'menu'		=>	array('express', 'insurance', 'overseas'),
															'permit'	=>	array(
																				'express' => array('add', 'edit', 'del'),
																				'insurance' => array('add', 'edit', 'del'),
																				'overseas' => array('add', 'edit', 'del')
																			)
														),
										'chat'		=>	array(
															'menu'		=>	array('set', 'chat'),
															'permit'	=>	array(
																				'chat' => array('add', 'edit', 'del')
																			)
														),
										'authorization'	=>	array(
															'menu'		=>	array('open', 'aliexpress', 'amazon'),
															'permit'	=>	array(
																				'open'			=>	array('edit', 'del'),
																				'aliexpress'	=>	array('add', 'edit', 'del'),
																				'amazon'		=>	array('add', 'edit', 'del')
																			)
														)
									),
					'content'	=>	array(
										'page'		=>	array(
															'menu'		=>	array('category', 'page'),
															'permit'	=>	array(
																				'category' => array('add', 'edit', 'del'),
																				'page' => array('add', 'edit', 'del')
																			)
														),
										'news'		=>	array(
															'menu'		=>	array('category', 'news'),
															'permit'	=>	array(
																				'category' => array('add', 'edit', 'del'),
																				'news' => array('add', 'edit', 'del')
																			)
														),
										'set'		=>	'',
										'ad'		=>	'',
										'partner'	=>	array('permit'	=>	array('add', 'edit', 'del'))
									),
					'products'	=>	array(
										'products'	=>	array('permit'	=>	array('add', 'edit', 'copy', 'del', 'export')),
										'category'	=>	array('permit'	=>	array('add', 'edit', 'del')),
										'tags'		=>	array('permit'	=>	array('add', 'edit', 'del')),
										'model'		=>	array(
															'menu'		=>	array('category', 'model'),
															'permit'	=>	array(
																				'category' => array('add', 'edit', 'del'),
																				'model' => array('add', 'edit', 'copy', 'del')
																			)
														),
										'upload_new'=>	'',
										'watermark'	=>	'',
										'set'		=>	'',
										'review'	=>	array('permit'	=>	array('edit', 'del')),
										'business'	=>	array(
															'menu'		=>	array('category', 'business'),
															'permit'	=>	array(
																				'category' => array('add', 'edit', 'del'),
																				'business' => array('add', 'edit', 'del')
																			)
														),
										'sync'		=>	array('permit'	=>	array('add', 'edit', 'del')),
									),
					'orders'	=>	array(
										'orders'	=>	array('permit'	=>	array('edit', 'del')),
										'waybill'	=>	array('permit'	=>	array('edit')),
										'import'	=>	'',
										'export'	=>	''
									),
					'user'		=>	array(
										'user'		=>	array('permit'	=>	array('add', 'edit', 'del', 'export')),
										'level'		=>	array('permit'	=>	array('add', 'edit', 'del')),
										'reg_set'	=>	array('permit'	=>	array('add', 'edit', 'del')),
										'inbox'		=>	array(
															'menu'		=>	array('products', 'orders', 'others'),
															'permit'	=>	array(
																				'products'	=> array('edit', 'del', 'export'),
																				'orders'	=> array('edit', 'del', 'export'),
																				'others'	=> array('add', 'edit', 'del', 'export')
																			)
														),
										'message'	=>	array('permit'	=>	array('add', 'edit', 'del'))
														
									),
					'sales'		=>	array(
										'sales'		=>	array('permit'	=>	array('add', 'edit', 'del')),
										'seckill'	=>	array('permit'	=>	array('add', 'edit', 'del')),
										'tuan'		=>	array('permit'	=>	array('add', 'edit', 'del')),
										'package'	=>	array('permit'	=>	array('add', 'edit', 'del')),
										'promotion'	=>	array('permit'	=>	array('add', 'edit', 'del')),
										'coupon'	=>	array('permit'	=>	array('add', 'edit', 'del', 'export')),
										'discount'	=>	'',
										'holiday'	=>	''
									),
					'extend'	=>	array(
										'seo'			=>	array(
															'menu'		=>	array('meta', 'third', 'sitemap'),
															'permit'	=>	array(
																				'meta' => array('edit'),
																				'third' => array('add', 'edit', 'del')
																			)
															),
										'blog'			=>	array(
															'menu'		=>	array('set', 'blog', 'category', 'review'),
															'permit'	=>	array(
																				'blog' => array('add', 'edit', 'del'),
																				'category' => array('add', 'edit', 'del'),
																				'review' => array('edit', 'del')
																			)
															),
										'search'		=>	array('permit'	=>	array('add', 'edit', 'del')),
										'translate'		=>	'',
										'search_logs'	=>	''
									)
				),
	'email'	=>	array(
					'email'		=>	array(
										'send'		=>	'',
										'config'	=>	'',
										'system'	=>	'',
										'email_logs'=>	'',
										'newsletter'=>	array('permit'	=>	array('edit', 'del')),
										'arrival'	=>	array('permit'	=>	array('del'))
									)
				),
	'mta'	=>	array(
					'mta'		=>	array(
										'visits'			=>	'',
										'visits_referrer'	=>	'',
										'visits_conversion'	=>	'',
										'visits_country'	=>	'',
										'orders'			=>	'',
										'orders_repurchase'	=>	'',
										'products_sales'	=>	'',
										'user'				=>	'',
									)
				),
	'mobile'=>	array(
					'mobile'	=>	array(
										'themes'	=>	'',
										'list'		=>	'',
										'config'	=>	''
									),
					'mpreview'	=>	''
				),
	'manage'	=>	array(
					'manage'	=>	array(
										'manage'	=>	array('permit'	=>	array('add', 'edit', 'del')),
										'manage_logs'=>	''
									)
				)
);
