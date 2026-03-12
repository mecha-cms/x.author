<?php

class Authors extends Users {

    public function page(...$lot) {
        static $c = [];
        if (isset($c[$id = json_encode($lot)])) {
            return $c[$id];
        }
        return ($c[$id] = new Author(...$lot));
    }

}