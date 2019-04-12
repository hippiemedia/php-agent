<?php declare(strict_types=1);

namespace Hippiemedia\Agent\Client;

interface Response
{
    public function statusCode(): int;

    public function getHeader(string $header): ?string;

    public function body(): ?Body;
}
