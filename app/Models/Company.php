<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'label',
    ];

    public function tariffs(): HasMany
    {
        return $this->hasMany(TariffPochta::class, 'companies_id', 'id');
    }
}
