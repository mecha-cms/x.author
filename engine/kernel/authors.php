<?php

class Authors extends Users {

    public function page(...$lot) {
        if (($v = $lot[0] ?? 0) instanceof Author) {
            return $v;
        }
        if (is_array($v)) {
            unset($v[P]);
            $lot[0] = $v;
        }
        return new Author(...$lot);
    }

}