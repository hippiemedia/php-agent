<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Client\Sync;

use Hippiemedia\Agent\Client;
use Hippiemedia\Agent\Client\Response;
use Hippiemedia\Agent\Client\Body;

final class FileGetContents implements Client
{
    private $defaultContext;

    const STATUS_LINE_PATTERN = "#^
        HTTP/(?P<protocol>\d+\.\d+)[\x20\x09]+
        (?P<status>[1-5]\d\d)[\x20\x09]*
        (?P<reason>[^\x01-\x08\x10-\x19]*)
    $#ix";

    public function __construct(array $defaultContext = [])
    {
        $this->defaultContext = $defaultContext;
    }

    public function __invoke($method, $uri, Body $requestBody = null, array $headers = []): Response
    {
        try {
            $responseBody = file_get_contents($uri, false, stream_context_create(array_merge_recursive($this->defaultContext, [
                'http' => [
                    'method' => $method,
                    'header' => array_map(function($key, $value) {
                        return "$key: $value";
                    }, array_keys($headers), $headers),
                    'content' => strval($requestBody)
                ],
            ])));
        }
        catch(\Throwable $e) {
            //error_log(strval($e));
        }

        return new class($responseBody ?? null, $http_response_header ?? []) implements Response
        {
            public function __construct(?string $body, array $headers)
            {
                $statusLine = array_shift($headers);
                if (preg_match(FileGetContents::STATUS_LINE_PATTERN, strval($statusLine), $matches)) {
                    $this->statusCode = intval($matches['status']);
                }
                $this->headers = array_reduce($headers, function($carry, $item) {
                    if (!strpos($item, ':')) return ;
                    [$key, $value] = explode(': ', $item);
                    $carry[strtolower($key)] = $value;
                    return $carry;
                }, []);
                $this->body = $body ? Body::fromString($body, $this->getHeader('content-type')) : null;
            }

            public function statusCode(): int
            {
                return $this->statusCode;
            }

            public function getHeader(string $header): ?string
            {
                return $this->headers[$header] ?? null;
            }

            public function body(): ?Body
            {
                return $this->body;
            }
        };
    }
}
