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
        $request->validate([
            'code' => 'required',
            'codein' => 'required',
            'bid' => 'required',
            'ask' => 'required',
        ]);

        dd($request->all());

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

    public function getCurrencyQuote($currency) {
        preg_match_all('/[A-Z]+/', $currency, $currencies);

        $code = $currencies[0];
        $codein = $currencies[1];

        CurrencyQuotes::where('code', $code)
                        ->where('codein', $codein)
                        ->get();

    }
}
