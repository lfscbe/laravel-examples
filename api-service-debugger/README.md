## About

A debugger service to record duration and memory usage of the code block it wraps. You can optionally request for the DB query logs - which works kind of like a SQL `EXPLAIN` (It will use Laravel's `DB::enableQueryLog`).

It will intelligently format the results.

#### Config inputs

```php
[
  "shouldDebug" => false,  // To indicate if it should run the debug or not (false by default)
  "withQueryLog" => false,  // To indicate if it should enable query logging (false by default)
]
```

#### Return example

```php
[
  "metrics_duration" => "30.27 ms",
  "metrics_memory" => "343.01 Kb",
]
```

With query logging:

```php
[
  "metrics_duration" => "30.27 ms",
  "metrics_memory" => "343.01 Kb",
  "query_log" => [
    // query logs here...
  ]
]
```
