<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use phpbrowscap\Browscap;

/**
 * @property EntityManager $db Doctrine Entity Manager
 * @property \Monolog\Logger $log Monolog Logger
 * @property User $user Current User
 * @property Stat $stat Application statistic object
 * @property mixed $browser
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

        $this->container->singleton("browser", function() {
            $bc = new Browscap('../cache');
            return $bc->getBrowser();
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

        // get statistic object
        $this->container->singleton('stat', function() {
            $app = Application::getInstance();
            $stat = new Stat;
            $stat->setIp($app->request->getIp())
                    ->setPath($app->request->getPath())
                    ->setRid(NULL)
                    ->setBrowser($app->browser->Browser)
                    ->setVersion($app->browser->Version)
                    ->setPlatform($app->browser->Platform)
                    ->setIsMobile($app->browser->isMobileDevice)
                    ->setVisitDate(new DateTime('now'))
                    ->setReferrer($app->request->getReferrer());
            return $stat;
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

    /**
     * Get application statistic object
     * @return Stat
     */
    public function getStat() {
        return $this->stat;
    }

    /**
     * Get current browser information
     * @return mixed
     */
    public function getBrowser() {
        return $this->browser;
    }

}
