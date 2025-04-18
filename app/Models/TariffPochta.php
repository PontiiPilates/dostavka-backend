<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TariffPochta extends Model
{
    use HasFactory;

    protected $table = 'tariffs_pochta';

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
