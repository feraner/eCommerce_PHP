<?php !isset($c) && exit();?>
<div class="goods_search wrapper">
    <div class="form">
        <form action="<?=$c['mobile_url'];?>/products/" method="get">
            <input type="search" value="" name="Keyword" placeholder="<?=$c['lang_pack']['search'];?>" class="text">
        </form>
    </div>
</div>
