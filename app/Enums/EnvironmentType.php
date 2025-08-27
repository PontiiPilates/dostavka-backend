<?php

namespace App\Enums;

enum  EnvironmentType: string
{
    case Dev = 'Dev';
    case Test = 'Test';
    case Prod = 'Prod';
}
