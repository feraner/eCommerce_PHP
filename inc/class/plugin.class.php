<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

/** 
* STBLOG PluginManager Class 
* 
* 插件机制的实现核心类 
* 
* @package        STBLOG 
* @subpackage    Libraries 
* @category    Libraries 
* @author        Saturn 
*    @link http://www.cnsaturn.com/ 
*/ 
class plugin{ 
	/** 
	 * 监听已注册的插件 
	 * 
	 * @access private 
	 * @var array 
	 */ 
	private $_listeners = array(); 

	/** 
	 * 构造函数 
	 *  
	 * @access public 
	 * @return void 
	 */ 
    public function __construct($category){
		#这里$plugin数组包含我们获取已经由用户激活的插件信息
		#为演示方便，我们假定$plugin中至少包含
		#$plugin = array(
		#    'name' => '插件名称',
		#    'directory'=>'插件安装目录'
		#);
		global $c;
        $plugins=self::get_active_plugins($category);#这个函数请自行实现
        if($plugins){
			foreach($plugins as $plugin){
				//假定每个插件文件夹中包含一个actions.php文件，它是插件的具体实现
				if(@file_exists($c['root_path'].'plugins/'.$plugin['dir'].'/actions.php')){
					include_once($c['root_path'].'plugins/'.$plugin['dir'].'/actions.php');
					$class=$plugin['name'].'_actions';
					if(class_exists($class)){//初始化所有插件
						new $class($this);
					}
				}
			}
        }
        #此处做些日志记录方面的东西
    } 
     
	/**
	 * 注册需要监听的插件方法（钩子）
	 *
	 * @param string $hook
	 * @param object $reference
	 * @param string $method
	 */
	function register($hook, &$reference, $method){
		//获取插件要实现的方法
		$key=get_class($reference).'->'.$method;
		//将插件的引用连同方法push进监听数组中
		$this->_listeners[$hook][$key]=array(&$reference, $method);
		#此处做些日志记录方面的东西
	}

	/**
	 * 触发一个钩子
	 *
	 * @param string $hook 钩子的名称
	 * @param mixed $data 钩子的入参 
	 *    @return mixed
	 */
	function trigger($hook, $function, $data=''){
		$result = '';
		//查看要实现的钩子，是否在监听数组之中
		if(isset($this->_listeners[$hook]) && is_array($this->_listeners[$hook]) && count($this->_listeners[$hook])>0){
			//print_r($this->_listeners);
			// 循环调用开始
			foreach($this->_listeners[$hook] as $listener){
				// 取出插件对象的引用和方法
				$class =& $listener[0];
				$method = $listener[1];
				if(method_exists($class,$method) && $function==$method){
					// 动态调用插件的方法
					$result .= $class->$method($data);
				}
			}
		}
		#此处做些日志记录方面的东西
		return $result;
	}
	
	function get_active_plugins($category){
		$row=db::get_all('plugins', 'IsUsed=1'.($category?" and Category='$category'":''), '*', 'Category asc, PluginId asc');
		$data=array();
		foreach($row as $v){
			$data[]=array('name'=>trim($v['ClassName']), 'dir'=>trim($v['Category']).'/'.trim($v['ClassName']));
		}
		return $data;
	}
}
?>