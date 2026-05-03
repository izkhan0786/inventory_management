<?php

namespace App\Services;

class CurrencyService
{
    protected static $currencies = [
        'USD' => ['symbol' => '$', 'name' => 'US Dollar'],
        'EUR' => ['symbol' => '€', 'name' => 'Euro'],
        'GBP' => ['symbol' => '£', 'name' => 'British Pound'],
        'CHF' => ['symbol' => 'Fr', 'name' => 'Swiss Franc'],
        'CAD' => ['symbol' => 'C$', 'name' => 'Canadian Dollar'],
        'AUD' => ['symbol' => 'A$', 'name' => 'Australian Dollar'],
        'JPY' => ['symbol' => '¥', 'name' => 'Japanese Yen'],
        'TRY' => ['symbol' => '₺', 'name' => 'Turkish Lira'],
    ];

    public static function getCurrencies()
    {
        return self::$currencies;
    }

    public static function format($amount, $currencyCode = 'USD')
    {
        $symbol = self::$currencies[$currencyCode]['symbol'] ?? '$';
        return $symbol . number_format($amount, 2);
    }
}
