<?php

namespace App\Models;

use Database\Factories\FormEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A saved submission of one of the forms demo forms.
 *
 * @property int $id
 * @property string $demo
 * @property string $title
 * @property array<string, mixed> $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['demo', 'title', 'data'])]
class FormEntry extends Model
{
    /** @use HasFactory<FormEntryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
