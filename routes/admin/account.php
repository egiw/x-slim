<?php

/* @var $app Application */
/* @var $em Doctrine\ORM\EntityManager */

// current user action
$app->group('/account', function() use($app) {
    $app->map('/settings', function() use($app) {
        $user = $app->user;
        $settings = $user->getSettings();
        // store settings
        if ($app->request->isPost()) {
            $settings['language'] = $app->request->post('language');
            $user->setSettings($settings);
            $app->db->flush($user);
            $app->redirect($app->urlFor("admin.index"));
        }
        // render form
        $app->render("admin/account/settings.twig");
    })->via("GET", "POST")->name("account.settings");
    $app->get('/logout', function() use($app) {
        unset($_SESSION["uid"]);
        $app->redirect($app->urlFor("auth"));
    })->name("account.logout");
});
