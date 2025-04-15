<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Pochta\PochtaPackagesType;
use App\Enums\Pochta\PochtaTariffType;
use App\Models\Company;
use App\UseCases\TK\BaikalsrCase;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
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
                    if ($selectedSumoc && !$selectedSumnp) {
                        $tariffs = $company->tariffs()->where([
                            ['sumoc', '=', true],
                            ['sumnp', '=', false],
                            ['international', '=', true],
                            ['disabled', '=', true],
                        ])->get();
                    }
                    if ($selectedSumoc && $selectedSumnp) {
                        $tariffs = $company->tariffs()->where([
                            ['sumoc', '=', true],
                            ['sumnp', '=', true],
                            ['international', '=', true],
                            ['disabled', '=', true],
                        ])->get();
                    }
                } else {
                    if ($selectedSumoc && !$selectedSumnp) {
                        $tariffs = $company->tariffs()->where([
                            ['sumoc', '=', true],
                            ['sumnp', '=', false],
                            ['international', '=', false],
                            ['disabled', '=', true],
                        ])->get();
                    }
                    if ($selectedSumoc && $selectedSumnp) {
                        $tariffs = $company->tariffs()->where([
                            ['sumoc', '=', true],
                            ['sumnp', '=', true],
                            ['international', '=', false],
                            ['disabled', '=', true],
                        ])->get();
                    }
                }

                foreach ($tariffs as $tariff) {

                    $parameters = [
                        'json' => '', // ответ в формате json
                        'object' => $tariff->number, // тариф -> приложение 1
                        'weight' => $selectedWeight, // вес отправления в граммах
                        'from' => $selectedFrom, // откуда
                        'to' => $selectedTo, // куда
                        'sumoc' => $selectedSumoc, // сумма объявленной ценности в копейках
                        'size' => $selectedSize, // размеры в см
                        'pack' => $selectedPack, // код типа упаковки -> приложение 3
                        // 'countinpack' => 1 // для простого международного исходящего
                    ];

                    $response = Http::get(config('companies.pochta.url'), $parameters)->object();

                    $responseData[] = [
                        'tariff_name' => $tariff->name,
                        'tariff_label' => $response->name,
                        'pay' => isset($response->paynds)
                            ? $response->paynds / 100
                            : null,
                        'errors' => isset($response->errors)
                            ? $response->errors
                            : null,
                        'deadline' => Carbon::parse($response->delivery->deadline)->format('d.m.Y'),
                        'days' => "от {$response->delivery->min} до {$response->delivery->max}",
                    ];

                    dd($responseData);
                }
            }
        }
    }

    /**
     * Проверка: является ли доставка интернациональной?
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
