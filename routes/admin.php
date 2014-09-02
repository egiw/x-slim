<?php

/* @var $app Application */

/**
 * Administrator section
 * Restrict guest user, redirect to auth page
 */
$app->group("/admin", function(Slim\Route $route) use($app) {
    if ($app->user->isGuest()) {
        $app->flash(ALERT_DANGER, gettext("Restricted area, access denied!"));
        $app->redirect($app->urlFor("auth"), 401);
    }

    // Set language
    $settings = $app->user->getSettings();
    $locale = "en_US.utf8";
    if (isset($settings['language'])) {
        switch ($settings['language']) {
            case 'id':
                $locale = "id_ID.utf8";
                break;
        }
    }

    putenv("LANG={$locale}");
    putenv("LANGUAGE={$locale}");
    setlocale(LC_MESSAGES, $locale);
    setlocale(LC_TIME, $locale);

    $domain = "messages";
    bindtextdomain($domain, __DIR__ . "/../locale");
    textdomain($domain);

    $app->view->set('template', $app->isPjax ? "pjax_template.twig" : "admin/template.twig");
    $app->view->set('languages', array(
        "id" => gettext("Indonesian"),
        "en" => gettext("English")
    ));

    $app->translations = $translations = array(
        'en' => 'English'
    );

    $app->view->set('translations', $translations);
}, function() use ($app) {
    // display dashboard page
    $app->get('/', function() use ($app) {
        $app->render("admin/index.twig");
    })->name("admin.index");

    include 'admin/account.php';
    include 'admin/article.php';
    include 'admin/news.php';
    include 'admin/event.php';
    include 'admin/category.php';
    include 'admin/api.php';
    include 'admin/user.php';
});
