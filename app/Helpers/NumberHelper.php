<?php

namespace App\Helpers;

class NumberHelper
{
    /**
     * Format number to Indonesian currency format with appropriate units
     *
     * @param float|int $number
     * @param int $decimals
     * @param string $currency
     * @return string
     */
    public static function formatCurrency($number, $decimals = 0, $currency = 'Rp')
    {
        if ($number == 0) {
            return $currency . ' 0';
        }

        $absNumber = abs($number);
        $sign = $number < 0 ? '-' : '';

        if ($absNumber >= 1000000000000) {
            // Triliun
            $value = $absNumber / 1000000000000;
            $formatted = number_format($value, max($decimals, 1), '.', '');
            $formatted = rtrim(rtrim($formatted, '0'), '.');
            return $sign . $currency . ' ' . $formatted . 't';
        } elseif ($absNumber >= 1000000000) {
            // Miliar
            $value = $absNumber / 1000000000;
            $formatted = number_format($value, max($decimals, 1), '.', '');
            $formatted = rtrim(rtrim($formatted, '0'), '.');
            return $sign . $currency . ' ' . $formatted . 'm';
        } elseif ($absNumber >= 1000000) {
            // Juta
            $value = $absNumber / 1000000;
            // Use floor to truncate to 1 decimal place
            $value = floor($value * 10) / 10;
            $formatted = number_format($value, 1, '.', '');
            $formatted = rtrim(rtrim($formatted, '0'), '.');
            return $sign . $currency . ' ' . $formatted . 'jt';
        } elseif ($absNumber >= 1000) {
            // Ribu
            $value = round($absNumber / 1000);
            $formatted = number_format($value, 0, '.', '');
            return $sign . $currency . ' ' . $formatted . 'rb';
        } else {
            // Kurang dari ribu
            $formatted = number_format($absNumber, $decimals, '.', '.');
            return $sign . $currency . ' ' . $formatted;
        }
    }

    /**
     * Format number to Indonesian format with appropriate units (without currency)
     *
     * @param float|int $number
     * @param int $decimals
     * @return string
     */
    public static function formatNumber($number, $decimals = 0)
    {
        if ($number == 0) {
            return '0';
        }

        $absNumber = abs($number);
        $sign = $number < 0 ? '-' : '';

        if ($absNumber >= 1000000000000) {
            // Triliun
            $value = $absNumber / 1000000000000;
            $formatted = number_format($value, max($decimals, 1), '.', '');
            $formatted = rtrim(rtrim($formatted, '0'), '.');
            return $sign . $formatted . 't';
        } elseif ($absNumber >= 1000000000) {
            // Miliar
            $value = $absNumber / 1000000000;
            $formatted = number_format($value, max($decimals, 1), '.', '');
            $formatted = rtrim(rtrim($formatted, '0'), '.');
            return $sign . $formatted . 'm';
        } elseif ($absNumber >= 1000000) {
            // Juta
            $value = $absNumber / 1000000;
            // Use floor to truncate to 1 decimal place
            $value = floor($value * 10) / 10;
            $formatted = number_format($value, 1, '.', '');
            $formatted = rtrim(rtrim($formatted, '0'), '.');
            return $sign . $formatted . 'jt';
        } elseif ($absNumber >= 1000) {
            // Ribu
            $value = round($absNumber / 1000);
            $formatted = number_format($value, 0, '.', '');
            return $sign . $formatted . 'rb';
        } else {
            // Kurang dari ribu
            $formatted = number_format($absNumber, $decimals, '.', '.');
            return $sign . $formatted;
        }
    }

    /**
     * Format number to Indonesian format with full text units
     *
     * @param float|int $number
     * @param int $decimals
     * @param string $currency
     * @return string
     */
    public static function formatCurrencyFull($number, $decimals = 0, $currency = 'Rp')
    {
        if ($number == 0) {
            return $currency . ' 0';
        }

        $absNumber = abs($number);
        $sign = $number < 0 ? '-' : '';

        if ($absNumber >= 1000000000000) {
            // Triliun
            $value = $absNumber / 1000000000000;
            $formatted = number_format($value, $decimals, ',', '.');
            return $sign . $currency . ' ' . $formatted . ' Triliun';
        } elseif ($absNumber >= 1000000000) {
            // Miliar
            $value = $absNumber / 1000000000;
            $formatted = number_format($value, $decimals, ',', '.');
            return $sign . $currency . ' ' . $formatted . ' Miliar';
        } elseif ($absNumber >= 1000000) {
            // Juta
            $value = $absNumber / 1000000;
            $formatted = number_format($value, $decimals, ',', '.');
            return $sign . $currency . ' ' . $formatted . ' Juta';
        } elseif ($absNumber >= 1000) {
            // Ribu
            $value = round($absNumber / 1000);
            $formatted = number_format($value, 0, ',', '.');
            return $sign . $currency . ' ' . $formatted . ' Ribu';
        } else {
            // Kurang dari ribu
            $formatted = number_format($absNumber, $decimals, ',', '.');
            return $sign . $currency . ' ' . $formatted;
        }
    }

    /**
     * Get the appropriate unit for a number
     *
     * @param float|int $number
     * @return string
     */
    public static function getUnit($number)
    {
        $absNumber = abs($number);

        if ($absNumber >= 1000000000000) {
            return 'T'; // Triliun
        } elseif ($absNumber >= 1000000000) {
            return 'M'; // Miliar
        } elseif ($absNumber >= 1000000) {
            return 'jt'; // Juta
        } elseif ($absNumber >= 1000) {
            return 'rb'; // Ribu
        } else {
            return ''; // No unit
        }
    }

    /**
     * Get the full text unit for a number
     *
     * @param float|int $number
     * @return string
     */
    public static function getUnitFull($number)
    {
        $absNumber = abs($number);

        if ($absNumber >= 1000000000000) {
            return 'Triliun';
        } elseif ($absNumber >= 1000000000) {
            return 'Miliar';
        } elseif ($absNumber >= 1000000) {
            return 'Juta';
        } elseif ($absNumber >= 1000) {
            return 'Ribu';
        } else {
            return '';
        }
    }
}
