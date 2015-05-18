<?php

require_once __DIR__.'/Vendor.php';

class Item {
    public $vendor, $name, $desc, $quantity, $category, $price;

    function __construct($id, Vendor $vendor, \GuzzleHttp\Client $httpClient) {
        $res = $httpClient->get("ineed-db.mybluemix.net/api/items/{$id}");
        $itemJson = $res->json();
        //echo json_encode($itemJson, JSON_PRETTY_PRINT);

        $this->vendor = $vendor;
        $this->name = $itemJson['prodName'];
        $this->desc = $itemJson['prodDesc'];
        $this->quantity = $itemJson['quantity'];
        $this->category = $itemJson['category'];
        $this->price    = $itemJson['price'];
    }

}