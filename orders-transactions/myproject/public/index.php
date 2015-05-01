<?php

/******************************* LOADING & INITIALIZING BASE APPLICATION ****************************************/

// Configuration for error reporting, useful to show every little problem during development
error_reporting(E_ALL);
ini_set("display_errors", 1);

// Load Composer's PSR-4 autoloader (necessary to load Slim, Mini etc.)
require '../vendor/autoload.php';

// Initialize Slim (the router/micro framework used)
$app = new \Slim\Slim();

// and define the engine used for the view @see http://twig.sensiolabs.org
$app->view = new \Slim\Views\Twig();
$app->view->setTemplatesDirectory("../Mini/view");

/******************************************* THE CONFIGS *******************************************************/

// Configs for mode "development" (Slim's default), see the GitHub readme for details on setting the environment
$app->configureMode('development', function () use ($app) {

    // pre-application hook, performs stuff before real action happens @see http://docs.slimframework.com/#Hooks
    $app->hook('slim.before', function () use ($app) {

        // SASS-to-CSS compiler @see https://github.com/panique/php-sass
        SassCompiler::run("scss/", "css/");

        // CSS minifier @see https://github.com/matthiasmullie/minify
        $minifier = new MatthiasMullie\Minify\CSS('css/style.css');
        $minifier->minify('css/style.css');

        // JS minifier @see https://github.com/matthiasmullie/minify
        // DON'T overwrite your real .js files, always save into a different file
        //$minifier = new MatthiasMullie\Minify\JS('js/application.js');
        //$minifier->minify('js/application.minified.js');
    });
});

/******************************************** THE MODEL ********************************************************/

// Initialize the model, pass the database configs. $model can now perform all methods from Mini\model\model.php
$model = new \Mini\Model\Model();

/************************************ THE ROUTES / CONTROLLERS *************************************************/

// GET request on homepage, simply show the view template index.twig
$app->get('/', function () use ($app) {
    $app->render('index.twig');
});

// GET request on /subpage, simply show the view template subpage.twig
$app->get('/subpage', function () use ($app) {
    $app->render('subpage.twig');
});

// GET request on /subpage/deeper (to demonstrate nested levels), simply show the view template subpage.deeper.twig
$app->get('/subpage/deeper', function () use ($app) {
    $app->render('subpage.deeper.twig');
});

$app->group('/members', function() use ($app, $model) {
   
   // GET request on /members
    $app->get('/', function() use ($app, $model) {
        $members = $model->getAllMembers();
        
        //$m = $model->getMember(end(array_values($members)).id);

        $app->render('members.twig', array(
            'members' => $members
        ));
   });
});

/******************************************* RUN THE APP *******************************************************/

$app->run();