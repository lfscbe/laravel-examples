## About

Um serviço de Debugger para gravar `duração` e `uso de memória` do código que é envolvido.

Pode opcionalmente requisitar os `query logs` do DB - que funciona como um `EXPLAIN` do SQL. (Utiliza o `DB::enableQueryLog` do Laravel)

Os resultado são formatados de maneira inteligente. (`5 ms`, `1 s, 140 ms`, `2 min, 40 s`, `200 kb`, `1 mb`, etc)

</br>
</br>

## Utilizando

Um serviço precisa extender o `DebuggerService` para receber suas funções de debug.

As principais funções são:

1. `setupDebug` que recebe configurações como `shouldDebug` e `withQueryLog` nesta ordem.

```php
$this->setupDebug(true, false);
```

2. `startDebug` que inicia o debug.
3. `stopDebug` que termina um debug e retorna os dados coletados.
4. `terminateDebug` para caso esteja-se pegando as query logs, executar um `DB::disableQueryLog`, ao finalizar um debug.

```php
try {
  $this->startDebug();

  // Lógica a ser debugada

  $this->stopDebug();
} finally {
  $this->terminateDebug();
}
```

> `finally` necessário apenas para garantir que se algum exceção acontecer durante, será executado o `DB::disableQueryLog`.

</br>
</br>

## Exemplos de Retorno

Sem o query log:

```php
[
  "metrics_duration" => "30.27 ms",
  "metrics_memory" => "343.01 Kb",
]
```

Com o query log:

```php
[
  "metrics_duration" => "30.27 ms",
  "metrics_memory" => "343.01 Kb",
  "query_log" => [
    // query logs here...
  ]
]
```
