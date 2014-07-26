<?php

require_once "../vendor/autoload.php";

/* @var $app Slim\Slim */
/* @var $twig Twig_Environment */

session_start();

if (!defined('LC_MESSAGES'))
    define('LC_MESSAGES', 5);

define('ALERT_SUCCESS', 'alert-success');
define('ALERT_DANGER', 'alert-danger');
define('ALERT_WARNING', 'alert-warning');
define('ALERT_INFO', 'alert-info');

include '../lib/functions.php';

date_default_timezone_set("Asia/Jakarta");

$config = array(
    'templates.path' => '../templates',
    'db' => array(
        'driver' => 'pdo_mysql',
        'user' => 'root',
        'password' => 'root',
        'dbname' => 'XSlim'
    )
);

// Prepare app
$app = new Application($config);
$app->setName("X-Slim");


$app->view->set('_user', $app->user);

$app->hook("slim.after.router", function() use ($app) {
    $app->response->header("X_PJAX_URL", $_SERVER['REQUEST_URI']);
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

$app->get('/test', function() use($app, $twig) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($app->config('templates.path')), RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
// force compilation
        if ($file->isFile()) {
            $twig->loadTemplate(str_replace($app->config('templates.path') . '/', '', $file));
        }
    }
});

// Run app
$app->run();
