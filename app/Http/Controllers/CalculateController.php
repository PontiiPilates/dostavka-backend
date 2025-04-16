<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Pochta\PochtaPackagesType;
use App\Enums\Pochta\PochtaTariffType;
use App\Models\Company;
use App\UseCases\TK\BaikalsrCase;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $selectedInternationals = $this->isInternational($request->to);

        $companies = Company::whereIn('name', $selectedCompanies)->with(['tariffs'])->get();
        foreach ($companies as $company) {

            foreach ($selectedPlaces as $place) {
                $selectedSize = "{$place['length']}x{$place['width']}x{$place['height']}";
                $selectedWeight = $place['weight'];
                $selectedPack = $this->choosePack([$place['length'], $place['width'], $place['height']]);


                if ($selectedInternationals) {

                    $parameters = [
                        'json' => '', // ответ в формате json
                        'weight' => $selectedWeight, // вес отправления в граммах
                        'from' => $selectedFrom, // откуда
                        'country-to' => $selectedTo, // куда
                        'size' => $selectedSize, // размеры в см
                        'pack' => $selectedPack, // код типа упаковки -> приложение 3
                    ];

                    if ($selectedSumoc && !$selectedSumnp) {
                        $tariffs = $company->tariffs()->where([
                            ['sumoc', '=', true],
                            ['sumnp', '=', false],
                            ['international', '=', true],
                            ['available', '=', true],
                        ])->get();

                        $parameters['sumoc'] = $selectedSumoc; // сумма объявленной ценности в копейках

                    }
                    if ($selectedSumoc && $selectedSumnp) {
                        $tariffs = $company->tariffs()->where([
                            ['sumoc', '=', true],
                            ['sumnp', '=', true],
                            ['international', '=', true],
                            ['available', '=', true],
                        ])->get();

                        $parameters['sumoc'] = $selectedSumoc; // сумма объявленной ценности в копейках
                        $parameters['sumnp'] = $selectedSumnp; // сумма объявленной ценности в копейках
                    }
                } else {

                    $parameters = [
                        'json' => '', // ответ в формате json
                        'weight' => $selectedWeight, // вес отправления в граммах
                        'from' => $selectedFrom, // откуда
                        'to' => $selectedTo, // куда
                        'size' => $selectedSize, // размеры в см
                        'pack' => $selectedPack, // код типа упаковки -> приложение 3
                    ];

                    if ($selectedSumoc && !$selectedSumnp) {
                        $tariffs = $company->tariffs()->where([
                            ['sumoc', '=', true],
                            ['sumnp', '=', false],
                            ['international', '=', false],
                            ['available', '=', true],
                        ])->get();

                        $parameters['sumoc'] = $selectedSumoc; // сумма объявленной ценности в копейках

                    }
                    if ($selectedSumoc && $selectedSumnp) {
                        $tariffs = $company->tariffs()->where([
                            ['sumoc', '=', true],
                            ['sumnp', '=', true],
                            ['international', '=', false],
                            ['available', '=', true],
                        ])->get();

                        $parameters['sumoc'] = $selectedSumoc; // сумма объявленной ценности в копейках
                        $parameters['sumnp'] = $selectedSumnp; // сумма объявленной ценности в копейках
                    }
                }

                $responses = Http::pool(fn(Pool $pool) => $this->pools($pool, $tariffs, $parameters));

                dd($this->responsePrepare($responses));
            }
        }
    }

    /**
     * Подготовка структуры данных для ответа.
     */
    private function responsePrepare($responses)
    {
        $responseData = [];

        foreach ($responses as $response) {

            $response = $response->object();

            if (isset($response->delivery)) {
                $deadline = Carbon::parse($response->delivery->deadline)->format('d.m.Y');
                $days = $this->rangeDays($response);
            } else {
                $deadline = null;
                $days = null;
            }

            $responseData[] = [
                'tariff_name' => $response->name,
                'tariff_number' => $response->id,
                'pay' => isset($response->paynds)
                    ? $response->paynds / 100
                    : null,
                'errors' => isset($response->errors)
                    ? $response->errors
                    : null,
                'deadline' => $deadline,
                'days' => $days,
            ];
        }

        return $responseData;
    }


    private function rangeDays($response)
    {
        if ($response->delivery->min == $response->delivery->max) {
            return $response->delivery->min;
        } else {
            return "{$response->delivery->min} - {$response->delivery->max}";
        }
    }

    /**
     * Генератор динамических запросов.
     */
    private function pools($pool, $tariffs, $parameters): array
    {
        foreach ($tariffs as $tariff) {
            $parameters['object'] = $tariff->number;
            $pools[] = $pool->get(config('companies.pochta.url'), $parameters);
        }
        return $pools;
    }

    /**
     * Проверка: является ли доставка интернациональной.
     * Это влияет на выбор группы тарифов.
     */
    private function isInternational($code): bool
    {
        if ($code == 643) return false;
        return DB::table('countries')->where('code', $code)->exists();
    }


    /**
     * Попытка подобрать упаковку.
     * Это требуется только одному тарифу.
     * Остальные не против принимать значение упаковки.
     */
    private function choosePack(array $param)
    {
        rsort($param);

        $pack = [
            PochtaPackagesType::S->value => [26, 17, 8],
            PochtaPackagesType::M->value => [30, 20, 15],
            PochtaPackagesType::L->value => [40, 27, 18],
            PochtaPackagesType::XL->value => [53, 36, 22],
        ];

        foreach ($pack as $k => $v) {
            if (
                $param[0] <= $v[0] &&
                $param[1] <= $v[1] &&
                $param[2] <= $v[2]
            ) return $k;
        }
        return null;
    }
}
