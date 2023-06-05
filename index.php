<?php namespace x\author;

function route__author($content, $path, $query, $hash) {
    if (null !== $content) {
        return $content;
    }
    \extract($GLOBALS, \EXTR_SKIP);
    $name = \From::query($query)['name'] ?? "";
    if ($path && \preg_match('/^(.*?)\/([1-9]\d*)$/', $path, $m)) {
        [$any, $path, $part] = $m;
    }
    $part = ((int) ($part ?? 1)) - 1;
    $path = \trim($path ?? "", '/');
    $route = \trim($state->x->author->route ?? 'author', '/');
    $folder = \LOT . \D . 'page' . \D . $path;
    if ($file = \exist([
        $folder . '.archive',
        $folder . '.page'
    ], 1)) {
        $page = new \Page($file);
    }
    $chunk = $page->chunk ?? 5;
    $deep = $page->deep ?? 0;
    $sort = [-1, 'time']; // Force page sort by the `time` data
    $pages = \Pages::from($folder, 'page', $deep)->sort($sort);
    \State::set([
        'chunk' => $chunk,
        'count' => $count = $pages->count, // Total number of page(s)
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
        $pager = \Pager::from($pages);
        $pager->path = $path . '/' . $route . '/' . $name;
        $pager = $pager->chunk($chunk, $part);
        $pages = $pages->chunk($chunk, $part);
        $count = $pages->count; // Total number of page(s) after chunk
        if (0 === $count) {
            // Greater than the maximum part or less than `1`, abort!
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
        \State::set([
            'is' => [
                'author' => false, // Never be `true`
                'authors' => true,
                'error' => false,
                'page' => false,
                'pages' => true
            ],
            'has' => [
                'page' => true,
                'pages' => $count > 0,
                'parent' => true
            ]
        ]);
        $GLOBALS['t'][] = \i('Author');
        $GLOBALS['t'][] = (string) $author;
        $GLOBALS['page'] = $page;
        $GLOBALS['pager'] = $pager;
        $GLOBALS['pages'] = $pages;
        \State::set('has', [
            'next' => !!$pager->next,
            'parent' => !!$pager->parent,
            'part' => !!($part + 1),
            'prev' => !!$pager->prev
        ]);
        return ['pages', [], 200];
    }
}

function route__page($content, $path, $query, $hash) {
    if (null !== $content) {
        return $content;
    }
    \extract($GLOBALS, \EXTR_SKIP);
    $route = \trim($state->x->author->route ?? 'author', '/');
    // Return the route value to the native page route and move the author route parameter to `name`
    if ($path && \preg_match('/^(.*?)\/' . \x($route) . '\/([^\/]+)\/([1-9]\d*)$/', $path, $m)) {
        [$any, $path, $name, $part] = $m;
        $query = \To::query(\array_replace(\From::query($query), ['name' => $name]));
        return \Hook::fire('route.author', [$content, $path . '/' . $part, $query, $hash]);
    }
    return $content;
}

$chops = \explode('/', $url->path ?? "");
$part = \array_pop($chops);
$author = \array_pop($chops);
$route = \array_pop($chops);

$GLOBALS['author'] = null;

if ($author && $route === \trim($state->x->author->route ?? 'author', '/') && ($file = \exist([
    \LOT . \D . 'user' . \D . $author . '.archive',
    \LOT . \D . 'user' . \D . $author . '.page'
], 1))) {
    $GLOBALS['author'] = new \User($file);
    \Hook::set('route.author', __NAMESPACE__ . "\\route__author", 100);
    \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
}