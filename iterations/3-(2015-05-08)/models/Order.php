<?php namespace iNeed;

include __DIR__ . '/../utils/Team10DB.php';
include 'iDBModel.php';
use GuzzleHttp\Message\ResponseInterface;

class Order implements iDBModel {
    public $paymentType, $vendorId, $total, $tax, $dealId, $dealDiscount, $ID = "";
    private $committed = false;

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

    function hasCommitted() {
        return $this->committed;
    }

    /**
     * Gets the ID of this instance. Empty string if it is not instantiated
     * @return String the unique object ID of this instance
     *
     */
    function getID() {
        return $this->ID;
    }

    /**
     * Commits this object to Team 10's database.
     *
     * Returns a GuzzleHttp ResponseInterface that has information about the POST request.
     * @return String id of this newly created object
     */
    function commit() {
        // This instance has already ommited.. do not recommit it to the DB
        if($this->committed == true)
            return -1;

        $res = Team10DB::post("orders", [
            'paymentType' => $this->paymentType,
            'vendorId' => $this->vendorId,
            'total' => $this->total,
            'tax' => $this->tax,
            'dealId' => $this->dealId,
            'dealDiscount' => $this->dealDiscount
        ]);

        $this->committed = true;
        $this->ID = $res->json()['_id'];
        return $this->ID;
    }


    /*
	public function getAll() {
        $res = $this->client->get("ineed-db.mybluemix.net/api/orders");

        return $res->json();
    }
    */

}