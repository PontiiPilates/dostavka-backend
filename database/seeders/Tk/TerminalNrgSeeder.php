<?php

namespace Database\Seeders\Tk;

use App\Enums\Nrg\NrgUrlType;
use App\Models\Location;
use App\Models\Tk\TerminalNrg;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class TerminalNrgSeeder extends Seeder
{
    private array $countryCodes = [
        0 => "RU",
        -1 => "RU",
        86015311036992 => "RU",
        86015311037009 => "KZ",
        86015311037004 => "KG",
        86015311036993 => "BY",
        86015311037003 => "AM",
        86015311037018 => "RU",
    ];

    private array $candidatsToUpdate = [];

    public function run(): void
    {
        $url = config('companies.nrg.url') . NrgUrlType::Cities->value;
        $token = config('companies.nrg.token');

        $response = Http::withHeaders(['NrgApi-DevToken' => $token])->get($url);

        foreach ($response->object()->cityList as $city) {

            $location = Location::query()
                ->where('name', $city->name)
                ->whereHas('country', function ($query) use ($city) {
                    $query->where('alpha2', $this->countryCodes[$city->idCountry]);
                })->first();

            // если локация не обнаружена, то она попадает в список кандидатов на парсинг
            if (!$location) {
                $this->candidatsToUpdate[] = $city->name . ': ' . $city->description;
                continue;
            }

            TerminalNrg::create([
                'location_id' => $location->id,
                'identifier' => $city->id,
                'name' => $city->name,
                'dirty' => $city->description,
            ]);
        }

        dump('Следующие локации остались не добавленными: ', $this->candidatsToUpdate);
    }
}
