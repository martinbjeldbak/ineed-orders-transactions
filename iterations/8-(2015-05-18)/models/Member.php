<?php

class Member implements iNeedModel {
    /** @var \GuzzleHttp\Client $httpClient */
    private $httpClient;
    /** @var string $email */
    private $email;
    /** @var string $cardNumber */
    private $cardNumber;
    /** @var string $cardCVC2 */
    private $cardCVC2;
    /** @var string $cardExpiration */
    private $cardExpiration;

    public function __construct($email, \GuzzleHttp\Client $httpClient)  {
        $this->httpClient = $httpClient;
        // TODO: exception handling if member not found, etc (property of $res)
        $res = $httpClient->get("http://ineed-members.mybluemix.net/api/profile?email={$email}");
        $member = $res->json();

        $this->email = $member['email'];

        // TODO: If this information is null, throw error
        $this->cardNumber = $member['creditCard']['cardNumber'];
        $this->cardCVC2 = $member['creditCard']['cardCVC2'];
        $this->cardExpiration = $member['creditCard']['cardExpiration'];

        /*
        if (null === $id) {
            throw new NotFoundHttpException(sprintf('User %d does not exist', $id));
        }
        */
    }

    public function getOrderHistory() {
        return Order::getOrdersForMember($this, $this->httpClient);
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
            'email'      => $this->email,
            'cardNumber' => $this->cardNumber,
            'cardCVC'    => $this->cardCVC2,
            'cardExp'    => $this->cardExpiration,
        ];
    }

    /**
     * @return string
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getCardNumber() {
        return $this->cardNumber;
    }

    /**
     * @return string
     */
    public function getCardCVC2() {
        return $this->cardCVC2;
    }

    /**
     * @return string
     */
    public function getCardExpiration() {
        return $this->cardExpiration;
    }
}