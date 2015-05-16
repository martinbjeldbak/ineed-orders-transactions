<?php

class Vendor {
    private $httpClient;
    public $id;

    public function __construct($id, \GuzzleHttp\Client $httpClient) {
        $this->httpClient = $httpClient;
        $this->id = $id;

        //$res = $this->httpClient->get("http://ineedvendors.mybluemix.net/api/vendors/");
        //echo json_encode($res['vendors'], JSON_PRETTY_PRINT);
    }

    public function getTransactionHistory() {
        $res = $this->httpClient->get("https://ineed-db.mybluemix.net/api/transactions?vendorId={$this->id}");

        return $res->json();
    }
}