<?php

require_once __DIR__.'/OrderState.php';

class Order {
    private $httpClient, $created = False;
    public $id = "not set yet (is set when createOrder() is called", $paymentType, $member, $orderState, $total, $tax;


    function __construct($paymentType, Member $member, $total, $tax, $orderState, \GuzzleHttp\Client $httpClient) {
        $this->paymentType = $paymentType;
        $this->member = $member;
        $this->total = $total;
        $this->tax = $tax;
        $this->orderState = $orderState;
        $this->httpClient = $httpClient;
    }

    /**
     * Commits this order instance to the DB
     */
    public function createOrder() {
        if($this->created) // Don't create a new order if this instance already has been created
            return;

        $res = $this->httpClient->post('https://ineed-db.mybluemix.net/api/orders', [ 'json' => [
            'paymentType' => $this->paymentType,
            'memberEmail' => $this->member->email,
            'total' => 0.0, // unused, moved to Transaction class
            'tax' => 0.0, // unused, moved to transaction class
            'dealId' => '000000000000000000000000', // unused, moved to transaction class
            'dealDiscount' => 0.0 // unused, moved to transaction class
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

            $orderState = OrderState::getOrderStateForOrderId($json['_id']);
            $member = new Member($json['memberEmail'], $httpClient);
            $order = new Order($json['paymentType'], $member, $json['total'], $json['tax'], $orderState, $httpClient);
            $order->id = $json['_id'];
            return $order;
        }
        catch(\GuzzleHttp\Exception\ClientException $ex) {
            // No order not found
            return null;
        }
    }

}