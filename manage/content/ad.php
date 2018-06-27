<?php !isset($c) && exit();?>
<?php
manage::check_permit('content', 1, array('a'=>'ad'));//检查权限
?>
<div class="r_nav">
	<h1>{/module.content.ad/}</h1>
	<?php if($c['manage']['do']=='index'){?>
		<!--<ul class="ico">
			<li><a href="./?m=content&a=ad&d=add" class="add" title="{/global.add/}"></a></li>
		</ul>-->
	<?php }?>
</div>
<div id="ad" class="r_con_wrap">
	<?php
	if($c['manage']['do']=='index'){
		$mobile_config=db::get_one('mobile_config');
		$mobile_row=db::get_all('config', "GroupId='mobile' and (Variable='HomeTpl' or Variable='ListTpl')");
		foreach($mobile_row as $v){
			$mobile_config[$v['Variable']]=$v['Value'];
		}
		$rs_row=str::str_code(db::get_all('ad', "Themes='{$c['manage']['web_themes']}'".(($c['FunVersion']>0 || (!(int)$c['FunVersion'] && !(int)$c['NewFunVersion']))?"  or MThemesHome='{$mobile_config['HomeTpl']}' or MThemesList='{$mobile_config['ListTpl']}'":'').($c['FunVersion']>1?" or Themes='tuan' or Themes='seckill'":''), 'AId,PageName,PageName_en,AdPosition,AdPosition_en', 'AId asc'));
		$ad_row=array();
		foreach($rs_row as $k=>$v){//根据 PageName 整合数组
			$PageName=$v['PageName'];
			$c['manage']['config']['ManageLanguage']=='en' && $PageName=$v['PageName_en'];
			if(!array_key_exists($PageName, $ad_row)){//pageName 键名不存在，新建一个数组
				$ad_row[$PageName]=array();
			}
			$ad_row[$PageName][]=$v;
		}
	?>
		<script type="text/javascript">$(document).ready(function(){content_obj.ad_init()});</script>
		<table border="0" cellpadding="5" cellspacing="0" class="r_con_table">
			<thead>
				<tr>
					<td width="10%" nowrap="nowrap">{/global.serial/}</td>
					<td width="20%" nowrap="nowrap">{/ad.ad.pagename/}</td>
					<td width="70%" nowrap="nowrap">{/ad.ad.position/}</td>
				</tr>
			</thead>
			<tbody>
				<?php 
				$i=1;
				foreach($ad_row as $k=>$v){
				?>
					<tr>
						<td nowrap="nowrap"><?=$i++;?></td>
						<td nowrap="nowrap"><?=$k;?></td>
						<td nowrap="nowrap">
							<table width="100%" border="0" cellspacing="0" cellpadding="0">
								<?php
								foreach((array)$v as $k2=>$v2){
									$AdPosition=$v2['AdPosition'];
									$c['manage']['config']['ManageLanguage']=='en' && $AdPosition=$v2['AdPosition_en'];
								?>
									<tr>
										<td width="85%"><?=$AdPosition;?></td>
										<td width="15%" class="center">
											<a class="tip_ico tip_min_ico" href="./?m=content&a=ad&d=edit&AId=<?=$v2['AId'];?>" label="{/global.edit/}"><img src="/static/ico/edit.png" alt="{/global.edit/}" align="absmiddle" /></a>
											<!--<a href="./?do_action=content.ad_del&AId=<?=$v2['AId'];?>" title="{/global.del/}" class="del" rel="del"><img src="/static/ico/del.png" alt="{/global.del/}" align="absmiddle" /></a>-->
										</td>
									</tr>
								<?php }?>
							</table>
						</td>
					</tr>
				<?php }?>
			</tbody>
		</table>
	<?php }elseif($c['manage']['do']=='add'){?>
		<script language="javascript">$(function(){content_obj.ad_add_init()});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/ad.ad.pagename/}</label>
				<span class="input"><input name="PageName" value="" type="text" class="form_input" size="35" maxlength="100" notnull /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/ad.ad.version/}</label>
				<span class="input">
					<select name="version">
						<option value="0">{/ad.ad.version_n.0/}</option>
						<option value="1">{/ad.ad.version_n.1/}</option>
					</select>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows" id="pagetype" style="display:none;">
				<label>{/ad.ad.pagetype/}</label>
				<span class="input">
					<select name="pagetype">
						<option value="0">{/ad.ad.pagetype_n.0/}</option>
						<option value="1">{/ad.ad.pagetype_n.1/}</option>
					</select>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/ad.ad.position/}</label>
				<span class="input"><input name="AdPosition" value="" type="text" class="form_input" size="20" maxlength="100" notnull /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/ad.ad.type/}</label>
				<span class="input">
					<select name="AdType">
						<option value="0">{/ad.ad.type_ary.0/}</option>
						<option value="1">{/ad.ad.type_ary.1/}</option>
					</select>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows" id="pic_qty">
				<label>{/ad.ad.pic_qty/}</label>
				<span class="input">
					<select name="PicCount">
						<?php for($i=1; $i<=5; $i++){?>
						<option value="<?=$i;?>"><?=$i;?></option>
						<?php }?>
					</select>
				</span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/ad.ad.width/}</label>
				<span class="input"><input name="Width" value="" type="text" class="form_input" size="5" maxlength="10" rel="amount" /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/ad.ad.height/}</label>
				<span class="input"><input name="Height" value="" type="text" class="form_input" size="5" maxlength="10" rel="amount" /></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=content&a=ad" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
			<input type="hidden" name="do_action" value="content.ad_add" />
		</form>
	<?php }elseif($c['manage']['do']=='edit'){
		//编辑
		$AId=(int)$_GET['AId'];
		$ad_row=db::get_one('ad', "AId='$AId'");
		!$ad_row && js::location('./?m=content&a=ad');
		
		$previewStyle="width:".($ad_row['Width']?$ad_row['Width'].'px':'100%')."; height:".($ad_row['Height']?$ad_row['Height'].'px':'100%').";";
		$ad_ary=array();
		for($i=0; $i<$ad_row['PicCount']; ++$i){
			$ad_ary['Name'][$i]=str::str_code(str::json_data($ad_row['Name_'.$i], 'decode'));
			$ad_ary['Brief'][$i]=str::str_code(str::json_data($ad_row['Brief_'.$i], 'decode'));
			$ad_ary['Url'][$i]=str::str_code(str::json_data($ad_row['Url_'.$i], 'decode'));
			$ad_ary['PicPath'][$i]=str::str_code(str::json_data($ad_row['PicPath_'.$i], 'decode'));
		}
		
		$PageName=$ad_row['PageName'];
		$c['manage']['config']['ManageLanguage']=='en' && $PageName=$ad_row['PageName_en'];
		$AdPosition=$ad_row['AdPosition'];
		$c['manage']['config']['ManageLanguage']=='en' && $AdPosition=$ad_row['AdPosition_en'];
	?>
    	<?=ly200::load_static('/static/js/plugin/ckeditor/ckeditor.js', '/static/js/plugin/dragsort/dragsort-0.5.1.min.js');?>
    	<script language="javascript">$(function(){content_obj.ad_edit_init()});</script>
		<form id="edit_form" class="r_con_form">
			<div class="rows">
				<label>{/ad.ad.pagename/}</label>
				<span class="input"><?=$PageName;?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/ad.ad.position/}</label>
				<span class="input"><?=$AdPosition;?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/ad.ad.width/}</label>
				<span class="input"><?=$ad_row['Width']?$ad_row['Width'].'px':'auto';?></span>
				<div class="clear"></div>
			</div>
			<div class="rows">
				<label>{/ad.ad.height/}</label>
				<span class="input"><?=$ad_row['Height']?$ad_row['Height'].'px':'auto';?></span>
				<div class="clear"></div>
			</div>
			<?php if($ad_row['AdType']==0){?>
				<?php if($ad_row['PicCount']>1){?>
					<div class="rows">
						<label>{/ad.ad.showtype/}</label>
						<span class="input">
							<select name="ShowType">
								<?php for($i=1;$i<4;$i++){?><option value="<?=$i;?>"<?=$i==$ad_row['ShowType']?' selected':'';?>>{/ad.ad.show.<?=$i;?>/}</option><?php }?>
							</select>
						</span>
						<div class="clear"></div>
					</div>
				<?php }?>
				
				<div class="rows">
                    <label>{/ad.ad.photo/}</label>
                    <span class="input tab_box">
                        <?=manage::html_tab_button('border');?>
                        <div class="blank9"></div>
                        <?php foreach($c['manage']['config']['Language'] as $k=>$v){?>
                            <div class="tab_txt tab_txt_<?=$k;?>">
                                <ul id="PicDetail_<?=$v;?>" class="ad_drag">
                                    <?php
                                    for($i=0; $i<$ad_row['PicCount']; ++$i){
                                        $pic_path=@is_file($c['root_path'].$ad_ary['PicPath'][$i][$v])?$ad_ary['PicPath'][$i][$v]:'';
                                    ?>
                                    <li class="adpic_row clean">
                                        <span class="multi_img upload_file_multi fl">
                                            <dl class="img" num="<?=$i;?>" lang="<?=$v;?>">
                                                <dt class="upload_box preview_pic">
                                                    <input type="button" id="PicUpload_<?=$i;?>" lang="<?=$v;?>" class="btn_ok upload_btn" name="submit_button" value="{/global.upload_pic/}" data-count="<?=$ad_row['PicCount']?>" tips="<?=sprintf(manage::language('{/notes.pic_tips/}'), $ad_row['PicCount']);?>" />
                                                    <input type="hidden" class="picpath" name="PicPath_<?=$v;?>[]" value="<?=$pic_path;?>" save="<?=is_file($c['root_path'].$pic_path)?1:0;?>" />
                                                </dt>
                                                <dd class="pic_btn">
                                                    <a href="javascript:;" label="{/global.edit/}" class="tip_ico tip_min_ico edit" lang="<?=$v;?>" data-count="<?=$ad_row['PicCount']?>"><img src="/static/ico/edit.png" align="absmiddle" /></a>
                                                    <a href="javascript:;" label="{/global.del/}" class="tip_ico tip_min_ico del" rel="del"><img src="/static/ico/del.png" align="absmiddle" /></a>
                                                </dd>
                                            </dl>
                                        </span>
                                        <div class="fl ad_info">
                                            <span class="price_input"><b>{/ad.ad.name/}<div class='arrow'><em></em><i></i></div></b><input name="Name_<?=$v;?>[]" value="<?=$ad_ary['Name'][$i][$v];?>" type="text" class="form_input" size="50" maxlength="50" /></span>
                                            <span class="price_input"><b>{/ad.ad.brief/}<div class='arrow'><em></em><i></i></div></b><input name="Brief_<?=$v;?>[]" value="<?=$ad_ary['Brief'][$i][$v];?>" type="text" class="form_input" size="50" maxlength="100" /></span>
                                            <span class="price_input"><b>{/ad.ad.url/}<div class='arrow'><em></em><i></i></div></b><input name="Url_<?=$v;?>[]" value="<?=$ad_ary['Url'][$i][$v];?>" type="text" class="form_input" size="50" maxlength="200" /></span>
                                        </div>
                                        <div class="fl drag_bg"></div>
                                    </li>
                                    <?php }?>
                                </ul>
                            </div>
                        <?php }?>
                    </span>
                    <div class="clear"></div>
                </div>
			<?php }elseif($ad_row['AdType']==1){?>
				<div class="rows">
					<label>{/ad.ad.name/}</label>
					<span class="input"><input name="Name" value="<?=$ad_row['Name'];?>" type="text" class="form_input" size="50" maxlength="100" notnull /></span>
					<div class="clear"></div>
				</div>
				<div class="rows">
					<label>{/ad.ad.contents/}</label>
					<span class="input"><?=manage::Editor('Contents', $ad_row['Contents']);?></span>
					<div class="clear"></div>
				</div>
			<?php }?>
			<?php /*if($ad_row['AdType']!=1){?>
				<div class="rows">
					<label>{/ad.ad.preview/}</label>
					<span class="input">
						<div style=" <?=$previewStyle;?>"><?=ly200::ad($ad_row['Number']);?></div>
					</span>
					<div class="clear"></div>
				</div>
			<?php }*/?>
			<div class="rows">
				<label></label>
				<span class="input">
					<input type="submit" class="btn_ok submit_btn" name="submit_button" value="{/global.submit/}" />
					<a href="./?m=content&a=ad" class="btn_cancel">{/global.return/}</a>
				</span>
				<div class="clear"></div>
			</div>
            <input type="hidden" name="PicCount" value="<?=$ad_row['PicCount'];?>" />
            <input type="hidden" name="AdType" value="<?=$ad_row['AdType'];?>" />
            <input type="hidden" name="AId" value="<?=$AId;?>" />
			<input type="hidden" name="do_action" value="content.ad_edit" />
		</form>
    <?php }?>
</div>