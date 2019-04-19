<?php declare(strict_types=1);

namespace Hippiemedia\Agent;

use Hippiemedia\Agent\Resource;
use Rize\UriTemplate;


final class Link
{
    public $rel;
    public $href;
    public $title;
    public $fields = [];
    private $agent;
    private $resolved;
    private $uriTemplate;

    public function __construct(Agent $agent, string $rel, string $href, Resource $resolved = null, string $title = null)
    {
        $this->rel = $rel;
        $this->agent = $agent;
        $this->href = $href;
        $this->resolved = $resolved;
        $this->title = $title;
        $this->uriTemplate = new UriTemplate($this->href);
        $parsed = $this->uriTemplate->extract($this->href, $this->href);
        $this->fields = array_map(function($key, $value) {
            return (object)['name' => $key, 'value' => $value];
        }, array_keys($parsed), $parsed);
    }

    public function follow(array $params = [], bool $force = false, array $headers = []): Resource
    {
        if ($this->resolved && !$force) {
            return $this->resolved;
        }
        return $this->agent->follow($this->uriTemplate->expand('', $params), $headers);
    }

    public function __toString(): string
    {
        $fields = implode("\n        ", array_map(function($field) {
            return sprintf('%s = %s', $field->name, $field->value ?? '');
        }, $this->fields));

        return <<<DOC
        - $this->rel: GET $this->href
            $this->title
                $fields

DOC;
    }
}
