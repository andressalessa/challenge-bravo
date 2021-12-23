<?php

namespace Database\Seeders;

use App\Http\Controllers\CurrencyRatesController;
use Illuminate\Database\Seeder;

class CurrencyQuotesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies_quote = new CurrencyRatesController();
        $currencies_quote->saveAllCurrencyQuotes();
    }
}
