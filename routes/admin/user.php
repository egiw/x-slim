<?php

use Respect\Validation\Validator as V;
use Respect\Validation\Exceptions\AllOfException;

/* @var $app Application */
/* @var $em Doctrine\ORM\EntityManager */

/**
 * User management
 * Admin only
 */
$app->group('/user', function() use($app) {
    if (!$app->user->isAdmin())
        $app->notFound();
}, function() use($app) {

    $messages = array(
        'username.notEmpty' => gettext('Username cannot be empty'),
        'username.length' => gettext('Username must be between 4 and 16 characters'),
        'username.callback' => gettext('Username already exists'),
        'email.notEmpty' => gettext('Email address cannot be empty'),
        'email.email' => gettext('Invalid email address'),
        'password.notEmpty' => gettext('Password cannot be empty'),
        'password.length' => gettext('Password must be at least 6 characters'),
        'passwordConfirmation.notEmpty' => gettext('Please retype password'),
        'passwordConfirmation.equals' => gettext('Password confirmation doesn\'t match'),
        'role.notEmpty' => gettext('Role must be selected')
    );

    /**
     * Display list of users
     */
    $app->get('/', function() use($app) {
        $app->render('admin/user/index.twig');
    })->setName('admin.user.index');

    /**
     * Display form and handle user creation 
     * 
     * @todo Validate username
     * @todo Option for send notification email
     * @todo Option for generate random password
     */
    $app->map('/create', function() use($app, $messages) {
        $data = array();
        if ($app->request->isPost()) {
            $data['input'] = $input = $app->request->post();
            try {
                // check whether username available
                $notExists = function($username) use($app) {
                    $user = $app->db->getRepository('User')->findOneBy(array('username' => $username));
                    return $user === null;
                };

                V::create()
                        ->key('username', V::create()->notEmpty()->length(4, 16)->callback($notExists))
                        ->key('email', V::create()->notEmpty()->email())
                        ->key('password', V::create()->notEmpty()->length(6))
                        ->key('passwordConfirmation', V::create()->notEmpty()->equals($input['password']))
                        ->key('role', V::create()->notEmpty())
                        ->assert($input);

                $user = new User;
                $user->setUsername($input['username'])
                        ->setEmail($input['email'])
                        ->setPassword(password_hash($input['password'], PASSWORD_BCRYPT))
                        ->setRole($input['role']);

                $app->db->persist($user);
                $app->db->flush();

                $app->flash(ALERT_SUCCESS, gettext('User has been successfully created'));
                $app->redirect($app->urlFor("admin.user.index"));
            } catch (AllOfException $ex) {
                $data['error'] = new Error($ex->findMessages($messages));
            }
        }
        $app->render('admin/user/create.twig', $data);
    })->via("GET", "POST")->setName('admin.user.create');

    /**
     * Display edit form and update user
     * @todo Send email notification if password changed
     */
    $app->map('/:id/edit', function($id) use($app, $messages) {
        if ($user = $app->db->find("User", $id)) {
            /* @var $user User */
            $data = array("user" => $user);
            if ($app->request->isPost()) {
                try {
                    $data['input'] = $input = $app->request->post();

                    $notExists = function($username) use($app, $user) {
                        if ($user->getUsername() === $username) {
                            return true;
                        } elseif ($app->db->getRepository("User")->findOneBy(array("username" => $username)) === null) {
                            return true;
                        } else {
                            return false;
                        }
                    };

                    $validator = V::create()
                            ->key("username", V::create()->notEmpty()->length(4, 16)->callback($notExists))
                            ->key('email', V::create()->notEmpty()->email())
                            ->key("password", V::create()->length(6))
                            ->key('role', V::create()->notEmpty());

                    if (!empty($input['password'])) {
                        $validator->key("passwordConfirmation", V::create()->notEmpty()->equals($input['password']));
                        $user->setPassword(password_hash($input['password'], PASSWORD_BCRYPT));
                    }

                    $validator->assert($input);

                    $user->setUsername($input['username'])
                            ->setEmail($input['email'])
                            ->setRole($input['role']);

                    $app->db->flush($user);
                    $app->flash(ALERT_SUCCESS, gettext('User has been successfully updated'));
                    $app->redirect($app->urlFor('admin.user.index'));
                } catch (AllOfException $ex) {
                    $data['error'] = new Error($ex->findMessages($messages));
                }
            }
            $app->render("admin/user/edit.twig", $data);
        }
    })->via("GET", "POST")->setName("admin.user.edit");

    /**
     * Handle user deletion
     */
    $app->delete('/:id', function($id) use($app) {
        if ($user = $app->db->find('User', $id)) {
            /* @var $user User */
            foreach ($user->getArticles() as $i18n) {
                /* @var $i18n Articlei18n */
                $article = $i18n->getArticle();
                $article->removeI18n($i18n);
                if ($article->getI18n()->count())
                    $app->db->remove($i18n);
                else
                    $app->db->remove($article);
            }
            $app->db->remove($user);
            $app->db->flush();
            return $app->response->write(json_encode(true));
        }
    })->setName('admin.user.delete');

    /**
     * Serve datatables json data
     */
    $app->get('/datatables', function() use($app) {
        if ($app->request->isAjax() || true) {
            $qb = $app->db->getRepository("User")
                    ->createQueryBuilder("u");

            $app->contentType("application/json");
            $data = new DataTables($qb, 'admin/user/datatables.twig');
            return $app->response->write(json_encode($data));
        }
    })->setName('admin.user.datatables');
});
