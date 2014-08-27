<?php

/* @var $app Application */
/* @var $em Doctrine\ORM\EntityManager */

$app->group('/api', 'ajax', function() use($app) {
    // upload an image
    $app->post('/upload', function() use ($app) {
        if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
            $image = $_FILES['image'];
            $caption = $app->request->post('caption', null);
            $filename = $caption ?
                    slugify($caption) . '.' . pathinfo($image['name'], PATHINFO_EXTENSION) :
                    $image['name'];

            $destination = uniqueFilename(UPLOAD_DIR . DS . $filename);
            if (move_uploaded_file($image['tmp_name'], $destination)) {
                return $app->response->write('/images/' . basename($destination), true);
            }
        }
        $app->halt(400);
    })->setName('admin.api.upload');
});
