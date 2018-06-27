<?php !isset($c) && exit();?>
<div id="newsletter" class="newsletter sidebar_to">
	<h2 class="newsletter_title b_title FontColor"><?=$c['lang_pack']['newsletter_titleTo'];?></h2>
    <div class="newsletter_list b_main">
		<div class="info"><?=$c['lang_pack']['newsletter_notesFo'];?></div>
		<div class="form">
			<form id="newsletter_form" class="clearfix">
				<input type="text" class="text fl" name="Email" value="" notnull="" format="Email" />
				<input type="submit" class="button fl" value="<?=$c['lang_pack']['go'];?>" />
			</form>
		</div>
	</div>
</div>