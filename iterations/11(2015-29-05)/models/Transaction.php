<?php

require_once __DIR__.'/TransactionState.php';
require_once __DIR__.'/OrderTransactionMediator.php';
require_once __DIR__.'/Item.php';
require_once __DIR__.'/Order.php';

class Transaction implements iNeedModel {
    /** @var \GuzzleHttp\Client $httpClient */
    private $httpClient;
    /** @var bool $created */
    private $created = False;
    /** @var bool $transactionFromDeal */
    private $transactionFromDeal = False;
    /** @var string $id */
    private $id = "Not set yet, call createTransaction()";
    /** @var int $transactionState  */
    private $transactionState;
    /** @var Order $order */
    private $order;
    /** @var Deal $deal */
    private $deal = null;
    /** @var Vendor $vendor */
    private $vendor = null;
    /** @var Item[] $items  */
    private $items = array();
    /** @var int $quantity */
    private $quantity;
    /** @var double $unitPrice */
    private $unitPrice;
    /** @var OrderTransactionMediator $mediator */
    private $mediator;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    /**
     * Create a transaction from a deal (no items or quantity)
     * @param Order $order
     * @param Deal $deal
     * @param $quantity
     * @param \GuzzleHttp\Client $httpClient
     */
    function __construct4(Order $order, Deal $deal, $quantity, \GuzzleHttp\Client $httpClient) {
        $this->httpClient = $httpClient;
        $this->order = $order;
        $this->deal = $deal;
        $this->items = $deal->getItems();
        $this->vendor = $deal->getVendor();
        $this->unitPrice = $deal->getPrice();
        $this->mediator = $this->order->getMediator();
        $this->mediator->registerTransaction($this);
        $this->setQuantity($quantity);
        $this->transactionFromDeal = True;
    }

    /**
     * Creating a transaction from an item purchase (no deal)
     * @param Order $order
     * @param Item $item
     * @param int $quantity
     * @param Vendor $vendor
     * @param \GuzzleHttp\Client $httpClient
     */
    function __construct5(Order $order, Item $item, $quantity, Vendor $vendor, \GuzzleHttp\Client $httpClient) {
        $this->order = $order;
        $this->items = [$item];
        $this->vendor = $vendor;
        $this->httpClient = $httpClient;
        $this->unitPrice = $item->getPrice();
        $this->mediator = $this->order->getMediator();
        $this->mediator->registerTransaction($this);
        $this->setQuantity($quantity);
    }
    
    // Begin a Mediator interaction
    public function setQuantity($toQuantity) {
        $this->quantity = $toQuantity;
        $this->mediator->updateTotal();
    }
    
    public function removeTransaction() {
        $this->mediator->unregisterTransaction($this);
        $this->mediator->updateTotal();
    }

    /**
     * Instantiate this instance of the transaction in the db
     */
    public function createinDB() {
        if($this->created) // Don't create a new transaction if this instance already has been created
            return;

        $res = $this->httpClient->post('https://ineed-db.mybluemix.net/api/transactions', [ 'json' => [
            'orderId' => $this->order->getID(),
            'itemId' => $this->deal ? null : $this->items[0]->getID(),
            'quantity' => $this->quantity,
            'unitPrice' => $this->unitPrice,
            'vendorId' => $this->vendor->getID(),
            'dealId' => $this->deal ? $this->deal->getID() : null,
            'dealDiscount' => $this->deal ? $this->deal->getDiscount() : null
        ]]);
        $transactionJson = $res->json();
        $this->id = $transactionJson['_id'];
        TransactionState::setState($this, TransactionState::$orderPlaced);
	$this->notifyVendor();	
        $this->created = True;
    }

	/**
	* Notifies Vendor About the transaction
	*/
     private function notifyVendor(){
		$vendorApiURL = "http://pyneed.mybluemix.net/api/vendor/notify?vendorId=".$this->vendor."&transactionId=".$this->id."&message=please";
		error_log($vendorApiURL);
		$res= $this->httpClient->get($vendorApiURL);
		error_log("notfify vendor ***************** ".print_r($res,true));
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

    /**
     * Gets the member who initiated this Transaction
     * @return Member who initiated transaction
     */
    public function getMember() {
        return $this->order->getMember();
    }

    public static function getTransactionsForOrder(Order $order, \GuzzleHttp\Client $httpClient) {
        $res = $httpClient->get("https://ineed-db.mybluemix.net/api/transactions?orderId={$order->getID()}");
        $transactionsJson = $res->json();

        $transactions = array();
        foreach($transactionsJson as $transactionJson) {
            array_push($transactions, self::createFromJson($transactionJson, $httpClient));
        }
        return $transactions;
    }

    /**
     * Private method to rebuild an existing Transaction instance retrieved from the database
     * @param $transactionJson array This is the transaction info returned as JSON
     * @param \GuzzleHttp\Client $httpClient
     * @return null|Transaction
     */
    private static function createFromJson($transactionJson, \GuzzleHttp\Client $httpClient) {
        $order = Order::getOrderFromId($transactionJson['orderId'], $httpClient);
        if(is_null($order)) // If no order exists for this transaction
            return null;

        // Items, quantity, price, are not part of transactions any longer

        $vendor = new Vendor($transactionJson['vendorId'], $httpClient);
        if(is_null($vendor)) // If vendor cannot be found for this trans
            return null;

        $quantity = $transactionJson['quantity'];

        if (!is_null($transactionJson['dealId'])) {
            // This transaction is the result of a deal
            $deal = new Deal($transactionJson['dealId'], $httpClient);

            $trans = new Transaction($order, $deal, $quantity, $httpClient);
            $trans->id = $transactionJson['_id'];
            $trans->transactionFromDeal = True;
            $trans->created = True;
            $trans->transactionState = TransactionState::getTransStateForTrans($trans);
            return $trans;
        }
        elseif(is_null($transactionJson['dealId']) && is_null($transactionJson['dealDiscount']) &&
               !is_null($transactionJson['itemId'])) {
            // This transaction is the result of an item purchase
            $item = new Item($transactionJson['itemId'], $httpClient);
            $trans = new Transaction($order, $item, $quantity, $vendor, $httpClient);
            $trans->id = $transactionJson['_id'];
            $trans->created = True;
            return $trans;
        }
        throw new Exception("Not supposed to be here in Transaction:" . json_encode($transactionJson, JSON_PRETTY_PRINT));
    }

    public static function getTransactionFromId($id, \GuzzleHttp\Client $httpClient) {
        $res = $httpClient->get("https://ineed-db.mybluemix.net/api/transactions/{$id}");
        return self::createFromJson($res->json(), $httpClient);
    }

    /**
     * Gets the ID of this transaction instance.
     * @return string the hexadecimal ID. Or "Not set yet, call createTransaction()" if this instance has not been
     * committed to the DB.
     */
    public function getID() {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function isTransactionFromDeal() {
        return $this->transactionFromDeal;
    }

    public function getTransactionState() {
        return $this->transactionState;
    }

    /**
     * @return Order
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * Gets the {Deal} object that this transaction was apart of.
     * @return Deal the corresponding deal to this transaction
     * @throws Exception if this transaction is not from a deal, but from an item purchase.
     */
    public function getDeal() {
        if($this->isTransactionFromDeal())
            return $this->deal;
        throw new Exception("Attempting to get deal from transaction resulting from normal item purchase");
    }

    /**
     * @return Vendor
     */
    public function getVendor() {
        return $this->vendor;
    }

    /**
     * Gets the quantity of the item purchased
     * @return int the quantity of item purchased
     * @throws Exception if this Transaction is not the result of a member puchasing a deal
     */
    public function getQuantity() {
        if($this->transactionFromDeal)
            throw new Exception("Attempting to get quantity from transaction resulting from a deal");
        return $this->quantity;
    }

    /**
     * @return float
     */
    public function getUnitPrice() {
        return $this->unitPrice;
    }

    /**
     * @return OrderTransactionMediator
     */
    public function getMediator() {
        return $this->mediator;
    }

    /**
     * @return Item[]
     */
    public function getItems() {
        if($this->transactionFromDeal)
            return $this->deal->getItems();
        return $this->items;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return [
            'transactionId'    => $this->id,
            'isDeal'           => $this->transactionFromDeal,
            'transactionState' => TransactionState::toString($this->transactionState),
            'orderId'          => $this->order->getID(),
            'items'            => $this->items,
            'quantity'         => $this->quantity,
            'unitPrice'        => $this->unitPrice, // TODO: Mediator caluclateTotal() instead?
            'vendorId'         => $this->vendor->getID(),
            'dealId'           => $this->deal ? $this->deal->getID() : null,
            'dealDiscount'     => $this->deal ? $this->deal->getDiscount() : null,
        ];
    }
}
