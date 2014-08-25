<?php

/* @var $app Application */

use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\AllOfException;

$app->group('/:articlei18n/image', function(\Slim\Route $route) use($app) {
    $articlei18n = $app->db->find('Articlei18n', $route->getParam('articlei18n'))
            or $app->notFound();

    $route->setParam('articlei18n', $articlei18n);
}, function() use($app) {

    // INDEX
    $app->get('/', function(Articlei18n $articlei18n) use($app) {
        $app->render('admin/article/image/index.twig', array(
            'articlei18n' => $articlei18n
        ));
    })->setName('admin.article.image.index');

    // CREATE
    $app->map('/create', function(Articlei18n $articlei18n) use($app) {
        $data = array('articlei18n' => $articlei18n);
        if ($app->request->isPost()) {
            try {
                // check if user upload wrong file
                V::create()->contains('image')->setName('image')
                        ->assert(array_keys($_FILES));
                // check whether file type is wrong or empty
                V::create()->in(array('image/jpeg'))
                        ->notEmpty()->setName('image')
                        ->assert($_FILES['image']['type']);

                $file = $_FILES['image'];
                $data['input'] = $input = $app->request->post();

                // use original filename or the slug of title
                $filename = empty($input['alt']) ?
                        $file['name'] :
                        slugify($input['alt']) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);

                $destination = uniqueFilename(ROOT . DS . 'images' . DS . 'article' . DS . $filename);
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    $image = new Image();
                    $image->setArticle($articlei18n->getArticle())
                            ->setFilename(pathinfo($destination, PATHINFO_BASENAME));

                    $imagei18n = new Imagei18n();
                    $imagei18n->setTitle($input['alt'])
                            ->setLanguage($articlei18n->getLanguage())
                            ->setImage($image)
                            ->setArticlei18n($articlei18n)
                            ->setCaption($input['caption']);

                    $image->addi18n($imagei18n);

                    $app->db->persist($image);
                    $app->db->flush();

                    $app->flash(ALERT_SUCCESS, gettext('Image added successfuly'));
                    $app->redirect($app->urlFor('admin.article.image.index', array(
                                'articlei18n' => $articlei18n->getId()
                    )));
                }
            } catch (AllOfException $ex) {
                $data['error'] = new Error($ex->findMessages(array(
                            'image' => gettext('Please upload a valid image'))
                ));
            }
        }

        $app->render('admin/article/image/create.twig', $data);
    })->via('GET', 'POST')->setName('admin.article.image.create');

    // DELETE
    $app->delete('/:id', 'ajax', function(Articlei18n $articlei18n, $id) use($app) {
        $image = $app->db->find('Image', $id) or $app->notFound();
        /* @var $image Image */

        $app->db->remove($image);
        $app->db->flush();

        // delete current associated image if any
        @unlink(ROOT . DS . 'images' . DS . 'article' . DS . $image->getFilename());

        $app->contentType('application/json');
        return $app->response->write(true);
    })->setName('admin.article.image.delete');

    // EDIT
    $app->map('/:id/edit', function(Articlei18n $articlei18n, $id) use($app) {
        $image = $app->db->find('Image', $id) or $app->notFound();
        /* @var $image Image */

        $imagei18n = $app->db->getRepository('Imagei18n')->findOneBy(array(
            'image' => $image,
            'articlei18n' => $articlei18n
        ));

        if (null === $imagei18n) {
            $imagei18n = new Imagei18n();
            $imagei18n->setImage($image)
                    ->setArticlei18n($articlei18n);
        }

        $data = array(
            'articlei18n' => $articlei18n,
            'imagei18n' => $imagei18n,
            'image' => $image
        );

        if ($app->request->isPost()) {
            try {
                // check if user uploaded a wrong file
                V::create()->contains('image')->setName('image')
                        ->assert(array_keys($_FILES));

                $file = $_FILES['image'];
                $data['input'] = $input = $app->request->post();
                $dir = ROOT . DS . 'images' . DS . 'article';

                // change existing image
                if ($file['size'] > 0) {
                    // check whether file type is wrong
                    V::create()->in(array('image/jpeg'))->setName('image')
                            ->assert($_FILES['image']['type']);

                    // upload new image
                    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                    $filename = empty($input['alt']) ?
                            $file['name'] :
                            slugify($input['alt']) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);

                    $destination = uniqueFilename($dir . DS . $filename);
                    // move the uploaded file
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        // remove old image
                        @unlink($dir . DS . $imagei18n->getImage()->getFilename());
                        $imagei18n->getImage()->setFilename(pathinfo($destination, PATHINFO_BASENAME));
                    }
                } elseif (!empty($input['alt']) && $app->request->post('rename')) {
                    // update filename
                    $image = $imagei18n->getImage();
                    $newname = uniqueFilename($dir . DS . slugify($input['alt']) .
                            '.' . pathinfo($image->getFilename(), PATHINFO_EXTENSION));

                    // rename the file
                    if (rename($dir . DS . $image->getFilename(), $newname)) {
                        $image->setFilename(pathinfo($newname, PATHINFO_BASENAME));
                    }
                }

                $imagei18n->setTitle($input['alt'])
                        ->setCaption($input['caption']);

                $app->db->persist($imagei18n);
                $app->db->flush();
                $app->flash(ALERT_SUCCESS, gettext('Image successfully updated'));
                $app->redirect($app->urlFor('admin.article.image.index', array(
                            'articlei18n' => $articlei18n->getId()
                )));
            } catch (AllOfException $ex) {
                $data['error'] = $ex->findMessages(array('image' => gettext('Please upload a valid image')));
            }
        }

        $app->render('admin/article/image/edit.twig', $data);
    })->via('GET', 'POST')->setName('admin.article.image.edit');

    // DATATABLE
    $app->get('/datatable', 'ajax', function(Articlei18n $articlei18n) use ($app) {
        $app->contentType("application/json");
        $qb = $app->db->getRepository('Image')
                ->createQueryBuilder('i')
                ->select(array('i.id', 'i.filename', 'j.title', 'j.caption'))
                ->leftJoin('i.i18n', 'j', 'WITH', 'j.articlei18n = :articlei18n')
                ->where('i.article = :article')
                ->setParameters(array(
            'article' => $articlei18n->getArticle(),
            'articlei18n' => $articlei18n
        ));

        $view = 'admin/article/image/datatables.twig';
        $searchFields = array('i.filename', 'i.title');
        $viewData = array('articlei18n' => $articlei18n);
        $data = new DataTables($qb, $view, $searchFields, $viewData);

        return $app->response->write(json_encode($data), true);
    })->setName('admin.article.image.datatable');
});
