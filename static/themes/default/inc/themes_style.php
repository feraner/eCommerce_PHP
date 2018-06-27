<?php !isset($c) && exit();?>
<style type="text/css">
#nav{width:630px; margin-left:40px; height:42px; position:relative;}
#nav .nav_menu{width:236px; position:absolute; left:0; top:0;}
#nav .nav_menu .nav_title{height:42px; line-height:42px;}
#nav .nav_menu .nav_title a{font-size:14px; color:#fff; padding-left:38px; display:block; text-decoration:none; font-weight:bold;}
#nav .nav_menu .nav_title a b{position:absolute; top:12px; right:12px; width:19px; height:19px; background:url(/static/themes/default/images/ico-img.png) right -185px no-repeat;}
#nav .nav_item{margin-left:236px; display:inline-block;}
#nav .nav_item li{float:left; line-height:42px; border-right:1px solid #357cbe;}
#nav .nav_item li a{text-align:center; color:#fff; padding:0 24px; display:inline-block; font-weight:bold; border-width:0px; border-right-width:1px; border-style:solid; text-decoration:none;}
</style>
<h3 class="rows_hd">{/themes.style.primary_color/}</h3>
<table width="100%" align="center" cellpadding="12" cellspacing="1" border="0" class="module_style_table">
    <tbody>
        <tr align="center">
            <td width="100">{/themes.style.module/}:</td>
            <td>{/themes.style.primary_color/}</td>
            <td>{/themes.style.price/}</td>
            <td>{/themes.style.cart_bg/}</td>
            <td>{/themes.style.buy_now_bg/}</td>
            <td>{/themes.style.review_bg/}</td>
            <td>{/themes.style.discount_bg/}</td>
        </tr>
        <tr align="center">
            <td>{/themes.style.color/}</td>
            <td><input type="text" class="form_input color" name="FontColor" value="<?=$data_ary['FontColor'];?>" /></td>
            <td><input type="text" class="form_input color" name="PriceColor" value="<?=$data_ary['PriceColor'];?>" /></td>
            <td><input type="text" class="form_input color" name="AddtoCartBgColor" value="<?=$data_ary['AddtoCartBgColor'];?>" /></td>
            <td><input type="text" class="form_input color" name="BuyNowBgColor" value="<?=$data_ary['BuyNowBgColor'];?>" /></td>
            <td><input type="text" class="form_input color" name="ReviewBgColor" value="<?=$data_ary['ReviewBgColor'];?>" /></td>
            <td><input type="text" class="form_input color" name="DiscountBgColor" value="<?=$data_ary['DiscountBgColor'];?>" /></td>
        </tr>
    </tbody>
</table>
<div class="rows_hd_blank"></div>
<h3 class="rows_hd">{/themes.style.nav_style/}</h3>
<table align="center" cellpadding="12" cellspacing="1" border="0" class="module_style_table">
    <thead>
        <tr>
            <td align="center" width="100">{/global.view/}:</td>
            <td colspan="5">
				<div class="NavBgColor">
					<div id="nav">
						<div class="nav_menu">
							<div class="nav_title CategoryBgColor"><a href="#" style="">All Categories<b></b></a></div>
						</div>
						<ul class="nav_item">
							<li class="NavBorderColor2"><a href="#" class="NavBorderColor1 NavHoverBgColor">Home</a></li>
							<li class="NavBorderColor2"><a href="#" class="NavBorderColor1 NavHoverBgColor">Products</a></li>
							<li class="NavBorderColor2"><a href="#" class="NavBorderColor1 NavHoverBgColor">Contact Us</a></li>
						</ul>
					</div>
				</div>
			</td>
        </tr>
    </thead>
    <tbody>
        <tr align="center">
            <td>{/themes.style.module/}:</td>
            <td>{/themes.style.nav_bg/}</td>
            <td>{/themes.style.nav_mouse/}</td>
            <td>{/themes.style.nav_border/}(1)</td>
            <td>{/themes.style.nav_border/}(2)</td>
            <td>{/themes.style.category_bg/}</td>
        </tr>
        <tr align="center">
            <td>{/themes.style.color/}:</td>
            <td><input type="text" class="form_input color" name="NavBgColor" value="<?=$data_ary['NavBgColor'];?>" /></td>
            <td><input type="text" class="form_input color" name="NavHoverBgColor" value="<?=$data_ary['NavHoverBgColor'];?>" /></td>
            <td><input type="text" class="form_input color" name="NavBorderColor1" value="<?=$data_ary['NavBorderColor1'];?>" /></td>
            <td><input type="text" class="form_input color" name="NavBorderColor2" value="<?=$data_ary['NavBorderColor2'];?>" /></td>
            <td><input type="text" class="form_input color" name="CategoryBgColor" value="<?=$data_ary['CategoryBgColor'];?>" /></td>
        </tr>
    </tbody>
</table>
<div class="rows_hd_blank"></div>
<h3 class="rows_hd">{/themes.style.prod_list/}</h3>
<table width="100%" align="center" cellpadding="12" cellspacing="1" border="0" class="module_style_table">
    <tbody>
        <tr align="center">
            <td width="100">{/themes.style.module/}:</td>
            <td>{/themes.style.effects_bg/}</td>
            <td>{/themes.style.effects_mouse/}</td>
        </tr>
        <tr align="center">
            <td>{/themes.style.color/}</td>
            <td><input type="text" class="form_input color" name="ProListBgColor" value="<?=$data_ary['ProListBgColor'];?>" /></td>
            <td><input type="text" class="form_input color" name="ProListHoverBgColor" value="<?=$data_ary['ProListHoverBgColor'];?>" /></td>
        </tr>
    </tbody>
</table>
<div class="rows_hd_blank"></div>
<h3 class="rows_hd">{/themes.style.prod_detail/}</h3>
<table width="100%" align="center" cellpadding="12" cellspacing="1" border="0" class="module_style_table">
    <tbody>
        <tr align="center">
            <td width="100">{/themes.style.module/}:</td>
            <td>{/themes.style.color_border/}</td>
            <td>{/themes.style.color_border_mouse/}</td>
        </tr>
        <tr align="center">
            <td>{/themes.style.color/}</td>
            <td><input type="text" class="form_input color" name="GoodBorderColor" value="<?=$data_ary['GoodBorderColor'];?>" /></td>
            <td><input type="text" class="form_input color" name="GoodBorderHoverColor" value="<?=$data_ary['GoodBorderHoverColor'];?>" /></td>
        </tr>
    </tbody>
</table>
<div class="rows_hd_blank"></div>