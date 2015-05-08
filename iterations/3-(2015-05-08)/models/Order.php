<?php namespace iNeed;

include __DIR__ . '/../utils/Team10DB.php';
include 'iDBModel.php';

class Order implements iDBModel {
    public $paymentType, $vendorId, $total, $tax, $dealId, $dealDiscount;
    public $committed = false;

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
        $this->paymentType = $paymentType;
        $this->vendorId = $vendorId;
        $this->total = $total;
        $this->tax = $tax;
        $this->dealId = $dealId;
        $this->dealDiscount = $dealDiscount;
    }

    function commit() {
        return Team10DB::post("orders", [
            'paymentType' => $this->paymentType,
            'vendorId' => $this->vendorId,
            'total' => $this->total,
            'tax' => $this->tax,
            'dealId' => $this->dealId,
            'dealDiscount' => $this->dealDiscount
        ]);
    }


    /*
	public function getAll() {
        $res = $this->client->get("ineed-db.mybluemix.net/api/orders");

        return $res->json();
    }
    */

}