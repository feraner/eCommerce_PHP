<?php
$OId=trim($_GET['OId']);

db::delete('shopping_excheckout', "{$c['where']['cart']} and OId='{$OId}'");

file::write_file('/_pay_log_/paypal_excheckout/log/'.date('Y_m/d/', $c['time']), "{$OId}-cancel.txt", date('Y-m-d H:i:s', $c['time'])."\r\n\r\n".print_r($_REQUEST, true)."\r\n\r\n".print_r($_SESSION['Gateway']['PaypalExcheckout'], true));	//把返回数据写入文件

unset($_SESSION['Gateway']);

js::location('/cart/');

exit();