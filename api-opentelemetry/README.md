## About

Usando OpenTelemetry com instrumentação **manual** em projetos Laravel.

Com o pacote `laravel-open-telemetry` da [Spatie](https://spatie.be/docs/laravel-open-telemetry/v1/introduction).

</br>
</br>

## Instalação

1. Instale o pacote.

```bash
composer require spatie/laravel-open-telemetry
```

2. Rode o instalador.

```bash
php artisan open-telemetry:install
```

Isso cria o arquivo `config/open-telemetry.php` com configurações do pacote.

</br>
</br>

## Setup

### Adicionando Tags padrões

Crie um provider de Tags ([doc](https://spatie.be/docs/laravel-open-telemetry/v1/basic-usage/adding-tags)).

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

Adicione a classe em `config/open-telemetry.php`:

```php
'span_tag_providers' => [
  DefaultTagsProvider::class
],
```

</br>

### Iniciando os Traces com middlewares

Crie um middleware para rodar os Traces, e acople-o as rotas.

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

</br>

### Configurando o driver do Exporter

Em `config/open-telemetry.php`, configure a `url` and `headers` (se necessario).

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

```
OTEL_EXPORTER_URL=https://api.axiom.co/v1/datasets/<dataset>/ingest
```

