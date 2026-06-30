<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class SettingsController extends Controller
{
    private const SETTINGS_KEY = 'sezy_settings';

    public function index()
    {
        $settings = Cache::get(self::SETTINGS_KEY, $this->defaults());

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name' => 'required|string|max:100',
            'site_tagline' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'currency' => 'required|in:FCFA,EUR,USD',
            'timezone' => 'required|string',
            'language' => 'required|in:fr,en',

            'instagram_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
            'tiktok_url' => 'nullable|url|max:255',

            'contact_email' => 'nullable|email|max:255',
            'support_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:30',
            'contact_address' => 'nullable|string|max:255',

            'mtn_momo_enabled' => 'boolean',
            'moov_money_enabled' => 'boolean',
            'wave_enabled' => 'boolean',
            'card_enabled' => 'boolean',
            'paydunya_api_key' => 'nullable|string|max:255',
            'paydunya_mode' => 'required|in:test,live',

            'free_shipping_threshold' => 'nullable|integer|min:0',
            'standard_shipping_fee' => 'nullable|integer|min:0',
            'express_shipping_fee' => 'nullable|integer|min:0',
            'click_collect_enabled' => 'boolean',
            'click_collect_delay_days' => 'nullable|integer|min:1|max:30',

            'notif_new_order' => 'boolean',
            'notif_low_stock' => 'boolean',
            'notif_new_review' => 'boolean',
            'low_stock_threshold' => 'nullable|integer|min:1',
            'admin_notif_email' => 'nullable|email|max:255',
        ]);

        // Merge with existing so we don't lose unset keys
        $existing = Cache::get(self::SETTINGS_KEY, $this->defaults());
        $merged = array_merge($existing, $data);

        Cache::forever(self::SETTINGS_KEY, $merged);

        return back()->with('success', 'Paramètres enregistrés avec succès.');
    }

    private function defaults(): array
    {
        return [
            'site_name' => 'SEZY',
            'site_tagline' => 'La plateforme e-commerce du Bénin',
            'site_description' => 'SEZY - Mode, cosmétiques, alimentation. Paiement Mobile Money.',
            'currency' => 'FCFA',
            'timezone' => 'Africa/Porto-Novo',
            'language' => 'fr',

            'instagram_url' => 'https://instagram.com/sezy',
            'facebook_url' => 'https://facebook.com/sezy',
            'whatsapp_number' => '+22900000000',
            'tiktok_url' => 'https://tiktok.com/@sezy',

            'contact_email' => 'agencesezy@gmail.com',
            'support_email' => 'agencesezy@gmail.com',
            'contact_phone' => '',
            'contact_address' => 'Cotonou, Bénin',

            'mtn_momo_enabled' => true,
            'moov_money_enabled' => true,
            'wave_enabled' => true,
            'card_enabled' => false,
            'paydunya_api_key' => '',
            'paydunya_mode' => 'test',

            'free_shipping_threshold' => 15000,
            'standard_shipping_fee' => 1500,
            'express_shipping_fee' => 3000,
            'click_collect_enabled' => true,
            'click_collect_delay_days' => 7,

            'notif_new_order' => true,
            'notif_low_stock' => true,
            'notif_new_review' => true,
            'low_stock_threshold' => 5,
            'admin_notif_email' => 'agencesezy@gmail.com',
        ];
    }
}
