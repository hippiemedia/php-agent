<?php declare(strict_types=1);

namespace tests\Client\FileGetContents;

use Hippiemedia\Agent\Client\Sync\FileGetContents;

$client = new FileGetContents;
assert($client('GET', __FILE__)->getBody() === file_get_contents(__FILE__));

echo "✓ uses_file_get_contents\n";
