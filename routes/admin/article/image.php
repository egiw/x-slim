<?php

/* @var $app Application */

use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\AllOfException;

$app->group('/:articlei18n/image', function(\Slim\Route $route) use($app) {
    $articlei18n = $app->db->find('Articlei18n', $route->getParam('articlei18n'));
    if (null === $articlei18n) {
        $app->notFound();
    }
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
        $data = array();
        if ($app->request->isPost()) {
            try {
                // check if user upload wrong file
                V::create()->contains('image')->setName('image')
                        ->assert($_FILES);
                // check whether file type is wrong
                V::create()->in(array('image/jpeg'))->setName('image')
                        ->assert($_FILES['type']);

                $file = $_FILES['image'];
                $data['input'] = $input = $app->request->post();
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = pathinfo($file['name'], PATHINFO_FILENAME);

                if (!empty($input['alt'])) {
                    $filename = slugify($input['alt']);
                }

                $destination = ROOT . DS . 'images' . DS . 'article';
                $i = 0;
                $name = $filename . '.' . $extension;
                while (file_exists($destination . DS . $name)) {
                    $name = $filename . '-' . $i . '.' . $extension;
                    $i++;
                }

                if (move_uploaded_file($file['tmp_name'], $destination . DS . $name)) {
                    $image = new Image();
                    $image->setArticle($articlei18n->getArticle())
                            ->setFilename($name);

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
                            'image' => gettext('Please upload an valid image'))
                ));
            }
        }

        $data['articlei18n'] = $articlei18n;
        $app->render('admin/article/image/create.twig', $data);
    })->via('GET', 'POST')->setName('admin.article.image.create');
    // DELETE
    $app->delete('/:id', 'ajax', function(Articlei18n $articlei18n, $id) use($app) {
        $result = false;
        $app->contentType('application/json');
        if ($image = $app->db->find('Image', $id)) {
            /* @var $image Image */
            $filename = ROOT . DS . 'images' . DS . 'article' . DS . $image->getFilename();
            if (file_exists($filename)) {
                @unlink($filename);
            }

            $app->db->remove($image);
            $app->db->flush();
            $result = true;
        }
        return $app->response->write($result, true);
    })->setName('admin.article.image.delete');
    // EDIT
    $app->map('/:id/edit', function(Articlei18n $articlei18n, $id) use($app) {
        $image = $app->db->find('Image', $id);
        $imagei18n = $app->db->getRepository('Imagei18n')->findOneBy(array(
            'image' => $image,
            'articlei18n' => $articlei18n
        ));

        if (null == $imagei18n) {
            $imagei18n = new Imagei18n();
            $imagei18n
                    ->setImage($image)
                    ->setArticlei18n($articlei18n);
        }

        if ($app->request->isPost()) {
            $file = $_FILES['image'];
            $title = $app->request->post('alt');
            $caption = $app->request->post('caption');
            $destination = ROOT . DS . 'images' . DS . 'article';

            // change existing image
            if ($file['size'] > 0) {
                // upload new image
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = empty($title) ? pathinfo($file['name'], PATHINFO_FILENAME) : slugify($title);

                // handle duplicate filename
                $i = 0;
                $name = $filename . '.' . $extension;
                while (file_exists($destination . DS . $name)) {
                    $name = $filename . '-' . $i . '.' . $extension;
                    $i++;
                }

                // move the uploaded file
                if (move_uploaded_file($file['tmp_name'], $destination . DS . $name)) {
                    // remove old image
                    $oldImage = $destination . DS . $imagei18n->getImage()->getFilename();
                    $imagei18n->getImage()->setFilename($name);
                    if (file_exists($oldImage))
                        @unlink($oldImage);
                }
            } elseif (!empty($title) && $imagei18n->getTitle() !== $title && $app->request->post('rename')) {
                // update filename
                $image = $imagei18n->getImage();
                $extension = pathinfo($image->getFilename(), PATHINFO_EXTENSION);
                $filename = slugify($title);

                // handle duplicate filename
                $i = 0;
                $name = $filename . '.' . $extension;
                while (file_exists($destination . DS . $name)) {
                    $name = $filename . '-' . $i . '.' . $extension;
                    $i++;
                }

                // rename the file
                if (rename($destination . DS . $image->getFilename(), $destination . DS . $name))
                    $image->setFilename($name);
            }

            $imagei18n->setTitle($title)
                    ->setCaption($caption);

            $app->db->persist($imagei18n);
            $app->db->flush();
            $app->flash(ALERT_SUCCESS, gettext('Image successfully updated'));
            $app->redirect($app->urlFor('admin.article.image.index', array(
                        'articlei18n' => $articlei18n->getId()
            )));
        }

        $app->render('admin/article/image/edit.twig', array(
            'articlei18n' => $articlei18n,
            'imagei18n' => $imagei18n,
            'image' => $image
        ));
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
