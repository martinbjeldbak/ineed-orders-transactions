<?php

require_once __DIR__.'/Vendor.php';

class Item {
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
}