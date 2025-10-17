<?php

namespace App\Enums;

enum  LocationType: string
{
    case Area = 'область';
    case Edge = 'край';
    case Republic = 'Республика';
    case AutonomousRegion = 'автономный округ';
    case District = 'район';
    case Locality = 'населённый пункт';
    case UrbanVillage = 'городской посёлок';
    case Town = 'город';
    case AgroTown = 'агрогородок';
    case Zato = 'зато';
    case SmallTown = 'городок';
    case Hamlet = 'деревня';
    case Township = 'посёлок';
    case Village = 'село';
    case Island = 'остров';
    case RualVillage = 'сельское поселение';
    case Farmstead = 'хутор';
    case Sloboda = 'слобода';
    case Aul = 'аул';
    case JobVillage = 'рабочий посёлок';
    case CottageVillage = 'дачный посёлок';
    case Pgt = 'пгт';
    case MicroDistrict = 'микрорайон';
    case ResidentialComplex = 'жилой комплекс';
    case Snt = 'снт';
    case Spk = 'спк';
    case Stanitsa = 'станица';
    case ResortVillage = 'курортный посёлок';
}
