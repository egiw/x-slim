<?php

use Respect\Validation\Validator as V;

/* @var $app Application */

// manage article
$app->group('/article', function() use ($app) {

    $validator = V::create()
            ->key('title', V::create()->notEmpty()->length(16, 60))
            ->key('content', V::create()->notEmpty()->length(160))
            ->key('status', V::create()->in(array(
                Article::STATUS_PUBLISH,
                Article::STATUS_PENDING,
                Article::STATUS_DRAFT
    )));

    // validation messages
    $messages = array(
        'title.notEmpty' => gettext('Title cannot be empty'),
        'title.length' => gettext('Title must be between 12 to 60 characters'),
        'title.notExists' => gettext('Title already exists'),
        'content.notEmpty' => gettext('Content cannot be empty'),
        'content.length' => gettext('Content must be at least 160 characters'),
        'status.in' => gettext('Invalid status')
    );

    // display list of articles
    $app->get('/', function() use ($app) {
        $app->render("admin/article/index.twig");
    })->name('admin.article.index');

    // display form and handle creation of article
    $app->map('/create', function() use($app, $validator, $messages) {
        $data = array();
        if ($app->request->isPost()) {
            try {
                $data['input'] = $input = $app->request->post();

                if ($input['status'] == Article::STATUS_PUBLISH && $app->user->isContributor()) {
                    $app->notFound();
                }

                $notExists = function($title) use ($app) {
                    return $app->db->getRepository("Articlei18n")
                                    ->findOneBy(array("title" => $title)) === null;
                };

                // validation
                $validator
                        ->key('title', V::create()->callback($notExists)->setName('notExists'))
                        ->assert($input);

                $article = new Article();
                $article->setStatus($input['status'])
                        ->setAuthor($app->user)
                        ->setCreatedAt(new DateTime('now'));

                $i18n = new Articlei18n();
                $i18n
                        ->setLanguage($input['language'])
                        ->setTitle($input['title'])
                        ->setSlug(Articlei18n::slugify($input['title']))
                        ->setContent($input['content'])
                        ->setCreatedAt(new DateTime('now'))
                        ->setAuthor($app->user)
                        ->setStatus($input['status'])
                        ->setArticle($article);

                $article->addI18n($i18n);
                $app->db->persist($article);
                $app->db->persist($i18n);
                $app->db->flush();
                $app->flash('success', 'Article has successfully created');
                $app->redirect($app->urlFor('admin.article.index'));
            } catch (InvalidArgumentException $ex) {
                $data['error'] = new Error($ex->findMessages($messages));
            }
        }
        $app->render('admin/article/create.twig', $data);
    })->via("GET", "POST")->name('admin.article.create');
    // display form and update an article
    $app->map("/:id/edit", function($id) use ($app, $validator, $messages) {
        if ($article = $app->db->find("Articlei18n", $id)) {
            /* @var $article Articlei18n */
            if (!$article->isPermitted($app->user)) {
                $app->notFound();
            }

            $data = array("article" => $article);
            if ($app->request->isPost()) {
                try {
                    $data['input'] = $input = $app->request->post();

                    if ($input['status'] == Article::STATUS_PUBLISH && $app->user->isContributor()) {
                        $app->notFound();
                    }

                    $notExists = function($title) use ($app, $article) {
                        if ($article->getTitle() == $title) {
                            return true;
                        }
                        $i18n = $app->db->getRepository("Articlei18n")
                                ->findOneBy(array("title" => $title));
                        if (null === $i18n || $article->getArticle() === $i18n->getArticle()) {
                            return true;
                        }
                        return false;
                    };

                    $validator->key('title', V::create()->callback($notExists)->setName('notExists'))
                            ->assert($input);

                    $article->setTitle($input['title'])
                            ->setSlug(Articlei18n::slugify($input['title']))
                            ->setContent($input['content'])
                            ->setUpdatedAt(new DateTime("now"))
                            ->setUpdatedBy($app->user)
                            ->setStatus($input['status']);

                    $article->getArticle()->setUpdatedAt(new DateTime("now"));

                    $app->db->flush($article);
                    $app->flash('succsss', 'Article has successfully updated');
                    $app->redirect($app->urlFor("admin.article.index"));
                } catch (InvalidArgumentException $ex) {
                    $data['error'] = new Error($ex->findMessages($messages));
                }
            }
            $app->render('admin/article/edit.twig', $data);
        }
    })->via("GET", "POST")->name("admin.article.edit");
    // display translation form
    $app->map('/:id/translate', function($id) use($app, $validator, $messages) {
        if ($article = $app->db->find("Article", $id)) {
            /* @var $article Article */

            if (!$article->isPermitted($app->user)) {
                $app->notFound();
            }

            $data = array('article' => $article);
            if ($app->request->isPost()) {
                try {
                    $data['input'] = $input = $app->request->post();

                    if ($app->user->isContributor() && $input['status'] === Article::STATUS_PUBLISH) {
                        $app->notFound();
                    }

                    $notExists = function($title) use($app, $article) {
                        $i18n = $app->db->getRepository("Articlei18n")
                                ->findOneBy(array("title" => $title));
                        return null == $i18n || $i18n->getArticle() == $article;
                    };

                    // validation
                    $validator
                            ->key("title", V::create()->callback($notExists)->setName("notExists"))
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

                    $article->addI18n($i18n)
                            ->setUpdatedAt(new DateTime("now"));

                    $app->db->persist($article);
                    $app->db->persist($i18n);
                    $app->db->flush();
                    $app->flash('success', gettext('Translation has successfully created'));
                    $app->redirect($app->urlFor("admin.article.index"));
                } catch (InvalidArgumentException $ex) {
                    $data['error'] = new Error($ex->findMessages($messages));
                }
            }
            $app->render("admin/article/translate.twig", $data);
        }
    })->via("GET", "POST")->name("admin.article.translate");
    // set article status
    $app->put('/:id/set/:status', 'ajax', function($id, $status) use($app) {
        // contributor can't publish any post
        if ($status == Article::STATUS_PUBLISH && $app->user->isContributor()) {
            $app->notFound();
        }

        if ($article = $app->db->find('Article', $id)) {
            /* @var $article Article */
            if ($article->isPermitted($app->user)) {
                $article->setStatus($status)
                        ->setUpdatedAt(new DateTime("now"))
                        ->setUpdatedBy($app->user);
                $app->db->persist($article);
                $app->db->flush();
                return $app->response->write(true);
            }
        }
        $app->notFound();
    })->conditions(array("status" => "(" . Article::STATUS_PUBLISH . "|" . Article::STATUS_DRAFT . "|" . Article::STATUS_PENDING . "|" . Article::STATUS_ARCHIVE . ")"))->name('admin.article.set');
    // delete article
    $app->delete("/:id(/:cid)", 'ajax', function($id, $cid = null) use($app) {
        if ($article = $app->db->find("Article", $id)) {
            /* @var $article Article */
            if ($cid && $i18n = $app->db->find('Articlei18n', $cid)) {
                /* @var $i18n Articlei18n */
                if (!$i18n->isPermitted($app->user))
                    $app->notFound();
                $article->removeI18n($i18n);
                $app->db->remove($i18n);
                if (!$article->getI18n()->count()) {
                    $app->db->remove($article);
                }
            } elseif ($article->getStatus() == Article::STATUS_ARCHIVE) {
                if (!$article->isPermitted($app->user))
                    $app->notFound();
                $app->db->remove($article);
            } else {
                $app->notFound();
            }
            $app->db->flush();
            return $app->response->write(true);
        }
        $app->notFound();
    })->name("admin.article.delete");
    /**
     * Serve datatables data
     * Admin or editor are allowed to see all posts
     * Contributor or author are only allowed to see own posts
     */
    $app->get('/datatables(/:status)', 'ajax', function($status = null) use($app) {
        $qb = $app->db
                ->getRepository("Articlei18n")
                ->createQueryBuilder("a")
                ->join("a.article", "b");

        if ($app->request->get('type') == 1 || $app->user->isContributor() || $app->user->isAuthor()) {
            $qb->where("b.author = :author")
                    ->setParameter("author", $app->user);
        }

        if ($status) {
            $qb->andWhere("b.status = :status")
                    ->setParameter("status", $status);
        }

        foreach ($app->request->get('order') as $order) {
            switch ((int) $order['column']) {
                case 2:
                    $qb->addOrderBy("b.createdAt", $order['dir'])
                            ->addOrderBy("a.createdAt", $order['dir']);
                    break;
                case 3:
                    $qb->addOrderBy("b.updatedAt", $order['dir'])
                            ->addOrderBy("a.updatedAt", $order['dir']);
                    break;
            }
        }

        $app->contentType("application/json");
        $data = new DataTables($qb, 'admin/article/datatables.twig', array('a.title'));
        return $app->response->write(json_encode($data));
    })->name('admin.article.datatables');


    /**
     * AJAX
     * Get count summary
     */
    $app->get('/count', 'ajax', function() use ($app) {
        $qb = $app->db->getRepository("Article")
                ->createQueryBuilder("a")
                ->select("COUNT(a)")
                ->where("a.status = :status");

        if ($app->request->get('type') == 1 || $app->user->isContributor() || $app->user->isAuthor()) {
            $qb->andWhere("a.author = :author")
                    ->setParameter("author", $app->user);
        }

        $data['publish'] = $qb->setParameter("status", Article::STATUS_PUBLISH)
                        ->getQuery()->getSingleScalarResult();
        $data['draft'] = $qb->setParameter("status", Article::STATUS_DRAFT)
                        ->getQuery()->getSingleScalarResult();
        $data['pending'] = $qb->setParameter("status", Article::STATUS_PENDING)
                        ->getQuery()->getSingleScalarResult();
        $data['archive'] = $qb->setParameter("status", Article::STATUS_ARCHIVE)
                        ->getQuery()->getSingleScalarResult();

        $app->contentType("application/json");
        return $app->response->body(json_encode($data));
    })->name('admin.article.count');
});
