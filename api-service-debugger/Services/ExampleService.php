<?php

class ExampleService extends DebugService
{

  /**
   * $debugOptions accepts two configurations:
   * - shouldDebug: bool, whether to enable debugging
   * - withQueryLogs: bool, whether to log database queries
   */
  public function execute(array|null $debugOptions): array
  {
    $this->setupDebug($debugOptions['shouldDebug'] ?? false, $debugOptions['withQueryLogs'] ?? false);

    return $this->serviceLogic();
  }

  private function serviceLogic(): array
  {
    try {
      $this->startDebug();

      // Debugged service logic here

      $debug = $this->endDebug();

      return [
        'data' => 'This is an example response from ExampleService',
        'debug' => $debug
      ];
    } finally {
      $this->terminateDebug();
    }
  }
}
