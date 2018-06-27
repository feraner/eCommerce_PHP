<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

return array(
	'orders'				=>	'订单',
	'oid'					=>	'订单号',
	'username'				=>	'用户名',
	'time'					=>	'下单时间',
	'source'				=>	'来源',
	'source_ary'			=>	array('PC', '移动'),
	'name'					=>	'姓名',
	'quantity'				=>	'数量',
	'amount'				=>	'小计',
	'orders_status'			=>	'订单状态',
	'discount'				=>	'折扣',
	'user_discount'			=>	'会员折扣',
	'addfee'				=>	'手续费',
	'addfee_affix'			=>	'附加费用',
	'total_price'			=>	'订单总额',
	'payment_method'		=>	'付款方式',
	'account_info'			=>	'账号信息',
	'brief'					=>	'简介',
	'remark'				=>	'备注',
	'remark_log'			=>	'备注日志',
	'operator'				=>	'操作人',
	'explode_all'			=>	'导出所有订单',
	'online'				=>	'线上支付',
	'user'					=>	'用户',
	'member'				=>	'会员',
	'tourists'				=>	'游客',
	'info'					=>	array(
									'order_info'			=>	'订单信息',
									'address_info'			=>	'收货地址',
									'ship_info'				=>	'配送方式',
									'name'					=>	'姓名',
									'address'				=>	'地址',
									'phone'					=>	'手机',
									'weight'				=>	'总重量',
									'volume'				=>	'总体积',
									'unit'					=>	'KG',
									'charges'				=>	'运费',
									'insurance'				=>	'保险费',
									'product_price'			=>	'产品总价',
									'charges_insurance'		=>	'运费及保险费',
									'handing_fee'			=>	'手续费',
									'discount_notes'		=>	'（填写范围：0至100，0为不打折，20等同于8折，100为免费）',
									'coupon'				=>	'优惠券',
									'payment_time'			=>	'付款时间',
									'order_time'			=>	'下单时间',
									'delivery_time'			=>	'发货时间',
									'receipt_time'			=>	'签收时间',
									'cancel_reason'			=>	'取消原因',
									'paypal_address'		=>	'买家Paypal账号的账单地址',
								),
	'status'				=>	array(
									1	=>	'等待支付',//Awaiting Payment
									2	=>	'等待确认支付',//Awaiting Confirm Payment
									3	=>	'支付出错',//Payment Wrong
									4	=>	'等待发货',//Awaiting Shipping
									5	=>	'已发货',//Shipment Shipped
									6	=>	'已完成',//Received
									7	=>	'已取消',//Cancelled
								),
	'address'				=>	array(
									'paypal_account'		=>	'Paypal账号',
									'name'					=>	'姓名',
									'address_line1'			=>	'地址栏1',
									'address_line2'			=>	'地址栏2',
									'city'					=>	'城市',
									'country'				=>	'国家/地区',
									'cpf_cnpj'				=>	'CPF或CNPJ',
									'cpf'					=>	'CPF (个人订单)',
									'cnpj'					=>	'CNPJ (公司订单)',
									'personal_vatid'		=>	'个人或增值税ID',
									'personal'				=>	'个人身份证号码 (个人订单)',
									'vatid'					=>	'增值税ID (公司订单)',
									'state'					=>	'州/省/地区',
									'zip'					=>	'邮政编码',
									'phone'					=>	'电话号码',
									'select'				=>	'请选择---',
									'select_country'		=>	'请选择你的国家',
								),
	'payment'				=>	array(
									'name'					=>	'付款人',
									'money'					=>	'付款金额',
									'mtcn'					=>	'监控号',
									'contents'				=>	'备注内容'
								),
	'shipping'				=>	array(
									'method'				=>	'发货方式',
									'auto_mod_price'		=>	'自动更新运费',
									'insurance'				=>	'购买保险',
									'track_no'				=>	'运单号',
									'ship'					=>	'发货',
									'tracking'				=>	'物流跟踪',
								),
	'product'				=>	array(
									'waybill_qty'			=>	'分单数量',
									'part'					=>	'分单',
									'merge'					=>	'合单'
								),
	'print'					=>	array(
									'type'		=>	array('报关单', '发票')
								),
	'tips'					=>	array(
									'paypal'		=>	'支持各大主流银行和信用卡支付，如：银联/Visa/MasterCard/American Express...',
									'excheckout'	=>	'支持各大主流银行和信用卡支付，如：银联/Visa/MasterCard/American Express...',
									'scoinpay'		=>	'支持各大主流银行和信用卡支付，如：银联/Visa/MasterCard/JCB...',
									'payeasy'		=>	'支持各大主流银行和信用卡支付，如：银联/Visa/MasterCard/JCB/American Express...',
									'dhpay'			=>	'支持各大主流银行和信用卡支付，如：银联/Visa/MasterCard/American Express...',
								),
	'export'				=>	array(
									'import'		=>	'导入快递',
									'export'		=>	'订单导出',
									'save_ok'		=>	'保存成功',
									'proname'		=>	'产品名称',
									'pronumber'		=>	'产品编号',
									'proqty'		=>	'产品数量',
									'prosku'		=>	'产品SKU',
									'proattr'		=>	'产品属性',
									'othdiscount'	=>	'会员优惠及其他优惠',
									'shipinfo'		=>	'收件人信息',
									'shipname'		=>	'收货姓名',
									'shipaddress'	=>	'收货地址',
									'shipaddress2'	=>	'收货地址2',
									'shipcountry'	=>	'收货国家',
									'shipstate'		=>	'收货省份',
									'shipcity'		=>	'收货城市',
									'shipzip'		=>	'收货邮编',
									'shipphone'		=>	'收货电话',
									'billinfo'		=>	'账单信息',
									'billname'		=>	'账单姓名',
									'billaddress'	=>	'账单地址',
									'billaddress2'	=>	'账单地址2',
									'billcountry'	=>	'账单国家',
									'billstate'		=>	'账单省份',
									'billcity'		=>	'账单城市',
									'billzip'		=>	'账单邮编',
									'billphone'		=>	'账单电话',
								)
)
?>