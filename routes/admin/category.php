<?php

use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\AllOfException;

/* @var $app Application */

$app->group('/category', function() use($app) {
    $app->get('/', function() use($app) {
        $categories = $app->db->getRepository('Category')->findBy(array(
            'parent' => null
        ));
        $app->render('admin/category/index.twig', array(
            'categories' => $categories
        ));
    })->setName('admin.category.index');

    $app->map('/create(/:pid)', function($pid = null) use($app) {
        $data = array('pid' => $pid);
        if ($app->request->isPost()) {
            $data['input'] = $input = $app->request->post();
            try {
                $validator = V::create();
                $category = new Category();

                if (!empty($input['parent'])) {
                    $parent = $app->db->find('Category', $input['parent']);
                    if (null !== $parent) {
                        $category->setParent($parent);
                    }
                }

                foreach ($input['translations'] as $lang => $detail) {
                    $validator
                            ->key('name', V::create()->notEmpty())
                            ->key('name', V::create()->callback(function($name) use($app) {
                                        $rep = $app->db->getRepository('Categoryi18n');
                                        return $rep->findOneBy(array('name' => $name)) === null;
                                    }))
                            ->assert($detail);

                    $categoryi18n = new Categoryi18n();
                    $categoryi18n
                            ->setLanguage($lang)
                            ->setName($detail['name'])
                            ->setDescription($detail['description'])
                            ->setSlug(slugify($detail['name']))
                            ->setParent($category);

                    $category->addTranslation($categoryi18n);
                }
                $app->db->persist($category);
                $app->db->flush();
                $app->flash(ALERT_SUCCESS, gettext('New category created successfully.'));
                $app->redirect($app->urlFor('admin.category.index'));
            } catch (AllOfException $ex) {
                $data['error'] = new Error($ex->findMessages(array(
                            'name.notEmpty' => gettext('Category name cannot be emtpy'),
                            'name.callback' => gettext('Category name already exists')
                )));
            }
        }

        $data['categories'] = $app->db->getRepository('Category')->findBy(array(
            'parent' => null
        ));

        $app->render('admin/category/create.twig', $data);
    })->via('GET', 'POST')->setName('admin.category.create');
});
