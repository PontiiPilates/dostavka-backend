<?php

namespace App\Enums;

enum  EnvironmentType: string
{
    case Local = 'local';
    case Dev = 'dev';
    case Test = 'test';
    case Prod = 'production';
}
