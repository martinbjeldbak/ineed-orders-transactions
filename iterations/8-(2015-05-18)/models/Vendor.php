<?php

require_once __DIR__.'/Deal.php';
require_once __DIR__.'/Item.php';
require_once __DIR__.'/Order.php';
require_once __DIR__.'/Transaction.php';

class Vendor {
    private $httpClient;
    public $id, $address, $description, $email, $name, $phoneNumber,
        $state, $type, $items = array("If you see this, call updateItems()"), $deals = array("If you see this, call updateDeals()");

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

        //echo json_encode($res['vendors'], JSON_PRETTY_PRINT);
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

            if(is_null($trans) || !$trans->transactionFromDeal)
                continue;

            array_push($transactions, $trans);
        }
        return $transactions;
    }

    public function updateItems() {
        $items = array();
        $res = $this->httpClient->get("http://ineedvendors.mybluemix.net/api/vendor/catalog/{$this->id}");

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
}