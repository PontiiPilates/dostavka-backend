<?php

namespace Database\Seeders\Tk;

use App\Enums\DPD\DpdFileType;
use App\Models\Country;
use App\Models\Location;
use App\Models\Region;
use App\Models\Tk\TerminalDpd;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class TerminalDpdSeeder extends Seeder
{
    public function run(): void
    {
        // особенностью данной тк является указание регионов, в которых отсутствует принадлежность к краю, области, республике и т.д.
        // зато есть код самого региона
        // что касается списка, то он вполне ёмкий и системмный

        // города с доставкой наложенным платежом
        $dataCitiesCashPay = Storage::json(DpdFileType::CitiesCashPay->value);

        // пункты выдачи с информацией об ограничениях
        $dataParcelShops = Storage::json(DpdFileType::ParcelShops->value);

        // пункты выдачи без ограничений по габаритам
        $dataTerminalsSelfDelivery2 = Storage::json(DpdFileType::TerminalsSelfDelivery2->value);

        // засев данными
        $this->seeding($dataCitiesCashPay);
    }

    private function seeding($data): void
    {
        $count = 0;
        foreach ($data['return'] as $city) {
            $city = (object) $city;

            // обрабатывает ситуацию, когда код региона представлен одним знаком
            // такой код региона должен быть указан с нулём в качестве первого знака
            strlen($city->regionCode) == 1
                ? $regionCode = '0' . $city->regionCode
                : $regionCode = $city->regionCode;

            // поиск локации в базе данных
            $location = Location::query()
                ->where('name', $city->cityName)
                ->whereHas('region', function ($query) use ($regionCode) {
                    $query->where('code', $regionCode);
                })
                ->whereHas('country', function ($query) use ($city) {
                    $query->where('alpha2', $city->countryCode);
                })->first();

            // если локация не обнаружена, то происходит ее добавление и добавление терминала
            if (!$location) {
                $location = $this->createLocation($city, $regionCode);
                $this->createTerminal($city, $regionCode, $location);

                $count++;
                continue;
            }

            // если локация обнаружена, то проиисходит добавление терминала и обновление индексов
            $this->createTerminal($city, $regionCode, $location);

            $location->update([
                'index_min' => isset($city->indexMin) && !empty($city->indexMin) ? $city->indexMin : null,
                'index_max' => isset($city->indexMax) && !empty($city->indexMax) ? $city->indexMax : null,
            ]);

            $count++;
        }
        dump("Успешно обработано $count записей");
    }

    private function createLocation($city, $regionCode): Location
    {
        return Location::create(
            [
                'country_id' => Country::select('id')->where('alpha2', $city->countryCode)->first()->id,
                'region_id' => Region::select('id')->where('code', $regionCode)->first()->id,
                'name' => $city->cityName,
                'type' => $city->abbreviation,
                'index_min' => isset($city->indexMin) && !empty($city->indexMin) ? $city->indexMin : null,
                'index_max' => isset($city->indexMax) && !empty($city->indexMax) ? $city->indexMax : null,
            ]
        );
    }

    private function createTerminal($city, $regionCode, $location): void
    {
        TerminalDpd::updateOrCreate(
            ['identifier' => $city->cityId],
            [
                'location_id' => $location->id,
                'identifier' => $city->cityId,
                'name' => $city->cityName,
                'dirty' => $city->abbreviation . '. ' . $city->cityName . ', ' . $city->regionName . ', код региона ' . $regionCode,
            ]
        );
    }
}
