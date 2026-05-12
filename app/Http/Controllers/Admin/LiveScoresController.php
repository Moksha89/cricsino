<?php

namespace App\Http\Controllers\Admin;

use App\Api\TheOddsApi;
use App\Http\Controllers\Controller;
use App\Models\ScoreRefreshLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class LiveScoresController extends Controller
{
    public function index()
    {
        $logs = ScoreRefreshLog::orderByDesc('created_at')->take(50)->get();
        $sportKeys = array_keys(TheOddsApi::sportKeyMap());
        $hasApiKey = !empty(TheOddsApi::apiKey());

        $gamesWithScores = \App\Models\Score::distinct('game_id')->count('game_id');
        $liveGames = \App\Models\Game::where('is_live', true)->where('closed', false)->count();

        return Inertia::render('Admin/LiveScores/Index', [
            'logs' => $logs,
            'sportKeys' => $sportKeys,
            'hasApiKey' => $hasApiKey,
            'gamesWithScores' => $gamesWithScores,
            'liveGames' => $liveGames,
        ]);
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'sport_key' => 'required|string',
        ]);

        $sportKey = $request->input('sport_key');
        $apiKey = TheOddsApi::apiKey();

        if (!$apiKey) {
            return back()->with('error', 'No TheOddsApi key configured. Set it in .env or admin settings.');
        }

        $log = ScoreRefreshLog::create([
            'sport_key' => $sportKey,
            'status' => 'running',
            'triggered_by' => $request->user()->id,
            'trigger_source' => 'admin_manual',
        ]);

        try {
            $result = TheOddsApi::importScores($sportKey, 3);

            $log->update([
                'games_updated' => $result['updated'],
                'games_skipped' => $result['skipped'],
                'errors_count' => $result['errors'],
                'status' => $result['errors'] > 0 ? 'partial' : 'completed',
            ]);

            return back()->with('success', "Refreshed scores: {$result['updated']} updated, {$result['skipped']} skipped, {$result['errors']} errors.");
        } catch (\Exception $e) {
            Log::error('Score refresh failed', [
                'sport_key' => $sportKey,
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return back()->with('error', 'Score refresh failed: ' . $e->getMessage());
        }
    }
}
