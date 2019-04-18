<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Client;

final class Body implements \IteratorAggregate
{
    private $callable;
    public $contentType;

    public function __construct(callable $callable, string $contentType = null)
    {
        $this->callable = $callable;
        $this->contentType = $contentType;
    }

    public static function fromString(string $content, string $contentType = null): self
    {
        return new self(function() use($content) {
            yield $content;
        }, $contentType);
    }

    public static function http_build_query(array $fields): self
    {
        return new self(function() use($fields) {
            yield http_build_query($fields);
        }, 'application/x-www-form-urlencoded');
    }

    public function getIterator()
    {
        return ($this->callable)();
    }

    public function __toString(): string
    {
        return implode('', iterator_to_array($this));
    }
}
