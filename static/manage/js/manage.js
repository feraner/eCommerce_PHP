/*
Powered by ueeshop.com		http://www.ueeshop.com
广州联雅网络科技有限公司		020-83226791
*/

var manage_obj={
	manage_list_init:function(){
		frame_obj.del_init($('#manage .r_con_table'));
	},
	
	manage_edit_init:function(){
		//权限勾选
		$('#PermitBox :checkbox').click(function(){
			var $obj,
				$this=$(this),
				$myId=$this.attr('id'),//自己的位置
				$checked=$this.attr('checked'),//勾选状态
				$position='';//当前位置
			if($checked=='checked'){
				if($myId=='selected_all'){
					$("#PermitBox :checkbox:visible").each(function(){
						this.checked=true;
					});
				}else{
					$position=$myId.split('_');
					if($position.length>2){
						manage_obj.permit_checked($position);
					}
					if($this.parent()[0].tagName=='DIV'){//当前为一级
						$this.parent().find(':checkbox').attr('checked', true);
					}else if($this.parent()[0].tagName=='DT'){//当前为二级
						$this.parent().siblings('dd').find(':checkbox').attr('checked', true);
					}else if($this.parent()[0].tagName=='DD'){//当前为三级
						$this.next().next().find(':checkbox').attr('checked', true);
					}
				}
			}else{
				if($myId=='selected_all'){
					$("#PermitBox :checkbox").each(function(){
						this.checked=false;
					});
				}else{
					if($this.parent()[0].tagName=='DIV'){//当前为一级
						$this.parent().find(':checkbox').attr('checked', false);
					}else if($this.parent()[0].tagName=='DT'){//当前为二级
						$this.parent().siblings('dd').find(':checkbox').attr('checked', false);
					}else if($this.parent()[0].tagName=='DD'){//当前为三级
						$this.next().next().find(':checkbox').attr('checked', false);
					}
				}
			}
		});
		
		$('#edit_form').delegate('select[name=GroupId]', 'change', function(){
			$('#PermitBox').css('display', $(this).val()==1?'none':'block');
			if($(this).val()==3){
				$('#PermitBox .module').each(function(){
					if($(this).children('input').attr('id')!='Permit_orders' && $(this).children('input').attr('id')!='Permit_user'){
						$(this).hide().find(':checkbox').attr('checked', false);
					}else{
						$(this).show();
					}
				});
			}else{
				$('#PermitBox .module').show();
			}
		});
		frame_obj.submit_form_init($('#edit_form'), './?m=manage&d=manage');
	},
	
	permit_checked:function(obj){
		obj.pop();//去掉最后一个元素
		var sObj=obj.join("_");
		$('#'+sObj).attr('checked', true);
		if(obj.length>2){
			manage_obj.permit_checked(obj);
		}
	},
	
	course_init:function(){
		$('#course a').click(function(){
			$('#course .view').html('<embed src="'+$(this).attr('url')+'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" wmode="opaque" width="720" height="600"></embed>');
		});
	}
}