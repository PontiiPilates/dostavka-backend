<?php

namespace Database\Seeders\Tk;

use App\Enums\Kit\KitUrlType;
use App\Models\Location;
use App\Models\Tk\TerminalKit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class TerminalKitSeeder extends Seeder
{
    private array $candidatsToUpdate = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $url = config('companies.kit.url');
        $token = config('companies.kit.token');

        $response = Http::withToken($token)->get($url . KitUrlType::City->value);

        // особенность данного списка в том, что он содержит дубли
        // поэтому имеет смысл перед добавлением добавить проверку на exists
        // или же сделать updateOrCreate

        foreach ($response->object() as $city) {

            $location = Location::query()
                ->where('name', $city->name)
                ->whereHas('country', function ($query) use ($city) {
                    $query->where('alpha2', $city->country_code);
                })->first();

            // если локация не обнаружена, то она попадает в список кандидатов на парсинг
            if (!$location) {
                $this->candidatsToUpdate[] = $city->name . ': ' . $city->type . ' ' . $city->country_code;
                continue;
            }

            TerminalKit::create([
                'location_id' => $location->id,
                'identifier' => $city->code,
                'name' => $city->name,
                'dirty' => null,
            ]);
        }

        dump('Следующие локации остались не добавленными: ', $this->candidatsToUpdate);
    }
}
