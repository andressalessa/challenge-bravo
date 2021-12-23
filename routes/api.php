<?php

use Illuminate\Support\Facades\Route;

Route::apiResource('currencies', 'App\Http\Controllers\api\CurrenciesController');
Route::apiResource('currency-quotes', 'App\Http\Controllers\api\CurrencyQuotesController');
Route::get('combine', 'App\Http\Controllers\api\CurrenciesController@combineAllCurrenciesPairPossible');