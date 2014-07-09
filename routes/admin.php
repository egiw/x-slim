<?php

use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\AllOfException;

/* @var $app Slim\Slim */
/* @var $em Doctrine\ORM\EntityManager */

/**
 * Administrator section
 * Restrict guest user, redirect to auth page
 */
$app->group(null, function() use($app) {
    if ($app->user->isGuest()) {
        $app->flash(ALERT_DANGER, gettext("Restricted area, access denied!"));
        $app->redirect($app->urlFor("auth"), 401);
    }
}, function() use($app, $em) {
    // handle user logout
    $app->get('/logout', function() use($app, $em) {
        unset($_SESSION["user"]);
        session_destroy();

        $app->redirect($app->urlFor("index"));
    })->name("logout");
    // administrative page
    $app->group("/admin", function() use($app, $em) {
        $settings = $app->user->getSettings();

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
        setlocale(LC_TIME, $locale);

        $domain = "messages";
        bindtextdomain($domain, __DIR__ . "/../locale");
        textdomain($domain);

        $app->view->set('template', $app->isPjax ? "pjax_template.twig" : "admin/template.twig");
        $app->view->set('languages', array(
            "id" => gettext("Indonesian"),
            "en" => gettext("English")
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

                        $notExists = function($title) use ($em) {
                            return $em->getRepository("Articlei18n")
                                            ->findOneBy(array("title" => $title)) === null;
                        };

                        // validation
                        V::create()
                                ->key("title", V::create()->notEmpty()->length(16, 60))
                                ->key("title", V::create()->callback($notExists)->setName("notExists"))
                                ->key("content", V::create()->notEmpty()->length(160))
                                ->assert($input);

                        $article = new Article();
                        $article->setStatus($input['status']);

                        $i18n = new Articlei18n();
                        $i18n
                                ->setLanguage($input['language'])
                                ->setTitle($input['title'])
                                ->setSlug(Articlei18n::slugify($input['title']))
                                ->setContent($input['content'])
                                ->setCreatedAt(new DateTime("now"))
                                ->setAuthor($app->user)
                                ->setStatus($input['status'])
                                ->setArticle($article);
                        $article->addI18n($i18n);
                        $em->persist($article);
                        $em->persist($i18n);
                        $em->flush();
                        $app->redirect($app->urlFor("admin.article.index"));
                    } catch (InvalidArgumentException $ex) {
                        $data['error'] = new Error($ex->findMessages(array(
                                    "title.notEmpty" => gettext("Title cannot be empty"),
                                    "title.length" => gettext("Title must be between 12 to 60 characters"),
                                    "title.notExists" => gettext("Title already exists"),
                                    "content.notEmpty" => gettext("Content cannot be empty"),
                                    "content.length" => gettext("Content must be at least 160 characters")
                        )));
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
                            $data['input'] = $input = $app->request->post();

                            $notExists = function($title) use ($article, $em) {
                                if ($article->getTitle() == $title)
                                    return true;

                                $i18n = $em->getRepository("Articlei18n")
                                        ->findOneBy(array("title" => $title));
                                if (null === $i18n || $article->getArticle() === $i18n->getArticle())
                                    return true;

                                return false;
                            };

                            // validation
                            V::create()
                                    ->key("title", V::create()->notEmpty()->length(16, 60))
                                    ->key("title", V::create()->callback($notExists)->setName("notExists"))
                                    ->key("content", V::create()->notEmpty()->length(160))
                                    ->assert($input);

                            $article->setTitle($input['title'])
                                    ->setSlug(Articlei18n::slugify($input['title']))
                                    ->setContent($input['content'])
                                    ->setUpdatedAt(new DateTime("now"))
                                    ->setUpdatedBy($app->user)
                                    ->setStatus($input['status']);

                            $em->persist($article);
                            $em->flush();

                            $app->redirect($app->urlFor("admin.article.index"));
                        } catch (InvalidArgumentException $ex) {
                            $data['error'] = new Error($ex->findMessages(array(
                                        "title.notEmpty" => gettext("Title cannot be empty"),
                                        "title.length" => gettext("Title must be between 12 to 60 characters"),
                                        "title.notExists" => gettext("Title already exists"),
                                        "content.notEmpty" => gettext("Content cannot be empty"),
                                        "content.length" => gettext("Content must be at least 160 characters")
                            )));
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
                            $data['input'] = $input = $app->request->post();

                            $notExists = function($title) use($article, $em) {
                                $i18n = $em->getRepository("Articlei18n")
                                        ->findOneBy(array("title" => $title));
                                return null == $i18n || $i18n->getArticle() == $article;
                            };

                            // validation
                            V::create()
                                    ->key("title", V::create()->notEmpty()->length(16, 60))
                                    ->key("title", V::create()->callback($notExists)->setName("notExists"))
                                    ->key("content", V::create()->length(160))
                                    ->assert($input);

                            $i18n = new Articlei18n();
                            $i18n
                                    ->setLanguage($input['language'])
                                    ->setTitle($input['title'])
                                    ->setSlug(Articlei18n::slugify($input['title']))
                                    ->setContent($input['content'])
                                    ->setCreatedAt(new DateTime("now"))
                                    ->setAuthor($app->user)
                                    ->setStatus($input['status'])
                                    ->setArticle($article);

                            $article->addI18n($i18n);

                            $em->persist($article);
                            $em->persist($i18n);
                            $em->flush();

                            $app->redirect($app->urlFor("admin.article.index"));
                        } catch (InvalidArgumentException $ex) {
                            $data['error'] = new Error($ex->findMessages(array(
                                        "title.notEmpty" => gettext("Title cannot be empty"),
                                        "title.length" => gettext("Title must be between 12 to 60 characters"),
                                        "title.notExists" => gettext("Title already exists"),
                                        "content.notEmpty" => gettext("Content cannot be empty"),
                                        "content.length" => gettext("Content must be at least 160 characters")
                            )));
                        }
                    }
                    $app->render("admin/article/translate.twig", $data);
                }
            })->via("GET", "POST")->name("admin.article.translate");
            // move article to archive
            $app->put("/:id/archive", function($id) use($app, $em) {
                /* @var $article Article */
                if ($article = $em->find("Article", $id)) {
                    $article->setStatus(Article::STATUS_ARCHIVE);
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
                if (null === $article || $article->getStatus() !== Article::STATUS_ARCHIVE) {
                    $app->notFound();
                }
                $article->setStatus(Article::STATUS_DRAFT);
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
            })->conditions(array("status" => "(" . Article::STATUS_PUBLISH . "|" . Article::STATUS_DRAFT . ")"))->name('admin.article.set');
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
                    } elseif ($article->getStatus() == Article::STATUS_ARCHIVE) {
                        $em->remove($article);
                    }
                    $em->flush();
                    echo true;
                }
                echo false;
            })->name("admin.article.delete");
            // get datatables data
            $app->get('/datatables(/:status)', function($status = null) use($app, $em) {
                if ($app->request->isAjax()) {
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
                $dql = "SELECT COUNT(a) FROM Article a LEFT JOIN a.i18n b WHERE b.id IS NULL";
                $data['all'] = $em->createQuery($dql)->getSingleScalarResult();
                $dql .= " AND a.status = :status";
                $query = $em->createQuery($dql);
                $data['published'] = $query
                        ->setParameter("status", Article::STATUS_PUBLISH)
                        ->getSingleScalarResult();
                $data['draft'] = $query
                        ->setParameter("status", Article::STATUS_DRAFT)
                        ->getSingleScalarResult();
                $data['archived'] = $query
                        ->setParameter("status", Article::STATUS_ARCHIVE)
                        ->getSingleScalarResult();
                $app->contentType("application/json");
                return $app->response->body(json_encode($data));
            })->name('admin.article.count');
        });
        // handle account settings
        $app->group('/account', function() use($app, $em) {
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
                $app->render("admin/account/settings.twig");
            })->via("GET", "POST")->name("account.settings");
        });

        /**
         * User management
         * Admin only
         */
        $app->group('/user', function() use($app) {
            if (!$app->user->isAdmin())
                $app->notFound();
        }, function() use($app, $em) {
            /**
             * Display list of users
             */
            $app->get('/', function() use($app, $em) {
                $app->render('admin/user/index.twig');
            })->setName('admin.user.index');

            /**
             * Display form and handle user creation 
             * 
             * @todo Validate username
             * @todo Option for send notification email
             * @todo Option for generate random password
             */
            $app->map('/create', function() use($app, $em) {
                $data = array();
                if ($app->request->isPost()) {
                    $data['input'] = $input = $app->request->post();
                    try {
                        // check whether username available
                        $notExists = function($username) use($em) {
                            $user = $em->getRepository('User')->findOneBy(array('username' => $username));
                            return $user === null;
                        };

                        V::create()
                                ->key('username', V::create()->notEmpty()->length(4, 16)->callback($notExists))
                                ->key('email', V::create()->notEmpty()->email())
                                ->key('password', V::create()->notEmpty()->length(6))
                                ->key('passwordConfirmation', V::create()->notEmpty()->equals($input['password']))
                                ->key('role', V::create()->notEmpty())
                                ->assert($input);

                        $user = new User;
                        $user->setUsername($input['username'])
                                ->setEmail($input['email'])
                                ->setPassword(password_hash($input['password'], PASSWORD_BCRYPT))
                                ->setRole($input['role']);

                        $em->persist($user);
                        $em->flush();

                        $app->flash(ALERT_SUCCESS, gettext('User has been successfully created'));
                        $app->redirect($app->urlFor("admin.user.index"));
                    } catch (AllOfException $ex) {
                        $data['error'] = new Error($ex->findMessages(array(
                                    'username.notEmpty' => gettext('Username cannot be empty'),
                                    'username.length' => gettext('Username must be between 4 and 16 characters'),
                                    'username.callback' => gettext('Username already exists'),
                                    'email.notEmpty' => gettext('Email address cannot be empty'),
                                    'email.email' => gettext('Invalid email address'),
                                    'password.notEmpty' => gettext('Password cannot be empty'),
                                    'password.length' => gettext('Password must be at least 6 characters'),
                                    'passwordConfirmation.notEmpty' => gettext('Please retype password'),
                                    'passwordConfirmation.equals' => gettext('Password confirmation doesn\'t match'),
                                    'role.notEmpty' => gettext('Role must be selected')
                        )));
                    }
                }
                $app->render('admin/user/create.twig', $data);
            })->via("GET", "POST")->setName('admin.user.create');

            /**
             * Display edit form and update user
             * @todo Send email notification if password changed
             */
            $app->map('/:id/edit', function($id) use($app, $em) {
                if ($user = $em->find("User", $id)) {
                    /* @var $user User */
                    $data = array("user" => $user);
                    if ($app->request->isPost()) {
                        try {
                            $data['input'] = $input = $app->request->post();

                            $notExists = function($username) use($em, $user) {
                                if ($user->getUsername() === $username) {
                                    return true;
                                } elseif ($em->getRepository("User")->findOneBy(array("username" => $username)) === null) {
                                    return true;
                                } else {
                                    return false;
                                }
                            };

                            $validator = V::create()
                                    ->key("username", V::create()->notEmpty()->length(4, 16)->callback($notExists))
                                    ->key('email', V::create()->notEmpty()->email())
                                    ->key("password", V::create()->length(6))
                                    ->key('role', V::create()->notEmpty());

                            $user->setUsername($input['username'])
                                    ->setEmail($input['email'])
                                    ->setRole($input['role']);

                            if (!empty($input['password'])) {
                                $validator->key("passwordConfirmation", V::create()->notEmpty()->equals($input['password']));
                                $user->setPassword(password_hash($input['password'], PASSWORD_BCRYPT));
                            }

                            $validator->assert($input);

                            $em->persist($user);
                            $em->flush();

                            $app->redirect($app->urlFor('admin.user.index'));
                        } catch (AllOfException $ex) {
                            $data['error'] = new Error($ex->findMessages(array(
                                        'username.notEmpty' => gettext('Username cannot be empty'),
                                        'username.length' => gettext('Username must be between 4 and 16 characters'),
                                        'username.callback' => gettext('Username already exists'),
                                        'email.notEmpty' => gettext('Email address cannot be empty'),
                                        'email.email' => gettext('Invalid email address'),
                                        'password.length' => gettext('Password must be at least 6 characters'),
                                        'passwordConfirmation.notEmpty' => gettext('Retype password'),
                                        'passwordConfirmation.equals' => gettext('Password confirmation doesn\'t match'),
                                        'role.notEmpty' => gettext('Role must be selected')
                            )));
                        }
                    }
                    $app->render("admin/user/edit.twig", $data);
                }
            })->via("GET", "POST")->setName("admin.user.edit");

            /**
             * Handle user deletion
             */
            $app->delete('/:id', function($id) use($app, $em) {
                if ($user = $em->find('User', $id)) {
                    $em->remove($user);
                    $em->flush();
                    return $app->response->write(json_encode(true));
                }
            })->setName('admin.user.delete');

            /**
             * Serve datatables json data
             */
            $app->get('/datatables', function() use($app, $em) {
                if ($app->request->isAjax() || true) {
                    $qb = $em->getRepository("User")
                            ->createQueryBuilder("u");

                    $app->contentType("application/json");
                    $data = new DataTables($qb, 'admin/user/datatables.twig');
                    return $app->response->write(json_encode($data));
                }
            })->setName('admin.user.datatables');
        });
    });
});
