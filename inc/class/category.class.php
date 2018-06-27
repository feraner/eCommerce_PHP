<?php
/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

class category{
	public static function get_UId_by_CateId($CateId, $table='products_category'){	//根据CateId取得UId
		$UId=db::get_value($table, "CateId='$CateId'", 'UId');
		return $UId?$UId.$CateId.',':'-1,-1,';
	}
	
	public static function get_CateId_by_UId($UId){	//根据UId取得CateId
		$arr_UId=@explode(',', $UId);
		$CateId=$arr_UId[count($arr_UId)-2];
		return $CateId;
	}
	
	public static function get_top_CateId_by_UId($UId){	//根据UId取得顶级的CateId
		$arr_UId=@explode(',', $UId);
		return $arr_UId[1];
	}
	
	public static function get_FCateId_by_UId($UId){	//返回上一级的CateId
		$arr_UId=explode(',', $UId);
		array_pop($arr_UId);
		$CateId=end($arr_UId);
		return $CateId;
	}
	
	public static function get_search_where_by_CateId($CateId, $table='products_category'){	//根据CateId创建条件用于数据库查询
		$UId=category::get_UId_by_CateId($CateId, $table);
		$category_row=db::get_all($table, "UId like '{$UId}%'", 'CateId');
		if($category_row){
			$subCateId='';
			foreach($category_row as $v){
				$subCateId.=$v['CateId'].',';
			}
			return "CateId in($subCateId$CateId)";
		}else{
			return "CateId='$CateId'";
		}
	}
	
	public static function get_search_where_by_ExtCateId($CateId, $table='products_category'){	//根据CateId创建条件用于数据库查询
		$UId=category::get_UId_by_CateId($CateId, $table);
		$category_row=db::get_all($table, "UId like '{$UId}%'", 'CateId');
		if($category_row){
			$subCateId='';
			foreach($category_row as $v){
				$subCateId.="ExtCateId like '%,{$v['CateId']},%' or ";
			}
			return "({$subCateId}ExtCateId like '%,{$CateId},%')";//"ExtCateId in($subCateId$CateId)";
		}else{
			return "ExtCateId like '%,{$CateId},%'";
		}
	}
	
	public static function ouput_Category_to_Select($select_name='CateId', $selected_CateId='', $table='products_category', $where='0', $ext_where=1, $attr='', $default_option='--请选择--'){	//输出成select表单
		global $c;
		ob_start();
		echo "<select name='$select_name' $attr>";
		echo "<option value=''>{$default_option}</option>";
		$category_row=db::get_all($table, $where, '*', $c['my_order'].' CateId asc');
		category::ouput_Category_to_Select_ext($category_row, $selected_CateId, $table, 0, '', $ext_where);
		echo '</select>';
		$select=ob_get_contents();
		ob_end_clean();
		return $select;
	}
	
	public static function ouput_Category_to_Select_ext($category_row, $selected_CateId, $table, $layer=0, $PreChars='', $ext_where){	//递归循环输出类别到Select
		global $c;
		foreach($category_row as $v){
			$value=str_replace(' ', '&nbsp;', $PreChars.($i+1==count($category_row)?'└':'├'));
			$selected=$v['CateId']==$selected_CateId?'selected':'';
			if($table=='business_category' || $table=='photo_category'){
				$category=$v['Category'];
			}else{
				$category=$c['manage']?$v['Category'.$c['manage']['web_lang']]:$v['Category'.$c['lang']];
			}
			echo "<option value='{$v['CateId']}' $selected>{$value}{$category}</option>";
			if($v['SubCateCount']){
				$ext_row=db::get_all($table, "UId='{$v['UId']}{$v['CateId']},' and $ext_where", '*', 'if(MyOrder>0, MyOrder, 100000) asc, CateId asc');
				$PreChars.=$i+1==count($category_row)?'    ':'｜';   //前导符
				$layer++;
				category::ouput_Category_to_Select_ext($ext_row, $selected_CateId, $table, $layer, $PreChars, $ext_where);
				$PreChars=substr($PreChars, 0, -3);
				$layer--;
			}
		}
	}
	
	public static function category_subcate_statistic($table, $where='1'){ //类别表子类别数量统计
		$category_row=db::get_all($table, $where, 'UId, CateId', 'MyOrder desc, CateId asc');
		foreach($category_row as $v){
			db::update($table, "CateId='{$v['CateId']}'", array('SubCateCount'=>db::get_row_count($table, "UId like '{$v['UId']}{$v['CateId']},%'")));
		}
	}
}
?>