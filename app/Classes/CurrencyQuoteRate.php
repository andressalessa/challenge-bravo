<?php

use App\Models\CurrencyQuotes;
use Illuminate\Http\Request;

class CurrencyQuoteRate {

    /**
     * 
     * @var string
     */
    protected static $currency;

    /**
     * 
     * @var string
     */
    protected static $code;

    /**
     * 
     * @var string
     */
    protected static $codein;

    /**
     * "code" => $currency['code'],
        "codein" => $currency['codein'],
        "bid" => $currency['bid'],
        "ask" => $currency['ask']
     */

    /**
     * Rate the Currency Quotes that does not exist yet on the external API
     * @param string $currency 
     * @return Request $request
     */
    public static function rate($currency)
    {
        static::$currency = $currency;

        return [];
    }

    protected static function getExistentReverseCurrencyQuote() 
    {
        preg_match_all('/[A-Z]+/', static::$currency, $currencies);

        $currency_quotes = new CurrencyQuotes();
        $existent_quote = $currency_quotes
                            ->select('code', 'codein', 'bid', 'ask')
                            ->where('code', $currencies[1])
                            ->where('codein', $currencies[0])->get();

        dd($existent_quote);

    }

    // protected static function 
}