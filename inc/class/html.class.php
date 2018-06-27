<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class html{
	/**
	 * 首页设置的显示内容
	 *
	 * @param: $config[object]	首页设置的数据
	 * @return string
	 */
	public static function config_edit_form($config){
		global $c;
		/*
		'Effects'		//特效方式
		'HeaderContent'	//头部内容
		'IndexContent'	//首页内容
		'TopMenu'		//顶部栏目
		'ContactMenu'	//联系我们
		'ShareMenu'		//分享栏目
		'IndexTitle'	//首页标题
		*/
		$ShareMenuAry=$c['manage']['config']['ShareMenu'];
		$ContactMenuAry=$c['manage']['config']['ContactMenu'];
		$TopMenuAry=$c['manage']['config']['TopMenu'];
		$html='';
		if((int)$config['Effects']){ //特效方式
			$html.='
				<div class="rows" style="display:none;">
					<label>{/themes.products_list.effects_order/}</label>
					<span class="input effects_list">
						<select name="Effects">
			';
				for($i=0; $i<7; ++$i){
					$html.='<option value="'.$i.'" number="'.$i.'"'.($c['manage']['config']['Effects']==$i?' selected':'').'>{/themes.products_list.effects_ary.'.$i.'/}</option>';
				}
			$html.='
						</select>
					</span>
					<div class="clear"></div>
				</div>
			';
		}
		if($config['HeaderContent'] || $config['IndexContent']){
			$html.='
				<div class="rows">
					<label>{/global.other/}</label>
					<span class="input tab_box">'.manage::html_tab_button('border').'<div class="blank9"></div>
			';
					foreach($c['manage']['config']['Language'] as $k=>$v){
						$html.='<div class="tab_txt tab_txt_'.$k.'">';
							for($i=0;$i<$config['HeaderContent'];++$i){ //头部内容
								$num = $config['HeaderContent']>1 ? '_'.$i : '';
								$html.='<span class="price_input lang_input"><b>{/set.config.header_content/}<div class="arrow"><em></em><i></i></div></b><input type="text" name="HeaderContent'.$num.'_'.$v.'" value="'.htmlspecialchars(htmlspecialchars_decode($c['manage']['config']['HeaderContent']["HeaderContent{$num}_{$v}"]), ENT_QUOTES).'" class="form_input" size="35" /></span>';
							}
							for($i=0;$i<$config['IndexContent'];++$i){ //首页内容
								$num = $config['IndexContent']>1 ? '_'.$i : '';
								$html.='<div class="blank9"></div><span class="price_input lang_input price_textarea"><b>{/set.config.index_content/}<div class="arrow"><em></em><i></i></div></b><textarea name="IndexContent'.$num.'_'.$v.'">'.htmlspecialchars(htmlspecialchars_decode($c['manage']['config']['IndexContent']["IndexContent{$num}_{$v}"]), ENT_QUOTES).'</textarea></span>';
							}
						$html.='</div>';
					}
			$html.='
					</span>
					<div class="clear"></div>
				</div>
			';
		}
		if((int)$config['TopMenu']){ //顶部栏目
			$html.='
				<div class="rows">
					<label>{/set.config.topmenu/}</label>
					<span class="input tab_box">'.manage::html_tab_button('border').'<div class="blank9"></div>
			';
					foreach($c['manage']['config']['Language'] as $k=>$v){
					$html.='<div class="tab_txt tab_txt_'.$k.'">';
					for($i=0; $i<4; ++$i){
						$html.='
							<div class="help_item">
								<span class="price_input not_input"><b>{/global.name/}<div class="arrow"><em></em><i></i></div></b><input type="text" name="TopName_'.$v.'[]" value="'.$TopMenuAry[$i]["TopName_{$v}"].'" class="form_input input_name" size="25" /></span>
								<span class="price_input not_input long"><b>{/themes.nav.url/}<div class="arrow"><em></em><i></i></div></b><input type="text" name="TopUrl[]" value="'.$TopMenuAry[$i]['TopUrl'].'" class="form_input input_url" size="45" '.($k?' disabled':'').' /></span>
								<b>{/themes.nav.target/}</b>
								<div class="switchery'.($TopMenuAry[$i]['TopNewTarget']?' checked':'').'">
									<input type="checkbox" name="TopNewTarget[]" value="1"'.($TopMenuAry[$i]['TopNewTarget']?' checked':'').($k?' disabled':'').'>
									<div class="switchery_toggler"></div>
									<div class="switchery_inner">
										<div class="switchery_state_on"></div>
										<div class="switchery_state_off"></div>
									</div>
								</div>
							</div>';
						}
					$html.='</div>';
					}
			$html.='</span>
					<div class="clear"></div>
				</div>
			';
		}
		if((int)$config['ContactMenu']){ //联系我们
			$html.='
				<div class="rows">
					<label>{/set.config.contactmenu/}</label>
					<span class="input">
			';
						for($i=0; $i<4; ++$i){
							$html.='<span class="price_input lang_input"><b>{/set.config.contactmenu_ary.'.$i.'/}<div class="arrow"><em></em><i></i></div></b><input type="text" name="ContactMenu[]" value="'.$ContactMenuAry[$i].'" class="form_input" size="35" /></span><div class="blank6"></div>';
						}
			$html.='
					</span>
					<div class="clear"></div>
				</div>
			';
		}
		if((int)$config['ShareMenu']){ //顶部栏目
			$html.='
				<div class="rows">
					<label>{/set.config.sharemenu/}</label>
					<span class="input">';
						foreach($c['share'] as $v){
							$html.='<span class="price_input lang_input"><b>'.$v.'<div class="arrow"><em></em><i></i></div></b><input type="text" name="Share'.$v.'" value="'.$ShareMenuAry[$v].'" class="form_input" size="35" /></span><div class="blank6"></div>';
						}
			$html.='
					</span>
					<div class="clear"></div>
				</div>
			';
		}
		if((int)$config['IndexTitle']){ //首页标题
			$html.='
				<div class="rows">
					<label>{/module.account.index/}{/global.title/}</label>
					<span class="input"><input name="IndexTitle" value="'.$c['manage']['config']['IndexTitle'].'" type="text" class="form_input" size="50" /></span>
					<div class="clear"></div>
				</div>
			';
		}
		$html && $html='<h3 class="rows_hd">{/set.config.index_config/}</h3>'.$html;
		return $html;
	}
	
	/**
	 * PC端 评论星星
	 *
	 * @param: $rating[int]	评论平均数
	 * @return string
	 */
	public static function review_star($rating, $class=''){
		$html='';
		
		$html.='<span class="review_star '.$class.'">';
		for($i=1; $i<6; ++$i){
			if($i<=$rating){
				$html.='<span class="star_1"></span>';
			}else{
				$html.='<span class="star_0"></span>';
			}
		}
		$html.='</span>';
		return $html;
	}
	
	/**
	 * 移动端 评论星星
	 *
	 * @param: $rating[int]	评论平均数
	 * @return string
	 */
	public static function mobile_review_star($rating, $class=''){
		$html='';
		
		$html.='<span class="review_star '.$class.'">';
		for($i=1; $i<6; ++$i){
			if($i<=$rating){
				$html.='<span class="star_1"></span>';
			}else{
				$html.='<span class="star_0"></span>';
			}
		}
		$html.='</span>';
		return $html;
	}
	
	/**
	 * 移动端 面包屑
	 *
	 * @param: $location[string]	面包屑后面接上的内容
	 * @return string
	 */
	public static function mobile_crumb($location){
		$html='';
		
		$html.='<div class="crumb clean">';
			$html.='<a href="/"><span class="icon_crumb_home"></span></a>'.$location;
		$html.='</div>';
		return $html;
	}
	
	/**
	 * 移动端 购物车的引导向标
	 *
	 * @param: $number[int]	引导数值  0:Checkout 1:Payment 2:Complete
	 * @return string
	 */
	public static function mobile_cart_step($number){
		global $c;
		$StyleData=(int)db::get_row_count('config_module', 'IsDefault=1')?db::get_value('config_module', 'IsDefault=1', 'StyleData'):db::get_value('config_module', "Themes='{$c['theme']}'", 'StyleData');
		$style_data=str::json_data($StyleData, 'decode');
		$html='';
		
		$html="<style type=\"text/css\">\r\n";
			$html.=".cart_step>div.current{background-color:".$style_data['FontColor'].";}\r\n";
			$html.=".cart_step>div.current>i{border-color:transparent transparent transparent ".$style_data['FontColor'].";}\r\n";
		$html.='</style>';
		
		$html.='<div class="cart_step clean">';
			for($i=0; $i<3; ++$i){
				$html.='<div class="step_'.$i.($number==$i?' current':'').'">'.($i>0?'<em></em>':'').$c['lang_pack']['mobile']['step_ary'][$i].($i<2?'<i></i>':'').'</div>';
			}
		$html.='</div>';
		return $html;
	}
}
?>