<?php

namespace {
    // Disable this extension if `user` extension is disabled or removed ;)
    if (!isset($state->x->user)) {
        return;
    }
    // Initialize layout variable(s)
    \lot('author', new \Author);
}

namespace x\author {
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
        // For `/…/author/:part`, and `/…/author/:name/:part`
        if ($part >= 0 && $path) {
            $folder = \LOT . \D . 'page' . \D . $path;
            if ($file = \exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                \lot('page', $page = new \Page($file));
                // For `/…/author/:name/:part`
                if ($name = \State::get('[x].query.author') ?? "") {
                    $chunk = $author->chunk ?? $page->chunk ?? 5;
                    $sort = \array_replace([1, 'path'], (array) ($page->sort ?? []), (array) ($author->sort ?? []));
                    $pages = $page->children('page', $deep = true)->is(function ($v) use ($name) {
                        $v = $v->author;
                        if ($v instanceof \User) {
                            $v = $v->name;
                        } else {
                            $v = (string) $v;
                        }
                        return $name === $v;
                    })->sort($sort);
                    \State::set([
                        'chunk' => $chunk,
                        'count' => $count = $pages->count, // Total number of page(s) before chunk
                        'deep' => $deep,
                        'part' => $part + 1,
                        'sort' => $sort
                    ]);
                    \lot('t')[] = $page->title;
                    \lot('t')[] = \i('Author');
                    \lot('t')[] = $author->title;
                    $pager = \Pager::from($pages);
                    $pager->path = $path . '/' . $route . '/' . $name;
                    \lot('pager', $pager = $pager->chunk($chunk, $part));
                    \lot('pages', $pages = $pages->chunk($chunk, $part));
                    \State::set([
                        'has' => [
                            'next' => !!$pager->next,
                            'page' => !!$page->exist,
                            'pages' => ($count = $pages->count) > 0, // Total number of page(s) after chunk
                            'parent' => !!$page->parent,
                            'prev' => !!$pager->prev
                        ],
                        'is' => [
                            'error' => false,
                            'page' => false,
                            'pages' => true
                        ]
                    ]);
                    if (0 === $count) {
                        \State::set('is.error', 404);
                        \lot('t')[] = \i('Error');
                        return ['pages/author/' . $name, [], 404];
                    }
                    return ['pages/author/' . $name, [], 200];
                }
                // For `/…/author/:part`
                $authors = [];
                $chunk = $page->chunk ?? 5;
                $sort = \array_replace([1, 'path'], (array) ($page->sort ?? []));
                if ($pages = $page->children('page', $deep = true)) {
                    foreach ($pages as $v) {
                        $v = $v->author;
                        if ($v && $v instanceof \User) {
                            $authors[$v->path] = [
                                'parent' => $page->path,
                                'path' => $v->path
                            ];
                        }
                    }
                }
                $authors = \Authors::from(\array_values($authors))->sort($sort);
                \State::set([
                    'chunk' => $chunk,
                    'count' => $count = $authors->count, // Total number of page(s) before chunk
                    'deep' => $deep,
                    'part' => $part + 1,
                    'sort' => $sort
                ]);
                \lot('t')[] = $page->title;
                \lot('t')[] = \i('Authors');
                $pager = \Pager::from($authors);
                $pager->path = $path . '/' . $route;
                \lot('pager', $pager = $pager->chunk($chunk, $part));
                \lot('pages', $authors = $authors->chunk($chunk, $part));
                \State::set([
                    'has' => [
                        'next' => !!$pager->next,
                        'page' => !!$page->exist,
                        'pages' => ($count = $authors->count) > 0, // Total number of page(s) after chunk
                        'parent' => !!$page->parent,
                        'prev' => !!$pager->prev
                    ],
                    'is' => [
                        'error' => false,
                        'page' => false,
                        'pages' => true
                    ]
                ]);
                if (0 === $count) {
                    \State::set('is.error', 404);
                    \lot('t')[] = \i('Error');
                    return ['pages/author', [], 404];
                }
                return ['pages/author', [], 200];
            }
            return $content;
        }
        // For `/author/:name`, and `/author/:name/:part`
        if ($name = \State::get('[x].query.author') ?? "") {
            \lot('page', $author);
            $folder = \LOT . \D . 'user' . \D . $name;
            if ($file = \exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                // For `/author/:name`
                if ($part < 0) {
                    \lot('t')[] = \i('Author');
                    \lot('t')[] = $author->title;
                    \State::set([
                        'has' => [
                            'page' => !!$author->exist
                        ],
                        'is' => [
                            'error' => false,
                            'page' => true,
                            'pages' => false
                        ]
                    ]);
                    return ['page/author/' . $name, [], 200];
                }
                $chunk = $author->chunk ?? 5;
                $sort = \array_replace([1, 'path'], (array) ($author->sort ?? []));
                // For `/author/:name/:part`
                $pages = \Pages::from(\LOT . \D . 'page' . ("" !== $path ? \D . $path : ""), 'page', $deep = true)->is(function ($v) use ($name) {
                    $v = $v->author;
                    if ($v instanceof \User) {
                        $v = $v->name;
                    } else {
                        $v = (string) $v;
                    }
                    return $name === $v;
                })->sort($sort);
                \State::set([
                    'chunk' => $chunk,
                    'count' => $count = $pages->count, // Total number of page(s) before chunk
                    'deep' => $deep,
                    'part' => $part + 1,
                    'sort' => $sort
                ]);
                \lot('t')[] = \i('Author');
                \lot('t')[] = $author->title;
                \lot('t')[] = \i('Pages');
                $pager = \Pager::from($pages);
                $pager->path = $path . '/' . $route . '/' . $name;
                \lot('pager', $pager = $pager->chunk($chunk, $part));
                \lot('pages', $pages = $pages->chunk($chunk, $part));
                \State::set([
                    'has' => [
                        'next' => !!$pager->next,
                        'page' => !!$page->exist,
                        'pages' => ($count = $pages->count) > 0, // Total number of page(s) after chunk
                        'parent' => !!$page->parent,
                        'prev' => !!$pager->prev
                    ],
                    'is' => [
                        'error' => false,
                        'page' => false,
                        'pages' => true
                    ]
                ]);
                if (0 === $count) {
                    \State::set('is.error', 404);
                    \lot('t')[] = \i('Error');
                    return ['pages/author/' . $name, [], 404];
                }
                return ['pages/author/' . $name, [], 200];
            }
            return $content;
        }
        if ($part < 0) {
            \State::set([
                'is' => [
                    'author' => false,
                    'authors' => false
                ]
            ]);
            return $content;
        }
        $chunk = $state->x->author->chunk ?? 5;
        $deep = $state->x->author->deep ?? 0;
        $sort = \array_replace([1, 'path'], (array) ($state->x->author->sort ?? []));
        // For `/author/:part`
        $pages = \Authors::from(\LOT . \D . 'user', 'page')->sort($sort);
        \State::set('count', $count = $pages->count); // Total number of page(s) before chunk
        if (0 === $count) {
            \State::set('is.error', 404);
            \lot('t')[] = \i('Error');
            return ['pages/authors', [], 404];
        }
        $pager = \Pager::from($pages);
        $pager->hash = $hash;
        $pager->path = $route;
        $pager->query = $query;
        \lot('page', $page = new \Page([
            'description' => \i('List of site %s.', 'authors'),
            'exist' => true,
            'title' => \i('Authors'),
            'type' => 'HTML'
        ]));
        \lot('pager', $pager = $pager->chunk($chunk, $part));
        \lot('pages', $pages = $pages->chunk($chunk, $part));
        \lot('t')[] = $page->title;
        \State::set([
            'has' => [
                'next' => !!$pager->next,
                'page' => !!$page->exist,
                'pages' => ($count = $pages->count) > 0, // Total number of page(s) after chunk
                'parent' => !!$page->parent,
                'prev' => !!$pager->prev
            ],
            'is' => [
                'error' => false,
                'page' => false,
                'pages' => true
            ]
        ]);
        if (0 === $count) {
            \State::set('is.error', 404);
            \lot('t')[] = \i('Error');
            return ['pages/authors', [], 404];
        }
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
            // For `/…/author/:part`
            if ($route === ($v = \array_pop($a))) {
                return \Hook::fire('route.author', [$content, ($a ? '/' . \implode('/', $a) : "") . '/' . $part, $query, $hash]);
            }
            // For `/…/author/:name/:part`
            $folder = \LOT . \D . 'user' . \D . $v;
            if ($route === \array_pop($a) && \exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                return \Hook::fire('route.author', [$content, ($a ? '/' . \implode('/', $a) : "") . '/' . $part, $query, $hash]);
            }
        }
        return $content;
    }
    // Cannot use function `x\page\part()` here because it is not yet defined :(
    // if ($part = \x\page\part($path = \trim($url->path ?? "", '/'))) {
    //     $path = \substr($path, 0, -\strlen('/' . $part));
    // }
    $path = \trim($url->path ?? "", '/');
    $part = \trim(\strrchr($path, '/') ?: $path, '/');
    if ("" !== $part && '0' !== $part[0] && \strspn($part, '0123456789') === \strlen($part) && ($part = (int) $part) > 0) {
        $path = \substr($path, 0, -\strlen('/' . $part));
    } else {
        $part = null;
    }
    $part = ($part ?? 0) - 1;
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
                'author' => $part < 0,
                'authors' => $part >= 0,
                'error' => 404,
                'page' => $part < 0,
                'pages' => $part >= 0
            ],
            'part' => $part + 1
        ]);
        // For `/author/:name/…`
        if ("" !== ($v = \substr($path, \strlen($route) + 1))) {
            \State::set('[x].query.author', $v);
            $folder = \LOT . \D . 'user' . \D . \strtr($v, '/', \D);
            if ($file = \exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                \lot('author', $author = new \Author($file));
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
        if ($a && $part >= 0 && $v === $route) {
            $folder = \LOT . \D . 'page' . \D . \implode(\D, $a);
            if ($file = \exist([
                $folder . '.archive',
                $folder . '.page'
            ], 1)) {
                \Hook::set('route.author', __NAMESPACE__ . "\\route__author", 100);
                \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
                $page = new \Page($file);
                \State::set([
                    'has' => [
                        'page' => !!$page->exist,
                        'pages' => ($pages = $page->children) && $pages->count > 0,
                        'parent' => !!$page->parent,
                        'part' => true
                    ],
                    'is' => [
                        'author' => false,
                        'authors' => true,
                        'error' => 404,
                        'page' => false,
                        'pages' => true
                    ],
                    'part' => $part + 1
                ]);
            }
        } else {
            $r = \array_pop($a);
            $folder = \LOT . \D . 'user' . \D . $v;
            // For `/…/author/:name/:part`
            if ($a && $part >= 0 && $r === $route) {
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
                        'author' => $part < 0,
                        'authors' => $part >= 0,
                        'error' => 404,
                        'page' => $part < 0,
                        'pages' => $part >= 0
                    ],
                    'part' => $part + 1
                ]);
                if ($file = \exist([
                    $folder . '.archive',
                    $folder . '.page'
                ], 1)) {
                    $folder = \LOT . \D . 'page' . \D . \implode(\D, $a);
                    \lot('author', new \Author($file, [
                        'parent' => \exist([
                            $folder . '.archive',
                            $folder . '.page'
                        ], 1) ?: null
                    ]));
                    \State::set([
                        'has' => ['page' => true],
                        'is' => ['error' => false]
                    ]);
                }
            }
        }
    }
}