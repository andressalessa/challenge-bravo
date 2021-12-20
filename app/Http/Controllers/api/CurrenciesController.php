<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Currencies;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CurrenciesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $currencies = json_response(Currencies::all())->getData();

        if ( empty($currencies)) {
            throw new ModelNotFoundException("Currency list not found!");
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
            'code' => 'required'
        ]);

        $currency = Currencies::create($request->all());
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
        if(!($currency = Currencies::find($id))){
            throw new ModelNotFoundException("Currency not found!");
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
        if( !($currency = Currencies::find($id)) ) {
            throw new ModelNotFoundException("Currency not found!");
        }

        $request->validate([
            'code' => 'required',
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
        if( !($currency = Currencies::find($id)) ) {
            throw new ModelNotFoundException("Currency not found!");
        }

        $currency->delete();
        return json_response("", 204);
    }

    /**
     * Combine all possibilities of currencies pair based on the default list of currencies
     * 
     * @return array
     */
    public static function combineAllCurrenciesPairPossible() {
        $currencies = json_response(Currencies::all())->getData();

        if ( empty($currencies)) {
            throw new ModelNotFoundException("Currency list not found!");
        }

        $all_combination = [];

        foreach ($currencies as $currency) {
            $code = $currency->code;
            foreach ($currencies as $aux_currency) {
                $aux_code = $aux_currency->code;
                if ($aux_code != $code) {
                    $all_combination[] = "$code-$aux_code";
                }
            }
        }

        return $all_combination;
    }
}
