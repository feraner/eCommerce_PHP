/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

var sync_obj={
	sync_init:function(){
		frame_obj.del_init($('#products .r_con_table'));
		frame_obj.select_all($('input[name=select_all]'), $('input[name=select]')); //批量操作
		
		$('#products .dashboard .div-btn').hover(function(){
			$(this).find('ul').show();
		}, function(){
			$(this).find('ul').hide();
		});
	},
	
	/***********************************速卖通部分(start)************************************/
	aliexpress_init:function(){
		frame_obj.del_bat($('.r_nav .del'), $('input[name=select]'), function(id_list){
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('?', {do_action:'products.aliexpress_products_del_bat', group_proid:id_list}, function(data){
					if(data.ret==1){window.location.reload();}
				}, 'json');
			}, 'confirm');
			return false;
		});
		
		/***********************************开始同步产品(start)************************************/
		$('button[name=aliexpress_product_list_sync]').click(function(){
			var $obj=$('.pop_form.box_aliexpress_sync');
			frame_obj.pop_form($obj);
		});
		$('#aliexpress_sync_form button[name=submit_button]').click(function(){
			$.post('./', 'do_action=products.aliexpress_products_sync&'+$('#aliexpress_sync_form').serialize(), function(data){
				frame_obj.pop_form($('.pop_form.box_aliexpress_sync'), 1, 1);
				if(data.ret==1){
					$('.pop_form.sync_progress .tips_contents').html(lang_obj.manage.products.sync.start_sync_products);
					frame_obj.pop_form($('.pop_form.sync_progress'));
					ajax_get_sync_status(data.msg.TaskId);
					//global_obj.win_alert(lang_obj.manage.products.sync.start_sync_products);
				}else if(data.ret==-1){
					global_obj.win_alert(lang_obj.manage.products.sync.not_repeat_task);
				}else{
					global_obj.win_alert(data.msg);
				}
			},'json');
		});
		$('.pop_form.sync_progress .tips_contents').delegate('.close', 'click', function(){frame_obj.pop_form($('.pop_form.sync_progress'),1);});
		
		function ajax_get_sync_status(TaskId){
			$.ajax({
				url:'./',
				type:'post',
				data:{'do_action':'products.aliexpress_products_sync_status', 'TaskId':TaskId},
				timeout:5000,
				dataType:'json',
				success:function(data){
					var $obj=$('.pop_form.sync_progress .tips_contents');
					if(data.ret==1){
						$obj.html(data.msg.tips);
						if(data.msg.status==2){
							setTimeout(function(){frame_obj.pop_form($('.pop_form.sync_progress'),1)}, 3000);
						}else{
							setTimeout(function(){ajax_get_sync_status(TaskId)},1000);
						}
					}else{
						ajax_get_sync_status(TaskId);
					}
				},
				complete:function(XMLHttpRequest,status){
					if(status=='timeout'){
						ajax_get_sync_status(TaskId);
					}
				}
			});
		}
		/***********************************开始同步产品(end)************************************/
		
		/***********************************复制产品部分************************************/
		/* 复制产品分类选择弹出框 */
		$('#batchPost li.local').on('click', function(){
			var $obj=$('.copy_products_box');
			$('.copy_products_box select[name=CateId]').find('option:selected').removeAttr('selected');
			frame_obj.pop_form($obj);
		});
		/* 提交产品复制 */
		frame_obj.del_bat($('.copy_products_box input.btn_ok'), $('input[name=select]'), function(id_list){
			var $obj=$('.copy_products_box select[name=CateId]').find('option:selected');
			if($obj.val()!=''){
				var $CateId=parseInt($obj.val());
			}else{
				alert(lang_obj.manage.products.category_tips);
				return false;
			}
			
			$.post('./', {do_action:'products.copy_alexpress_to_products', group_id:id_list, CateId:$CateId}, function(data){
				if(data.ret==1){
					$('.copy_products_box').hide();
					global_obj.win_alert(lang_obj.manage.products.copy_complete);
				}else{
					alert(data.msg);
				}
			}, 'json');
		}, lang_obj.global.dat_select);
		/***********************************复制产品部分************************************/
		
		$('#products .account_list a').click(function(){
			if(!$(this).hasClass('cur')){
				global_obj.div_mask();
				global_obj.data_posting(1, lang_obj.manage.products.sync_change_account);
				$.post('./', 'do_action=products.change_aliexpress_authorization_account&AccountId='+$(this).attr('data-id'), function(data){
					if(data.ret==1){
						window.top.location.reload();
					}else{
						data.msg && global_obj.win_alert(data.msg);
					}
					setTimeout(function(){global_obj.div_mask(1)},500);
					global_obj.data_posting();
				},'json');
			}
		});
	},
	
	aliexpress_edit_init:function(){
		//加载速卖通账号相关信息（分组、运费模板、服务模板等）
		sync_obj.aliexpress_load_account_contents($('#edit_form select[name=Account]').val());
		$('#edit_form select[name=Account]').change(function(){sync_obj.aliexpress_load_account_contents($(this).val());});
		//按分类显示属性内容
		sync_obj.aliexpress_edit_category_select($('#edit_form select[name=categoryId]').val(), $('input[name=ProId]').val());
		$('#edit_form select[name=categoryId]').change(function(){sync_obj.aliexpress_edit_category_select($(this).val(), $('input[name=ProId]').val());});

		//自定义属性设置
		$('.property-table').delegate('.custom-property .custom-property-item .del', 'click', function(){$(this).parent('.custom-property-item').remove();});
		$('.property-table').delegate('.custom-property .add-property .add', 'click', function(){
			var $html='<div class="custom-property-item"><input name="attrName[]" value="" type="text" class="form_input" size="35" maxlength="40" notnull /> : <input name="attrValue[]" value="" type="text" class="form_input" size="60" maxlength="70" notnull /> <a href="javascript:;" class="green del">'+lang_obj.global.del+'</a></div>';
			$('.property-table .custom-property .custom-property-item').length?$('.property-table .custom-property .custom-property-item:last').after($html):$('.property-table .custom-property').prepend($html);
		});
		//设置单位、体积等
		var resetUnit=function(){
			var pUnit=$('select[name=productUnit] option:selected').attr('data-unit');
			$('.pUnit').text(pUnit);
		}
		var resetVolume=function(){
			var pLength=parseInt($('input[name=packageLength]').val()),
				pWidth=parseInt($('input[name=packageWidth]').val()),
				pHeight=parseInt($('input[name=packageHeight]').val());
			var pVolume=parseInt(pLength*pWidth*pHeight);
			$('.pVolume').text(pVolume);
		}
		resetUnit();
		resetVolume();
		$('select[name=productUnit]').change(function(){resetUnit();});
		$('input[name=packageLength], input[name=packageWidth], input[name=packageHeight]').on('keyup keypress', function(){resetVolume();});
		//销售方式设置
		$('input[name=packageType]').click(function(){
			if($(this).val()==1){
				$('.salesByPack').show().find('input').attr('notnull', '');
			}else{
				$('.salesByPack').hide().find('input').removeAttr('notnull');
			}
		});
		
		
		/*************************************产品图片上传(start)*************************************/
		$('.multi_img .upload_btn, .pic_btn .edit').on('click', function(){
			var $num=$(this).parents('.img').attr('num');
			frame_obj.photo_choice_init('PicUpload_'+$num, '', 'PicDetail .img[num;'+$num+']', 'products', 6, 'do_action=products.aliexpress_products_img_del&Model=products');
		});
		$('.multi_img input[name=PicPath\\[\\]]').each(function(){
			if($(this).attr('save')==1){
				$(this).parent().append(frame_obj.upload_img_detail($(this).attr('data-value'))).children('.upload_btn').hide();
			}
		});
		$('.multi_img').dragsort({
			dragSelector:'dl',
			dragSelectorExclude:'',
			placeHolderTemplate:'<dl class="img placeHolder"></dl>',
			scrollSpeed:5
		});
		$('.pic_btn .del').on('click', function(){
			var $obj=$(this).parents('.img'),
				$num=parseInt($obj.attr('num')),
				$path=$obj.find('input[name=PicPath\\[\\]]').val();
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.ajax({
					url:'./?do_action=products.aliexpress_products_img_del&Model=products&Path='+$path+'&Index='+$num+'&ProId='+$('#ProId').val(),
					success:function(data){
						json=eval('('+data+')');
						$('#PicDetail dl[num='+json.msg[0]+'] .preview_pic .upload_btn').show();
						$('#PicDetail dl[num='+json.msg[0]+'] .preview_pic a').remove();
						$('#PicDetail dl[num='+json.msg[0]+'] .preview_pic input[name=PicPath\\[\\]]').val('').attr('save', 0);
					}
				});
			}, 'confirm');
			return false;
		});
		/*************************************产品图片上传(end)*************************************/
		
		/*************************************同步分组(start)*************************************/
		$('#GroupList a.sync').click(function(){
			global_obj.div_mask();
			global_obj.data_posting(true, lang_obj.global.sync);
			
			var sGroupId=parseInt($('#GroupList').attr('data-value'));
			$.ajax({
				url:'./',
				type:'post',
				data:{'do_action':'products.aliexpress_grouplist_sync', 'GroupId':sGroupId},
				timeout:30000,
				dataType:'json',
				success:function(data){
					if(data.ret==1){
						$('select[name=GroupId]').html(data.msg);
					}
					setTimeout(function(){global_obj.div_mask(1)},500);
					global_obj.data_posting();
				},
				complete:function(XMLHttpRequest,status){
					if(status=='timeout'){
						setTimeout(function(){global_obj.div_mask(1)},500);
						global_obj.data_posting();
					}
				}
			});
		});
		/*************************************同步分组(end)*************************************/

		/*************************************同步运费模板(start)*************************************/
		$('#freightTemp a.sync').click(function(){
			global_obj.div_mask();
			global_obj.data_posting(true, lang_obj.global.sync);
			
			var sFId=parseInt($('#freightTemp').attr('data-value'));
			$.ajax({
				url:'./',
				type:'post',
				data:{'do_action':'products.aliexpress_freight_template_sync', 'FId':sFId},
				timeout:30000,
				dataType:'json',
				success:function(data){
					if(data.ret==1){
						//$('select[name=freightTemplateId]').html(data.msg);
						$('#freightTemp select').remove();
						$('#freightTemp').prepend(data.msg);
					}
					setTimeout(function(){global_obj.div_mask(1)},500);
					global_obj.data_posting();
				},
				complete:function(XMLHttpRequest,status){
					if(status=='timeout'){
						setTimeout(function(){global_obj.div_mask(1)},500);
						global_obj.data_posting();
					}
				}
			});
		});
		/*************************************同步运费模板(end)*************************************/

		/*************************************同步服务模板(start)*************************************/
		$('#serviceTemp a.sync').click(function(){
			global_obj.div_mask();
			global_obj.data_posting(true, lang_obj.global.sync);
			
			var sServiceId=parseInt($('#serviceTemp').attr('data-value'));
			$.ajax({
				url:'./',
				type:'post',
				data:{'do_action':'products.aliexpress_service_template_sync', 'ServiceId':sServiceId},
				timeout:30000,
				dataType:'json',
				success:function(data){
					if(data.ret==1){
						//$('select[name=freightTemplateId]').html(data.msg);
						$('#serviceTemp select').remove();
						$('#serviceTemp').prepend(data.msg);
					}
					setTimeout(function(){global_obj.div_mask(1)},500);
					global_obj.data_posting();
				},
				complete:function(XMLHttpRequest,status){
					if(status=='timeout'){
						setTimeout(function(){global_obj.div_mask(1)},500);
						global_obj.data_posting();
					}
				}
			});
		});
		/*************************************同步服务模板(end)*************************************/
		
		//批发价 - 开启、同步显示折扣
		$('input[name=IsWholeSale]').click(function(){$(this).is(':checked')?$('.WholeSaleBox').show():$('.WholeSaleBox').hide();});
		$('input[name=bulkDiscount]').on('keyup keypress', function(){
			if($('input[name=IsWholeSale]').is(':checked') && $('.wholesaleDiscount').size()){
				var discount=$(this).val()?(100-parseInt($(this).val()))/10:'';
				$('.wholesaleDiscount').text(discount);
			}
		});
		//移动端描述 - 开启、同步
		$('input[name=IsMobileDetail]').click(function(){$(this).is(':checked')?$('.mobile_detail').show():$('.mobile_detail').hide();});
		$('.turn_on_mobile_detail a.sync').click(function(){
			var htmlData=CKEDITOR.instances.Detail.getData();
			CKEDITOR.instances.mobileDetail.setData(htmlData);
		});
		//包装信息 - 自定义计重
		$('input[name=isPackSell]').click(function(){$(this).is(':checked')?$('.custom_weight').show().find('input').attr('notnull', ''):$('.custom_weight').hide().find('input').removeAttr('notnull');});
		
		sync_obj.aliexpress_edit_load_attr();	//加载产品属性
		frame_obj.submit_form_init($('#edit_form'), './?m=products&a=sync', function(){
			//多选框必选检测
			var checkboxChecked=true,tips='';
			$('.form-control.form-checkbox-notnull').each(function(){
                if($('.form-control input:checkbox[notnull]').length){
					if(!$('.form-control input:checkbox[notnull]:checked').length){
						checkboxChecked=false;
						tips+=lang_obj.global.selected+' '+$(this).attr('title')+'!<br />';
					}
				}
            });
			if(!checkboxChecked){
				global_obj.win_alert(tips);
				return false;
			}
			//return false;
		});
	},
	
	aliexpress_load_account_contents:function(Account){	//加载速卖通账号信息
		if(!Account) return;
		
		var sGroupId=$('#GroupList').attr('data-value'),freightId=$('#freightTemp').attr('data-value'),serviceId=$('#serviceTemp').attr('data-value');
		$.post('?', {"do_action":"products.aliexpress_load_account", "Account":Account, "GroupId":sGroupId, "freightId":freightId, "serviceId":serviceId}, function(data){
			json=eval('('+data+')');
			if(json.ret==1){
				$('select[name=GroupId]').html(json.msg[0]);
				
				$('#freightTemp select, #serviceTemp select').remove();
				$('#freightTemp').prepend(json.msg[1]);
				$('#serviceTemp').prepend(json.msg[2]);
			}
			return false;
		});
	},
	
	aliexpress_edit_load_attr:function(){	//产品属性显示
		$('#sku').delegate('.sku-label-list .sku-property-list label input', 'click', function(){
			var obj=$(this);
			sync_obj.aliexpress_edit_attr_init(obj.attr('data'), obj.attr('id'), 1);
		});
	},
	
	aliexpress_edit_synchronize:function(){	//产品属性同步按钮
		$('#edit_form .synchronize_btn').off().on('click', function(){
			var $num=parseInt($(this).attr('data-num')),
				$obj=$(this).parents('tr'),
				$value=$obj.next('tr').find('td:eq('+($num+1)+') input').val();
			$obj.siblings('tr').find('td:eq('+($num+1)+') input').val($value);
		});
	},
	
	aliexpress_edit_category_select:function(value, ProId){//切换产品分类
		if(!value) return;
		var AttrId=parseInt($('#edit_form .attribute').attr('attrid'));
		
		global_obj.div_mask();
		global_obj.data_posting(1, lang_obj.global.loading);
		$.post('?', {"do_action":"products.aliexpress_get_attr", "AttrId":AttrId, "categoryId":value, "ProId":ProId}, function(data){//购物车属性
			json=eval('('+data+')');
			if(json.ret==1){
				$('.category_list').html(json.msg[0]);
				$('.property-table .property-form').html(json.msg[1]);
				json.msg[2] && $('.property-table .custom-property').prepend(json.msg[2]);
				json.msg[2] && $('.property-table .custom-property').removeClass('hide');
				$('#sku .sku-label-list').remove();
				$('#sku').prepend(json.msg[3]).attr('attrid', value);
				$('#all_attr').val(json.msg[4].toString());
				$('#ext_attr').val(json.msg[5].toString());
				$('#check_attr').val('');
				if(json.msg[6]){ //执行返回函数
					eval(json.msg[6]);
				}
			}
			global_obj.data_posting();
			setTimeout(function(){global_obj.div_mask(1)},500);
			return false;
		});
	},
	
	aliexpress_edit_attr_init:function(data, id, ischeck){//加载属性
		var dataObj=eval("("+data+")");
		var obj=$('input[id='+id+']');
		var all_attr=global_obj.json_encode_data($('#all_attr').val());
		var ext_attr=global_obj.json_encode_data($('#ext_attr').val());
		
		if(ischeck==0 || obj.is(':checked')){
			$('#attribute_ext_box').removeClass('hide');
			if($('#AttrId_'+dataObj.AttrId).length==0){
				var html_t=$('#attribute_tmp .column').html();
				html_t=html_t.replace('XXX',dataObj.AttrId).replace('Column',dataObj.Column.replace(/@8#/g, "'"));
				$('#attribute_ext').append(html_t);
			}
			if($('#VId_'+dataObj.Num).length==0){
				var html_c=$('#attribute_tmp .contents').html();
				html_c=html_c.replace(/XXX/g,dataObj.Num).replace('Name',dataObj.Name.replace(/@8#/g, "'"));
				$('#AttrId_'+dataObj.AttrId).append(html_c);
			}
		}else{
			if($('#VId_'+dataObj.Num).length>0){
				$('#VId_'+dataObj.Num).remove();
			}
			if($('#AttrId_'+dataObj.AttrId+' tr').length==1){
				$('#AttrId_'+dataObj.AttrId).remove();
			}
		}
		
		var check_attr=global_obj.json_encode_data($('#check_attr').val());
		if(!check_attr) check_attr=new Object();
		var check_attr_len=0;
		if(ischeck==0 || obj.is(':checked')){
			if(!check_attr[dataObj.AttrId]){
				check_attr[dataObj.AttrId]=new Array();
				check_attr[dataObj.AttrId][0]=dataObj.Num;
			}else{
				check_attr[dataObj.AttrId][check_attr[dataObj.AttrId].length]=dataObj.Num;
			}
		}else{
			for(k in check_attr[dataObj.AttrId]){//删除为空的属性
				if(check_attr[dataObj.AttrId].length<2){
					check_attr[dataObj.AttrId]=undefined;
					delete check_attr[dataObj.AttrId];
					break;
				}else if(check_attr[dataObj.AttrId][k]==dataObj.Num){
					check_attr[dataObj.AttrId].splice(k, 1);
					break;
				}
			}
		}
		
		var attr_value_ary=new Object();
		$('#check_attr').val(global_obj.json_decode_data(check_attr));
		//统计组合属性是否已经组合
		for(k in check_attr) check_attr_len+=1;
		if(check_attr_len){//>1
			for(k in all_attr){
				$('#AttrId_'+k).hide().find('input').attr('disabled', true);
				attr_value_ary[k]=global_obj.json_encode_data(all_attr[k]);
			}
			//列出属性
			var attr_ary=new Array(), key_ary=new Array();
			for(k in check_attr){
				key_ary.push(k);
				for(var i=0; i<check_attr[k].length; i++){
					attr_ary.push(k+'_'+check_attr[k][i]);
				}
			}
			//记录属性的名称
			var attr_name_ary=new Object();
			var attr_n;
			for(k in attr_value_ary){
				for(kk in attr_value_ary[k]){
					attr_n=kk;
					attr_name_ary[parseInt(isNaN(attr_n) ? attr_n.substr(1,attr_n.length-2): attr_n)]=attr_value_ary[k][kk];
				}
			}
			//组合属性
			var attr_arr=ary_0=ary_1=new Array();
			function CartAttr($arr, $num){
				var _arr=new Array();
				if($num==0){
					for(j in check_attr[key_ary[$num]]){
						$arr.push(check_attr[key_ary[$num]][j]);
					}
				}else{
					for(i in $arr){
						for(j in check_attr[key_ary[$num]]){
							_arr.push($arr[i]+'_'+check_attr[key_ary[$num]][j]);
						}
					}
					$arr=_arr;
				}
				++$num;
				if($num<check_attr_len){
					CartAttr($arr, $num);
				}else{
					attr_arr=$arr;
				}
			}
			CartAttr(attr_arr, 0);
			
			if(attr_arr.length<800){ //限制数量在800个以内
				if($('#AttrId_0').length==0){
					var html_t=$('#attribute_tmp .column').html();
					html_t=html_t.replace('XXX',0).replace('Column', lang_obj.manage.products.group_attr);
					$('#attribute_ext').append(html_t);
				}
				$('#AttrId_0 .group').remove();
				
				var insert='', html_c, html_attr, name, p_v, s_v, u_v;
				var html_contents=$('#attribute_tmp .contents').html();
				var number=0;
				for(var i=0; i<attr_arr.length; ++i){
					if(i>799) break;
					ary=attr_arr[i].split('_');
					ary.sort(function(a,b){ return a-b });
					ary_str=ary.join('_');
					if($('#AttrId_'+ary_str).length==0){
						html_c=html_contents;
						html_attr='';
						name='';
						for(j in ary){
							name+=(j==0?'':' + ')+attr_name_ary[ary[j]].replace(/@8#/g, "'")
						}
						val_ary=ext_attr[ary_str];
						for(k in check_attr){
							for(j in ary){
								if(global_obj.in_array(ary[j], check_attr[k])){
									html_attr+=' data-attr-id-'+k+'='+ary[j];
								}
							}
						}
						if(!val_ary) val_ary=[0,0,''];
						p_v=val_ary[0]?parseFloat(val_ary[0]).toFixed(2):''; //价格
						s_v=val_ary[1]?val_ary[1]:''; //库存
						u_v=val_ary[2]?val_ary[2]:''; //SKU
						html_c=html_c.replace(/XXX/g, ary_str).replace('Name', name).replace('p_v', p_v).replace('s_v', s_v).replace('u_v', u_v);
						html_c=html_c.replace('attr_txt=""', html_attr);
						insert+=html_c;
					}
					++number;
				}
				$('#AttrId_0').append(insert).find('tr:gt(0)').addClass('group');
			}else{ //超出限制数量
				obj.parent().click();
			}
			
			if($('#AttrId_0 tr').length==1){
				$('#AttrId_0').remove();

			}
		}else{
			for(k in all_attr){
				$('#AttrId_'+k).show().find('input').attr('disabled', false);
			}
			$('#AttrId_0').remove();
		}
		sync_obj.aliexpress_edit_synchronize();
		
		var id_c=dataObj.Num;
		var trId=((dataObj.customizedName==1 && dataObj.customizedPic==1)?'#CustomAll_':(dataObj.customizedName==1?'#CustomInput_':'#CustomImage_'))+id_c;
		if((dataObj.customizedName==1 || dataObj.customizedPic==1) && (ischeck==0 || obj.is(':checked'))){
			
			if($(trId).length==0){
				var html_pic=cPic=cName='';
				if(dataObj.customizedPic==1){
					if(dataObj.skuImage) cPic=dataObj.skuImage;
					html_pic+='<span class="multi_img upload_file_multi ImageDetail" id="ImageDetail_' + id_c + '"><dl class="img"><dt class="upload_box preview_pic">';
					html_pic+='<input type="button" id="ImagePath_' + id_c + '" class="btn_ok upload_btn" name="ImagePath[' + id_c + ']" value="' + lang_obj.manage.frame.file_upload + '" style="'+(cPic?'display:none;':'')+'" />';
					html_pic+='<input type="hidden" name="ImagePath[' + id_c + ']" value="' + cPic + '" save="'+(cPic?1:0)+'" />';
					if(cPic) html_pic+='<a href="javascript:;"><img src="' + dataObj.skuImageExt + '"><em></em></a><a href="' + cPic + '" class="zoom" target="_blank"></a>';
					html_pic+='</dt><dd class="pic_btn">';
						html_pic+='<a href="javascript:;" label="'+lang_obj.global.edit+'" class="tip_ico tip_min_ico edit"><img src="/static/ico/edit.png" align="absmiddle" /></a> ';
						html_pic+='<a href="javascript:;" label="'+lang_obj.global.del+'" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>';
					html_pic+='</dd></dl></span>';		
				}
				if(dataObj.customizedName==1 && dataObj.customName) cName=dataObj.customName;
				
				if(dataObj.customizedName==1 && dataObj.customizedPic==1) var html_c=$('#custom_tmp .contents_all').html();
				else if(dataObj.customizedPic==1) var html_c=$('#custom_tmp .contents_image').html();
				else if(dataObj.customizedName==1) var html_c=$('#custom_tmp .contents_input').html();
				html_c=html_c.replace(/XXX/g, id_c).replace('Name', dataObj.Name.replace(/@8#/g, "'")).replace('Content', html_pic).replace('c_n', cName);
				
				$('#sku-custom-property-' + dataObj.AttrId).removeClass('hide').find('table tbody').append(html_c);
				
				$('#ImageDetail_'+id_c+' .upload_btn, #ImageDetail_'+id_c+' .pic_btn .edit').on('click', function(){
					frame_obj.photo_choice_init(trId.replace(/#/g, ""), 'input[name;ImagePath\\['+id_c+'\\]\\[\\]]', 'ImageDetail_'+id_c+' .img', 'products', 1, 'do_action=products.aliexpress_products_img_del&Model=products');
				});
				$('#ImageDetail_'+id_c+' .pic_btn .del').off('click').on('click', function(){
					var $obj=$(this).parents('.img'),
						$path=$obj.find('input[type=hidden]').val();
					global_obj.win_alert(lang_obj.global.del_confirm, function(){
						$.ajax({
							url:'./?do_action=products.aliexpress_products_img_del&Model=products&Path='+$path,
							success:function(data){
								json=eval('('+data+')');
								$('#ImageDetail_'+id_c+' dl .preview_pic .upload_btn').val('').show();
								$('#ImageDetail_'+id_c+' dl .preview_pic a').remove();
								$('#ImageDetail_'+id_c+' dl .preview_pic input:hidden').val('').attr('save', 0);
							}
						});
					}, 'confirm');
					return false;
				});
			}
		}else{
			$(trId).length && $(trId).remove();
		}
		if(ischeck==0 || $('#attribute_ext tbody tr').size()){
			$('#attribute_ext_box').removeClass('hide');
			$('#sku .rows.form-post-list').addClass('hide').find('input').attr('disabled', 'disabled');
		}else{
			$('#attribute_ext_box').addClass('hide');
			$('#sku .rows.form-post-list').removeClass('hide').find('input').removeAttr('disabled');
		}
		if(ischeck==0 || $('#sku-custom-property-' + dataObj.AttrId).find('tbody tr').size()){
			$('#sku-custom-property-' + dataObj.AttrId).removeClass('hide');
		}else{
			$('#sku-custom-property-' + dataObj.AttrId).addClass('hide');
		}
	},
	/***********************************速卖通部分(end)************************************/

	/***********************************亚马逊部分(start)************************************/
	amazon_init:function(){
		frame_obj.del_bat($('.r_nav .del'), $('input[name=select]'), function(id_list){
			global_obj.win_alert(lang_obj.global.del_confirm, function(){
				$.get('?', {do_action:'products.amazon_products_del_bat', group_proid:id_list}, function(data){
					if(data.ret==1){window.location.reload();}
				}, 'json');
			}, 'confirm');
			return false;
		});
		
		/***********************************开始同步产品(start)************************************/
		$('button[name=amazon_product_list_sync]').click(function(){
			$.post('./', 'do_action=products.amazon_products_sync&'+$('#aliexpress_sync_form').serialize(), function(data){
				frame_obj.pop_form($('.pop_form.box_aliexpress_sync'), 1, 1);
				if(data.ret==1){
					$('.pop_form.sync_progress .tips_contents').html(lang_obj.manage.products.sync.start_sync_products);
					frame_obj.pop_form($('.pop_form.sync_progress'));
					ajax_get_sync_status(data.msg.TaskId);
					//global_obj.win_alert(lang_obj.manage.products.sync.start_sync_products);
				}else if(data.ret==-1){
					global_obj.win_alert(lang_obj.manage.products.sync.not_repeat_task);
				}else{
					global_obj.win_alert(data.msg);
				}
			},'json');
		});
		$('.pop_form.sync_progress .tips_contents').delegate('.close', 'click', function(){frame_obj.pop_form($('.pop_form.sync_progress'),1);});
		
		function ajax_get_sync_status(TaskId){
			$.ajax({
				url:'./',
				type:'post',
				data:{'do_action':'products.amazon_products_sync_status', 'TaskId':TaskId},
				timeout:5000,
				dataType:'json',
				success:function(data){
					var $obj=$('.pop_form.sync_progress .tips_contents');
					if(data.ret==1){
						$obj.html(data.msg.tips);
						if(data.msg.status==2){
							setTimeout(function(){frame_obj.pop_form($('.pop_form.sync_progress'),1)}, 3000);
						}else{
							setTimeout(function(){ajax_get_sync_status(TaskId)},1000);
						}
					}else{
						ajax_get_sync_status(TaskId);
					}
				},
				complete:function(XMLHttpRequest,status){
					if(status=='timeout'){
						ajax_get_sync_status(TaskId);
					}
				}
			});
		}
		/***********************************开始同步产品(end)************************************/
		
		/***********************************复制产品部分************************************/
		/* 复制产品分类选择弹出框 */
		$('#batchPost li.local').on('click', function(){
			var $obj=$('.copy_products_box');
			$('.copy_products_box select[name=CateId]').find('option:selected').removeAttr('selected');
			frame_obj.pop_form($obj);
		});
		/* 提交产品复制 */
		frame_obj.del_bat($('.copy_products_box input.btn_ok'), $('input[name=select]'), function(id_list){
			$('.copy_products_box input.btn_ok').attr('disabled', 'disabled');
			var $obj=$('.copy_products_box select[name=CateId]').find('option:selected');
			if($obj.val()!=''){
				var $CateId=parseInt($obj.val());
			}else{
				alert(lang_obj.manage.products.category_tips);
				$('.copy_products_box input.btn_ok').removeAttr('disabled');
				return false;
			}
			
			$.post('./', {do_action:'products.copy_amazon_to_products', group_id:id_list, CateId:$CateId}, function(data){
				$('.copy_products_box input.btn_ok').removeAttr('disabled');
				if(data.ret==1){
					$('.copy_products_box').hide();
					global_obj.win_alert(lang_obj.manage.products.copy_complete);
				}else{
					alert(data.msg);
				}
			}, 'json');
		}, lang_obj.global.dat_select);
		/***********************************复制产品部分************************************/
		
		$('#products .account_list a').click(function(){
			if(!$(this).hasClass('cur')){
				global_obj.div_mask();
				global_obj.data_posting(1, lang_obj.manage.products.sync_change_account);
				$.post('./', 'do_action=products.change_amazon_authorization_account&AccountId='+$(this).attr('data-id'), function(data){
					if(data.ret==1){
						window.top.location.reload();
					}else{
						data.msg && global_obj.win_alert(data.msg);
					}
					setTimeout(function(){global_obj.div_mask(1)},500);
					global_obj.data_posting();
				},'json');
			}
		});
	},
	
	amazon_edit_init:function(){
		/* 图片上传 */
		$('#ImageUpload, .upload_image .edit').on('click', function(){frame_obj.photo_choice_init('ImageUpload', 'form input[name=ImagePath]', 'ImageDetail', '', 1);});
		if($('form input[name=ImagePath]').attr('save')==1){
			$('#ImageDetail').append(frame_obj.upload_img_detail($('form input[name=ImagePath]').val())).children('.upload_btn').hide();
		}
		$('.upload_image .del').on('click', function(){
			$('#ImageDetail').children('a').remove();
			$('#ImageDetail').children('.upload_btn').show();
			$('#edit_form input[name=ImagePath]').val('');
		});

		frame_obj.submit_form_init($('#edit_form'), './?m=products&a=sync&d=amazon');

	}
	/***********************************亚马逊部分(end)************************************/
}