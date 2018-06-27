<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
姓名：sheldon
日期: 2014-07-25
备注:分工的语言包开发，上线后该文件统一整合 
*/
return array(
	'info'		=>	array(
						'order_price'		=>	'Order Price',
						'insurance_price'	=>	'Insurance Charges',
						'api'				=>	'Press %API% API',
					),
	'shipping'	=>	array(
						'name'				=>	'Name ',
						'express'			=>	'Express Company',
						'used'				=>	'Start Using',
						'area'				=>	'Areas',
						'freight'			=>	'Freight Charges',
						'logo'				=>	'Logo',
						'brief'				=>	'Brief Introduction',
						'is_api'			=>	'Use API',
						'weight'			=>	'Weight',
						'max_weight'		=>	'The maximum weight limit',
						'limit'				=>	'Weight limit',
						'weightarea'		=>	'Weight Range',
						'weightarea_type'	=>	array('Press per KG', 'Press full price'),
						'startweight'		=>	'Starting Weight of the Area',
						'weightmix'			=>	'Weight Mixing',
						'ext_weightarea'	=>	'Continued Weight Range',
						'qty'				=>	'Quantity',
						'special'			=>	'Air / Ocean',
						'volume_limit'		=>	'Volume limit',
						'node'				=>	'Node',
						'unit'				=>	'KG',
						'volumearea'		=>	'Volume Range',
						'calculation'		=>	'Compute Mode',
						'first_weight'		=>	'The First Weight',
						'ext_weight'		=>	'Added Weight',
						'set'				=>	'Freight Charges Settings',
						'air'				=>	'Air Transportation',
						'ocean'				=>	'Maritime Transportation',
						'weight_tips'		=>	'Shipping weight ≥ %input, calculate freight charges by %name.',
						'volume_tips'		=>	'Shipping volume ≥ %input, calculate freight charges by %name.',
						'volume_unit'		=>	'Cubic Meter',
						'insurance'			=>	'Insurance Range',
						'over'				=>	'Above',
						'isair_notes'		=>	'Start using this freight calculation formula.',
						'used_notes'		=>	'The default is “on”; this option of express shall not be shown on the website after turning off.',
						'brief_notes'		=>	'Describe this express to let customer know it.',
						'limit_notes'		=>	'Set the corresponding weight value to limit, in order to achieve courier within the limits before they can be used.',
						'volume_limit_notes'=>	'Set the corresponding volume value to limit, in order to achieve courier within the limits before they can be used.',
						'weight_area_0'		=>	'Set the standards of the first weight and added weight; start to calculate charges of added weight when exceeding fees of the first weight, and the specific expenses can be set in Areas Setting.',
						'weight_area_1'		=>	'Set node in different weight range, and the specific expenses can be set in Areas Setting.',
						'weight_area_2'		=>	'Simultaneously use two weight calculation modes, give priority to conditions of weight range, that is, stop calculating added weight when weight conditions of the range is satisfied.',
						'weight_area_3'		=>	'Set the quantity of the first and the weight to be calculated by the number, and calculate the cost within the specified quantity',
						'weight_area_4'		=>	'Both weight intervals and volume interval calculations are used, and the weight interval is preferred if both are allowed',
						'query'				=>	'Express a single query'
					),
	'area'		=>	array(
						'area'				=>	'Areas',
						'setcountry'		=>	'Country Settings',
						'setcountry_to'		=>	'Country Settings',
						'choice'			=>	'Select Country',
						'added'				=>	'Added Country',
						'countcountry'		=>	'%d countries have been added',
						'position'			=>	'Position',
						'freeshipping'		=>	'Free Freight Amount',
						'free_notes'		=>	'After turning on, if order amount exceeds this figure, freight of this order is free',
						'free_weight_notes'	=>	'After turning on, if order weight less than this figure, freight of this order is free',
						'additional'		=>	'Additional Charges',
						'ships_from'		=>	'Ships from',
						'area_list'			=>	'Area list',
						'area_add'			=>	'Add area'
					),
);