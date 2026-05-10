<?php

namespace App\Http\Controllers;

use App\Models\CasinoGame;
use App\Models\CasinoSession;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Str;

class CasinoController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category');
        $provider = $request->get('provider');

        $query = CasinoGame::active();

        if ($category) {
            $query->byCategory($category);
        }
        if ($provider) {
            $query->byProvider($provider);
        }

        $games = $query->orderBy('sort_order')->paginate(24);

        $categories = [
            'live_casino' => 'Live Casino',
            'slots' => 'Slots',
            'table_games' => 'Table Games',
            'teen_patti' => 'Teen Patti',
            'andar_bahar' => 'Andar Bahar',
            'roulette' => 'Roulette',
            'blackjack' => 'Blackjack',
            'baccarat' => 'Baccarat',
            'poker' => 'Poker',
        ];

        $providers = [
            'evolution' => 'Evolution Gaming',
            'pragmatic_play' => 'Pragmatic Play',
            'ezugi' => 'Ezugi',
            'betsoft' => 'BetSoft',
            'microgaming' => 'Microgaming',
        ];

        return Inertia::render('Casino/Index', [
            'games' => $games,
            'categories' => $categories,
            'providers' => $providers,
            'selectedCategory' => $category,
            'selectedProvider' => $provider,
        ]);
    }

    public function show(CasinoGame $casinoGame)
    {
        return Inertia::render('Casino/Show', [
            'game' => $casinoGame,
        ]);
    }

    public function launch(Request $request, CasinoGame $casinoGame)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->balance < 1) {
            throw ValidationException::withMessages([
                'balance' => ['Insufficient balance to play casino games.']
            ]);
        }

        $session = CasinoSession::create([
            'uuid' => Str::uuid(),
            'user_id' => $user->id,
            'casino_game_id' => $casinoGame->id,
            'balance_before' => $user->balance,
            'status' => 'active',
        ]);

        return Inertia::render('Casino/Play', [
            'game' => $casinoGame,
            'session' => $session,
            'launchUrl' => $casinoGame->launch_url,
        ]);
    }

    public function liveCasino()
    {
        $games = CasinoGame::active()->live()->orderBy('sort_order')->paginate(24);

        return Inertia::render('Casino/LiveCasino', [
            'games' => $games,
        ]);
    }
}
