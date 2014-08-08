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
            $image = $_FILES['image'];
            $messages = array();
            $validator = V::create();
            $_input = array();
            $category = new Category();
            try {
                if (!empty($input['parent'])) {
                    if ($parent = $app->db->find('Category', $input['parent'])) {
                        $category->setParent($parent);
                    }
                }
                $_input['type'] = $image['type'];
                $_input['size'] = $image['size'];
                $validator
                        ->key('type', V::create()->in(array('image/jpg', 'image/jpeg')))
                        ->key('size', V::create()->min(1));
                $messages['type.in'] = gettext('File extension must be JPG');
                $messages['size.min'] = gettext('Image cannot be empty');
                foreach ($input['translations'] as $lang => $detail) {
                    $name = $lang . '_name';
                    $_input[$name] = $detail['name'];

                    $validator
                            ->key($name, V::create()->notEmpty())
                            ->key($name, V::create()->callback(function($name) use($app) {
                                        $rep = $app->db->getRepository('Categoryi18n');
                                        return $rep->findOneBy(array('name' => $name)) === null;
                                    }));

                    $messages[$name . '.notEmpty'] = gettext('Category name cannot be empty');
                    $messages[$name . '.callback'] = gettext('Category name already exists');

                    if ($validator->validate($_input)) {
                        $categoryi18n = new Categoryi18n();
                        $categoryi18n
                                ->setLanguage($lang)
                                ->setName($detail['name'])
                                ->setDescription($detail['description'])
                                ->setSlug(slugify($detail['name']))
                                ->setParent($category);
                        $category->addTranslation($categoryi18n);
                    }
                }
                $validator->assert($_input);
                $filename = $category->getTranslations()->first()->getSlug() . '.jpg';
                $destination = ROOT . '/images/category/' . $filename;
                if (move_uploaded_file($image['tmp_name'], $destination)) {
                    $category->setImage($filename);
                    $app->db->persist($category);
                    $app->db->flush();
                    $app->flash(ALERT_SUCCESS, gettext('New category created successfully.'));
                    $app->redirect($app->urlFor('admin.category.index'));
                };
            } catch (AllOfException $ex) {
                $data['error'] = new Error($ex->findMessages($messages));
            }
        }

        $data['categories'] = $app->db->getRepository('Category')->findBy(array(
            'parent' => null
        ));

        $app->render('admin/category/create.twig', $data);
    })->via('GET', 'POST')->setName('admin.category.create');
    $app->map('/:id/edit', function($id) use($app) {
        if ($category = $app->db->find('Category', $id)) {
            /* @var $category Category */
            $data = array('category' => $category);
            // process update
            if ($app->request->isPost()) {
                $repo = $app->db->getRepository('Categoryi18n');
                $data['input'] = $input = $app->request->post();
                $upload = false;
                $validator = V::create();
                $_input = array();
                $messages = array();


                if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
                    $upload = true;
                    $_input['type'] = $_FILES['image']['type'];
                    $validator->key('type', V::create()->in(array('image/jpg', 'image/jpeg')));
                    $messages['type.in'] = gettext('File extension must be JPG');
                }

                try {
                    foreach ($input['translations'] as $lang => $detail) {
                        $i18n = $repo->findOneBy(array('language' => $lang, 'parent' => $category));
                        /* @var $i18n Categoryi18n */
                        if (null !== $i18n) {
                            $name = $lang . '_name';
                            $_input[$name] = $detail['name'];
                            $messages[$name . '.notEmpty'] = gettext('Category name cannot be empty');
                            $messages[$name . '.callback'] = gettext('Category name already exists');

                            $validator->key($name, V::create()->notEmpty());
                            $validator->key($name, V::create()
                                            ->callback(function($name) use ($repo, $i18n) {
                                                if ($name === $i18n->getName()) {
                                                    return true;
                                                } else {
                                                    $exists = $repo->findOneBy(array('name' => $name));
                                                    if (null === $exists) {
                                                        return true;
                                                    } elseif ($exists->getParent() === $i18n->getParent()) {
                                                        return true;
                                                    }
                                                }
                                                return false;
                                            })
                            );

                            if ($validator->validate($_input)) {
                                $i18n->setName($detail['name'])
                                        ->setDescription($detail['description'])
                                        ->setSlug(slugify($detail['name']));
                                $app->db->persist($i18n);
                            }
                        }
                    }

                    $validator->assert($_input);

                    if (!empty($input['parent'])) {
                        if ($parent = $app->db->find('Category', $input['parent'])) {
                            $category->setParent($parent);
                        }
                    }

                    if ($upload) {
                        $old = ROOT . '/images/category/' . $category->getImage();
                        if (file_exists($old))
                            unlink($old);
                        $name = $category->getTranslations()->first()->getSlug() . '.jpg';
                        $destination = ROOT . '/images/category/' . $name;
                        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                            $category->setImage($name);
                        };
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
    $app->delete('/:id', function($id) use($app) {
        if ($category = $app->db->find('Category', $id)) {
            $app->db->remove($category);
            $app->db->flush();
            $filename = ROOT . '/images/category/' . $category->getImage();
            if (file_exists($filename))
                unlink($filename);
            $app->flash(ALERT_SUCCESS, gettext('Category deleted successfully'));
            return $app->response->write(json_encode(true), true);
        }
    })->name('admin.category.delete');
});
