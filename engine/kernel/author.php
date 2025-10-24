<?php

class Author extends User {

    public function __construct(...$lot) {
        parent::__construct(...$lot);
        if ($author = $this->author) {
            $this->title = $author;
        }
    }

    public function route(...$lot) {
        $v = $lot[0] ?? null;
        if (!$path = $this->_exist()) {
            return $v;
        }
        extract(lot(), EXTR_SKIP);
        if (0 === strpos($path, LOT . D . 'user' . D)) {
            return '/' . trim($state->x->author->route ?? 'author', '/') . '/' . $this->name;
        }
        return $v;
    }

    public function URL(...$lot) {
        extract(lot(), EXTR_SKIP);
        if ($route = $this->route) {
            return $url . $route;
        }
    }

}