<?php

namespace App\Classes;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;

class CurrencyQuoteRate {

    /**
     * 
     * @var string
     */
    protected static $currency;

    /**
     * 
     * @var int
     */
    protected static $id_from;

    /**
     * 
     * @var int
     */
    protected static $id_to;

    /**
     * 
     * @var double
     */
    protected static $bid;

    /**
     * 
     * @var double
     */

    protected static $ask;
    /**
     * 
     * @var double
     */
    protected static $aux_bid;

    /**
     * 
     * @var double
     */
    protected static $aux_ask;

    /**
     * Calculate the Rate Currency Quotes that does not exist yet on the external API
     * @param mixed $currency 
     * @return array|false 
     * @throws BindingResolutionException 
     */
    public static function rate($currency)
    {
        static::$currency = $currency;
        if (static::getReverseCurrencyQuote()) {
            static::calculateBuyQuote();
            static::calculateSellQuote();
            return static::fillDataToSave();
        }
        return false;
    }

    /**
     * Get reverse currency quote to make the calculation of the new currency quote
     * @return bool 
     * @throws BindingResolutionException 
     */
    protected static function getReverseCurrencyQuote() {
        preg_match_all('/[A-Z]+/', static::$currency, $currencies);
        $currency_quotes = app('\App\Http\Controllers\api\CurrencyQuotesController')->getCurrencyQuote($currencies[0][1], $currencies[0][0]);

        if (count($currency_quotes)) {
            static::$id_from = $currency_quotes['id_to'];
            static::$id_to = $currency_quotes['id_from'];
            static::$aux_bid = $currency_quotes['bid'];
            static::$aux_ask = $currency_quotes['ask'];
            return true;
        }
        return false;
    }

    protected static function calculateBuyQuote() {
        static::$bid = 1/static::$aux_bid;
        static::$bid = sprintf("%f", static::$bid);
    }

    protected static function calculateSellQuote() {
        static::$ask = 1/static::$aux_ask;
        static::$ask = sprintf("%f", static::$ask);
    }

    /**
     * Return data to save on the database
     * @return array
     */
    protected static function fillDataToSave() {
        return [
            "id_from" => static::$id_from,
            "id_to" => static::$id_to,
            "bid" => static::$bid,
            "ask" => static::$ask
        ];
    }

}