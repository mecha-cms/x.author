<?php

class Author extends User {

    public function __construct(...$lot) {
        parent::__construct(...$lot);
        if ($v = $this->author) {
            $this->title = $this->title ?? $v;
        }
    }

    public function route(...$lot) {
        if (0 === strpos($this->path ?? D, LOT . D . 'user' . D)) {
            if (!is_string($name = $this->name())) {
                return null;
            }
            extract(lot(), EXTR_SKIP);
            $parent = $this->parent;
            return ($parent ? $parent->route : "") . '/' . trim($state->x->author->route ?? 'author', '/') . '/' . $name . ($parent ? '/1' : "");
        }
        return parent::route(...$lot);
    }

}