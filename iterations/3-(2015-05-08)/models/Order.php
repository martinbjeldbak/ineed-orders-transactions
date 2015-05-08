<?php

require __DIR__ . '../vendor/autoload.php';

class Order {
    private $paymentType, $vendorId, $total, $tax, $dealId, $dealDiscount;

    /**
     * Creates a new order object from the given parameters, but does not commit it to the db.
     *
     * @param $paymentType String type of payment, credit, PayPal, stripe, etc
     * @param $vendorId String object reference to the vendor that this order belongs to
     * @param $total Float total price for the item, in USD
     * @param $tax Float tax for the item, in USD
     * @param $dealId String object reference to deal applied to this order
     * @param $dealDiscount Float how much of a discount to apply, between 0.00 - 1.00
     */
    function __construct($paymentType, $vendorId, $total, $tax, $dealId, $dealDiscount) {
        $this->client = new GuzzleHttp\Client();

        $this->paymentType = $paymentType;
        $this->vendorId = $vendorId;
        $this->total = $total;
        $this->tax = $tax;
        $this->dealId = $dealId;
        $this->dealDiscount = $dealDiscount;
    }


    /**
     * Commits this object to Team 10's database.
     *
     * Returns a GuzzleHttp ResponseInterface that has information about the POST request.
     */
    function commit() {
        $res = $this->client->post("https://ineed-db.mybluemix.net/api/orders", [
            'json' => [
                'paymentType' => $this->paymentType,
                'vendorId' => $this->vendorId,
                'total' => $this->total,
                'tax' => $this->tax,
                'dealId' => $this->dealId,
                'dealDiscount' => $this->dealDiscount
            ]
        ]);
        return $res;
    }


    /*
	public function getAll() {
        $res = $this->client->get("ineed-db.mybluemix.net/api/orders");

        return $res->json();
    }
    */

}