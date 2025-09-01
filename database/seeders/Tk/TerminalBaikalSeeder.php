<?php

namespace Database\Seeders\Tk;

use App\Enums\Baikal\BaikalUrlType;
use App\Models\Location;
use App\Models\Tk\TerminalBaikal;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class TerminalBaikalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $username = config('companies.baikal.username');
        $url = config('companies.baikal.url') . BaikalUrlType::Affiliate->value;

        $response = Http::withBasicAuth($username, '')->get($url);

        $iterable = 0;
        $timeStart = Carbon::now();

        foreach ($response->object() as $fillial) {

            // проблема данных Байкал сервис в том, что они не содержат принадлежности к стране
            try {
                $country = Location::where('name', $fillial->name)->first()->country()->first()->alpha2;
            } catch (\Throwable $th) {
                $places = [
                    'Пушкино' => 'RU',
                    'Актау' => 'KZ',
                    'Кызылорда' => 'KZ',
                    'Жезказган' => 'KZ',
                    'Новомосковск' => 'RU',
                    'Железнодорожный' => 'RU',
                    'Томилино' => 'RU',
                    'Одинцово' => 'RU',
                    'Зеленоград' => 'RU',
                    'Великие Луки' => 'RU',
                    'Кокшетау' => 'KZ',
                    'Балашиха' => 'RU',
                    'Балхаш' => 'KZ',
                ];

                $country = $places[$fillial->name];
            }

            $region = null;
            $district = null;
            $type = null;
            $federal = false;

            if ($fillial->name == 'Санкт-Петербург' || $fillial->name == 'Москва' || $fillial->name == 'Севастополь') {
                $region = $fillial->name;
                $federal = true;
            }

            TerminalBaikal::create([
                'identifier' => $fillial->guid,
                'name' => $fillial->name,
                'type' => $type,
                'district' => $district,
                'region' => $region,
                'federal' => $federal,
                'country' => $country ?? null,
            ]);

            $iterable++;
        }

        $timeEnd = Carbon::now();
        $executionTime = $timeStart->diffInSeconds($timeEnd);
        $executionTime = number_format((float) $executionTime, 1, '.');

        $this->command->info("Добавлено $iterable терминалов, $executionTime сек.");
    }
}
