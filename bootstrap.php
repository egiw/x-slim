<?php

// bootstrap.php
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;


$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . "/src"), $isDevMode);

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
