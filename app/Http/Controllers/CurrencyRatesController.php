<?php

namespace App\Http\Controllers;

use App\Classes\CurrencyQuoteRate;
use App\Http\Controllers\api\CurrenciesController;
use App\Http\ResponseFactory;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

define("URL_CURRENCY_QUOTES_API", "https://economia.awesomeapi.com.br/last/");

/**
 * 
 * @package App\Http\Controllers
 */

class CurrencyRatesController extends Controller
{
    protected $json_response;
    protected $currencies_not_found = [];

    /**
     * 
     * @return void 
     * @throws ModelNotFoundException 
     */
    public function __construct()
    {
        $currencies_pairs = $this->getListOfDefaultCurrenciesPairs();

        $this->getCurrencyQuotes($currencies_pairs);
    }

    /**
     * Access the external API of updated currency quotes
     * @return void 
     * @throws ModelNotFoundException 
     */
    protected function getCurrencyQuotes($currencies_pairs)
    {
        $url = URL_CURRENCY_QUOTES_API.implode(',', $currencies_pairs);
        $this->json_response = Http::get($url);

        $this->validateExistenceOfCurrenciesOnJsonResponse($currencies_pairs);
    }

    /**
     * Verify if there is some currency pair that it's not on the API JSON Response
     * In case of not founding, the default currency will be calculated to be saved on data base table
     * @return void 
     */
    protected function validateExistenceOfCurrenciesOnJsonResponse($currencies_pairs) {
        $a_json_response = json_decode($this->json_response, true);
        if (isset($a_json_response['status']) AND $a_json_response['status'] == 404) {
            preg_match('/[A-Z]+-[A-Z]+/', $a_json_response['message'], $matches);
            $this->currencies_not_found[] = $matches[0];
            $key = array_search($matches[0], $currencies_pairs);
            array_splice($currencies_pairs, $key, 1);
            $this->getCurrencyQuotes($currencies_pairs);
        }
    }

    /**
     * Save all currency quotes on the database 
     * @return void 
     * @throws BindingResolutionException 
     * @throws ModelNotFoundException 
     */
    public function saveAllCurrencyQuotes() {
        $success = $this->saveCurrencyQuotesFromApi();

        if ($success === true) 
            $this->saveCurrencyQuoteNotExistentOnApi();
    }

    /**
     * Prepare an array with the fields and values and save on the data base table (from the API)
     * @return void 
     * @throws BindingResolutionException 
     * @throws ModelNotFoundException 
     */
    protected function saveCurrencyQuotesFromApi() 
    {
        $a_response = json_decode($this->json_response, true);

        foreach ($a_response as $currency) {
            $keys = CurrenciesController::getCurrenciesPrimaryKey(array($currency['code'], $currency['codein']));

            $currencies_quote = [
                "id_from" => $keys[$currency['code']],
                "id_to" => $keys[$currency['codein']],
                "bid" => $currency['bid'],
                "ask" => $currency['ask']
            ];

            $request = new Request($currencies_quote);
            $response = $this->save($request);

            if ($response->getStatusCode() != 201 and $response->getStatusCode() != 200) {
                Log::error($response);
                throw new ModelNotFoundException("Something went wrong! Look out the laravel log to see more information!");
            }
        }
        return true;
    }

    /**
     * Prepare an array with the fields and values and save on the data base table (that doesn't exist on the API)
     * @return void 
     * @throws BindingResolutionException 
     * @throws ModelNotFoundException 
     */
    protected function saveCurrencyQuoteNotExistentOnApi()
    {
        if (count($this->currencies_not_found) > 0) {
            foreach ($this->currencies_not_found as $new_currency) {
                $currencies_quote = CurrencyQuoteRate::rate($new_currency);
                if ($currencies_quote) {
                    $request = new Request($currencies_quote);
                    $response = $this->save($request);
                    if ($response->getStatusCode() != 201 and $response->getStatusCode() != 200) {
                        Log::error($response);
                        throw new ModelNotFoundException("Something went wrong! Look out the laravel log to see more information!");
                    }
                    Log::debug($request);
                }
            }
        }
    }

    /**
     * Save the currencies on the CurrencyQuotes table
     * @param Request $request 
     * @return ResponseFactory 
     * @throws BindingResolutionException 
     */
    protected function save(Request $request) 
    {
        $response = app('\App\Http\Controllers\api\CurrencyQuotesController')->store($request);
        return $response;
    }

    /**
     * Pair up all the possibilities of combinations from the default currencies list
     * @return string 
     * @throws ModelNotFoundException 
     */
    protected function getListOfDefaultCurrenciesPairs() {
        $currencies_pairs = CurrenciesController::combineAllCurrenciesPairPossible();

        return $currencies_pairs;
    }
}
