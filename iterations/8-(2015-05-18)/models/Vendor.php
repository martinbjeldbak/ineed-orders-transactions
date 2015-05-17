<?php

class Vendor {
    private $httpClient;
    public $id, $address, $description, $email, $name, $phoneNumber, $state, $type;

    public function __construct($id, \GuzzleHttp\Client $httpClient) {
        $this->httpClient = $httpClient;

        $res = $this->httpClient->get("https://ineed-db.mybluemix.net/api/vendors/{$id}");
        $vendorJson = $res->json();

        $this->id = $vendorJson['_id'];
        $this->address = $vendorJson['address'];
        $this->description = $vendorJson['description'];
        $this->email = $vendorJson['email'];
        $this->name = $vendorJson['name'];
        $this->phoneNumber = $vendorJson['phoneNumber'];
        $this->state = $vendorJson['state'];
        $this->type = $vendorJson['type'];

        //echo json_encode($res['vendors'], JSON_PRETTY_PRINT);
    }

    public function getTransactionHistory() {
        $res = $this->httpClient->get("https://ineed-db.mybluemix.net/api/transactions?vendorId={$this->id}");

        return $res->json();
    }

    /**
     * Static method to get an array of all vendors
     * @param \GuzzleHttp\Client $httpClient To be able to make requests
     * @return array of {Vendor} types, all encapsulated
     */
    public static function getAllVendors(\GuzzleHttp\Client $httpClient) {
        $vendorsJson = $httpClient->get('http://ineedvendors.mybluemix.net/api/vendors')->json()['vendors'];

        $vendors = array();
        foreach($vendorsJson as $vendorJson) {
            array_push($vendors, new Vendor($vendorJson['_id'], $httpClient));
        }
        return $vendors;
    }
}