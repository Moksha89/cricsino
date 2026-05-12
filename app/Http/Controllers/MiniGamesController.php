<?php

namespace App\Http\Controllers;

use App\Enums\TransactionAction;
use App\Enums\TransactionType;
use App\Models\CasinoGame;
use App\Models\CasinoSession;
use App\Models\User;
use App\Support\ProvablyFair;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Str;

class MiniGamesController extends Controller
{
    private static function gameId(string $slug): int
    {
        static $ids = [];
        if (!isset($ids[$slug])) {
            $ids[$slug] = CasinoGame::where('slug', $slug)->value('id') ?? 0;
        }
        return $ids[$slug];
    }

    // ─── CRASH GAME ───

    public function crash()
    {
        return Inertia::render('Casino/Crash');
    }

    public function crashBet(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'auto_cashout' => 'nullable|numeric|min:1.01',
        ]);

        $serverSeed = ProvablyFair::generateServerSeed();
        $clientSeed = $request->input('client_seed', Str::random(16));
        $nonce = rand(1, 999999);
        $crashPoint = ProvablyFair::crashPoint($serverSeed, $clientSeed, $nonce);

        return DB::transaction(function () use ($request, $serverSeed, $clientSeed, $nonce, $crashPoint) {
            $user = User::lockForUpdate()->find($request->user()->id);
            if ($user->balance < $request->amount) {
                throw ValidationException::withMessages(['amount' => ['Insufficient balance.']]);
            }

            $balance_before = $user->balance;
            $user->decrement('balance', $request->amount);

            $autoCashout = $request->auto_cashout;
            $won = $autoCashout && $autoCashout <= $crashPoint;
            $payout = $won ? round($request->amount * $autoCashout, 2) : 0;

            if ($won) {
                $user->increment('balance', $payout);
            }

            CasinoSession::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'casino_game_id' => self::gameId('crash'),
                'bet_amount' => $request->amount,
                'win_amount' => $payout,
                'balance_before' => $balance_before,
                'status' => 'completed',
            ]);

            return response()->json([
                'crash_point' => $crashPoint,
                'won' => $won,
                'payout' => $payout,
                'balance' => $user->fresh()->balance,
                'server_seed' => $serverSeed,
                'client_seed' => $clientSeed,
                'nonce' => $nonce,
                'server_seed_hash' => ProvablyFair::hashServerSeed($serverSeed),
            ]);
        });
    }

    // ─── DICE GAME ───

    public function dice()
    {
        return Inertia::render('Casino/Dice');
    }

    public function diceBet(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'target' => 'required|numeric|min:1|max:98',
            'direction' => 'required|in:over,under',
        ]);

        $serverSeed = ProvablyFair::generateServerSeed();
        $clientSeed = $request->input('client_seed', Str::random(16));
        $nonce = rand(1, 999999);
        $roll = ProvablyFair::diceResult($serverSeed, $clientSeed, $nonce);

        $won = $request->direction === 'over'
            ? $roll > $request->target
            : $roll < $request->target;

        $winChance = $request->direction === 'over'
            ? 100 - $request->target
            : $request->target;
        $multiplier = round((100 / $winChance) * 0.97, 4); // 3% house edge
        $payout = $won ? round($request->amount * $multiplier, 2) : 0;

        return DB::transaction(function () use ($request, $serverSeed, $clientSeed, $nonce, $roll, $won, $multiplier, $payout) {
            $user = User::lockForUpdate()->find($request->user()->id);
            if ($user->balance < $request->amount) {
                throw ValidationException::withMessages(['amount' => ['Insufficient balance.']]);
            }

            $balance_before = $user->balance;
            $user->decrement('balance', $request->amount);
            if ($won) {
                $user->increment('balance', $payout);
            }

            CasinoSession::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'casino_game_id' => self::gameId('dice'),
                'bet_amount' => $request->amount,
                'win_amount' => $payout,
                'balance_before' => $balance_before,
                'status' => 'completed',
            ]);

            return response()->json([
                'roll' => $roll,
                'target' => $request->target,
                'direction' => $request->direction,
                'won' => $won,
                'multiplier' => $multiplier,
                'payout' => $payout,
                'balance' => $user->fresh()->balance,
                'server_seed' => $serverSeed,
                'client_seed' => $clientSeed,
                'nonce' => $nonce,
            ]);
        });
    }

    // ─── MINES GAME ───

    public function mines()
    {
        return Inertia::render('Casino/Mines');
    }

    public function minesStart(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'mines' => 'required|integer|min:1|max:24',
        ]);

        $serverSeed = ProvablyFair::generateServerSeed();
        $clientSeed = $request->input('client_seed', Str::random(16));
        $nonce = rand(1, 999999);
        $result = ProvablyFair::minesGrid($serverSeed, $clientSeed, $nonce, $request->mines);

        return DB::transaction(function () use ($request, $serverSeed, $clientSeed, $nonce, $result) {
            $user = User::lockForUpdate()->find($request->user()->id);
            if ($user->balance < $request->amount) {
                throw ValidationException::withMessages(['amount' => ['Insufficient balance.']]);
            }

            $balance_before = $user->balance;
            $user->decrement('balance', $request->amount);

            $session = CasinoSession::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'casino_game_id' => self::gameId('mines'),
                'bet_amount' => $request->amount,
                'win_amount' => 0,
                'balance_before' => $balance_before,
                'status' => 'active',
                'session_token' => encrypt(json_encode([
                    'mines' => $result['mines'],
                    'server_seed' => $serverSeed,
                    'client_seed' => $clientSeed,
                    'nonce' => $nonce,
                    'revealed' => [],
                    'amount' => $request->amount,
                    'mine_count' => $request->mines,
                ])),
            ]);

            return response()->json([
                'session_id' => $session->uuid,
                'server_seed_hash' => ProvablyFair::hashServerSeed($serverSeed),
                'balance' => $user->fresh()->balance,
            ]);
        });
    }

    public function minesReveal(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
            'position' => 'required|integer|min:0|max:24',
        ]);

        return DB::transaction(function () use ($request) {
            $session = CasinoSession::lockForUpdate()
                ->where('uuid', $request->session_id)
                ->where('user_id', $request->user()->id)
                ->where('status', 'active')
                ->firstOrFail();

            $data = json_decode(decrypt($session->session_token), true);
            $mines = $data['mines'];
            $revealed = $data['revealed'];

            if (in_array($request->position, $revealed)) {
                return response()->json(['error' => 'Already revealed'], 400);
            }

            $isMine = in_array($request->position, $mines);
            $revealed[] = $request->position;
            $data['revealed'] = $revealed;

            if ($isMine) {
                $session->status = 'completed';
                $session->save();

                return response()->json([
                    'is_mine' => true,
                    'mines' => $mines,
                    'payout' => 0,
                    'server_seed' => $data['server_seed'],
                    'balance' => $request->user()->fresh()->balance,
                ]);
            }

            $safeCount = count($revealed);
            $multiplier = static::minesMultiplier($safeCount, $data['mine_count']);

            $session->session_token = encrypt(json_encode($data));
            $session->save();

            return response()->json([
                'is_mine' => false,
                'position' => $request->position,
                'multiplier' => $multiplier,
                'potential_payout' => round($data['amount'] * $multiplier, 2),
            ]);
        });
    }

    public function minesCashout(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        return DB::transaction(function () use ($request) {
            $session = CasinoSession::lockForUpdate()
                ->where('uuid', $request->session_id)
                ->where('user_id', $request->user()->id)
                ->where('status', 'active')
                ->firstOrFail();

            $data = json_decode(decrypt($session->session_token), true);
            $safeCount = count($data['revealed']);

            if ($safeCount === 0) {
                return response()->json(['error' => 'Must reveal at least one tile'], 400);
            }

            $multiplier = static::minesMultiplier($safeCount, $data['mine_count']);
            $payout = round($data['amount'] * $multiplier, 2);

            $user = User::lockForUpdate()->find($request->user()->id);
            $user->increment('balance', $payout);

            $session->win_amount = $payout;
            $session->status = 'completed';
            $session->save();

            return response()->json([
                'payout' => $payout,
                'multiplier' => $multiplier,
                'mines' => $data['mines'],
                'server_seed' => $data['server_seed'],
                'balance' => $user->fresh()->balance,
            ]);
        });
    }

    private static function minesMultiplier(int $revealed, int $mineCount): float
    {
        $totalTiles = 25;
        $safeTiles = $totalTiles - $mineCount;
        $multiplier = 1.0;

        for ($i = 0; $i < $revealed; $i++) {
            $multiplier *= ($totalTiles - $i) / ($safeTiles - $i);
        }

        return round($multiplier * 0.97, 4); // 3% house edge
    }

    // ─── HI-LO GAME ───

    public function hilo()
    {
        return Inertia::render('Casino/HiLo');
    }

    public function hiloBet(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'guess' => 'required|in:higher,lower',
            'current_card' => 'nullable|integer|min:1|max:13',
        ]);

        $serverSeed = ProvablyFair::generateServerSeed();
        $clientSeed = $request->input('client_seed', Str::random(16));
        $nonce = rand(1, 999999);
        $card = ProvablyFair::hiloCard($serverSeed, $clientSeed, $nonce);
        $currentCard = $request->current_card ?? rand(1, 13);

        $won = $request->guess === 'higher'
            ? $card['numeric'] > $currentCard
            : $card['numeric'] < $currentCard;

        $higherChance = (13 - $currentCard) / 13;
        $lowerChance = ($currentCard - 1) / 13;
        $winChance = $request->guess === 'higher' ? $higherChance : $lowerChance;
        $multiplier = $winChance > 0 ? round((1 / $winChance) * 0.97, 4) : 0;
        $payout = $won ? round($request->amount * $multiplier, 2) : 0;

        return DB::transaction(function () use ($request, $serverSeed, $clientSeed, $nonce, $card, $currentCard, $won, $multiplier, $payout) {
            $user = User::lockForUpdate()->find($request->user()->id);
            if ($user->balance < $request->amount) {
                throw ValidationException::withMessages(['amount' => ['Insufficient balance.']]);
            }

            $balance_before = $user->balance;
            $user->decrement('balance', $request->amount);
            if ($won) {
                $user->increment('balance', $payout);
            }

            CasinoSession::create([
                'uuid' => Str::uuid(),
                'user_id' => $user->id,
                'casino_game_id' => self::gameId('hilo'),
                'bet_amount' => $request->amount,
                'win_amount' => $payout,
                'balance_before' => $balance_before,
                'status' => 'completed',
            ]);

            return response()->json([
                'card' => $card,
                'current_card' => $currentCard,
                'guess' => $request->guess,
                'won' => $won,
                'multiplier' => $multiplier,
                'payout' => $payout,
                'balance' => $user->fresh()->balance,
                'server_seed' => $serverSeed,
                'client_seed' => $clientSeed,
                'nonce' => $nonce,
            ]);
        });
    }
}
