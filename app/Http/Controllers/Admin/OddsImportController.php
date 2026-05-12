<?php

namespace App\Http\Controllers\Admin;

use App\Api\TheOddsApi;
use App\Http\Controllers\Controller;
use App\Models\OddsImportLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class OddsImportController extends Controller
{
    public function index()
    {
        $logs = OddsImportLog::orderByDesc('created_at')->take(50)->get();
        $sportKeys = array_keys(TheOddsApi::sportKeyMap());

        $hasApiKey = !empty(TheOddsApi::apiKey());

        $maskedKey = null;
        $rawKey = TheOddsApi::apiKey();
        if ($rawKey) {
            $maskedKey = str_repeat('*', max(0, strlen($rawKey) - 4)) . substr($rawKey, -4);
        }

        $oddsCount = \App\Models\Odd::count();
        $gamesWithOdds = \App\Models\Odd::distinct('game_id')->count('game_id');

        return Inertia::render('Admin/OddsImport/Index', [
            'logs' => $logs,
            'sportKeys' => $sportKeys,
            'hasApiKey' => $hasApiKey,
            'maskedKey' => $maskedKey,
            'oddsCount' => $oddsCount,
            'gamesWithOdds' => $gamesWithOdds,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'sport_key' => 'required|string',
        ]);

        $sportKey = $request->input('sport_key');
        $apiKey = TheOddsApi::apiKey();

        if (!$apiKey) {
            return back()->with('error', 'No TheOddsApi key configured. Set it in .env or admin settings.');
        }

        $log = OddsImportLog::create([
            'sport_key' => $sportKey,
            'status' => 'running',
            'triggered_by' => $request->user()->id,
            'trigger_source' => 'admin_manual',
        ]);

        try {
            $count = TheOddsApi::importOdds($sportKey);

            $gamesImported = \App\Models\Game::where('gameId', 'like', '%')
                ->where('updated_at', '>=', $log->created_at)
                ->count();

            $log->update([
                'odds_imported' => $count,
                'games_imported' => $gamesImported,
                'status' => 'completed',
            ]);

            return back()->with('success', "Imported {$count} odds records for {$sportKey}.");
        } catch (\Exception $e) {
            Log::error('Odds import failed', [
                'sport_key' => $sportKey,
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    public function updateApiKey(Request $request)
    {
        $request->validate([
            'api_key' => 'required|string|min:10',
        ]);

        Setting::where('name', 'theoddsapi_api_key')
            ->where('group', 'site')
            ->update(['val' => $request->input('api_key')]);

        settings()->refresh();

        return back()->with('success', 'TheOddsApi key updated.');
    }
}
