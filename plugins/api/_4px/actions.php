<?
/**
 * 这是一个很牛逼的插件实现
 * 
 * @package     payment
 * @subpackage  _4px(4PX)
 * @category    api
 * @author      鄙人
 * @link        http://www.ueeshop.com/
 */
/**
 * 需要注意的几个默认规则：
 * 1. 本插件类的文件名必须是action
 * 2. 插件类的名称必须是{插件名_actions}
 */
class _4px_actions 
{ 
    //解析函数的参数是pluginManager的引用 
    function __construct(&$pluginManager){
        //注册这个插件 
        //第一个参数是钩子的名称 
        //第二个参数是pluginManager的引用 
        //第三个是插件所执行的方法 
        $pluginManager->register('_4px', $this, '__config'); 
        $pluginManager->register('_4px', $this, 'create_order');
		$pluginManager->register('_4px', $this, 'pre_alert_order');
		$pluginManager->register('_4px', $this, 'cargo_tracking');
		$pluginManager->register('_4px', $this, 'find_tracking_number');
		$pluginManager->register('_4px', $this, 'charge_calculate');
    }
	
	function __config($data){
		return @in_array($data, array('create_order', 'pre_alert_order', 'cargo_tracking', 'find_tracking_number', 'charge_calculate'))?'enable':'';
	}
     
    function create_order($data){
		//创建订单
		global $c;
		$orders_row=db::get_one('orders', "OrderId='{$data['OrderId']}'");
		if($orders_row){
			if(class_exists('OrderOnline')!=true){//防止重复调用
				include('config/OrderOnline.php');
			}
			$Obj=new OrderOnline($data, 'OrderOnline');
			$Acronym=db::get_value('country', "Country='{$orders_row['ShippingCountry']}'", 'Acronym');
			$order_ary=array(
				'buyerId'				=> $orders_row['Email'],//买家ID
				'cargoCode'				=> 'P',//货物类型(默认：P)，参照货物类型表
				'city'					=> $orders_row['ShippingCity'],//城市
				'consigneeCompanyName'	=> $orders_row['ShippingFirstName'],//收件人公司名称
				'consigneeEmail'		=> $orders_row['Email'],//收件人Email 
				'consigneeFax'			=> '',//收件人传真号码
				'consigneeName'			=> $orders_row['ShippingFirstName'].' '.$orders_row['ShippingLastName'],//收件人公司名称姓名
				'consigneePostCode' 	=> $orders_row['ShippingZipCode'],//收件人邮编
				'consigneeTelephone'	=> $orders_row['ShippingCountryCode'].'-'.$orders_row['ShippingPhoneNumber'],//收件人电话号码
				'customerWeight'		=> $orders_row['TotalWeight'],//客户自己称的重量(单位：KG) 
				'destinationCountryCode'=> $Acronym,//目的国家二字代码，参照国家代码表
				'initialCountryCode'	=> 'CN',//起运国家二字代码，参照国家代码表
				'insurType'				=> '',//保险类型，参照保险类型表
				'insurValue'			=> 0,//保险价值(单位：USD)0 < Amount <= [10,2] 
				'orderNo'				=> $orders_row['OId'],//客户订单号码，由客户自己定义
				'orderNote'				=> $orders_row['Remarks'],//订单备注信息 
				'paymentCode'			=> 'P',//付款类型(默认：P)，参照付款类型表
				'pieces'				=> '1',//货物件数(默认：1) 0 < Amount <= [10,2] 
				'productCode'			=> 'B1',//产品代码，指DHL、新加坡小包挂号、联邮通挂号等，参照产品代码表
				'returnSign'			=> 'N',//小包退件标识 Y: 发件人要求退回 N: 无须退回(默认) 
				'shipperAddress'		=> '',//发件人地址 
				'shipperCompanyName'	=> '',//发件人公司名称 
				'shipperFax'			=> '',//发件人传真号码 
				'shipperName'			=> '',//发件人姓名 
				'shipperPostCode'		=> '',//发件人邮编
				'shipperTelephone'		=> '',//发件人电话号码
				'stateOrProvince'		=> $orders_row['ShippingState'],//州、省
				'street'				=> $orders_row['ShippingAddressLine2']?$orders_row['ShippingAddressLine2']:$orders_row['ShippingAddressLine1'],//街道
				'trackingNumber'		=> '',//服务商跟踪号码【无效时系统自动分配】
				'transactionId'			=> '',//交易ID 
				'declareInvoice'		=> array()
			);
			$pro_ary=array();
			$pro_list_row=db::get_all('orders_products_list', "OrderId='{$orders_row['OrderId']}'");
			foreach($pro_list_row as $k=>$v){//获取订单产品的信息
				$pro_ary[]=array(
					'declareNote'		=> $v['Remark'],//配货备注
					'declarePieces'		=> $v['Qty'],//件数(默认: 1)
					'declareUnitCode'	=> 'PCE',//申报单位类型代码(默认:PCE)，参照申报单位类型代码表
					'eName'				=> $v['Name'],//海关申报英文品名
					'name'				=> $v['Name'],//海关申报中文品名
					'unitPrice'			=> ($v['Price']+$v['PropertyPrice'])*($v['Discount']<100?$v['Discount']/100:1),//单价 0 < Amount <= [10,2]
				);
			}
			$order_ary['declareInvoice']=$pro_ary;
			$result=$Obj->createOrderService($data['account']['AUTHTOKEN'], $order_ary);
			$return=array();
			if($ack=='Success'){ //返回成功
				$return['referenceNumber']	= $result['referenceNumber'];//引用单号，一般为客户单号
				$return['timestamp'] 		= $result['timestamp'];//服务器响应时间
				$return['trackingNumber']	= $result['trackingNumber'];//服务商跟踪号码
				$return['trackingNumber'] && db::update('orders', "OId='{$orders_row['OId']}'", array('TrackingNumber'=>$return['trackingNumber']));//更新订单运单号信息
			}else{ //返回失败 
				$return['cnMessage']	= $result['cnMessage'];//错误详细内容中文描述
				$return['cnAction']		= $result['cnAction'];//错误的处理方法中文描述
				$return['code'] 		= $result['code'];//错误代码
				$return['defineMessage']= $result['defineMessage'];//错误信息补充说明
				$return['enMessage']	= $result['enMessage'];//错误详细内容英文描述
				$return['enAction']		= $result['enAction'];//错误的处理方法英文描述
				$return['timestamp']	= $result['timestamp'];//服务器响应时间
			}
			$Obj='';
			ob_start();
			print_r($data);
			print_r($order_ary);
			print_r($result);
			$log=ob_get_contents();
			ob_end_clean();
			file::write_file('/logs/4px/create_order/'.date('Y_m/', $c['time']), "{$orders_row['OId']}.txt", $log);//把返回数据写入文件
		}
    }
	
	function pre_alert_order($data){
		//申请运单发货
		global $c;
		$orders_row=db::get_one('orders', "OId='{$data['OId']}'");
		if($orders_row){
			if(class_exists('OrderOnline')!=true){//防止重复调用
				include('config/OrderOnline.php');
			}
			$Obj=new OrderOnline($data, 'OrderOnline');
			$order_ary=array($data['OId']);
			$result=$Obj->preAlertOrderService($data['account']['AUTHTOKEN'], $order_ary);
			$return=array();
			if($ack=='Success'){ //返回成功
				$return['referenceNumber']	= $result['referenceNumber'];//引用单号，一般为客户单号
				$return['timestamp'] 		= $result['timestamp'];//服务器响应时间
				$return['trackingNumber']	= $result['trackingNumber'];//服务商跟踪号码
			}else{ //返回失败 
				$return['cnMessage']	= $result['cnMessage'];//错误详细内容中文描述
				$return['cnAction']		= $result['cnAction'];//错误的处理方法中文描述
				$return['code'] 		= $result['code'];//错误代码
				$return['defineMessage']= $result['defineMessage'];//错误信息补充说明
				$return['enMessage']	= $result['enMessage'];//错误详细内容英文描述
				$return['enAction']		= $result['enAction'];//错误的处理方法英文描述
				$return['timestamp']	= $result['timestamp'];//服务器响应时间
			}
			$Obj='';
			ob_start();
			print_r($data);
			print_r($order_ary);
			print_r($result);
			$log=ob_get_contents();
			ob_end_clean();
			file::write_file('/logs/4px/pre_alert_order/'.date('Y_m/', $c['time']), "{$data['OId']}.txt", $log);//把返回数据写入文件
		}
	}
	
	function cargo_tracking($data){
		//查询轨迹
		global $c;
		$orders_row=db::get_one('orders', "OrderId='{$data['OrderId']}'");
		if($orders_row){
			if(class_exists('OrderOnline')!=true){//防止重复调用
				include('config/OrderOnline.php');
			}
			$Obj=new OrderOnline($data, 'OrderOnlineTool');
			$order_ary=array($orders_row['OId']);
			//$order_ary=array('LS542113934CN'); 测试号
			$result=$Obj->cargoTrackingService($data['account']['AUTHTOKEN'], $order_ary);
			$Obj='';
			$return=array();
			if($result['ack']=='Success'){ //返回成功
				foreach($result['createDate'] as $k=>$v){
					$return[$k]['Address']=$result['occurAddress'][$k];//轨迹发生地点
					$return[$k]['Date']=$result['occurDate'][$k];//轨迹发生时间
					$return[$k]['Content']=$result['trackContent'][$k];//轨迹状态描述补充
				}
				return str::json_data($return);
			}else{
				return false;
			}
		}
	}
	
	function find_tracking_number($data){
		//查询跟踪单号
		global $c;
		$orders_row=db::get_one('orders', "OrderId='{$data['OrderId']}'");
		if($orders_row){
			if(class_exists('OrderOnline')!=true){//防止重复调用
				include('config/OrderOnline.php');
			}
			$Obj=new OrderOnline($data, 'OrderOnlineTool');
			$order_ary=array($orders_row['OId']);
			//$order_ary=array('112-2129841-2598622'); 测试号
			$result=$Obj->findTrackingNumberService($data['account']['AUTHTOKEN'], $order_ary);
			$Obj='';
			/*$return=array();
			if($ack=='Success'){ //返回成功
				$return['referenceNumber']	= $result['referenceNumber'];//引用单号，一般为客户单号
				$return['timestamp'] 		= $result['timestamp'];//服务器响应时间
				$return['trackingNumber']	= $result['trackingNumber'];//服务商跟踪号码
			}else{ //返回失败 
				$return['cnMessage']	= $result['cnMessage'];//错误详细内容中文描述
				$return['cnAction']		= $result['cnAction'];//错误的处理方法中文描述
				$return['code'] 		= $result['code'];//错误代码
				$return['defineMessage']= $result['defineMessage'];//错误信息补充说明
				$return['enMessage']	= $result['enMessage'];//错误详细内容英文描述
				$return['enAction']		= $result['enAction'];//错误的处理方法英文描述
				$return['timestamp']	= $result['timestamp'];//服务器响应时间
			}*/
		}
	}
	
	function charge_calculate($data){
		//查询运费计算
		global $c;
		$return=array();
		$Weight=(float)$data['Weight'];
		if($Weight>0){
			if(class_exists('OrderOnline')!=true){//防止重复调用
				include('config/OrderOnline.php');
			}
			$Obj=new OrderOnline($data, 'OrderOnlineTool');
			$data_ary=array(
				'startShipmentId'	=> $data['account']['startShipmentId'],//起运地 ID，参照起运地 ID 表
				'countryCode'		=> $data['Acronym'],//目的国家二字代码，参照国家代码表
				'weight'			=> $Weight,//计费重量，单位(kg)
				'length'			=> '',//长度(计算体积重使用)
				'width'				=> '',//宽度(计算体积重使用)
				'height'			=> '',//高度(计算体积重使用)
				'cargoCode'			=> 'P',//货物类型(默认：P)
				'displayOrder'		=> 1,//计费结果产品显示级别(默认：1)
				'productCode'		=> $data['account']['productCode'],//产品代码组,该属性不为空，只返回该产品组计费结果，参照产品代码表
				'postCode'			=> $data['PostCode'],//邮编
			);
			$result=$Obj->chargeCalculateService($data['account']['AUTHTOKEN'], $data_ary);
			$Obj='';
			/*
			ack 返回状态[Success:成功, Failure:失败]
			返回成功参数：
				currencyCode 币种
				deliveryperiod 递送时间
				freightAmount 运费金额
				freightRmbAmount 人民币运费金额
				incidentalAmount 杂费金额
				incidentalRmbAmount 人民币杂费金额
				productCName 产品中文名称
				productCode 产品代码
				productEName 产品英文名称
				totalAmount 总费用金额
				totalRmbAmount 人民币总费用
				tracking 可跟踪
				volumn 按体积重量计费
				note 备注
				freightOilAmount 燃油附加费金额
				freightOilRmbAmount 人民币燃油附加费金额
			返回失败参数：
				cnMessage 错误详细内容中文描述
				cnAction 错误的处理方法中文描述
				code 错误代码
				defineMessage 错误信息补充说明
				enMessage 错误详细内容英文描述
				enAction 错误的处理方法英文描述
				timestamp 服务器响应时间
			*/
			if($result){
				$return=$result;
			}
		}
		return str::json_data($return);
	}
}
?>