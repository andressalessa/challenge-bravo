<?php

namespace Database\Seeders;

use App\Http\Controllers\CurrencyRatesController;
use App\Models\CurrencyQuotes;
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

        foreach ($currencies_quote as $currencies) {
            CurrencyQuotes::create($currencies);
        }
    }
}
