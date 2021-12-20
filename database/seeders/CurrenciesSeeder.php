<?php

namespace Database\Seeders;

use App\Models\Currencies;
use Illuminate\Database\Seeder;

class CurrenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (config('defaultcurrencies') as $currency) {
            Currencies::create($currency);
        }
    }


}
