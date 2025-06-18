<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        "code",
        "name",
        "fullname",
        "alpha2",
        "alpha3",
    ];

    public function cities(): HasMany
    {
        return $this->hasMany(Location::class, 'country_id', 'id');
    }

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class, 'country_id', 'id');
    }
}
