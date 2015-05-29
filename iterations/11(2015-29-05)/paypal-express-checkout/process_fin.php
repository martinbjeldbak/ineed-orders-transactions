<?php

//Paypal redirects back to this page using ReturnURL, We should receive TOKEN and Payer ID
function finishPayment($token, $payer_id,$httpClient) {
    $PayPalMode 			= 'sandbox'; // sandbox or live
    $PayPalApiUsername 		= 'ssuryana-facilitator_api1.eng.ucsd.edu'; //PayPal API Username
    $PayPalApiPassword 		= 'LL2VJL7824HSBHSL'; //Paypal API password
    $PayPalApiSignature 	= 'AiPC9BjkCyDFQXbSkoZcgqH3hpacAy6mPRw.LrSVPmwTXbqHPAybey9k'; //Paypal API Signature
    $PayPalCurrencyCode 	= 'USD'; //Paypal Currency Code
    //we will be using these two variables to execute the "DoExpressCheckoutPayment"
    //Note: we haven't received any payment yet.

    //get session variables
    $paypal_product = $_SESSION["paypal_products"];
    $paypal_data = '';
    $ItemTotalPrice = 0;

    foreach($paypal_product['items'] as $key=>$p_item)
    {
        $paypal_data .= '&L_PAYMENTREQUEST_0_QTY'.$key.'='. urlencode($p_item['itm_qty']);
        $paypal_data .= '&L_PAYMENTREQUEST_0_AMT'.$key.'='.urlencode($p_item['itm_price']);
        $paypal_data .= '&L_PAYMENTREQUEST_0_NAME'.$key.'='.urlencode($p_item['itm_name']);
        $paypal_data .= '&L_PAYMENTREQUEST_0_NUMBER'.$key.'='.urlencode($p_item['itm_code']);

        // item price X quantity
        $subtotal = ($p_item['itm_price']*$p_item['itm_qty']);

        //total price
        $ItemTotalPrice = ($ItemTotalPrice + $subtotal);
    }

    $padata = 	'&TOKEN='.urlencode($token).
        '&PAYERID='.urlencode($payer_id).
        '&PAYMENTREQUEST_0_PAYMENTACTION='.urlencode("SALE").
        $paypal_data.
        '&PAYMENTREQUEST_0_ITEMAMT='.urlencode($ItemTotalPrice).
        '&PAYMENTREQUEST_0_TAXAMT='.urlencode($paypal_product['assets']['tax_total']).
        '&PAYMENTREQUEST_0_SHIPPINGAMT='.urlencode($paypal_product['assets']['shippin_cost']).
        '&PAYMENTREQUEST_0_HANDLINGAMT='.urlencode($paypal_product['assets']['handaling_cost']).
        '&PAYMENTREQUEST_0_SHIPDISCAMT='.urlencode($paypal_product['assets']['shippin_discount']).
        '&PAYMENTREQUEST_0_INSURANCEAMT='.urlencode($paypal_product['assets']['insurance_cost']).
        '&PAYMENTREQUEST_0_AMT='.urlencode($paypal_product['assets']['grand_total']).
        '&PAYMENTREQUEST_0_CURRENCYCODE='.urlencode($PayPalCurrencyCode);

    //We need to execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
    $paypal= new MyPayPal();
    error_log("hi + ".$PayPalMode);
    $httpParsedResponseAr = $paypal->PPHttpPost('DoExpressCheckoutPayment', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);

    //Check if everything went ok..
    if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
    {

        echo '<h2>Success</h2>';
        echo 'Your Transaction ID : '.urldecode($httpParsedResponseAr["PAYMENTINFO_0_TRANSACTIONID"]);

        /*
        //Sometimes Payment are kept pending even when transaction is complete.
        //hence we need to notify user about it and ask him manually approve the transiction
        */

        if('Completed' == $httpParsedResponseAr["PAYMENTINFO_0_PAYMENTSTATUS"])
        {
            echo '<div style="color:green">Payment Received! Your product will be sent to you very soon!</div>';
        }
        elseif('Pending' == $httpParsedResponseAr["PAYMENTINFO_0_PAYMENTSTATUS"])
        {
            echo '<div style="color:red">Transaction Complete, but payment is still pending! '.
                'You need to manually authorize this payment in your <a target="_new" href="http://www.paypal.com">Paypal Account</a></div>';
        }

        // we can retrive transection details using either GetTransactionDetails or GetExpressCheckoutDetails
        // GetTransactionDetails requires a Transaction ID, and GetExpressCheckoutDetails requires Token returned by SetExpressCheckOut
        $padata = 	'&TOKEN='.urlencode($token);
        $paypal= new MyPayPal();
        $httpParsedResponseAr = $paypal->PPHttpPost('GetExpressCheckoutDetails', $padata, $PayPalApiUsername, $PayPalApiPassword, $PayPalApiSignature, $PayPalMode);

        if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"]))
        {
		
            $memberEmail = $_COOKIE["memberEmail"];
            $products = $_SESSION["products"];
            $member = new Member($memberEmail,$httpClient);
            $order = new Order ('seb/mar testing', $member, $httpParsedResponseAr["AMT"],0 , $httpClient );	
            foreach ($products as $key => $product) {
                    $item = new Item ($product["id"],$httpClient);
                    $qty = $product["qty"];
                    $order->addTransaction($item, $item->getVendor(),$qty);	
            }
            $order->placeOrder();				
            /*
            #### SAVE BUYER INFORMATION IN DATABASE ###
            //see (http://www.sanwebe.com/2013/03/basic-php-mysqli-usage) for mysqli usage
            //use urldecode() to decode url encoded strings.

            $buyerName = urldecode($httpParsedResponseAr["FIRSTNAME"]).' '.urldecode($httpParsedResponseAr["LASTNAME"]);
            $buyerEmail = urldecode($httpParsedResponseAr["EMAIL"]);

            //Open a new connection to the MySQL server
            $mysqli = new mysqli('host','username','password','database_name');

            //Output any connection error
            if ($mysqli->connect_error) {
                die('Error : ('. $mysqli->connect_errno .') '. $mysqli->connect_error);
            }

            $insert_row = $mysqli->query("INSERT INTO BuyerTable
            (BuyerName,BuyerEmail,TransactionID,ItemName,ItemNumber, ItemAmount,ItemQTY)
            VALUES ('$buyerName','$buyerEmail','$transactionID','$ItemName',$ItemNumber, $ItemTotalPrice,$ItemQTY)");

            if($insert_row){
                print 'Success! ID of last inserted record is : ' .$mysqli->insert_id .'<br />';
            }else{
                die('Error : ('. $mysqli->errno .') '. $mysqli->error);
            }

            */
        } else  {
            echo '<div style="color:red"><b>GetTransactionDetails failed:</b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
            echo '<pre>';
            print_r($httpParsedResponseAr);
            echo '</pre>';

        }

    }else{
        echo '<div style="color:red"><b>Error : </b>'.urldecode($httpParsedResponseAr["L_LONGMESSAGE0"]).'</div>';
        echo '<pre>';
        print_r($httpParsedResponseAr);
        echo '</pre>';
    }
}
