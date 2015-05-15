<?php
include_once("config.php");

$dealId = $_GET["dealId"] ;
$memberId = $_GET["memberId"];
//$pickUpLocation = $_GET["pickUpLocation"];
$pickUpLocation = "0";


$url = "http://ineed-dealqq.mybluemix.net/getOneDeal?deal_id=". $dealId;
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);

$dealResult = curl_exec($ch);

processResult ($dealResult ,  $memberId, $pickUpLocation);

function processResult($dealResult , $memberId, $pickUpLocation){
	if(($dealResult != null) && ($obj = json_decode($dealResult,true))  && (isset($obj["dealName"])) && (isset($obj["vendorName"])) &&
	(isset($obj["vendorId"]))  && isset($obj["price"])  &&  isset($obj["discount"]) 
	&& isset($obj["expireDate"]) && isset($obj["couponCode"])  && isset($obj["redeemCount"]) && isset($obj["itemSell"])  )
	{
		$vendorId = $obj["vendorId"];
		$price = $obj["price"];	
		$discount = $obj["discount"];
		$expireDate = $obj["expireDate"];
		$couponCode = $obj["couponCode"];
		$redeemCount = $obj["redeemCount"];
		$items = $obj["itemSell"];
		
		$orderDetails = array($pickUpLocation);
		$orderDetails[MEMBER_ID] =  $memberId;
		$orderDetails[PICKUP_LOCATION] = $pickUpLocation;										
		$orderDetails[VENDOR_ID] =  $vendorId;
		$orderDetails[VENDOR_LINK] = "";
		$orderDetails[VENDOR_IMAGE] = "";
		$orderDetails[ORDER_PLACED_DATE] = date('l jS \of F Y h:i:s A');
		$mediator = createMediator ();	
		$order = createOrderObject($orderDetails,$mediator);		

		$transactionDetails = array();
		foreach ($items as $key => $item){
			//need to make vendor call
			$transactionDetails[$key] = array();
			$transactionDetails[$key][QUANTITY] = 1;
			$transactionDetails[$key][PRODUCT_ID] = $item; 
			$transactionDetails[$key][UNIT_PRICE] = 0;	
		}
		createTransactionObject($transactionDetails,$order,$mediator);
		echo ("Order No : ". $order -> getOrderNo());
	}
	else{
		echo "DEAL TEAMS API ARE BROKEN";
	}
		
}

curl_close($ch);

