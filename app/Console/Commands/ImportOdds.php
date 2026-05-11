<?php

namespace App\Console\Commands;

use App\Api\TheOddsApi;
use Illuminate\Console\Command;

class ImportOdds extends Command
{
    protected $signature = 'odds:import {sport? : Sport key (e.g. soccer_epl, cricket_ipl)}';
    protected $description = 'Import odds from The Odds API';

    public function handle()
    {
        $apiKey = TheOddsApi::apiKey();
        if (!$apiKey) {
            $this->error('No API key configured. Set site.theoddsapi_api_key in admin settings.');
            return 1;
        }

        $sportKey = $this->argument('sport');

        if ($sportKey) {
            $this->info("Importing odds for: {$sportKey}");
            $count = TheOddsApi::importOdds($sportKey);
            $this->info("Imported {$count} odds records.");
        } else {
            $this->info('Importing all available sports...');
            $results = TheOddsApi::importAllSports();
            foreach ($results as $sport => $count) {
                $this->line("  {$sport}: {$count} odds");
            }
            $total = array_sum($results);
            $this->info("Total: {$total} odds imported across " . count($results) . " sports.");
        }

        return 0;
    }
}
