<?php

class Authors extends Users {

    public function page(...$lot) {
        if (($v = reset($lot)) && $v instanceof Author) {
            return $v;
        }
        if (is_array($v) && isset($v["\0"])) {
            return $v["\0"];
        }
        return new Author(...$lot);
    }

}