<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Url;

function resolve(string $base_url, string $target_url): string
{
    $target = parse_url($target_url);
    if (!empty($target['host'])) {
        return $target_url;
    }

    $base = parse_url($base_url);

    $scheme   = isset($base['scheme']) ? $base['scheme'] . '://' : '';
    $host     = isset($base['host']) ? $base['host'] : '';
    $port     = isset($base['port']) ? ':' . $base['port'] : '';
    $user     = isset($base['user']) ? $base['user'] : '';
    $pass     = isset($base['pass']) ? ':' . $base['pass']  : '';
    $pass     = ($user || $pass) ? "$pass@" : '';

    $query    = isset($target['query']) ? '?' . $target['query'] : '';
    $fragment = isset($target['fragment']) ? '#' . $target['fragment'] : '';

    if (0 === strpos($target['path'], '/')) {
        $path = $target['path'];
    }
    else {
        $basePath = $base['path'] ?? '';
        if (substr($basePath, -1) === '/') {
            $path = $basePath.$target['path'];
        }
        else {
            $path = dirname($basePath).'/'.$target['path'];
        }
    }

    $url = "{$scheme}{$user}{$pass}{$host}{$port}{$path}{$query}{$fragment}";

    return $url;
}
