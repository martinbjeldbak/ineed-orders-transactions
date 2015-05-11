<?php

$order = array (
		"0" => array (	"memberId" => 123123,
				"productId" => 123123,
				"quantity" => 12
				),
		"1" => array (
				"memberId" => 123123,
                                "productId" => 123123,
                                "quantity" => 12

				)
			);

$data =  array('paymentType' => 'credit', 'vendorId' => "553c40993d5ce8cc1e455bd4", 'total' => 2134,
         'tax' => 123, 'dealId'=> "553dd674641ddfd849dae1ea", 'dealDiscount' => 12321);
        $data_string = json_encode($data);
print_r($data_string);
$ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_string))
);
curl_setopt($ch,CURLOPT_URL,'https://ineed-db.mybluemix.net/api/orders');
$result = curl_exec($ch);
$info = curl_getinfo($ch);
print_r($info);
$json=json_decode($result,true);
curl_close($ch);
print_r($json);
