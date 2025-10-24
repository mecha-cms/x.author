<?php

class Authors extends Users {

    public function page(...$lot) {
        return new Author(...$lot);
    }

}