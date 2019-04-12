<?php declare(strict_types=1);

namespace tests\Agent;

use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Client;
use Hippiemedia\Agent\Client\Response;
use Hippiemedia\Agent\Client\Body;
use Hippiemedia\Agent\Adapter\Fallback;
use Hippiemedia\Agent\Link;

$client = new class implements Client {
    function __invoke($method, $uri, Body $body = null, array $headers = []): Response {
        return new class implements Response {
            public function statusCode(): int
            {
                return 200;
            }

            public function getHeader(string $header): ?string
            {
                return ['content-type' => 'application/json',][$header];
            }

            public function body(): ?Body
            {
                return Body::fromString('["CONTENT"]');
            }
        };
    }
};

//public function __construct(Agent $agent, string $rel, string $href, Resource $resolved = null, string $title)
$link = new Link(
    (new Agent($client, [], new Fallback))->preferring('*/*'),
    'rel',
    '/search/{term}/{?limit}'
);

assert($link->follow(['term' => 'test', 'limit' => 10])->url === '/search/test/?limit=10');
assert($link->fields == [
    (object)['name' => 'term', 'value' => ''],
]);

echo "✓ resolve uri template when follow\n";
