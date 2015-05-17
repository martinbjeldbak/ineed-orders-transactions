<?php

require_once __DIR__.'/Item.php';

class Deal {
    private $httpClient;
    public $id, $name, $vendor, $items = array(), $type, $discount, $redeemCount, $price, $expireDate, $sendCount;

    function __construct($id, Vendor $vendor, $dealName, $discount, $expireDate, array $itemIDs, $price, $redeemCount, $sendCount, $type, \GuzzleHttp\Client $httpClient) {
        $this->httpClient = $httpClient;
        $this->name = $dealName;
        $this->vendor = $vendor;
        $this->id = $id;
        $this->discount = $discount;
        $this->redeemCount = $redeemCount;
        $this->price = $price;
        $this->expireDate = $expireDate;
        $this->sendCount = $sendCount;
        $this->type = $type;

        foreach($itemIDs as $itemID) {
            try {
                $item = new Item($itemID, $this->vendor, $this->httpClient);
                array_push($this->items, $item);
            }
            catch(\GuzzleHttp\Exception\ClientException $ex) {
                // If item ID not found in DB, move on to next item
                continue;
            }

        }
    }

}