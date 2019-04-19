<?php declare(strict_types=1);

namespace tests\Client\FileGetContents;

use Hippiemedia\Agent\Client\Sync\FileGetContents;

$client = new FileGetContents;
assert(strval($client('GET', __FILE__)->body()) === file_get_contents(__FILE__));

assert($client('GET', 'https://google.com')->statusCode() === 301);

echo "✓ uses file_get_contents\n";
