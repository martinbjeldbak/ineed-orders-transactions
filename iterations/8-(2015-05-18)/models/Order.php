<?php

class Order {
    private $httpClient;
    public $id, $paymentType, $member, $deal;

    function __construct($paymentType, Member $member, Deal $deal, \GuzzleHttp\Client $httpClient) {
        $this->httpClient = $httpClient;
        $this->paymentType = $paymentType;
        $this->member = $member;
        $this->deal = $deal;

        // TODO: Process payment details here

        $res = $httpClient->post('ineed-db.mybluemix.net/api/orders', [ 'json' => [
            'paymentType' => $this->paymentType,
            'memberId' => $this->member->email,
            'total' => $this->deal->price,
            'tax' => '0.0',
            'dealId' => $this->deal->id,
            'dealDiscount' => $this->deal->discount
        ]]);
        $orderJson = $res->json();

        $this->id = $orderJson['_id'];
    }


}