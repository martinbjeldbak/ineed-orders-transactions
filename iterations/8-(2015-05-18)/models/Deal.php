<?php

require_once __DIR__.'/Item.php';

class Deal {
    private $httpClient;
    public $id, $name, $vendor, $items = array(), $type, $discount, $redeemCount, $price, $expireDate, $sendCount;

    function __construct()
    {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    function __construct2($id, \GuzzleHttp\Client $httpClient) {
        $this->httpClient = $httpClient;
        $res = $this->httpClient->get("http://ineed-dealqq.mybluemix.net/getOneDeal?deal_id={$id}");
        $dealJson = $res->json();

        $this->name = $dealJson['dealName'];
        $this->vendor = new Vendor($dealJson['vendorId'], $httpClient);;

        foreach($dealJson['itemSell'] as $itemID) {
            try {
                $item = new Item($itemID, $this->vendor, $this->httpClient);
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

    function __construct11($id, Vendor $vendor, $dealName, $discount, $expireDate, array $itemIDs, $price, $redeemCount, $sendCount, $type, \GuzzleHttp\Client $httpClient) {
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