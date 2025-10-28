<?php

namespace App\Models\Tk;

use App\Enums\LocationType;
use App\Models\Location;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TerminalKit extends Model
{
    use HasFactory;

    protected $table = 'terminals_kit';

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            set: fn(string $name) => $this->normalizeName($name),
        );
    }

    protected function type(): Attribute
    {
        return Attribute::make(
            set: fn(string $type) => $this->normalizeType($type),
        );
    }

    private function normalizeName($name)
    {
        if (str_contains($name, '(')) {
            return trim(strstr($name, '(', true));
        }
        return trim($name);
    }

    /**
     * Приведение типов транспортной компании к установленному формату
     */
    private function normalizeType($type)
    {
        if ($type == 'гор.' || $type == 'г') {
            return LocationType::Town->value;
        }
        if ($type == 'пос.' || $type == 'посело' || $type == 'посёло') {
            return LocationType::Township->value;
        }
        if ($type == 'дер.' || $type == 'деревн') {
            return LocationType::Hamlet->value;
        }
        if ($type == 'с.' || $type == 'село') {
            return LocationType::Village->value;
        }
        if ($type == 'пгт') {
            return LocationType::Pgt->value;
        }
        if ($type == 'аул') {
            return LocationType::Aul->value;
        }
        if ($type == 'Хутор') {
            return LocationType::Farmstead->value;
        }
        if ($type == 'стан.' || $type == 'станци') {
            return LocationType::Stanitsa->value;
        }
        if ($type == 'городс') {
            return LocationType::UrbanVillage->value;
        }
        if ($type == 'Остров') {
            return LocationType::Island->value;
        }
        if ($type == 'Слобод') {
            return LocationType::Sloboda->value;
        }
        if ($type == 'Агрого') {
            return LocationType::AgroTown->value;
        }
    }
}
