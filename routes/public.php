<?php

use Doctrine\ORM\Tools\Pagination\Paginator;

/* @var $app Slim\Slim */
/* @var $em Doctrine\ORM\EntityManager */

// public route
$app->group(null, function() use($app, $em) {
    $qb = $em->getRepository("Articlei18n")
            ->createQueryBuilder("a")
            ->join("a.article", "b")
            ->where("b.status = :status")
            ->andWhere("a.language = :language")
            ->andWhere("a.status = :status");

    // todo (add cache system)
    $qb->select(array(
                "DATE_FORMAT(a.createdAt, '%M %Y') AS name",
                "DATE_FORMAT(a.createdAt, '%m') AS month",
                "DATE_FORMAT(a.createdAt, '%Y') AS year",
                "COUNT(a.id) AS total"
            ))
            ->groupBy("name")
            ->orderBy("name", "DESC")
            ->setParameters(array(
                "language" => "id",
                "status" => StatusEnum::PUBLISH
    ));
    $app->view->set('archives', $qb->getQuery()->getResult());
    $app->view->set('template', $app->isPjax ? "pjax_template.twig" : "template.twig");
}, function() use($app, $em) {
    // display main page
    $app->get('/', function() use($app, $em) {
        $limit = 3;

        $page = $app->request->get("page", 1);
        $qb = $em->getRepository("Articlei18n")
                ->createQueryBuilder("a")
                ->join("a.article", "b")
                ->where("b.status = :status")
                ->andWhere("a.status = :status")
                ->andWhere("a.language = :language")
                ->orderBy("a.createdAt", "DESC")
                ->addOrderBy("a.updatedAt", "DESC")
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

        $qb->setParameters(array(
            "status" => StatusEnum::PUBLISH,
            "language" => "id"
        ));

        $articles = new Paginator($qb);

        $pages = ceil(count($articles) / $limit);

        // Sample log message
        $app->log->info("Slim-Skeleton '/' route");

        // Render index view
        $app->render('index.twig', array(
            'articles' => $articles,
            "page" => $page,
            "hasPrev" => $page - 1 != 0,
            "hasNext" => $page + 1 <= $pages
        ));
    })->name('index');
    // display archive page
    $app->get('/(:year(/:month(/:day(/:slug))))', function($year = null, $month = null, $day = null, $slug = null) use ($app, $em) {
        $repo = $em->getRepository("Article");
        $qb = $repo->createQueryBuilder("a");

        $qb->where("a.status = :status")
                ->setParameter("status", StatusEnum::PUBLISH);

        if (null !== $year)
            $qb->andWhere("YEAR(a.createdAt) = :year")
                    ->setParameter("year", $year);

        if (null !== $month)
            $qb->andWhere("MONTH(a.createdAt) = :month")
                    ->setParameter("month", $month);

        if (null !== $day)
            $qb->andWhere("DAY(a.createdAt) = :day")
                    ->setParameter("day", $day);

        if (null !== $slug) {
            $qb->andWhere("a.slug = :slug")
                    ->setParameter("slug", $slug);

            $article = $qb->getQuery()->getOneOrNullResult();

            if (null == $article)
                return $app->pass();

            $app->render("detail.twig", array(
                "article" => $article,
                "title" => $article->getTitle()
            ));
        } else {
            $limit = 2;

            $page = $app->request->get('page', 1);
            $qb->setFirstResult(($page - 1) * $limit)
                    ->setMaxResults($limit);

            $articles = new Paginator($qb);

// nothing found :(
            if (count($articles) == 0)
                return $app->pass();

            $pages = ceil(count($articles) / $limit);

            $app->render("archive.twig", array(
                "articles" => $articles,
                "page" => $page,
                "hasPrev" => $page - 1 != 0,
                "hasNext" => $page + 1 <= $pages,
                "title" => date('F Y', mktime(0, 0, 0, $month, $day, $year))
            ));
        }
    })->name("article.archive")->conditions(array("year" => "\d", "month" => "\d", "day" => "\d"));
});

// only unauthenticated user, redirect to homepage instead
$app->group(null, function() use($app) {
    if (isset($_SESSION['user']))
        $app->redirect($app->urlFor('index'));
}, function() use($app, $em) {
    // handle user login
    $app->map('/auth', function() use($app, $em) {
        // An user is trying to login..process credential
        if ($app->request->isPost()) {
            $input = $app->request->post();

            // Find the user
            $user = $em->getRepository("User")
                    ->findOneBy(array("username" => $input['username']));

            // Let's check whether the user found and the provided password is correct
            if (null !== $user && password_verify($input['password'], $user->getPassword())) {
                $app->log->info('Authenticated', array(
                    "id" => $user->getId(),
                    "username" => $user->getUsername()
                ));

                $_SESSION['user'] = $user->getId();

                $app->redirect($app->urlFor("admin.index"));
            }
        }

// render login page
        $app->render('auth.twig');
    })->via("GET", "POST")->name("auth");
    // handle user register
    $app->get('/register', function() use($app, $em) {
        $user = new User;
        $user->setUsername("testing")
                ->setPassword(password_hash("testing", PASSWORD_BCRYPT))
                ->setFullname("Test Account")
                ->setEmail("test@myblog.com");

        $em->persist($user);
        $em->flush();

        echo "An user has registered";
    });
});
