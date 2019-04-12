<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Client\Async\Amp;

use Hippiemedia\Agent\Client;
use Hippiemedia\Agent\Client\Response;
use Amp\Artax\DefaultClient;
use Amp\Artax\Request;
use Amp\Artax\StringBody;
use Concurrent\Task;
use Hippiemedia\Agent\Client\Body;

final class Artax implements Client
{
    public function __construct(DefaultClient $ampClient)
    {
        $this->ampClient = $ampClient;
    }

    public function __invoke($method, $uri, Body $body = null, $headers = []): Response
    {
        $response = Task::await($this->ampClient->request((new Request($uri, $method))
           ->withHeaders($headers)
           ->withBody(strval($body))
       ));

        return new class($response) implements Response {
            private $response;
            public function __construct($response)
            {
                $this->response = $response;
            }

            public function statusCode(): int
            {
                return $this->response->getStatus();
            }

            public function getHeader(string $header): ?string
            {
                return $this->response->getHeader($header);
            }

            public function body(): ?Body
            {
                return Body::fromString(await($this->response->getBody()), $this->getHeader('content-type'));
            }
        };
    }
}
