<?php

function ajax() {
    $app = Slim\Slim::getInstance();
    if (!$app->request->isAjax()) {
        $app->notFound();
    }
}
