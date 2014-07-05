<?php

use Respect\Validation\Validator as V;

/* @var $app Slim\Slim */
/* @var $em Doctrine\ORM\EntityManager */

// Administrator Page, only authenticated user, redirect to login page instead
$app->group(null, function() use($app, $em) {
    if (!isset($_SESSION['user'])) {
        return $app->redirect($app->urlFor('auth'), array(), "401");
    }
    $app->user = $em->find("User", $_SESSION['user']);
}, function() use($app, $em) {
    // handle user logout
    $app->get('/logout', function() use($app, $em) {
        $app->log->info("Logged out", array(
            "id" => $app->user->getId(),
            "username" => $app->user->getUsername()
        ));

        unset($_SESSION["user"]);
        session_destroy();

        $app->redirect($app->urlFor("index"));
    })->name("logout");
    // administrative page
    $app->group("/admin", function() use($app, $em) {
        $user = $app->user;
        $settings = $user->getSettings();

        $locale = "en_US.utf8";
        if (isset($settings['language'])) {
            switch ($settings['language']) {
                case 'id':
                    $locale = "id_ID.utf8";
                    break;
            }
        }

        putenv("LANG={$locale}");
        putenv("LANGUAGE={$locale}");
        setlocale(LC_MESSAGES, $locale);

        $domain = "messages";
        bindtextdomain($domain, __DIR__ . "/../locale");
        textdomain($domain);

        $app->view->set('template', $app->isPjax ? "pjax_template.twig" : "admin/template.twig");
        $app->view->set('user', $user);
        $app->view->set('languages', array(
            "id" => "Indonesian",
            "en" => "English"
        ));
    }, function() use ($app, $em) {
        // display dashboard page
        $app->get('/', function() use ($app) {
            $app->render("admin/index.twig");
        })->name("admin.index");
        // manage article
        $app->group('/article', function() use ($app, $em) {
            // display list of articles
            $app->get('/', function() use ($app, $em) {
                $app->render("admin/article/index.twig");
            })->name("admin.article.index");
            // display form and handle creation of article
            $app->map('/create', function() use($app, $em) {
                $data = array();
                if ($app->request->isPost()) {
                    try {
                        $data['input'] = $input = $app->request->post();

                        $article = new Article();
                        $article->setStatus($input['status']);

                        $i18n = new Articlei18n();
                        $i18n
                                ->setLanguage($input['language'])
                                ->setTitle($input['title'])
                                ->setSlug(slugify($input['title']))
                                ->setContent($input['content'])
                                ->setCreatedAt(new DateTime("now"))
                                ->setAuthor($app->user)
                                ->setStatus($input['status'])
                                ->setArticle($article);

                        $article->addI18n($i18n);

                        // validation
                        V::create()
                                ->attribute("title", V::create()->notEmpty()->length(12, 60))
                                ->attribute("content", V::create()->length(100, null))
                                ->assert($i18n);

                        $em->persist($article);
                        $em->persist($i18n);
                        $em->flush();

                        $app->redirect($app->urlFor("admin.article.index"));
                    } catch (InvalidArgumentException $ex) {
                        $data['errors'] = $ex->findMessages(array(
                            "title" => gettext("Title is required and between 12-60 characters"),
                            "content" => gettext("Content cannot be null and at least 100 characters")
                        ));
                    }
                }
                $app->render('admin/article/create.twig', $data);
            })->via("GET", "POST")->name('admin.article.create');
            // display form and update an article
            $app->map("/:id/edit", function($id) use ($app, $em) {
                if ($article = $em->find("Articlei18n", $id)) {
                    /* @var $article Articlei18n */
                    $data = array("article" => $article);
                    if ($app->request->isPost()) {
                        try {
                            $input = $app->request->post();

                            $article->setTitle($input['title'])
                                    ->setSlug(slugify($input['title']))
                                    ->setContent($input['content'])
                                    ->setUpdatedAt(new DateTime("now"))
                                    ->setUpdatedBy($app->user)
                                    ->setStatus($input['status']);

                            V::create()
                                    ->attribute("title", V::create()->notEmpty()->length(12, 50))
                                    ->attribute("content", V::create()->length(100, null))
                                    ->assert($article);

                            $em->persist($article);
                            $em->flush();

                            $app->redirect($app->urlFor("admin.article.index"));
                        } catch (InvalidArgumentException $ex) {
                            $data['errors'] = $ex->findMessages(array(
                                "title" => gettext("Title is required and between 12-60 characters"),
                                "content" => gettext("Content cannot be null and at least 100 characters")
                            ));
                        }
                    }
                    $app->render('admin/article/edit.twig', $data);
                }
            })->via("GET", "POST")->name("admin.article.edit");
            // display translation form
            $app->map("/:id/translate", function($id) use($app, $em) {
                if ($article = $em->find("Article", $id)) {
                    /* @var $article Article */
                    $data = array('article' => $article);
                    if ($app->request->isPost()) {
                        try {
                            $input = $app->request->post();

                            $i18n = new Articlei18n();
                            $i18n
                                    ->setLanguage($input['language'])
                                    ->setTitle($input['title'])
                                    ->setSlug(slugify($input['title']))
                                    ->setContent($input['content'])
                                    ->setCreatedAt(new DateTime("now"))
                                    ->setAuthor($app->user)
                                    ->setStatus($input['status'])
                                    ->setArticle($article);

                            $article->addI18n($i18n);

                            // validation
                            V::create()
                                    ->attribute("title", V::create()->notEmpty()->length(12, 60))
                                    ->attribute("content", V::create()->length(100, null))
                                    ->assert($i18n);

                            $em->persist($article);
                            $em->persist($i18n);
                            $em->flush();

                            $app->redirect($app->urlFor("admin.article.index"));
                        } catch (InvalidArgumentException $ex) {
                            $data['errors'] = $ex->findMessages(array(
                                "title" => gettext("Title is required and between 12-60 characters"),
                                "content" => gettext("Content cannot be null and at least 100 characters")
                            ));
                        }
                    }
                    $app->render("admin/article/translate.twig", $data);
                }
            })->via("GET", "POST")->name("admin.article.translate");
            // move article to archive
            $app->put("/:id/archive", function($id) use($app, $em) {
                /* @var $article Article */
                if ($article = $em->find("Article", $id)) {
                    $article->setStatus(StatusEnum::ARCHIVED);
                    $em->persist($article);
                    $em->flush();
                    echo true;
                } else {
                    $app->notFound();
                }
            })->name('admin.article.archive');
            // restore archived article
            $app->put("/:id/restore", function($id) use($app, $em) {
                $article = $em->find("Article", $id);
                if (null === $article || $article->getStatus() !== StatusEnum::ARCHIVED) {
                    $app->notFound();
                }
                $article->setStatus(StatusEnum::DRAFT);
                $em->persist($article);
                $em->flush();
                echo true;
            })->name('admin.article.restore');
            // publish/unpublish article
            $app->put("/:id/set/:status", function($id, $status) use($app, $em) {
                if ($article = $em->find('Article', $id)) {
                    $article->setStatus($status);
                    $em->persist($article);
                    $em->flush();
                }
                echo true;
            })->conditions(array("status" => "(" . StatusEnum::PUBLISH . "|" . StatusEnum::DRAFT . ")"))->name('admin.article.set');
            // remove article or translation from db
            $app->delete("/:id(/:cid)", function($id, $cid = null) use($app, $em) {
                if ($article = $em->find("Article", $id)) {
                    /* @var $article Article */
                    if ($cid && $i18n = $em->find('Articlei18n', $cid)) {
                        $article->removeI18n($i18n);
                        $em->remove($i18n);
                        if (!$article->getI18n()->count()) {
                            $em->remove($article);
                        }
                    } elseif ($article->getStatus() == StatusEnum::ARCHIVED) {
                        $em->remove($article);
                    }
                    $em->flush();
                    echo true;
                }
                echo false;
            })->name("admin.article.delete");
            // get datatables data
            $app->get('/datatables(/:status)', function($status = null) use($app, $em) {
                if ($app->request->isAjax() || true) {
                    $qb = $em->getRepository("Articlei18n")
                            ->createQueryBuilder("a")
                            ->join("a.article", "b");

                    if ($status) {
                        $qb->andWhere("b.status = :status")
                                ->setParameter("status", $status);
                    }

                    foreach ($app->request->get('order') as $order) {
                        switch ((int) $order['column']) {
                            case 0:
                                $qb->addOrderBy("a.title", $order['dir']);
                                break;
                            case 1:
                                $qb->addOrderBy("a.createdAt", $order['dir']);
                                break;
                            case 2:
                                $qb->addOrderBy("a.updatedAt", $order['dir']);
                                break;
                        }
                    }

                    $app->contentType("application/json");
                    $data = new DataTables($qb, 'admin/article/all.datatables.twig', array('a.title'));
                    return $app->response->write(json_encode($data));
                }
                $app->notFound();
            })->name('admin.article.datatables');
            // get status count summary
            $app->get('/count', function() use ($app, $em) {
                $dql = "SELECT COUNT(a) FROM Article a";
                $data['all'] = $em->createQuery($dql)->getSingleScalarResult();
                $dql .= " WHERE a.status = :status";
                $query = $em->createQuery($dql);
                $data['published'] = $query
                        ->setParameter("status", StatusEnum::PUBLISH)
                        ->getSingleScalarResult();
                $data['draft'] = $query
                        ->setParameter("status", StatusEnum::DRAFT)
                        ->getSingleScalarResult();
                $data['archived'] = $query
                        ->setParameter("status", StatusEnum::ARCHIVED)
                        ->getSingleScalarResult();
                $app->contentType("application/json");
                return $app->response->body(json_encode($data));
            })->name('admin.article.count');
        });

        $app->group('/user', function() use($app, $em) {
            $app->map('/settings', function() use($app, $em) {
                $user = $app->user;
                $settings = $user->getSettings();
                // store settings
                if ($app->request->isPost()) {
                    $input = $app->request->post();
                    $settings['language'] = $input['language'];
                    $user->setSettings($settings);
                    $em->flush($user);

                    $app->response->redirect($app->urlFor("admin.index"));
                }
                // render form
                $app->render("admin/user/settings.twig");
            })->via("GET", "POST")->name("user.settings");
        });
    });
});
