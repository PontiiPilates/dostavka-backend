<?php

namespace Database\Seeders\Tk;

use App\Enums\Boxberry\BoxberryUrlType;
use App\Models\Location;
use App\Models\Tk\TerminalBoxberry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class TerminalBoxberrySeeder extends Seeder
{
    private array $candidatsToUpdate = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = config('companies.boxberry.url');
        $token = config('companies.boxberry.token');

        $response = Http::get($url, ['token' => $token, 'method' => BoxberryUrlType::ListCitiesFull->value]);

        // этот список более чистый чем некоторые другие
        // его можно использовать выше Байкал и Кит
        foreach ($response->object() as $place) {

            $location = Location::query()
                ->where('name', $place->Name)
                ->whereHas('country', function ($query) use ($place) {
                    $query->where('code', $place->CountryCode);
                })->first();

            // если локация не обнаружена, то она попадает в список кандидатов на парсинг
            if (!$location) {
                $this->candidatsToUpdate[] = $place->Name . ': ' . $place->Prefix . ', ' . $place->UniqName;
                continue;
            }

            TerminalBoxberry::create([
                'location_id' => $location->id,
                'identifier' => $place->Code,
                'name' => $place->Name,
                'dirty' => $place->UniqName . ', ' . $place->Region,
            ]);
        }

        dump('Следующие локации остались не добавленными: ', $this->candidatsToUpdate);
    }
}
