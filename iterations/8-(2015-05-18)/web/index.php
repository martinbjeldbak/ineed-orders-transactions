<?php

// PLEASE SEE: http://silex.sensiolabs.org/doc/usage.html

require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../models/Member.php';
require_once __DIR__.'/../models/Vendor.php';
require_once __DIR__.'/../models/Deal.php';
require_once __DIR__.'/../models/Order.php';
require_once __DIR__.'/../models/OrderState.php';
require_once __DIR__.'/../models/Transaction.php';
require_once __DIR__.'/../models/TransactionState.php';

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

$app->get('api/v1/purchase/{member}/{deal}', function (Member $member, Deal $deal) use ($app) {
    // TODO: We are the only team to use tax
    $order = new Order('seb/mar testing', $member, $deal->price, 0, $app['httpClient']);
    $order->addTransaction($deal, 1/*quantity*/);
    $order->placeOrder();
    return $app->json(array('transactionId' => $order->transaction->id));
})
->convert('member', $memberProvider)
->convert('deal', $dealProvider);

$app->get('api/v1/members/{member}/orders', function (Member $member) use ($app) {
    $result = array();
    /** @var Order $order */
    foreach($member->getOrderHistory() as $order) {
        array_push($result, $order->toJsonObject());
    }

    return $app->json($result);
})
->convert('member', $memberProvider); // construct member class

$app->get('api/v1/vendors/{vendor}/transactions', function (Vendor $vendor) use ($app) {
    $result = array();

    /** @var Transaction $trans */
    foreach($vendor->getTransactionHistory() as $trans) {
        array_push($result, $trans->toJsonObject());
    }

    return $app->json($result);
})
->convert('vendor', $vendorProvider);

$app->get('api/v1/vendors/transactions', function () use ($app) {
    $result = array();
    foreach(Vendor::getAllVendorHistory($app['httpClient']) as $transactions) {
        /** @var Transaction $transaction */
        foreach($transactions as $transaction) {
            array_push($result, $transaction->toJsonObject());
        }
    }
    return $app->json($result);
})
->convert('vendor', $vendorProvider);

$app->get('api/v1/vendors/transactions/deals', function () use ($app) {
    $result = array();

    foreach(Vendor::getAllDealsPurchased($app['httpClient']) as $deals) {
        /** @var Transaction $trans */
        foreach($deals as $trans) {
            array_push($result, array(
                'member_email' => $trans->getMember()->email,
                'dealId' => $trans->deal->id));
        }
    }
    return $app->json($result);
})
    ->convert('vendor', $vendorProvider);







// VIEW ROUTES AND LOGIC
$app->get('/', function() use ($app) {
    return $app['twig']->render('index.twig');
});

$app->get('members/{member}/shopping', function (Member $member) use ($app) {

    return $app['twig']->render('shopping.twig', array(
        'member' => $member,
        'vendors' => Vendor::getAllVendors($app['httpClient'])
    ));
})
->convert('member', $memberProvider);

$app->get('members/{member}/shopping/{vendor}', function (Member $member, Vendor $vendor) use ($app) {
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