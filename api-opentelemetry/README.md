## About

How to use OpenTelemtry with **manual** instrumentation in a Laravel Project.

The `laravel-open-telemetry` package from [Spatie](https://spatie.be/docs/laravel-open-telemetry/v1/introduction) is used.

#### Installation

1. Install the package.

```bash
composer require spatie/laravel-open-telemetry
```

2. Run the installer command

```bash
php artisan open-telemetry:install
```

This will create a `config/open-telemetry.php` with configurations of the package.

#### Setup

##### Add default tag providers

Create a Default provider ([here](https://spatie.be/docs/laravel-open-telemetry/v1/basic-usage/adding-tags))

```php
use Spatie\OpenTelemetry\Support\TagProviders\TagProvider;

class DefaultTagsProvider implements TagProvider
{
  public function tags(): array
  {
    return [
      'host.name' => gethostname(),
      'host.ip' => gethostbyname(gethostname()),
      'host.os' => php_uname('s') . ' ' . php_uname('r'),
      'host.architecture' => php_uname('m'),
      'host.memory.usage' => memory_get_usage(false),
      'host.memory.usage_peak' => memory_get_peak_usage(false),
    ];
  }
}
```

Then add this class to the `config/open-telemetry.php`:

```php
'span_tag_providers' => [
  DefaultTagsProvider::class
],
```

#### Start the traces with a middleware

Create a middleware to run the traces and add it in the routes.

```php
class OpenTelemetryTrace
{
  public function handle(Request $request, Closure $next)
  {
    $spanName = Str::kebab($request->route()->getActionMethod());
    $spanInstance = null;
    $response = null;
    try {
      $spanInstance = Measure::start($spanName)->tags([
        'http.method' => $request->method(),
        'http.base_url' => $request->getBaseUrl(),
        'http.full_url' => $request->fullUrl(),
        'http.user_agent' => $request->header('User-Agent', 'unknown'),
        'http.client_ip' => $request->ip(),
      ]);
      return $response = $next($request);
    } finally {
      if ($spanInstance !== null) {
        $spanInstance->tags([
          'http.status_code' => $response->getStatusCode() ?? 'unknown',
        ]);
      }
      Measure::stop($spanName);
    }
  }
}
```

```php
Route::middleware([..., OpenTelemetryTrace::class])->group(...);
```

#### Configure the exporter driver

In `config/open-telemetry.php`, configure the `url` and `Bearer Token`

```php
'drivers' => [
  Spatie\OpenTelemetry\Drivers\HttpDriver::class => [
    'url' => env('OTEL_EXPORTER_URL', 'http://localhost:9412/api/v2/spans'),
    'headers' => [
      'Authorization' => "Bearer " . env('OTEL_EXPORTER_TOKEN', ''),
      'Content-Type' => 'application/json',
    ],
  ],
],
```

