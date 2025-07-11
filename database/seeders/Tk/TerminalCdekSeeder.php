<?php

namespace Database\Seeders\Tk;

use App\Enums\Cdek\CdekUrlType;
use App\Models\Location;
use App\Models\Tk\TerminalCdek;
use App\Services\Tk\TokenCdekService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class TerminalCdekSeeder extends Seeder
{
    private array $candidatsToUpdate = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tokenCdecService = new TokenCdekService();
        $token = $tokenCdecService->getActualToken();

        $response = Http::withToken($token)->get($tokenCdecService->url . CdekUrlType::Cities->value);

        // этот список очень чистый он должен быть выше прочих
        foreach ($response->object() as $place) {

            $location = Location::query()
                ->where('name', $place->city)
                ->whereHas('country', function ($query) use ($place) {
                    $query->where('alpha2', $place->country_code);
                })->first();

            // если локация не обнаружена, то она попадает в список кандидатов на парсинг
            if (!$location) {
                $this->candidatsToUpdate[] = $place->city . ': ' . ($place->region ?? '') . ', ' . ($place->sub_region ?? '');
                continue;
            }

            TerminalCdek::create([
                'location_id' => $location->id,
                'identifier' => $place->code,
                'name' => $place->city,
                'dirty' => $place->city . ': ' . ($place->region ?? '') . ', ' . ($place->sub_region ?? ''),
            ]);
        }

        dump('Следующие локации остались не добавленными: ', $this->candidatsToUpdate);
    }
}
