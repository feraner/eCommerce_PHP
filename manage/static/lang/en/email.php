<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
姓名：sheldon
日期: 2014-07-25
备注:分工的语言包开发，上线后该文件统一整合 
*/
return array(
	'send'			=>	'Send ',
	'module'		=>	'Sending module',
	'default'		=>	'Default settings',
	'custom_set'	=>	'Custom settings',
	'from_email'	=>	'Sender’s address',
	'from_name'		=>	'Sender’s name',
	'smtp'			=>	'SMTP address',
	'port'			=>	'SMTP port',
	'email'			=>	'Mail account',
	'password'		=>	'Mail password',
	'mailbox_config'=>	'Mailbox config',
	'customize'		=>	'Customize config',
	'bottom_content'=>	'Content bottom',
	'subject'		=>	'Subject',
	'addressee'		=>	'Receiver',
	'member_group'	=>	'Select members grouping',
	'import_list'	=>	'Import the name list',
	'import'		=>	'Please select *.txt format when importing the list',
	'send_status'	=>	'Send status',
	'send_status_ary'=>	array('No', 'Yes'),
	'send_time'		=>	'Send time',
	'templates'		=>	'Template',
	'email_tpl'		=>	'Select mail template',
	'save_email_tpl'=>	'Save mail template',
	'select_tpl'	=>	'Please select the mail template',
	'edit_success'	=>	'Mail template edit successful',
	'isused_tips'	=>	'After you open a custom mail template, use the system default message when closed',
	'email_tpl_class'=>	array(
							'promotions'	=>	'Promotion template',
							'festival'		=>	'Holiday template',
							'invitation'	=>	'Invitation letter template',
							'customize'		=>	'Customize template'
						),
	'sys_email_tpl'	=>	array(
							'create_account'	=>	'Registered member',
							'forgot_password'	=>	'Forget password',
							'validate_mail'		=>	'Register Verification Mailbox',
							'order_create'		=>	'New order',
							'order_payment'		=>	'Order payment is successful',
							'order_shipped'		=>	'Order delivery',
							'order_change'		=>	'Order status changes',
							'order_cancel'		=>	'Cancel the order',
						),
	'remark'		=>	'Remarks: for multiple addressees, please fill in one on each line and separate the <span class="fc_red">email address</span> and <span class="fc_red">name</span> of each addressee with <span class="fc_red">/</span>. Such as: webmaster@ly200.com/liming
						<br />Such as: webmaster@ly200.com/liming<br />
						Variables (content in red) can be used for the subject or content of emails:<br />
						<span class="fc_red">{Email}</span>: Email address<br />
						<span class="fc_red">{FullName}</span>: Name',
	'remark_single'	=>	'Formats Such as: webmaster@ly200.com/liming<br />
						Variables (content in red) can be used for the subject or content of emails:<br />
						<span class="fc_red">{Email}</span>: Email address<br />
						<span class="fc_red">{FullName}</span>: Name',
	'send_tips'		=>	'The format of the addressee is wrong; operation failed, please modify',
	'email_logs'	=>	array(
							'form_email'	=>	'Sender',
							'form_name'		=>	'Sender Name',
							'to_email'		=>	'Addressee',
							'subject'		=>	'Email Subject',
							'content'		=>	'Email Content',
							'status'		=>	'Send Status',
							'status_ary'	=>	array('Send Success', 'Send Failure')
						),
	'newsletter'	=>	array(
							'status'		=>	'Already subscribed',
							'submit'		=>	'Subscription',
							'cancel'		=>	'Unsubscribe'
						),
	'notice'		=>	array(
							'notice_config'	=>	'Email Notice',
							'notice_ary'	=>	array(
													'order_payment'	=>	'Payment successful',
													'order_shipped'	=>	'Order delivery',
													'order_change'	=>	'Order completed',
													'order_cancel'	=>	'Order cancel',
													'create_account'=>	'Member registration',
													'forgot_password'=>	'Forgot password',
													'order_create'	=>	'Place an order'
												)
						),
	'sys_remark'	=>	'<strong class="fc_gory">Global variable, all templates are available：</strong><br />
						<span class="fc_red">{Logo}</span>: Website Logo<br />
						<span class="fc_red">{Domain}</span>: The domain name of the site does not contain http or https<br />
						<span class="fc_red">{FullDomain}</span>: The full domain name of the site, including http or https, applies to the hyperlink URL settings<br />
						<span class="fc_red">{Time}</span>: Mail delivery time<br />
						<span class="fc_red">{UserName}</span>: Customer Name<br />
						<span class="fc_red">{Email}</span>: Customer mailbox<br />
						<span class="fc_red">{Password}</span>: Customer password<br />
						<strong class="fc_gory">Order template variable：</strong><br />
						<span class="fc_red">{OrderNum}</span>: Order number<br />
						<span class="fc_red">{OrderDetail}</span>: Order details form, including order price, product and other information<br />
						<span class="fc_red">{OrderUrl}</span>: View the order link for the hyperlink URL settings<br />
						<span class="fc_red">{OrderStatus}</span>: Order Status<br />
						<span class="fc_red">{OrderPrice}</span>: Order price<br />
						<div class="tpl_tips order_create">
							<strong class="fc_gory">Create an order template：</strong><br />
							<span class="fc_red">{OrderPaymentUrl}</span>: Order payment link for hyperlink URL settings<br />
							<span class="fc_red">{OrderPaymentName}</span>: Order payment method<br />
						</div>
						<div class="tpl_tips order_shipped">
							<strong class="fc_gory">Order shipping template：</strong><br />
							<span class="fc_red">{ShippingName}</span>: Express name<br />
							<span class="fc_red">{ShippingBrief}</span>: Express profile<br />
							<span class="fc_red">{TrackingNumber}</span>: Waybill number<br />
							<span class="fc_red">{ShippingTime}</span>: Delivery time<br />
							<span class="fc_red">{QueryUrl}</span>: Waybill query link<br />
						</div>
						<div class="tpl_tips validate_mail">
							<strong class="fc_gory">Register to verify the mailbox template：</strong><br />
							<span class="fc_red">{VerUrl}</span>: Verify the Mailbox link for the hyperlink URL settings<br />
						</div>
						<div class="tpl_tips forgot_password">
							<strong class="fc_gory">Forget the password template：</strong><br />
							<span class="fc_red">{ForgotUrl}</span>: Forgot the password link for the hyperlink URL setting<br />
						</div>',
);