<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

/**
 * @property EntityManager $db Doctrine Entity Manager
 * @property \Monolog\Logger $log Monolog Logger
 * @property User $user Current User
 */
class Application extends \Slim\Slim {

    public function __construct(array $userSettings = array()) {
        parent::__construct($userSettings);

        $this->container->singleton('db', function($c) {
            $conn = $c['settings']['db'];
            $isDevMode = true;
            $config = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . '/../src'), $isDevMode);
            $config->setCustomDatetimeFunctions(array(
                "month" => "\DoctrineExtensions\Query\Mysql\Month",
                "day" => "\DoctrineExtensions\Query\Mysql\Day",
                "year" => "\DoctrineExtensions\Query\Mysql\Year",
                "date_format" => "DoctrineExtensions\Query\Mysql\DateFormat"
            ));

            // obtaining the entity manager
            $em = EntityManager::create($conn, $config);

            $platform = $em->getConnection()->getDatabasePlatform();
            $platform->registerDoctrineTypeMapping('enum', 'string');

            return $em;
        });

        $this->container->singleton("isPjax", function() {
            $request = $this->request;
            $pjax = (isset($request->headers["X-PJAX"]) && $request->headers["X-PJAX"] === "true");
            return $request->isAjax() && $pjax;
        });

        // Create monolog logger and store logger in container as singleton 
        // (Singleton resources retrieve the same log resource definition each time)
        $this->container->singleton('log', function () {
            $log = new \Monolog\Logger($this->getName());
            $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
            return $log;
        });

        // get current user object
        $this->container->singleton('user', function() {
            if (isset($_SESSION['uid'])) {
                $user = $this->db->find('User', $_SESSION['uid']);
                if (null !== $user) {
                    return $user;
                } else {
                    unset($_SESSION['uid']);
                }
            }

            $guest = new User;
            $guest->setUsername('guest')
                    ->setRole(User::ROLE_GUEST);

            return $guest;
        });
    }

    /**
     * @return EntityManager Doctrine Entity Manager
     */
    public function getDb() {
        return $this->db;
    }

    /**
     * @return \Monolog\Logger
     */
    public function getLog() {
        return $this->log;
    }

    /**
     * 
     * @return User
     */
    public function getUser() {
        return $this->user;
    }

}
