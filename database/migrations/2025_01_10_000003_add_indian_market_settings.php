<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add WhatsApp settings
        $settings = [
            ['key' => 'site.whatsapp_enabled', 'value' => 'true'],
            ['key' => 'site.whatsapp_number', 'value' => ''],
            ['key' => 'site.whatsapp_message', 'value' => 'Hi, I need help with my betting account.'],
            // Update default currency to INR
            ['key' => 'site.currency_code', 'value' => 'INR'],
            ['key' => 'site.currency_symbol', 'value' => '₹'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('key', [
            'site.whatsapp_enabled',
            'site.whatsapp_number',
            'site.whatsapp_message',
        ])->delete();

        // Revert currency
        DB::table('settings')->where('key', 'site.currency_code')->update(['value' => 'USD']);
        DB::table('settings')->where('key', 'site.currency_symbol')->update(['value' => '$']);
    }
};
