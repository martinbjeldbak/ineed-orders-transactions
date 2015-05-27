<?php

require_once __DIR__.'/Vendor.php';
require_once __DIR__.'/iNeedModel.php';

class Item implements iNeedModel {
    public $id, $vendor, $name, $desc, $price, $quantity, $category;

    function __construct() {
        $a = func_get_args();
        $i = func_num_args();
        if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a);
        }
    }

    // TODO: Deals team only returns Item IDs, so we're forced to query DB directly
    function __construct3($id, Vendor $vendor, \GuzzleHttp\Client $httpClient) {
        $res = $httpClient->get("ineed-db.mybluemix.net/api/items/{$id}");
        $itemJson = $res->json();

        $this->vendor   = $vendor;
        $this->id       = $itemJson['_id'];
        $this->name     = $itemJson['prodName'];
        $this->desc     = $itemJson['prodDesc'];
        $this->quantity = $itemJson['quantity'];
        $this->category = $itemJson['category'];
        $this->price    = $itemJson['price'];
    }

    // TODO: Vendors return item array with all the details, so no direct query required
    function __construct5($id, $description, $name, $price, Vendor $vendor) {
        $this->id       = $id;
        $this->vendor   = $vendor;
        $this->name     = $name;
        $this->desc     = $description;
        $this->quantity = "This instance built from Vendors team, no quantity given";
        $this->category = "This instance built from Vendors team, no category given";
        $this->price    = $price;
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize() {
        return [
            'itemID' => $this->id,
            'name' => $this->name,
            'description' => $this->desc,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'vendor' => $this->vendor,
        ];
    }
}