<?php

// PLEASE SEE: http://silex.sensiolabs.org/doc/usage.html

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../models/Member.php';
require_once __DIR__.'/../models/Vendor.php';
require_once __DIR__.'/../models/Deal.php';
require_once __DIR__.'/../models/Order.php';
require_once __DIR__.'/../models/OrderState.php';

use Symfony\Component\HttpFoundation\Response;

$app = new Silex\Application();

$app['debug'] = true;

// REGISTER SERVICES

/**
 * Create one instance of our http client, use it
 * across entire application
 */
$app['httpClient'] = $app->share(function () {
    return new \GuzzleHttp\Client();
});

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

/**
 * Instantiates a new member from the given ID, this
 * fetches it from the Member's team so we have access to
 * payment details
 * @param $email String email belonging to member
 * @return Member object with various fields
 */
$memberProvider = function ($email) use ($app) {
    return new Member($email, $app['httpClient']);
};

$vendorProvider = function ($id) use ($app) {
    return new Vendor($id, $app['httpClient']);
};

$dealProvider = function ($id) use ($app) {
    return new Deal($id, $app['httpClient']);
};

// API ROUTES AND LOGIC


// GET ORDER HISTORY
$app->get('api/member/{member}/orders', function (Member $member) use ($app) {
    return $app->json($member);
    //return $app->json($member->getOrderHistory());
})
->convert('member', $memberProvider); // construct member class


// GET TRANSACTION HISTORY
/*
$app->get('api/vendor/{vendor}/transactions', function (Vendor $vendor) use ($app) {
    return $app->json($vendor->getTransactionHistory());
})
->convert('vendor', $vendorProvider);
*/

$app->get('/', function() {
    $message = "";
    $message .= "<h1>Please use one of the following API calls:</h1>";
    $message .= "<ul>";
    $message .= "<li><p>api/purchase/{memberEmail}/{dealId} have member purchase a deal</p> Live example: <a href=\"/api/purchase/pramodbiligiri@gmail.com/5553e5ad6f2b4e2b00975921\">/api/purchase/pramodbiligiri@gmail.com/5553e5ad6f2b4e2b00975921</a></li>";
    $message .= "<li><p>member/{memberEmail}/shopping to visit a list of Vendors available to the member</p> Live example <a href=\"/member/pramodbiligiri@gmail.com/shopping\">member/pramodbiligiri@gmail.com/shopping</a></li>";
    $message .= "<li><p>member/{member}/shopping/{vendorId} to browse the items and deals for a specific vendor</p> Live example <a href=\"/member/pramodbiligiri@gmail.com/shopping/5553e5ac6f2b4e2b0097591f\">/member/pramodbiligiri@gmail.com/shopping/5553e5ac6f2b4e2b0097591f</a></li>";
    $message .= "</ul>";

    return new Response($message);
});


// MEMBER PURCHASES DEAL (/api/purchase/pramodbiligiri@gmail.com/5553e5ad6f2b4e2b00975921)
$app->get('api/purchase/{member}/{deal}', function (Member $member, Deal $deal) use ($app) {
    $order = new Order('credit', $member, $deal, $app['httpClient']);

    return $app->json(array('id' => $order->id));
})
->convert('member', $memberProvider)
->convert('deal', $dealProvider);


// VIEW ROUTES AND LOGIC
$app->get('member/{member}/shopping', function (Member $member) use ($app) {

    return $app['twig']->render('shopping.twig', array(
        'member' => $member,
        'vendors' => Vendor::getAllVendors($app['httpClient'])
    ));
})
->convert('member', $memberProvider);



// VIEW ROUTES AND LOGIC
// (http://homestead.app/member/pramodbiligiri@gmail.com/shopping/5553e5ac6f2b4e2b0097591f)
$app->get('member/{member}/shopping/{vendor}', function (Member $member, Vendor $vendor) use ($app) {
    $vendor->updateDeals();
    $vendor->updateItems();

    return $app['twig']->render('shoppingVendor.twig', array(
        'member' => $member,
        'vendor' => $vendor
    ));
})
->convert('member', $memberProvider)
->convert('vendor', $vendorProvider);















// ERROR RESPONSE
$app->error(function (\Exception $e, $code) use ($app) {
    // Keep error messages if in debug mode
    if ($app['debug']) {
        return;
    }

    // Logic to handle errors
    switch ($code) {
        case 404:
            $message = 'The requested page could not be found. If you believe this is an error, please email the Orders
            and Transactions team: cse210-orders-transactions@googlegroups.com';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong. Please email the Orders & Transactions
            team: cse210-orders-transactions@googlegroups.com';
    }

    return new Response($message);
});

$app->run();