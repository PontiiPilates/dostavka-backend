<?php

namespace App\Models\Tk;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerminalJde extends Model
{
    use HasFactory;

    protected $table = 'terminals_jde';

    public function city(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }
}
