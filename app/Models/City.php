<?php

namespace App\Models;

use App\Models\Tk\TerminalJde;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_name',
        'country_id'
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function terminalsJde(): HasMany
    {
        return $this->hasMany(TerminalJde::class, 'city_id', 'id');
    }
}
