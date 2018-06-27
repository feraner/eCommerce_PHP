<?php
/**
 * 在线订单操作类，负责核心的wsdl操作请求
 * @package
 * @license
 * @author seaqi
 * @contact 980522557@qq.com / xiayouqiao2008@163.com
 * @version $Id: class.orderonline.php 2011-07-20 15:56:00
 */
class OrderOnline{
	private static $soapClient;
	
	public function __construct($data, $type){
		$fileName='Struct.php';
		isset($GLOBALS[$fileName]) or (($GLOBALS[$fileName] = 1) and require $fileName);
		
		if($type=='OrderOnline'){ //在线订单操作类
			$Url='http://api.4px.com/OrderOnlineService.dll?wsdl';
		}else{ //在线订单操作工具类
			$Url='http://api.4px.com/OrderOnlineToolService.dll?wsdl';
		}

		if(is_null(self::$soapClient) || !is_object(self::$soapClient)){//多次操作有明显效果
			try{
				self::$soapClient=new SoapClient($Url, array(true));//【第一步】
			}catch(Exception $e){
				return array('ack'=>'Failure', 'cnMessage'=>'网络连接故障<br />'.$e->__toString());
			}
		}
	}
	
	private static function common($inputStructMethodName, $customerParameter){
		try{
			$params=call_user_func_array(array('Struct', $inputStructMethodName), $customerParameter);//【第四步】
			$result=self::$soapClient->__soapCall($inputStructMethodName, array($params));
			$arr=Struct::outputStruct($result);
			if(is_array($arr) && !empty($arr)){
				return $arr;
			}else{
				return false;
			}
		}catch(Exception $e){
			return array('ack'=>'Failure', 'cnMessage'=>'方法执行错误<br />'.$e->__toString());
		}
	}
	
	public function __call($inputStructMethodName, $customerParameter){
		if(self::$soapClient){
			try{
				$tmp=self::$soapClient->__getFunctions();//【第二步】
				if(is_array($tmp)){
					foreach($tmp as $theValue){
						$pos=strpos(strtolower($theValue), strtolower($inputStructMethodName));
						if($pos===false){
							continue;
						}else{
							return self::common($inputStructMethodName, $customerParameter);//【第三步】
						}
					}
					//以上没有正常return说明没有找到指定方法
					throw new Exception('当前没有此服务方法，请检查方法名是否有误');
				}else{
					$pos=strpos($tmp, (string)$inputStructMethodName);
					if($pos===false){
						throw new Exception('当前没有此服务方法，请检查方法名是否有误');
					}else{
						return self::common($inputStructMethodName,$customerParameter);
					}
				}
			}catch(Exception $e){
				return array('ack'=>'Failure', 'cnMessage'=>'检查方法时出错：<br />'.$e->__toString());
			}
		}else{
			return array('ack'=>'Failure', 'cnMessage'=>'网络连接故障');
		}
	}
}