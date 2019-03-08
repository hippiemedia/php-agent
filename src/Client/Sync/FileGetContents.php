<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Client\Sync;

use Hippiemedia\Agent\Client;
use Hippiemedia\Agent\Client\Response;

final class FileGetContents implements Client
{
    private $defaultContext;

    public function __construct(array $defaultContext = [])
    {
        $this->defaultContext = $defaultContext;
    }

    public function __invoke($method, $uri, array $params = [], array $headers = []): Response
    {
        $body = file_get_contents($uri, false, stream_context_create(array_merge($this->defaultContext, [
            'http' => [
                'method' => $method,
                'header' => array_map(function($key, $value) {
                    return "$key: $value";
                }, array_keys($headers), $headers),
                'content' => http_build_query($params),
            ],
        ])));

        return new class($body, $http_response_header) implements Response
        {
            public function __construct($body, array $headers)
            {
                $this->body = $body;
                array_shift($headers);
                $this->headers = array_reduce($headers, function($carry, $item) {
                    if (!strpos($item, ':')) return ;
                    [$key, $value] = explode(': ', $item);
                    $carry[$key] = $value;
                    return $carry;
                }, []);
            }

            public function getHeader(string $header): ?string
            {
                return $this->headers[$header] ?? '';
            }

            public function getBody(): string
            {
                return $this->body;
            }
        };
    }
}
