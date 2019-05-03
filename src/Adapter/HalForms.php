<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Adapter;

use function Hippiemedia\Agent\Url\resolve;
use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Adapter;
use Hippiemedia\Agent\Resource;
use Hippiemedia\Agent\Link;
use Hippiemedia\Agent\Operation;
use Hippiemedia\Agent\Client\Body;
use Hippiemedia\Agent\Client\Response;

final class HalForms implements Adapter
{
    public function supports(string $contentType): bool
    {
        return $contentType === 'application/prs.hal-forms+json';
    }

    public function accepts(): string
    {
        return 'application/prs.hal-forms+json';
    }

    public function build(Agent $agent, string $url, Response $response): Resource
    {
        $body = json_decode(strval($response->body()));
        $template = $body->_templates->default;
        $state = null;
        if (is_object($body)) {
            $state = clone $body;
            unset($state->_templates, $state->_links);
        }
        return new Resource(
            $url,
            [],
            [new Operation($agent, $template->title, $template->method, resolve($url, $body->_links->self[0]->href), $template->contentType, $template->properties, $template->title)],
            $response,
            $state
        );
    }
}
