<?php
$currency = '$'; //Currency sumbol or code

//db settings
$db_username = 'root';
$db_password = '';
$db_name = 'ineed';
$db_host = 'localhost';
$mysqli = new mysqli($db_host, $db_username, $db_password,$db_name);

//paypal settings
$PayPalMode 			= 'sandbox'; // sandbox or live
$PayPalApiUsername 		= 'paypaluser@somemail.com'; //PayPal API Username
$PayPalApiPassword 		= '190798792445'; //Paypal API password
$PayPalApiSignature 	= '797987709709709oipuiou98Eq.Gufar'; //Paypal API Signature
$PayPalCurrencyCode 	= 'USD'; //Paypal Currency Code
$PayPalReturnURL 		= 'http://localhost/shopping-cart/paypal-express-checkout/process.php'; //Point to process.php page
$PayPalCancelURL 		= 'http://localhost/shopping-cart/paypal-express-checkout/cancel_url.html'; //Cancel URL if user clicks cancel

include_once("Buy.php");
include_once("Order.class.php");
include_once("Transaction.class.php");
include_once("OrderState.class.php");
include_once("StateMediator.class.php");
include_once("TransactionState.class.php");
include_once("OrderTransactionMediator.class.php");
?>