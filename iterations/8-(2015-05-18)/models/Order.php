<?php

require_once __DIR__.'/Transaction.php';
require_once __DIR__.'/OrderState.php';
require_once __DIR__.'/OrderTransactionMediator.php';

class Order {
    /** @var \GuzzleHttp\Client $httpClient */
    private $httpClient;
    /** @var boolean $id */
    public $id = "not set yet (is set when createOrder() is called";
    /** @var string $paymentType */
    public $paymentType;
    /** @var Member $member */
    public $member;
    /** @var int $orderState */
    public $orderState;
    /** @var bool $created */
    public $created = False;
    /** @var Transaction $transaction */
    public $transaction;
    /** @var OrderTransactionMediator $mediator */
    public $mediator;
    /** @var double $total */
    public $total;

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
        $this->transaction = new Transaction($this, $deal, $quantity, $this->httpClient);
    }
    // Transaction containing normal line item
    public function addTransaction3(Item $item, Vendor $vendor, $quantity) {
        $this->transaction = new Transaction($this, $item, $quantity, $vendor, $this->httpClient);
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
            'memberEmail' => $this->member->email,
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
        $res = $httpClient->get("https://ineed-db.mybluemix.net/api/orders?memberEmail={$member->email}");
        $orders = array();

        foreach($res->json() as $orderJson) {
            $order = new Order($orderJson['paymentType'], $member, $orderJson['total'], $orderJson['tax'],
                $httpClient);
            $order->id = $orderJson['_id'];
            $order->created = True;
            $order->orderState = OrderState::getOrderStateForOrder($order);
            array_push($orders, $order);
        }
        return $orders;
    }
}