<?php declare(strict_types=1);

namespace tests\Agent;

use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Client\Response;
use Hippiemedia\Agent\Client;
use Hippiemedia\Agent\Adapter\Fallback;

$client = new class implements Client {
    function __invoke($method, $uri, array $params = [], array $headers = []): Response {
        return new class implements Response {
            public function getHeader(string $header): ?string
            {
                return ['Content-Type' => 'application/json',][$header];
            }

            public function getBody(): string
            {
                return 'CONTENT';
            }
        };
    }
};

$agent = (new Agent($client, [], new Fallback))->preferring('*/*');
assert($agent->follow('/')->body === 'CONTENT');

echo "✓ agent is usable\n";
