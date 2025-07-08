<?php

declare(strict_types=1);

namespace App\Builders;

use App\Enums\DeliveryType;
use Exception;
use Illuminate\Support\Facades\Log;

class BaseBuilder
{
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
     * Проверка информации о наложенном платеже: выбрасывает исключение если компания не работает с наложенным платежём.
     * 
     * @var object $request
     * @return void
     */
    protected function checkCashOnDelivery(object $request): void
    {
        if (isset($request->cash_on_delivery) && $request->cash_on_delivery > 0) {
            throw new Exception('Компания не работает с наложенным платежём, поэтому не сможет участвовать в калькуляции.', 200);
        }
    }

    /**
     * Проверка суммы объявленной ценности: выбрасывает исключение если сумма объявленной ценности больше установленного предела.
     */
    protected function checkDeclarePrice($declarePrice): void
    {
        if ($declarePrice >= 50000) {
            throw new Exception('Сумма объявленной ценности больше установленной. Компания не будет участвовать в калькуляции.');
        }
    }
}
