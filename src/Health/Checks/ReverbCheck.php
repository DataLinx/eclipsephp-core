<?php

namespace Eclipse\Core\Health\Checks;

use Exception;
use Illuminate\Support\Facades\Cache;
use Spatie\Health\Checks\Check;
use Spatie\Health\Checks\Result;
use Spatie\Health\Traits\Pingable;

class ReverbCheck extends Check
{
    use Pingable;

    protected ?string $heartbeatUrl = null;

    /**
     * Optional setter. If a consumer of the class calls it, the provided
     * URL will override the default config URL in run().
     */
    public function heartbeatUrl(string $url): self
    {
        $this->heartbeatUrl = $url;

        return $this;
    }

    public function run(): Result
    {
        $result = Result::make();

        // Check if Reverb is configured
        if (! config('reverb.servers.reverb')) {
            return $result->failed('Reverb does not seem to be configured correctly.');
        }

        $config = config('reverb.servers.reverb');
        $configHost = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 8080;

        // If server is bound to 0.0.0.0, we should connect to localhost
        $host = $configHost === '0.0.0.0' ? '127.0.0.1' : $configHost;

        // Try to connect to the Reverb server using socket connection
        try {
            $socket = @fsockopen($host, $port, $errno, $errstr, 5);

            if (! $socket) {
                // Check if there's a recent restart signal (might indicate server is starting)
                $lastRestart = Cache::get('laravel:reverb:restart');
                if ($lastRestart && now()->diffInMinutes($lastRestart) < 2) {
                    return $result
                        ->warning('Reverb server might be restarting.')
                        ->shortSummary('Restarting');
                }

                return $result
                    ->failed("Cannot connect to Reverb server: {$errstr} ({$errno})")
                    ->shortSummary('Not running');
            }

            fclose($socket);
        } catch (Exception $e) {
            // Check if there's a recent restart signal (might indicate server is starting)
            $lastRestart = Cache::get('laravel:reverb:restart');
            if ($lastRestart && now()->diffInMinutes($lastRestart) < 2) {
                return $result
                    ->warning('Reverb server might be restarting.')
                    ->shortSummary('Restarting');
            }

            return $result
                ->failed("Reverb server is not running or not accessible: {$e->getMessage()}")
                ->shortSummary('Not running');
        }

        $heartbeatUrl = $this->heartbeatUrl ?? config('health.reverb.heartbeat_url');

        if ($heartbeatUrl) {
            $this->pingUrl($heartbeatUrl);
        }

        return $result->ok()->shortSummary('Running');
    }
}
