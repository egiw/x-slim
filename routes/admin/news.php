<?php

use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\AllOfException;

/* @var $app Application */
$app->group('/news', function() use($app) {
	$app->get('/', function() use($app) {
		$app->render('admin/news/index.twig');
	})->setName('admin.news.index');

	$app->map('/create', function() use($app) {
		if ($app->request->isPost()) {
			try {
				$data['input'] = $input = $app->request->post();

				V::create()->key('title', V::create()->notEmpty())
								->key('content', V::create()->notEmpty()->length(160))
								->assert($input);

				$news = new News();
				$news->setDetail(new Newsi18n());

				$news->getDetail()
								->setTitle($input['title'])
								->setContent($input['content'])
								->setStatus($input['status'])
								->setCreatedAt(new DateTime('now'))
								->setCreatedBy($app->user);

				foreach ($app->request->post('relatedArticles', array()) as $articleId) {
					$article = $app->db->find('Article', $articleId);
					if (null !== $article) {
						$news->addRelatedArticle($article);
					}
				}

				$app->db->persist($news);
				$app->db->flush();

				$app->flash(ALERT_SUCCESS, gettext('News created successfully'));
				$app->redirect($app->urlFor('admin.news.index'));
			} catch (AllOfException $ex) {
				$data['error'] = new Error($ex->findMessages(array(
										'title.notEmpty' => gettext('Title cannot be empty'),
										'content.notEmpty' => gettext('Content cannot be empty'),
										'content.length' => gettext('Content must at least 160 characters')
				)));
			}
		}

		$data['articles'] = $app->db->getRepository('Article')->findBy(array(
				'status' => STATUS_PUBLISH
		));

		$app->render('admin/news/create.twig', $data);
	})->via('GET', 'POST')->setName('admin.news.create');

	$app->map('/:id/edit', function($id) use($app) {
		$data['news'] = $news = $app->db->find('News', $id);
		/* @var $news News */
		if (null === $news)
			$app->notFound();

		if ($app->request->isPost()) {
			try {
				$data['input'] = $input = $app->request->post();

				V::create()
								->key('title', V::create()->notEmpty())
								->key('content', V::create()->notEmpty()->length(160))
								->assert($input);

				$news->getDetail()
								->setStatus($input['status'])
								->setTitle($input['title'])
								->setContent($input['content']);

				$relatedArticles = new \Doctrine\Common\Collections\ArrayCollection();
				foreach ($app->request->post('relatedArticles', array()) as $articleId) {
					$article = $app->db->find('Article', $articleId);
					if (null !== $article) {
						$relatedArticles->add($article);
					}
				}

				$news->setRelatedArticles($relatedArticles);

				$app->db->persist($news);
				$app->db->flush();

				$app->flash(ALERT_SUCCESS, gettext('News updated successfully'));
				$app->redirect($app->urlFor('admin.news.index'));
			} catch (AllOfException $ex) {
				$data['error'] = new Error($ex->findMessages(array(
										'title.notEmpty' => gettext('Title cannot be empty'),
										'content.notEmpty' => gettext('Content cannot be empty'),
										'content.length' => gettext('Content must at least 160 character')
				)));
			}
		}

		$data['articles'] = $app->db->getRepository('Article')->findBy(array(
				'status' => STATUS_PUBLISH
		));

		$app->render('admin/news/edit.twig', $data);
	})->via('GET', 'POST')->setName('admin.news.edit');

	$app->map('/:id/translate/:language', function($id, $language) use($app) {
		$news = $app->db->find('News', $id);
		/* @var $news News */
		if(null === $news)
			$app->notFound();

		$data['i18n'] = $i18n = $app->db->getRepository('Newsi18n')->findBy(array(
				'base' => $news->getDetail(),
				'language' => $language
		));

		if(null === $i18n) {
			$data['i18n'] = $i18n = new Newsi18n();
			$i18n->setBase($news->getDetail())
							->setCreatedAt(new DateTime('now'))
							->setCreatedBy($app->user);
		} else {
			$i18n->setUpdatedAt(new DateTime('now'))
							->setUpdatedBy($app->user);
		}

	})->setName('admin.news.translate', $data);

	$app->get('/datatable', function() use($app) {
		$qb = $app->db
						->getRepository('News')
						->createQueryBuilder('a');

		$app->contentType('application/json');
		$data = new DataTables($qb, 'admin/news/datatables.twig');
		return $app->response->write(json_encode($data));
	})->setName('admin.news.datatables');
});
