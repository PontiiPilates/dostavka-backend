<?php

declare(strict_types=1);

namespace App\Enums;

enum  RegionType: string
{
    case Area = 'область';
    case Edge = 'край';
    case District = 'район';
    case Locality = 'населённый пункт';
    case Town = 'город';
    case Zato = 'ЗАТО';
    case SmallTown = 'городок';
    case Hamlet = 'деревня';
    case Township = 'посёлок';
    case Village = 'село';
    case Farmstead = 'хутор';
    case Aul = 'аул';
    case JobVillage = 'рабочий посёлок';
    case Pgt = 'ПГТ';
    case Microdistrict = 'микрорайон';
    case ResidentialComplex = 'жилой комплекс';
    case Snt = 'СНТ';
    case Spk = 'СПК';
    case Stanitsa = 'станица';
    case Station = 'станция';
}
