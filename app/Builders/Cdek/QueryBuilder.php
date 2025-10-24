<?php

namespace App\Builders\Cdek;

use App\Builders\BaseBuilder;
use App\Enums\Cdek\CdekDeliveryType;
use App\Enums\Cdek\CdekUrlType;
use App\Enums\DeliveryType;
use App\Interfaces\RequestBuilderInterface;
use App\Models\Location;
use App\Services\Tk\TokenCdekService;
use Exception;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Log;

class QueryBuilder extends BaseBuilder implements RequestBuilderInterface
{
    private string $token;

    private string $urlTarif;
    private string $urlList;

    private array $tariffCodes;

    private TokenCdekService $tokenCdecService;

    public function __construct()
    {
        $this->tokenCdecService = new TokenCdekService();

        $this->urlTarif = config('companies.cdek.url') . CdekUrlType::Tariff->value;
        $this->urlList = config('companies.cdek.url') . CdekUrlType::TariffList->value;
        $this->token = $this->tokenCdecService->getActualToken();

        // выявленные ограничения
        $this->limitWeight = (int) 99900000;            // гр
        $this->limitLength = (int) 1000;                // см
        $this->limitWidth = (int) 1000;                 // см
        $this->limitHeight = (int) 1000;                // см
        $this->limitInsurance = (float) 1000000000000;  // руб

        $this->tariffCodes = [
            DeliveryType::Dd->value => CdekDeliveryType::Dd->value,
            DeliveryType::Ds->value => CdekDeliveryType::Ds->value,
            DeliveryType::Sd->value => CdekDeliveryType::Sd->value,
            DeliveryType::Ss->value => CdekDeliveryType::Ss->value,
            DeliveryType::Tt->value => CdekDeliveryType::Tt->value,
            DeliveryType::Dp->value => CdekDeliveryType::Dp->value,
            DeliveryType::Sp->value => CdekDeliveryType::Sp->value,
            DeliveryType::Pd->value => CdekDeliveryType::Pd->value,
            DeliveryType::Ps->value => CdekDeliveryType::Ps->value,
            DeliveryType::Pp->value => CdekDeliveryType::Pp->value,
        ];
    }

    /**
     * Обеспечивает сборку пула запросов для ассинхронной отправки.
     * 
     * @param array $request
     * @param Pool $pool
     * 
     * @return array
     */
    public function build(array $request, Pool $pool): array
    {
        // особенности:
        // данная тк производит расчёт как по конкретному тарифу, так и по всем возможным

        $request = (object) $request;

        // проверка наложенного платежа
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
            $from = Location::find($request->from)->terminalsCdek()->firstOrFail();
            $to = Location::find($request->to)->terminalsCdek()->firstOrFail();
        } catch (\Throwable $th) {
            throw new Exception("ТК не работает с локациями: $request->from -> $request->to", 200);
        }

        // если выбран способ доставки, то расчет по конкретному тарифу, иначе по всем возможным
        if (isset($request->delivery_type)) {
            foreach ($request->delivery_type as $type) {
                $template = $this->template($request, $from, $to, $type);
                Log::channel('requests')->info("Отправка запроса: " . $this->urlTarif, $template);
                $pools[] = $pool->as($type)->withToken($this->token)->post($this->urlTarif, $template);
            }
        } else {
            $template = $this->template($request, $from, $to);
            Log::channel('requests')->info("Отправка запроса: " . $this->urlList, $template);
            $pools[] = $pool->withToken($this->token)->post($this->urlList, $template);
        }

        return $pools;
    }

    private function places($request)
    {
        foreach ($request->places as $place) {

            $place = (object) $place;

            $gabarits = (object) [
                'weight' => (int) $place->weight * 1000,    // вес, грамм
                'length' => (int) $place->length,           // длина, см
                'width' => (int) $place->width,             // ширина, см
                'height' => (int) $place->height,           // высота, см
            ];

            // проверка габаритов
            try {
                parent::checkGabarits($gabarits);
            } catch (\Throwable $th) {
                throw $th;
            }

            $places[] = [
                "weight" => $gabarits->weight,
                "length" => $gabarits->length,
                "width" => $gabarits->width,
                "height" => $gabarits->height,
            ];
        }

        return $places;
    }

    private function template($request, $from, $to, $type = null)
    {
        return array_filter([
            "date" => (string) $request->shipment_date . 'T00:00:00+0000',
            "lang" => "rus",
            "tariff_code" => isset($request->delivery_type)
                ? $this->tariffCodes[$type]
                : null,
            "from_location" => [
                "code" => (int) $from->identifier
            ],
            "to_location" => [
                "code" => (int) $to->identifier
            ],
            "services" => [
                [
                    "code" => "INSURANCE",
                    "parameter" => (string) ($request->insurance ?? 0)
                ]
            ],
            "packages" => (array) $this->places($request),
        ]);
    }
}
