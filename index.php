<?php

namespace {
    // Initialize layout variable(s)
    \lot('author', new \User);
}

namespace x\author {
    function page__route($v) {
        if (!$path = $this->_exist()) {
            return $v;
        }
        \extract(\lot(), \EXTR_SKIP);
        if (0 === \strpos($path, \LOT . \D . 'user' . \D)) {
            return '/' . \trim($state->x->author->route ?? 'author', '/') . '/' . $this->name;
        }
        return $v;
    }
    function route__author($content, $path, $query, $hash) {
        if (null !== $content) {
            return $content;
        }
        \extract(\lot(), \EXTR_SKIP);
        $route = \trim($state->x->author->route ?? 'author', '/');
        if ($part = \x\page\part($path = \trim($path ?? "", '/'))) {
            $path = \substr($path, 0, -\strlen('/' . $part));
        }
        $part = ($part ?? 0) - 1;
        $page = $author->parent ?? $author;
        $chunk = $author->chunk ?? $page->chunk ?? 5;
        $deep = "" !== $path ? 0 : true;
        $sort = \array_replace([1, 'path'], (array) ($author->sort ?? []), (array) ($page->sort ?? []));
        // For `/author/:name`, `/author/:name/:part`, and `/…/author/:name/:part`
        if ($name = \State::get('[x].query.author') ?? "") {
            $folder = \LOT . \D . 'user';
            if ($file = \exist([
                $folder . \D . $name . '.archive',
                $folder . \D . $name . '.page'
            ], 1)) {
                // For `/author/:name`
                if ($part < 0 && "" === $path) {
                    \lot('page', $author);
                    \lot('t')[] = \i('Author');
                    \lot('t')[] = $author->title;
                    \State::set([
                        'has' => [
                            'next' => false,
                            'prev' => false
                        ]
                    ]);
                    return ['page/author/' . $name, [], 200];
                }
                $pages = \Pages::from(\LOT . \D . 'page' . ("" !== $path ? \D . $path : ""), 'page', $deep)->sort($sort);
                \State::set([
                    'chunk' => $chunk,
                    'count' => $count = $pages->count, // Total number of page(s) before chunk
                    'deep' => $deep,
                    'part' => $part + 1,
                    'sort' => $sort
                ]);
                if ($count > 0) {
                    $pages = $pages->is(function ($v) use ($name) {
                        $author = $v->author;
                        if ($author instanceof \User) {
                            $author = $author->name;
                        } else {
                            $author = (string) $author;
                        }
                        return $name === $author;
                    });
                }
                \lot('t')[] = \i('Authors');
                \lot('t')[] = $author->title;
                $pager = \Pager::from($pages);
                $pager->path = $path . '/' . $route . '/' . $name;
                \lot('page', $page);
                \lot('pager', $pager = $pager->chunk($chunk, $part));
                \lot('pages', $pages = $pages->chunk($chunk, $part));
                if (0 === $pages->count) { // Total number of page(s) after chunk
                    \State::set([
                        'has' => [
                            'next' => false,
                            'prev' => false
                        ],
                        'is' => [
                            'page' => false,
                            'pages' => true
                        ]
                    ]);
                    \lot('t')[] = \i('Error');
                    return ['pages/author/' . $name, [], 404];
                }
                \State::set('has', [
                    'next' => !!$pager->next,
                    'pages' => true,
                    'parent' => !!$author->parent,
                    'prev' => !!$pager->prev
                ]);
                return ['pages/author/' . $name, [], 200];
            }
            return $content;
        }
        $chunk = $state->x->author->chunk ?? 5;
        $deep = $state->x->author->deep ?? 0;
        $sort = \array_replace([1, 'path'], (array) ($state->x->author->sort ?? []));
        // For `/…/author/:part`
        if ($path) {
            $folder = \LOT . \D . 'page' . \D . $path;
            $page = new \Page(\exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1) ?: null);
            $authors = [];
            foreach (\Pages::from($folder, 'page', $deep) as $v) {
                $author = $v->author;
                if (!$author || !($author instanceof \User)) {
                    continue;
                }
                $k = \LOT . \D . 'author' . \D . $author->name;
                if (!$k = \exist([
                    $k . '.archive',
                    $k . '.page'
                ], 1)) {
                    continue;
                }
                $authors[$k] = [
                    'parent' => $page->path,
                    'path' => $k,
                    'title' => 'asdf'
                ];
            }
            $pages = (new \Users(\array_values($authors)))->sort($sort);
            if (0 === ($count = $pages->count)) {
                return $content;
            }
            \State::set('count', $count);
            $pager = \Pager::from($pages);
            $pager->hash = $hash;
            $pager->path = $path . '/' . $route;
            $pager->query = $query;
            \lot('page', $page);
            \lot('pager', $pager = $pager->chunk($chunk, $part));
            \lot('pages', $pages = $pages->chunk($chunk, $part));
            \lot('t')[] = $page->title;
            \lot('t')[] = \i('Authors');
            if (0 === $pages->count) { // Total number of page(s) after chunk
                \State::set([
                    'has' => [
                        'next' => false,
                        'page' => false,
                        'pages' => false,
                        'parent' => !!$page->parent,
                        'prev' => false
                    ],
                    'is' => [
                        'error' => 404,
                        'page' => false,
                        'pages' => true
                    ]
                ]);
                \lot('t')[] = \i('Error');
                return ['pages/authors/' . $path, [], 404];
            }
            \State::set([
                'has' => [
                    'next' => !!$pager->next,
                    'page' => true,
                    'pages' => true,
                    'parent' => !!$page->parent,
                    'prev' => !!$pager->prev
                ],
                'is' => [
                    'error' => false,
                    'page' => false,
                    'pages' => true
                ]
            ]);
            return ['pages/authors', [], 200];
        }
        // For `/author/:part`
        $pages = \Pages::from(\LOT . \D . 'user', 'page')->sort($sort);
        if (0 === ($count = $pages->count)) { // Total number of page(s) before chunk
            return $content;
        }
        \State::set('count', $count);
        $pager = \Pager::from($pages);
        $pager->hash = $hash;
        $pager->path = $route;
        $pager->query = $query;
        \lot('page', $page = new \Page([
            'description' => \i('List of the %s.', 'authors'),
            'exist' => true,
            'title' => \i('Authors'),
            'type' => 'HTML'
        ]));
        \lot('pager', $pager = $pager->chunk($chunk, $part));
        \lot('pages', $pages = $pages->chunk($chunk, $part));
        \lot('t')[] = $page->title;
        if (0 === $pages->count) { // Total number of page(s) after chunk
            \State::set([
                'has' => [
                    'next' => false,
                    'page' => false,
                    'pages' => false,
                    'parent' => true,
                    'prev' => false
                ],
                'is' => [
                    'error' => 404,
                    'page' => false,
                    'pages' => true
                ]
            ]);
            \lot('t')[] = \i('Error');
            return ['pages/authors', [], 404];
        }
        \State::set([
            'has' => [
                'next' => !!$pager->next,
                'page' => false,
                'pages' => true,
                'parent' => !!$page->parent,
                'prev' => !!$pager->prev
            ],
            'is' => [
                'error' => false,
                'page' => false,
                'pages' => true
            ]
        ]);
        return ['pages/authors', [], 200];
    }
    function route__page($content, $path, $query, $hash) {
        if (null !== $content) {
            return $content;
        }
        \extract(\lot(), \EXTR_SKIP);
        if ($part = \x\page\part($path = \trim($path ?? "", '/'))) {
            $path = \substr($path, 0, -\strlen('/' . $part));
        }
        $route = \trim($state->x->author->route ?? 'author', '/');
        // For `/author/:part`, `/author/:name`, and `/author/:name/:part`
        if (0 === \strpos($path . '/', $route . '/')) {
            return \Hook::fire('route.author', [$content, $part ? '/' . $part : null, $query, $hash]);
        }
        if ($part && $path) {
            $a = \explode('/', $path);
            // For `/…/author/:part`, and `/…/author/:name/:part`
            if (\array_pop($a) === $route || \array_pop($a) === $route) {
                return \Hook::fire('route.author', [$content, \implode('/', $a) . '/' . $part, $query, $hash]);
            }
        }
        return $content;
    }
    // Cannot use `x\page\part()` function here because it is not yet defined :(
    // if ($part = \x\page\part($path = \trim($url->path ?? "", '/'))) {
    //     $path = \substr($path, 0, -\strlen('/' . $part));
    // }
    $path = \trim($url->path ?? "", '/');
    $part = \trim(\strrchr($path, '/') ?: $path, '/');
    if ("" !== $part && '0' !== $part[0] && \strspn($part, '0123456789') === \strlen($part) && ($part = (int) $part) > 0) {
        $path = \substr($path, 0, -\strlen('/' . $part));
    }
    $route = \trim($state->x->author->route ?? 'author', '/');
    // For `/author/…`
    if (0 === \strpos($path . '/', $route . '/')) {
        \Hook::set('route.author', __NAMESPACE__ . "\\route__author", 100);
        \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
        \State::set([
            'has' => [
                'page' => false,
                'pages' => false,
                'parent' => false,
                'part' => $part >= 0
            ],
            'is' => [
                'author' => !$part,
                'authors' => !!$part,
                'error' => 404,
                'page' => !$part,
                'pages' => !!$part
            ],
            'part' => $part
        ]);
        // For `/author/:name/…`
        if ("" !== ($v = \substr($path, \strlen($route) + 1))) {
            \State::set('[x].query.author', $v);
            $folder = \LOT . \D . 'user' . \D . \strtr($v, '/', \D);
            if ($file = \exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                \lot('author', $author = new \User($file, ['title' => 'asdf']));
                \State::set([
                    'has' => ['page' => true],
                    'is' => ['error' => false]
                ]);
            }
        }
    } else {
        $a = \explode('/', $path);
        $v = \array_pop($a);
        // For `/…/author/:part`
        if ($a && $part && $v === $route) {
            \Hook::set('route.author', __NAMESPACE__ . "\\route__author", 100);
            \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
            \State::set([
                'has' => [
                    'page' => false,
                    'pages' => false,
                    'parent' => true,
                    'part' => true
                ],
                'is' => [
                    'author' => !$part,
                    'authors' => !!$part,
                    'error' => 404,
                    'page' => !$part,
                    'pages' => !!$part
                ],
                'part' => $part
            ]);
        } else {
            $r = \array_pop($a);
            $folder = \LOT . \D . 'user' . \D . $v;
            // For `/…/author/:name/:part`
            if ($a && $part && $r === $route) {
                \Hook::set('route.author', __NAMESPACE__ . "\\route__author", 100);
                \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
                \State::set('[x].query.author', $v);
                \State::set([
                    'has' => [
                        'page' => false,
                        'pages' => false,
                        'parent' => true,
                        'part' => true
                    ],
                    'is' => [
                        'author' => !$part,
                        'authors' => !!$part,
                        'error' => 404,
                        'page' => !$part,
                        'pages' => !!$part
                    ],
                    'part' => $part
                ]);
                if ($file = \exist([
                    $folder . '.archive',
                    $folder . '.page'
                ], 1)) {
                    $folder = \LOT . \D . 'page' . \D . \implode(\D, $a);
                    \lot('author', new \User($file, [
                        'parent' => \exist([
                            $folder . '.archive',
                            $folder . '.page'
                        ], 1) ?: null,
                        'title' => 'asdf'
                    ]));
                    \State::set([
                        'has' => ['page' => true],
                        'is' => ['error' => false]
                    ]);
                }
            }
        }
    }
    \Hook::set('page.route', __NAMESPACE__ . "\\page__route", 100);
}