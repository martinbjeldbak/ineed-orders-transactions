<?php

require_once __DIR__.'/Item.php';

class Deal {
    private $httpClient;
    public $id, $name, $vendor, $items = array(), $type, $discount, $redeemCount, $price, $expireDate, $sendCount;

    function __construct($id, \GuzzleHttp\Client $httpClient) {
        $this->httpClient = $httpClient;

        $res = $this->httpClient->get("http://ineed-dealqq.mybluemix.net/getOneDeal?deal_id={$id}");
        $dealJson = $res->json();

        $this->name = $dealJson['dealName'];
        $this->vendor = new Vendor($dealJson['vendorId'], $httpClient);

        foreach($dealJson['itemSell'] as $itemID) {
            try {
                $item = new Item($itemID, $this->httpClient);
                array_push($this->items, $item);
            }
            catch(\GuzzleHttp\Exception\ClientException $ex) {
                // If item ID not found in DB, move on to next item
                continue;
            }

        }

        $this->id = $dealJson['_id'];
        $this->type = $dealJson['type'];
        $this->discount = $dealJson['discount'];
        $this->redeemCount = $dealJson['redeemCount'];
        $this->price = $dealJson['price'];
        $this->expireDate = $dealJson['expireDate'];
        $this->sendCount = $dealJson['sendCount'];
    }

}