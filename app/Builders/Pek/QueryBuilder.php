<?php

namespace App\Builders\Pek;

use App\Builders\BaseBuilder;
use App\Enums\DeliveryType;
use App\Enums\Pek\PekTariffType;
use App\Enums\Pek\PekUrlType;
use App\Interfaces\RequestBuilderInterface;
use App\Models\Location;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QueryBuilder extends BaseBuilder implements RequestBuilderInterface
{
    private string $url;
    private string $user;
    private string $password;

    public function __construct()
    {
        $this->url = config('companies.pek.url') . PekUrlType::Calculate->value;
        $this->user = config('companies.pek.user');
        $this->password = config('companies.pek.password');

        // выявленные ограничения
        $this->limitInsurance = (float) 999999999999;   // руб

        // ограничения для авто-тарифа
        $this->autoLimitWeight = 20000; // кг
        $this->autoLimitLength = 13.4;  // м
        $this->autoLimitWidth = 2.42;   // м
        $this->autoLimitHeight = 2.45;  // м
        $this->autoLimitVolume = 80;    // м3

        // ограничения для авиа-тарифа
        $this->aviaLimitWeight = 80;    // кг
        $this->aviaLimitLength = 2;     // м
        $this->aviaLimitWidth = 1;      // м
        $this->aviaLimitHeight = 0.8;   // м
        $this->aviaLimitVolume = 1.6;   // м3
    }

    /**
     * Обеспечивает сборку запросов для ассинхронной отправки.
     * 
     * @param Pool $pool
     * @param Request $request
     * 
     * @return array
     */
    public function build(array $request, Pool $pool): array
    {
        $request = (object) $request;

        // проверка наложенного платежа (не работает с нп)
        try {
            parent::checkCashOnDelivery($request);
        } catch (\Throwable $th) {
            throw $th;
        }

        // проверка объявленной ценности
        try {
            parent::checkDeclarePrice($request);
        } catch (\Throwable $th) {
            throw $th;
        }

        // проверка корректности получения идентификатора населённого пункта
        try {
            $from = Location::find($request->from)->terminalsPek()->firstOrFail();
            $to = Location::find($request->to)->terminalsPek()->firstOrFail();
        } catch (\Throwable $th) {
            throw new Exception("ТК не работает с локациями: $request->from -> $request->to", 200);
        }

        $tariffs = collect([]);
        $gabarits = $this->gabarits($request);

        // проверка габаритов для авиа-тарифа
        try {
            parent::checkAviaGabarits($gabarits);
            $tariffs->push(PekTariffType::AviaExpress->value);
        } catch (\Throwable $th) {
            // если параметры больше, то авиа-тариф не будет принимать участие в калькуляции
        }

        // проверка габаритов для авто-тарифов
        try {
            parent::checkAutoGabarits($gabarits);
            $tariffs->push(
                PekTariffType::Auto->value,
                PekTariffType::AutoDts->value,
                // PekTariffType::AutoExpress->value, // не обслуживается
                PekTariffType::AutoEasyWay->value,
            );
        } catch (\Throwable $th) {
            throw new Exception("Параметры груза превышают допустимые габариты.", 500);
        }

        // проверка способа доставки, применение способа поумолчанию, если ни один не выбран
        $deliveryTypes = parent::checkDeliveryType($request);

        foreach ($deliveryTypes as $type) {

            $cargos = [];
            foreach ($request->places as $place) {

                $place = (object) $place;

                // тк допускает возможность отсутствия параметров дшв либо объёма
                $gabarits = (object) array_filter([
                    'weight' => (float) $place->weight,
                    'length' => isset($place->length) ? $place->length / 100 : null,
                    'width' => isset($place->width) ? $place->width / 100 : null,
                    'height' => isset($place->height) ? $place->height / 100 : null,
                    'volume' => isset($place->volume) ? (float) $place->volume : null,
                ]);

                $cargos[] = (array) $gabarits;
            }

            $template = [
                "types" => $tariffs->toArray(),
                "senderWarehouseId" => $from->identifier,
                "receiverWarehouseId" => $to->identifier,
                "plannedDateTime" => $request->shipment_date . 'T00:00:00',
                "isInsurance" => isset($request->insurance) ? true : false,
                "isInsurancePrice" => isset($request->insurance) ?  $request->insurance : 0.0,
                'isPickUp' => $type == DeliveryType::Ds->value || $type == DeliveryType::Dd->value ? true : false,
                'isDelivery' => $type == DeliveryType::Sd->value || $type == DeliveryType::Dd->value ? true : false,
                "pickup" => ["address" => mb_ucfirst(mb_strtolower($from->location->country->name)) . ', ' . $from->name],
                "delivery" => ["address" => mb_ucfirst(mb_strtolower($to->location->country->name)) . ', ' . $to->name],
                "cargos" => $cargos,
            ];

            // отладка
            if (env('SHOW_Q')) {
                dump($template);
            }

            Log::channel('tk')->info("Отправка запроса: " . $this->url, $template);
            $pools[] = $pool->as($type)->withBasicAuth($this->user, $this->password)->post($this->url, $template);
        }

        return $pools;
    }

    /**
     * Возвращает преобразованные параметры груза для последующей проверке на ограничения.
     * 
     * Допускается отсутствие дшв либо объёма.
     * Параметры включают в себя максимальные агрегированные значения всего груза.
     * Данная тк использует в качестве единиц измерения: килограмм, метр, кубический метр.
     * 
     * @param object $request
     * @return object
     */
    private function gabarits(object $request): object
    {
        $places = collect($request->places);

        return (object) array_filter([
            'weight' => $places->sum('weight'),                                         // итоговый вес, кг
            'length' => $places->max('length') ? $places->max('length') / 100 : null,   // длина, м
            'width' => $places->max('width') ? $places->max('width') / 100 : null,      // ширина, м
            'height' => $places->max('height') ? $places->max('height') / 100 : null,   // высота, м
            'volume' => $places->sum('volume') ? $places->sum('volume') : null,         // итоговый объём, м3
        ]);
    }
}
