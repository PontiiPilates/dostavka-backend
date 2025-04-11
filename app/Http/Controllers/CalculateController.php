<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Pochta\PochtaTariffType;
use App\UseCases\TK\BaikalsrCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CalculateController extends Controller
{
    public function handle(Request $request)
    {

        foreach (PochtaTariffType::cases() as $key => $tariffCode) {

            dump($tariffCode->value);

            $param = [
                'json' => '', // ответ в формате json
                'object' => $tariffCode->value, // тариф -> приложение 1
                'weight' => '1000', // вес отправления в граммах
                // 'from' => 101000, // откуда
                // 'to' => 660005, // куда
                // 'to' => 398, // куда
                // 'from' => '660005', // откуда
                'country-from' => '398', // откуда
                'import' => '104000', // индекс входящего мппо
                // 'country-to' => '398', // куда
                'sumoc' => '50000', // сумма объявленной ценности в копейках
                'sumnp' => '38000', // сумма наложенного платежа в копейках
                'pack' => 10, // код типа упаковки -> приложение 3
                'size' => '130x20x10' // размеры в см
                // 'transtype' => 1, // тип доставки
                // 'countinpack' => 1, // количество отправлений в группе
                // 'group' => 0, // признак тарификации группы отправлений
                // 'service' => 26, // идентификаторы услуг -> приложение 2
            ];

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

        dd('stop');





        // dump($response->json());






        $response = $case->handle();

        dd($response);

        return response()->json($paraeters);
    }

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

    /**
     * Комиссия за ценность
     */
    private function comisFromCost($cost, array $places)
    {
        //разнесем комиссию за ценность - по местам
        if ($cost > 0) {
            $sumoc = $cost / count($places);
            $sumoc = round($sumoc * 100); //переведем в копейки
        } else {
            $sumoc = 10000;
        }
    }
}
