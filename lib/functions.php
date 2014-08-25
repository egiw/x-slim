<?php

function ajax() {
    $app = Slim\Slim::getInstance();
    if (!$app->request->isAjax()) {
        $app->notFound();
    }
}

function slugify($text) {
    // replace non letter or digits by -
    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

    // trim
    $text = trim($text, '-');

    // transliterate
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

    // lowercase
    $text = strtolower($text);

    // remove unwanted characters
    $text = preg_replace('~[^-\w]+~', '', $text);

    if (empty($text)) {
        return 'n-a';
    }

    return $text;
}

function uniqueFilename($filename) {
    $pathinfo = pathinfo($filename);
    $result = $filename;
    $i = 0;
    while (file_exists($result)) {
        $result = $pathinfo['dirname'] . DS . $pathinfo['filename'] . '-'
                . $i . '.' . $pathinfo['extension'];
        $i++;
    }

    return $result;
}
