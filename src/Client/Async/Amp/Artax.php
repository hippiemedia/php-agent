<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Client\Async\Amp;

use Hippiemedia\Agent\Client;
use Hippiemedia\Agent\Client\Response;
use Amp\Artax\DefaultClient;
use Amp\Artax\Request;
use Amp\Artax\StringBody;
use function amp\fiber\await;

final class Artax implements Client
{
    public function __construct(DefaultClient $ampClient)
    {
        $this->ampClient = $ampClient;
    }

    public function __invoke($method, $uri, array $params = [], $headers = []): Response
    {
        $response = await($this->ampClient->request((new Request($uri, $method))
           ->withHeaders($headers)
           ->withBody(new StringBody(http_build_query($params)))
       ));

        return new class($response) implements Response {
            private $response;
            public function __construct($response)
            {
                $this->response = $response;
            }

            public function getHeader(string $header): ?string
            {
                return $this->response->getHeader($header);
            }

            public function getBody(): string
            {
                return await($this->response->getBody());
            }
        };
    }
}
