<?php

declare(strict_types=1);

namespace App\Builders;

use App\Enums\DeliveryType;
use Exception;

class BaseBuilder
{
    protected $limitWeight;

    protected $limitLength;
    protected $limitWidth;
    protected $limitHeight;
    protected $limitVolume;

    protected $limitInsurance;
    protected $limitCashOnDelivery;

    /**
     * Проверка способа доставки: возвращает способ доставки поумолчанию если ни один не выбран.
     * 
     * @var object $request)
     * @return array
     */
    protected function checkDeliveryType(object $request): array
    {
        if (!$request->delivery_type) {
            return [DeliveryType::Ss->value];
        }

        return $request->delivery_type;
    }

    /**
     * Проверка информации о наложенном платеже: выбрасывает исключение если компания не работает с наложенным платежём либо он превышает установленный предел.
     * 
     * @var object $request
     * @return void
     */
    protected function checkCashOnDelivery(object $request): void
    {
        // обработка случая, когда параметр наложенного платежа не используется
        $currentCashOnDelivery = $request->cash_on_delivery ?? 0;
        $limitCashOnDelivery = $this->limitCashOnDelivery ?? 0;

        // ! для отладки
        // dump("$currentCashOnDelivery > $limitCashOnDelivery");

        if ($currentCashOnDelivery > $limitCashOnDelivery) {
            throw new Exception('Компания не работает с наложенным платежём либо он превышает установленный предел. Компания не сможет участвовать в калькуляции.', 200);
        }
    }

    /**
     * Проверка суммы объявленной ценности: выбрасывает исключение если сумма объявленной ценности больше установленного предела.
     */
    protected function checkDeclarePrice(object $request): void
    {
        // обработка случая, когда параметр объявленной стоимости не используется
        $currentinsurance = $request->insurance ?? 0;
        $limitinsurance = $this->limitInsurance ?? 0;

        // ! для отладки
        // dump("$currentinsurance > $limitinsurance");

        if ($currentinsurance > $limitinsurance) {
            throw new Exception('Сумма объявленной ценности больше установленной. Компания не сможет участвовать в калькуляции.', 200);
        }
    }

    /**
     * Проверка габаритов: выбрасывает исключение, если габариты превышают допустимые.
     */
    protected function checkGabarits(object $place)
    {
        // обработка случая, когда тк не использует параметр объёма
        $currentVolume = $place->volume ?? 0;
        $limitVolume = $this->limitVolume ?? 0;

        // обработка случая, когда у тк не определены пространственные ограничения
        $limitLength = $this->limitLength ?? 999999;
        $limitWidth = $this->limitWidth ?? 999999;
        $limitHeight = $this->limitHeight ?? 999999;

        // ! для отладки
        // dump("$place->weight > $this->limitWeight
        // $place->length > $limitLength
        // $place->width > $limitWidth
        // $place->height > $limitHeight
        // $currentVolume > $limitVolume");

        if (
            $place->weight > $this->limitWeight
            || $place->length > $limitLength
            || $place->width > $limitWidth
            || $place->height > $limitHeight
            || $currentVolume > $limitVolume
        ) {
            throw new Exception("Габариты превышают допустимые. Компания не сможет участвовать в калькуляции.", 200);
        }
    }
}
