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

use Symfony\Component\HttpFoundation\Request;
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

$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

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

$itemProvider = function ($id) use ($app) {
    return new Item($id, $app['httpClient']);
};

$localDebugging = false;
if ($localDebugging && !isset($_COOKIE['sessionToken']) && isset($_COOKIE['memberEmail'])) {
    setrawcookie('sessionToken', 'fc84ade4-7914-4796-8830-d763896aa136');
    $_COOKIE['sessionToken'] = 'fc84ade4-7914-4796-8830-d763896aa136';
    setrawcookie('memberEmail', 'seb@test.com');
    $_COOKIE['memberEmail'] = 'seb@test.com';
}
// check if sessionToken exists
if(!isset($_COOKIE['sessionToken'])) {
    header("Location: http://ineed-members.mybluemix.net/auth?redirectUrl=http%3A%2F%2Forders.mybluemix.net");
    die();
}
// check if sessionToken has expired
else if(!$localDebugging){
    $res = $app['httpClient']->get("https://ineed-db.mybluemix.net/api/sessions?sessionToken={$_COOKIE['sessionToken']}");
    if(empty($res->json())) {
        header("Location: http://ineed-members.mybluemix.net/auth?redirectUrl=http%3A%2F%2Forders.mybluemix.net");
        die();
    }
}
session_id($_COOKIE['sessionToken']);
session_start();
if (!isset($_SESSION['products'])) {
	$_SESSION['products'] = array();
}

// API ROUTES AND LOGIC

// TODO: This should probably be api/v2/purchase/deal/member/deal
$app->get('api/v1/purchase/{member}/{deal}', function (Member $member, Deal $deal) use ($app) {
    // TODO: We are the only team to use tax
    $order = new Order('seb/mar testing', $member, $deal->getPrice(), 0, $app['httpClient']);
    $order->addTransaction($deal, 1/*quantity*/);
    $order->placeOrder();
    return $app->json(array('transactionId' => $order->getTransactions()[0]->getID()));
})
->convert('member', $memberProvider)
->convert('deal',   $dealProvider);

$app->get('api/v1/purchase/item/{member}/{item}', function (Member $member, Item $item) use ($app) {
    // TODO: We are the only team to use tax
    $order = new Order('seb/mar testing', $member, $item->getPrice(), 0, $app['httpClient']);
    $order->addTransaction($item, $item->getVendor(), 1);
    $order->placeOrder();
    return $app->json(array('transactionId' => $order->getTransactions()[0]->getID()));
})
->convert('member', $memberProvider)
->convert('vendor', $vendorProvider)
->convert('item', $itemProvider);


$app->get('api/v1/members/{member}/orders', function (Member $member) use ($app) {
    $result = array();
    /** @var Order $order */
    foreach($member->getOrderHistory() as $order) {
        array_push($result, $order);
    }

    return $app->json($result);
})
->convert('member', $memberProvider); // construct member class

$app->get('api/v1/vendors/{vendor}/transactions', function (Vendor $vendor) use ($app) {
    $result = array();

    /** @var Transaction $trans */
    foreach($vendor->getTransactionHistory() as $trans) {
        array_push($result, $trans);
    }

    return $app->json($result);
})
->convert('vendor', $vendorProvider);

$app->get('api/v1/vendors/transactions', function () use ($app) {
    $result = array();
    foreach(Vendor::getAllVendorHistory($app['httpClient']) as $transactions) {
        /** @var Transaction $transaction */
        foreach($transactions as $transaction) {
            array_push($result, $transaction);
        }
    }
    return $app->json($result);
})
->convert('vendor', $vendorProvider);

$app->get('api/v1/vendors/transactions/deals', function () use ($app) {
    $result = array();

    foreach(Vendor::getAllDealsPurchased($app['httpClient']) as $deals) {
        /** @var Transaction $dealTrans */
        foreach($deals as $dealTrans) {
            array_push($result, array(
                'transactionId' => $dealTrans->getID(),
                'member_email' => $dealTrans->getMember()->getEmail(),
                'dealId' => $dealTrans->getDeal()->getID()));
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
    //$app['session']->clear();
    //$vendor->updateDeals();
    $vendor->updateItems();

    return $app['twig']->render('shoppingVendor.twig', array(
        'member' => $member,
        'vendor' => $vendor,
        'products' => $_SESSION['products']
    ));
})
->convert('member', $memberProvider)
->convert('vendor', $vendorProvider)
->bind('vendorShopping');

$app->post('members/{member}/shopping/{vendor}/cart_update', function (Member $member, Vendor $vendor, Request $form) use ($app) {
    $item_id = $form->get('id');

    // Empty cart
    if(!is_null($form->get('emptycart')) && $form->get('emptycart') == 1) {
        unset($_SESSION['products']);
        $_SESSION['products'] = array();
    }

    // Add item to shopping cart
    if(!is_null($form->get('type')) && $form->get('type') == 'add') {
        /** @var Item $item */
        $item = new Item($item_id, $app['httpClient']);
        $qty = $form->get('qty');
        $product[] = array();

        // prepare array for session variable
        $new_product = array('name' => $item->getName(), 'id' => $item->getID(), 'qty' => $qty, 'price' => $item->getPrice());

        if(!empty($_SESSION['products'])) { // if we have the session
            $found = false;
			foreach ($_SESSION['products'] as $key => $cart_itm) {
                if(empty($cart_itm))
                    continue;
                if($cart_itm['id'] == $item_id) { // item exists in array, update qty
                    $found = true;
                    $foundInd = $key;
                }
            }
            if($found == false) { //we didn't find item in array
                array_push($_SESSION['products'], $new_product);
            }
            else {
                $_SESSION['products'][$foundInd]['qty'] = $qty;
            }
        }
        else {
           array_push($_SESSION['products'], $new_product);
        }
    }

    // Remove item from shopping cart
    if(!is_null($form->get('type')) && $form->get('type') == 'remove' && !empty($_SESSION['products'])) {
        foreach ($_SESSION['products'] as $key => $cart_itm) {
            if ($cart_itm['id'] == $item_id) {
                unset($_SESSION['products'][$key]);
            }
        }
    }

    // Return to shopping route
    return $app->redirect($app['url_generator']->generate('vendorShopping', array('member' => $member->getEmail(), 'vendor' => $vendor->getID())));
})
->convert('member', $memberProvider)
->convert('vendor', $vendorProvider);

$app->get('members/{member}/shopping/{vendor}/view_cart', function (Member $member, Vendor $vendor) use ($app) {
    /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
    $session = $app['session'];

    return $app['twig']->render('viewCart.twig', array(
        'member' => $member,
        'vendor' => $vendor,
    ));
})
->convert('member', $memberProvider)
->convert('vendor', $vendorProvider);

$app->post('members/{member}/shopping/{vendor}/checkout', function (Request $form) use ($app) {
    echo "TODO: Process paypal here, see viewCart.twig for information given to us in the form";
    echo $form->get('num_cart_items');
});






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