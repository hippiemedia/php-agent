<?php declare(strict_types=1);

namespace tests\Agent;

use Hippiemedia\Agent\Agent;

(function() {
    $agent = (new Agent($client, [], new HalJson(new HalForms), new HalForms, new SirenJson));
    echo "✓ agent inits\n";
})();
