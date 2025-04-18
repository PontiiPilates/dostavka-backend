<?php

declare(strict_types=1);

namespace App\Enums;

enum  ValidateMessagesType: string
{
    // типовые случаи
    case Required = 'Обязательно для заполнения';
    case ToBeArray = 'Должно быть массивом';
    case ToBeDate = 'Должной быть датой в формате ДД.ММ.ГГГГ';
    case ToBeInteger = 'Должно быть целым числом';

    // частные случаи
    case ToBeNoMore = 'Должна быть не больше суммы объявленной ценности :value';
    case ToBeDecimal = 'Должно быть числом с одним десятичным знаком после точки';
}
