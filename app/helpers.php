<?php

use App\Helpers\NumberHelper;

if (!function_exists('format_currency_id')) {
    /**
     * Format number to Indonesian currency format with appropriate units
     *
     * @param float|int $number
     * @param int $decimals
     * @param string $currency
     * @return string
     */
    function format_currency_id($number, $decimals = 0, $currency = 'Rp')
    {
        return NumberHelper::formatCurrency($number, $decimals, $currency);
    }
}

if (!function_exists('format_number_id')) {
    /**
     * Format number to Indonesian format with appropriate units (without currency)
     *
     * @param float|int $number
     * @param int $decimals
     * @return string
     */
    function format_number_id($number, $decimals = 0)
    {
        return NumberHelper::formatNumber($number, $decimals);
    }
}

if (!function_exists('format_currency_id_full')) {
    /**
     * Format number to Indonesian format with full text units
     *
     * @param float|int $number
     * @param int $decimals
     * @param string $currency
     * @return string
     */
    function format_currency_id_full($number, $decimals = 0, $currency = 'Rp')
    {
        return NumberHelper::formatCurrencyFull($number, $decimals, $currency);
    }
}

if (!function_exists('get_number_unit')) {
    /**
     * Get the appropriate unit for a number
     *
     * @param float|int $number
     * @return string
     */
    function get_number_unit($number)
    {
        return NumberHelper::getUnit($number);
    }
}

if (!function_exists('get_number_unit_full')) {
    /**
     * Get the full text unit for a number
     *
     * @param float|int $number
     * @return string
     */
    function get_number_unit_full($number)
    {
        return NumberHelper::getUnitFull($number);
    }
}
