<?php

namespace App\Http\Api\Services;

use Illuminate\Support\Facades\DB;

class DebugService
{
    private $shouldDebug = false;
    private $debugWithQueryLog = false;
    private $debugTemp = null;
    private $debugData = null;

    protected function setupDebug(bool $shouldDebug = false, bool $withQueryLog = false): void
    {
        $this->shouldDebug = $shouldDebug;
        $this->debugWithQueryLog = $withQueryLog;
        $this->resetDebug();
    }


    protected function startDebug(): void
    {
        if ($this->shouldDebug && $this->debugTemp !== null) {
            if ($this->debugWithQueryLog) {
                DB::flushQueryLog();
                DB::enableQueryLog();
            }
            $this->debugTemp['metrics_duration_start'] = microtime(true);
            $this->debugTemp['metrics_memory_start'] = memory_get_usage();
        }
    }

    protected function endDebug(): array|null
    {
        if ($this->shouldDebug && $this->debugTemp !== null && $this->debugData !== null) {
            $this->debugData['metrics_duration'] = $this->formatDuration(microtime(true) - $this->debugTemp['metrics_duration_start']);
            $this->debugData['metrics_memory'] = $this->formatMemory(memory_get_usage() - $this->debugTemp['metrics_memory_start']);
            if ($this->debugWithQueryLog) {
                $this->debugData['query_log'] = DB::getQueryLog();
                $this->terminateDebug();
            }
        }
        return $this->debugData;
    }

    protected function resetDebug(): void
    {
        if ($this->shouldDebug) {
            $this->debugTemp = [
                'metrics_duration_start' => null,
                'metrics_memory_start' => null,
            ];
            $this->debugData = [
                'metrics_duration' => null,
                'metrics_memory' => null,
            ];
            if ($this->debugWithQueryLog) {
                $this->debugData['query_log'] = [];
            }
        } else {
            $this->debugTemp = null;
            $this->debugData = null;
        }
    }

    protected function getDebug(): array|null
    {
        return $this->debugData;
    }

    /**
     * Make sure to disable the query loggin if enabled.
     */
    protected function terminateDebug(): void
    {
        if ($this->shouldDebug && $this->debugWithQueryLog) {
            DB::disableQueryLog();
        }
    }

    private function formatDuration(float $duration): string
    {
        if ($duration < 0) {
            return 'Inconsistent duration value';
        } elseif ($duration < 1) {
            return number_format($duration * 1000, 2) . ' ms';
        } elseif ($duration < 60) {
            $seconds = floor($duration);
            $milliseconds = ($duration - $seconds) * 1000;
            return sprintf('%d s, %.2f ms', $seconds, $milliseconds);
        } else {
            $minutes = floor($duration / 60);
            $seconds = $duration - ($minutes * 60);
            return sprintf('%d min, %.3f s', $minutes, $seconds);
        }
    }

    private function formatMemory(int $memory): string
    {
        if ($memory < 0) {
            return 'Inconsistent memory usage value';
        } else if ($memory < 1024) {
            return number_format($memory) . ' bytes';
        } elseif ($memory < 1024 * 1024) {
            return number_format($memory / 1024, 2) . ' Kb';
        } elseif ($memory < 1024 * 1024 * 1024) {
            return number_format($memory / 1024 / 1024, 2) . ' Mb';
        } else {
            return number_format($memory / 1024 / 1024 / 1024, 2) . ' Gb';
        }
    }
}