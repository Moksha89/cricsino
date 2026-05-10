<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CasinoGame;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class CasinoGamesController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $query = CasinoGame::query();

        if (!empty($keyword)) {
            $query->where('name', 'LIKE', "%$keyword%")
                ->orWhere('provider', 'LIKE', "%$keyword%")
                ->orWhere('category', 'LIKE', "%$keyword%");
        }

        $games = $query->latest()->paginate(25);

        return Inertia::render('Admin/CasinoGames/Index', [
            'games' => $games,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/CasinoGames/Create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string',
            'category' => 'required|string',
            'game_id' => 'required|string',
            'image' => 'nullable|string',
            'description' => 'nullable|string',
            'launch_url' => 'nullable|url',
            'is_live' => 'boolean',
        ]);

        CasinoGame::create([
            'uuid' => Str::uuid(),
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'provider' => $request->provider,
            'category' => $request->category,
            'game_id' => $request->game_id,
            'image' => $request->image,
            'description' => $request->description,
            'launch_url' => $request->launch_url,
            'is_live' => $request->boolean('is_live'),
            'active' => true,
        ]);

        return redirect()->route('admin.casino.index')->with('success', 'Casino game created.');
    }

    public function show(CasinoGame $casinoGame)
    {
        $casinoGame->load(['sessions' => function ($q) {
            $q->latest()->take(50)->with('user');
        }]);

        return Inertia::render('Admin/CasinoGames/Show', [
            'game' => $casinoGame,
        ]);
    }

    public function update(Request $request, CasinoGame $casinoGame)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'required|string',
            'category' => 'required|string',
            'launch_url' => 'nullable|url',
            'is_live' => 'boolean',
        ]);

        $casinoGame->update($request->only([
            'name', 'provider', 'category', 'image', 'description', 'launch_url', 'is_live',
        ]));

        return back()->with('success', 'Casino game updated.');
    }

    public function toggle(CasinoGame $casinoGame)
    {
        $casinoGame->active = !$casinoGame->active;
        $casinoGame->save();
        return back()->with('success', 'Casino game status toggled.');
    }

    public function destroy(CasinoGame $casinoGame)
    {
        $casinoGame->delete();
        return redirect()->route('admin.casino.index')->with('success', 'Casino game deleted.');
    }
}
