<?php

require_once __DIR__.'/OrderState.php';

class Order {
    private $httpClient, $created = False;
    public $id, $paymentType, $member, $deal, $orderState;

    function __construct($paymentType, Member $member, Deal $deal, \GuzzleHttp\Client $httpClient) {
        $this->httpClient = $httpClient;
        $this->paymentType = $paymentType;
        $this->member = $member;
        $this->deal = $deal;

        // TODO: Process payment details here

        $this->createOrder();
        $this->updateOrderState(OrderState::$orderPlaced);
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
        $this->created = True;
    }

    /**
     * Updates the state of this order
     * @param $toState int Use the static {OrderState} class for the specific classifications of states
     */
    public function updateOrderState($toState) {
        $this->orderState = $toState;
        $this->httpClient->post("https://ineed-db.mybluemix.net/api/orders/{$this->id}/order_state", [
            'json' => [
                "currentState" => $toState
        ]]);
    }

}