<?php
/*************************************************
APIError.php

Displays error parameters.

Called by DoDirectPaymentReceipt.php, TransactionDetails.php,
GetExpressCheckoutDetails.php and DoExpressCheckoutPayment.php.

*************************************************/

session_start();
$resArray=$_SESSION['Gateway']['PaypalExcheckout']['reshash']; 
?>

<html>
<head>
<title>PayPal API Error</title>
</head>

<body alink=#0000FF vlink=#0000FF>


<?php  //it will print if any URL errors 
	if(isset($_SESSION['Gateway']['PaypalExcheckout']['curl_error_no'])){ 
			$errorCode= $_SESSION['Gateway']['PaypalExcheckout']['curl_error_no'];
			$errorMessage=$_SESSION['Gateway']['PaypalExcheckout']['curl_error_msg'];
			unset($_SESSION['Gateway']);	
			//session_unset();
?>
    <center>
        <table width="280">
            <tr>
                <td colspan="2" class="header">The PayPal API has returned an error!</td>
            </tr>
            <tr>
                <td>Error Number:</td>
                <td><?php echo $errorCode; ?></td>
            </tr>
            <tr>
                <td>Error Message:</td>
                <td><?php echo $errorMessage; ?></td>
            </tr>
        </table>
    </center>
<?php } else {

/* If there is no URL Errors, Construct the HTML page with 
   Response Error parameters.   
   */
?>
    <center>
        <font size=2 color=black face=Verdana><b></b></font>
        <br><br>
    
        <b> PayPal API Error</b><br><br>
	
    	<?php 
    
		require 'ShowAllResponse.php';
		?>
    </center>		
<?php 
}// end else
//unset($_SESSION['reshash'], $resArray);
?>
<br>
<br>
<br>
<br>

</body>
</html>

