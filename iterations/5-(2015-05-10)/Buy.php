<?php 

CONST MEMBER_ID = "memberId";
CONST PRODUCTS = "products";
CONST QUANTITY = "qty";
CONST PRODUCT_ID = "code";
CONST UNIT_PRICE = "unitPrice";
CONST PICKUP_LOCATION = "pickUpLocation";

function getAllProducts(){
    $products = array("0"=>(array("0" => 1,"id" => 1, "1" => "code 001",  "product_code" => "code 001","2" => "Wrist Watch",
    "product_name" => "Wrist Watch", "3" => "This watch iz the best bruh","product_desc" => "This watch iz the best bruh",  
     "4" => "wrist-watch.jpg", "product_img_name" => "wrist-watch.jpg",  "5" => "20.00",   "price" => "20.00")), 
    "1"=>(array("0" => 2,"id" => 2, "1" => "code 002",  "product_code" => "code 002","2" => "android-phone",
    "product_name" => "android-phone", "3" => "This phone iz the best bruh","product_desc" => "This phone iz the best bruh",  
     "4" => "android-phone.jpg", "product_img_name" => "android-phone.jpg",  "5" => "20.00",   "price" => "20.00")), 
    "2"=>(array("0" => 3,"id" => 3, "1" => "code 003",  "product_code" => "code 003","2" => "external-hard-disk",
    "product_name" => "external-hard-disk", "3" => "This hard drive iz the best bruh","product_desc" => "This hard drive iz the best bruh",  
     "4" => "external-hard-disk.jpg", "product_img_name" => "external-hard-disk.jpg",  "5" => "20.00",   "price" => "20.00")));

    return $products;
}

function getProductInfo($productCode){
	$products = getAllProducts();
	foreach ($products as $key => $value) {
		if($value["product_code"] == $productCode)
			return $value;
	}
}

function getDetails ($order)
{
	$memberId = $order[MEMBER_ID];
	$orderDetails = getOrderDetails($order);
    $transactionDetails = getTransactionDetails($order[PRODUCTS],$order[MEMBER_ID]);
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

function getTransactionDetails ($order,$memberId)
{
	$transactions = array();
	foreach ($order as $key => $value) {
		error_log(print_r($value,true));
		$transactions[$key][QUANTITY] = $value[QUANTITY];
		$transactions[$key][PRODUCT_ID] = $value[PRODUCT_ID];
		$transactions[$key][MEMBER_ID] = $memberId;
		$transactions[$key][UNIT_PRICE] = getUnitPrice();
	}
	return $transactions;
}
function getUnitPrice ()
{
	return 20;
}

function createMediator(){
	return new OrderTransactionMediator();
}

function getVendorImage(){
	return "google.com";
}

function createOrderObject ( $orderDetails,$mediator)
{
	
	$total = 0;
	$orderNo = createOrderNo();
	$memberId = $orderDetails[MEMBER_ID];
	$vendorId = getVendorId();
	$vendorLink =getVendorLink();
	$image = getVendorImage();
	$pickUpLocation = $orderDetails[PICKUP_LOCATION];
	$orderPlacedDate = date('l jS \of F Y h:i:s A');
	$deal = null;
	$order = new Order($orderNo,$memberId,$vendorId,$mediator,$vendorLink,$pickUpLocation,$orderPlacedDate,$deal,$image);
	addOrderToDB($order,$orderNo);
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

function getImage(){
	return 'google.com';
}

function createTransactionObject ($transactionDetails,$orderDetails,$mediator){
	$memberId = $orderDetails["memberId"];
	$vendorId = $orderDetails["orderId"];		
	$transactions = array();
	foreach ($transactionDetails as $transactionDetail){
		$image = getImage();
		$quantity = $transactionDetail["qty"];
		$productId = $transactionDetail["code"];
		$unitPrice = $transactionDetail[UNIT_PRICE];
		$transaction[] = new Transaction(createTransactionNo(),$memberId,$vendorId,$productId,$quantity,$mediator,$unitPrice,$image);
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

