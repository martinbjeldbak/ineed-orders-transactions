<?php

require_once __DIR__.'/TransactionState.php';

class Transaction {
    private $httpClient, $created = False;
    public $id, $paymentType, $member, $deal, $transactionState;

    function __construct(Order $order, Item $item = NULL/*remove NULL later*/, Vendor $vendor = NULL/*remove NULL later*/, Deal $deal, \GuzzleHttp\Client $httpClient) {
        $this->httpClient = $httpClient;
        $this->orderId = $order->id;
        // If dealId and itemId are mutually exclusive, we need schema change to make them both optional,
        // and require one of the two?
        $this->itemId = /*$item->id*/"placeholder"; // WARNING need service to get item from dealId for caller of this
        $this->quantity = 1;
        $this->unitPrice = /*$item->price*/1.99;
        $this->vendorId = /*$vendor->id*/"placeholder"; // WARNING need service to get vendor from dealId for caller of this
        $this->dealId = $deal->id;
        $this->dealDiscount = $deal->discount; 

        $this->createTransaction();
    }

    /**
     * Creates this transaction instance
     */
    private function createTransaction() {
        if($this->created) // Don't create a new transaction if this instance already has been created
            return;

        $res = $this->httpClient->post('https://ineed-db.mybluemix.net/api/transactions', [ 'json' => [
            'orderId' => $this->orderId,
            'itemId' => $this->itemId,
            'quantity' => $this->quantity,
            'unitPrice' => $this->unitPrice,
            'vendorId' => $this->vendorId,
            'dealId' => $this->dealId,
            'dealDiscount' => $this->dealDiscount
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