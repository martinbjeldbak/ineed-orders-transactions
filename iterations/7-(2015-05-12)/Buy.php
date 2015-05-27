<?php 

CONST MEMBER_ID = "memberId";
CONST PRODUCTS = "products";
CONST QUANTITY = "qty";
CONST PRODUCT_ID = "code";
CONST UNIT_PRICE = "unitPrice";
CONST PICKUP_LOCATION = "pickUpLocation";
CONST VENDOR_ID = "vendorId";
CONST VENDOR_LINK = "vendorLink";
CONST VENDOR_IMAGE = "vendorImage";
CONST ORDER_PLACED_DATE = "orderPlacedDate";
CONST IMAGE = "image";
function getAllProducts(){
    $products = array("0"=>(array("0" => 1,"id" => 1, "1" => "code 001",  "product_code" => "55365ce1ab2b1c4525207001","2" => "Wrist Watch",
    "product_name" => "Wrist Watch", "3" => "This watch iz the best bruh","product_desc" => "This watch iz the best bruh",  
     "4" => "wrist-watch.jpg", "product_img_name" => "wrist-watch.jpg",  "5" => "20.00",   "price" => "20.00")), 
    "1"=>(array("0" => 2,"id" => 2, "1" => "code 002",  "product_code" => "55365ce1ab2b1c4525207002","2" => "android-phone",
    "product_name" => "android-phone", "3" => "This phone iz the best bruh","product_desc" => "This phone iz the best bruh",  
     "4" => "android-phone.jpg", "product_img_name" => "android-phone.jpg",  "5" => "20.00",   "price" => "20.00")), 
    "2"=>(array("0" => 3,"id" => 3, "1" => "code 003",  "product_code" => "55365ce1ab2b1c4525207003","2" => "external-hard-disk",
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
	$order = createOrderObject($orderDetails,$mediator);
    createTransactionObject($transactionDetails,$order,$mediator);
}

function getOrderDetails($order)
{
	$orderDetails= array();
	$orderDetails[MEMBER_ID] = $order[MEMBER_ID];
	$orderDetails[PICKUP_LOCATION] = $order[PICKUP_LOCATION];
	$orderDetails[VENDOR_ID] = getVendorId();
	$orderDetails[VENDOR_LINK] = getVendorLink();
	$orderDetails[VENDOR_IMAGE] = getImage();
	$orderDetails[ORDER_PLACED_DATE] = date('l jS \of F Y h:i:s A');
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
		$transactions[$key][IMAGE] = getImage();
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
	$memberId = $orderDetails[MEMBER_ID];
	$vendorId = $orderDetails[VENDOR_ID];
	$vendorLink = $orderDetails[VENDOR_LINK];
	$image = $orderDetails[VENDOR_IMAGE];  
	$pickUpLocation = $orderDetails[PICKUP_LOCATION];
	$orderPlacedDate = $orderDetails[ORDER_PLACED_DATE];	
	$deal = null;
	$order = new Order($memberId,$vendorId,$mediator,$vendorLink,$pickUpLocation,$orderPlacedDate,$deal,$image);
	$order_id = addOrderToDB($order);
    $order->setOrderNo($order_id);
	addOrderStateToDB($order); // TODO: void method, fill this in with "created" status
	return $order;
}


function getVendorLink(){
	return "google.com";
}

function getImage(){
	return 'google.com';
}

function createTransactionObject ($transactionDetails,$order,$mediator){
	$memberId = $order->getMember();
	$vendorId = $order->getVendor();		
	$transactions = array();
	foreach ($transactionDetails as $transactionDetail){
		$image = getImage();
		$quantity = $transactionDetail[QUANTITY];
		$productId = $transactionDetail[PRODUCT_ID];
		$unitPrice = $transactionDetail[UNIT_PRICE];			
		$transaction = new Transaction($memberId,$vendorId,$productId,$quantity,$mediator,$unitPrice,$image);
	      $transaction_id = addTransactionToDB($transaction,$order);
        $transaction->setTransactionNo($transaction_id);
    }
}

function postData($data,$url){ 
	$data_string = json_encode($data);                                                                                   
	error_log("data string ********* ".print_r($data_string,true)); 
	$ch = curl_init($url);                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(                                                           
	    'Accept: application/json',               
	    'Content-Type: application/json',                                                                                
	    'Content-Length: ' . strlen($data_string))                                                                       
	);                                                                                                                   
 
	$result = curl_exec($ch);
	error_log("result from DB ".print_r($result,true));
    return json_decode($result, true);
}

function addTransactionToDB ($transaction, $order)
{
	$data = array("orderId" => $order->getOrderNo(), "itemId"=> $transaction->getProductId(), "quantity" => $transaction->getQuantity(), "unitPrice" => $transaction->getUnitPrice(), "vendorId" => $transaction->getVendor() );
	$url = 'https://ineed-db.mybluemix.net/api/transactions';
	$trans = postData($data,$url);
    return $trans['_id'];
}

function addOrderToDB ($order){
	if($order->getDeal() == null)
        { 
                $data =  array('paymentType' => 'credit', 'vendorId' => $order->getVendor(), 'total' => $order->getTotal() ,
         'tax' => $order->getTax());
        }
        else{
                $dealId = getDealId();
                $dealDiscount = 0;
                $dealDiscount = $order->getDeal()->getDiscount();
                $data =  array('paymentType' => 'credit', 'vendorId' => $order->getVendor(), 'total' => $order->getTotal() ,
         'tax' => $order->getTax(), 'dealId'=> $dealId, 'dealDiscount' => $dealDiscount);
        }
	$url = 'https://ineed-db.mybluemix.net/api/orders';
	$response = postData($data,$url);
    return $response['_id'];
}

function addOrderStateToDB($order){
					
}
function getTransactionDetailsFromDB()
{
	
}

