<?php

namespace Database\Seeders\Tk;

use App\Enums\Pek\PekUrlType;
use App\Models\Location;
use App\Models\Tk\TerminalPek;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class TerminalPekSeeder extends Seeder
{
    private array $candidatsToUpdate = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = config('companies.pek.url');
        $user = config('companies.pek.user');
        $password = config('companies.pek.password');

        $response = Http::withBasicAuth($user, $password)->post($url . PekUrlType::Terminals->value);

        foreach ($response->object()->branches as $location) {

            foreach ($location->cities as $city) {

                foreach ($location->divisions as $division) {

                    if ($division->cityId === $city->cityId) {

                        foreach ($division->warehouses as $warehouse) {

                            $locationModel = Location::query()
                                ->where('name', $this->cleanCityName($city->title))
                                ->whereHas('country', function ($query) use ($location) {
                                    $query->where('code', $location->countryOfRegistrationCode);
                                })->first();

                            // если локация не обнаружена, то она попадает в список кандидатов на парсинг
                            if (!$locationModel) {
                                $this->candidatsToUpdate[] = $city->title . ': ' . $location->address;
                                continue;
                            }

                            TerminalPek::create([
                                'location_id' => $locationModel->id,
                                'identifier' => $warehouse->id,
                                'name' => $this->cleanCityName($city->title),
                                'dirty' => $location->address,
                                'max_weight' => $warehouse->maxWeight,
                                'max_volume' => $warehouse->maxVolume,
                                'max_weight_per_place' => $warehouse->maxWeightPerPlace,
                                'max_dimension' => $warehouse->maxDimension,
                            ]);
                        }
                    }
                }
            }
        }

        dump('Следующие локации остались не добавленными: ', $this->candidatsToUpdate);
    }

    /**
     * Возвращает чистое название населённого пункта, без указания региона в скобочках.
     */
    private function cleanCityName(string $name): string
    {
        $withouthRegion = strstr($name, '(', true);

        if ($withouthRegion) {
            return trim($withouthRegion);
        }

        return trim($name);
    }
}
