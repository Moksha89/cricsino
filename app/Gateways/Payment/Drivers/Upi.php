<?php

namespace App\Gateways\Payment\Drivers;

use App\Enums\DepositStatus;
use App\Gateways\Payment\Actions\DepositTx;
use App\Gateways\Payment\Contracts\Provider;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class Upi implements Provider
{
    public $name = 'upi';

    public function __construct() {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getConfig(): Collection
    {
        $config = settings()->for('upi');
        if ($config->count() == 0) {
            settings()->set('upi.name', 'UPI Payment');
            settings()->set('upi.logo', '/images/gateways/upi.png');
            settings()->set('upi.enable_withdraw', 'true');
            settings()->set('upi.auto_approve_withdraw_address', 'false');
            settings()->set('upi.enable_deposit', 'true');
            settings()->set('upi.merchant_upi_id', null);
            settings()->set('upi.merchant_name', null);
            settings()->set('upi.qr_code_image', null);
            settings()->set('upi.max_withdraw_limit', 200000);
            settings()->set('upi.min_withdraw_limit', 100);
            $config = settings()->for('upi');
        }
        return $config;
    }

    public function setConfig(Request $request): Collection
    {
        $request->validate([
            'name' => 'required|string',
            'merchant_upi_id' => 'nullable|string',
            'merchant_name' => 'nullable|string',
            'enable_withdraw' => 'required|string',
            'enable_deposit' => 'required|string',
            'max_withdraw_limit' => 'required|numeric',
            'min_withdraw_limit' => 'required|numeric',
        ]);

        settings()->set('upi.name', $request->name);
        settings()->set('upi.merchant_upi_id', $request->merchant_upi_id);
        settings()->set('upi.merchant_name', $request->merchant_name);
        settings()->set('upi.enable_withdraw', $request->enable_withdraw);
        settings()->set('upi.enable_deposit', $request->enable_deposit);
        settings()->set('upi.max_withdraw_limit', $request->max_withdraw_limit);
        settings()->set('upi.min_withdraw_limit', $request->min_withdraw_limit);

        return settings()->for('upi');
    }

    public function updateCurrencies()
    {
        Currency::updateOrCreate(
            ['code' => 'INR', 'gateway' => 'upi'],
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
        $upiId = $config->get('merchant_upi_id');
        $merchantName = $config->get('merchant_name') ?? 'BetRiver';
        $amount = $deposit->gateway_amount;

        $upiLink = "upi://pay?pa={$upiId}&pn={$merchantName}&am={$amount}&cu=INR&tn=Deposit-{$deposit->uuid}";

        return Inertia::render('Deposits/Upi', [
            'deposit' => $deposit,
            'upiId' => $upiId,
            'upiLink' => $upiLink,
            'amount' => $amount,
            'merchantName' => $merchantName,
            'qrCodeImage' => $config->get('qr_code_image'),
        ]);
    }

    public function checkDepositStatus(Deposit $deposit)
    {
        return null;
    }

    public function webhook(Request $request, string $type = 'deposit')
    {
        return response('OK', 200);
    }

    public function withdraw(Collection $withdraws)
    {
        // Manual withdrawal - admin reviews and sends UPI payment manually
    }

    public function returned(Request $request, Deposit $deposit)
    {
        return redirect()->route('deposits.create')
            ->with('info', 'Please wait for admin to confirm your payment.');
    }

    public function updateWithdrawStatus(Withdraw $withdraw): bool
    {
        return false;
    }
}
