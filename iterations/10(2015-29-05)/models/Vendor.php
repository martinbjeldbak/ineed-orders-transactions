<?php

require_once __DIR__.'/Deal.php';
require_once __DIR__.'/Item.php';
require_once __DIR__.'/Order.php';
require_once __DIR__.'/Transaction.php';

class Vendor implements iNeedModel {
    /** @var \GuzzleHttp\Client $httpClient */
    private $httpClient;
    /** @var string $id */
    private $id;
    /** @var string $address */
    private $address;
    /** @var string $description */
    private $description;
    /** @var string $email */
    private $email;
    /** @var string $name */
    private $name;
    /** @var string $phoneNumber */
    private $phoneNumber;
    /** @var string $state */
    private $state;
    /** @var string $type */
    private $type;
    /** @var Item[] $items */
    private $items = array("If you see this, call updateItems()");
    /** @var Deal[] $deals  */
    private $deals = array("If you see this, call updateDeals()");

    public function __construct($id, \GuzzleHttp\Client $httpClient) {
        $this->httpClient = $httpClient;

        $res = $this->httpClient->get("https://ineed-db.mybluemix.net/api/vendors/{$id}");
        $vendorJson = $res->json();

        // Hacks... sometimes we might be given a null _id
        if(!array_key_exists('_id', $vendorJson)) {
            return;
        }

        $this->id = $vendorJson['_id'];
        $this->address = $vendorJson['address'];
        $this->description = $vendorJson['description'];
        $this->email = $vendorJson['email'];
        $this->name = $vendorJson['name'];
        $this->phoneNumber = $vendorJson['phoneNumber'];
        $this->state = $vendorJson['state'];
        $this->type = $vendorJson['type'];
    }

    public function getTransactionHistory() {
        $res = $this->httpClient->get("https://ineed-db.mybluemix.net/api/transactions?vendorId={$this->id}");
        $transactionsJson = $res->json();
        $transactions = array();

        if(empty($transactionsJson))
            return [];

        foreach($transactionsJson as $transactionJson) {
            $trans = Transaction::getTransactionFromId($transactionJson['_id'], $this->httpClient);

            if(is_null($trans))
                continue;
            array_push($transactions, $trans);
        }
        return $transactions;
    }

    /**
     * Ges all purchased deals from this vendor
     * @return Transaction[]
     */
    public function getPurchasedDeals() {
        $res = $this->httpClient->get("https://ineed-db.mybluemix.net/api/transactions?vendorId={$this->id}");
        $transactionsJson = $res->json();
        $transactions = array();

        if(empty($transactionsJson))
            return [];

        foreach($transactionsJson as $transactionJson) {
            $trans = Transaction::getTransactionFromId($transactionJson['_id'], $this->httpClient);

            if(is_null($trans) || !$trans->isTransactionFromDeal())
                continue;

            array_push($transactions, $trans);
        }
        return $transactions;
    }

    public function updateItems() {
        $items = array();
        $res = $this->httpClient->get("http://ineedvendors052715.mybluemix.net/api/vendor/catalog/{$this->id}");

        foreach ($res->json()['products'] as $itemJson)
            array_push($items, new Item($itemJson['id'], $itemJson['description'], $itemJson['name'],
                $itemJson['price'], $this));
        $this->items = $items;
    }

    public function updateDeals() {
        $deals = array();
        $res = $this->httpClient->get("http://ineed-dealqq.mybluemix.net/findDeal?vendorId={$this->id}");

        foreach($res->json() as $dealJson)
            array_push($deals,
                new Deal($dealJson['_id'], $this, $dealJson['dealName'], $dealJson['discount'], $dealJson['expireDate'],
                    $dealJson['itemSell'], $dealJson['price'], $dealJson['redeemCount'], $dealJson['sendCount'],
                    $dealJson['type'], $this->httpClient
                ));

        $this->deals = $deals;
    }

    /**
     * Static method to get an array of all vendors
     * @param \GuzzleHttp\Client $httpClient To be able to make requests
     * @return Vendor[] all encapsulated vendor types
     */
    public static function getAllVendors(\GuzzleHttp\Client $httpClient) {
        $vendorsJson = $httpClient->get('http://ineedvendors.mybluemix.net/api/vendors')->json()['vendors'];

        $vendors = array();
        foreach($vendorsJson as $vendorJson) {
            array_push($vendors, new Vendor($vendorJson['_id'], $httpClient));
        }
        return $vendors;
    }

    /**
     * Static function to get ALL {Vendor}'s transaction history
     * @param \GuzzleHttp\Client $httpClient instance of a Guzzle {Client}
     * @return array of transactions (TODO: probably should be an array of arrays
     */
    public static function getAllVendorHistory(\GuzzleHttp\Client $httpClient) {
        $hist = array();

        foreach(self::getAllVendors($httpClient) as $vendor) {
            $transHist = $vendor->getTransactionHistory();

            if(empty($transHist)) // if vendor has no transaction history, go to next vendor
                continue;

            array_push($hist, $transHist);

        }
        return $hist;
    }

    /**
     * @param \GuzzleHttp\Client $httpClient
     * @return Transaction[][] Array of arrays, with each outer
     */
    public static function getAllDealsPurchased(\GuzzleHttp\Client $httpClient) {
        $hist = array();

        foreach(self::getAllVendors($httpClient) as $vendor) {
            $transHist = $vendor->getPurchasedDeals();

            if(empty($transHist)) { // if vendor has no transaction history, go to next vendor
                continue;
            }

            array_push($hist, $transHist);

        }
        return $hist;
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
    public function getAddress() {
        return $this->address;
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
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPhoneNumber() {
        return $this->phoneNumber;
    }

    /**
     * @return string
     */
    public function getState() {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @return Deal[]
     */
    public function getDeals() {
        return $this->deals;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize() {
        return [
            'vendorID'    => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'email'       => $this->email,
            'phoneNumber' => $this->phoneNumber,
            'state'       => $this->state,
            'type'        => $this->type,
        ];
    }
}