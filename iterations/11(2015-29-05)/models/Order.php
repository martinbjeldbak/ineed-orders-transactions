<?php

require_once __DIR__.'/iNeedModel.php';

class Order implements iNeedModel {
    /** @var \GuzzleHttp\Client $httpClient */
    private $httpClient;
    /** @var string $id */
    private $id = "not set yet (is set when createOrder() is called";
    /** @var string $paymentType */
    private $paymentType;
    /** @var Member $member */
    private $member;
    /** @var int $orderState */
    private $orderState;
    /** @var bool $created */
    private $created = False;
    /** @var Transaction[] $transaction */
    private $transactions;
    /** @var OrderTransactionMediator $mediator */
    private $mediator;
    /** @var double $total */
    private $total;

    function __construct($paymentType, Member $member, $total, $tax, \GuzzleHttp\Client $httpClient) {
        $this->httpClient = $httpClient;
        $this->paymentType = $paymentType;
        $this->member = $member;
        $this->total = $total;
        $this->tax = $tax;
        $this->mediator = new OrderTransactionMediator();
        $this->mediator->registerOrder($this);
    }
    
    public function setTotal($total) {
        $this->total = $total;
    }
 
    public function addTransaction() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='addTransaction'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }
    //Transaction containing deal
    public function addTransaction2(Deal $deal, $quantity) {
        $this->transactions = [new Transaction($this, $deal, $quantity, $this->httpClient)];
    }
    // Transaction containing normal line item
    public function addTransaction3(Item $item, Vendor $vendor, $quantity) {
        $this->transactions = [new Transaction($this, $item, $quantity, $vendor, $this->httpClient)];
    }
    
    // COMMIT TO DB, Begin a mediator interaction
    public function placeOrder() { 
        $this->createInDB(); //initializes $this->id
        $this->mediator->createTransactionsInDB();
    }

    /**
     * Commits this order instance to the DB
     */
    private function createInDB() {
        if($this->created) // Don't create a new order if this instance already has been created
            return;

        $res = $this->httpClient->post('https://ineed-db.mybluemix.net/api/orders', [ 'json' => [
            'paymentType' => $this->paymentType,
            'memberEmail' => $this->member->getEmail(),
            'total' => $this->total,
            'tax' => '0.0',
        ]]);
        $orderJson = $res->json();
        $this->id = $orderJson['_id'];
        OrderState::setState($this, OrderState::$orderPlaced);
        $this->created = True;
    }

    /**
     * Updates the state of this order
     * @param $toState int Use the static {OrderState} class for the specific classifications of states
     */
    public function updateOrderState($toState) {
        $this->orderState = $toState;
        OrderState::setState($this, $toState);
    }

    /**
     * Static method to retrieve an existing {Order}, given the ID
     * @param $id string id of order to be found (hexadecimal)
     * @param \GuzzleHttp\Client $httpClient instance of a Guzzle htpt client
     * @return null|Order {Order} if order was found for given ID, else {null}
     */
    public static function getOrderFromId($id, \GuzzleHttp\Client $httpClient) {
        try {
            $res = $httpClient->get("https://ineed-db.mybluemix.net/api/orders/{$id}");
            $json = $res->json();

            if(array_key_exists('memberId', $json)) {
                // Then this order was created before we switched to member email as primary key
                return null;
            }

            $member = new Member($json['memberEmail'], $httpClient);
            $order  = new Order($json['paymentType'], $member, $json['total'], $json['tax'], $httpClient);
            $order->id = $json['_id'];
            $order->created = True;
            $order->orderState = OrderState::getOrderStateForOrder($order);
            return $order;
        }
        catch(\GuzzleHttp\Exception\ClientException $ex) {
            // No order not found
            return null;
        }
    }

    /**
     * Returns an array of {Order}s (can be an empty list if no orders) that the supplied {Member}
     * has participated in.
     * @param Member $member the member to get orders for
     * @param \GuzzleHttp\Client $httpClient an instance of a Guzzle http client
     * @return Order[] orders belonging to this member
     */
    public static function getOrdersForMember(Member $member, \GuzzleHttp\Client $httpClient) {
        $res = $httpClient->get("https://ineed-db.mybluemix.net/api/orders?memberEmail={$member->getEmail()}");
        $orders = array();

        foreach($res->json() as $orderJson) {
            $order = new Order($orderJson['paymentType'], $member, $orderJson['total'], $orderJson['tax'],
                $httpClient);
            $order->id = $orderJson['_id'];
            $order->created = True;
            $order->orderState = OrderState::getOrderStateForOrder($order);
            $order->transactions = Transaction::getTransactionsForOrder($order, $httpClient);
            array_push($orders, $order);
        }
        return $orders;
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
    public function getPaymentType() {
        return $this->paymentType;
    }

    /**
     * @return Member
     */
    public function getMember() {
        return $this->member;
    }

    /**
     * @return int
     */
    public function getOrderState() {
        return $this->orderState;
    }

    /**
     * Checks whether this instance of the object has been committed to Team 10's DB
     * @return boolean {True} if this instance has been committed to the DB
     */
    public function isCreated() {
        return $this->created;
    }

    /**
     * @return Transaction[]
     */
    public function getTransactions() {
        return $this->mediator->getTransactions();
    }

    /**
     * @return OrderTransactionMediator
     */
    public function getMediator() {
        return $this->mediator;
    }

    /**
     * @return float
     */
    public function getTotal() {
        return $this->total;
    }

    /**
     * Gets all items for this order, across all Transactions
     * @return Item[]
     */
    public function getItems() {
        $items = array();
        foreach($this->transactions as $transaction) {
            array_push($items, $transaction->getItems());
        }
        return $items;
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
            'orderId'    => $this->id,
            'total'      => $this->total,
            'orderState' => OrderState::toString($this->orderState),
            'tax'        => $this->tax,
            'items'      => $this->getItems(),
        ];
    }
}