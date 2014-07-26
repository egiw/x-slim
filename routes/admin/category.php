<?php

use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\AllOfException;

/* @var $app Application */

$app->group('/category', function() use($app) {
    // Index
    $app->get('/', function() use($app) {
        $categories = $app->db->getRepository('Category')->findBy(array(
            'parent' => null
        ));
        $app->render('admin/category/index.twig', array(
            'categories' => $categories
        ));
    })->setName('admin.category.index');
    // Create
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
    // Edit
    $app->map('/:id/edit', function($id) use($app) {
        if ($category = $app->db->find('Category', $id)) {
            /* @var $category Category */
            $data = array('category' => $category);

            // process update
            if ($app->request->isPost()) {
                $data['input'] = $input = $app->request->post();
                $repo = $app->db->getRepository('Categoryi18n');
                $messages = array();
                try {
                    $validator = V::create();
                    $_input = array();
                    foreach ($input['translations'] as $lang => $detail) {
                        $i18n = $repo->findOneBy(array('language' => $lang, 'parent' => $category));
                        /* @var $i18n Categoryi18n */
                        $name = $lang . '_name';
                        $_input[$name] = $detail['name'];
                        $messages[$name . '.notEmpty'] = gettext('Category name cannot be empty');
                        $messages[$name . '.callback'] = gettext('Category name already exists');

                        $validator->key($name, V::create()->notEmpty());
                        $validator->key($name, V::create()
                                        ->callback(function($name) use ($repo, $i18n) {
                                            return $name === $i18n->getName() || $repo->findOneBy(array('name' => $name)) === null;
                                        })
                        );
                    }
                    $validator->assert($_input);

                    foreach ($input['translations'] as $lang => $detail) {
                        $i18n = $repo->findOneBy(array('language' => $lang, 'parent' => $category));
                        /* @var $i18n Categoryi18n */
                        if (null !== $i18n) {
                            $i18n
                                    ->setName($detail['name'])
                                    ->setDescription($detail['description'])
                                    ->setSlug(slugify($detail['name']));
                            $app->db->persist($i18n);
                        }
                    }

                    if (!empty($input['parent'])) {
                        if ($parent = $app->db->find('Category', $input['parent'])) {
                            $category->setParent($parent);
                        }
                    }

                    $app->db->persist($category);
                    $app->db->flush();
                    $app->flash(ALERT_SUCCESS, gettext('Category updated successfully'));
                    $app->redirect($app->urlFor('admin.category.index'));
                } catch (AllOfException $ex) {
                    $data['error'] = new Error($ex->findMessages($messages));
                }
            }

            if ($category->getParent() != null || $category->getSubcategories()->count() == 0) {
                $data['categories'] = $app->db->getRepository('Category')->findBy(array(
                    'parent' => null
                ));
            }

            $app->render('admin/category/edit.twig', $data);
        } else {
            $app->notFound();
        };
    })->via('GET', 'POST')->setName('admin.category.edit');
    // Delete
    $app->delete('/:id', function($id) use($app) {
        if ($category = $app->db->find('Category', $id)) {
            $app->db->remove($category);
            $app->db->flush();
            $app->flash(ALERT_SUCCESS, gettext('Category deleted successfully'));
            return $app->response->write(json_encode(true), true);
        }
    })->name('admin.category.delete');
});
