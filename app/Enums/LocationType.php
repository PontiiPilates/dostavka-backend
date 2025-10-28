<?php

namespace App\Enums;

enum  LocationType: string
{
    case AgroTown = 'агрогородок';
    case Area = 'область';
    case Aul = 'аул';
    case AutonomousRegion = 'автономный округ';
    case CottageVillage = 'дачный посёлок';
    case District = 'район';
    case Edge = 'край';
    case Farmstead = 'хутор';
    case Hamlet = 'деревня';
    case Island = 'остров';
    case JobVillage = 'рабочий посёлок';
    case Locality = 'населённый пункт';
    case MicroDistrict = 'микрорайон';
    case Pgt = 'пгт';
    case Republic = 'Республика';
    case ResidentialComplex = 'жилой комплекс';
    case ResortVillage = 'курортный посёлок';
    case RualVillage = 'сельское поселение';
    case Sloboda = 'слобода';
    case SmallTown = 'городок';
    case Snt = 'снт';
    case Spk = 'спк';
    case Stanitsa = 'станица';
    case Town = 'город';
    case Township = 'посёлок';
    case UrbanVillage = 'городской посёлок';
    case Village = 'село';
    case Zato = 'зато';
}
