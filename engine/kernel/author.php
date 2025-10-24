<?php

class Author extends User {

    public function __construct(...$lot) {
        parent::__construct(...$lot);
        if ($v = $this->author) {
            $this->title = $this->title ?? $v;
        }
    }

    public function route(...$lot) {
        if (0 === strpos($this->path ?? P, LOT . D . 'user' . D)) {
            extract(lot(), EXTR_SKIP);
            $name = $this->name;
            $parent = $this->parent;
            return ($parent ? $parent->route : "") . '/' . trim($state->x->author->route ?? 'author', '/') . '/' . $name;
        }
        return parent::route(...$lot);
    }

}