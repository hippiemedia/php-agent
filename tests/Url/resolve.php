<?php declare(strict_types=1);

namespace tests\Url;

use function Hippiemedia\Agent\Url\resolve;

// relative
assert(resolve('/base/uri/', 'test') === '/base/uri/test');
assert(resolve('/base/uri', 'test') === '/base/test');

// absolute
assert(resolve('/base/uri/', '/test') === '/test');
assert(resolve('/', '/test') === '/test');

// hosts
assert(resolve('https://example.org/sub', '/other/test') === 'https://example.org/other/test');
assert(resolve('https://example.org/sub/', 'test') === 'https://example.org/sub/test');
assert(resolve('https://example.org', 'http://other.example.org/test') === 'http://other.example.org/test');

// auth
assert(resolve('https://user:pass@example.org/sub', '/other/test') === 'https://user:pass@example.org/other/test');

// fragment and query
assert(resolve('https://example.org/sub?test[sub]=1#nope', '/other/test?other[thing]=0#test') === 'https://example.org/other/test?other[thing]=0#test');

// dot dot
assert(resolve('/', '../../sub') === '/../../sub');
assert(resolve('', '../../sub') === '/../../sub');

echo "✓ it resolves relative urls\n";
