<?php

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

class Error {

    private $messages = array();

    public function __construct($messages) {
        foreach ($messages as $key => $message) {
            if (!empty($message)) {
                if (strpos($key, "_") !== false) {
                    list($p, $c) = explode("_", $key);
                    if (!isset($this->messages[$p]) || is_string($this->messages[$p])) {
                        $this->messages[$p] = array();
                    }
                    $this->messages[$p][$c] = $message;
                } else {
                    $this->messages[$key] = $message;
                }
            }
        }
    }

    public function __call($name, $arguments) {
        if (isset($this->messages[$name])) {
            return $this->messages[$name];
        } else {
            return null;
        }
    }

}
