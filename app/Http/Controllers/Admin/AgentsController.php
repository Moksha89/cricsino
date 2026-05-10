<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentTransaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class AgentsController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 25;
        $query = Agent::query()->with(['user', 'parent.user', 'children']);

        if (!empty($keyword)) {
            $query->where('code', 'LIKE', "%$keyword%")
                ->orWhere('role', 'LIKE', "%$keyword%")
                ->orWhereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%$keyword%")
                        ->orWhere('email', 'LIKE', "%$keyword%");
                });
        }

        $agents = $query->latest()->paginate($perPage);

        return Inertia::render('Admin/Agents/Index', [
            'agents' => $agents,
        ]);
    }

    public function create()
    {
        $users = User::whereDoesntHave('agent')->get(['id', 'name', 'email']);
        $agents = Agent::with('user')->get(['id', 'code', 'role', 'user_id']);

        return Inertia::render('Admin/Agents/Create', [
            'users' => $users,
            'parentAgents' => $agents,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:agents,user_id',
            'parent_id' => 'nullable|exists:agents,id',
            'role' => 'required|in:super_admin,master,agent',
            'commission_rate' => 'required|numeric|min:0|max:100',
            'credit_limit' => 'required|numeric|min:0',
            'max_users' => 'required|integer|min:1',
            'max_sub_agents' => 'required|integer|min:0',
        ]);

        $parent = $request->parent_id ? Agent::find($request->parent_id) : null;

        if ($parent && $parent->children()->count() >= $parent->max_sub_agents) {
            throw ValidationException::withMessages([
                'parent_id' => ['Parent agent has reached maximum sub-agents limit.']
            ]);
        }

        $agent = Agent::create([
            'uuid' => Str::uuid(),
            'user_id' => $request->user_id,
            'parent_id' => $request->parent_id,
            'role' => $request->role,
            'code' => strtoupper(Str::random(8)),
            'commission_rate' => $request->commission_rate,
            'credit_limit' => $request->credit_limit,
            'max_users' => $request->max_users,
            'max_sub_agents' => $request->max_sub_agents,
            'can_create_users' => $request->boolean('can_create_users', true),
            'can_manage_bets' => $request->boolean('can_manage_bets', false),
            'can_manage_deposits' => $request->boolean('can_manage_deposits', false),
            'allowed_sports' => $request->input('allowed_sports'),
        ]);

        return redirect()->route('admin.agents.index')
            ->with('success', "Agent {$agent->code} created successfully.");
    }

    public function show(Agent $agent)
    {
        $agent->load(['user', 'parent.user', 'children.user', 'users', 'transactions' => function ($q) {
            $q->latest()->take(50);
        }]);

        $stats = [
            'total_users' => $agent->getTotalUsers(),
            'direct_users' => $agent->users()->count(),
            'sub_agents' => $agent->children()->count(),
            'total_credited' => $agent->transactions()->where('type', 'credit')->sum('amount'),
            'total_debited' => $agent->transactions()->where('type', 'debit')->sum('amount'),
            'total_commission' => $agent->transactions()->where('type', 'commission')->sum('amount'),
        ];

        return Inertia::render('Admin/Agents/Show', [
            'agent' => $agent,
            'stats' => $stats,
        ]);
    }

    public function update(Request $request, Agent $agent)
    {
        $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
            'credit_limit' => 'required|numeric|min:0',
            'max_users' => 'required|integer|min:1',
            'max_sub_agents' => 'required|integer|min:0',
            'active' => 'required|boolean',
        ]);

        $agent->update($request->only([
            'commission_rate', 'credit_limit', 'max_users', 'max_sub_agents', 'active',
            'can_create_users', 'can_manage_bets', 'can_manage_deposits',
        ]));

        return back()->with('success', 'Agent updated successfully.');
    }

    public function credit(Request $request, Agent $agent)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $agent->creditBalance($request->amount, $request->description ?? 'Admin credit');
        return back()->with('success', "Credited ₹{$request->amount} to agent {$agent->code}.");
    }

    public function debit(Request $request, Agent $agent)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        if ($agent->balance < $request->amount) {
            throw ValidationException::withMessages(['amount' => ['Insufficient agent balance.']]);
        }

        $agent->debitBalance($request->amount, $request->description ?? 'Admin debit');
        return back()->with('success', "Debited ₹{$request->amount} from agent {$agent->code}.");
    }

    public function toggle(Agent $agent)
    {
        $agent->active = !$agent->active;
        $agent->save();
        return back()->with('success', 'Agent status toggled.');
    }

    public function destroy(Agent $agent)
    {
        if ($agent->balance != 0) {
            return back()->with('error', 'Cannot delete agent with non-zero balance.');
        }
        $agent->delete();
        return redirect()->route('admin.agents.index')->with('success', 'Agent deleted.');
    }

    public function hierarchy()
    {
        $agents = Agent::with(['user', 'children.user', 'children.children.user'])
            ->whereNull('parent_id')
            ->get();

        return Inertia::render('Admin/Agents/Hierarchy', [
            'agents' => $agents,
        ]);
    }
}
