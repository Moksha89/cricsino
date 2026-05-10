<?php

namespace App\Gateways\Payment\Drivers;

use App\Gateways\Payment\Contracts\Provider;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class BankTransfer implements Provider
{
    public $name = 'bank_transfer';

    public function __construct() {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getConfig(): Collection
    {
        $config = settings()->for('bank_transfer');
        if ($config->count() == 0) {
            settings()->set('bank_transfer.name', 'Bank Transfer');
            settings()->set('bank_transfer.logo', '/images/gateways/bank.png');
            settings()->set('bank_transfer.enable_withdraw', 'true');
            settings()->set('bank_transfer.auto_approve_withdraw_address', 'false');
            settings()->set('bank_transfer.enable_deposit', 'true');
            settings()->set('bank_transfer.bank_name', null);
            settings()->set('bank_transfer.account_name', null);
            settings()->set('bank_transfer.account_number', null);
            settings()->set('bank_transfer.ifsc_code', null);
            settings()->set('bank_transfer.branch', null);
            settings()->set('bank_transfer.max_withdraw_limit', 1000000);
            settings()->set('bank_transfer.min_withdraw_limit', 500);
            $config = settings()->for('bank_transfer');
        }
        return $config;
    }

    public function setConfig(Request $request): Collection
    {
        $request->validate([
            'name' => 'required|string',
            'bank_name' => 'nullable|string',
            'account_name' => 'nullable|string',
            'account_number' => 'nullable|string',
            'ifsc_code' => 'nullable|string',
            'branch' => 'nullable|string',
            'enable_withdraw' => 'required|string',
            'enable_deposit' => 'required|string',
            'max_withdraw_limit' => 'required|numeric',
            'min_withdraw_limit' => 'required|numeric',
        ]);

        settings()->set('bank_transfer.name', $request->name);
        settings()->set('bank_transfer.bank_name', $request->bank_name);
        settings()->set('bank_transfer.account_name', $request->account_name);
        settings()->set('bank_transfer.account_number', $request->account_number);
        settings()->set('bank_transfer.ifsc_code', $request->ifsc_code);
        settings()->set('bank_transfer.branch', $request->branch);
        settings()->set('bank_transfer.enable_withdraw', $request->enable_withdraw);
        settings()->set('bank_transfer.enable_deposit', $request->enable_deposit);
        settings()->set('bank_transfer.max_withdraw_limit', $request->max_withdraw_limit);
        settings()->set('bank_transfer.min_withdraw_limit', $request->min_withdraw_limit);

        return settings()->for('bank_transfer');
    }

    public function updateCurrencies()
    {
        Currency::updateOrCreate(
            ['code' => 'INR', 'gateway' => 'bank_transfer'],
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

        return Inertia::render('Deposits/BankTransfer', [
            'deposit' => $deposit,
            'bankName' => $config->get('bank_name'),
            'accountName' => $config->get('account_name'),
            'accountNumber' => $config->get('account_number'),
            'ifscCode' => $config->get('ifsc_code'),
            'branch' => $config->get('branch'),
            'amount' => $deposit->gateway_amount,
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
        // Manual withdrawal via bank transfer
    }

    public function returned(Request $request, Deposit $deposit)
    {
        return redirect()->route('deposits.create')
            ->with('info', 'Please upload your payment receipt. Admin will verify and confirm.');
    }

    public function updateWithdrawStatus(Withdraw $withdraw): bool
    {
        return false;
    }
}
