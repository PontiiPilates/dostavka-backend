<?php

namespace Database\Seeders\Tk;

use App\Enums\Dpd\DpdFileType;
use App\Enums\LocationType;
use App\Models\Region;
use App\Models\Tk\TerminalDpd;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TerminalDpdSeeder extends Seeder
{
    private array $disabledTypes = [
        'тер',
        'снт',
        'промзона',
        'кв-л',
        'жилрайон',
        'п/ст',
        'ст',
        'свх',
        'отд',
        'р-н',
        'рзд',
        'у',
        'ж/д_оп',
        'массив',
        'нп',
        'м',
        'п/о',
        'автодорога',
        'ж/д_будка',
        'ж/д_ст',
    ];

    public function run(): void
    {
        // особенности:
        // указание кодов регионов
        // отсутствие принадлежности к районам
        // системный и емкий список

        // города с доставкой наложенным платежом
        $dataCitiesCashPay = Storage::json(DpdFileType::CitiesCashPay->value);

        $iterable = 0;
        $timeStart = Carbon::now();

        TerminalDpd::truncate();

        foreach ($dataCitiesCashPay['return'] as $city) {
            $city = (object) $city;

            // если обнаружена принадлежность к нежелательным типам территорий
            if (in_array($city->abbreviation, $this->disabledTypes)) {
                continue;
            }

            $region = null;
            $federal = false;

            // если обнаружена принадлежность к территиории федерального значения
            if ($city->cityName == 'Санкт-Петербург' || $city->cityName == 'Москва' || $city->cityName == 'Севастополь') {
                $region = $city->cityName;
                $federal = true;
            }

            // обработка ситуации, когда код региона представлен одним знаком
            // такой код должен быть указан с нулём впереди
            strlen($city->regionCode) == 1
                ? $regionCode = '0' . $city->regionCode
                : $regionCode = $city->regionCode;

            // определение территориальной принадлежности
            $region = Region::where('code', $regionCode)->first()->name;

            TerminalDpd::create([
                'identifier' => $city->cityId,
                'name' => $city->cityName,
                'type' => $this->correctorType($city->abbreviation),
                'region' => $region,
                'federal' => $federal,
                'country' => $city->countryCode,
                'index_min' => $city->indexMin ?? null,
                'index_max' => $city->indexMax ?? null,
            ]);

            $iterable++;
        }

        $timeEnd = Carbon::now();
        $executionTime = $timeStart->diffInSeconds($timeEnd);
        $executionTime = number_format((float) $executionTime, 1, '.');

        $this->command->info("Добавлено $iterable терминалов, $executionTime сек.");
    }

    /**
     * Возвращает чистое имя типа.
     */
    private function correctorType(string $type): string
    {
        switch ($type) {
            case 'г':
                return LocationType::Town->value;
            case 'п':
                return LocationType::Township->value;
            case 'рп':
                return LocationType::JobVillage->value;
            case 'с':
                return LocationType::Village->value;
            case 'мкр':
                return LocationType::MicroDistrict->value;
            case 'д':
                return LocationType::Hamlet->value;
            case 'дп':
                return LocationType::CottageVillage->value;
            case 'х':
                return LocationType::Farmstead->value;
            case 'сл':
                return LocationType::Sloboda->value;
            case 'ст-ца':
                return LocationType::Stanitsa->value;
            case 'кп':
                return LocationType::ResortVillage->value;
            case 'с/п':
                return LocationType::RualVillage->value;
            default:
                return $type;
        }
    }
}
