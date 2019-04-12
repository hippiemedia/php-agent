<?php declare(strict_types=1);

namespace tests\Client\Request\Body;

use Hippiemedia\Agent\Client\Body;


assert(strval(Body::fromString('test')) === 'test');
assert(strval(Body::http_build_query(['test' => ['a test']])) === 'test%5B0%5D=a+test');

echo "✓ body as string\n";
