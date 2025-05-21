<?php

namespace App\Models\Tk;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerminalJde extends Model
{
    use HasFactory;

    protected $table = 'terminals_jde';

    protected $fillable = [
        'city_id',
        'city_name',
        'terminal_id',
        'acceptance',
        'issue'
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class, 'city_id', 'id');
    }
}
