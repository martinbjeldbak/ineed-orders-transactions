<?php

require_once __DIR__.'/OrderState.php';

class Order {
    private $httpClient, $created = False;
    public $id, $paymentType, $member, $deal, $orderState;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    function __construct5($id, $paymentType, Member $member, Deal $deal, OrderState $orderState) {
        $this->id = $id;
        $this->paymentType = $paymentType;
        $this->member = $member;
        $this->deal = $deal;
        $this->orderState = $orderState;
    }

    function __construct3(Member $member, Deal $deal, \GuzzleHttp\Client $httpClient) {
        $this->httpClient = $httpClient;
        $this->paymentType = "martin testing";
        $this->member = $member;
        $this->deal = $deal;

        // TODO: Process payment details here

        $this->createOrder();
    }

    /**
     * Creates this order instance
     */
    private function createOrder() {
        if($this->created) // Don't create a new order if this instance already has been created
            return;

        $res = $this->httpClient->post('https://ineed-db.mybluemix.net/api/orders', [ 'json' => [
            'paymentType' => $this->paymentType,
            'memberId' => $this->member->email,
            'total' => $this->deal->price,
            'tax' => '0.0',
            'dealId' => $this->deal->id,
            'dealDiscount' => $this->deal->discount
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
            // ($id, $paymentType, Member $member, Deal $deal, OrderState $orderState)
            return new Order($json['_id'], $json['paymentType'],
                new Member($json['memberEmail'], $httpClient), new Deal($json['dealId'], $httpClient),
                OrderState::getOrderStateForOrderId($json['id']));
        }
        catch(\GuzzleHttp\Exception\ClientException $ex) {
            // No order not found
            return null;
        }
    }

}