<?php declare(strict_types=1);

namespace tests\Agent;

use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Client\Response;
use Hippiemedia\Agent\Client;
use Hippiemedia\Agent\Adapter\Fallback;
use Hippiemedia\Agent\Client\Body;

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
                return Body::fromString('CONTENT');
            }
        };
    }
};

$agent = (new Agent($client, [], new Fallback))->preferring('*/*');
assert(strval($agent->follow('/')->response->body()) === 'CONTENT');

echo "✓ agent is usable\n";
