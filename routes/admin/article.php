<?php

use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\AllOfException;

/* @var $app Application */
$validator = V::create()
        ->key('title', V::create()->notEmpty()->length(16, 60))
        ->key('content', V::create()->notEmpty()->length(160))
        ->key('status', V::create()->in(array(
            STATUS_PUBLISH,
            STATUS_PENDING,
            STATUS_DRAFT
        )));

// validation messages
$messages = array(
    'title.notEmpty' => gettext('Title cannot be empty'),
    'title.length' => gettext('Title must be between 12 to 60 characters'),
    'title.notExists' => gettext('Title already exists'),
    'content.notEmpty' => gettext('Content cannot be empty'),
    'content.length' => gettext('Content must be at least 160 characters'),
    'status.in' => gettext('Invalid status'),
    'image.notEmpty' => gettext('Please upload a featured image')
);

// manage article
$app->group('/article', function() use ($app, $validator, $messages) {

    // INDEX
    $app->get('/', function() use ($app) {
        $app->render("admin/article/index.twig");
    })->name('admin.article.index');

    // CREATE
    $app->map('/create', function() use($app, $validator, $messages) {
        $data = array();

        // handle post
        if ($app->request->isPost()) {
            try {
                $data['input'] = $input = $app->request->post();

                // contributor is prohibited to publish posts
                if ($input['status'] == STATUS_PUBLISH && $app->user->isContributor()) {
                    $app->halt(401, gettext('Not authorized to publish articles'));
                }

                // title shouldn't be exists
                $notExists = function($title) use ($app) {
                    return $app->db->getRepository("Articlei18n")
                                    ->findOneBy(array("title" => $title)) === null;
                };

                // test input data
                $validator
                        ->key('title', V::create()->callback($notExists)->setName('notExists'))
                        ->key('image', V::create()->notEmpty())
                        ->assert($input);

                // save dataURI to JPG
                $filename = uniqueFilename(UPLOAD_DIR . DS . slugify($input['title']) . '.jpg');
                if (!file_put_contents($filename, file_get_contents($input['image']))) {
                    $app->halt(500, gettext('Featured image cannot be saved'));
                };

                $article = new Article();
                $article->setDetail(new Articlei18n())
                        ->setFeaturedImage(basename($filename));

                $article->getDetail()
                        ->setTitle($input['title'])
                        ->setSlug(slugify($input['title']))
                        ->setContent($input['content'])
                        ->setAuthor($app->user)
                        ->setCreatedAt(new DateTime('now'))
                        ->setStatus($input['status']);

                foreach ($app->request->post('categories', array()) as $key) {
                    $category = $app->db->find('Category', $key);
                    if (null !== $category) {
                        $article->addCategory($category);
                    }
                }

                foreach ($app->request->post('regions', array()) as $key) {
                    $region = $app->db->find('Region', $key);
                    if (null !== $region) {
                        $article->addRegion($region);
                    }
                }

                foreach ($app->request->post('related', array()) as $articleId) {
                    $relatedArticle = $app->db->find('Article', $articleId);
                    if (null !== $relatedArticle) {
                        $article->addRelated($relatedArticle);
                    }
                }

                $app->db->persist($article);
                $app->db->flush();

                $app->flash(ALERT_SUCCESS, 'Article successfully created');
                $app->redirect($app->urlFor('admin.article.index'));
            } catch (AllOfException $ex) {
                $data['error'] = new Error($ex->findMessages($messages));
            }
        }

        $data['categories'] = $app->db->getRepository('Category')
                ->findBy(array('parent' => null));
        $data['regions'] = $app->db->getRepository('Region')
                ->findBy(array('parent' => 0));
        $data['related'] = $app->db->getRepository('Article')->findAll();

        $app->render('admin/article/create.twig', $data);
    })->via("GET", "POST")->name('admin.article.create');

    // EDIT
    $app->map("/:id/edit", function($id) use ($app, $validator, $messages) {
        $data['article'] = $article = $app->db->find("Article", $id);
        /* @var $article Article */

        if (null === $article) {
            $app->notFound();
        }

        if ($article->getDetail()->isArchive()) {
            $app->halt(403, gettext('It\'s not allowed to edit archived article'));
        }

        if (!$article->getDetail()->isPermitted($app->user)) {
            $app->halt(401, gettext('Not authorized to edit this article'));
        };

        /* @var $i18n Articlei18n */
        if ($app->request->isPost()) {
            try {
                $data['input'] = $input = $app->request->post();

                // contributor is prohibited to publish posts
                if ($input['status'] == STATUS_PUBLISH && $app->user->isContributor()) {
                    $app->halt(401, gettext('Not authorized to publish articles'));
                }

                // the article must not be exists
                $notExists = function($title) use ($app, $article) {
                    if ($article->getDetail()->getTitle() === $title) {
                        return true;
                    }
                    $_i18n = $app->db->getRepository("Articlei18n")
                            ->findOneBy(array("title" => $title));

                    if (null === $_i18n) {
                        return true;
                    }
                    return false;
                };

                $validator->key('title', V::create()->callback($notExists)->setName('notExists'))
                        ->assert($input);

                $article->getDetail()
                        ->setTitle($input['title'])
                        ->setSlug(slugify($input['title']))
                        ->setContent($input['content'])
                        ->setUpdatedAt(new DateTime('now'))
                        ->setUpdatedBy($app->user)
                        ->setStatus($input['status']);

                $categories = new Doctrine\Common\Collections\ArrayCollection();
                $regions = new \Doctrine\Common\Collections\ArrayCollection();
                $related = new \Doctrine\Common\Collections\ArrayCollection();
                foreach ($app->request->post('categories', array()) as $key) {
                    $category = $app->db->find('Category', $key);
                    if (null !== $category) {
                        $categories->add($category);
                    }
                }
                foreach ($app->request->post('regions', array()) as $key) {
                    $region = $app->db->find('Region', $key);
                    if (null !== $region) {
                        $regions->add($region);
                    }
                }
                foreach ($app->request->post('related', array()) as $key) {
                    $relatedArticle = $app->db->find('Article', $key);
                    if (null !== $relatedArticle) {
                        $related->add($relatedArticle);
                    }
                }
                $article->setCategories($categories)
                        ->setRegions($regions)
                        ->setRelated($related);

                // upload new image and overwrite old image
                if (!empty($input['image'])) {
                    $fp = fopen(UPLOAD_DIR . DS . $article->getFeaturedImage(), 'w+');
                    if ($fp !== FALSE) {
                        fwrite($fp, file_get_contents($input['image']));
                        fclose($fp);
                    }
                }

                $app->db->persist($article);
                $app->db->flush();
                $app->flash(ALERT_SUCCESS, gettext('Article successfully updated'));
                $app->redirect($app->urlFor("admin.article.index"));
            } catch (InvalidArgumentException $ex) {
                $data['error'] = new Error($ex->findMessages($messages));
            }
        }

        $data['categories'] = $app->db->getRepository('Category')->findBy(array(
            'parent' => null
        ));

        $data['regions'] = $app->db->getRepository('Region')->findBy(array(
            'parent' => 0
        ));

        $data['related'] = $app->db->getRepository('Article')->findAll();

        $app->render('admin/article/edit.twig', $data);
    })->via("GET", "POST")->name("admin.article.edit");

    // TRANSLATE
    $app->map('/:id/translate/:language', function($id, $language) use($app, $validator, $messages) {
        $data['article'] = $article = $app->db->find('Article', $id);
        /* @var $article Article */

        if (null === $article) {
            $app->notFound();
        }

        if (!$article->isPermitted($app->user)) {
            $app->halt(401, gettext('Not authorized to translate this article'));
        }

        $data['i18n'] = $i18n = $app->db->getRepository('Articlei18n')->findOneBy(array(
            'base' => $article->getDetail(),
            'language' => $language
        ));

        if (null === $i18n) {
            $data['i18n'] = $i18n = new Articlei18n();
            $i18n
                    ->setLanguage($language)
                    ->setBase($article->getDetail())
                    ->setAuthor($app->user)
                    ->setCreatedAt(new DateTime('now'));
        } else {
            $i18n->setUpdatedAt(new DateTime('now'))
                    ->setUpdatedBy($app->user);
        }

        if ($app->request->isPost()) {
            try {
                $data['input'] = $input = $app->request->post();

                if ($app->user->isContributor() && $input['status'] === STATUS_PUBLISH) {
                    $app->halt(401, gettext('Not authorized to publish translations'));
                }

                $notExists = function($title) use($app, $i18n) {
                    $exists = $app->db->getRepository("Articlei18n")
                            ->findOneBy(array('title' => $title));
                    /* @var $exists Articlei18n */
                    return null === $exists || $exists === $i18n->getBase();
                };

                // validation
                $validator
                        ->key("title", V::create()->callback($notExists)->setName("notExists"))
                        ->assert($input);

                $i18n
                        ->setTitle($input['title'])
                        ->setSlug(slugify($input['title']))
                        ->setContent($input['content'])
                        ->setStatus($input['status']);

                $app->db->persist($i18n);
                $app->db->flush();
                $app->flash('success', gettext('Translations updated successfully'));
                $app->redirect($app->urlFor("admin.article.index"));
            } catch (InvalidArgumentException $ex) {
                $data['error'] = new Error($ex->findMessages($messages));
            }
        }
        $app->render("admin/article/translate.twig", $data);
    })->via("GET", "POST")->name("admin.article.translate");

    // SET STATUS
    $app->put('/:id/set/:status', 'ajax', function($id, $status) use($app) {
        $article = $app->db->find('Article', $id);
        /* @var $article Article */
        if (null === $article) {
            $app->notFound();
        }

        if (// contributor is publishing an article
                ($status === STATUS_PUBLISH && $app->user->isContributor())
                // or the article is not belongs to user(author or contributor)
                || !$article->isPermitted($app->user)) {
            $app->halt(401);
        };

        $article->getDetail()
                ->setStatus($status)
                ->setUpdatedAt(new DateTime("now"))
                ->setUpdatedBy($app->user);

        $app->db->persist($article);
        $app->db->flush();
        return $app->response->write(true);
    })->conditions(array("status" => "(" . STATUS_PUBLISH . "|" . STATUS_DRAFT . "|" . STATUS_PENDING . "|" . STATUS_ARCHIVE . ")"))->name('admin.article.set');

    // DELETE
    $app->delete("/:id(/:language)", 'ajax', function($id, $language = null) use($app) {
        $article = $app->db->find('Article', $id);
        /* @var $article Article */

        if (null === $article) {
            $app->notFound();
        }

        if (!$article->getDetail()->isPermitted($app->user)) {
            $app->halt(401, gettext('Not authorized to delete this article'));
        }

        if (null === $language) {
            if (!$article->getDetail()->isArchive()) {
                $app->halt(403, gettext('It\'s not allowed to delete non-archived articles'));
            }
            $app->db->remove($article);
        } else {
            $i18n = $app->db->getRepository('Articlei18n')->findOneBy(array(
                'base' => $article->getDetail(),
                'language' => $language
            ));

            if (null === $i18n) {
                $app->notFound();
            }

            $app->db->remove($i18n);
        }
        $app->db->flush();
        $app->contentType('application/json');
        return $app->response->write(true);
    })->name("admin.article.delete");

    // DATATABLE
    $app->get('/datatables(/:status)', function($status = null) use($app) {
        $qb = $app->db
                ->getRepository("Article")
                ->createQueryBuilder("a")
                ->join('a.detail', 'b');

//		if ($app->request->get('type') == 1 || $app->user->isContributor() || $app->user->isAuthor()) {
//			$qb->where("b.author = :author")
//					->setParameter("author", $app->user);
//		}
//
        if (null !== $status) {
            $qb->andWhere("b.status = :status")
                    ->setParameter("status", $status);
        }
//
//		foreach ($app->request->get('order') as $order) {
//			switch ((int) $order['column']) {
//				case 3:
//					$qb->addOrderBy("b.createdAt", $order['dir'])
//							->addOrderBy("a.createdAt", $order['dir']);
//					break;
//				case 4:
//					$qb->addOrderBy("b.updatedAt", $order['dir'])
//							->addOrderBy("a.updatedAt", $order['dir']);
//					break;
//			}
//		}
//
        $app->contentType("application/json");
        $data = new DataTables($qb, 'admin/article/datatables.twig');
        return $app->response->write(json_encode($data));
    })->name('admin.article.datatables');

    // COUNT SUMMARY
    $app->get('/count', 'ajax', function() use ($app) {
        $qb = $app->db->getRepository("Article")
                ->createQueryBuilder("a")
                ->join('a.detail', 'b')
                ->select("COUNT(a)")
                ->where("b.status = :status");

        if ($app->request->get('type') == 1 || $app->user->isContributor() || $app->user->isAuthor()) {
            $qb->andWhere("b.author = :author")
                    ->setParameter("author", $app->user);
        }

        $data['publish'] = $qb->setParameter("status", STATUS_PUBLISH)
                        ->getQuery()->getSingleScalarResult();
        $data['draft'] = $qb->setParameter("status", STATUS_DRAFT)
                        ->getQuery()->getSingleScalarResult();
        $data['pending'] = $qb->setParameter("status", STATUS_PENDING)
                        ->getQuery()->getSingleScalarResult();
        $data['archive'] = $qb->setParameter("status", STATUS_ARCHIVE)
                        ->getQuery()->getSingleScalarResult();

        $app->contentType("application/json");
        return $app->response->body(json_encode($data));
    })->name('admin.article.count');

    include 'article/image.php';
});
