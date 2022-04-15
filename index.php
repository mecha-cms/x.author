<?php namespace x\author;

function route($content, $path, $query, $hash, $r) {
    if (null !== $content) {
        return $content;
    }
    \extract($GLOBALS, \EXTR_SKIP);
    $name = $r['name'];
    if ($path && \preg_match('/^(.*?)\/([1-9]\d*)$/', $path, $m)) {
        [$any, $path, $i] = $m;
    }
    $i = ((int) ($i ?? 1)) - 1;
    $path = \trim($path ?? "", '/');
    $route = \trim($state->x->author->route ?? 'author', '/');
    $folder = \LOT . \D . 'page' . \D . $path;
    if ($file = \exist([
        $folder . '.archive',
        $folder . '.page'
    ], 1)) {
        $page = new \Page($file);
    }
    \State::set([
        'chunk' => $chunk = $page['chunk'] ?? 5,
        'deep' => $deep = $page['deep'] ?? 0,
        'sort' => $sort = [-1, 'time'] // Force page sort by the `time` data
    ]);
    $pages = \Pages::from($folder, 'page', $deep)->sort($sort);
    if ($pages->count() > 0) {
        $pages->lot($pages->is(static function($v) use($name) {
            $page = new \Page($v);
            $author = $page->author->name ?? "";
            return $author && $name === $author;
        })->get());
    }
    \State::set([
        'is' => [
            'error' => false,
            'page' => false,
            'pages' => true,
            'author' => false, // Never be `true`
            'authors' => true
        ],
        'has' => [
            'page' => true,
            'pages' => $pages->count() > 0,
            'parent' => true
        ]
    ]);
    $GLOBALS['t'][] = \i('Author');
    if (\is_file($file = \LOT . \D . 'user' . \D . $name . '.page')) {
        $GLOBALS['t'][] = (string) (new \User($file));
    }
    $pager = new \Pager\Pages($pages->get(), [$chunk, $i], (object) [
        'link' => $url . '/' . $path . '/' . $route . '/' . $name
    ]);
    // Set proper parent link
    $pager->parent = $i > 0 ? (object) ['link' => $url . '/' . $path . '/' . $route . '/' . $name . '/1'] : $page;
    $pages = $pages->chunk($chunk, $i);
    $GLOBALS['page'] = $page;
    $GLOBALS['pager'] = $pager;
    $GLOBALS['pages'] = $pages;
    $GLOBALS['parent'] = $page;
    if (0 === $pages->count()) {
        // Greater than the maximum step or less than `1`, abort!
        \State::set([
            'has' => [
                'next' => false,
                'parent' => false,
                'prev' => false
            ],
            'is' => [
                'error' => 404,
                'page' => true,
                'pages' => false
            ]
        ]);
        $GLOBALS['t'][] = \i('Error');
        return ['page', [], 404];
    }
    \State::set('has', [
        'next' => !!$pager->next,
        'parent' => !!$pager->parent,
        'part' => $i + 1,
        'prev' => !!$pager->prev
    ]);
    return ['pages', [], 200];
}

$chops = \explode('/', $url->path ?? "");
$i = \array_pop($chops);
$author = \array_pop($chops);
$route = \array_pop($chops);

$GLOBALS['author'] = null;

if ($author && $route === \trim($state->x->author->route ?? 'author', '/') && \is_file($file = \LOT . \D . 'user' . \D . $author . '.page')) {
    $GLOBALS['author'] = new \User($file);
    \Hook::set('route.author', __NAMESPACE__ . "\\route", 100);
    \Hook::set('route.page', function($content, $path, $query, $hash) use($route) {
        if ($path && \preg_match('/^(.*?)\/' . \x($route) . '\/([^\/]+)\/([1-9]\d*)$/', $path, $m)) {
            [$any, $path, $name, $i] = $m;
            $r['name'] = $name;
            return \Hook::fire('route.author', [$content, $path, $query, $hash, $r]);
        }
        return $content;
    }, 90);
}