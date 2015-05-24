<?php

require_once __DIR__.'/Transaction.php';
require_once __DIR__.'/OrderState.php';
require_once __DIR__.'/OrderTransactionMediator.php';

class Order {
    private $httpClient, $created = False;
    public $id, $paymentType, $member, $orderState;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    function __construct4($id, $paymentType, Member $member, $orderState) {
        $this->id = $id;
        $this->paymentType = $paymentType;
        $this->member = $member;
        $this->orderState = $orderState;
        $this->mediator = new OrderTransactionMediator;
        $this->mediator->registerOrder($this);
    }

    function __construct2(Member $member, \GuzzleHttp\Client $httpClient) {
        $this->httpClient = $httpClient;
        $this->paymentType = "martin testing";
        $this->member = $member;
        $this->mediator = new OrderTransactionMediator;
        $this->mediator->registerOrder($this);

        // TODO: Process payment details here
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
        $transaction = new Transaction($this, $deal, $quantity, $this->httpClient);
        
    }
    // Transaction containing normal line item
    public function addTransaction3(Item $item, Vendor $vendor, $quantity) {
        $transaction = new Transaction($this, $item, $vendor, $deal, $this->httpClient);
    }
    
    // COMMIT TO DB, Begin a mediator interaction
    public function placeOrder() { 
        $this->createInDB(); //initializes $this->id
        $this->mediator->createTransactionsInDB();
    }

    /**
     * Creates this order instance
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

        $this->httpClient->post("https://ineed-db.mybluemix.net/api/orders/{$this->id}/order_state", [
            'json' => [
                "currentState" => OrderState::$orderPlaced
            ]]);

        $this->created = True;
    }

    /**
     * Updates the state of this order
     * @param $toState int Use the static {OrderState} class for the specific classifications of states
     */
    public function updateOrderState($toState) {
        $this->orderState = $toState;
        $this->httpClient->put("https://ineed-db.mybluemix.net/api/orders/{$this->id}/order_state", [
            'json' => [
                "currentState" => $toState
        ]]);
    }

    /**
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

            // echo json_encode($json, JSON_PRETTY_PRINT);
            // ($id, $paymentType, Member $member, OrderState $orderState)
            return new Order($json['_id'], $json['paymentType'],
                new Member($json['memberEmail'], $httpClient),
                OrderState::getOrderStateForOrderId($json['_id']));
        }
        catch(\GuzzleHttp\Exception\ClientException $ex) {
            // No order not found
            return null;
        }
    }

}