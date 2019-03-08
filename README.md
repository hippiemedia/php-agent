# hippiemedia/php-agent

## What ?

A php library that navigates through HTTP hypermedia APIs.


## How ?

    composer config repositories.hippiemedia/agent vcs https://github.com/hippiemedia/php-agent/
    composer require 'hippiemedia/agent'

```php
$agent = (new Agent($client, ['authorization' => getenv('TOKEN')], new HalJson(new HalForms), new HalForms, new SirenJson))
    ->preferring('application/vnd.siren+json');

$host = getenv('HOST');
$entrypoint = $agent->follow("$host/api");
echo $entrypoint;

echo $entrypoint->operation('subscribe')->submit(json_decode(file_get_contents('php://stdin'), true));
```

> Note: `$client` must be an implementation of `Hippiemedia\Agent\Client`. You can find a [sync](./example/sync.php) or an [async](./example/async.php) example in this repo.

## Async support

You can exploit [`ext-async`](https://github.com/concurrent-php/ext-async) to make non-blocking calls by using the [amp/artax](src/Client/Async/Amp/Artax.php) client.

## Tests

Run all the tests in parallel:

    find tests -name '*.php' | xargs -P0 -n1 php -d auto_prepend_file=vendor/autoload.php
