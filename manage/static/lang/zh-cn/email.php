<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
姓名：sheldon
日期: 2014-07-25
备注:分工的语言包开发，上线后该文件统一整合 
*/
return array(
	'send'			=>	'发送',
	'module'		=>	'发送模块',
	'default'		=>	'默认设置',
	'custom_set'	=>	'自定义设置',
	'from_email'	=>	'发件人邮箱',
	'from_name'		=>	'发件人名称',
	'smtp'			=>	'SMTP地址',
	'port'			=>	'SMTP端口',
	'email'			=>	'邮箱帐号',
	'password'		=>	'邮箱密码',
	'mailbox_config'=>	'邮箱设置',
	'customize'		=>	'自定义设置',
	'bottom_content'=>	'底部签名内容',
	'subject'		=>	'主题',
	'addressee'		=>	'收件人',
	'member_group'	=>	'选择会员分组',
	'import_list'	=>	'外部导入名单',
	'import'		=>	'导入列表请选择*.txt格式',
	'send_status'	=>	'发送状态',
	'send_status_ary'=>	array('未发送', '已发送'),
	'send_time'		=>	'发送时间',
	'templates'		=>	'模板',
	'email_tpl'		=>	'选择邮件模板',
	'save_email_tpl'=>	'保存邮件模板',
	'select_tpl'	=>	'请选择邮件模板',
	'edit_success'	=>	'邮件模板编辑成功',
	'isused_tips'	=>	'开启后使用自定义邮件模板，关闭则使用系统默认邮件',
	'email_tpl_class'=>	array(
							'promotions'	=>	'促销模板',
							'festival'		=>	'节日模板',
							'invitation'	=>	'邀请函模板',
							'customize'		=>	'自定义模板'
						),
	'sys_email_tpl'	=>	array(
							'create_account'	=>	'会员注册',
							'forgot_password'	=>	'忘记密码',
							'validate_mail'		=>	'注册验证邮箱',
							'order_create'		=>	'下单',
							'order_payment'		=>	'付款成功',
							'order_shipped'		=>	'订单发货',
							'order_change'		=>	'订单完成',
							'order_cancel'		=>	'订单取消',
						),
	'remark'		=>	'备注: 多个收件人请每行填写一个，并且每个收件人的<span class="fc_red">邮箱地址</span>与<span class="fc_red">姓名</span>用<span class="fc_red">/</span>分隔开
						<br />如: webmaster@ly200.com/liming<br />
						邮件主题或邮件内容可用变量（红色内容）:<br />
						<span class="fc_red">{Email}</span>: 邮箱地址<br />
						<span class="fc_red">{FullName}</span>: 姓名',
	'remark_single'	=>	'格式 如: webmaster@ly200.com/liming<br />
						邮件主题或邮件内容可用变量（红色内容）:<br />
						<span class="fc_red">{Email}</span>: 邮箱地址<br />
						<span class="fc_red">{FullName}</span>: 姓名',
	'send_tips'		=>	'收件人格式有误，未能完成操作，请修改',
	'email_logs'	=>	array(
							'form_email'	=>	'发件人',
							'form_name'		=>	'发件人名称',
							'to_email'		=>	'收件人',
							'subject'		=>	'邮件主题',
							'content'		=>	'邮件内容',
							'status'		=>	'发送状态',
							'status_ary'	=>	array('发送成功', '发送失败')
						),
	'newsletter'	=>	array(
							'status'		=>	'已订阅',
							'submit'		=>	'订阅',
							'cancel'		=>	'取消订阅'
						),
	'notice'		=>	array(
							'notice_config'	=>	'邮件通知',
							'notice_ary'	=>	array(
													'order_payment'	=>	'付款成功',
													'order_shipped'	=>	'订单发货',
													'order_change'	=>	'订单完成',
													'order_cancel'	=>	'订单取消',
													'create_account'=>	'会员注册',
													'forgot_password'=>	'忘记密码',
													'order_create'	=>	'下单'
												)
						),
	'sys_remark'	=>	'<strong class="fc_gory">全局变量，所有模板可用：</strong><br />
						<span class="fc_red">{Logo}</span>: 网站Logo图片<br />
						<span class="fc_red">{Domain}</span>: 网站域名，不包含http或https<br />
						<span class="fc_red">{FullDomain}</span>: 网站完整域名，包含http或https，适用于超链接URL设置<br />
						<span class="fc_red">{Time}</span>: 邮件发送时间<br />
						<span class="fc_red">{UserName}</span>: 客户姓名<br />
						<span class="fc_red">{Email}</span>: 客户邮箱<br />
						<span class="fc_red">{Password}</span>: 客户密码<br />
						<strong class="fc_gory">订单模板变量：</strong><br />
						<span class="fc_red">{OrderNum}</span>: 订单号<br />
						<span class="fc_red">{OrderDetail}</span>: 订单详情表格，包含订单价格，产品等信息<br />
						<span class="fc_red">{OrderUrl}</span>: 查看订单链接，适用于超链接URL设置<br />
						<span class="fc_red">{OrderStatus}</span>: 订单状态<br />
						<span class="fc_red">{OrderPrice}</span>: 订单价格<br />
						<div class="tpl_tips order_create">
							<strong class="fc_gory">创建订单模板：</strong><br />
							<span class="fc_red">{OrderPaymentUrl}</span>: 订单支付链接，适用于超链接URL设置<br />
							<span class="fc_red">{OrderPaymentName}</span>: 订单付款方式<br />
						</div>
						<div class="tpl_tips order_shipped">
							<strong class="fc_gory">订单发货模板：</strong><br />
							<span class="fc_red">{ShippingName}</span>: 快递名称<br />
							<span class="fc_red">{ShippingBrief}</span>: 快递简介<br />
							<span class="fc_red">{TrackingNumber}</span>: 运单号<br />
							<span class="fc_red">{ShippingTime}</span>: 发货时间<br />
							<span class="fc_red">{QueryUrl}</span>: 运单查询链接<br />
						</div>
						<div class="tpl_tips validate_mail">
							<strong class="fc_gory">注册验证邮箱模板：</strong><br />
							<span class="fc_red">{VerUrl}</span>: 验证邮箱链接，适用于超链接URL设置<br />
						</div>
						<div class="tpl_tips forgot_password">
							<strong class="fc_gory">忘记密码模板：</strong><br />
							<span class="fc_red">{ForgotUrl}</span>: 忘记密码链接，适用于超链接URL设置<br />
						</div>',
);