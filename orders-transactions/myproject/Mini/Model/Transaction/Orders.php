<?php 

function getDetails ($order)
{
	$orderDetails = getOrderDetails($order);
        $transactionDetails = getTransactionDetails($order); 	 
}

function getOrderDetails($order)
{
	return;
}

function getTransactionDetails ($order)
{
	return;
}

function createOrderObject ($transactions, $orderDetails)
{
	
	$total = 0;
	foreach ($transactions  as $transaction){
		$total += $transaction["unitPrice"];						
	}
	$order = new Order($orderNo,$memberId,$vendorId,$total);
	addOrderToDB($order);
}
function createTransactionObject ($transactionDetails,$orderDetails){
	$memberId = $orderDetails["memberId"];
	$vendorId = $orderDetails["orderId"];		
	$transactions = array();
	foreach ($transactionDetails as $transactionDetail){
		$quantity = $transactionDetail["quantity"];
		$productId = $transactionDetail["productId"];
		$transaction[] = new Transaction($memberId,$vendorId,$productId,$quantity);
	     	addTransactionToDB($transaction,$orderNo);		
        } 	  
}

function addTransactionToDB ($transaction, $orderNo)
{
	$file = 'transaction.txt';
	$transactionDetails =  $transaction["TransactionNo"]+"#"+$transaction["OrderNo"]+ "#"+$transaction["ProductId"] +
	 "#" +$transaction["UnitPrice"] + "#" + $transaction["Quantity"] + "#" +  $transaction["Store"]; 
	file_put_contents($file,$transactionDetails,FILE_APPEND);
}

function addOrderToDB ($order , $orderNo){
	$file = "order.txt";
	$orderDetails = $order["OrderNo"] + "#" + $order["VendorId"] + "#" + $order["MemberId"];
	file_put_contents($file, $orderDetails,FILE_APPEND) ;
}

function getTransactionDetailsFromDB()
{
	
}

