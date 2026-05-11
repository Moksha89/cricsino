<?php

namespace App\Console\Commands;

use Database\Seeders\DemoSportsSeeder;
use Illuminate\Console\Command;

class SeedDemoData extends Command
{
    protected $signature = 'demo:seed';
    protected $description = 'Seed demo sports and casino data for testing';

    public function handle()
    {
        $this->info('Seeding demo sports data...');
        $seeder = new DemoSportsSeeder();
        $seeder->run();
        $this->info('Demo data seeded successfully!');
        $this->info('IPL, T20 International, EPL, NBA matches created.');
        $this->info('Use "php artisan odds:import" to fetch real odds from The Odds API.');
        return 0;
    }
}
