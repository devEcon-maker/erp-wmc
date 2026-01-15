<?php

namespace App\Helpers;

class NumberToWords
{
    private static array $units = [
        '', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
        'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept', 'dix-huit', 'dix-neuf'
    ];

    private static array $tens = [
        '', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante', 'soixante', 'quatre-vingt', 'quatre-vingt'
    ];

    /**
     * Convert a number to words in French
     */
    public static function convert(float|int $number, string $currency = 'FCFA'): string
    {
        if ($number == 0) {
            return 'zéro';
        }

        $number = abs(intval($number));

        if ($number >= 1000000000000) {
            return 'nombre trop grand';
        }

        $words = self::convertToWords($number);

        return trim($words);
    }

    /**
     * Convert number to French words recursively
     */
    private static function convertToWords(int $number): string
    {
        if ($number < 0) {
            return 'moins ' . self::convertToWords(abs($number));
        }

        if ($number < 20) {
            return self::$units[$number];
        }

        if ($number < 100) {
            return self::convertTens($number);
        }

        if ($number < 1000) {
            return self::convertHundreds($number);
        }

        if ($number < 1000000) {
            return self::convertThousands($number);
        }

        if ($number < 1000000000) {
            return self::convertMillions($number);
        }

        return self::convertBillions($number);
    }

    /**
     * Convert tens (20-99)
     */
    private static function convertTens(int $number): string
    {
        $ten = intval($number / 10);
        $unit = $number % 10;

        // Special cases for French (70-79, 90-99)
        if ($ten == 7 || $ten == 9) {
            $unit += 10;
        }

        $result = self::$tens[$ten];

        if ($unit > 0) {
            // "et" is used for 21, 31, 41, 51, 61, 71
            if ($unit == 1 && $ten != 8 && $ten != 9) {
                $result .= ' et ' . self::$units[$unit];
            } else {
                $separator = ($ten == 8 && $unit == 0) ? '' : '-';
                $result .= $separator . self::$units[$unit];
            }
        }

        // Plural for "quatre-vingt" when alone
        if ($ten == 8 && $unit == 0) {
            $result .= 's';
        }

        return $result;
    }

    /**
     * Convert hundreds (100-999)
     */
    private static function convertHundreds(int $number): string
    {
        $hundred = intval($number / 100);
        $remainder = $number % 100;

        $result = '';

        if ($hundred == 1) {
            $result = 'cent';
        } else {
            $result = self::$units[$hundred] . ' cent';
        }

        // Plural for "cents" when alone
        if ($remainder == 0 && $hundred > 1) {
            $result .= 's';
        } elseif ($remainder > 0) {
            $result .= ' ' . self::convertToWords($remainder);
        }

        return $result;
    }

    /**
     * Convert thousands (1000-999999)
     */
    private static function convertThousands(int $number): string
    {
        $thousand = intval($number / 1000);
        $remainder = $number % 1000;

        $result = '';

        if ($thousand == 1) {
            $result = 'mille';
        } else {
            $result = self::convertToWords($thousand) . ' mille';
        }

        if ($remainder > 0) {
            $result .= ' ' . self::convertToWords($remainder);
        }

        return $result;
    }

    /**
     * Convert millions (1000000-999999999)
     */
    private static function convertMillions(int $number): string
    {
        $million = intval($number / 1000000);
        $remainder = $number % 1000000;

        $result = self::convertToWords($million) . ' million';

        // Plural for "millions"
        if ($million > 1) {
            $result .= 's';
        }

        if ($remainder > 0) {
            $result .= ' ' . self::convertToWords($remainder);
        }

        return $result;
    }

    /**
     * Convert billions (1000000000-999999999999)
     */
    private static function convertBillions(int $number): string
    {
        $billion = intval($number / 1000000000);
        $remainder = $number % 1000000000;

        $result = self::convertToWords($billion) . ' milliard';

        // Plural for "milliards"
        if ($billion > 1) {
            $result .= 's';
        }

        if ($remainder > 0) {
            $result .= ' ' . self::convertToWords($remainder);
        }

        return $result;
    }

    /**
     * Convert with currency name
     */
    public static function convertWithCurrency(float|int $number, string $currency = 'FCFA'): string
    {
        $words = self::convert($number);
        return ucfirst($words) . ' (' . number_format($number, 0, ',', ' ') . ') ' . $currency;
    }
}
