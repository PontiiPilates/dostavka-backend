<?php

namespace App\Enums;

enum  LocationType: string
{
    // типы регионов
    case Area = 'область';
    case AutonomousRegion = 'автономный округ';
    case District = 'район';
    case Edge = 'край';
    case Republic = 'Республика';

        // типы локаций
    case AgroTown = 'агрогородок';
    case Aul = 'аул';
    case CottageVillage = 'дачный посёлок';
    case Farmstead = 'хутор';
    case Hamlet = 'деревня';
    case Island = 'остров';
    case JobVillage = 'рабочий посёлок';
    case Locality = 'населённый пункт';
    case MicroDistrict = 'микрорайон';
    case Pgt = 'пгт';
    case ResidentialComplex = 'жилой комплекс';
    case ResortVillage = 'курортный посёлок';
    case RualVillage = 'сельское поселение';
    case Sloboda = 'слобода';
    case SmallTown = 'городок';
    case Snt = 'снт';
    case Spk = 'спк';
    case Stanitsa = 'станица';
    case StateFarm = 'совхоз';
    case Town = 'город';
    case Township = 'посёлок';
    case UrbanVillage = 'городской посёлок';
    case Village = 'село';
    case Zato = 'зато';
}
