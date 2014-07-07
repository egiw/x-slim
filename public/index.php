<?php

/* @var $app Slim\Slim */
/* @var $twig Twig_Environment */
/* @var $em Doctrine\ORM\EntityManager */

session_start();

// inhibit DOMPDF's auto-loader
define('DOMPDF_ENABLE_AUTOLOAD', false);
define('DOMPDF_ENABLE_REMOTE', true);
define('DOMPDF_ENABLE_CSS_FLOAT', true);

require '../bootstrap.php';
//include the DOMPDF config file (required)
require '../vendor/dompdf/dompdf/dompdf_config.inc.php';
//if you get errors about missing classes please also add:
require_once('../vendor/dompdf/dompdf/include/autoload.inc.php');

// Prepare app
$app = new \Slim\Slim(array('templates.path' => '../templates'));

$app->container->singleton("isPjax", function() use($app) {
    $request = $app->request;
    $pjax = (isset($request->headers["X-PJAX"]) && $request->headers["X-PJAX"] === "true");
    return $request->isAjax() && $pjax;
});

$app->hook("slim.after.router", function() use ($app) {
    $app->response->header("X_PJAX_URL", $_SERVER['REQUEST_URI']);
});

// Create monolog logger and store logger in container as singleton 
// (Singleton resources retrieve the same log resource definition each time)
$app->container->singleton('log', function () {
    $log = new \Monolog\Logger('slim-skeleton');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});

// Prepare view
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'debug' => true,
    'charset' => 'utf-8',
    'cache' => realpath('../templates/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);

$app->view->parserExtensions = array(
    new \Slim\Views\TwigExtension(),
    new Twig_Extension_Debug()
);

$twig = $app->view->getInstance();
$twig->addExtension(new Twig_Extensions_Extension_I18n());

$twig->addFilter(new Twig_SimpleFilter('pack', function($string) {
    $jsPacker = new JavaScriptPacker($string);
    return $jsPacker->pack();
}));

$twig->addFilter(new Twig_SimpleFilter('strftime', function(DateTime $date, $format) {
    return strftime($format, $date->getTimestamp());
}));

include_once '../routes/public.php';
include_once '../routes/admin.php';

$app->get('/test', function() use($app, $em, $twig) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($app->config('templates.path')), RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
        // force compilation
        if ($file->isFile()) {
            $twig->loadTemplate(str_replace($app->config('templates.path') . '/', '', $file));
        }
    }
});

// Run app
$app->run();
