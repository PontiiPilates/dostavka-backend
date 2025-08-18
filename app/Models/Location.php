<?php

namespace App\Models;

use App\Models\Region;
use App\Models\Tk\TerminalBaikal;
use App\Models\Tk\TerminalBoxberry;
use App\Models\Tk\TerminalCdek;
use App\Models\Tk\TerminalDellin;
use App\Models\Tk\TerminalDpd;
use App\Models\Tk\TerminalJde;
use App\Models\Tk\TerminalKit;
use App\Models\Tk\TerminalNrg;
use App\Models\Tk\TerminalPek;
use App\Models\Tk\TerminalVozovoz;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_name',
        'country_id',
        'index_min',
        'index_max',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class, 'region_id', 'id');
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'district_id', 'id');
    }

    public function terminalsBaikal(): HasMany
    {
        return $this->hasMany(TerminalBaikal::class, 'location_id', 'id');
    }

    public function terminalsKit(): HasMany
    {
        return $this->hasMany(TerminalKit::class, 'location_id', 'id');
    }

    public function terminalsBoxberry(): HasMany
    {
        return $this->hasMany(TerminalBoxberry::class, 'location_id', 'id');
    }

    public function terminalsCdek(): HasMany
    {
        return $this->hasMany(TerminalCdek::class, 'location_id', 'id');
    }

    public function terminalsDellin(): HasMany
    {
        return $this->hasMany(TerminalDellin::class, 'location_id', 'id');
    }

    public function terminalsDpd(): HasMany
    {
        return $this->hasMany(TerminalDpd::class, 'location_id', 'id');
    }

    public function terminalsJde(): HasMany
    {
        return $this->hasMany(TerminalJde::class, 'location_id', 'id');
    }

    public function terminalsNrg(): HasMany
    {
        return $this->hasMany(TerminalNrg::class, 'location_id', 'id');
    }

    public function terminalsPek(): HasMany
    {
        return $this->hasMany(TerminalPek::class, 'location_id', 'id');
    }

    public function terminalsVozovoz(): HasMany
    {
        return $this->hasMany(TerminalVozovoz::class, 'location_id', 'id');
    }
}
