<?php 

CONST MEMBER_ID = "memberId";
CONST PRODUCTS = "products";
CONST QUANTITY = "qty";
CONST PRODUCT_ID = "code";
CONST UNIT_PRICE = "unitPrice";
CONST PICKUP_LOCATION = "pickUpLocation";
function getDetails ($order)
{
	$memberId = $order[MemberId];
	$orderDetails = getOrderDetails($order);
    $transactionDetails = getTransactionDetails($order[PRODUCTS]);
    $mediator =  createMediator();
    createTransactionObject($transactionDetails,$orderDetails,$mediator);
    createOrderObject($orderDetails,$mediator);
}

function getOrderDetails($order)
{
	$orderDetails= array();
	$orderDetails = $order[MEMBER_ID];
	$orderDetails =$order[PICKUP_LOCATION];
}

function getTransactionDetails ($order)
{
	$transactions = array();
	foreach ($order as $key => $value) {
		$transactions[$key][QUANTITY] = $value[QUANTITY];
		$transactions[$key][PRODUCT_ID] = $value[PRODUCT_ID];
		$transactions[$key][MEMBER_ID] = $value[MEMBER_ID];
		$transactions[$key][UNIT_PRICE] = getUnitPrice();
	}
}
function getUnitPrice ()
{
	return 20;
}

function createMediator(){
	return new OrderTransactionMediator();
}

function createOrderObject ( $orderDetails,$mediator)
{
	
	$total = 0;
	$orderNo = createOrderNo();
	$memberId = $orderDetails[MEMBER_ID];
	$vendorId = getVendordId();
	$vendorLink =getVendorLink();
	$pickUpLocation = $orderDetails[PICKUP_LOCATION];
	$orderPlacedDate = date('l jS \of F Y h:i:s A');
	$deal = null;
	$order = new Order($orderNo,$memberId,$vendorId,$mediator,$vendorLink,$pickUpLocation,$orderPlacedDate,$deal);
	addOrderToDB($order);
}


function getVendorLink(){
	return "google.com";
}
function getVendorId(){
	return 2;
}

function createOrderNo(){
	return 1;
}
function createTransactionObject ($transactionDetails,$orderDetails,$mediator){
	$memberId = $orderDetails["memberId"];
	$vendorId = $orderDetails["orderId"];		
	$transactions = array();
	foreach ($transactionDetails as $transactionDetail){
		$quantity = $transactionDetail["qty"];
		$productId = $transactionDetail["code"];
		$unitPrice = $transactions["unitPrice"];
		$transaction[] = new Transaction(createTransactionNo(),$memberId,$vendorId,$productId,$quantity,$mediator,$unitPrice);
	     //	addTransactionToDB($transaction,$orderNo);		
        } 	  
}

function createTransactionNo(){
	return 1;
}

function postData($order){ 
	$dealId = "";
	$dealDiscount =0;           
	if($order->getDeal() == null)
	{	
		$dealId = "";
		$dealDiscount = 0;
	}
	else{
		$dealId = $order->getDeal()->getId();
		$dealDiscount = $order->getDeal()->getDiscount();
	}
	$data = $arrayName = array('paymentType' => 'credit', 'vendorId' => $order->getVendor(),
	 'tax' => $order->getTax(), 'dealId'=> $dealId, 'dealDiscount' => $dealDiscount);                                                     
	$data_string = json_encode($data);                                                                                    
	$ch = curl_init('https://ineed-db.mybluemix.net/api/orders');                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                                          
	    'Content-Type: application/json',                                                                                
	    'Content-Length: ' . strlen($data_string))                                                                       
);                                                                                                                   
 
$result = curl_exec($ch);
}

function addTransactionToDB ($transaction, $orderNo)
{
	$file = 'transaction.txt';
	$transactionDetails =  $transaction["TransactionNo"]+"#"+$transaction["OrderNo"]+ "#"+$transaction["code"] +
	 "#" +$transaction["UnitPrice"] + "#" + $transaction["qty"] + "#" +  $transaction["Store"]; 
	file_put_contents($file,$transactionDetails,FILE_APPEND);
}

function addOrderToDB ($order , $orderNo){
	postData($order);
}

function getTransactionDetailsFromDB()
{
	
}

