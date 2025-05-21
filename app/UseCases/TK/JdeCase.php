<?php

declare(strict_types=1);

use App\Interfaces\CaseInterface;
use Illuminate\Http\Request;

class JdeCase implements CaseInterface
{
    public function handle(Request $request): array
    {
        // $country = Country::where('code', 112)->first();
        // dd($country->cities()->get()->toArray());

        // $city = City::where('city_name', 'Красноярск')->first();
        // dd($city->country()->first()->toArray());

        // $city = City::where('city_name', 'Красноярск')->first();
        // dd($city->terminalsJde()->first()->toArray());

        // $terminal = TerminalJde::where('city_name', 'Красноярск')->first();
        // dd($terminal->city()->first()->toArray());
        return [];
    }
}
