<?php

require_once __DIR__.'/Vendor.php';

class Item {
    public $vendor, $prodName, $prodDesc, $quantity, $category, $price;

    function __construct($id, \GuzzleHttp\Client $httpClient) {
        $res = $httpClient->get("ineed-db.mybluemix.net/api/items/{$id}");
        $itemJson = $res->json();
        //echo json_encode($itemJson, JSON_PRETTY_PRINT);

        $this->vendor = new Vendor($itemJson['vendorId'], $httpClient);
        $this->prodName = $itemJson['prodName'];
        $this->prodDesc = $itemJson['prodDesc'];
        $this->quantity = $itemJson['quantity'];
        $this->category = $itemJson['category'];
        $this->price    = $itemJson['price'];
    }

}