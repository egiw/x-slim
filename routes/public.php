<?php

use Doctrine\ORM\Tools\Pagination\Paginator;

/* @var $app Application */
/* @var $em Doctrine\ORM\EntityManager */

/**
 * Public page
 * Everyone has access
 */
$app->group(null, function() use($app) {
    $qb = $app->db->getRepository("Articlei18n")
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
                "status" => Article::STATUS_PUBLISH
    ));
    $app->view->set('archives', $qb->getQuery()->getResult());
    $app->view->set('template', $app->isPjax ? "pjax_template.twig" : "template.twig");
}, function() use($app) {
    // display main page
    $app->get('/', function() use($app) {
        $limit = 3;

        $page = $app->request->get("page", 1);
        $qb = $app->db->getRepository("Articlei18n")
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
            "status" => Article::STATUS_PUBLISH,
            "language" => "id"
        ));

        $articles = new Paginator($qb);

        $pages = ceil(count($articles) / $limit);

        // Render index view
        $app->render('index.twig', array(
            'articles' => $articles,
            "page" => $page,
            "hasPrev" => $page - 1 != 0,
            "hasNext" => $page + 1 <= $pages
        ));
    })->name('index');
    // display archive page
    $app->get('/(:year(/:month(/:day(/:slug))))', function($year = null, $month = null, $day = null, $slug = null) use ($app) {
        $repo = $app->db->getRepository("Articlei18n");
        $qb = $repo->createQueryBuilder("a");

        $qb->where("a.status = :status")
                ->setParameter("status", Article::STATUS_PUBLISH);

        $qb->andWhere("a.language = 'id'");

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
            /* @var $article Articlei18n */

            if (null == $article)
                return $app->pass();

            // track
            $article->addStat($app->stat);
            $app->db->flush($article);

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
    })->conditions(array("year" => "\d+", "month" => "\d+", "day" => "\d+"))->name("article.archive");
});

/**
 * Guest Only
 * Redirect to homepage instead
 */
$app->group(null, function() use($app) {
    if (!$app->user->isGuest())
        return $app->redirect($app->urlFor("index"));
}, function() use($app) {
    // handle user login
    $app->map('/auth', function() use($app) {
        // An user is trying to login..process credential
        if ($app->request->isPost()) {
            $input = $app->request->post();

            // Find the user
            $user = $app->db->getRepository("User")
                    ->findOneBy(array("username" => $input['username']));

            // Let's check whether the user found and the provided password is correct
            if (null !== $user && password_verify($input['password'], $user->getPassword())) {
                $_SESSION['uid'] = $user->getId();
                $app->redirect($app->urlFor("admin.index"));
            } else {
                $app->flashNow(ALERT_DANGER, gettext("Invalid username or password"));
            }
        }

// render login page
        $app->render('auth.twig');
    })->via("GET", "POST")->name("auth");
    // handle user register
    $app->get('/register', function() use($app) {
        $user = new User;
        $user->setUsername("admin")
                ->setPassword(password_hash("admin", PASSWORD_BCRYPT))
                ->setFullname("Administrator")
                ->setEmail("admin@domain.tld");

        $app->db->persist($user);
        $app->db->flush();

        echo "An user has registered";
    });
});
