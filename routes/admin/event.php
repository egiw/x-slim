<?php

use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\AllOfException;

/* @var $app Application */
$app->group('/event', function() use ($app) {
    $app->get('/', function() use($app) {

        $app->render('admin/event/index.twig');
    })->setName('admin.event.index');

    $app->map('/create', function() use($app) {
        $data = array();

        if ($app->request->isPost()) {
            try {
                $data['input'] = $input = $app->request->post();
                V::create()
                        ->key('title', V::create()->notEmpty())
                        ->key('start', V::create()->notEmpty())
                        ->key('end', V::create()->notEmpty())
                        ->assert($input);

                $event = new Event();
                $event->setStart(DateTime::createFromFormat('d/m/Y', $input['start']))
                        ->setEnd(DateTime::createFromFormat('d/m/Y', $input['end']))
                        ->setDetail(new Eventi18n());

                $event->getDetail()
                        ->setCreatedAt(new DateTime('now'))
                        ->setCreatedBy($app->user)
                        ->setDescription($input['description'])
                        ->setTitle($input['title'])
                        ->setEvent($event);

                foreach ($app->request->post('relatedArticles', array()) as $articleId) {
                    if ($article = $app->db->find('Article', $articleId)) {
                        $event->addRelatedArticle($article);
                    }
                }

                $app->db->persist($event);
                $app->db->flush();
                $app->flash(ALERT_SUCCESS, gettext('Event created successfully'));
                $app->redirect($app->urlFor('admin.event.index'));
            } catch (AllOfException $ex) {
                $data['error'] = new Error($ex->findMessages(array(
                            'title.notEmpty' => gettext('Title cannot be empty'),
                            'start.notEmpty' => gettext('Event date cannot be empty'),
                            'end.notEmpty' => gettext('Event date cannot be empty')
                )));
            }
        }

        $data['articles'] = $app->db->getRepository('Article')->findAll();
        $app->render('admin/event/create.twig', $data);
    })->via('GET', 'POST')->setName('admin.event.create');

    $app->map('/:id/edit', function($id) use ($app) {
        $data['event'] = $event = $app->db->find('Event', $id);
        /* @var $event Event */

        if (null === $event)
            $app->notFound();

        if ($app->request->isPost()) {
            try {
                $data['input'] = $input = $app->request->post();

                V::create()
                        ->key('title', V::create()->notEmpty())
                        ->key('start', V::create()->notEmpty())
                        ->key('end', V::create()->notEmpty())
                        ->assert($input);

                $relatedArticles = new \Doctrine\Common\Collections\ArrayCollection();
                foreach ($app->request->post('relatedArticles', array()) as $articleId) {
                    $article = $app->db->find('Article', $articleId);
                    if (null !== $article)
                        $relatedArticles->add($article);
                }

                $event->setStart(DateTime::createFromFormat('d/m/Y', $input['start']))
                        ->setEnd(DateTime::createFromFormat('d/m/Y', $input['end']))
                        ->setRelatedArticles($relatedArticles);

                $event->getDetail()
                        ->setTitle($input['title'])
                        ->setDescription($input['description'])
                        ->setUpdatedAt(new DateTime('now'))
                        ->setUpdatedBy($app->user);

                $app->db->persist($event);
                $app->db->flush();

                $app->flash(ALERT_SUCCESS, gettext('Event updated successfully'));
                $app->redirect($app->urlFor('admin.event.index'));
            } catch (AllOfException $ex) {
                $data['error'] = new Error($ex->findMessages(array(
                            'title.notEmpty' => gettext('Title cannot be empty'),
                            'start.notEmpty' => gettext('Event date cannot be empty'),
                            'end.notEmpty' => gettext('Event date cannot be empty')
                )));
            }
        }

        $data['articles'] = $app->db->getRepository('Article')->findAll();
        $app->render('admin/event/edit.twig', $data);
    })->via('GET', 'POST')->setName('admin.event.edit');

    $app->map('/:id/translate/:language', function($id, $language) use($app) {
        if (!in_array($language, array_keys($app->translations))) {
            $app->notFound();
        }

        $data['event'] = $event = $app->db->find('Event', $id);
        $data['language'] = $language;

        $data['i18n'] = $i18n = $app->db->getRepository('Eventi18n')->findOneBy(array(
            'base' => $event->getDetail(),
            'language' => $language
        ));

        if (null === $i18n) {
            $data['i18n'] = $i18n = new Eventi18n();
            $i18n->setBase($event->getDetail())
                    ->setLanguage($language);

            $message = gettext('Translation created successfully');
        } else {
            $i18n->setUpdatedAt(new DateTime('now'))
                    ->setUpdatedBy($app->user);

            $message = gettext('Translation updated successfully');
        }

        /* @var $event Event */
        if (null === $event) {
            $app->notFound();
        }

        if ($app->request->isPost()) {
            try {
                $data['input'] = $input = $app->request->post();
                V::create()
                        ->key('title', V::create()->notEmpty())
                        ->key('description', V::create()->length(160))
                        ->assert($input);

                $i18n->setTitle($input['title'])
                        ->setDescription($input['description'])
                        ->setCreatedAt(new DateTime('now'))
                        ->setCreatedBy($app->user);

                $event->getDetail()->addI18n($i18n);

                $app->db->persist($event);
                $app->db->flush();

                $app->flash(ALERT_SUCCESS, $message);
                $app->redirect($app->urlFor('admin.event.index'));
            } catch (AllOfException $ex) {
                $data['error'] = new Error($ex->findMessages(array(
                            'title.notEmpty' => gettext('Title cannot be empty'),
                            'description.length' => gettext('Description must at least 160 characters.')
                )));
            }
        }

        $app->render('admin/event/translate.twig', $data);
    })->via('GET', 'POST')->setName('admin.event.translate');

    $app->delete('/:id(/:language)', function($id, $language = null) use($app) {
        $event = $app->db->find('Event', $id);
        if (null === $event) {
            $app->notFound();
        }

        $app->contentType('application/json');
        // delete entire
        if (null === $language) {
            $app->db->remove($event);
        } else {
            $i18n = $app->db->getRepository('Eventi18n')->findOneBy(array(
                'base' => $event->getDetail(),
                'language' => $language
            ));

            if (null === $i18n) {
                $app->notFound();
            }

            $app->db->remove($i18n);
        }

        $app->db->flush();
        return $app->response->write(true);
    })->setName('admin.event.delete');

    $app->get('/datatable', function() use($app) {
        $qb = $app->db->getRepository('Event')
                ->createQueryBuilder('a');

        $app->contentType('application/json');
        $data = new DataTables($qb, 'admin/event/datatables.twig');
        return $app->response->write(json_encode($data));
    })->setName('admin.event.datatables');
});
