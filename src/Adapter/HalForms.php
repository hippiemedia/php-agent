<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Adapter;

use Hippiemedia\Agent\Agent;
use Hippiemedia\Agent\Adapter;
use Hippiemedia\Agent\Resource;
use Hippiemedia\Agent\Link;
use Hippiemedia\Agent\Operation;

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

    public function build(Agent $agent, string $url, string $contentType, string $body): Resource
    {
        $state = json_decode($body);
        $template = $state->_templates->default;
        return new Resource(
            $url,
            [],
            [new Operation($agent, $template->method, $state->_links->self[0]->href, $template->contentType, $template->properties, $template->title)],
            $body
        );
    }
}
