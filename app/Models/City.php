<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'country_code',
        'country_name',
        'country_fullname',
        'region_code',
        'region_name',
        'city_code',
        'city_name',
        'index_min',
        'index_max',
        'alpha2',
        'alpha3',
        'city_id_boxberry',
    ];
}
