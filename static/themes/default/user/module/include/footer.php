<?php !isset($c) && exit();?>
<div class="footer">
    <div class="img"><?=ly200::partners();?></div>
    <div class="copyright"><?=$c['config']['global']['CopyRight']['CopyRight'.$c['lang']];?> &nbsp;&nbsp;&nbsp;&nbsp; <?=$c['powered_by'];?></div>
</div>
<?php
include("{$c['root_path']}/inc/lib/global/onlineChat.php");
echo ly200::out_put_third_code();
?>