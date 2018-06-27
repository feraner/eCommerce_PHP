<?php !isset($c) && exit();?>
<?php
$include="{$c['theme_path']}products/{$a}.php";
@is_file($include)?include($include):js::location('/');
?>