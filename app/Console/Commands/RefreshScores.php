<?php

namespace App\Console\Commands;

use App\Api\TheOddsApi;
use App\Models\ScoreRefreshLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshScores extends Command
{
    protected $signature = 'scores:refresh {sport_key? : TheOddsApi sport key (e.g. cricket_ipl)} {--days=3 : Days from now to fetch scores for}';

    protected $description = 'Fetch live scores from TheOddsApi and update game records (display only, no settlement)';

    public function handle(): int
    {
        $sportKey = $this->argument('sport_key');
        $daysFrom = (int) $this->option('days');

        $apiKey = TheOddsApi::apiKey();
        if (!$apiKey) {
            $this->error('No TheOddsApi key configured. Set THEODDSAPI_APIKEY in .env or admin settings.');
            return self::FAILURE;
        }

        $sportKeys = $sportKey ? [$sportKey] : array_keys(TheOddsApi::sportKeyMap());

        $totalUpdated = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        foreach ($sportKeys as $key) {
            $this->info("Fetching scores for {$key}...");

            $log = ScoreRefreshLog::create([
                'sport_key' => $key,
                'status' => 'running',
                'triggered_by' => null,
                'trigger_source' => 'command',
            ]);

            try {
                $result = TheOddsApi::importScores($key, $daysFrom);

                $log->update([
                    'games_updated' => $result['updated'],
                    'games_skipped' => $result['skipped'],
                    'errors_count' => $result['errors'],
                    'status' => $result['errors'] > 0 ? 'partial' : 'completed',
                ]);

                $totalUpdated += $result['updated'];
                $totalSkipped += $result['skipped'];
                $totalErrors += $result['errors'];

                $this->info("  Updated: {$result['updated']}, Skipped: {$result['skipped']}, Errors: {$result['errors']}");
            } catch (\Exception $e) {
                Log::error('Score refresh failed', ['sport_key' => $key, 'error' => $e->getMessage()]);

                $log->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                $totalErrors++;
                $this->error("  Failed: {$e->getMessage()}");
            }
        }

        $this->info("Score refresh complete. Updated: {$totalUpdated}, Skipped: {$totalSkipped}, Errors: {$totalErrors}");
        return self::SUCCESS;
    }
}
