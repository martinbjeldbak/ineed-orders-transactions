<?php
require_once __DIR__.'/paypal.class.php';

function makePayment($price, $PayPalReturnURL, $PayPalCancelURL){
error_log("making payment ".$price);
$PayPalMode 			= 'sandbox'; // sandbox or live
$PayPalApiUsername 		= 'ssuryana-facilitator_api1.eng.ucsd.edu'; //PayPal API Username
$PayPalApiPassword 		= 'LL2VJL7824HSBHSL'; //Paypal API password
$PayPalApiSignature 	= 'AiPC9BjkCyDFQXbSkoZcgqH3hpacAy6mPRw.LrSVPmwTXbqHPAybey9k'; //Paypal API Signature
$PayPalCurrencyCode 	= 'USD'; //Paypal Currency Code

$paypalmode = ($PayPalMode=='sandbox') ? '.sandbox' : '';

$obj =array();
$obj["product_name"]  = "payment";
$obj["item_code"] = "01";
$obj["price"]= $price;

if($_POST) //Post Data received from product list page.
{
        error_log("inside post");
    //Other important variables like tax, shipping cost
	$TotalTaxAmount 	= 0; 
	$HandalingCost 		= 0; 
	$InsuranceCost 		= 0; 
	$ShippinDiscount 	= 0; 
	$ShippinCost 		= 0; 

	$paypal_data ='';
	$ItemTotalPrice = 0;

	$subtotal =$price;
	$ItemTotalPrice = $ItemTotalPrice + $subtotal;
	
    
  //  $product_code 	= filter_var($_POST['item_code'][$key], FILTER_SANITIZE_STRING); 
	
	//$results = $mysqli->query("SELECT product_name, product_desc, price FROM products WHERE product_code='$product_code' LIMIT 1");
	//$obj = $results->fetch_object();
//	$obj = getProductInfo($product_code);

	$key = 1;

    $paypal_data .= '&L_PAYMENTREQUEST_0_NAME'.$key.'='.urlencode($obj["product_name"]);
    $paypal_data .= '&L_PAYMENTREQUEST_0_NUMBER'.$key.'='.urlencode($obj['item_code']);
    $paypal_data .= '&L_PAYMENTREQUEST_0_AMT'.$key.'='.urlencode($obj["price"]);		
    
	//create items for session
	$paypal_product['items'][] = array('itm_name'=>$obj["product_name"],
										'itm_price'=>$obj["price"],
										'itm_code'=>$obj['item_code'], 
										'itm_qty'=>1
										);

	
	//Grand total including all tax, insurance, shipping cost and discount
	$GrandTotal = ($ItemTotalPrice + $TotalTaxAmount + $HandalingCost + $InsuranceCost + $ShippinCost + $ShippinDiscount);
	
								
	$paypal_product['assets'] = array('tax_total'=>$TotalTaxAmount, 
								'handaling_cost'=>$HandalingCost, 
								'insurance_cost'=>$InsuranceCost,
								'shippin_discount'=>$ShippinDiscount,
								'shippin_cost'=>$ShippinCost,
								'grand_total'=>$GrandTotal);
	
	//create session array for later use
	$_SESSION["paypal_products"] = $paypal_product;
	
	//Parameters for SetExpressCheckout, which will be sent to PayPal
	$padata = 	'&METHOD=SetExpressCheckout'.
				'&RETURNURL='.urlencode($PayPalReturnURL ).
				'&CANCELURL='.urlencode($PayPalCancelURL).
				'&PAYMENTREQUEST_0_PAYMENTACTION='.urlencode("SALE").
				$paypal_data.				
				'&NOSHIPPING=0'. //set 1 to hide buyer's shipping address, in-case products that does not require shipping
				'&PAYMENTREQUEST_0_ITEMAMT='.urlencode($ItemTotalPrice).
				'&PAYMENTREQUEST_0_TAXAMT='.urlencode($TotalTaxAmount).
				'&PAYMENTREQUEST_0_SHIPPINGAMT='.urlencode($ShippinCost).
				'&PAYMENTREQUEST_0_HANDLINGAMT='.urlencode($HandalingCost).
				'&PAYMENTREQUEST_0_SHIPDISCAMT='.urlencode($ShippinDiscount).
				'&PAYMENTREQUEST_0_INSURANCEAMT='.urlencode($InsuranceCost).
				'&PAYMENTREQUEST_0_AMT='.urlencode($GrandTotal).
				'&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($PayPalCurrencyCode).
				'&LOCALECODE=GB'. //PayPal pages to match the language on your website.
				//'&LOGOIMG=http://www.sanwebe.com/wp-content/themes/sanwebe/img/logo.png'. //site logo
				'&CARTBORDERCOLOR=FFFFFF'. //border color of cart
				'&ALLOWNOTE=1';
		
		//We need to execute the "SetExpressCheckOut" method to obtain paypal token
		$paypal= new MyPayPal();
		$httpParsedResponseAr = $paypal->PPHttpPost('SetExpressCheckout', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);
		
		//Respond according to message we receive from Paypal
		if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
		{
                                error_log("success");
				//Redirect user to PayPal store with Token received.
			 	$paypalurl ='https://www'.$paypalmode.'.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token='.$httpParsedResponseAr["TOKEN"].'';
				header('Location: '.$paypalurl);
                                die();
		}
		else
		{
			//Show error message
			echo '<div style="color:red"><b>Error : </b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
			echo '<pre>';
			print_r($httpParsedResponseAr);
			echo '</pre>';
		}

}
}