<?php

declare(strict_types=1);

namespace App\Enums;

enum  ValidateMessagesType: string
{
    case Required = 'Обязательно для заполнения';
    case ToBeArray = 'Должно быть массивом';
    case ToBeDate = 'Должной быть датой в формате ДД.ММ.ГГГГ';
    case ToBeInteger = 'Должно быть целым числом';

    case InvalidCharacters = 'Содержит недопустимые символы';

    case ToBeMore = 'Должно быть минимум :min символа';
    case ToBeLess = 'Должно быть максимум :max символов';

    case ToBeNoMore = 'Должна быть не больше суммы объявленной ценности :value';
    case ToBeDecimal = 'Должно быть числом с одним десятичным знаком после точки';
}
