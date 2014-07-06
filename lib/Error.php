<?php

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
