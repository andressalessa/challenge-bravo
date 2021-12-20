<?php

namespace App\Http\Controllers;

use App\Http\Controllers\api\CurrenciesController;
use App\Http\ResponseFactory;
use CurrencyQuoteRate;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
     * @return array 
     */
    public function __construct()
    {
        $currencies_pairs = $this->getListOfDefaultCurrenciesPairs();

        $this->getCurrencyQuotes($currencies_pairs);
        return $this->prepareDataToSave();
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
        // dd($this->json_response);
    }

    /**
     * Verify if there is some currency pair that it's not on the API JSON Response
     * In case of not founding, the default currency will be calculated to be saved on data base table
     * @return void 
     */
    protected function validateExistenceOfCurrenciesOnJsonResponse($currencies_pairs) {
        $a_json_response = json_decode($this->json_response);
        if (isset($a_json_response['status']) AND $a_json_response['status'] == 404) {
            preg_match('/[A-Z]+-[A-Z]+/', $a_json_response['message'], $matches);
            $this->currencies_not_found[] = $matches[0];
            $key = array_search($matches[0], $currencies_pairs);
            array_splice($currencies_pairs, $key, 1);
            $this->getCurrencyQuotes($currencies_pairs);
        }
    }

    /**
     * Prepare an array with the fields and values and save on the data base table
     * @return ResponseFactory 
     * @throws BindingResolutionException 
     */
    protected function saveCurrencyQuotesFromApi() 
    {
        $currencies_quote = [];

        $a_response = json_decode($this->json_response);

        foreach ($a_response as $currency) {
            $currencies_quote[] = [
                "code" => $currency['code'],
                "codein" => $currency['codein'],
                "bid" => $currency['bid'],
                "ask" => $currency['ask']
            ];
        }

        $request = new Request($currencies_quote);
        return $this->save($request);
    }

    protected function prepareFieldsCurrencyQuoteNotExistentOnApi()
    {
        /**
         * PAREI AQUI
         * FAZENDO A PARTE QUE VAI CALCULAR AS QUE NÃO EXISTEM
         * VER RETORNO DA CLASSE QUE SERÁ ARRAY
         */
        if (count($this->currencies_not_found) > 0) {
            foreach ($this->currencies_not_found as $new_currency) {
                $currencies_quote[] = CurrencyQuoteRate::rate($new_currency);
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
        $response = app('\App\Http\Controllers\CurrencyQuotesController')->store($request->all());
        return json_response($response, 201);
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
