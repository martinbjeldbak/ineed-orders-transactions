<?php
require __DIR__ . '/vendor/autoload.php';

$http_client = new \GuzzleHttp\Client();

$memberID = $_GET["memberId"];
$dealID   = $_GET["dealId"];

// Query Team 1's (Member) API for member info
$resMemberQuery = $http_client->get("https://ineed-members.mybluemix.net/api/profile?email={$memberID}");

// 200 OK, member was found
if($resMemberQuery->getStatusCode() == 200) {
    // Query Team 2's (Deal) API for deal info
    $resDealQuery = $http_client->get("http://ineed-dealqq.mybluemix.net/getOneDeal?deal_id={$dealID}");

    // 200 OK, deal was found
    if($resDealQuery->getStatusCode() == 200) {
        $deal = $resDealQuery->json();

        echo json_encode($deal, JSON_PRETTY_PRINT);

        $member = $resMemberQuery->json();
        //echo json_encode($member, JSON_PRETTY_PRINT);

        // TODO: Probably need to check if these fields are set, they are probably not mandatory
        $cardNumber = $member['creditCard']['cardNumber'];
        $cardExp = $member['creditCard']['cardExpiration'];
        $cardCvc = $member['creditCard']['cardCVC2'];

        // their API for redemption seems to be down
        //$http_client->put("http://ineed-dealqq.mybluemix.net/redeemDeal?deal_id={$dealID}");
        //echo json_encode($http_client->get("http://ineed-dealqq.mybluemix.net/getOneDeal?deal_id={$dealID}")->json(), JSON_PRETTY_PRINT);
    }
}