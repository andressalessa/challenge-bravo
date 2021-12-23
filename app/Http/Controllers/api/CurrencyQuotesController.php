<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CurrencyQuotes;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CurrencyQuotesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currencies = json_response(CurrencyQuotes::all())->getData();

        if ( empty($currencies)) {
            throw new ModelNotFoundException("Currency Quote list not found!");
        }
        
        return $currencies;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /**
         * Todo verify how to fix this validation
         */
        // $request->validate([
        //     'id_from' => 'required',
        //     'id_to' => 'required',
        //     'bid' => 'required',
        //     'ask' => 'required',
        // ]);

        

        $currency = CurrencyQuotes::create($request->all());
        return json_response($currency, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if(!($currency = CurrencyQuotes::find($id))){
            throw new ModelNotFoundException("Currency Quote not found!");
        }
        return json_response($currency);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if( !($currency = CurrencyQuotes::find($id)) ) {
            throw new ModelNotFoundException("Currency Quote not found!");
        }

        $request->validate([
            'code' => 'required',
            'codein' => 'required',
            'bid' => 'required',
            'ask' => 'required',
        ]);

        $currency->fill($request->all());
        $currency->save();
        return json_response($currency, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if( !($currency = CurrencyQuotes::find($id)) ) {
            throw new ModelNotFoundException("Currency Quote not found!");
        }

        $currency->delete();
        return json_response("", 204);
    }

    /**
     * Get information of desired currency quote
     * 
     * @param string $from 
     * @param string $to 
     * @return array
     */

    public function getCurrencyQuote(string $from, string $to) {
        $currency_quote = [];
        $collection = CurrencyQuotes::from('currency_quotes as q')
                                            ->join('currencies as cfrom', CurrencyQuotes::raw('q.id_from'), '=', CurrencyQuotes::raw('cfrom.id'))
                                            ->join('currencies as cto', CurrencyQuotes::raw('q.id_to'), '=', CurrencyQuotes::raw('cto.id'))
                                            ->select(CurrencyQuotes::raw('cfrom.id as id_from'), CurrencyQuotes::raw('cto.id as id_to'), CurrencyQuotes::raw('q.bid'), CurrencyQuotes::raw('q.ask'), CurrencyQuotes::raw('cfrom.code as code_from'), CurrencyQuotes::raw('cto.code as code_to'))
                                            ->where(CurrencyQuotes::raw('cfrom.code'), $from)
                                            ->where(CurrencyQuotes::raw('cto.code'), $to)
                                            ->get();
        $collection->each(function ($collection) use (&$currency_quote)
        {
            $currency_quote = $collection->toArray();
        });

        return $currency_quote;
    }
}
