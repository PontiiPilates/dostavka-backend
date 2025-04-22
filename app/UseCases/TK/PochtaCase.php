<?php

declare(strict_types=1);

namespace App\UseCases\TK;

use App\Enums\CompanyType;
use App\Enums\Pochta\PochtaPackagesType;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

final class PochtaCase extends BaseCase
{
    public function handle(Request $request)
    {
        $selectedFrom = $request->from;
        $selectedTo = $request->to;
        $selectedRegimes = $request->regimes;
        $selectedPlaces = $request->places;
        $selectedSumoc = $request->sumoc * 100; // перевод в копейки
        $selectedSumnp = $request->sumnp * 100; // перевод в копейки
        $selectedInternationals = $this->isInternational($request->to);

        $company = Company::where('name', CompanyType::Pochta->value)->with(['tariffs'])->first();

        foreach ($selectedPlaces as $place) {
            $selectedSize = "{$place['length']}x{$place['width']}x{$place['height']}";
            $selectedWeight = $place['weight'] * 1000; // перевод в граммы
            $selectedPack = $this->choosePack([$place['length'], $place['width'], $place['height']]);

            // 'volume' => "3000", // код типа упаковки -> приложение 3
            // 'sizemax' => "500", // максимальный размер одной из сторон  
            // 'country-to' => 398, // куда
            // 'sumoc' => 7000, // сумма объявленной ценности в копейках
            // 'sumnp' => 6899, ; // сумма объявленной ценности в копейках

            $requestParameters = [
                'json' => '', // ответ в формате json
                'weight' => $selectedWeight, // вес отправления в граммах
                'from' => $selectedFrom, // откуда
                'size' => $selectedSize, // размеры в см
                'pack' => $selectedPack, // код типа упаковки -> приложение 3
            ];

            $queryParameters = [
                ['available', '=', true],
                ['const_weight', '>=', $selectedWeight],
            ];

            // интернациональный, без объявленной ценности, без наложенного платежа
            if ($selectedInternationals && !$selectedSumoc && !$selectedSumnp) {

                $requestParameters['country-to'] = $selectedTo;

                $queryParameters[] = ['international', '=', true];
                $queryParameters[] = ['sumoc', '=', false];
                $queryParameters[] = ['sumnp', '=', false];

                $tariffs = $company->tariffs()->where($queryParameters)->get();
            }

            // интернациональный, с объявленной ценностью, без наложенного платежа
            if ($selectedInternationals && $selectedSumoc && !$selectedSumnp) {

                $requestParameters['country-to'] = $selectedTo;
                $requestParameters['sumoc'] = $selectedSumoc;

                $queryParameters[] = ['international', '=', true];
                $queryParameters[] = ['sumoc', '=', true];
                $queryParameters[] = ['sumnp', '=', false];

                $tariffs = $company->tariffs()->where($queryParameters)->get();
            }

            // интернациональный, с объявленной ценностью, с наложенным платежём
            if ($selectedInternationals && $selectedSumoc && $selectedSumnp) {

                $requestParameters['country-to'] = $selectedTo;
                $requestParameters['sumoc'] = $selectedSumoc;
                $requestParameters['sumnp'] = $selectedSumnp;

                $queryParameters[] = ['international', '=', true];
                $queryParameters[] = ['sumoc', '=', true];
                $queryParameters[] = ['sumnp', '=', true];

                $tariffs = $company->tariffs()->where($queryParameters)->get();
            }

            // внутренний, без объявленной ценности, без наложенного платежа
            if (!$selectedInternationals && !$selectedSumoc && !$selectedSumnp) {

                $requestParameters['to'] = $selectedTo;

                $queryParameters[] = ['international', '=', false];
                $queryParameters[] = ['sumoc', '=', false];
                $queryParameters[] = ['sumnp', '=', false];

                $tariffs = $company->tariffs()->where($queryParameters)->get();
            }

            // внутренний, с объявленной ценностью, без наложенного платежа
            if (!$selectedInternationals && $selectedSumoc && !$selectedSumnp) {

                $requestParameters['to'] = $selectedTo;
                $requestParameters['sumoc'] = $selectedSumoc;

                $queryParameters[] = ['international', '=', false];
                $queryParameters[] = ['sumoc', '=', true];
                $queryParameters[] = ['sumnp', '=', false];

                $tariffs = $company->tariffs()->where($queryParameters)->get();
            }

            // внутренний, с объявленной ценностью, с наложенным платежём
            if (!$selectedInternationals && $selectedSumoc && $selectedSumnp) {

                $requestParameters['to'] = $selectedTo;
                $requestParameters['sumoc'] = $selectedSumoc;
                $requestParameters['sumnp'] = $selectedSumnp;

                $queryParameters[] = ['international', '=', false];
                $queryParameters[] = ['sumoc', '=', true];
                $queryParameters[] = ['sumnp', '=', true];

                $tariffs = $company->tariffs()->where($queryParameters)->get();
            }

            // если не найдено тарифов
            if ($tariffs->count() === 0) {
                return response()->json([
                    'message' => 'для заданных условий нет подходящих тарифов',
                ]);
            }

            $responses = Http::pool(fn(Pool $pool) => $this->pools($pool, $tariffs, $requestParameters));

            return $this->responsePrepare($responses);
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
                $deadline = 'требует уточнения';
                $days = 'требует уточнения';
            }

            // если существуют ошибки, то пропустить тариф
            if (isset($response->errors)) {
                continue;
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
            return "{$response->delivery->min}-{$response->delivery->max}";
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
        return PochtaPackagesType::Unstandart->value;
    }
}
