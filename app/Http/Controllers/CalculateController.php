<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Pochta\PochtaTariffType;
use App\Models\Company;
use App\UseCases\TK\BaikalsrCase;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CalculateController extends Controller
{
    public function handle(Request $request)
    {

        $selectedFrom = $request->from;
        $selectedTo = $request->to;
        $selectedCompanies = $request->companies;
        $selectedRegimes = $request->regimes;
        $selectedPlaces = $request->places;
        $selectedSumoc = $request->sumoc;
        $selectedSumnp = $request->sumnp;

        $companies = Company::whereIn('name', $selectedCompanies)->with(['tariffs'])->get();
        foreach ($companies as $company) {

            foreach ($selectedPlaces as $place) {
                $selectedSize = "{$place['length']}x{$place['width']}x{$place['height']}";
                $selectedWeight = $place['weight'];

                $responseData = [];

                if ($selectedSumoc && !$selectedSumnp) {

                    $tariffs = $company->tariffs()->where([
                        ['sumoc', '=', true],
                        ['sumnp', '=', false],
                    ])->get();

                    foreach ($tariffs as $tarif) {

                        $parameters = [
                            'json' => '',                   // ответ в формате json
                            'object' => $tarif->number,     // тариф -> приложение 1
                            'weight' => $selectedWeight,    // вес отправления в граммах
                            'from' => $selectedFrom,        // откуда
                            'to' => $selectedTo,            // куда
                            'sumoc' => $selectedSumoc,      // сумма объявленной ценности в копейках
                            'size' => $selectedSize         // размеры в см
                        ];

                        $response = Http::get('https://tariff.pochta.ru/v2/calculate/tariff/delivery', $parameters)->object();

                        dump($response);

                        $responseData[] = [
                            'tariff_name' => $response->name,
                            'pay' => $response->paynds / 100,
                            'deadline' => Carbon::parse($response->delivery->deadline)->format('d.m.Y'),
                            'days' => "от {$response->delivery->min} до {$response->delivery->max}",
                        ];
                    }
                }
            }
        }

        dd($responseData);

        // если можно подобрать упаковку, подключаем эти тарифы и собираем эти запросы
        // если отправление международное, подключаем эти тарифы и собираем эти запросы
        // если есть сумма объявленной ценности, подключаем эти тарифы и собираем эти запросы
        // если есть наложенный платёж, подключаем эти тарифы и собираем эти запросы

        // создаем массив запросов и тарифов

        // обходим этот массив и собираем результаты, которые удовлетворяют требованиям

        // выводим эти результаты пользователю

        dd('stop');
        // dd($tariffs->tariffs()->get());


        dd($request->all());

        foreach (PochtaTariffType::cases() as $key => $tariffCode) {

            dump($tariffCode->value);

            $param = [];

            $response = Http::get('https://tariff.pochta.ru/v2/calculate/tariff/delivery', $param);
            dump($response->json());

            // в ответе интересует:
            // - paynds - стоимость ндс
            // - name - название тарифа
            // - items.0.tariff.valnds - стоимость ндс
            // - items.1.delivery.min - минимальный срок доставки
            // - items.1.delivery.max - максимальный срок доставки
            // - items.2.delivery.deadline - крайняя дата доставки
        }
    }

    // 'json' => '', // ответ в формате json
    // 'object' => $tariffCode->value, // тариф -> приложение 1
    // 'weight' => '1000', // вес отправления в граммах
    // 'from' => 101000, // откуда
    // 'to' => 660005, // куда
    // 'to' => 398, // куда
    // 'from' => '660005', // откуда
    // 'country-from' => '398', // откуда
    // 'import' => '104000', // индекс входящего мппо
    // 'country-to' => '398', // куда
    // 'sumoc' => '50000', // сумма объявленной ценности в копейках
    // 'sumnp' => '38000', // сумма наложенного платежа в копейках
    // 'pack' => 10, // код типа упаковки -> приложение 3
    // 'size' => '130x20x10' // размеры в см
    // 'transtype' => 1, // тип доставки
    // 'countinpack' => 1, // количество отправлений в группе
    // 'group' => 0, // признак тарификации группы отправлений
    // 'service' => 26, // идентификаторы услуг -> приложение 2

    /**
     * Справка по габаритам https://delivery.pochta.ru/post-calculator-api.pdf?836 приложение №3
     */
    private function whatPackage($length, $width, $height)
    {
        //расположим габариты по убыванию
        $size_desc = [$length, $width, $height];
        rsort($size_desc); //расположили по убыванию
        //перечислим габариты коробок
        $pack =
            [
                10 => [26, 17, 8], //Коробка S 26×17×8 cм
                20 => [30, 20, 15], //Коробка M 30×20×15 cм
                30 => [40, 27, 18], //Коробка L 40×27×18 см
                40 => [53, 36, 22], //Коробка XL 53×36×22 см
            ];
        //подберем
        foreach ($pack as $k => $v) {
            if ($size_desc[0] <= $v[0] && $size_desc[1] <= $v[1] && $size_desc[2] <= $v[2]) return $k;
        }
        return 40;
    }
}
