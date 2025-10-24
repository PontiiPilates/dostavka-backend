<?php

declare(strict_types=1);

namespace App\Builders\Cdek;

use App\DTO\CalculationResultDto;
use App\Enums\Cdek\CdekDeliveryType;
use App\Enums\Cdek\CdekUrlType;
use App\Enums\CompanyType;
use App\Enums\DeliveryType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResponseBuilder
{
    private string $urlTarif;
    private string $urlList;

    private string $company;

    private array $tariffCodes;

    public function __construct()
    {
        $this->urlTarif = config('companies.cdek.url') . CdekUrlType::Tariff->value;
        $this->urlList = config('companies.cdek.url') . CdekUrlType::TariffList->value;

        $this->company = CompanyType::Cdek->value;

        $this->tariffCodes = [
            CdekDeliveryType::Dd->value => DeliveryType::Dd->value,
            CdekDeliveryType::Ds->value => DeliveryType::Ds->value,
            CdekDeliveryType::Sd->value => DeliveryType::Sd->value,
            CdekDeliveryType::Ss->value => DeliveryType::Ss->value,
            CdekDeliveryType::Tt->value => DeliveryType::Tt->value,
            CdekDeliveryType::Dp->value => DeliveryType::Dp->value,
            CdekDeliveryType::Sp->value => DeliveryType::Sp->value,
            CdekDeliveryType::Pd->value => DeliveryType::Pd->value,
            CdekDeliveryType::Ps->value => DeliveryType::Ps->value,
            CdekDeliveryType::Pp->value => DeliveryType::Pp->value,
        ];
    }

    /**
     * Обеспечивает сборку требуемой структуры ответа.
     * 
     * @param array $responses
     * @return array
     */
    public function build(array $responses): array
    {
        // особенность данного обработчика в наличии двух сценариев ответа
        // один предлагает возможные варианты доставки
        // второй возвращает только жёстко определённые

        $result = CalculationResultDto::filler($this->company);

        foreach ($responses as $key => $response) {
            $response = $response->object();

            // todo: возможно эта обработка должна находиться не здесь/не только здесь
            if (isset($response->errors)) {
                $errorId = Str::random(10);

                Log::channel('tk')->error(
                    sprintf('Ошибка %s при обработке ответа: %s %s %s', $errorId, $this->urlList, __FILE__, __LINE__),
                    [$response->errors]
                );

                continue;
            }

            // если не выбран способ доставки и компания предлагает варианты
            if (isset($response->tariff_codes)) {
                foreach ($response->tariff_codes as $tariff) {
                    $deliveryType = $this->tariffCodes[$tariff->delivery_mode];
                    $result = $this->template($result, $deliveryType, $tariff);
                }
            } else {
                // если способ доставки жёстко определён
                // todo: добавить обработку ошибки при жестко определенном тарифе
                $result = $this->template($result, $key, $response);
            }
        }

        return $result;
    }

    private function template($result, $deliveryType, $tariff)
    {
        $result['data']['success'][$deliveryType][] = CalculationResultDto::tariff(
            $tariff->tariff_name ?? DeliveryType::{mb_ucfirst($deliveryType)}->label(),
            $tariff->delivery_sum,
            $tariff->calendar_min,
            $tariff->calendar_max,
        );

        return $result;
    }
}
