<?php

require_once __DIR__.'/Item.php';

class Deal {
    /** @var \GuzzleHttp\Client $httpClient */
    private $httpClient;
    /** @var string $id */
    private $id;
    /** @var string $name */
    private $name;
    /** @var Vendor $vendor */
    private $vendor;
    /** @var Item[] $items */
    private $items = array();
    /** @var string $type */
    private $type;
    /** @var double $discount */
    private $discount;
    /** @var int $redeemCount */
    private $redeemCount;
    /** @var double $price */
    private $price;
    /** @var string $expireDate */
    private $expireDate;
    /** @var int $sendCount */
    private $sendCount;

    function __construct() {
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
                $item = new Item($itemID, $this->httpClient);
                array_push($this->items, $item);
            }
            catch(\GuzzleHttp\Exception\ClientException $ex) {
                // If item ID not found in DB, move on to next item
                continue;
            }

        }
    }

    /**
     * @return string
     */
    public function getID() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return Vendor
     */
    public function getVendor() {
        return $this->vendor;
    }

    /**
     * @return Item[]
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return float
     */
    public function getDiscount() {
        return $this->discount;
    }

    /**
     * @return int
     */
    public function getRedeemCount() {
        return $this->redeemCount;
    }

    /**
     * @return float
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * @return string
     */
    public function getExpireDate() {
        return $this->expireDate;
    }

    /**
     * @return int
     */
    public function getSendCount() {
        return $this->sendCount;
    }

}