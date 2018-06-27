<?php !isset($c) && exit();?>
<?php 
/*
//默认数据
array(
	'default'=>'{"FontColor":"#005ab0","NavBgColor":"#005bae","NavHoverBgColor":"#004d93","CategoryBgColor":"#063f74"}',
)
{"FontColor":"#005AB0","NavBgColor":"#005AB0","NavHoverBgColor":"#004D93","NavBorderColor1":"#02468D","NavBorderColor2":"#357CBE","CategoryBgColor":"#063F74","PriceColor":"#005AB0","AddtoCartBgColor":"#005AAD","BuyNowBgColor":"#F28810","ReviewBgColor":"#F28810","DiscountBgColor":"#FE8A27"}
*/
?>
<style type="text/css">
#nav{width:100%; height:45px;}
#nav .nav{height:45px; overflow:hidden; border-left-width:1px; border-left-style:solid;}
#nav .nav .itemes{line-height:45px;}
#nav .nav .itemes .navlink{color:#fff; font-size:16px; font-family:Arial, Helvetica, sans-serif; display:inline-block; height:45px; line-height:45px; padding:0 16px; text-decoration:none; border-right-width:1px; border-right-style:solid;}
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
				<div id="nav" class="NavBgColor NavBorderColor1">
					<div class="w clean">
						<div class="nav fl">
							<div class="itemes fl"><a href="javascript:;" class="navlink NavHoverBgColor NavBorderColor1">Home</a></div>
							<div class="itemes fl"><a href="javascript:;" class="navlink NavHoverBgColor NavBorderColor1">Products</a></div>
							<div class="itemes fl"><a href="javascript:;" class="navlink NavHoverBgColor NavBorderColor1">Contact Us</a></div>
						</div>
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
            <td>{/themes.style.nav_border/}</td>
            <td>{/themes.style.category_bg/}</td>
        </tr>
        <tr align="center">
            <td>{/themes.style.color/}:</td>
            <td><input type="text" class="form_input color" name="NavBgColor" value="<?=$data_ary['NavBgColor'];?>" /></td>
            <td><input type="text" class="form_input color" name="NavHoverBgColor" value="<?=$data_ary['NavHoverBgColor'];?>" /></td>
            <td><input type="text" class="form_input color" name="NavBorderColor1" value="<?=$data_ary['NavBorderColor1'];?>" /></td>
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