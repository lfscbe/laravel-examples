<?php

namespace App\Http\Api\Controllers;

class AnalyticsController extends Controller
{
  public function index(Request $resquest, ExampleService $exampleService)
  {
    // Example of using the service
    $data = $exampleService->execute(['shouldDebug' => true, 'withQueryLogs' => false]);

    return response()->json($data);
  }
}

