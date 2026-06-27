<?php

namespace App\Models;

use Database\Factories\LevelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $min_xp
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'min_xp', 'sort_order'])]
class Level extends Model
{
    /** @use HasFactory<LevelFactory> */
    use HasFactory;

    /**
     * @return array<string, mixed>
     */
    public function toPageArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'min_xp' => $this->min_xp,
            'sort_order' => $this->sort_order,
        ];
    }
}
