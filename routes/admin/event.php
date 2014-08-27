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
                        ->setEnd(DateTime::createFromFormat('d/m/Y', $input['end']));

                foreach ($input['relatedArticles'] as $articleId) {
                    if ($article = $app->db->find('Article', $articleId)) {
                        $event->getRelatedArticles()->add($article);
                    }
                }

                $i18n = new Eventi18n();
                $i18n->setCreatedAt(new DateTime('now'))
                        ->setCreatedBy($app->user)
                        ->setDescription($input['description'])
                        ->setLanguage($input['language'])
                        ->setTitle($input['title'])
                        ->setEvent($event);

                $app->db->persist($event);
                $app->db->persist($i18n);
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

    $app->get('/datatable', function() use($app) {
        $qb = $app->db->getRepository('Event')
                ->createQueryBuilder('a')
                ->select(array('a.id', 'b.title', 'COUNT(c) as views', 'a.start', 'a.end'))
                ->join('a.i18n', 'b', 'WITH', 'b.language = :language')
                ->leftJoin('b.stats', 'c')
                ->groupBy('b');

        $qb->setParameters(array(
            'language' => 'id'
        ));

        $app->contentType('application/json');
        $data = new DataTables($qb, 'admin/event/datatables.twig');
        return $app->response->write(json_encode($data));
    })->setName('admin.event.datatables');
});
