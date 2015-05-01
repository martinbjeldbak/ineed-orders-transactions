<?php

namespace Mini\Model;

require '../vendor/autoload.php';

class Model {
    /**
     * The database connection
     * @var PDO
     */
	private $db;

    function __construct()
    {   
        // Instantiate http client
        $this->httpClient = new \GuzzleHttp\Client();
	}
    
    public function getAllMembers() {
        $members = $this->httpClient->get('https://ineed-db.mybluemix.net/api/members');
        
        return $members->json();
    }
}
