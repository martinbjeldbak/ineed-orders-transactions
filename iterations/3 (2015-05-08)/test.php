<html>
<body>

<h1>Welcome to a test!</h1>

<?php 
include 'JSONObject.php';
include 'Order.php';
include 'Transaction.php';

// test data, replace with GETs to DB
$orderData = array(
    "paymentType" => "credit",
    "vendorId" => "553c40993d5ce8cc1e455bd4",
    "total" => 8.99,
	"tax" => 0.08,
	"dealId" => "553dd674641ddfd849dae1ea",
	"dealDiscount" => "0.15"
);
$orderJsonString = json_encode($orderData);

$transactionData = array(
	"orderId" => "55465be1aa2b104525207007",
	"itemId" => "55365ce1ab2b1c4525207001",
	"quantity" => 1,
	"unitPrice" => 8.99,
	"vendorId" => "553c40993d5ce8cc1e455bd4",
	"dealId" => "553dd674641ddfd849dae1ea",
	"dealDiscount" => 0.15
);
$transactionJsonString = json_encode($transactionData);
	
function print_r2($val){
        echo '<pre>';
        print_r($val);
        echo '</pre>';
}

$order = new Order($orderJsonString);
print_r2($order);

$transaction = new transaction($transactionJsonString);
print_r2($transaction);
?>

</body>
</html>