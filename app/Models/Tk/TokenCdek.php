<?php

namespace App\Models\Tk;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenCdek extends Model
{
    use HasFactory;

    protected $table = 'token_cdek';

    protected $fillable = [
        'token',
        'expires'
    ];
}
