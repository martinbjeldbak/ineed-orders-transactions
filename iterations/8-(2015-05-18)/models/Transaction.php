<?php

require_once __DIR__.'/TransactionState.php';
require_once __DIR__.'/Item.php';
require_once __DIR__.'/Order.php';

class Transaction {
    private $httpClient, $created = False;
    public $id, $paymentType, $member, $deal, $transactionState, $order, $vendor = null, $item = null, $quantity;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    /**
     * Creating a transaction from an item purchase
     * @param Order $order
     * @param Item $item
     * @param int $quantity
     * @param Vendor $vendor
     * @param \GuzzleHttp\Client $httpClient
     */
    function __construct5(Order $order, Item $item, $quantity = 1, Vendor $vendor, \GuzzleHttp\Client $httpClient) {
        // vendor
        $this->order = $order;
        $this->item = $item;
        $this->quantity = $quantity;
        $this->vendor = $vendor;
        $this->httpClient = $httpClient;
    }

    /**
     * Creating a transaction from a deal
     * @param Order $order
     * @param Deal $deal
     * @param \GuzzleHttp\Client $httpClient
     */
    function __construct3(Order $order, Deal $deal, \GuzzleHttp\Client $httpClient) {
        $this->httpClient = $httpClient;
        $this->order = $order;
        $this->quantity = 1;
        $this->deal = $deal;
        $this->vendor = $deal->vendor;

        //$this->createTransaction();
    }

    public static function getTransactionFromId($id, \GuzzleHttp\Client $httpClient)
    {
        $res = $httpClient->get("https://ineed-db.mybluemix.net/api/transactions/{$id}");

        // Order $order, Item $item, Vendor $vendor, Deal $deal, \GuzzleHttp\Client $httpClient

        $transactionJson = $res->json();

        $order = Order::getOrderFromId($transactionJson['orderId'], $httpClient);
        if(is_null($order)) // If no order exists for this transaction
            return null;

        // Items, quantity, price, are not part of transactions any longer

        $vendor = new Vendor($transactionJson['vendorId'], $httpClient);
        if(is_null($vendor)) // If vendor cannot be found for this trans
            return null;


        echo "Debug: Woooosh";
        $item = new Item($transactionJson['itemId'], $httpClient);
        $quantity = $transactionJson['quantity'];
        if (array_key_exists('dealId', $transactionJson)) {
            // This transaction is the result of a deal
            $deal = new Deal($transactionJson['dealId'], $httpClient);
            // (Order $order, Deal $deal, \GuzzleHttp\Client $httpClient) {
            echo "Debug: Is deal";
            return new Transaction($order, $deal, $httpClient);
        }
        else {
            echo "Debug: Is no deal";
            // This transaction is the result of an item purchase
            //  __construct5(Order $order, Item $item, $quantity = 1, Vendor $vendor, \GuzzleHttp\Client $httpClient
            return new Transaction($order, $item, $quantity, $vendor, $httpClient);
        }
    }

    /**
     * Instantiate this instance of the transaction in the db
     */
    public function createTransaction() {
        if($this->created) // Don't create a new transaction if this instance already has been created
            return;

        $res = $this->httpClient->post('https://ineed-db.mybluemix.net/api/transactions', [ 'json' => [
            'orderId' => $this->order->id,
            'itemId' => $this->item->id,
            'quantity' => $this->quantity,
            'unitPrice' => $this->unitPrice,
            'vendorId' => $this->vendor->id,
            'dealId' => $this->deal->id,
            'dealDiscount' => $this->deal->discount
        ]]);
        $transactionJson = $res->json();

        $this->id = $transactionJson['_id'];

        $this->httpClient->post("https://ineed-db.mybluemix.net/api/transactions/{$this->id}/transaction_state", [
            'json' => [
                "currentState" => TransactionState::$orderPlaced
            ]]);

        $this->created = True;
    }

    /**
     * Updates the state of this transaction
     * @param $toState int Use the static {TransactionState} class for the specific classifications of states
     */
    public function updateTransactionState($toState) {
        $this->transactionState = $toState;
        $this->httpClient->put("https://ineed-db.mybluemix.net/api/transactions/{$this->id}/transaction_state", [
            'json' => [
                "currentState" => $toState
        ]]);
    }

}