<?php

declare(strict_types=1);

namespace App\Builders;

use App\Enums\DeliveryType;
use Exception;

class BaseBuilder
{
    // стандартные ограничения
    protected $limitWeight;
    protected $limitLength;
    protected $limitWidth;
    protected $limitHeight;
    protected $limitVolume;
    protected $limitInsurance;
    protected $limitCashOnDelivery;

    // ограничения малогабаритного груза
    protected $smalllimitLength;
    protected $smallLimitWidth;
    protected $smallLimitHeight;
    protected $smallLimitVolume;
    protected $smallLimitQuantity;

    // ограничения для негабаритного груза
    protected $мaxLimitlength;
    protected $мaxLimitWidth;
    protected $мaxLimitHeight;

    // ограничения для авто-тарифа (pek)
    protected $autoLimitWeight;
    protected $autoLimitLength;
    protected $autoLimitWidth;
    protected $autoLimitHeight;
    protected $autoLimitVolume;

    // ограничения для авиа-тарифа (pek)
    protected $aviaLimitWeight;
    protected $aviaLimitLength;
    protected $aviaLimitWidth;
    protected $aviaLimitHeight;
    protected $aviaLimitVolume;

    /**
     * Проверка способа доставки: возвращает способ доставки поумолчанию если ни один не выбран.
     * 
     * @var object $request)
     * @return array
     */
    protected function checkDeliveryType(object $request): array
    {
        if (!isset($request->delivery_type)) {
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
    protected function checkGabarits(object $gabarits)
    {
        // обработка случая, когда тк не использует параметр объёма
        $currentVolume = $gabarits->volume ?? 0;
        $limitVolume = $this->limitVolume ?? 0;

        // обработка слечая, когда компания использует общий объём посылки (jde)
        if (!$currentVolume) {
            $currentVolume = $gabarits->totalVolume ?? 0;
        }

        // обработка случая, когда у тк не определены пространственные ограничения
        $limitLength = $this->limitLength ?? 999999;
        $limitWidth = $this->limitWidth ?? 999999;
        $limitHeight = $this->limitHeight ?? 999999;

        // ! для отладки
        // dump("
        // $gabarits->weight > $this->limitWeight
        // $gabarits->length > $limitLength
        // $gabarits->width > $limitWidth
        // $gabarits->height > $limitHeight
        // $currentVolume > $limitVolume
        // ");

        if (
            $gabarits->weight > $this->limitWeight
            || $gabarits->length > $limitLength
            || $gabarits->width > $limitWidth
            || $gabarits->height > $limitHeight
            || $currentVolume > $limitVolume
        ) {
            throw new Exception("Габариты превышают допустимые. Компания не сможет участвовать в калькуляции.", 200);
        }
    }

    /**
     * Проверка габаритов малогабаритного груза: выбрасывает исключение, если габариты превышают допустимые.
     */
    protected function checkSmallGabarits(object $gabarits)
    {
        // ! для отладки
        // dump("
        //     $gabarits->length > $this->smalllimitLength
        //     || $gabarits->width > $this->smallLimitWidth
        //     || $gabarits->height > $this->smallLimitHeight
        //     || $gabarits->totalVolume > $this->smallLimitVolume
        //     || $gabarits->quantity > $this->smallLimitQuantity
        // ");

        if (
            $gabarits->length > $this->smalllimitLength
            || $gabarits->width > $this->smallLimitWidth
            || $gabarits->height > $this->smallLimitHeight
            || $gabarits->totalVolume > $this->smallLimitVolume
            || $gabarits->quantity > $this->smallLimitQuantity
        ) {
            throw new Exception("Габариты превышают параметры малогабаритного груза. Будет применён стандартный тариф.", 200);
        }
    }

    /**
     * Проверка габаритов негабаритного груза: выбрасывает исключение, если габариты превышают допустимые.
     */
    protected function checkNonGabarits(object $gabarits)
    {
        // ! для отладки
        // dump("
        //     $gabarits->length > $this->мaxLimitlength
        //     || $gabarits->width > $this->мaxLimitWidth
        //     || $gabarits->height > $this->мaxLimitHeight
        // ");

        if (
            $gabarits->length > $this->мaxLimitlength
            || $gabarits->width > $this->мaxLimitWidth
            || $gabarits->height > $this->мaxLimitHeight
        ) {
            throw new Exception("Габариты превышают параметры малогабаритного груза. Будет применён стандартный тариф.", 200);
        }
    }

    /**
     * Проверка габаритов автомобильного груза: выбрасывает исключение, если габариты превышают допустимые.
     */
    protected function checkAutoGabarits(object $gabarits)
    {
        // обработка случая, когда переданы только параметры дшв
        $currentVolume = isset($gabarits->volume)
            ? $gabarits->volume
            : $gabarits->length * $gabarits->width * $gabarits->height;

        // обработка случая, когда передан только параметр объёма
        $currentLength = isset($gabarits->length) ? $gabarits->length : 0;
        $currentWidth = isset($gabarits->width) ? $gabarits->width : 0;
        $currentHeight = isset($gabarits->height) ? $gabarits->height : 0;

        // ! для отладки
        // dump("$gabarits->weight > $this->autoLimitWeight");
        // dump("$currentLength > $this->autoLimitLength");
        // dump("$currentWidth > $this->autoLimitWidth");
        // dump("$currentHeight > $this->autoLimitHeight");
        // dump("$currentVolume > $this->autoLimitVolume");

        if (
            $gabarits->weight > $this->autoLimitWeight
            || $currentLength > $this->autoLimitLength
            || $currentWidth > $this->autoLimitWidth
            || $currentHeight > $this->autoLimitHeight
            || $currentVolume > $this->autoLimitVolume
        ) {
            throw new Exception("Габариты превышают параметры авто-перевозимого груза. Авто тарифы будут исключены из расчётов.", 200);
        }
    }

    /**
     * Проверка габаритов авиационного груза: выбрасывает исключение, если габариты превышают допустимые.
     */
    protected function checkAviaGabarits(object $gabarits)
    {
        // обработка случая, когда переданы только параметры дшв
        $currentVolume = isset($gabarits->volume)
            ? $gabarits->volume
            : $gabarits->length * $gabarits->width * $gabarits->height;

        // обработка случая, когда передан только параметр объёма
        $currentLength = isset($gabarits->length) ? $gabarits->length : 0;
        $currentWidth = isset($gabarits->width) ? $gabarits->width : 0;
        $currentHeight = isset($gabarits->height) ? $gabarits->height : 0;

        // ! для отладки
        // dump("$gabarits->weight > $this->aviaLimitWeight");
        // dump("$currentLength > $this->aviaLimitLength");
        // dump("$currentWidth > $this->aviaLimitWidth");
        // dump("$currentHeight > $this->aviaLimitHeight");
        // dump("$currentVolume > $this->aviaLimitVolume");

        if (
            $gabarits->weight > $this->aviaLimitWeight
            || $currentLength > $this->aviaLimitLength
            || $currentWidth > $this->aviaLimitWidth
            || $currentHeight > $this->aviaLimitHeight
            || $currentVolume > $this->aviaLimitVolume
        ) {
            throw new Exception("Габариты превышают параметры авиа-перевозимого груза. Авиа-тарифы будут исключены из расчётов.", 200);
        }
    }
}
