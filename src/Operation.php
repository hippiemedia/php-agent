<?php declare(strict_types=1);

namespace Hippiemedia\Agent;

use Amp\Promise;

final class Operation
{
    public $rel;
    public $method;
    public $href;
    public $contentType;
    public $fields;
    public $title;
    private $agent;

    public function __construct(Agent $agent, string $rel, string $method, string $href, string $contentType, array $fields = [], string $title)
    {
        $this->agent = $agent;
        $this->rel = $rel;
        $this->method = $method;
        $this->href = $href;
        $this->contentType = $contentType;
        $this->fields = $fields;
        $this->title = $title;
    }

    public function submit(array $params = []): Promise//<Resource>
    {
        return $this->agent->call($this->method, $this->href, $params, ['Content-Type' => $this->contentType,]);
    }

    public function __toString(): string
    {
        $fields = implode("\n        ", array_map(function($field) {
            return sprintf('%s = %s', $field->name, $field->value ?? '');
        }, $this->fields));

        return <<<DOC
        - $this->method $this->href ($this->contentType)
            $this->title
                $fields
        DOC;
    }
}
