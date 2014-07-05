<?php

// bootstrap.php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

date_default_timezone_set("Asia/Jakarta");

require_once "vendor/autoload.php";

$paths = array(__DIR__ . "/app/src/metadata/xml");
$isDevMode = true;
$config = Setup::createXMLMetadataConfiguration($paths, $isDevMode);
$config->setCustomDatetimeFunctions(array(
    "month" => "\DoctrineExtensions\Query\Mysql\Month",
    "day" => "\DoctrineExtensions\Query\Mysql\Day",
    "year" => "\DoctrineExtensions\Query\Mysql\Year",
    "date_format" => "DoctrineExtensions\Query\Mysql\DateFormat"
));

// database configuration parameters
$conn = array(
    'driver' => 'pdo_mysql',
    'user' => 'root',
    'password' => 'root',
    'dbname' => 'XSlim'
);

// obtaining the entity manager
$em = EntityManager::create($conn, $config);

$platform = $em->getConnection()->getDatabasePlatform();
$platform->registerDoctrineTypeMapping('enum', 'string');
