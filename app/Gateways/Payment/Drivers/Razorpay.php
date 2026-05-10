<?php

namespace App\Gateways\Payment\Drivers;

use App\Enums\DepositStatus;
use App\Enums\WithdrawStatus;
use App\Gateways\Payment\Actions\DepositTx;
use App\Gateways\Payment\Contracts\Provider;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Withdraw;
use App\Support\Rate;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Str;

class Razorpay implements Provider
{
    public $name = 'razorpay';
    private string $baseUrl = 'https://api.razorpay.com/v1';

    public function __construct(
        public ?string $key_id = null,
        public ?string $key_secret = null,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getConfig(): Collection
    {
        $config = settings()->for('razorpay');
        if ($config->count() == 0) {
            settings()->set('razorpay.name', 'Razorpay');
            settings()->set('razorpay.logo', '/images/gateways/razorpay.png');
            settings()->set('razorpay.enable_withdraw', 'true');
            settings()->set('razorpay.auto_approve_withdraw_address', 'true');
            settings()->set('razorpay.enable_deposit', 'true');
            settings()->set('razorpay.key_id', null);
            settings()->set('razorpay.key_secret', null);
            settings()->set('razorpay.webhook_secret', null);
            settings()->set('razorpay.max_withdraw_limit', 500000);
            settings()->set('razorpay.min_withdraw_limit', 100);
            $config = settings()->for('razorpay');
        }
        return $config;
    }

    public function setConfig(Request $request): Collection
    {
        $request->validate([
            'name' => 'required|string',
            'key_id' => 'nullable|string',
            'key_secret' => 'nullable|string',
            'webhook_secret' => 'nullable|string',
            'enable_withdraw' => 'required|string',
            'enable_deposit' => 'required|string',
            'max_withdraw_limit' => 'required|numeric',
            'min_withdraw_limit' => 'required|numeric',
        ]);

        settings()->set('razorpay.name', $request->name);
        settings()->set('razorpay.key_id', $request->key_id);
        settings()->set('razorpay.key_secret', $request->key_secret);
        settings()->set('razorpay.webhook_secret', $request->webhook_secret);
        settings()->set('razorpay.enable_withdraw', $request->enable_withdraw);
        settings()->set('razorpay.enable_deposit', $request->enable_deposit);
        settings()->set('razorpay.max_withdraw_limit', $request->max_withdraw_limit);
        settings()->set('razorpay.min_withdraw_limit', $request->min_withdraw_limit);

        return settings()->for('razorpay');
    }

    public function updateCurrencies()
    {
        Currency::updateOrCreate(
            ['code' => 'INR', 'gateway' => 'razorpay'],
            [
                'name' => 'Indian Rupee',
                'symbol' => '₹',
                'rate' => 1,
                'precision' => 2,
                'active' => true,
            ]
        );
    }

    public function deposit(Deposit $deposit)
    {
        $config = $this->getConfig();
        $keyId = $config->get('key_id') ?? $this->key_id;
        $keySecret = $config->get('key_secret') ?? $this->key_secret;

        if (!$keyId || !$keySecret) {
            Log::error('Razorpay: Missing API credentials');
            return back()->with('error', 'Payment gateway not configured');
        }

        $amountInPaise = (int) round($deposit->gateway_amount * 100);

        $response = Http::withBasicAuth($keyId, $keySecret)
            ->post($this->baseUrl . '/orders', [
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'receipt' => $deposit->uuid,
                'notes' => [
                    'deposit_id' => $deposit->id,
                    'user_id' => $deposit->user_id,
                ],
            ]);

        if ($response->failed()) {
            Log::error('Razorpay order creation failed', ['response' => $response->json()]);
            return back()->with('error', 'Failed to create payment order');
        }

        $order = $response->json();
        $deposit->remoteId = $order['id'];
        $deposit->data = $order;
        $deposit->save();

        return Inertia::render('Deposits/Razorpay', [
            'deposit' => $deposit,
            'order' => $order,
            'keyId' => $keyId,
            'callbackUrl' => route('deposits.gateway.callback', ['gateway' => 'razorpay']),
        ]);
    }

    public function checkDepositStatus(Deposit $deposit)
    {
        $config = $this->getConfig();
        $keyId = $config->get('key_id') ?? $this->key_id;
        $keySecret = $config->get('key_secret') ?? $this->key_secret;

        if (!$deposit->remoteId) return null;

        $response = Http::withBasicAuth($keyId, $keySecret)
            ->get($this->baseUrl . '/orders/' . $deposit->remoteId);

        if ($response->successful()) {
            $order = $response->json();
            if ($order['status'] === 'paid') {
                (new DepositTx)($deposit);
                return (object) ['status' => 'completed'];
            }
        }
        return null;
    }

    public function webhook(Request $request, string $type = 'deposit')
    {
        $payload = $request->all();
        $event = $payload['event'] ?? null;

        if ($event === 'payment.captured' || $event === 'order.paid') {
            $orderId = $payload['payload']['order']['entity']['id'] ?? null;
            if (!$orderId) return response('Missing order ID', 400);

            $deposit = Deposit::where('remoteId', $orderId)
                ->where('status', DepositStatus::PENDING)
                ->first();

            if ($deposit) {
                (new DepositTx)($deposit);
            }
        }

        return response('OK', 200);
    }

    public function withdraw(Collection $withdraws)
    {
        $config = $this->getConfig();
        $keyId = $config->get('key_id') ?? $this->key_id;
        $keySecret = $config->get('key_secret') ?? $this->key_secret;

        foreach ($withdraws as $withdraw) {
            $response = Http::withBasicAuth($keyId, $keySecret)
                ->post($this->baseUrl . '/payouts', [
                    'account_number' => settings('razorpay.account_number'),
                    'fund_account' => [
                        'account_type' => 'vpa',
                        'vpa' => ['address' => $withdraw->to],
                    ],
                    'amount' => (int) round($withdraw->gateway_amount * 100),
                    'currency' => 'INR',
                    'mode' => 'UPI',
                    'purpose' => 'payout',
                    'reference_id' => $withdraw->uuid,
                ]);

            if ($response->successful()) {
                $payout = $response->json();
                $withdraw->remoteId = $payout['id'] ?? null;
                $withdraw->status = WithdrawStatus::PROCESSING;
                $withdraw->data = $payout;
                $withdraw->save();
            } else {
                Log::error('Razorpay payout failed', [
                    'withdraw_id' => $withdraw->id,
                    'response' => $response->json(),
                ]);
            }
        }
    }

    public function returned(Request $request, Deposit $deposit)
    {
        $paymentId = $request->input('razorpay_payment_id');
        $orderId = $request->input('razorpay_order_id');
        $signature = $request->input('razorpay_signature');

        if ($paymentId && $orderId && $signature) {
            $config = $this->getConfig();
            $keySecret = $config->get('key_secret') ?? $this->key_secret;
            $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);

            if (hash_equals($expectedSignature, $signature)) {
                (new DepositTx)($deposit);
                return redirect()->route('deposits.create')->with('success', 'Deposit successful!');
            }
        }

        return redirect()->route('deposits.create')->with('error', 'Payment verification failed');
    }

    public function updateWithdrawStatus(Withdraw $withdraw): bool
    {
        if (!$withdraw->remoteId) return false;

        $config = $this->getConfig();
        $keyId = $config->get('key_id') ?? $this->key_id;
        $keySecret = $config->get('key_secret') ?? $this->key_secret;

        $response = Http::withBasicAuth($keyId, $keySecret)
            ->get($this->baseUrl . '/payouts/' . $withdraw->remoteId);

        if ($response->successful()) {
            $payout = $response->json();
            $status = $payout['status'] ?? 'pending';

            if ($status === 'processed') {
                $withdraw->status = WithdrawStatus::COMPLETE;
                $withdraw->save();
                return true;
            } elseif (in_array($status, ['reversed', 'failed', 'cancelled'])) {
                $withdraw->status = WithdrawStatus::FAILED;
                $withdraw->save();
                return true;
            }
        }

        return false;
    }
}
